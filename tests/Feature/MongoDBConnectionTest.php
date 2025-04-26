<?php

namespace Tests\Feature;

use Tests\TestCase;
use MongoDB\Client;
use MongoDB\Driver\Exception\ConnectionException;

class MongoDBConnectionTest extends TestCase
{
    public function test_mongodb_connection()
    {
        try {
            $client = new Client(env('MONGODB_URI'), [
                'ssl' => true,
                'retryWrites' => true,
                'w' => 'majority',
                'readPreference' => 'primary',
            ]);
            
            // Test the connection by listing databases
            $databases = $client->listDatabases();
            $databaseNames = [];
            foreach ($databases as $database) {
                $databaseNames[] = $database->getName();
            }
            
            // Check if our target database is in the list
            $this->assertContains(env('MONGODB_DATABASE'), $databaseNames, 'Target database not found in MongoDB Atlas');
            $this->assertTrue(true, 'MongoDB Atlas connection successful');
        } catch (ConnectionException $e) {
            $this->fail('MongoDB Atlas connection failed: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->fail('Unexpected error: ' . $e->getMessage());
        }
    }
} 