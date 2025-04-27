<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeaveType;
use MongoDB\Laravel\Eloquent\Model;

class LeaveTypeSeeder extends Seeder
{
    public function run()
    {
        // First, clear existing leave types using MongoDB's deleteMany
        LeaveType::raw()->deleteMany([]);

        $leaveTypes = [
            [
                'name' => 'Sick Leave',
                'code' => 'SICK',
                'category' => 'medical',
                'days_per_year' => 12,
                'requires_approval' => true,
                'requires_document' => true,
                'document_type' => 'medical_certificate',
                'gender_restriction' => null,
                'consecutive_days_limit' => 30,
                'minimum_notice_days' => 0,
                'can_be_applied_same_day' => true,
                'description' => 'Leave for medical reasons with doctor\'s certificate'
            ],
            [
                'name' => 'Casual Leave',
                'code' => 'CASUAL',
                'category' => 'personal',
                'days_per_year' => 12,
                'requires_approval' => true,
                'requires_document' => false,
                'document_type' => null,
                'gender_restriction' => null,
                'consecutive_days_limit' => 3,
                'minimum_notice_days' => 1,
                'can_be_applied_same_day' => false,
                'description' => 'Leave for personal matters'
            ],
            [
                'name' => 'Earned Leave',
                'code' => 'EARNED',
                'category' => 'earned',
                'days_per_year' => 30,
                'requires_approval' => true,
                'requires_document' => false,
                'document_type' => null,
                'gender_restriction' => null,
                'consecutive_days_limit' => 30,
                'minimum_notice_days' => 7,
                'can_be_applied_same_day' => false,
                'description' => 'Accumulated leave based on service'
            ],
            [
                'name' => 'Maternity Leave',
                'code' => 'MLA',
                'category' => 'medical',
                'days_per_year' => 180,
                'requires_approval' => true,
                'requires_document' => true,
                'document_type' => 'medical_certificate',
                'gender_restriction' => 'female',
                'consecutive_days_limit' => 180,
                'minimum_notice_days' => 30,
                'can_be_applied_same_day' => false,
                'description' => 'Leave for female employees during pregnancy and childbirth'
            ],
            [
                'name' => 'Maternity Leave (Adoption)',
                'code' => 'MLA_ADOPTION',
                'category' => 'medical',
                'days_per_year' => 90,
                'requires_approval' => true,
                'requires_document' => true,
                'document_type' => 'adoption_certificate',
                'gender_restriction' => 'female',
                'consecutive_days_limit' => 90,
                'minimum_notice_days' => 30,
                'can_be_applied_same_day' => false,
                'description' => 'Leave for female employees for adoption'
            ],
            [
                'name' => 'Paternity Leave',
                'code' => 'PL',
                'category' => 'medical',
                'days_per_year' => 15,
                'requires_approval' => true,
                'requires_document' => true,
                'document_type' => 'birth_certificate',
                'gender_restriction' => 'male',
                'consecutive_days_limit' => 15,
                'minimum_notice_days' => 7,
                'can_be_applied_same_day' => false,
                'description' => 'Leave for male employees on childbirth'
            ],
            [
                'name' => 'Study Leave',
                'code' => 'STL',
                'category' => 'education',
                'days_per_year' => 30,
                'requires_approval' => true,
                'requires_document' => true,
                'document_type' => 'education_certificate',
                'gender_restriction' => null,
                'consecutive_days_limit' => 30,
                'minimum_notice_days' => 30,
                'can_be_applied_same_day' => false,
                'description' => 'Leave for educational purposes'
            ]
        ];

        foreach ($leaveTypes as $leaveType) {
            LeaveType::updateOrCreate(
                ['code' => $leaveType['code']],
                $leaveType
            );
        }
    }
} 