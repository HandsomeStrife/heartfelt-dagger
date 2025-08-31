<?php

declare(strict_types=1);

use Domain\Room\Actions\GenerateWasabiPresignedUrl;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\Room\Services\WasabiS3Service;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Wasabi Recording Integration', function () {
    test('GenerateWasabiPresignedUrl action works with bucket auto-creation', function () {
        // Skip if no real Wasabi credentials in environment
        if (empty(config('services.wasabi.access_key')) || empty(config('services.wasabi.secret_key'))) {
            $this->markTestSkipped('Wasabi credentials not available in environment');
        }

        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);
        
        // Use a unique bucket name that likely doesn't exist
        $uniqueBucketName = 'daggerheart-test-action-' . time() . '-' . rand(1000, 9999);
        
        $storageAccount = UserStorageAccount::factory()->wasabi()->create([
            'user_id' => $user->id,
            'display_name' => 'Action Test Wasabi',
            'encrypted_credentials' => [
                'access_key_id' => config('services.wasabi.access_key'),
                'secret_access_key' => config('services.wasabi.secret_key'),
                'region' => env('WASABI_REGION', 'us-east-1'),
                'endpoint' => env('WASABI_ENDPOINT', 'https://s3.wasabisys.com'),
                'bucket_name' => $uniqueBucketName,
            ],
        ]);

        $recordingSettings = RoomRecordingSettings::factory()->create([
            'room_id' => $room->id,
            'recording_enabled' => true,
            'storage_provider' => 'wasabi',
            'storage_account_id' => $storageAccount->id,
        ]);

        $room->refresh();

        try {
            $action = new GenerateWasabiPresignedUrl();
            
            // This should work even if the bucket doesn't exist (auto-creation)
            $result = $action->execute(
                $room,
                $user,
                'integration-test-recording.webm',
                'video/webm',
                5 * 1024 * 1024, // 5MB
                ['test_type' => 'integration', 'created_by' => 'automated_test']
            );

            // Verify result structure
            expect($result)->toBeArray();
            expect($result['success'])->toBeTrue();
            expect($result['bucket'])->toBe($uniqueBucketName);
            expect($result['presigned_url'])->toContain($uniqueBucketName);
            expect($result['key'])->toContain('integration-test-recording.webm');
            
            // Verify metadata includes our custom data
            expect($result['metadata']['test_type'])->toBe('integration');
            expect($result['metadata']['created_by'])->toBe('automated_test');
            
            // Verify that the bucket now exists by testing service directly
            $wasabiService = new WasabiS3Service($storageAccount);
            $connectionTest = $wasabiService->testConnection();
            expect($connectionTest)->toBeTrue();

            // Test that we can generate additional presigned URLs for the same bucket
            $secondResult = $action->execute(
                $room,
                $user,
                'second-test-recording.mp4',
                'video/mp4',
                2 * 1024 * 1024
            );
            
            expect($secondResult['success'])->toBeTrue();
            expect($secondResult['bucket'])->toBe($uniqueBucketName);
            
        } catch (\Exception $e) {
            $this->fail('Integration test failed: ' . $e->getMessage());
        } finally {
            // Clean up: Delete the test bucket if it was created
            try {
                $wasabiService = new WasabiS3Service($storageAccount);
                $s3Client = $wasabiService->createS3ClientWithCredentials($storageAccount->encrypted_credentials);
                
                // Delete the bucket (it should be empty since we only generated URLs, didn't upload)
                $s3Client->deleteBucket(['Bucket' => $uniqueBucketName]);
            } catch (\Exception $e) {
                error_log("Failed to clean up integration test bucket {$uniqueBucketName}: " . $e->getMessage());
            }
        }
    })->skip(fn() => app()->environment('testing') && empty(config('services.wasabi.access_key')), 
        'Wasabi credentials not configured for testing');

    test('RoomRecordingSettings factory works correctly with Wasabi', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);
        
        $storageAccount = UserStorageAccount::factory()->wasabi()->create([
            'user_id' => $user->id,
        ]);

        // Test the withWasabiStorage factory method
        $settings = RoomRecordingSettings::factory()
            ->withWasabiStorage($storageAccount)
            ->create(['room_id' => $room->id]);

        expect($settings->recording_enabled)->toBeTrue();
        expect($settings->storage_provider)->toBe('wasabi');
        expect($settings->storage_account_id)->toBe($storageAccount->id);
        expect($settings->isRecordingEnabled())->toBeTrue();
        expect($settings->isUsingWasabi())->toBeTrue();
        expect($settings->hasStorageProvider())->toBeTrue();

        // Test that the relationship works
        expect($settings->storageAccount)->toBeInstanceOf(UserStorageAccount::class);
        expect($settings->storageAccount->provider)->toBe('wasabi');
    });

    test('RoomRecordingSettings validation methods work correctly', function () {
        $settings = RoomRecordingSettings::factory()->create([
            'recording_enabled' => false,
            'stt_enabled' => false,
            'storage_provider' => null,
            'storage_account_id' => null,
        ]);

        // Test disabled state
        expect($settings->isRecordingEnabled())->toBeFalse();
        expect($settings->isSttEnabled())->toBeFalse();
        expect($settings->hasStorageProvider())->toBeFalse();
        expect($settings->isUsingWasabi())->toBeFalse();
        expect($settings->isUsingGoogleDrive())->toBeFalse();

        // Test enabled state with Wasabi
        $storageAccount = UserStorageAccount::factory()->wasabi()->create();
        $settings->enableRecording('wasabi', $storageAccount);

        expect($settings->isRecordingEnabled())->toBeTrue();
        expect($settings->hasStorageProvider())->toBeTrue();
        expect($settings->isUsingWasabi())->toBeTrue();
        expect($settings->isUsingGoogleDrive())->toBeFalse();
        expect($settings->storage_provider)->toBe('wasabi');
        expect($settings->storage_account_id)->toBe($storageAccount->id);

        // Test disabling recording
        $settings->disableRecording();
        expect($settings->isRecordingEnabled())->toBeFalse();
        // Note: disableRecording() only sets recording_enabled to false
        // Storage provider and account remain configured for re-enabling
        expect($settings->storage_provider)->toBe('wasabi');
        expect($settings->storage_account_id)->toBe($storageAccount->id);
    });

    test('room with recording settings loads relationship correctly', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);
        
        $storageAccount = UserStorageAccount::factory()->wasabi()->create(['user_id' => $user->id]);
        
        $settings = RoomRecordingSettings::factory()
            ->withWasabiStorage($storageAccount)
            ->create(['room_id' => $room->id]);

        // Test lazy loading
        $freshRoom = Room::find($room->id);
        expect($freshRoom->recordingSettings)->toBeInstanceOf(RoomRecordingSettings::class);
        expect($freshRoom->recordingSettings->storage_provider)->toBe('wasabi');

        // Test eager loading
        $eagerRoom = Room::with('recordingSettings.storageAccount')->find($room->id);
        expect($eagerRoom->recordingSettings)->toBeInstanceOf(RoomRecordingSettings::class);
        expect($eagerRoom->recordingSettings->storageAccount)->toBeInstanceOf(UserStorageAccount::class);
        expect($eagerRoom->recordingSettings->storageAccount->provider)->toBe('wasabi');
    });

    test('multiple rooms can have different recording configurations', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $room1 = Room::factory()->create(['creator_id' => $user1->id]);
        $room2 = Room::factory()->create(['creator_id' => $user2->id]);
        $room3 = Room::factory()->create(['creator_id' => $user1->id]);

        $wasabiAccount = UserStorageAccount::factory()->wasabi()->create(['user_id' => $user1->id]);
        
        // Room 1: Wasabi recording enabled
        RoomRecordingSettings::factory()
            ->withWasabiStorage($wasabiAccount)
            ->create(['room_id' => $room1->id]);

        // Room 2: Local recording enabled  
        RoomRecordingSettings::factory()
            ->withLocalStorage()
            ->create(['room_id' => $room2->id]);

        // Room 3: Recording disabled
        RoomRecordingSettings::factory()->create([
            'room_id' => $room3->id,
            'recording_enabled' => false,
        ]);

        // Test that each room has correct settings
        $room1->refresh();
        $room2->refresh();
        $room3->refresh();

        expect($room1->recordingSettings->isUsingWasabi())->toBeTrue();
        expect($room1->recordingSettings->storage_account_id)->toBe($wasabiAccount->id);

        expect($room2->recordingSettings->storage_provider)->toBe('local');
        expect($room2->recordingSettings->storage_account_id)->toBeNull();

        expect($room3->recordingSettings->isRecordingEnabled())->toBeFalse();
        expect($room3->recordingSettings->hasStorageProvider())->toBeFalse();
    });
});
