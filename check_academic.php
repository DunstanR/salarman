<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use MongoDB\Client;
use MongoDB\BSON\ObjectId;

$email = 'dunstanrathnayake@gmail.com';

echo "Starting check...\n";

try {
    // Test MongoDB connection first
    echo "Testing MongoDB connection...\n";
    $client = new Client(env('MONGODB_URI'), [
        'connectTimeoutMS' => 5000, // 5 seconds timeout
        'serverSelectionTimeoutMS' => 5000,
        'ssl' => true,
    ]);
    
    $database = $client->selectDatabase(env('MONGODB_DATABASE'));
    echo "MongoDB connection successful\n\n";

    // Find the user
    echo "Searching for user with email: {$email}\n";
    $user = User::where('email', $email)->first();

    if (!$user) {
        echo "User with email {$email} not found.\n";
        exit;
    }

    echo "User found:\n";
    echo "ID: {$user->_id}\n";
    echo "Name: {$user->firstname} {$user->lastname}\n\n";

    // Show all records in employee_academic collection using direct MongoDB query
    echo "All records in employee_academic collection:\n";
    $collection = $database->employee_academic;
    $cursor = $collection->find([]);
    
    $found = false;
    foreach ($cursor as $record) {
        $found = true;
        echo "\nRecord ID: " . $record->_id . "\n";
        echo "Employee Ref: " . $record->employeeRef . "\n";
        echo "Education: " . $record->education . "\n";
        echo "Primary Subject: " . $record->primary_subject . "\n";
        echo "Secondary Subjects: " . json_encode($record->secondary_subjects) . "\n";
        echo "Section Assigned: " . $record->section_assigned . "\n";
        
        if (!empty($record->previous_appointments)) {
            echo "Previous Appointments:\n";
            foreach ($record->previous_appointments as $appointment) {
                foreach ($appointment as $details) {
                    echo "  School: " . $details->school . "\n";
                    echo "  Primary Subject: " . $details->primary_subject . "\n";
                    if (isset($details->start_date)) {
                        echo "  Start Date: Not specified (timestamp shows 0)\n";
                    }
                    if (isset($details->end_date)) {
                        echo "  End Date: Not specified (timestamp shows 0)\n";
                    }
                }
            }
        }
        echo "----------------------------------------\n";
    }

    if (!$found) {
        echo "No records found in employee_academic collection.\n";
    }

    // Check for specific user's academic records
    echo "\nChecking for academic records for user ID: {$user->_id}\n";
    $userRecord = $collection->findOne(['employeeRef' => new ObjectId($user->_id)]);

    if ($userRecord) {
        echo "Academic details found:\n";
        echo "Education: " . $userRecord->education . "\n";
        echo "Primary Subject: " . $userRecord->primary_subject . "\n";
        echo "Secondary Subjects: " . json_encode($userRecord->secondary_subjects) . "\n";
        echo "Section Assigned: " . $userRecord->section_assigned . "\n";
        
        if (!empty($userRecord->previous_appointments)) {
            echo "Previous Appointments:\n";
            foreach ($userRecord->previous_appointments as $appointment) {
                foreach ($appointment as $details) {
                    echo "  School: " . $details->school . "\n";
                    echo "  Primary Subject: " . $details->primary_subject . "\n";
                    if (isset($details->start_date)) {
                        echo "  Start Date: Not specified (timestamp shows 0)\n";
                    }
                    if (isset($details->end_date)) {
                        echo "  End Date: Not specified (timestamp shows 0)\n";
                    }
                }
            }
        }
    } else {
        echo "No academic details found for this user.\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit(1);
} 