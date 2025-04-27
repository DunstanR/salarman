<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Auth\User as MongoAuthenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;

class User extends MongoAuthenticatable implements AuthorizableContract
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, Authorizable;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'password',
        'mobile',
        'address',
        'active',
        'archived',
        'refNo',
        'role',
        'imageUrl',
        'department',
        'gender'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'active' => 'boolean',
        'archived' => 'boolean',
    ];

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->firstname} {$this->lastname}";
    }

    /**
     * Check if the user is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active === true;
    }

    /**
     * Check if the user is archived.
     *
     * @return bool
     */
    public function isArchived(): bool
    {
        return $this->archived === true;
    }

    /**
     * Find a user by email.
     *
     * @param string $email
     * @return \App\Models\User|null
     */
    public static function findByEmail(string $email): ?self
    {
        return static::where('email', $email)->first();
    }

    /**
     * Set the user's password.
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value, ['rounds' => 12]);
    }

    /**
     * Verify the user's password.
     *
     * @param string $password
     * @return bool
     */
    public function verifyPassword(string $password): bool
    {
        return Hash::check($password, $this->password);
    }

    /**
     * Get the user's role.
     */
    public function getRoleAttribute()
    {
        if (!isset($this->attributes['role'])) {
            return null;
        }
        return Role::find($this->attributes['role']);
    }

    /**
     * Check if the user is a teacher.
     *
     * @return bool
     */
    public function isTeacher(): bool
    {
        $role = $this->getRoleAttribute();
        return $role && strtoupper($role->name) === 'TEACHER';
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role', '_id');
    }

    public function department()
    {
        if (!isset($this->attributes['department'])) {
            return null;
        }
        
        return Department::find($this->attributes['department']);
    }

    /**
     * Get the department name.
     *
     * @return string|null
     */
    public function getDepartmentNameAttribute(): ?string
    {
        $dept = $this->department();
        if (!$dept) {
            return null;
        }
        
        $refNo = $dept->refNo ?? '';
        return $refNo ? "{$dept->depName} ({$refNo})" : $dept->depName;
    }

    /**
     * Get the user's leave applications.
     */
    public function leaveApplications()
    {
        return $this->hasMany(LeaveApplication::class, 'user_id', '_id');
    }

    /**
     * Get the user's leave balance for a specific leave type.
     */
    public function getLeaveBalance(string $leaveTypeCode): int
    {
        $leaveType = LeaveType::where('code', $leaveTypeCode)->first();
        if (!$leaveType) {
            return 0;
        }

        return $leaveType->getBalanceFor($this);
    }

    /**
     * Get all leave balances for the user.
     */
    public function getAllLeaveBalances(): array
    {
        $balances = [];
        $leaveTypes = LeaveType::all();

        foreach ($leaveTypes as $leaveType) {
            if ($leaveType->isAvailableFor($this)) {
                $balances[$leaveType->code] = [
                    'name' => $leaveType->name,
                    'total' => $leaveType->days_per_year,
                    'used' => $leaveType->days_per_year - $leaveType->getBalanceFor($this),
                    'remaining' => $leaveType->getBalanceFor($this)
                ];
            }
        }

        return $balances;
    }

    /**
     * Check if the user can apply for a specific leave type.
     */
    public function canApplyForLeave(string $leaveTypeCode, Carbon $startDate, Carbon $endDate): bool
    {
        $leaveType = LeaveType::where('code', $leaveTypeCode)->first();
        if (!$leaveType) {
            return false;
        }

        // Check if leave type is available for user
        if (!$leaveType->isAvailableFor($this)) {
            return false;
        }

        // Check if user has sufficient balance
        if ($leaveType->getBalanceFor($this) < $startDate->diffInDays($endDate) + 1) {
            return false;
        }

        // Check consecutive days limit
        if ($leaveType->consecutive_days_limit && 
            $startDate->diffInDays($endDate) + 1 > $leaveType->consecutive_days_limit) {
            return false;
        }

        // Check minimum notice period
        if (!$leaveType->can_be_applied_same_day && 
            $startDate->diffInDays(now()) < $leaveType->minimum_notice_days) {
            return false;
        }

        // Check if there's any overlapping leave
        $hasOverlapping = $this->leaveApplications()
            ->where('status', 'approved')
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->exists();

        if ($hasOverlapping) {
            return false;
        }

        return true;
    }

    /**
     * Get the user's gender, defaulting to 'f' for teachers if not specified.
     *
     * @return string
     */
    public function getGenderAttribute(): string
    {
        if (isset($this->attributes['gender'])) {
            return $this->attributes['gender'];
        }

        return $this->isTeacher() ? 'f' : 'm';
    }

    /**
     * Set the user's gender.
     *
     * @param string|null $value
     */
    public function setGenderAttribute(?string $value)
    {
        if ($value) {
            // Convert any form of male/female input to m/f
            $lowered = strtolower($value);
            if (in_array($lowered, ['male', 'm'])) {
                $this->attributes['gender'] = 'm';
            } elseif (in_array($lowered, ['female', 'f'])) {
                $this->attributes['gender'] = 'f';
            }
        }
    }
}
