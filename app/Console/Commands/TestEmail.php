<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    protected $signature = 'mail:test';
    protected $description = 'Send a test email to verify mail configuration';

    public function handle()
    {
        $this->info('Sending test email...');

        try {
            Mail::raw('Test email from Salarman application', function($message) {
                $message->to('salarman.code@gmail.com')
                        ->subject('Test Email');
            });

            $this->info('Test email sent successfully!');
        } catch (\Exception $e) {
            $this->error('Failed to send test email:');
            $this->error($e->getMessage());
        }
    }
} 