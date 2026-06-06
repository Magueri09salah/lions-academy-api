<?php

declare(strict_types=1);

use App\Support\Enums\RegistrationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table): void {
            $table->id();

            // Identity
            $table->string('full_name', 150);
            $table->string('whatsapp_phone', 32)->index();
            $table->string('email', 200)->index();
            $table->string('city', 120);

            // Profile
            $table->string('education_level', 64);
            $table->string('profession', 120)->nullable();

            // Formation choice (FK + snapshot)
            $table->foreignId('formation_id')
                ->nullable()
                ->constrained('formations')
                ->nullOnDelete();
            $table->string('formation_title', 200)->nullable(); // snapshot

            // Logistics + free text
            $table->string('availability', 64);
            $table->text('message')->nullable();
            $table->boolean('privacy_accepted')->default(false);

            // Lifecycle
            $table->string('status', 32)
                ->default(RegistrationStatus::New->value)
                ->index();
            $table->text('admin_notes')->nullable();

            // Provenance (for spam analysis / audit)
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();

            $table->timestamp('submitted_at')->index();
            $table->timestamp('status_changed_at')->nullable();
            $table->foreignId('status_changed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            // Common admin queries: latest-first within a status.
            $table->index(['status', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
