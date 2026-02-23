<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // 🔐 Prevent clickjacking
        $response->headers->set('X-Frame-Options', 'DENY');
        
        // 🔐 Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        
        // 🔐 Enable XSS filter
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        
        // 🔐 Control referrer
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // 🔐 Restrict browser features
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(), microphone=(), camera=(), payment=(), usb=()'
        );
        
        // ✅ FIXED: CSP yang compatible dengan Alpine.js
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' https://cdn.jsdelivr.net https://cdn.tailwindcss.com 'unsafe-inline' 'unsafe-eval'",
            "style-src 'self' https://fonts.googleapis.com 'unsafe-inline'",
            "font-src 'self' https://fonts.gstatic.com",
            "img-src 'self' https: data:",
            "connect-src 'self'",
            "frame-ancestors 'none'",
            "form-action 'self'",
            "base-uri 'self'",
        ]);
        $response->headers->set('Content-Security-Policy', $csp);
        
        // 🔐 HSTS - Only in production
        if (config('app.env') === 'production') {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        return $response;
    }
}