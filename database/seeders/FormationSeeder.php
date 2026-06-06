<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Formation;
use Illuminate\Database\Seeder;

/**
 * Seeds the single formation Lions Academy ships with at launch.
 * Content mirrors `lion-s-roar-academy/src/lib/data.ts:FORMATIONS[0]`
 * so the public site looks identical whether it reads from mocks or
 * from the live API.
 */
class FormationSeeder extends Seeder
{
    public function run(): void
    {
        Formation::query()->updateOrCreate(
            ['slug' => 'architecture-interieur'],
            [
                'title' => "Architecture d'intérieur & décoration",
                'is_active' => true,
                'display_order' => 1,
                'duration' => '6 mois',
                'format' => 'À distance',
                'level' => 'Débutant accepté',
                'cover_url' => asset('storage/seed/project-1.jpg'),
                'summary' => "Une formation complète et structurée pour concevoir, modéliser et présenter un projet d'aménagement intérieur de A à Z.",
                'audience' => 'Jeunes passionnés, étudiants, personnes en reconversion ou débutants curieux du métier.',
                'method' => 'Cours en ligne, exercices, corrections individuelles, rendus mensuels et projet de fin de formation.',
                'certification' => 'Certificat délivré après validation des rendus mensuels et du Projet de Fin de Formation.',
                'objectives' => [
                    "Comprendre les bases de l'architecture d'intérieur",
                    'Lire et produire des plans simples',
                    'Utiliser les logiciels de base (AutoCAD, SketchUp)',
                    'Modéliser un espace en 3D',
                    'Créer une ambiance intérieure et un moodboard',
                    'Comprendre les styles et mouvements',
                    'Présenter un projet de manière professionnelle',
                    'Réaliser un projet final complet',
                ],
                'categories' => [
                    ['title' => 'Logiciels 2D & plans', 'items' => ['AutoCAD', 'ArchiCAD', 'Lecture de plans', 'Plans 2D, cotations', 'Coupes et élévations', 'Bases du dessin architectural']],
                    ['title' => '3D & modélisation', 'items' => ['SketchUp', "Modélisation d'espaces intérieurs", 'Volumes et mobilier', 'Matériaux et textures', 'Préparation des scènes et exports']],
                    ['title' => 'Théorie & culture architecturale', 'items' => ["Histoire de l'architecture", 'Mouvements architecturaux et artistiques', 'Styles décoratifs', 'Symbolique des formes', 'Lumière, matière, ambiance', 'Psychologie des couleurs']],
                    ['title' => 'Infographie & présentation', 'items' => ['Adobe Photoshop', 'Adobe Illustrator', 'Planches de présentation', 'Moodboards', 'Mise en page et identité projet']],
                    ['title' => 'Atelier de conception', 'items' => ["Analyse d'un besoin client", "Création d'un concept", 'Choix du style et des matériaux', 'Rendu mensuel corrigé', 'Projet de Fin de Formation']],
                ],
            ],
        );
    }
}
