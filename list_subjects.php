<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use MongoDB\Client;

try {
    echo "Connecting to MongoDB...\n";
    $client = new Client(env('MONGODB_URI'));
    $database = $client->selectDatabase(env('MONGODB_DATABASE'));
    $collection = $database->academic_subjects;

    echo "\nListing all academic subjects:\n";
    $cursor = $collection->find([]);

    foreach ($cursor as $subject) {
        echo "\nSubject ID: " . $subject->_id . "\n";
        echo "Name: " . ($subject->name ?? 'Not set') . "\n";
        echo "Code: " . ($subject->code ?? 'Not set') . "\n";
        echo "Description: " . ($subject->description ?? 'Not set') . "\n";
        echo "----------------------------------------\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 