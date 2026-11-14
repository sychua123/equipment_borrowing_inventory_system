<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceSchedule extends Model
{

    protected $table = 'maintenance_schedules';

    protected $fillable = [
        'equipment_id',
        'created_by',
        'schedule_name',
        'description',
        'interval_months',
        'last_maintenance_date',
        'next_maintenance_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'last_maintenance_date' => 'date',
            'next_maintenance_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id', 'id');
    }

    public function maintenanceRecords()
    {
        return $this->hasMany(MaintenanceRecord::class, 'maintenance_schedule_id', 'id');
    }

}
