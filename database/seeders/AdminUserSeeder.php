<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Support\Enums\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) config('lions.seed_admin.email');
        $password = (string) config('lions.seed_admin.password');
        $name = (string) config('lions.seed_admin.name');

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role' => UserRole::Admin,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        $this->command?->info("Seeded admin user: {$email}");
    }
}
