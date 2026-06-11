<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A lead may target multiple architecture concours in parallel (e.g. ENA
 * AND UIR). Switching `concours_vise` from a single string to a JSON
 * array so the form can submit multiple values.
 *
 * Existing rows have a single-string value — we migrate them to a 1-item
 * JSON array before changing the column type.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Stash existing single-string values to a temporary column.
        Schema::table('registrations_concours', function (Blueprint $table): void {
            $table->string('concours_vise_old', 32)->nullable()->after('concours_vise');
        });

        DB::statement('UPDATE registrations_concours SET concours_vise_old = concours_vise');

        // 2. Drop the old index + column.
        Schema::table('registrations_concours', function (Blueprint $table): void {
            $table->dropIndex(['concours_vise']);
            $table->dropColumn('concours_vise');
        });

        // 3. Re-create as JSON.
        Schema::table('registrations_concours', function (Blueprint $table): void {
            $table->json('concours_vise')->after('city');
        });

        // 4. Backfill the JSON column with the stashed single value wrapped
        //    in a 1-item array. Default to ["ena"] for any stray nulls.
        DB::statement(
            'UPDATE registrations_concours '
            ."SET concours_vise = JSON_ARRAY(COALESCE(concours_vise_old, 'ena'))"
        );

        // 5. Drop the temp column.
        Schema::table('registrations_concours', function (Blueprint $table): void {
            $table->dropColumn('concours_vise_old');
        });
    }

    public function down(): void
    {
        Schema::table('registrations_concours', function (Blueprint $table): void {
            $table->string('concours_vise_old', 32)->nullable()->after('concours_vise');
        });

        // Take only the first element of the JSON array on rollback.
        DB::statement(
            'UPDATE registrations_concours '
            ."SET concours_vise_old = COALESCE(JSON_UNQUOTE(JSON_EXTRACT(concours_vise, '$[0]')), 'ena')"
        );

        Schema::table('registrations_concours', function (Blueprint $table): void {
            $table->dropColumn('concours_vise');
        });
        Schema::table('registrations_concours', function (Blueprint $table): void {
            $table->string('concours_vise', 32)->default('ena')->after('city')->index();
        });

        DB::statement('UPDATE registrations_concours SET concours_vise = concours_vise_old');

        Schema::table('registrations_concours', function (Blueprint $table): void {
            $table->dropColumn('concours_vise_old');
        });
    }
};
