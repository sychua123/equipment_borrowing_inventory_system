<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{

    protected $table = 'locations';

    protected $fillable = [
        'location_name',
        'building',
        'room_number',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function equipment()
    {
        return $this->hasMany(Equipment::class, 'location_id', 'id');
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class, 'from_location_id', 'id');
    }

    public function toLocationInventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class, 'to_location_id', 'id');
    }

}
