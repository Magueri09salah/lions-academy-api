<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the full Formation content shape to the table.
 *
 * The minimal stub we shipped earlier only carried slug/title/is_active/order
 * (just enough for registrations.formation_id FK). The public site needs
 * the entire detail view from `lion-s-roar-academy/src/lib/data.ts`.
 *
 * objectives and categories are stored as JSON because:
 *   - admin UI edits them as a single ordered list / nested list (no need for separate rows)
 *   - read access is always "fetch the entire formation in one query"
 *   - the shape (`string[]` and `{title, items: string[]}[]`) doesn't change frequently
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formations', function (Blueprint $table): void {
            $table->string('cover_url', 500)->nullable()->after('title');
            $table->string('duration', 60)->nullable()->after('cover_url');
            $table->string('format', 60)->nullable()->after('duration');
            $table->string('level', 60)->nullable()->after('format');
            $table->text('summary')->nullable()->after('level');
            $table->text('audience')->nullable()->after('summary');
            $table->text('method')->nullable()->after('audience');
            $table->text('certification')->nullable()->after('method');
            $table->json('objectives')->nullable()->after('certification');
            $table->json('categories')->nullable()->after('objectives');
        });
    }

    public function down(): void
    {
        Schema::table('formations', function (Blueprint $table): void {
            $table->dropColumn([
                'cover_url', 'duration', 'format', 'level',
                'summary', 'audience', 'method', 'certification',
                'objectives', 'categories',
            ]);
        });
    }
};
