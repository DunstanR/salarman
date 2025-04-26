<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class VerificationCode extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'verification_codes';

    protected $fillable = [
        'email',
        'code',
        'expires_at',
        'used'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean'
    ];

    public function isValid()
    {
        return !$this->used && $this->expires_at->isFuture();
    }
} 