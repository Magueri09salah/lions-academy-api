<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * Sales funnel for ENA-prep leads (Concours d'entrée des ENA).
 *
 * Different vocabulary from the academy's Registrations module because
 * this is a marketing/sales pipeline rather than an enrollment lifecycle.
 *
 *   new        → lead just submitted the landing-page form
 *   contacted  → sales has reached out (WhatsApp, phone, email)
 *   qualified  → conversation confirmed serious intent + fit
 *   converted  → enrolled in the prep program / paid
 *   lost       → declined, unreachable, not a fit
 */
enum RegistrationConcoursStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Qualified = 'qualified';
    case Converted = 'converted';
    case Lost = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Nouveau',
            self::Contacted => 'Contacté',
            self::Qualified => 'Qualifié',
            self::Converted => 'Inscrit',
            self::Lost => 'Perdu',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::New => 'gold',
            self::Contacted => 'ink',
            self::Qualified => 'sand',
            self::Converted => 'success',
            self::Lost => 'destructive',
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    /** @return array<int, array{value:string,label:string,tone:string}> */
    public static function options(): array
    {
        return array_map(
            fn (self $c) => ['value' => $c->value, 'label' => $c->label(), 'tone' => $c->tone()],
            self::cases(),
        );
    }
}
