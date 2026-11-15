<?php
// Author: Lai Chee Kit
// Module 4: one in-app notification for one user about one ticket

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    // Named ticket_notifications so it never clashes with Laravel's own "notifications" table
    protected $table = 'ticket_notifications';

    protected $fillable = ['user_id', 'ticket_id', 'type', 'message'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
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
        return $this->read_at === null;
    }
}
