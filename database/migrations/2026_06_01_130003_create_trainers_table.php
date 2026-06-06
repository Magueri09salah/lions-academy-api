<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainers', function (Blueprint $table): void {
            $table->id();
            // Public slug used as the frontend id ("kb", "se", "ot" in the mock).
            $table->string('slug', 60)->unique();
            $table->string('name', 150);
            $table->string('role', 150);
            $table->string('specialty', 200);
            $table->text('bio')->nullable();
            $table->string('experience', 30);          // e.g. "10 ans"
            $table->string('initials', 8);             // e.g. "KB"
            $table->string('photo_url', 500)->nullable();
            $table->json('modules')->nullable();       // string[]
            $table->json('software')->nullable();      // string[]
            $table->string('instagram_url', 300)->nullable();
            $table->string('linkedin_url', 300)->nullable();
            $table->unsignedSmallInteger('display_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainers');
    }
};
