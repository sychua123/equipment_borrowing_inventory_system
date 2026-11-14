<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinePayment extends Model
{

    protected $table = 'fine_payments';

    protected $fillable = [
        'fine_id',
        'payment_reference',
        'stripe_payment_intent_id',
        'stripe_checkout_session_id',
        'amount',
        'currency',
        'payment_status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    public function fine()
    {
        return $this->belongsTo(Fine::class, 'fine_id', 'id');
    }

}
