<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Carbon\Carbon;
use MongoDB\BSON\ObjectId;
use Illuminate\Support\Facades\DB;

class LeaveApplication extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'leave_applications';

    protected $fillable = [
        'user_id',
        'leave_type',
        'start_date',
        'end_date',
        'duration',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'document_url',
        'document_type',
        'document_number',
        'document_date',
        'document_issuer',
        'document_expiry_date',
        'document_verified',
        'document_verified_by',
        'document_verified_at',
        'document_verification_notes',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'user_id' => 'string',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'duration' => 'string',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'document_date' => 'datetime',
        'document_expiry_date' => 'datetime',
        'document_verified' => 'boolean',
        'document_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Set the user_id attribute.
     */
    public function setUserIdAttribute($value)
    {
        $this->attributes['user_id'] = new ObjectId($value);
    }

    /**
     * Get the user who applied for the leave.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', '_id');
    }

    /**
     * Get the leave type.
     */
    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type', '_id');
    }

    /**
     * Get the user who approved the leave.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by', '_id');
    }

    /**
     * Get the user who rejected the leave.
     */
    public function rejector()
    {
        return $this->belongsTo(User::class, 'rejected_by', '_id');
    }

    /**
     * Get the user who verified the document.
     */
    public function documentVerifier()
    {
        return $this->belongsTo(User::class, 'document_verified_by', '_id');
    }

    /**
     * Check if the application is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the application is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the application is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if the application requires document verification.
     */
    public function requiresDocumentVerification(): bool
    {
        return $this->leaveType->requires_document && !$this->document_verified;
    }

    /**
     * Check if the application can be approved.
     */
    public function canBeApproved(): bool
    {
        if ($this->isApproved() || $this->isRejected()) {
            return false;
        }

        if ($this->requiresDocumentVerification()) {
            return false;
        }

        return true;
    }

    /**
     * Check if the application can be rejected.
     */
    public function canBeRejected(): bool
    {
        return $this->isPending();
    }

    /**
     * Check if the application can be verified.
     */
    public function canBeVerified(): bool
    {
        return $this->requiresDocumentVerification();
    }

    /**
     * Get the status color for display.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'secondary'
        };
    }
} 