<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Principle;
use Illuminate\Database\Seeder;

/**
 * Seeds the academy principles matching data.ts:PRINCIPLES.
 */
class PrincipleSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['title' => 'Formation accessible à distance', 'description' => "L'élève peut suivre les cours depuis n'importe quelle ville, sans obligation de déplacement."],
            ['title' => 'Formation orientée métier', 'description' => 'Former des personnes capables de comprendre un besoin client, concevoir un projet, produire des plans, créer une 3D et présenter leur travail.'],
            ['title' => 'Apprentissage par la pratique', 'description' => "Chaque mois, l'élève produit un rendu ou un exercice concret, corrigé individuellement."],
            ['title' => 'Maîtrise des outils professionnels', 'description' => 'AutoCAD, SketchUp, Photoshop, Illustrator — les logiciels essentiels du métier.'],
            ['title' => 'Culture architecturale et artistique', 'description' => "Mouvements architecturaux et artistiques, styles, formes, ambiances, couleurs et histoire de l'espace."],
            ['title' => "Sens, formes et pensée de l'espace", 'description' => 'Relation entre formes géométriques, architecture, ressenti, symbolique, usages et expérience humaine.'],
            ['title' => 'Éthique et professionnalisme', 'description' => "Comprendre la responsabilité du designer envers le client, l'espace, le budget et la qualité du rendu."],
            ['title' => 'Certification sérieuse', 'description' => 'Le certificat est obtenu seulement après validation des rendus mensuels et du Projet de Fin de Formation.'],
        ];

        foreach ($rows as $index => $row) {
            Principle::query()->updateOrCreate(
                ['title' => $row['title']],
                array_merge($row, ['display_order' => $index + 1, 'is_active' => true]),
            );
        }
    }
}
