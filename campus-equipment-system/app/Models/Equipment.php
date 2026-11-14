<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{

    protected $table = 'equipment';

    protected $fillable = [
        'category_id',
        'location_id',
        'asset_tag',
        'equipment_name',
        'brand',
        'model',
        'serial_number',
        'description',
        'image_path',
        'purchase_date',
        'purchase_cost',
        'equipment_condition',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'purchase_cost' => 'decimal:2',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id', 'id');
    }

    public function borrowingItems()
    {
        return $this->hasMany(BorrowingItem::class, 'equipment_id', 'id');
    }

    public function damageReports()
    {
        return $this->hasMany(DamageReport::class, 'equipment_id', 'id');
    }

    public function feedback()
    {
        return $this->hasMany(Feedback::class, 'equipment_id', 'id');
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class, 'equipment_id', 'id');
    }

    public function maintenanceRecords()
    {
        return $this->hasMany(MaintenanceRecord::class, 'equipment_id', 'id');
    }

    public function maintenanceSchedules()
    {
        return $this->hasMany(MaintenanceSchedule::class, 'equipment_id', 'id');
    }

    public function statusHistories()
    {
        return $this->hasMany(EquipmentStatusHistory::class, 'equipment_id', 'id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'equipment_id', 'id');
    }

}
