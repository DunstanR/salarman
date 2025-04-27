<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Section;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            [
                'name' => 'Science Section',
                'code' => 'SCI',
                'description' => 'Science and Mathematics Section'
            ],
            [
                'name' => 'Arts Section',
                'code' => 'ART',
                'description' => 'Arts and Humanities Section'
            ],
            [
                'name' => 'Commerce Section',
                'code' => 'COM',
                'description' => 'Commerce and Business Studies Section'
            ],
            [
                'name' => 'Technology Section',
                'code' => 'TECH',
                'description' => 'Technology and ICT Section'
            ]
        ];

        foreach ($sections as $section) {
            Section::create($section);
        }
    }
} 