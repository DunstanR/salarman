<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use MongoDB\Client;
use MongoDB\BSON\ObjectId;
use App\Models\User;
use App\Models\EmployeeAcademic;

try {
    echo "Debugging academic record...\n\n";
    
    $email = 'dunstanrathnayake@gmail.com';
    
    // Find the user
    $user = User::where('email', $email)->first();
    
    if (!$user) {
        echo "User not found with email: {$email}\n";
        exit;
    }
    
    echo "User found:\n";
    echo "ID: {$user->_id}\n";
    echo "Name: {$user->firstname} {$user->lastname}\n\n";
    
    // Try to find academic details using the model
    $academicDetails = EmployeeAcademic::where('employeeRef', new ObjectId($user->_id))->first();
    
    if ($academicDetails) {
        echo "Academic details found using model:\n";
        echo "Education: " . ($academicDetails->education ?? 'Not set') . "\n";
        echo "Primary Subject ID: " . ($academicDetails->primary_subject ?? 'Not set') . "\n";
        echo "Secondary Subject ID: " . ($academicDetails->secondary_subject ?? 'Not set') . "\n";
        echo "Section Assigned: " . ($academicDetails->section_assigned ?? 'Not set') . "\n";
        
        if ($academicDetails->previous_appointments) {
            echo "\nPrevious Appointments:\n";
            print_r($academicDetails->previous_appointments);
        }
    } else {
        echo "No academic details found using model.\n";
    }
    
    // Try direct MongoDB query
    $client = new Client(env('MONGODB_URI'));
    $database = $client->selectDatabase(env('MONGODB_DATABASE'));
    $collection = $database->employee_academic;
    
    $directQuery = $collection->findOne(['employeeRef' => new ObjectId($user->_id)]);
    
    if ($directQuery) {
        echo "\nAcademic details found using direct MongoDB query:\n";
        print_r($directQuery);
    } else {
        echo "\nNo academic details found using direct MongoDB query.\n";
    }
    
    // List all records in employee_academic collection
    echo "\nAll records in employee_academic collection:\n";
    $cursor = $collection->find([]);
    foreach ($cursor as $doc) {
        echo "\nDocument:\n";
        print_r($doc);
        echo "----------------------------------------\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 