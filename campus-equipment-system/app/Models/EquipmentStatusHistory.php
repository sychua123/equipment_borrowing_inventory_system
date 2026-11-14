<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentStatusHistory extends Model
{
    protected $table = 'equipment_status_histories';

    public $timestamps = false;

    protected $fillable = [
        'equipment_id',
        'changed_by',
        'old_status',
        'new_status',
        'remarks',
        'changed_at',
    ];

    protected function casts(): array
    {
        return ['changed_at' => 'datetime'];
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id', 'id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by', 'id');
    }
}
