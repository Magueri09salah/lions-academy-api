<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Formation;
use App\Models\Registration;
use App\Support\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Registration>
 */
class RegistrationFactory extends Factory
{
    protected $model = Registration::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $formation = Formation::query()->inRandomOrder()->first()
            ?? Formation::factory()->create();

        return [
            'full_name' => fake()->name(),
            'whatsapp_phone' => '+212'.fake()->numerify('6########'),
            'email' => fake()->unique()->safeEmail(),
            'city' => fake()->randomElement(['Casablanca', 'Marrakech', 'Rabat', 'Agadir', 'Tanger', 'Fès']),
            'address' => fake()->streetAddress(),
            'education_level' => fake()->randomElement(['Bac', 'Bac+2', 'Bac+3', 'Bac+5', 'Autre']),
            'profession' => fake()->boolean(40) ? fake()->jobTitle() : null,
            'formation_id' => $formation->id,
            'formation_title' => $formation->title,
            'message' => fake()->boolean(70) ? fake()->paragraph() : null,
            'privacy_accepted' => true,
            'status' => RegistrationStatus::New,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'submitted_at' => fake()->dateTimeBetween('-3 months', 'now'),
        ];
    }

    public function status(RegistrationStatus $status): self
    {
        return $this->state(fn () => [
            'status' => $status,
            'status_changed_at' => now(),
        ]);
    }
}
