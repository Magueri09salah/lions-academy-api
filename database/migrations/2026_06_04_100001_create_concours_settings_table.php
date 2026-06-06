<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Singleton settings row for the /concours-ena landing page.
 *
 * Holds the four video slots (hero + explainer + 2 testimonials) plus
 * optional poster thumbnails. Editing them through the back-office
 * means marketing can A/B test campaigns without a redeploy.
 *
 * Singleton pattern: at most one row, identified by id=1. The model
 * exposes a `current()` accessor that creates the row on first access
 * if it doesn't exist.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('concours_settings', function (Blueprint $table): void {
            $table->id();

            // Hero — autoplay, muted, looped atmospheric clip.
            $table->string('hero_video_url', 500)->nullable();
            $table->string('hero_video_poster_url', 500)->nullable();

            // Explainer — clickable, with controls. Below the form.
            $table->string('explainer_video_url', 500)->nullable();
            $table->string('explainer_video_poster_url', 500)->nullable();
            $table->string('explainer_title', 150)->nullable();

            // Testimonial slots (admin can leave them empty).
            $table->string('testimonial_1_url', 500)->nullable();
            $table->string('testimonial_1_poster_url', 500)->nullable();
            $table->string('testimonial_1_label', 150)->nullable();

            $table->string('testimonial_2_url', 500)->nullable();
            $table->string('testimonial_2_poster_url', 500)->nullable();
            $table->string('testimonial_2_label', 150)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('concours_settings');
    }
};
