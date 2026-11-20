<?php
// Author: Lai Chee Kit
// Module 4: one reply on a ticket. Replies are never edited or deleted.
// Uses the template's "ticket_replies" table (the text is the template's "message" column).

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketReply extends Model
{
    protected $fillable = ['message'];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    // The user who wrote the reply
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(TicketAttachment::class, 'ticket_reply_id');
    }
}
