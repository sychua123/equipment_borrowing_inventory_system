<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BorrowingItem extends Model
{

    protected $table = 'borrowing_items';

    protected $fillable = [
        'borrowing_request_id',
        'equipment_id',
        'issued_by',
        'received_by',
        'borrow_date',
        'expected_return_date',
        'actual_return_date',
        'return_condition',
        'status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'borrow_date' => 'datetime',
            'expected_return_date' => 'datetime',
            'actual_return_date' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id', 'id');
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by', 'id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by', 'id');
    }

    public function borrowingRequest()
    {
        return $this->belongsTo(BorrowingRequest::class, 'borrowing_request_id', 'id');
    }

    public function damageReports()
    {
        return $this->hasMany(DamageReport::class, 'borrowing_item_id', 'id');
    }

    public function fines()
    {
        return $this->hasMany(Fine::class, 'borrowing_item_id', 'id');
    }

    public function violationRecords()
    {
        return $this->hasMany(ViolationRecord::class, 'borrowing_item_id', 'id');
    }

}
