<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\EmployeeAcademic;
use MongoDB\BSON\ObjectId;

class CheckAcademicDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:academic {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check academic details for a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? 'dunstanrathnayake@gmail.com';
        
        $this->info("Checking academic details for user with email: {$email}");
        
        // Find the user
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User not found with email: {$email}");
            return 1;
        }
        
        $this->info("User found:");
        $this->info("ID: {$user->_id}");
        $this->info("Name: {$user->firstname} {$user->lastname}");
        
        // Try to find academic details using the model
        $academicDetails = EmployeeAcademic::where('employeeRef', new ObjectId($user->_id))->first();
        
        if ($academicDetails) {
            $this->info("Academic details found using model:");
            $this->info("ID: {$academicDetails->_id}");
            $this->info("Education: " . ($academicDetails->education ?? 'Not set'));
            $this->info("Primary Subject ID: " . ($academicDetails->primary_subject ?? 'Not set'));
            $this->info("Secondary Subjects: " . (is_array($academicDetails->secondary_subjects) ? json_encode($academicDetails->secondary_subjects) : 'Not set'));
            $this->info("Section Assigned: " . ($academicDetails->section_assigned ?? 'Not set'));
            
            // Check relationships
            $this->info("\nChecking relationships:");
            $this->info("Primary Subject: " . ($academicDetails->primarySubject ? $academicDetails->primarySubject->name : 'Not found'));
            $this->info("Secondary Subject: " . ($academicDetails->secondarySubject ? $academicDetails->secondarySubject->name : 'Not found'));
            $this->info("Section: " . ($academicDetails->section ? $academicDetails->section->name : 'Not found'));
        } else {
            $this->error("No academic details found using model.");
        }
        
        return 0;
    }
} 