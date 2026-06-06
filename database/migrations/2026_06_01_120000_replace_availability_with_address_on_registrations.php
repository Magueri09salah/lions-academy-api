<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Inscription form was changed: applicants now give their full address
 * instead of a coarse availability window.
 *
 * `address` is nullable at the DB level so existing rows (created before
 * this change) survive the migration. The public form's StoreRegistration
 * request still validates it as required, so new submissions always carry
 * an address value.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table): void {
            $table->dropColumn('availability');
        });

        Schema::table('registrations', function (Blueprint $table): void {
            $table->string('address', 255)->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table): void {
            $table->dropColumn('address');
        });

        Schema::table('registrations', function (Blueprint $table): void {
            $table->string('availability', 64)->after('city');
        });
    }
};
