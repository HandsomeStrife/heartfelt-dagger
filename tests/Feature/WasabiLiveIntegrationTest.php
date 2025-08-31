<?php

declare(strict_types=1);

use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;
use Domain\Room\Services\WasabiS3Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Aws\Exception\AwsException;

uses(RefreshDatabase::class);

describe('Wasabi Live Integration Tests', function () {
    test('can connect to real Wasabi and verify response structure', function () {
        // Skip if no real Wasabi credentials in environment
        if (empty(config('services.wasabi.access_key')) || empty(config('services.wasabi.secret_key'))) {
            $this->markTestSkipped('Wasabi credentials not available in environment');
        }

        $user = User::factory()->create();
        
        $storageAccount = UserStorageAccount::factory()->wasabi()->create([
            'user_id' => $user->id,
            'display_name' => 'Live Test Wasabi',
            'encrypted_credentials' => [
                'access_key_id' => config('services.wasabi.access_key'),
                'secret_access_key' => config('services.wasabi.secret_key'),
                'region' => env('WASABI_REGION', 'us-east-1'),
                'endpoint' => env('WASABI_ENDPOINT', 'https://s3.wasabisys.com'),
                'bucket_name' => env('WASABI_BUCKET', 'daggerheart-test'),
            ],
        ]);

        $wasabiService = new WasabiS3Service($storageAccount);
        
        // Test creating a multipart upload
        $testKey = 'rooms/test-room/users/' . $user->id . '/live-test-' . time() . '.webm';
        
        try {
            // Test presigned upload URL generation
            $uploadResult = $wasabiService->generatePresignedUploadUrl(
                $testKey,
                'video/webm',
                60
            );

            // Verify response structure
            expect($uploadResult)->toBeArray();
            expect($uploadResult)->toHaveKeys(['presigned_url', 'bucket', 'key', 'expires_at', 'headers']);
            expect($uploadResult['presigned_url'])->toBeString();
            expect($uploadResult['presigned_url'])->toContain('https://');
            expect($uploadResult['bucket'])->toBeString();
            expect($uploadResult['key'])->toBe($testKey);
            expect($uploadResult['expires_at'])->toBeString();

            // Test generating a presigned download URL
            $downloadData = $wasabiService->generatePresignedDownloadUrl($testKey, 60);
            
            expect($downloadData)->toBeArray();
            expect($downloadData)->toHaveKeys(['download_url', 'bucket', 'key', 'expires_at']);
            expect($downloadData['download_url'])->toBeString();
            expect($downloadData['download_url'])->toContain('https://');
            expect($downloadData['bucket'])->toBeString();
            expect($downloadData['key'])->toBe($testKey);
            expect($downloadData['expires_at'])->toBeString();

            // Test connection by listing buckets

            // Verify we can list buckets (connectivity test)
            $listResult = $wasabiService->createS3ClientWithCredentials($storageAccount->encrypted_credentials)
                ->listBuckets();
            
            expect($listResult)->toBeInstanceOf(\Aws\Result::class);
            expect($listResult['Buckets'])->toBeArray();

        } catch (\Exception $e) {
            $this->fail('Live Wasabi integration failed: ' . $e->getMessage());
        }
    })->skip(fn() => app()->environment('testing') && empty(config('services.wasabi.access_key')), 
        'Wasabi credentials not configured for testing');

    test('can actually upload files to Wasabi and verify they exist', function () {
        // Skip if no real Wasabi credentials in environment
        if (empty(config('services.wasabi.access_key')) || empty(config('services.wasabi.secret_key'))) {
            $this->markTestSkipped('Wasabi credentials not available in environment');
        }

        $user = User::factory()->create();
        
        $storageAccount = UserStorageAccount::factory()->wasabi()->create([
            'user_id' => $user->id,
            'display_name' => 'Live Upload Test Wasabi',
            'encrypted_credentials' => [
                'access_key_id' => config('services.wasabi.access_key'),
                'secret_access_key' => config('services.wasabi.secret_key'),
                'region' => env('WASABI_REGION', 'us-east-1'),
                'endpoint' => env('WASABI_ENDPOINT', 'https://s3.wasabisys.com'),
                'bucket_name' => env('WASABI_BUCKET', 'daggerheart-test'),
            ],
        ]);

        $wasabiService = new WasabiS3Service($storageAccount);
        
        // Test files from the media directory
        $testFiles = [
            [
                'path' => base_path('tests/Browser/media/talking_test.wav'),
                'content_type' => 'audio/wav',
                'key_suffix' => 'talking_test.wav'
            ],
            [
                'path' => base_path('tests/Browser/media/Big_Buck_Bunny_1080_10s_5MB.mp4'),
                'content_type' => 'video/mp4', 
                'key_suffix' => 'Big_Buck_Bunny_1080_10s_5MB.mp4'
            ]
        ];

        $uploadedKeys = [];

        try {
            foreach ($testFiles as $fileInfo) {
                // Skip if test file doesn't exist
                if (!file_exists($fileInfo['path'])) {
                    continue;
                }

                // Generate unique test key
                $testKey = 'test-uploads/live-integration/' . $user->id . '/' . time() . '-' . $fileInfo['key_suffix'];
                $uploadedKeys[] = $testKey;

                // Generate presigned upload URL
                $uploadResult = $wasabiService->generatePresignedUploadUrl(
                    $testKey,
                    $fileInfo['content_type'],
                    30 // 30 minutes should be plenty for test
                );

                // Read file content
                $fileContent = file_get_contents($fileInfo['path']);
                $fileSize = filesize($fileInfo['path']);

                // Upload file using the presigned URL
                $uploadResponse = Http::withHeaders([
                    'Content-Type' => $fileInfo['content_type'],
                    'Content-Length' => (string) $fileSize,
                ])
                ->withBody($fileContent, $fileInfo['content_type'])
                ->put($uploadResult['presigned_url']);

                // Verify upload was successful (S3 returns 200 OK for successful PUT)
                if (!$uploadResponse->successful()) {
                    throw new \Exception("Upload failed for {$fileInfo['key_suffix']}: HTTP {$uploadResponse->status()} - {$uploadResponse->body()}");
                }

                // Wait a moment for the upload to be processed
                sleep(1);

                // Verify the file exists on Wasabi using getObjectMetadata
                $metadata = $wasabiService->getObjectMetadata($testKey);
                
                expect($metadata)->toBeArray();
                expect($metadata)->toHaveKey('content_length');
                expect($metadata)->toHaveKey('content_type');
                expect($metadata['content_length'])->toBe($fileSize);
                expect($metadata['content_type'])->toContain($fileInfo['content_type']);

                // Test that we can generate a download URL for the uploaded file
                $downloadData = $wasabiService->generatePresignedDownloadUrl($testKey, 10);
                expect($downloadData)->toBeArray();
                expect($downloadData['download_url'])->toBeString();

                // Verify the download URL actually works by attempting to download
                $downloadResponse = Http::timeout(30)->get($downloadData['download_url']);
                expect($downloadResponse->successful())->toBeTrue();
                expect(strlen($downloadResponse->body()))->toBe($fileSize);
            }

            // Verify we uploaded at least one file successfully
            expect(count($uploadedKeys))->toBeGreaterThan(0);

        } catch (\Exception $e) {
            $this->fail('Live Wasabi file upload integration failed: ' . $e->getMessage());
        } finally {
            // Clean up: Delete all uploaded test files
            foreach ($uploadedKeys as $key) {
                try {
                    $wasabiService->deleteObject($key);
                } catch (\Exception $e) {
                    // Log cleanup failure but don't fail the test
                    error_log("Failed to clean up test file {$key}: " . $e->getMessage());
                }
            }
        }
    })->skip(fn() => app()->environment('testing') && empty(config('services.wasabi.access_key')), 
        'Wasabi credentials not configured for testing');

    test('can directly upload files using S3 client without presigned URLs', function () {
        // Skip if no real Wasabi credentials in environment
        if (empty(config('services.wasabi.access_key')) || empty(config('services.wasabi.secret_key'))) {
            $this->markTestSkipped('Wasabi credentials not available in environment');
        }

        $user = User::factory()->create();
        
        $storageAccount = UserStorageAccount::factory()->wasabi()->create([
            'user_id' => $user->id,
            'display_name' => 'Direct Upload Test Wasabi',
            'encrypted_credentials' => [
                'access_key_id' => config('services.wasabi.access_key'),
                'secret_access_key' => config('services.wasabi.secret_key'),
                'region' => env('WASABI_REGION', 'us-east-1'),
                'endpoint' => env('WASABI_ENDPOINT', 'https://s3.wasabisys.com'),
                'bucket_name' => env('WASABI_BUCKET', 'daggerheart-test'),
            ],
        ]);

        $wasabiService = new WasabiS3Service($storageAccount);
        $s3Client = $wasabiService->createS3ClientWithCredentials($storageAccount->encrypted_credentials);
        $bucket = $storageAccount->encrypted_credentials['bucket_name'];

        // Test file for direct upload
        $testFile = base_path('tests/Browser/media/talking_test.wav');
        
        // Skip if test file doesn't exist
        if (!file_exists($testFile)) {
            $this->markTestSkipped('Test media file not found');
        }

        $testKey = 'test-uploads/direct-s3/' . $user->id . '/' . time() . '-direct-talking_test.wav';
        
        try {
            // First ensure bucket exists (this will create it if needed)
            $wasabiService->testConnection();
            
            // Direct upload using S3 client
            $result = $s3Client->putObject([
                'Bucket' => $bucket,
                'Key' => $testKey,
                'Body' => fopen($testFile, 'rb'),
                'ContentType' => 'audio/wav',
            ]);

            // Verify upload was successful
            expect($result)->toBeInstanceOf(\Aws\Result::class);
            expect($result['ETag'])->toBeString();

            // Verify file exists by getting metadata
            $metadata = $wasabiService->getObjectMetadata($testKey);
            expect($metadata)->toBeArray();
            expect($metadata['content_length'])->toBe(filesize($testFile));
            expect($metadata['content_type'])->toContain('audio/wav');

            // Test we can list objects and find our uploaded file
            $listResult = $s3Client->listObjectsV2([
                'Bucket' => $bucket,
                'Prefix' => 'test-uploads/direct-s3/' . $user->id . '/',
                'MaxKeys' => 10
            ]);

            expect($listResult['KeyCount'])->toBeGreaterThan(0);
            
            // Find our uploaded object in the list
            $foundObject = null;
            foreach ($listResult['Contents'] ?? [] as $object) {
                if ($object['Key'] === $testKey) {
                    $foundObject = $object;
                    break;
                }
            }
            
            expect($foundObject)->not->toBeNull();
            expect((int)$foundObject['Size'])->toBe(filesize($testFile));

        } catch (\Exception $e) {
            $this->fail('Direct S3 upload failed: ' . $e->getMessage());
        } finally {
            // Clean up the test file
            try {
                $wasabiService->deleteObject($testKey);
            } catch (\Exception $e) {
                error_log("Failed to clean up direct upload test file {$testKey}: " . $e->getMessage());
            }
        }
    })->skip(fn() => app()->environment('testing') && empty(config('services.wasabi.access_key')), 
        'Wasabi credentials not configured for testing');

    test('can generate working presigned URLs that match expected format', function () {
        if (empty(config('services.wasabi.access_key'))) {
            $this->markTestSkipped('Wasabi credentials not available');
        }

        $user = User::factory()->create();
        
        $storageAccount = UserStorageAccount::factory()->wasabi()->create([
            'user_id' => $user->id,
            'encrypted_credentials' => [
                'access_key_id' => config('services.wasabi.access_key'),
                'secret_access_key' => config('services.wasabi.secret_key'),
                'region' => env('WASABI_REGION', 'us-east-1'),
                'endpoint' => env('WASABI_ENDPOINT', 'https://s3.wasabisys.com'),
                'bucket_name' => env('WASABI_BUCKET', 'daggerheart-test'),
            ],
        ]);

        $wasabiService = new WasabiS3Service($storageAccount);
        $testKey = 'test-files/sample.webm';

        // Generate presigned download URL
        $downloadData = $wasabiService->generatePresignedDownloadUrl($testKey, 30);

        // Verify URL structure matches expectations
        expect($downloadData['download_url'])->toMatch('/^https:\/\//');
        expect($downloadData['download_url'])->toContain($storageAccount->encrypted_credentials['bucket_name']);
        expect($downloadData['download_url'])->toContain($testKey);
        expect($downloadData['download_url'])->toContain('X-Amz-Signature=');
        expect($downloadData['download_url'])->toContain('X-Amz-Expires=');
        
        // URL should be valid for ~30 minutes
        $parsedUrl = parse_url($downloadData['download_url']);
        parse_str($parsedUrl['query'], $queryParams);
        expect((int)$queryParams['X-Amz-Expires'])->toBeLessThanOrEqual(1800); // 30 minutes
        expect((int)$queryParams['X-Amz-Expires'])->toBeGreaterThan(1700); // At least 28+ minutes

        // Expires timestamp should be roughly 30 minutes from now
        $expiresAt = new \DateTime($downloadData['expires_at']);
        $expectedExpiry = now()->addMinutes(30);
        $timeDiff = abs($expiresAt->getTimestamp() - $expectedExpiry->getTimestamp());
        expect($timeDiff)->toBeLessThan(60); // Within 1 minute tolerance
    })->skip(fn() => app()->environment('testing') && empty(config('services.wasabi.access_key')), 
        'Wasabi credentials not configured for testing');

    test('can automatically create buckets that do not exist', function () {
        // Skip if no real Wasabi credentials in environment
        if (empty(config('services.wasabi.access_key')) || empty(config('services.wasabi.secret_key'))) {
            $this->markTestSkipped('Wasabi credentials not available in environment');
        }

        $user = User::factory()->create();
        
        // Use a unique bucket name to ensure it doesn't already exist
        $uniqueBucketName = 'daggerheart-test-autocreate-' . time() . '-' . rand(1000, 9999);
        
        $storageAccount = UserStorageAccount::factory()->wasabi()->create([
            'user_id' => $user->id,
            'display_name' => 'Auto-Create Bucket Test Wasabi',
            'encrypted_credentials' => [
                'access_key_id' => config('services.wasabi.access_key'),
                'secret_access_key' => config('services.wasabi.secret_key'),
                'region' => env('WASABI_REGION', 'us-east-1'),
                'endpoint' => env('WASABI_ENDPOINT', 'https://s3.wasabisys.com'),
                'bucket_name' => $uniqueBucketName,
            ],
        ]);

        $wasabiService = new WasabiS3Service($storageAccount);
        $s3Client = $wasabiService->createS3ClientWithCredentials($storageAccount->encrypted_credentials);
        
        try {
            // First, verify the bucket doesn't exist by trying to list objects
            try {
                $s3Client->listObjectsV2([
                    'Bucket' => $uniqueBucketName,
                    'MaxKeys' => 1,
                ]);
                
                // If we get here, the bucket already exists - skip test
                $this->markTestSkipped('Test bucket already exists: ' . $uniqueBucketName);
                
            } catch (AwsException $e) {
                // Expect NoSuchBucket error - this is what we want
                expect($e->getAwsErrorCode())->toBe('NoSuchBucket');
            }

            // Now call testConnection() which should create the bucket automatically
            $connectionResult = $wasabiService->testConnection();
            expect($connectionResult)->toBeTrue();

            // Verify the bucket now exists by successfully listing objects
            $listResult = $s3Client->listObjectsV2([
                'Bucket' => $uniqueBucketName,
                'MaxKeys' => 1,
            ]);
            
            expect($listResult)->toBeInstanceOf(\Aws\Result::class);
            expect($listResult['Name'])->toBe($uniqueBucketName);

            // Test that we can now generate presigned URLs (which also ensures bucket exists)
            $testKey = 'test-autocreate/' . $user->id . '/test-file.txt';
            $uploadResult = $wasabiService->generatePresignedUploadUrl(
                $testKey,
                'text/plain',
                30
            );
            
            expect($uploadResult)->toBeArray();
            expect($uploadResult['bucket'])->toBe($uniqueBucketName);
            expect($uploadResult['presigned_url'])->toContain($uniqueBucketName);

        } catch (\Exception $e) {
            $this->fail('Auto-create bucket test failed: ' . $e->getMessage());
        } finally {
            // Clean up: Delete the test bucket
            try {
                // Delete the bucket (it should be empty)
                $s3Client->deleteBucket(['Bucket' => $uniqueBucketName]);
            } catch (\Exception $e) {
                error_log("Failed to clean up test bucket {$uniqueBucketName}: " . $e->getMessage());
            }
        }
    })->skip(fn() => app()->environment('testing') && empty(config('services.wasabi.access_key')), 
        'Wasabi credentials not configured for testing');
});
