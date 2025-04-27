<?php
require __DIR__.'/vendor/autoload.php';

// MongoDB Atlas connection string
$dsn = 'mongodb+srv://easytravelappnipun:nuLV9MTVegi8F3QS@easytravelapp.jcifyj4.mongodb.net/edhirya?authSource=admin&replicaSet=atlas-yg44qu-shard-0&w=majority&readPreference=primary&appname=MongoDB%20Compass&retryWrites=true&ssl=true';
$database = 'edhirya';

// Try to load from .env if it exists
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            if (trim($key) === 'MONGODB_URI') $dsn = trim($value);
            if (trim($key) === 'DB_DATABASE') $database = trim($value);
        }
    }
}

try {
    echo "Connecting to MongoDB Atlas...\n";
    $client = new MongoDB\Client($dsn);
    $db = $client->selectDatabase($database);
    
    // Get a sample user to show how to concatenate name
    echo "\nSample User Data:\n";
    echo "================\n";
    $sampleUser = $db->users->findOne();
    if ($sampleUser) {
        echo "User ID: " . $sampleUser->_id . "\n";
        echo "First Name: " . $sampleUser->firstname . "\n";
        echo "Last Name: " . $sampleUser->lastname . "\n";
        echo "Concatenated Name: " . $sampleUser->firstname . " " . $sampleUser->lastname . "\n";
    }
    
    // Get leave types to show category mapping
    echo "\nLeave Types and Categories:\n";
    echo "=========================\n";
    $leaveTypes = $db->leave_types->find();
    foreach ($leaveTypes as $type) {
        echo "\nType: {$type->name}\n";
        echo "Code: {$type->code}\n";
        echo "Category: {$type->category}\n";
        echo "Default Duration: " . ($type->code === 'CASUAL' ? 'From Database' : 'FULL') . "\n";
        echo "----------------------------------------\n";
    }
    
    // Show a sample leave application with proper formatting
    echo "\nSample Leave Application Format:\n";
    echo "=============================\n";
    $sampleLeave = $db->leaves->findOne();
    if ($sampleLeave) {
        $formattedLeave = [
            'refNo' => 'Leave' . time(), // Generate unique reference number
            'teacherName' => $sampleUser->firstname . ' ' . $sampleUser->lastname,
            'category' => 'SICK', // This should be the selected leave type
            'designation' => 'GRADE_1', // This should come from user's designation
            'type' => 'FULL', // Default for non-casual leaves
            'fromDate' => new MongoDB\BSON\UTCDateTime(strtotime('2024-03-20') * 1000),
            'toDate' => new MongoDB\BSON\UTCDateTime(strtotime('2024-03-21') * 1000),
            'leaveDays' => 2, // This should be calculated based on fromDate and toDate
            'reason' => 'Sample reason',
            'status' => 'Pending', // Default status for new applications
            'archived' => false, // Default value
            'createdBy' => $sampleUser->_id, // ObjectId of the applicant
            'createdAt' => new MongoDB\BSON\UTCDateTime(),
            'updatedAt' => new MongoDB\BSON\UTCDateTime(),
            '__v' => 0
        ];
        
        echo "Required fields and their formats:\n";
        foreach ($formattedLeave as $field => $value) {
            echo str_pad($field, 15) . " | Type: " . gettype($value) . "\n";
            if ($value instanceof MongoDB\BSON\UTCDateTime) {
                echo str_pad("", 15) . " | Format: MongoDB\BSON\UTCDateTime\n";
            } elseif (is_string($value) || is_numeric($value)) {
                echo str_pad("", 15) . " | Value: " . $value . "\n";
            }
        }
    }
    
    // Show how to calculate leave days
    echo "\nLeave Days Calculation Example:\n";
    echo "============================\n";
    $fromDate = strtotime('2024-03-20');
    $toDate = strtotime('2024-03-21');
    $days = ceil(($toDate - $fromDate) / (60 * 60 * 24)) + 1;
    echo "From: " . date('Y-m-d', $fromDate) . "\n";
    echo "To: " . date('Y-m-d', $toDate) . "\n";
    echo "Calculated Days: " . $days . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Error type: " . get_class($e) . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 