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
        'department'
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
}
