<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * High-school / bac options for the ENA lead form.
 * Matches the PDF spec exactly. "Autre" catches anything else.
 */
enum EnaFiliere: string
{
    case SciencesMathA = 'sciences_math_a';
    case SciencesMathB = 'sciences_math_b';
    case SciencesPhysiques = 'sciences_physiques';
    case SVT = 'svt';
    case SciencesEconomiques = 'sciences_economiques';
    case ArtsAppliques = 'arts_appliques';
    case STElectriques = 'st_electriques';
    case STMecaniques = 'st_mecaniques';
    case Autre = 'autre';

    public function label(): string
    {
        return match ($this) {
            self::SciencesMathA => 'Sciences Mathématiques A',
            self::SciencesMathB => 'Sciences Mathématiques B',
            self::SciencesPhysiques => 'Sciences Physiques',
            self::SVT => 'Sciences de la Vie et de la Terre (SVT)',
            self::SciencesEconomiques => 'Sciences Économiques',
            self::ArtsAppliques => 'Arts Appliqués',
            self::STElectriques => 'Sciences et Technologies Électriques',
            self::STMecaniques => 'Sciences et Technologies Mécaniques',
            self::Autre => 'Autre',
        };
    }

    /**
     * Compatible filières for the ENA concours — used by the priority
     * computation. Tweak this list if marketing changes the criteria.
     *
     * @return array<int, self>
     */
    public static function compatible(): array
    {
        return [
            self::SciencesMathA,
            self::SciencesMathB,
            self::SciencesPhysiques,
            self::ArtsAppliques,
        ];
    }

    public function isCompatible(): bool
    {
        return in_array($this, self::compatible(), true);
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
