<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_months', function (Blueprint $table): void {
            $table->id();
            // Numeric position (1..N) drives the order on the public page.
            $table->unsignedTinyInteger('position')->index();
            $table->string('month_label', 32);       // e.g. "Mois 1"
            $table->string('title', 200);
            $table->string('axis', 200);
            $table->text('objective');
            $table->string('deliverable', 200);
            $table->json('items')->nullable();       // string[]
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_months');
    }
};
