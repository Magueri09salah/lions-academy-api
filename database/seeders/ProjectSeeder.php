<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

/**
 * Seeds student projects matching data.ts:PROJECTS.
 * Image URLs resolve through `php artisan storage:link` →
 * `${APP_URL}/storage/seed/project-X.jpg`.
 */
class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $img = fn (string $name) => asset('storage/seed/'.$name);

        $rows = [
            ['title' => 'Salon contemporain ivoire', 'student_name' => 'Sara M.', 'promotion' => 'Promo 2026', 'category' => 'Rendus', 'software' => ['SketchUp', 'Photoshop'], 'description' => "Rendu d'ambiance d'un salon résidentiel jouant sur les tons ivoire, bois clair et touches de laiton.", 'status' => 'Rendu mensuel', 'cover_url' => $img('project-1.jpg'), 'gallery_urls' => [$img('project-1.jpg'), $img('project-2.jpg')]],
            ['title' => 'Moodboard matières naturelles', 'student_name' => 'Yassine B.', 'promotion' => 'Promo 2026', 'category' => 'Moodboards', 'software' => ['Photoshop', 'Illustrator'], 'description' => 'Moodboard explorant matières brutes : pierre, bois huilé, lin et terracotta.', 'status' => 'Rendu mensuel', 'cover_url' => $img('project-2.jpg'), 'gallery_urls' => [$img('project-2.jpg')]],
            ['title' => 'Chambre arche premium', 'student_name' => 'Imane F.', 'promotion' => 'Promo 2026', 'category' => 'Projet de Fin de Formation', 'software' => ['AutoCAD', 'SketchUp', 'Photoshop'], 'description' => 'Projet de fin de formation : chambre parentale haut de gamme avec arche en niche, dressing intégré et palette chaude.', 'status' => 'PFF', 'cover_url' => $img('project-3.jpg'), 'gallery_urls' => [$img('project-3.jpg'), $img('project-1.jpg'), $img('project-4.jpg')]],
            ['title' => 'Cuisine bois & laiton', 'student_name' => 'Mehdi A.', 'promotion' => 'Promo 2026', 'category' => 'Modélisations 3D', 'software' => ['SketchUp'], 'description' => "Modélisation 3D d'une cuisine ouverte mêlant bois fumé et détails laiton brossé.", 'status' => 'Rendu mensuel', 'cover_url' => $img('project-4.jpg'), 'gallery_urls' => [$img('project-4.jpg')]],
            ['title' => "Plan d'aménagement T3", 'student_name' => 'Nora K.', 'promotion' => 'Promo 2026', 'category' => 'Plans 2D', 'software' => ['AutoCAD'], 'description' => "Plan d'aménagement d'un appartement T3 avec cotations, mobilier et circulations.", 'status' => 'Rendu mensuel', 'cover_url' => $img('project-1.jpg'), 'gallery_urls' => [$img('project-1.jpg')]],
            ['title' => 'Planche de présentation cuisine', 'student_name' => 'Hicham R.', 'promotion' => 'Promo 2026', 'category' => 'Planches de présentation', 'software' => ['Photoshop', 'Illustrator'], 'description' => 'Planche complète : plan, vues 3D, matériaux et ambiance.', 'status' => 'Rendu mensuel', 'cover_url' => $img('project-4.jpg'), 'gallery_urls' => [$img('project-4.jpg'), $img('project-2.jpg')]],
        ];

        foreach ($rows as $index => $row) {
            Project::query()->updateOrCreate(
                ['title' => $row['title']],
                array_merge($row, ['display_order' => $index + 1, 'is_active' => true]),
            );
        }
    }
}
