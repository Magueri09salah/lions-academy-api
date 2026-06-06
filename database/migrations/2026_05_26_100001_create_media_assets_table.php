<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_assets', function (Blueprint $table): void {
            $table->id();
            $table->string('disk', 32);
            $table->string('path', 512);
            $table->string('mime', 128);
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('original_name', 255)->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt_text', 255)->nullable();
            $table->string('checksum', 64)->nullable()->index();
            $table->string('visibility', 16)->default('public')->index();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['disk', 'visibility']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_assets');
    }
};
