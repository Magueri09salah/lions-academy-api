<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The academy now offers multiple formations (interior design, English, …)
 * and each programme month belongs to ONE formation. Existing months are
 * backfilled onto the oldest formation (the original interior-design one).
 *
 * The column is nullable with nullOnDelete so deleting a formation doesn't
 * cascade-delete its programme — the months just become unassigned and the
 * admin can re-attach them. Validation makes it required on create.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('program_months', function (Blueprint $table): void {
            $table->foreignId('formation_id')
                ->nullable()
                ->after('id')
                ->constrained('formations')
                ->nullOnDelete();
        });

        // Backfill: attach every existing month to the oldest formation.
        $firstFormationId = DB::table('formations')->orderBy('id')->value('id');
        if ($firstFormationId !== null) {
            DB::table('program_months')
                ->whereNull('formation_id')
                ->update(['formation_id' => $firstFormationId]);
        }
    }

    public function down(): void
    {
        Schema::table('program_months', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('formation_id');
        });
    }
};
