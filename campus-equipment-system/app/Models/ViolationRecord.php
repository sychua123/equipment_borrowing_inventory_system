<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViolationRecord extends Model
{

    protected $table = 'violation_records';

    protected $fillable = [
        'user_id',
        'borrowing_item_id',
        'violation_type',
        'description',
        'violation_date',
        'status',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'violation_date' => 'datetime',
            'resolved_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function borrowingItem()
    {
        return $this->belongsTo(BorrowingItem::class, 'borrowing_item_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
