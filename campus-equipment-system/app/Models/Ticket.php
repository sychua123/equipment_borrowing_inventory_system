<?php
// Author: Lai Chee Kit
// Module 4: Support Ticket. Uses the template's "tickets" table (+ category and closed_at from
// 2026_11_20_000001). Status and priority are the template's lowercase enum values.

namespace App\Models;

use App\States\ClosedState;
use App\States\InProgressState;
use App\States\OpenState;
use App\States\ResolvedState;
use App\States\TicketState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    // The allowed values, kept in one place so controllers, requests and views share them
    public const CATEGORIES = ['Fault', 'Usage Problem', 'General Feedback'];
    public const PRIORITIES = ['low', 'medium', 'high'];

    // State Pattern: each stored status value is handled by one state class
    public const STATES = [
        'open' => OpenState::class,
        'in_progress' => InProgressState::class,
        'resolved' => ResolvedState::class,
        'closed' => ClosedState::class,
    ];

    // "status" is not here on purpose: only the state classes may change it
    protected $fillable = [
        'equipment_id',
        'category',
        'priority',
        'subject',
        'description',
    ];

    // A new ticket is always open
    protected $attributes = [
        'status' => 'open',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    // Shows a stored value such as "in_progress" as "In Progress" (for screens and messages)
    public static function label(?string $value): string
    {
        return ucwords(str_replace('_', ' ', (string) $value));
    }

    // ---------- Relationships ----------

    // The user who submitted the ticket
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class)->oldest();
    }

    // Photos of the ticket itself (photos of replies are not included)
    public function photos(): HasMany
    {
        return $this->hasMany(TicketAttachment::class)->whereNull('ticket_reply_id');
    }

    // Template relations, kept for the other modules
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    // ---------- State Pattern ----------

    // The state object for the current status
    public function state(): TicketState
    {
        $class = self::STATES[$this->status];

        return new $class();
    }

    // Called only by the state classes to move the ticket into a new state
    public function moveTo(TicketState $newState): void
    {
        $this->status = $newState->name();
        $newState->onEnter($this);
        $this->save();
    }

    // ---------- Query helpers ----------

    // Admin sees every ticket; Student/Staff see only their own
    public function scopeVisibleTo(Builder $query, User $user): void
    {
        if (! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }
    }

    // "Open fault" = a Fault ticket that is not Resolved or Closed yet
    public function scopeOpenFaults(Builder $query): void
    {
        $query->where('category', 'Fault')->whereIn('status', ['open', 'in_progress']);
    }
}
