<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ThrottleSpam
{
    public function handle(Request $request, Closure $next, string $key = 'default', int $maxAttempts = 10, int $decayMinutes = 1): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $userId = Auth::id();
        $ip = $request->ip();
        $cacheKey = "spam_throttle_{$key}_{$userId}_{$ip}";
        
        $attempts = Cache::get($cacheKey, 0);
        
        if ($attempts >= $maxAttempts) {
            Log::warning('Spam throttle triggered', [
                'user_id' => $userId,
                'ip' => $ip,
                'key' => $key,
                'attempts' => $attempts,
                'user_agent' => $request->userAgent(),
            ]);
            
            // Optional: Temporarily ban user from action
            Cache::put("spam_banned_{$key}_{$userId}", true, now()->addMinutes(5));
            
            return response()->json([
                'error' => 'Too many requests. Please slow down.',
            ], 429);
        }
        
        Cache::put($cacheKey, $attempts + 1, now()->addMinutes($decayMinutes));
        
        return $next($request);
    }
}