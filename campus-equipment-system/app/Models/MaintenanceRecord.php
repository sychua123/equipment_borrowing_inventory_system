<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceRecord extends Model
{

    protected $table = 'maintenance_records';

    protected $fillable = [
        'equipment_id',
        'damage_report_id',
        'maintenance_schedule_id',
        'requested_by',
        'assigned_to',
        'maintenance_type',
        'maintenance_description',
        'scheduled_date',
        'start_date',
        'completion_date',
        'status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'datetime',
            'start_date' => 'datetime',
            'completion_date' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to', 'id');
    }

    public function damageReport()
    {
        return $this->belongsTo(DamageReport::class, 'damage_report_id', 'id');
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id', 'id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by', 'id');
    }

    public function maintenanceSchedule()
    {
        return $this->belongsTo(MaintenanceSchedule::class, 'maintenance_schedule_id', 'id');
    }

    public function repairRecords()
    {
        return $this->hasMany(RepairRecord::class, 'maintenance_record_id', 'id');
    }

}
