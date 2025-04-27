<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicSubject;

class AcademicSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            [
                'name' => 'Mathematics',
                'code' => 'MATH',
                'description' => 'Mathematics and related subjects'
            ],
            [
                'name' => 'Science',
                'code' => 'SCI',
                'description' => 'General Science and related subjects'
            ],
            [
                'name' => 'English',
                'code' => 'ENG',
                'description' => 'English Language and Literature'
            ],
            [
                'name' => 'History',
                'code' => 'HIST',
                'description' => 'History and Social Studies'
            ],
            [
                'name' => 'Geography',
                'code' => 'GEO',
                'description' => 'Geography and Environmental Studies'
            ],
            [
                'name' => 'Physics',
                'code' => 'PHY',
                'description' => 'Physics and related subjects'
            ],
            [
                'name' => 'Chemistry',
                'code' => 'CHEM',
                'description' => 'Chemistry and related subjects'
            ],
            [
                'name' => 'Biology',
                'code' => 'BIO',
                'description' => 'Biology and Life Sciences'
            ],
            [
                'name' => 'Computer Science',
                'code' => 'CS',
                'description' => 'Computer Science and Information Technology'
            ],
            [
                'name' => 'Physical Education',
                'code' => 'PE',
                'description' => 'Physical Education and Sports'
            ],
            [
                'name' => 'Art',
                'code' => 'ART',
                'description' => 'Art and Design'
            ],
            [
                'name' => 'Music',
                'code' => 'MUS',
                'description' => 'Music and Performing Arts'
            ],
            [
                'name' => 'Languages',
                'code' => 'LANG',
                'description' => 'Foreign Languages'
            ],
            [
                'name' => 'Economics',
                'code' => 'ECON',
                'description' => 'Economics and Business Studies'
            ],
            [
                'name' => 'Psychology',
                'code' => 'PSY',
                'description' => 'Psychology and Behavioral Sciences'
            ]
        ];

        foreach ($subjects as $subject) {
            AcademicSubject::create($subject);
        }
    }
} 