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
    
    // Clear all subjects
    echo "Clearing all subjects...\n";
    $database->academic_subjects->deleteMany([]);
    
    // Run the seeder
    echo "Running academic subject seeder...\n";
    Artisan::call('db:seed', ['--class' => 'AcademicSubjectSeeder']);
    
    // Get the Physics subject ID
    $physicsSubject = $database->academic_subjects->findOne(['code' => 'PHY']);
    if (!$physicsSubject) {
        throw new Exception("Physics subject not found!");
    }
    
    echo "Found Physics subject with ID: " . $physicsSubject->_id . "\n";
    
    // Update the academic record
    $result = $database->employee_academic->updateOne(
        ['_id' => new ObjectId('6802041b8ab9503aa4a8b25a')],
        ['$set' => ['primary_subject' => $physicsSubject->_id]]
    );
    
    if ($result->getModifiedCount() > 0) {
        echo "Successfully updated academic record!\n";
    } else {
        echo "No changes made to academic record.\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 