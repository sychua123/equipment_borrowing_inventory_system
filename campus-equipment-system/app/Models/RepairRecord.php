<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepairRecord extends Model
{

    protected $table = 'repair_records';

    protected $fillable = [
        'maintenance_record_id',
        'performed_by',
        'repair_description',
        'repair_cost',
        'start_date',
        'completion_date',
        'repair_outcome',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'completion_date' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'repair_cost' => 'decimal:2',
        ];
    }

    public function maintenanceRecord()
    {
        return $this->belongsTo(MaintenanceRecord::class, 'maintenance_record_id', 'id');
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by', 'id');
    }

}
