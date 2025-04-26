<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Role extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'roles';

    protected $fillable = [
        'name',
        'description'
    ];

    /**
     * Get the users that belong to this role.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
} 