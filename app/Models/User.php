<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use MongoDB\Laravel\Auth\User as MongoDBUser;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Support\Facades\Hash;

class User extends MongoDBUser implements AuthenticatableContract
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, AuthenticatableTrait;

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
}
