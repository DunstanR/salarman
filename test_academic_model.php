<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use MongoDB\Client;
use MongoDB\BSON\ObjectId;

try {
    echo "Testing MongoDB connection...\n";
    $client = new Client(env('MONGODB_URI'), [
        'connectTimeoutMS' => 5000,
        'serverSelectionTimeoutMS' => 5000,
        'ssl' => true,
    ]);
    
    $database = $client->selectDatabase(env('MONGODB_DATABASE'));
    $collection = $database->employee_academic;

    echo "Searching for academic record...\n";
    $academic = $collection->findOne(['_id' => new ObjectId('6802041b8ab9503aa4a8b25a')]);

    if (!$academic) {
        echo "Academic record not found!\n";
        exit(1);
    }

    echo "\nAcademic Record Details:\n";
    echo "Education: " . $academic->education . "\n";
    echo "Primary Subject ID: " . $academic->primary_subject . "\n";
    echo "Section Assigned: " . $academic->section_assigned . "\n\n";

    echo "Previous Appointment Details:\n";
    if (isset($academic->previous_appointments[0][0])) {
        $appointment = $academic->previous_appointments[0][0];
        echo "School: " . ($appointment->school ?? 'Not specified') . "\n";
        echo "Primary Subject: " . ($appointment->primary_subject ?? 'Not specified') . "\n";
        
        if (isset($appointment->start_date) && $appointment->start_date instanceof \MongoDB\BSON\UTCDateTime) {
            echo "Start Date: " . $appointment->start_date->toDateTime()->format('Y-m-d') . "\n";
        } else {
            echo "Start Date: Not specified\n";
        }
        
        if (isset($appointment->end_date) && $appointment->end_date instanceof \MongoDB\BSON\UTCDateTime) {
            echo "End Date: " . $appointment->end_date->toDateTime()->format('Y-m-d') . "\n";
        } else {
            echo "End Date: Not specified\n";
        }
    } else {
        echo "No previous appointments found.\n";
    }

    // Also show the raw data for verification
    echo "\nRaw previous_appointments data:\n";
    print_r($academic->previous_appointments);

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit(1);
} 