<?php

declare(strict_types=1);

use Domain\Room\Actions\CreateWasabiRecording;
use Domain\Room\Actions\GenerateWasabiDownloadUrl;
use Domain\Room\Actions\GenerateWasabiPresignedUrl;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\Room\Models\RoomRecording;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;

test('can generate wasabi presigned URL for upload', function () {
    // Create test user and room
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    // Create storage account for Wasabi
    $storageAccount = UserStorageAccount::create([
        'user_id' => $user->id,
        'provider' => 'wasabi',
        'encrypted_credentials' => [
            'access_key_id' => 'test_access_key',
            'secret_access_key' => 'test_secret_key',
            'bucket_name' => 'test-bucket',
            'region' => 'us-east-1',
            'endpoint' => 'https://s3.wasabisys.com',
        ],
        'display_name' => 'Test Wasabi Account',
        'is_active' => true,
    ]);

    // Enable recording for the room with Wasabi storage
    RoomRecordingSettings::create([
        'room_id' => $room->id,
        'recording_enabled' => true,
        'stt_enabled' => false,
        'storage_provider' => 'wasabi',
        'storage_account_id' => $storageAccount->id,
    ]);

    // Create participant with consent
    RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_id' => null,
        'character_name' => 'Test Character',
        'character_class' => 'Warrior',
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
    ]);

    // Test presigned URL generation API endpoint
    $response = $this->actingAs($user)
        ->postJson("/api/rooms/{$room->id}/recordings/presign-wasabi", [
            'filename' => 'test_recording.webm',
            'content_type' => 'video/webm',
            'size' => 1024000,
            'metadata' => [
                'started_at_ms' => 0,
                'ended_at_ms' => 5000,
            ]
        ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'presigned_url',
            'key',
            'bucket',
            'expires_at',
            'headers',
            'metadata' => [
                'provider',
                'provider_file_id',
                'storage_account_id',
                'room_id',
                'user_id',
                'filename',
                'size_bytes',
                'content_type',
            ]
        ])
        ->assertJson([
            'success' => true,
            'bucket' => 'test-bucket',
            'metadata' => [
                'provider' => 'wasabi',
                'storage_account_id' => $storageAccount->id,
                'room_id' => $room->id,
                'user_id' => $user->id,
                'filename' => 'test_recording.webm',
                'size_bytes' => 1024000,
                'content_type' => 'video/webm',
            ]
        ]);

    expect($response->json('presigned_url'))->toContain('test-bucket');
    expect($response->json('key'))->toContain('recordings/');
    expect($response->json('key'))->toContain("room_{$room->id}");
    expect($response->json('key'))->toContain("user_{$user->id}");
});

test('can confirm successful wasabi upload', function () {
    // Create test user and room
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    // Create storage account for Wasabi
    $storageAccount = UserStorageAccount::create([
        'user_id' => $user->id,
        'provider' => 'wasabi',
        'encrypted_credentials' => [
            'access_key_id' => 'test_access_key',
            'secret_access_key' => 'test_secret_key',
            'bucket_name' => 'test-bucket',
            'region' => 'us-east-1',
            'endpoint' => 'https://s3.wasabisys.com',
        ],
        'display_name' => 'Test Wasabi Account',
        'is_active' => true,
    ]);

    // Enable recording for the room with Wasabi storage
    RoomRecordingSettings::create([
        'room_id' => $room->id,
        'recording_enabled' => true,
        'stt_enabled' => false,
        'storage_provider' => 'wasabi',
        'storage_account_id' => $storageAccount->id,
    ]);

    // Create participant with consent
    RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_id' => null,
        'character_name' => 'Test Character',
        'character_class' => 'Warrior',
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
    ]);

    // Test confirm upload API endpoint
    $response = $this->actingAs($user)
        ->postJson("/api/rooms/{$room->id}/recordings/confirm-wasabi", [
            'provider_file_id' => 'recordings/2024/01/01/room_1/user_1/test_recording.webm',
            'filename' => 'test_recording.webm',
            'size_bytes' => 1024000,
            'started_at_ms' => 0,
            'ended_at_ms' => 5000,
            'mime_type' => 'video/webm'
        ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Recording confirmed and saved'
        ])
        ->assertJsonStructure(['recording_id']);

    // Verify recording was created in database
    expect(RoomRecording::count())->toBe(1);
    
    $recording = RoomRecording::first();
    expect($recording->room_id)->toBe($room->id);
    expect($recording->user_id)->toBe($user->id);
    expect($recording->provider)->toBe('wasabi');
    expect($recording->provider_file_id)->toBe('recordings/2024/01/01/room_1/user_1/test_recording.webm');
    expect($recording->filename)->toBe('test_recording.webm');
    expect($recording->size_bytes)->toBe(1024000);
    expect($recording->status)->toBe('uploaded');
});

test('cannot generate wasabi presigned URL without consent', function () {
    // Create test user and room
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    // Create storage account for Wasabi
    $storageAccount = UserStorageAccount::create([
        'user_id' => $user->id,
        'provider' => 'wasabi',
        'encrypted_credentials' => [
            'access_key_id' => 'test_access_key',
            'secret_access_key' => 'test_secret_key',
            'bucket_name' => 'test-bucket',
            'region' => 'us-east-1',
            'endpoint' => 'https://s3.wasabisys.com',
        ],
        'display_name' => 'Test Wasabi Account',
        'is_active' => true,
    ]);

    // Enable recording for the room with Wasabi storage
    RoomRecordingSettings::create([
        'room_id' => $room->id,
        'recording_enabled' => true,
        'stt_enabled' => false,
        'storage_provider' => 'wasabi',
        'storage_account_id' => $storageAccount->id,
    ]);

    // Create participant WITHOUT consent
    RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_id' => null,
        'character_name' => 'Test Character',
        'character_class' => 'Warrior',
        'stt_consent_given' => null, // No consent
        'stt_consent_at' => null,
    ]);

    // Test presigned URL generation should fail
    $response = $this->actingAs($user)
        ->postJson("/api/rooms/{$room->id}/recordings/presign-wasabi", [
            'filename' => 'test_recording.webm',
            'content_type' => 'video/webm',
            'size' => 1024000,
        ]);

    $response->assertStatus(403)
        ->assertJson([
            'error' => 'Video recording consent required',
            'requires_consent' => true
        ]);
});

test('cannot generate wasabi presigned URL for wrong storage provider', function () {
    // Create test user and room
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    // Enable recording for the room with LOCAL storage (not Wasabi)
    RoomRecordingSettings::create([
        'room_id' => $room->id,
        'recording_enabled' => true,
        'stt_enabled' => false,
        'storage_provider' => 'local', // Not Wasabi
        'storage_account_id' => null,
    ]);

    // Create participant with consent
    RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_id' => null,
        'character_name' => 'Test Character',
        'character_class' => 'Warrior',
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
    ]);

    // Test presigned URL generation should fail due to wrong storage provider
    $response = $this->actingAs($user)
        ->postJson("/api/rooms/{$room->id}/recordings/presign-wasabi", [
            'filename' => 'test_recording.webm',
            'content_type' => 'video/webm',
            'size' => 1024000,
        ]);

    $response->assertStatus(500)
        ->assertJsonStructure(['error']);
});

