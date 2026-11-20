<?php
// Author: Lai Chee Kit
// Module 4: one in-app notification for one user about one ticket.
// Uses the template's "notifications" table (+ ticket_id from 2026_11_20_000001).

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = ['user_id', 'ticket_id', 'title', 'message', 'notification_type', 'is_read', 'read_at'];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function isUnread(): bool
    {
        return ! $this->is_read;
    }

    // Marks the notification as read (both the flag and the time)
    public function markAsRead(): void
    {
        $this->is_read = true;
        $this->read_at = now();
        $this->save();
    }
}
