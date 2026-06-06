<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * Registration (inscription) lifecycle.
 *
 * Frontend page route stays `/inscription` (French). The backend canonical
 * vocabulary uses English: "registration" and these five values.
 *
 * Maps to the French labels expected in the admin UI (cf. CPS §8.7):
 *   new        → Nouveau
 *   contacted  → Contacté
 *   pending    → En attente
 *   registered → Inscrit
 *   rejected   → Refusé / annulé
 */
enum RegistrationStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Pending = 'pending';
    case Registered = 'registered';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Nouveau',
            self::Contacted => 'Contacté',
            self::Pending => 'En attente',
            self::Registered => 'Inscrit',
            self::Rejected => 'Refusé / annulé',
        };
    }

    /**
     * Colour token for the admin UI badges. The frontend can map this to its
     * design tokens; we keep raw values here so the API stays presentation-agnostic.
     */
    public function tone(): string
    {
        return match ($this) {
            self::New => 'gold',
            self::Contacted => 'ink',
            self::Pending => 'sand',
            self::Registered => 'success',
            self::Rejected => 'destructive',
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    /**
     * @return array<int, array{value:string,label:string,tone:string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $c) => ['value' => $c->value, 'label' => $c->label(), 'tone' => $c->tone()],
            self::cases(),
        );
    }
}
