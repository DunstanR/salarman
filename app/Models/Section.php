<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\BSON\ObjectId;

class Section extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'sections';

    protected $fillable = [
        'name',
        'code',
        'description'
    ];

    protected $casts = [
        '_id' => 'string'
    ];

    /**
     * Get the academic records associated with this section.
     */
    public function academicRecords()
    {
        return $this->hasMany(EmployeeAcademic::class, 'section_assigned', '_id');
    }
} 