<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AcademicSubject;

try {
    echo "Checking academic subject...\n\n";
    
    $subjectId = '680205dd8ab9503aa4a8b25d';
    $subject = AcademicSubject::find($subjectId);
    
    if ($subject) {
        echo "Subject found!\n";
        echo "ID: " . $subject->_id . "\n";
        echo "Name: " . $subject->name . "\n";
        echo "Code: " . $subject->code . "\n";
    } else {
        echo "Subject not found with ID: " . $subjectId . "\n";
        
        echo "\nAll subjects in the database:\n";
        $allSubjects = AcademicSubject::all();
        foreach ($allSubjects as $s) {
            echo "ID: " . $s->_id . "\n";
            echo "Name: " . $s->name . "\n";
            echo "Code: " . $s->code . "\n";
            echo "------------------------\n";
        }
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 