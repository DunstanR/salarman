<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\ObjectId;

class EmployeeAcademic extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'employee_academic';

    protected $fillable = [
        'employeeRef',
        'education',
        'primary_subject',
        'secondary_subjects',
        'section_assigned',
        'previous_appointments',
        'service_grade'
    ];

    protected $casts = [
        'previous_appointments' => 'array',
        'employeeRef' => 'string',
        'primary_subject' => 'string',
        'section_assigned' => 'string',
        'secondary_subjects' => 'array'
    ];

    /**
     * Get the user that owns the academic record.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'employeeRef', '_id');
    }

    /**
     * Get the primary subject for this academic record.
     */
    public function primarySubject()
    {
        return $this->belongsTo(AcademicSubject::class, 'primary_subject', '_id');
    }

    /**
     * Get the secondary subjects for this academic record.
     */
    public function secondarySubjects()
    {
        return $this->belongsToMany(AcademicSubject::class, null, '_id', '_id', 'secondary_subjects', '_id');
    }

    /**
     * Get the section assigned to this employee.
     */
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_assigned', '_id');
    }

    /**
     * Get the formatted start date of the first previous appointment.
     */
    public function getFirstAppointmentStartDateAttribute()
    {
        if (isset($this->previous_appointments[0][0]['start_date']) && 
            $this->previous_appointments[0][0]['start_date'] instanceof UTCDateTime) {
            return $this->previous_appointments[0][0]['start_date']->toDateTime()->format('Y-m-d');
        }
        return null;
    }

    /**
     * Get the formatted end date of the first previous appointment.
     */
    public function getFirstAppointmentEndDateAttribute()
    {
        if (isset($this->previous_appointments[0][0]['end_date']) && 
            $this->previous_appointments[0][0]['end_date'] instanceof UTCDateTime) {
            return $this->previous_appointments[0][0]['end_date']->toDateTime()->format('Y-m-d');
        }
        return null;
    }

    /**
     * Get the school of the first previous appointment.
     */
    public function getFirstAppointmentSchoolAttribute()
    {
        return $this->previous_appointments[0][0]['school'] ?? null;
    }

    /**
     * Get the primary subject of the first previous appointment.
     */
    public function getFirstAppointmentPrimarySubjectAttribute()
    {
        return $this->previous_appointments[0][0]['primary_subject'] ?? null;
    }
} 