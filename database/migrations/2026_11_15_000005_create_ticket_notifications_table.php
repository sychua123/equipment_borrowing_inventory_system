<?php
// Author: Lai Chee Kit
// Module 4: in-app notifications (the bell in the navbar)

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ticket_notifications', function (Blueprint $table) {
            $table->id();
            // The user who receives the notification
            $table->foreignId('user_id')->constrained();
            // The ticket the notification is about
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            // new_ticket, new_reply or status_changed
            $table->string('type', 30);
            $table->string('message');
            // Empty = unread
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_notifications');
    }
};
