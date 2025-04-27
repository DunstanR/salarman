<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use MongoDB\Client;

echo "Testing MongoDB Atlas Connection...\n";

try {
    // Get MongoDB URI from environment
    $mongoUri = env('MONGODB_URI');
    $mongoDb = env('MONGODB_DATABASE');
    
    echo "MongoDB URI: " . $mongoUri . "\n";
    echo "MongoDB Database: " . $mongoDb . "\n\n";
    
    // Try to connect to MongoDB Atlas
    $client = new Client($mongoUri, [
        'connectTimeoutMS' => 5000,
        'serverSelectionTimeoutMS' => 5000,
    ]);
    
    // List all databases to test connection
    $databases = $client->listDatabases();
    echo "Successfully connected to MongoDB Atlas!\n";
    echo "Available databases:\n";
    foreach ($databases as $db) {
        echo "- " . $db->getName() . "\n";
    }
} catch (Exception $e) {
    echo "Error connecting to MongoDB Atlas:\n";
    echo $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 