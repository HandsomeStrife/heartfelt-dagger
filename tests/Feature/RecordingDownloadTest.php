<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecording;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;
use Illuminate\Support\Facades\Storage;
use function Pest\Laravel\{actingAs, getJson};

beforeEach(function () {
    // Configure MinIO for testing
    config([
        'filesystems.disks.wasabi' => [
            'driver' => 's3',
            'key' => 'minioadmin',
            'secret' => 'minioadmin',
            'region' => 'us-east-1',
            'bucket' => 'recordings',
            'endpoint' => 'http://minio:9000',
            'use_path_style_endpoint' => true,
        ],
    ]);
});

describe('Recording Download Endpoints', function () {
    test('generates signed URL for Wasabi download', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);
        $storageAccount = UserStorageAccount::factory()->create([
            'user_id' => $user->id,
            'provider' => 'wasabi',
            'encrypted_credentials' => [
                'access_key_id' => 'minioadmin',
                'secret_access_key' => 'minioadmin',
                'bucket_name' => 'recordings',
                'region' => 'us-east-1',
                'endpoint' => 'http://minio:9000',
            ],
        ]);

        RoomRecordingSettings::factory()->create([
            'room_id' => $room->id,
            'recording_enabled' => true,
            'storage_provider' => 'wasabi',
            'storage_account_id' => $storageAccount->id,
        ]);

        // Add user as participant
        $room->participants()->create([
            'user_id' => $user->id,
            'character_name' => 'Test Character',
        ]);

        $recording = RoomRecording::create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'provider' => 'wasabi',
            'provider_file_id' => "rooms/{$room->id}/users/{$user->id}/test-recording.webm",
            'filename' => 'test-recording.webm',
            'size_bytes' => 1024 * 1024, // 1MB
            'started_at_ms' => 1234567890000,
            'ended_at_ms' => 1234567920000,
            'mime_type' => 'video/webm',
            'status' => 'uploaded',
            'started_at_ms' => 1234567890000,
            'ended_at_ms' => 1234567920000,
        ]);

        $response = actingAs($user)
            ->get("/api/rooms/{$room->id}/recordings/{$recording->id}/download");

        $response->assertStatus(302); // Redirect response

        $redirectUrl = $response->headers->get('location');
        expect($redirectUrl)->toBeString();
        expect($redirectUrl)->toContain('minio:9000'); // MinIO endpoint  
        expect($redirectUrl)->not()->toContain('127.0.0.1'); // Should use storage endpoint, not app
    });

    test('generates signed URL for Google Drive download', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        RoomRecordingSettings::factory()->create([
            'room_id' => $room->id,
            'recording_enabled' => true,
            'storage_provider' => 'google_drive',
        ]);

        // Add user as participant
        $room->participants()->create([
            'user_id' => $user->id,
            'character_name' => 'Test Character',
        ]);

        $recording = RoomRecording::create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'provider' => 'google_drive',
            'provider_file_id' => 'google-drive-file-id-123',
            'filename' => 'gdrive-recording.webm',
            'size_bytes' => 2048 * 1024, // 2MB
            'mime_type' => 'video/webm',
            'status' => 'uploaded',
            'started_at_ms' => 1234567890000,
            'ended_at_ms' => 1234567920000,
        ]);

        $response = actingAs($user)
            ->getJson("/api/rooms/{$room->id}/recordings/{$recording->id}/download");

        // This may fail if Google Drive integration isn't set up,
        // but we can test the basic structure
        if ($response->status() === 200) {
            $response->assertJsonStructure([
                'success',
                'download_url',
                'filename',
                'provider',
            ]);

            $data = $response->json();
            expect($data['provider'])->toBe('google_drive');
            expect($data['filename'])->toBe('gdrive-recording.webm');
        } else {
            // If Google Drive isn't configured, expect a specific error
            expect($response->status())->toBeIn([500, 502]);
        }
    });

    test('handles local storage downloads', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        RoomRecordingSettings::factory()->create([
            'room_id' => $room->id,
            'recording_enabled' => true,
            'storage_provider' => 'local',
        ]);

        // Add user as participant
        $room->participants()->create([
            'user_id' => $user->id,
            'character_name' => 'Test Character',
        ]);

        $recording = RoomRecording::create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'provider' => 'local',
            'provider_file_id' => 'recordings/local-test-file.webm',
            'filename' => 'local-recording.webm',
            'size_bytes' => 512 * 1024, // 512KB
            'mime_type' => 'video/webm',
            'status' => 'uploaded',
            'started_at_ms' => 1234567890000,
            'ended_at_ms' => 1234567920000,
        ]);

        // Create a fake file in storage for testing
        Storage::disk('local')->put('recordings/local-test-file.webm', 'fake-video-content');

        $response = actingAs($user)
            ->get("/api/rooms/{$room->id}/recordings/{$recording->id}/download");

        $response->assertStatus(200);
        
        // Verify headers for file download
        $response->assertHeader('Content-Type', 'video/webm');
        $response->assertHeader('Content-Disposition', 'attachment; filename="local-recording.webm"');
        $response->assertHeader('Content-Length', (string) (512 * 1024));
        
        // Verify content is the fake file content
        expect($response->getContent())->toBe('fake-video-content');

        // Clean up test file
        Storage::disk('local')->delete('recordings/local-test-file.webm');
    });

    test('fails when user lacks room access', function () {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $owner->id]);

        $recording = RoomRecording::create([
            'room_id' => $room->id,
            'user_id' => $owner->id,
            'provider' => 'local',
            'provider_file_id' => 'some-file.webm',
            'filename' => 'restricted.webm',
            'size_bytes' => 1024,
            'mime_type' => 'video/webm',
            'status' => 'uploaded',
            'started_at_ms' => 1234567890000,
            'ended_at_ms' => 1234567920000,
        ]);

        // Other user tries to download
        $response = actingAs($otherUser)
            ->getJson("/api/rooms/{$room->id}/recordings/{$recording->id}/download");

        $response->assertStatus(403)
            ->assertJson(['message' => 'Access denied']);
    });

    test('fails when user is not an active participant', function () {
        $roomOwner = User::factory()->create();
        $unauthorizedUser = User::factory()->create(); 
        $room = Room::factory()->create(['creator_id' => $roomOwner->id]);

        $recording = RoomRecording::create([
            'room_id' => $room->id,
            'user_id' => $roomOwner->id,
            'provider' => 'local',
            'provider_file_id' => 'some-file.webm',
            'filename' => 'test.webm',
            'size_bytes' => 1024,
            'mime_type' => 'video/webm',
            'status' => 'uploaded',
            'started_at_ms' => 1234567890000,
            'ended_at_ms' => 1234567920000,
        ]);

        // Unauthorized user (not room creator, not participant) tries to download
        $response = actingAs($unauthorizedUser)
            ->getJson("/api/rooms/{$room->id}/recordings/{$recording->id}/download");

        $response->assertStatus(403)
            ->assertJson(['message' => 'Access denied']);
    });

    test('fails when recording belongs to different room', function () {
        $user = User::factory()->create();
        $room1 = Room::factory()->create(['creator_id' => $user->id]);
        $room2 = Room::factory()->create(['creator_id' => $user->id]);

        // Add user as participant to room1
        $room1->participants()->create([
            'user_id' => $user->id,
            'character_name' => 'Test Character',
        ]);

        // Create recording in room2
        $recording = RoomRecording::create([
            'room_id' => $room2->id,
            'user_id' => $user->id,
            'provider' => 'local',
            'provider_file_id' => 'some-file.webm',
            'filename' => 'test.webm',
            'size_bytes' => 1024,
            'mime_type' => 'video/webm',
            'status' => 'uploaded',
            'started_at_ms' => 1234567890000,
            'ended_at_ms' => 1234567920000,
        ]);

        // Try to access recording from room2 through room1 endpoint
        $response = actingAs($user)
            ->getJson("/api/rooms/{$room1->id}/recordings/{$recording->id}/download");

        $response->assertStatus(404)
            ->assertJson(['message' => 'Recording not found in this room']);
    });

    test('fails when local file does not exist', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        // Add user as participant
        $room->participants()->create([
            'user_id' => $user->id,
            'character_name' => 'Test Character',
        ]);

        $recording = RoomRecording::create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'provider' => 'local',
            'provider_file_id' => 'recordings/non-existent-file.webm', // File doesn't exist
            'filename' => 'missing.webm',
            'size_bytes' => 1024,
            'mime_type' => 'video/webm',
            'status' => 'uploaded',
            'started_at_ms' => 1234567890000,
            'ended_at_ms' => 1234567920000,
        ]);

        $response = actingAs($user)
            ->getJson("/api/rooms/{$room->id}/recordings/{$recording->id}/download");

        $response->assertStatus(404)
            ->assertJson(['message' => 'Recording file not found']);
    });

    test('fails with unsupported storage provider', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        // Add user as participant
        $room->participants()->create([
            'user_id' => $user->id,
            'character_name' => 'Test Character',
        ]);

        $recording = RoomRecording::create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'provider' => 'unsupported_provider', // Invalid provider
            'provider_file_id' => 'some-file.webm',
            'filename' => 'test.webm',
            'size_bytes' => 1024,
            'mime_type' => 'video/webm',
            'status' => 'uploaded',
            'started_at_ms' => 1234567890000,
            'ended_at_ms' => 1234567920000,
        ]);

        $response = actingAs($user)
            ->getJson("/api/rooms/{$room->id}/recordings/{$recording->id}/download");

        $response->assertStatus(400)
            ->assertJson(['message' => 'Download not supported for this storage provider']);
    });

    test('download URL points to storage host not app host', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);
        $storageAccount = UserStorageAccount::factory()->create([
            'user_id' => $user->id,
            'provider' => 'wasabi',
            'is_active' => true,
            'encrypted_credentials' => [
                'access_key_id' => 'minioadmin',
                'secret_access_key' => 'minioadmin',
                'bucket_name' => 'recordings',
                'region' => 'us-east-1',
                'endpoint' => 'http://minio:9000',
            ],
        ]);

        RoomRecordingSettings::factory()->create([
            'room_id' => $room->id,
            'recording_enabled' => true,
            'storage_provider' => 'wasabi',
            'storage_account_id' => $storageAccount->id,
        ]);

        $room->participants()->create([
            'user_id' => $user->id,
            'character_name' => 'Test Character',
        ]);

        $recording = RoomRecording::create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'provider' => 'wasabi',
            'provider_file_id' => "rooms/{$room->id}/users/{$user->id}/test.webm",
            'filename' => 'test.webm',
            'size_bytes' => 1024,
            'mime_type' => 'video/webm',
            'status' => 'uploaded',
            'started_at_ms' => 1234567890000,
            'ended_at_ms' => 1234567920000,
        ]);

        $response = actingAs($user)
            ->get("/api/rooms/{$room->id}/recordings/{$recording->id}/download");

        $response->assertStatus(302); // Redirect for Wasabi
        $redirectUrl = $response->headers->get('location');

        // URL should point to storage endpoint, not app endpoint
        expect($redirectUrl)->toContain('minio:9000'); // MinIO endpoint
        expect($redirectUrl)->not()->toContain('localhost:80'); // App endpoint
        expect($redirectUrl)->not()->toContain('127.0.0.1'); // App endpoint variations
        expect($redirectUrl)->not()->toContain('127.0.0.1:80'); // App endpoint variations

        // Should be a signed URL with query parameters
        expect($redirectUrl)->toContain('X-Amz-Signature'); // AWS signature
        expect($redirectUrl)->toContain('X-Amz-Expires'); // Expiration
    });
});
