<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'student_staff_id',
        'phone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // Borrowing relationships
    public function submittedBorrowingRequests()
    {
        return $this->hasMany(BorrowingRequest::class, 'user_id');
    }

    public function approvedBorrowingRequests()
    {
        return $this->hasMany(BorrowingRequest::class, 'approved_by');
    }

    public function issuedBorrowingItems()
    {
        return $this->hasMany(BorrowingItem::class, 'issued_by');
    }

    public function receivedBorrowingItems()
    {
        return $this->hasMany(BorrowingItem::class, 'received_by');
    }

    // Notification relationship
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    // Damage and violation relationships
    public function reportedDamageReports()
    {
        return $this->hasMany(DamageReport::class, 'reported_by');
    }

    public function assessedDamageReports()
    {
        return $this->hasMany(DamageReport::class, 'assessed_by');
    }

    public function violationRecords()
    {
        return $this->hasMany(ViolationRecord::class, 'user_id');
    }

    // Inventory relationship
    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class, 'moved_by');
    }

    // Maintenance relationships
    public function requestedMaintenanceRecords()
    {
        return $this->hasMany(MaintenanceRecord::class, 'requested_by');
    }

    public function assignedMaintenanceRecords()
    {
        return $this->hasMany(MaintenanceRecord::class, 'assigned_to');
    }

    public function maintenanceSchedules()
    {
        return $this->hasMany(MaintenanceSchedule::class, 'created_by');
    }

    public function repairRecords()
    {
        return $this->hasMany(RepairRecord::class, 'performed_by');
    }

    // Feedback relationship
    public function feedback()
    {
        return $this->hasMany(Feedback::class, 'user_id');
    }

    // Support ticket relationships
    public function createdTickets()
    {
        return $this->hasMany(Ticket::class, 'user_id');
    }

    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    public function ticketReplies()
    {
        return $this->hasMany(TicketReply::class, 'user_id');
    }

    public function ticketAttachments()
    {
        return $this->hasMany(TicketAttachment::class, 'uploaded_by');
    }

    // Equipment status history
    public function equipmentStatusHistories()
    {
        return $this->hasMany(EquipmentStatusHistory::class, 'changed_by');
    }
}
