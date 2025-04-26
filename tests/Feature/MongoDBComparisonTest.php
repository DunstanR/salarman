<?php

namespace Tests\Feature;

use Tests\TestCase;
use MongoDB\Client;
use MongoDB\Driver\Exception\ConnectionException;

class MongoDBComparisonTest extends TestCase
{
    public function test_compare_employees_and_users()
    {
        try {
            $client = new Client(env('MONGODB_URI'), [
                'ssl' => true,
                'retryWrites' => true,
                'w' => 'majority',
                'readPreference' => 'primary',
            ]);
            
            $database = $client->selectDatabase(env('MONGODB_DATABASE'));
            
            // Get collections
            $employeesCollection = $database->selectCollection('employees');
            $usersCollection = $database->selectCollection('users');
            
            // Get sample documents
            $employee = $employeesCollection->findOne();
            $user = $usersCollection->findOne();
            
            if (!$employee || !$user) {
                $this->fail('Could not retrieve sample documents from both collections');
                return;
            }
            
            // Convert to arrays for easier comparison
            $employeeArray = json_decode(json_encode($employee), true);
            $userArray = json_decode(json_encode($user), true);
            
            // Create output string
            $output = "";
            
            // Output document details
            $output .= "\n=== EMPLOYEE DOCUMENT ===\n";
            $output .= $this->getDocumentDetails($employeeArray);
            
            $output .= "\n=== USER DOCUMENT ===\n";
            $output .= $this->getDocumentDetails($userArray);
            
            // Compare fields
            $output .= "\n=== FIELD COMPARISON ===\n";
            $output .= $this->getFieldComparison($employeeArray, $userArray);
            
            // Check for potential relationships
            $output .= "\n=== POTENTIAL RELATIONSHIPS ===\n";
            $output .= $this->getRelationships($employeeArray, $userArray);
            
            // Write to file
            file_put_contents(storage_path('app/mongodb_comparison.txt'), $output);
            
            // Also output to console
            echo $output;
            
            $this->assertTrue(true, 'Successfully compared employee and user documents');
        } catch (ConnectionException $e) {
            $this->fail('MongoDB Atlas connection failed: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->fail('Unexpected error: ' . $e->getMessage());
        }
    }
    
    private function getDocumentDetails($document)
    {
        $output = "";
        foreach ($document as $key => $value) {
            if (is_array($value)) {
                $output .= "$key: " . json_encode($value) . "\n";
            } else {
                $output .= "$key: $value\n";
            }
        }
        return $output;
    }
    
    private function getFieldComparison($employee, $user)
    {
        $output = "";
        $employeeFields = array_keys($employee);
        $userFields = array_keys($user);
        
        $output .= "Fields in employee but not in user: " . implode(', ', array_diff($employeeFields, $userFields)) . "\n";
        $output .= "Fields in user but not in employee: " . implode(', ', array_diff($userFields, $employeeFields)) . "\n";
        
        $commonFields = array_intersect($employeeFields, $userFields);
        $output .= "Common fields: " . implode(', ', $commonFields) . "\n";
        
        // Check for field type differences
        $output .= "\nField type differences:\n";
        foreach ($commonFields as $field) {
            $employeeType = gettype($employee[$field]);
            $userType = gettype($user[$field]);
            
            if ($employeeType !== $userType) {
                $output .= "$field: employee($employeeType) vs user($userType)\n";
            }
        }
        
        return $output;
    }
    
    private function getRelationships($employee, $user)
    {
        $output = "";
        // Check for potential relationships based on common fields
        $potentialRelationships = [];
        
        // Check email
        if (isset($employee['email']) && isset($user['email']) && $employee['email'] === $user['email']) {
            $potentialRelationships[] = "Email match: {$employee['email']}";
        }
        
        // Check name fields
        if (isset($employee['firstname']) && isset($user['firstname']) && 
            isset($employee['lastname']) && isset($user['lastname']) &&
            $employee['firstname'] === $user['firstname'] && 
            $employee['lastname'] === $user['lastname']) {
            $potentialRelationships[] = "Name match: {$employee['firstname']} {$employee['lastname']}";
        }
        
        // Check mobile
        if (isset($employee['mobile']) && isset($user['mobile']) && $employee['mobile'] === $user['mobile']) {
            $potentialRelationships[] = "Mobile match: {$employee['mobile']}";
        }
        
        // Check refNo
        if (isset($employee['refNo']) && isset($user['refNo']) && $employee['refNo'] === $user['refNo']) {
            $potentialRelationships[] = "RefNo match: {$employee['refNo']}";
        }
        
        if (empty($potentialRelationships)) {
            $output .= "No direct field matches found between the sample documents.\n";
        } else {
            foreach ($potentialRelationships as $relationship) {
                $output .= "$relationship\n";
            }
        }
        
        return $output;
    }
} 