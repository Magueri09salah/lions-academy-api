<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Whitelist of Moroccan cities accepted on the ENA lead form
 * (per the marketing spec). "Autre" allows the lead to type anything
 * else; the controller stores the raw value either way.
 *
 * Kept as a simple class with a constant rather than an enum because
 * city is fundamentally a free-text field — this list just powers the
 * dropdown on the landing page and the filter chip in admin.
 */
final class EnaCities
{
    /** @var array<int, string> */
    public const LIST = [
        'Agadir',
        'Marrakech',
        'Casablanca',
        'Rabat',
        'Tanger',
        'Fès',
        'Meknès',
        'Oujda',
        'Kénitra',
        'El Jadida',
        'Safi',
        'Essaouira',
        'Beni Mellal',
        'Tétouan',
        'Nador',
        'Laâyoune',
        'Dakhla',
        'Guelmim',
        'Taroudant',
        'Tiznit',
        'Autre',
    ];

    /** @return array<int, string> */
    public static function values(): array
    {
        return self::LIST;
    }
}
