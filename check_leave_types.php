<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\LeaveType;

try {
    echo "Checking leave types in MongoDB...\n";
    
    $leaveTypes = LeaveType::all();
    
    if ($leaveTypes->isEmpty()) {
        echo "No leave types found in the database.\n";
        echo "Running LeaveTypeSeeder...\n";
        
        $seeder = new \Database\Seeders\LeaveTypeSeeder();
        $seeder->run();
        
        echo "LeaveTypeSeeder completed. Checking leave types again...\n";
        $leaveTypes = LeaveType::all();
    }
    
    echo "\nFound " . $leaveTypes->count() . " leave types:\n";
    foreach ($leaveTypes as $type) {
        echo "\nType: {$type->name}\n";
        echo "Code: {$type->code}\n";
        echo "Category: {$type->category}\n";
        echo "Days per year: {$type->days_per_year}\n";
        echo "----------------------------------------\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
} 