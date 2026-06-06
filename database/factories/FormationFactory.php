<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Formation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Formation>
 */
class FormationFactory extends Factory
{
    protected $model = Formation::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'slug' => Str::slug($title).'-'.fake()->unique()->randomNumber(4),
            'title' => $title,
            'is_active' => true,
            'display_order' => 0,
        ];
    }
}
