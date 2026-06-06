<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Trainer;
use Illuminate\Database\Seeder;

/**
 * Seeds trainers matching data.ts:TRAINERS.
 */
class TrainerSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'slug' => 'kb',
                'name' => 'Khalid Benani',
                'role' => "Architecte d'intérieur",
                'specialty' => 'Espaces résidentiels haut de gamme',
                'bio' => "10 ans d'expérience en agence, spécialiste des espaces résidentiels haut de gamme et de la conception sur-mesure.",
                'experience' => '10 ans',
                'initials' => 'KB',
                'photo_url' => null,
                'modules' => ["Bases de l'architecture d'intérieur", 'Atelier de conception', 'Projet de Fin de Formation'],
                'software' => ['AutoCAD', 'ArchiCAD', 'Plan théchnique'],
                'instagram_url' => 'https://instagram.com/',
                'linkedin_url' => 'https://linkedin.com/',
            ],
            [
                'slug' => 'se',
                'name' => 'Salma El Idrissi',
                'role' => 'Designer & formatrice 3D',
                'specialty' => 'Modélisation 3D et rendu',
                'bio' => 'Experte SketchUp et rendu, passionnée par la transmission et les ateliers pratiques.',
                'experience' => '8 ans',
                'initials' => 'SE',
                'photo_url' => null,
                'modules' => ['3D & modélisation', 'Infographie & présentation'],
                'software' => ['SketchUp', 'Photoshop', 'Illustrator'],
                'instagram_url' => 'https://instagram.com/',
                'linkedin_url' => 'https://linkedin.com/',
            ],
            [
                'slug' => 'ot',
                'name' => 'Omar Tazi',
                'role' => 'Architecte & enseignant',
                'specialty' => "Culture architecturale et théorie de l'espace",
                'bio' => "Architecte enseignant, spécialiste de l'histoire des styles et de la pensée de l'espace.",
                'experience' => '15 ans',
                'initials' => 'OT',
                'photo_url' => null,
                'modules' => ['Théorie & culture architecturale'],
                'software' => ['AutoCAD', 'ArchiCAD'],
                'instagram_url' => 'https://instagram.com/',
                'linkedin_url' => 'https://linkedin.com/',
            ],
        ];

        foreach ($rows as $index => $row) {
            Trainer::query()->updateOrCreate(
                ['slug' => $row['slug']],
                array_merge($row, ['display_order' => $index + 1, 'is_active' => true]),
            );
        }
    }
}
