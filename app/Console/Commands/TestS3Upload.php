<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestS3Upload extends Command
{
    protected $signature = 'test:s3-upload';

    protected $description = 'Test S3 upload functionality';

    public function handle(): void
    {
        $this->info('Testing S3 connection and upload...');

        try {
            // Test 1: Check S3 configuration
            $s3Disk = Storage::disk('s3');
            $this->info('S3 disk configuration loaded successfully');

            // Test 2: Create a test file
            $testContent = 'This is a test file created at ' . now()->toString();
            $testFileName = 'test-files/test-' . time() . '.txt';

            // Test 3: Upload the test file
            $this->info("Uploading test file: {$testFileName}");
            
            // Enable S3 exception throwing for debugging
            $s3DiskWithExceptions = Storage::build([
                'driver' => 's3',
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
                'region' => env('AWS_DEFAULT_REGION'),
                'bucket' => env('AWS_BUCKET'),
                'url' => env('AWS_URL'),
                'endpoint' => env('AWS_ENDPOINT'),
                'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
                'throw' => true, // This will throw exceptions instead of returning false
            ]);
            
            $uploaded = $s3DiskWithExceptions->put($testFileName, $testContent);

            if ($uploaded) {
                $this->info('✅ File uploaded successfully!');

                // Test 4: Verify file exists
                if ($s3Disk->exists($testFileName)) {
                    $this->info('✅ File verified to exist on S3');

                    // Test 5: Get file URL
                    $url = $s3Disk->url($testFileName);
                    $this->info("✅ File URL: {$url}");

                    // Test 6: Clean up
                    $s3Disk->delete($testFileName);
                    $this->info('✅ Test file cleaned up');
                } else {
                    $this->error('❌ File upload succeeded but file does not exist on S3');
                }
            } else {
                $this->error('❌ File upload failed');
            }

            // Test 7: Test environment variables
            $this->info('Environment variables:');
            $this->line('AWS_ACCESS_KEY_ID: ' . (env('AWS_ACCESS_KEY_ID') ? 'Set' : 'Not set'));
            $this->line('AWS_SECRET_ACCESS_KEY: ' . (env('AWS_SECRET_ACCESS_KEY') ? 'Set' : 'Not set'));
            $this->line('AWS_DEFAULT_REGION: ' . env('AWS_DEFAULT_REGION', 'Not set'));
            $this->line('AWS_BUCKET: ' . env('AWS_BUCKET', 'Not set'));
            $this->line('AWS_URL: ' . env('AWS_URL', 'Not set'));

        } catch (\Exception $e) {
            $this->error('❌ S3 test failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }
    }
}