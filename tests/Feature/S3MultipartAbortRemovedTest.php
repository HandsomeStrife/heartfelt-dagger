<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;

describe('S3 Multipart Abort Endpoint Removal', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->room = Room::factory()->create(['creator_id' => $this->user->id]);
        
        // Create recording settings with Wasabi storage
        $this->recordingSettings = RoomRecordingSettings::factory()->create([
            'room_id' => $this->room->id,
            'recording_enabled' => true,
            'storage_provider' => 'wasabi',
        ]);
        
        // Create storage account
        $this->storageAccount = UserStorageAccount::factory()->create([
            'user_id' => $this->user->id,
            'provider' => 'wasabi',
        ]);
        
        $this->recordingSettings->update(['storage_account_id' => $this->storageAccount->id]);
    });

    test('abort endpoint is no longer available', function () {
        $requestData = [
            'uploadId' => 'test-upload-id',
            'key' => "rooms/{$this->room->id}/users/{$this->user->id}/test.webm",
            'room_id' => $this->room->id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/uploads/s3/multipart/abort', $requestData);

        // Should return 404 Not Found since the route is removed
        $response->assertNotFound();
    });

    test('other multipart endpoints still work', function () {
        // Test that create endpoint still exists (should fail with missing credentials but route exists)
        $response = $this->actingAs($this->user)
            ->postJson('/api/uploads/s3/multipart/create', [
                'filename' => 'test.webm',
                'type' => 'video/webm',
                'size' => 1024,
                'room_id' => $this->room->id,
            ]);

        // Should not be 404 (route exists), may be other error due to missing credentials
        expect($response->status())->not()->toBe(404);
    });

    test('sign endpoint still works', function () {
        // Test that sign endpoint still exists (should fail with validation but route exists)
        $response = $this->actingAs($this->user)
            ->postJson('/api/uploads/s3/multipart/sign', [
                'uploadId' => 'test-upload-id',
                'key' => "rooms/{$this->room->id}/users/{$this->user->id}/test.webm",
                'partNumber' => 1,
                'room_id' => $this->room->id,
            ]);

        // Should not be 404 (route exists), may be other error due to missing credentials
        expect($response->status())->not()->toBe(404);
    });

    test('complete endpoint still works', function () {
        // Test that complete endpoint still exists (should fail with validation but route exists)
        $response = $this->actingAs($this->user)
            ->postJson('/api/uploads/s3/multipart/complete', [
                'uploadId' => 'test-upload-id',
                'key' => "rooms/{$this->room->id}/users/{$this->user->id}/test.webm",
                'parts' => [
                    ['PartNumber' => 1, 'ETag' => 'test-etag']
                ],
                'room_id' => $this->room->id,
            ]);

        // Should not be 404 (route exists), may be other error due to missing credentials
        expect($response->status())->not()->toBe(404);
    });
});
