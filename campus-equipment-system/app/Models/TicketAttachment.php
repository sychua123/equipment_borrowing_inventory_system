<?php
// Author: Lai Chee Kit
// Module 4: one photo attached to a ticket or to a reply.
// Uses the template's "ticket_attachments" table, which has no updated_at column.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketAttachment extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['ticket_id', 'ticket_reply_id', 'uploaded_by', 'file_name', 'file_path', 'mime_type', 'file_size'];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function reply(): BelongsTo
    {
        return $this->belongsTo(TicketReply::class, 'ticket_reply_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
