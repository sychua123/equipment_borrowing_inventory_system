<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fine extends Model
{

    protected $table = 'fines';

    protected $fillable = [
        'borrowing_item_id',
        'fine_type',
        'fine_description',
        'fine_amount',
        'assessment_status',
        'payment_status',
        'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'fine_amount' => 'decimal:2',
        ];
    }

    public function borrowingItem()
    {
        return $this->belongsTo(BorrowingItem::class, 'borrowing_item_id', 'id');
    }

    public function finePayments()
    {
        return $this->hasMany(FinePayment::class, 'fine_id', 'id');
    }

}
