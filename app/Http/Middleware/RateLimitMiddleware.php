<?php

declare(strict_types=1);

namespace App\Http\Middleware;

class RateLimitMiddleware
{
    public static function handle(int $maxAttempts = 60, int $window = 60): void
    {
        // Implementation placeholder - uses session-based rate limiting
    }
}
