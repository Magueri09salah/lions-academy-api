<?php

declare(strict_types=1);

namespace App\Services\Security;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Verifies Cloudflare Turnstile tokens (when configured).
 *
 * Used as a server-side check on public form submissions in addition to
 * the silent honeypot. Returns true (no-op) when no secret key is set
 * so local development keeps working without ceremony.
 *
 * Wire into a route by checking `TurnstileVerifier::isHuman($token, $ip)`
 * inside the FormRequest's withValidator(), or via a dedicated middleware.
 */
final class TurnstileVerifier
{
    private const ENDPOINT = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function isHuman(?string $token, ?string $remoteIp = null): bool
    {
        $secret = (string) config('lions.turnstile.secret_key');

        // Disabled when no secret is configured (local/dev).
        if ($secret === '') {
            return true;
        }

        if (empty($token)) {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post(self::ENDPOINT, array_filter([
                    'secret' => $secret,
                    'response' => $token,
                    'remoteip' => $remoteIp,
                ]));
        } catch (\Throwable $e) {
            Log::warning('Turnstile verification failed', ['error' => $e->getMessage()]);

            return false;
        }

        if (! $response->ok()) {
            return false;
        }

        return (bool) ($response->json('success') ?? false);
    }
}
