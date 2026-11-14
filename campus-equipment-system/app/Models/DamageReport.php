<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DamageReport extends Model
{

    protected $table = 'damage_reports';

    protected $fillable = [
        'equipment_id',
        'reported_by',
        'borrowing_item_id',
        'incident_type',
        'description',
        'evidence_url',
        'assessment_amount',
        'assessed_by',
        'assessed_at',
        'report_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'assessed_at' => 'datetime',
            'report_date' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'assessment_amount' => 'decimal:2',
        ];
    }

    public function assessedBy()
    {
        return $this->belongsTo(User::class, 'assessed_by', 'id');
    }

    public function borrowingItem()
    {
        return $this->belongsTo(BorrowingItem::class, 'borrowing_item_id', 'id');
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id', 'id');
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by', 'id');
    }

    public function maintenanceRecords()
    {
        return $this->hasMany(MaintenanceRecord::class, 'damage_report_id', 'id');
    }

}
