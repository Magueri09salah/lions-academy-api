<?php

declare(strict_types=1);

use App\Support\Enums\RegistrationConcoursStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Separate table from `registrations` because the ENA prep funnel is a
 * different product with different qualifying fields, different sales
 * vocabulary, and different reporting needs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations_concours', function (Blueprint $table): void {
            $table->id();

            // Identity (per PDF spec)
            $table->string('full_name', 150);
            $table->string('whatsapp_phone', 32)->index();
            $table->string('email', 200)->index();

            // Qualification answers
            $table->string('filiere', 50)->index();         // Enum value
            $table->string('regional_grade', 32)->index();  // Enum value
            $table->string('city', 120)->index();           // From CITIES list (or "Autre")
            $table->string('preferred_format', 50)->index();// Enum value
            $table->boolean('passed_ena_before')->default(false);

            // Lifecycle
            $table->string('status', 32)
                ->default(RegistrationConcoursStatus::New->value)
                ->index();

            // Computed lead score — sorted-by in admin so the marketing
            // team works the high-fit leads first.
            $table->string('priority', 16)->index();

            $table->text('admin_notes')->nullable();

            // Provenance
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();

            $table->timestamp('submitted_at')->index();
            $table->timestamp('status_changed_at')->nullable();
            $table->foreignId('status_changed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            // Common admin views: latest-first within a status; priority
            // sort within a date window.
            $table->index(['status', 'submitted_at']);
            $table->index(['priority', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations_concours');
    }
};
