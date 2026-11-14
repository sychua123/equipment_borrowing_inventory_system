<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{

    protected $table = 'inventory_movements';

    protected $fillable = [
        'equipment_id',
        'from_location_id',
        'to_location_id',
        'moved_by',
        'movement_date',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'movement_date' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id', 'id');
    }

    public function fromLocation()
    {
        return $this->belongsTo(Location::class, 'from_location_id', 'id');
    }

    public function movedBy()
    {
        return $this->belongsTo(User::class, 'moved_by', 'id');
    }

    public function toLocation()
    {
        return $this->belongsTo(Location::class, 'to_location_id', 'id');
    }

}
