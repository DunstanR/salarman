<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class LeaveType extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'leave_types';

    protected $fillable = [
        'name',
        'code',
        'category',
        'days_per_year',
        'rules',
        'requires_approval',
        'requires_document',
        'document_type',
        'gender_restriction',
        'consecutive_days_limit',
        'minimum_notice_days',
        'can_be_applied_same_day',
        'description'
    ];

    protected $casts = [
        'days_per_year' => 'integer',
        'requires_approval' => 'boolean',
        'requires_document' => 'boolean',
        'consecutive_days_limit' => 'integer',
        'minimum_notice_days' => 'integer',
        'can_be_applied_same_day' => 'boolean',
        'rules' => 'array'
    ];

    /**
     * Get the leave applications for this leave type.
     */
    public function leaveApplications()
    {
        return $this->hasMany(LeaveApplication::class, 'leave_type', '_id');
    }

    /**
     * Check if the leave type is available for the given user.
     */
    public function isAvailableFor(User $user): bool
    {
        // Check gender restriction
        if ($this->gender_restriction) {
            $restriction = strtolower($this->gender_restriction) === 'female' ? 'f' : 'm';
            if ($user->gender !== $restriction) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the leave balance for a user for this leave type.
     */
    public function getBalanceFor(User $user): int
    {
        $currentYear = now()->year;
        $usedDays = $user->leaveApplications()
            ->where('leave_type', $this->_id)
            ->whereYear('start_date', $currentYear)
            ->where('status', 'approved')
            ->sum('duration');

        return $this->days_per_year - $usedDays;
    }
} 