<?php
// Author: Lai Chee Kit
// Module 4: Support Ticket

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    // The allowed values, kept in one place so the controller and views share them
    public const CATEGORIES = ['Fault', 'Usage Problem', 'General Feedback'];
    public const PRIORITIES = ['Low', 'Medium', 'High'];

    // "status" is not here on purpose: a new ticket is always Open (database default)
    protected $fillable = [
        'equipment_id',
        'category',
        'priority',
        'title',
        'description',
    ];

    // A new ticket is Open even before it is read back from the database
    protected $attributes = [
        'status' => 'Open',
    ];

    // A ticket belongs to the user who submitted it
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
