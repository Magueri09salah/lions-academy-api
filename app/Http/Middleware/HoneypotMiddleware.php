<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Silent honeypot for public POST forms (inscription, contact).
 *
 * Two checks:
 *   1. A bait field (`_hp_field` by default) must be present and empty.
 *      Bots tend to autofill every input — if it's not empty, drop.
 *   2. A timer field (`_hp_time`, unix-millis when the form was rendered)
 *      must indicate a human took at least N seconds to submit.
 *
 * On failure we pretend success-ish (return a generic 422) and log the
 * attempt — bots get no signal that they were detected.
 */
class HoneypotMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $field = (string) config('lions.honeypot.field');
        $timerField = (string) config('lions.honeypot.timer_field');
        $minSeconds = (int) config('lions.honeypot.min_seconds', 2);

        $bait = $request->input($field);
        $timer = $request->input($timerField);

        if (! empty($bait)) {
            return $this->rejectSilently($request, 'honeypot_filled');
        }

        if ($timer !== null && is_numeric($timer)) {
            $elapsedSeconds = max(0, (int) ((microtime(true) * 1000) - (int) $timer) / 1000);
            if ($elapsedSeconds < $minSeconds) {
                return $this->rejectSilently($request, 'honeypot_too_fast');
            }
        }

        // Strip honeypot fields so they never reach validation/controllers.
        $request->request->remove($field);
        $request->request->remove($timerField);

        return $next($request);
    }

    private function rejectSilently(Request $request, string $reason): Response
    {
        Log::warning('Honeypot triggered', [
            'reason' => $reason,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'path' => $request->path(),
        ]);

        // Generic shape: indistinguishable from a normal validation error.
        return ApiResponse::error(
            message: 'Requête invalide.',
            status: 422,
            code: 'validation_failed',
        );
    }
}
