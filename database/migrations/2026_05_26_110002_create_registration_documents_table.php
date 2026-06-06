<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('registration_id')
                ->constrained('registrations')
                ->cascadeOnDelete();
            $table->foreignId('media_asset_id')
                ->constrained('media_assets')
                ->cascadeOnDelete();
            $table->string('label', 64)->nullable(); // e.g. "photo", "cin"
            $table->timestamps();

            $table->unique(['registration_id', 'media_asset_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_documents');
    }
};
