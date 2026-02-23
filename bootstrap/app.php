<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 🔐 Register custom Security Headers middleware
        $middleware->web(append: [
            SecurityHeaders::class,
        ]);

        // 🔐 Stateful domains for Sanctum
        $middleware->statefulApi();

        // 🔐 Validate CSRF tokens
        $middleware->validateCsrfTokens(except: [
            // Add routes that don't need CSRF
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 🔐 Log security-related exceptions
        $exceptions->render(function (\Throwable $e, Request $request): ?Response {
            
            // Log 403 Forbidden
            if ($e instanceof AccessDeniedHttpException) {
                \Log::warning('Access Denied', [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'user_id' => auth()->id(),
                    'message' => $e->getMessage(),
                ]);
            }

            // Log 404 with suspicious patterns
            if ($e instanceof NotFoundHttpException) {
                $path = $request->path();
                $suspicious = ['wp-admin', 'phpmyadmin', '.env', 'config', 'backup', 'sql', 'admin'];
                
                foreach ($suspicious as $keyword) {
                    if (str_contains(strtolower($path), $keyword)) {
                        \Log::warning('Suspicious 404 Scan Attempt', [
                            'path' => $path,
                            'ip' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                        ]);
                        break;
                    }
                }
            }

            // Log all unhandled exceptions in production
            if (config('app.env') === 'production' && config('app.debug') === false) {
                \Log::error('Unhandled Exception', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'url' => $request->fullUrl(),
                    'ip' => $request->ip(),
                ]);
                
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Server error'], 500);
                }
            }

            return null;
        });

        // 🔐 Report only critical exceptions
        $exceptions->report(function (\Throwable $e) {
            if ($e instanceof NotFoundHttpException && config('app.env') === 'production') {
                return false;
            }
            return true;
        });
    })
    ->create();