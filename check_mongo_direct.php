<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use MongoDB\Client;
use MongoDB\BSON\ObjectId;

try {
    echo "Connecting to MongoDB...\n";
    $client = new Client(env('MONGODB_URI'));
    $database = $client->selectDatabase(env('MONGODB_DATABASE'));
    
    // Get user ID
    $userId = '680c618cbf1412be9ad684b7';
    
    echo "\nChecking employee_academic collection for user ID: " . $userId . "\n";
    $collection = $database->employee_academic;
    
    // Try to find by employeeRef
    $record = $collection->findOne(['employeeRef' => new ObjectId($userId)]);
    
    if ($record) {
        echo "\nFound record by employeeRef:\n";
        print_r($record);
    } else {
        echo "\nNo record found by employeeRef\n";
    }
    
    // Show all records in the collection
    echo "\nAll records in employee_academic collection:\n";
    $cursor = $collection->find([]);
    
    foreach ($cursor as $doc) {
        echo "\nDocument:\n";
        print_r($doc);
        echo "----------------------------------------\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 