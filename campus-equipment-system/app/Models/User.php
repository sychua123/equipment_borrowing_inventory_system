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
            'locked_until' => 'datetime',
            'failed_login_attempts' => 'integer',
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

    // ===== Module 4 (Lai Chee Kit) =====
    // Support tickets submitted by this user (same rows as createdTickets())
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'user_id');
    }

    // Bell notifications of this user for Module 4, newest first
    public function ticketNotifications()
    {
        return $this->hasMany(Notification::class, 'user_id')->latest();
    }

    // Admin = Inventory Officer
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public const MAX_FAILED_ATTEMPTS = 5;
    public const LOCK_MINUTES = 15;

    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    public function registerFailedLogin(): void
    {
        $this->increment('failed_login_attempts');

        if ($this->failed_login_attempts >= self::MAX_FAILED_ATTEMPTS) {
            $this->locked_until = now()->addMinutes(self::LOCK_MINUTES);
            $this->save();
        }
    }

    public function clearFailedLogins(): void
    {
        $this->failed_login_attempts = 0;
        $this->locked_until = null;
        $this->save();
    }

    // Equipment status history
    public function equipmentStatusHistories()
    {
        return $this->hasMany(EquipmentStatusHistory::class, 'changed_by');
    }
}
