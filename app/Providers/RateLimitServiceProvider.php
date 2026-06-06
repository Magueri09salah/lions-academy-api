<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

/**
 * Named rate-limit buckets used by routes. Counts are read from
 * config/lions.php which itself reads .env so DevOps can tune limits
 * without redeploying.
 *
 * Buckets:
 *   - public-read    : list/detail GETs from the public site (per IP)
 *   - public-write   : POST /inscriptions, /contact-messages (per IP, per hour)
 *   - auth           : POST /admin/auth/* (per IP + per email)
 *   - admin          : authenticated /admin/* (per user)
 *   - media-download : signed-URL hits (per IP)
 */
class RateLimitServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('public-read', function (Request $request) {
            return Limit::perMinute((int) config('lions.rate_limits.public_read'))
                ->by($request->ip());
        });

        RateLimiter::for('public-write', function (Request $request) {
            return Limit::perHour((int) config('lions.rate_limits.public_write_per_hour'))
                ->by($request->ip())
                ->response(fn () => response()->json([
                    'success' => false,
                    'error' => [
                        'message' => 'Trop de soumissions. Veuillez réessayer plus tard.',
                        'code' => 'rate_limited',
                    ],
                ], 429));
        });

        RateLimiter::for('auth', function (Request $request) {
            $emailKey = strtolower((string) $request->input('email', 'anon'));

            return [
                Limit::perMinute((int) config('lions.rate_limits.auth_per_minute'))
                    ->by($request->ip()),
                Limit::perMinute((int) config('lions.rate_limits.auth_per_minute'))
                    ->by('auth:'.$emailKey),
            ];
        });

        RateLimiter::for('admin', function (Request $request) {
            return Limit::perMinute((int) config('lions.rate_limits.admin_per_minute'))
                ->by(optional($request->user())->id ?: $request->ip());
        });

        RateLimiter::for('media-download', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip());
        });

        // Default `api` bucket — applied automatically by $middleware->throttleApi()
        // through the named-limiter Laravel uses out of the box.
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
