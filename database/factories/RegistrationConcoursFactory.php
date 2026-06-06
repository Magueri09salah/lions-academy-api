<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RegistrationConcours;
use App\Support\EnaCities;
use App\Support\Enums\EnaFiliere;
use App\Support\Enums\EnaPreferredFormat;
use App\Support\Enums\EnaRegionalGrade;
use App\Support\Enums\RegistrationConcoursStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegistrationConcours>
 */
class RegistrationConcoursFactory extends Factory
{
    protected $model = RegistrationConcours::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $filiere = fake()->randomElement(EnaFiliere::cases());
        $grade = fake()->randomElement(EnaRegionalGrade::cases());

        return [
            'full_name' => fake()->name(),
            'whatsapp_phone' => '+212'.fake()->numerify('6########'),
            'email' => fake()->unique()->safeEmail(),
            'filiere' => $filiere,
            'regional_grade' => $grade,
            'city' => fake()->randomElement(EnaCities::LIST),
            'preferred_format' => fake()->randomElement(EnaPreferredFormat::cases()),
            'passed_ena_before' => fake()->boolean(20),
            'status' => RegistrationConcoursStatus::New,
            'priority' => RegistrationConcours::computePriority($filiere, $grade),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'submitted_at' => fake()->dateTimeBetween('-3 months', 'now'),
        ];
    }
}
