<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 200);
            $table->string('student_name', 150);
            $table->string('promotion', 50);          // e.g. "Promo 2026"
            $table->string('category', 100)->index(); // e.g. "Modélisations 3D"
            $table->json('software')->nullable();     // string[]
            $table->text('description')->nullable();
            $table->string('status', 50);             // e.g. "Rendu mensuel" / "PFF"
            $table->string('cover_url', 500)->nullable();
            $table->json('gallery_urls')->nullable(); // string[]
            $table->unsignedSmallInteger('display_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
