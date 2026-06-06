<?php

declare(strict_types=1);

use App\Support\Enums\ContactMessageStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table): void {
            $table->id();

            // Sender (collected from the public form — no FK to users).
            $table->string('name', 150);
            $table->string('email', 200)->index();
            $table->string('phone', 32)->nullable();
            $table->string('subject', 200);
            $table->text('message');

            // Lifecycle
            $table->string('status', 32)
                ->default(ContactMessageStatus::New->value)
                ->index();
            $table->text('admin_notes')->nullable();

            // Audit
            $table->timestamp('read_at')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->foreignId('handled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Provenance — kept here for spam analysis; not exposed in the
            // admin list view but available on the detail page (admin-only).
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();

            $table->timestamp('submitted_at')->index();
            $table->timestamps();

            // Common admin query: latest-first within a status.
            $table->index(['status', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
