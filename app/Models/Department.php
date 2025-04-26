<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Department extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'departments';

    protected $fillable = [
        'depName',
        'description',
        'refNo'
    ];

    /**
     * Get the users that belong to this department.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'department', '_id');
    }
} 