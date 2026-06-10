<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * Architecture concours the lead is targeting. The /concours-ena landing
 * page was originally ENA-only — it was expanded to cover any Moroccan
 * architecture concours per the marketing brief.
 */
enum ArchitectureConcours: string
{
    case Ena = 'ena';
    case Uir = 'uir';
    case SapD = 'sap_d';
    case Eac = 'eac';
    case Autre = 'autre';

    public function label(): string
    {
        return match ($this) {
            self::Ena => 'ENA — École Nationale d\'Architecture',
            self::Uir => 'UIR — Université Internationale de Rabat',
            self::SapD => 'SAP+D — School of Architecture, Planning & Design',
            self::Eac => 'EAC — École d\'Architecture de Casablanca',
            self::Autre => 'Autre concours d\'architecture',
        };
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
