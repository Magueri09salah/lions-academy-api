<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ContactMessage;
use App\Support\Enums\ContactMessageStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactMessage>
 */
class ContactMessageFactory extends Factory
{
    protected $model = ContactMessage::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->boolean(60) ? '+212'.fake()->numerify('6########') : null,
            'subject' => fake()->randomElement([
                'Renseignements sur la formation',
                'Demande de tarif',
                'Question sur le programme',
                'Disponibilité prochaine session',
                'Autre',
            ]),
            'message' => fake()->paragraph(3),
            'status' => ContactMessageStatus::New,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'submitted_at' => fake()->dateTimeBetween('-3 months', 'now'),
        ];
    }

    public function status(ContactMessageStatus $status): self
    {
        return $this->state(fn () => [
            'status' => $status,
            'read_at' => $status !== ContactMessageStatus::New ? now() : null,
            'replied_at' => $status === ContactMessageStatus::Replied ? now() : null,
        ]);
    }
}
