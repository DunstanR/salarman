<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use MongoDB\Client;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

try {
    echo "Connecting to MongoDB...\n";
    $client = new Client(env('MONGODB_URI'), [
        'connectTimeoutMS' => 5000,
        'serverSelectionTimeoutMS' => 5000,
        'ssl' => true,
    ]);
    
    $database = $client->selectDatabase(env('MONGODB_DATABASE'));
    $collection = $database->employee_academic;

    // Create DateTime objects for the start and end dates
    $startDateTime = new DateTime('2022-01-01');
    $endDateTime = new DateTime('2024-12-31');
    
    $startDate = new UTCDateTime($startDateTime->getTimestamp() * 1000);
    $endDate = new UTCDateTime($endDateTime->getTimestamp() * 1000);

    // First, get the current document
    $doc = $collection->findOne(['_id' => new ObjectId('6802041b8ab9503aa4a8b25a')]);
    
    if (!$doc) {
        echo "Document not found!\n";
        exit(1);
    }

    // Create the appointment array
    $appointment = [
        'school' => 'Kannnangara MV.',
        'primary_subject' => 'Physics',
        'start_date' => $startDate,
        'end_date' => $endDate
    ];

    // Update the document
    $result = $collection->updateOne(
        ['_id' => new ObjectId('6802041b8ab9503aa4a8b25a')],
        ['$set' => ['previous_appointments' => [[$appointment]]]]
    );

    if ($result->getModifiedCount() > 0) {
        echo "Successfully updated the dates!\n";
        echo "Start date set to: " . $startDateTime->format('Y-m-d') . "\n";
        echo "End date set to: " . $endDateTime->format('Y-m-d') . "\n";
    } else {
        echo "No documents were modified.\n";
    }

    // Verify the current state
    $updatedDoc = $collection->findOne(['_id' => new ObjectId('6802041b8ab9503aa4a8b25a')]);
    echo "\nCurrent document state:\n";
    
    if (isset($updatedDoc->previous_appointments[0][0]['start_date'])) {
        $startDate = $updatedDoc->previous_appointments[0][0]['start_date']->toDateTime()->format('Y-m-d');
        $endDate = $updatedDoc->previous_appointments[0][0]['end_date']->toDateTime()->format('Y-m-d');
        echo "Previous appointment dates:\n";
        echo "Start date: $startDate\n";
        echo "End date: $endDate\n";
    } else {
        echo "Could not find dates in the updated document.\n";
        var_export($updatedDoc);
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit(1);
} 