<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Widens the registrations_concours table to cover any architecture
 * concours (ENA / UIR / SAP+D / EAC / Autre) rather than ENA only, and
 * captures an optional free-form message from the lead.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations_concours', function (Blueprint $table): void {
            // Which concours the lead is targeting. Indexed because the
            // marketing team will filter / count leads per concours.
            $table->string('concours_vise', 32)
                ->default('ena')
                ->after('city')
                ->index();

            // Optional message — captured from the form's "Message
            // facultatif" textarea. Nullable, kept short to avoid abuse.
            $table->text('message')
                ->nullable()
                ->after('preferred_format');
        });
    }

    public function down(): void
    {
        Schema::table('registrations_concours', function (Blueprint $table): void {
            $table->dropIndex(['concours_vise']);
            $table->dropColumn(['concours_vise', 'message']);
        });
    }
};
