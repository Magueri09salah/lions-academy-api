<?php

declare(strict_types=1);

namespace App\Services\Registration;

/**
 * Builds wa.me deep links with pre-filled messages.
 *
 * Mirrors the frontend's `whatsappUrl()` helper in
 * `lion-s-roar-academy/src/lib/site.ts` so links generated server-side
 * (in registration responses, admin tables, notification emails) match
 * the format the public site already produces.
 */
final class WhatsAppLinkBuilder
{
    /**
     * Build a link addressed to the academy's WhatsApp number.
     * Returns null when the academy number is not configured.
     */
    public function forAcademy(?string $message = null): ?string
    {
        $number = $this->cleanNumber((string) config('lions.whatsapp.number'));
        if ($number === '') {
            return null;
        }

        $msg = $message ?? (string) config('lions.whatsapp.default_message');

        return $this->buildUrl($number, $msg);
    }

    /**
     * Build a link addressed to a specific applicant's phone, used by
     * the back-office to reach out from the registration table.
     */
    public function forApplicant(?string $phone, ?string $message = null): ?string
    {
        $number = $this->cleanNumber((string) $phone);
        if ($number === '') {
            return null;
        }

        return $this->buildUrl($number, (string) ($message ?? ''));
    }

    private function buildUrl(string $number, string $message): string
    {
        return sprintf(
            'https://wa.me/%s?text=%s',
            $number,
            rawurlencode($message),
        );
    }

    /**
     * wa.me expects digits only, no leading "+". Accept "+212...", "00212...",
     * or "212..." and any human formatting (spaces, dashes).
     */
    private function cleanNumber(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value);

        return (string) $digits;
    }
}
