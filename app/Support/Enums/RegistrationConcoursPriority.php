<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * Lead priority tag, computed server-side from regional grade + filière.
 * Marketing sorts callbacks by this so high-fit leads are reached first.
 *
 *   high   → grade ≥ 14/20 AND filière compatible with ENA
 *   medium → exactly one of those two criteria
 *   low    → neither
 */
enum RegistrationConcoursPriority: string
{
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';

    public function label(): string
    {
        return match ($this) {
            self::High => 'Prioritaire',
            self::Medium => 'Moyenne',
            self::Low => 'Basse',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::High => 'success',
            self::Medium => 'gold',
            self::Low => 'sand',
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
