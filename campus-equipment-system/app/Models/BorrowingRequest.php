<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BorrowingRequest extends Model
{

    protected $table = 'borrowing_requests';

    protected $fillable = [
        'user_id',
        'approved_by',
        'request_date',
        'approved_at',
        'status',
        'rejection_reason',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'request_date' => 'datetime',
            'approved_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function borrowingItems()
    {
        return $this->hasMany(BorrowingItem::class, 'borrowing_request_id', 'id');
    }

}
