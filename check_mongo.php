<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use MongoDB\Client;
use MongoDB\BSON\ObjectId;

try {
    echo "Getting MongoDB connection details...\n";
    $mongoUrl = config('database.connections.mongodb.dsn');
    $mongoDb = config('database.connections.mongodb.database');
    
    if (!$mongoUrl || !$mongoDb) {
        throw new Exception("MongoDB connection details not found in configuration");
    }
    
    echo "Connecting to MongoDB Atlas...\n";
    echo "Database name: " . $mongoDb . "\n";
    
    $client = new Client($mongoUrl);
    $database = $client->selectDatabase($mongoDb);
    
    echo "Connected successfully to Atlas\n\n";
    
    // First, let's get the user details
    $usersCollection = $database->users;
    $userId = "680c618cbf1412be9ad684b7";
    $user = $usersCollection->findOne(['_id' => new ObjectId($userId)]);
    
    if ($user) {
        echo "User Details:\n";
        echo "ID: " . $user->_id . "\n";
        echo "Name: " . ($user->firstname ?? 'Not set') . " " . ($user->lastname ?? '') . "\n";
        echo "Email: " . ($user->email ?? 'Not set') . "\n";
        echo "----------------------------------------\n\n";
    } else {
        echo "User not found!\n";
    }
    
    // Now get the academic record
    $academicCollection = $database->employee_academic;
    $userId = "680c618cbf1412be9ad684b7";
    
    // Add service_grade field
    $result = $academicCollection->updateOne(
        ['employeeRef' => new ObjectId($userId)],
        ['$set' => ['service_grade' => 'GRADE_1']]
    );
    
    if ($result->getModifiedCount() > 0) {
        echo "Successfully added service_grade field with value GRADE_1\n\n";
    } else {
        echo "No changes were made (record might not exist)\n\n";
    }
    
    // Verify the updated record
    $academic = $academicCollection->findOne(['employeeRef' => new ObjectId($userId)]);
    
    if ($academic) {
        echo "Updated Academic Record:\n";
        echo "Document ID: " . $academic->_id . "\n";
        echo "Employee Ref: " . $academic->employeeRef . "\n";
        echo "Education: " . ($academic->education ?? 'Not set') . "\n";
        echo "Service Grade: " . ($academic->service_grade ?? 'Not set') . "\n";
        
        // Get and display primary subject details
        if (isset($academic->primary_subject)) {
            $subjectsCollection = $database->academic_subjects;
            $primarySubject = $subjectsCollection->findOne(['_id' => new ObjectId($academic->primary_subject)]);
            echo "Primary Subject: " . ($primarySubject->name ?? 'Unknown') . " (ID: " . $academic->primary_subject . ")\n";
        } else {
            echo "Primary Subject: Not set\n";
        }
        
        echo "Secondary Subjects: " . (isset($academic->secondary_subjects) && !empty($academic->secondary_subjects) ? json_encode($academic->secondary_subjects) : 'None') . "\n";
        
        echo "\nFields that could be updated:\n";
        if (!isset($primarySubject) || !$primarySubject) {
            echo "- Primary Subject ID exists but subject not found in academic_subjects collection\n";
        }
    } else {
        echo "Academic record not found!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 