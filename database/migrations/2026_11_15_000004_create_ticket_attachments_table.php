<?php
// Author: Lai Chee Kit
// Module 4: photos attached to a ticket or to a reply

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ticket_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            // Empty = the photo belongs to the ticket itself; filled = it belongs to that reply
            $table->foreignId('ticket_reply_id')->nullable()->constrained()->cascadeOnDelete();
            // Where the file is kept inside storage/app/private (not reachable by URL)
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type', 50);
            $table->unsignedInteger('size');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_attachments');
    }
};
