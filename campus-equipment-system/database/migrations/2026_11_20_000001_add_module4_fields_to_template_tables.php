<?php
// Author: Lai Chee Kit
// Module 4: adds the few columns Module 4 needs that the template's tables do not have.
// The tables themselves come from the template (2026_11_13_000001_create_campus_equipment_tables),
// so this file only ALTERs them. It never creates a table again.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Fault, Usage Problem or General Feedback (used by filters, reports and the open-fault API)
            $table->string('category', 30)->default('General Feedback')->after('equipment_id');
            // When the ticket was closed (used by the handling-time report)
            $table->timestamp('closed_at')->nullable()->after('resolved_at');
        });

        Schema::table('notifications', function (Blueprint $table) {
            // The ticket a bell notification is about
            $table->foreignId('ticket_id')->nullable()->after('user_id')->constrained('tickets')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ticket_id');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['category', 'closed_at']);
        });
    }
};
