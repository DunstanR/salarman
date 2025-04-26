<?php

namespace Tests\Feature;

use Tests\TestCase;
use MongoDB\Client;
use MongoDB\Driver\Exception\ConnectionException;

class MongoDBCollectionsTest extends TestCase
{
    public function test_list_collections_and_structure()
    {
        try {
            $client = new Client(env('MONGODB_URI'), [
                'ssl' => true,
                'retryWrites' => true,
                'w' => 'majority',
                'readPreference' => 'primary',
            ]);
            
            $database = $client->selectDatabase(env('MONGODB_DATABASE'));
            
            // List all collections
            $collections = $database->listCollections();
            $collectionNames = [];
            foreach ($collections as $collection) {
                $collectionNames[] = $collection->getName();
            }
            
            // Output collection names
            echo "\nCollections in database '" . env('MONGODB_DATABASE') . "':\n";
            foreach ($collectionNames as $name) {
                echo "- $name\n";
            }
            
            // For each collection, get a sample document to understand structure
            echo "\nCollection structures:\n";
            foreach ($collectionNames as $name) {
                $collection = $database->selectCollection($name);
                $sample = $collection->findOne();
                
                if ($sample) {
                    echo "\nCollection: $name\n";
                    echo "Sample document structure:\n";
                    $this->printDocumentStructure($sample);
                } else {
                    echo "\nCollection: $name (empty)\n";
                }
            }
            
            $this->assertTrue(true, 'Successfully retrieved collection structures');
        } catch (ConnectionException $e) {
            $this->fail('MongoDB Atlas connection failed: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->fail('Unexpected error: ' . $e->getMessage());
        }
    }
    
    private function printDocumentStructure($document, $indent = 0)
    {
        $indentStr = str_repeat('  ', $indent);
        
        foreach ($document as $key => $value) {
            if (is_object($value) || is_array($value)) {
                echo "$indentStr$key: " . gettype($value) . "\n";
                $this->printDocumentStructure($value, $indent + 1);
            } else {
                echo "$indentStr$key: " . gettype($value) . "\n";
            }
        }
    }
} 