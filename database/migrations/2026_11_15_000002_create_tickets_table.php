<?php
// Author: Lai Chee Kit
// Module 4: Support Ticket

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            // The user who submitted the ticket
            $table->foreignId('user_id')->constrained();
            // Equipment belongs to Module 1, so we store the ID only (no foreign key)
            $table->unsignedBigInteger('equipment_id')->nullable()->index();
            $table->string('category', 30);
            $table->string('priority', 10);
            $table->string('title', 120);
            $table->text('description');
            $table->string('status', 20)->default('Open')->index();
            // When the Admin marked it Resolved (used for auto-close and handling time)
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
