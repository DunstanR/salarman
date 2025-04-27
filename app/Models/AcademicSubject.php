<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class AcademicSubject extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'academic_subjects';

    protected $fillable = [
        'name',
        'code',
        'description'
    ];

    /**
     * Get the employee academics that use this subject as primary subject.
     */
    public function primaryEmployeeAcademics()
    {
        return $this->hasMany(EmployeeAcademic::class, 'primary_subject', '_id');
    }

    /**
     * Get the employee academics that use this subject as secondary subject.
     */
    public function secondaryEmployeeAcademics()
    {
        return $this->hasMany(EmployeeAcademic::class, 'secondary_subject', '_id');
    }
} 