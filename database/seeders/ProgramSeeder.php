<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ProgramMonth;
use Illuminate\Database\Seeder;

/**
 * Seeds the 6-month programme matching data.ts:PROGRAM.
 */
class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['position' => 1, 'month_label' => 'Mois 1', 'title' => "Bases de l'architecture d'intérieur", 'axis' => "Bases de l'architecture d'intérieur", 'objective' => "Comprendre l'espace, les usages et les bases du design.", 'deliverable' => 'Exercice 1', 'items' => []],
            ['position' => 2, 'month_label' => 'Mois 2', 'title' => 'Plans 2D et logiciels', 'axis' => 'Plans 2D et logiciels', 'objective' => 'Lire et produire des plans simples.', 'deliverable' => 'Exercice 2', 'items' => ['AutoCAD : prise en main', 'Cotations et coupes', "Plan d'aménagement"]],
            ['position' => 3, 'month_label' => 'Mois 3', 'title' => 'Modélisation 3D', 'axis' => 'Modélisation 3D', 'objective' => 'Créer un espace intérieur en volume.', 'deliverable' => 'Exercice 3', 'items' => ['Initiation SketchUp', 'Volumes et matériaux', "Modélisation d'une pièce"]],
            ['position' => 4, 'month_label' => 'Mois 4', 'title' => 'Théorie, styles et ambiance', 'axis' => 'Théorie, styles et ambiance', 'objective' => 'Construire un concept cohérent.', 'deliverable' => 'Exercice 4', 'items' => ['Styles décoratifs', 'Moodboards et palettes', 'Symbolique des formes']],
            ['position' => 5, 'month_label' => 'Mois 5', 'title' => 'Infographie et présentation', 'axis' => 'Infographie et présentation', 'objective' => 'Présenter un projet professionnellement.', 'deliverable' => 'Exercice 5', 'items' => ['Photoshop & Illustrator', 'Planches de présentation', 'Storytelling visuel']],
            ['position' => 6, 'month_label' => 'Mois 6', 'title' => 'Atelier final', 'axis' => 'Préparations aux examens avec documents autorisés', 'objective' => 'Réaliser un projet complet.', 'deliverable' => 'Projet de Fin de Formation', 'items' => ['Conception complète', 'Plans, 3D et moodboard', 'Soutenance & certification']],
        ];

        foreach ($rows as $row) {
            ProgramMonth::query()->updateOrCreate(
                ['position' => $row['position']],
                array_merge($row, ['is_active' => true]),
            );
        }
    }
}
