<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use MongoDB\Client;
use MongoDB\BSON\ObjectId;

try {
    echo "Connecting to MongoDB Atlas...\n";
    $client = new Client(env('MONGODB_URI'));
    $database = $client->selectDatabase(env('MONGODB_DATABASE'));
    
    $subjectId = '680d99a2692d59cc2e0b9fb9';
    
    echo "\nChecking academic_subjects collection for ID: " . $subjectId . "\n";
    $collection = $database->academic_subjects;
    
    // Try to find by ID
    $subject = $collection->findOne(['_id' => new ObjectId($subjectId)]);
    
    if ($subject) {
        echo "\nFound subject:\n";
        print_r($subject);
    } else {
        echo "\nSubject not found by ID\n";
        
        // Try to find Physics by code
        echo "\nTrying to find Physics by code:\n";
        $physicsSubject = $collection->findOne(['code' => 'PHY']);
        if ($physicsSubject) {
            echo "Found Physics subject:\n";
            print_r($physicsSubject);
        } else {
            echo "Physics subject not found by code\n";
            
            // List all subjects
            echo "\nAll subjects in the collection:\n";
            $cursor = $collection->find([]);
            foreach ($cursor as $s) {
                echo "\nSubject:\n";
                print_r($s);
                echo "----------------------------------------\n";
            }
        }
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 