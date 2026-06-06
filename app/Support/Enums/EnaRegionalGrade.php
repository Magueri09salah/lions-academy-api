<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * Bucket of the bac régional grade. Stored as a string code; the
 * `isHigh()` helper drives the priority computation.
 */
enum EnaRegionalGrade: string
{
    case Below12 = 'below_12';
    case Between12And14 = '12_to_14';
    case Between14And16 = '14_to_16';
    case Above16 = 'above_16';

    public function label(): string
    {
        return match ($this) {
            self::Below12 => 'Moins de 12/20',
            self::Between12And14 => 'Entre 12/20 et 14/20',
            self::Between14And16 => 'Entre 14/20 et 16/20',
            self::Above16 => 'Plus de 16/20',
        };
    }

    /** Upper two buckets count as "high" for ENA priority scoring. */
    public function isHigh(): bool
    {
        return $this === self::Between14And16 || $this === self::Above16;
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    /** @return array<int, array{value:string,label:string}> */
    public static function options(): array
    {
        return array_map(
            fn (self $c) => ['value' => $c->value, 'label' => $c->label()],
            self::cases(),
        );
    }
}
