<?php

namespace App\Http\Middleware\RateLimit;

use App\Helpers\ApiResponseHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\RateLimiter as RateLimiterFacade;

class AuthRateLimitMiddleware
{

    public function handle(Request $request, Closure $next)
    {
        $key = $this->throttleKey($request);

        // Allow 5 login attempts per minute per IP/email combination
        $maxAttempts = 5;
        $decayMinutes = 1;

        if (RateLimiterFacade::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiterFacade::availableIn($key);

            return ApiResponseHelper::error(
                'Too many authentication attempts. Please try again later.',
                [
                    'retry_after_seconds' => $seconds,
                    'retry_after_minutes' => ceil($seconds / 60),
                    'limit_type' => 'auth_rate_limit'
                ],
                429
            )->withHeaders([
                'Retry-After' => $seconds,
                'X-RateLimit-Limit' => $maxAttempts,
                'X-RateLimit-Remaining' => 0,
            ]);
        }

        $response = $next($request);

        if ($response->getStatusCode() === 401) {
            RateLimiterFacade::hit($key, $decayMinutes * 60);
        } else {
            RateLimiterFacade::clear($key);
        }

        $remaining = $maxAttempts - RateLimiterFacade::attempts($key);
        $response->withHeaders([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => max(0, $remaining),
        ]);

        return $response;
    }


    protected function throttleKey(Request $request): string
    {
        $email = $request->input('email', '');
        $ip = $request->ip();

        return 'auth_attempts:' . sha1($email . '|' . $ip);
    }
}
