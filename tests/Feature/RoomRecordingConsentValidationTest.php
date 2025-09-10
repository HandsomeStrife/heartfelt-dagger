<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;

describe('Video Recording Consent Validation', function () {
    test('Google Drive upload URL generation requires recording consent', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        // Create Google Drive storage account
        $storageAccount = UserStorageAccount::factory()->create([
            'user_id' => $user->id,
            'provider' => 'google_drive',
            'encrypted_credentials' => [
                'access_token' => 'test_token',
                'refresh_token' => 'test_refresh',
            ],
        ]);

        // Enable recording with Google Drive
        RoomRecordingSettings::create([
            'room_id' => $room->id,
            'recording_enabled' => true,
            'storage_provider' => 'google_drive',
            'storage_account_id' => $storageAccount->id,
        ]);

        // Create participant WITHOUT recording consent
        RoomParticipant::create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'character_name' => 'Test Character',
            'character_class' => 'Warrior',
            'stt_consent_given' => true,        // STT consent granted
            'recording_consent_given' => null,  // NO recording consent
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/rooms/{$room->id}/recordings/google-drive-upload-url", [
                'filename' => 'test-recording.webm',
                'content_type' => 'video/webm',
                'size' => 1024 * 1024, // 1MB
                'metadata' => [
                    'started_at_ms' => now()->timestamp * 1000,
                    'ended_at_ms' => now()->addMinutes(5)->timestamp * 1000,
                ],
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Video recording consent required',
                'requires_consent' => true,
            ]);
    });

    test('Google Drive upload URL generation succeeds with recording consent', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        // Create Google Drive storage account
        $storageAccount = UserStorageAccount::factory()->create([
            'user_id' => $user->id,
            'provider' => 'google_drive',
            'encrypted_credentials' => [
                'access_token' => 'test_token',
                'refresh_token' => 'test_refresh',
            ],
        ]);

        // Enable recording with Google Drive
        RoomRecordingSettings::create([
            'room_id' => $room->id,
            'recording_enabled' => true,
            'storage_provider' => 'google_drive',
            'storage_account_id' => $storageAccount->id,
        ]);

        // Create participant WITH recording consent
        RoomParticipant::create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'character_name' => 'Test Character',
            'character_class' => 'Warrior',
            'stt_consent_given' => false,       // STT consent denied (should not affect recording)
            'recording_consent_given' => true, // Recording consent granted
            'recording_consent_at' => now(),
        ]);

        // Mock the Google Drive service to avoid actual API calls
        $this->mock(\Domain\Room\Actions\GenerateGoogleDriveUploadUrl::class, function ($mock) {
            $mock->shouldReceive('execute')
                ->once()
                ->andReturn([
                    'success' => true,
                    'upload_url' => 'https://www.googleapis.com/upload/drive/v3/files/resumable?uploadType=resumable',
                    'session_uri' => 'https://www.googleapis.com/upload/drive/v3/files/resumable?uploadType=resumable',
                    'filename' => 'test-recording.webm',
                    'content_type' => 'video/webm',
                ]);
        });

        $response = $this->actingAs($user)
            ->postJson("/api/rooms/{$room->id}/recordings/google-drive-upload-url", [
                'filename' => 'test-recording.webm',
                'content_type' => 'video/webm',
                'size' => 1024 * 1024, // 1MB
                'metadata' => [
                    'started_at_ms' => now()->timestamp * 1000,
                    'ended_at_ms' => now()->addMinutes(5)->timestamp * 1000,
                ],
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'upload_url',
                'session_uri',
                'filename',
                'content_type',
            ]);
    });

    test('STT consent does not affect video recording validation', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        // Create Google Drive storage account
        $storageAccount = UserStorageAccount::factory()->create([
            'user_id' => $user->id,
            'provider' => 'google_drive',
            'encrypted_credentials' => [
                'access_token' => 'test_token',
                'refresh_token' => 'test_refresh',
            ],
        ]);

        // Enable recording with Google Drive
        RoomRecordingSettings::create([
            'room_id' => $room->id,
            'recording_enabled' => true,
            'storage_provider' => 'google_drive',
            'storage_account_id' => $storageAccount->id,
        ]);

        // Create participant with STT consent denied but recording consent granted
        RoomParticipant::create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'character_name' => 'Test Character',
            'character_class' => 'Warrior',
            'stt_consent_given' => false,       // STT consent DENIED
            'stt_consent_at' => now(),
            'recording_consent_given' => true, // Recording consent GRANTED
            'recording_consent_at' => now(),
        ]);

        // Mock the Google Drive service
        $this->mock(\Domain\Room\Actions\GenerateGoogleDriveUploadUrl::class, function ($mock) {
            $mock->shouldReceive('execute')
                ->once()
                ->andReturn([
                    'success' => true,
                    'upload_url' => 'https://www.googleapis.com/upload/drive/v3/files/resumable?uploadType=resumable',
                    'session_uri' => 'https://www.googleapis.com/upload/drive/v3/files/resumable?uploadType=resumable',
                    'filename' => 'test-recording.webm',
                    'content_type' => 'video/webm',
                ]);
        });

        $response = $this->actingAs($user)
            ->postJson("/api/rooms/{$room->id}/recordings/google-drive-upload-url", [
                'filename' => 'test-recording.webm',
                'content_type' => 'video/webm',
                'size' => 1024 * 1024, // 1MB
                'metadata' => [
                    'started_at_ms' => now()->timestamp * 1000,
                    'ended_at_ms' => now()->addMinutes(5)->timestamp * 1000,
                ],
            ]);

        // Should succeed because recording consent is granted (STT consent irrelevant)
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'upload_url',
                'session_uri',
                'filename',
                'content_type',
            ]);
    });

    test('Google Drive upload confirmation requires recording consent', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        // Create Google Drive storage account
        $storageAccount = UserStorageAccount::factory()->create([
            'user_id' => $user->id,
            'provider' => 'google_drive',
        ]);

        // Enable recording with Google Drive
        RoomRecordingSettings::create([
            'room_id' => $room->id,
            'recording_enabled' => true,
            'storage_provider' => 'google_drive',
            'storage_account_id' => $storageAccount->id,
        ]);

        // Create participant WITHOUT recording consent
        RoomParticipant::create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'character_name' => 'Test Character',
            'character_class' => 'Warrior',
            'recording_consent_given' => false, // Recording consent DENIED
            'recording_consent_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/rooms/{$room->id}/recordings/confirm-google-drive", [
                'session_uri' => 'https://www.googleapis.com/upload/drive/v3/files/resumable?uploadType=resumable',
                'metadata' => [
                    'filename' => 'test-recording.webm',
                    'started_at_ms' => now()->timestamp * 1000,
                    'ended_at_ms' => now()->addMinutes(5)->timestamp * 1000,
                ],
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Video recording consent required',
                'requires_consent' => true,
            ]);
    });
});

describe('STT Consent Validation Independence', function () {
    test('STT endpoints check STT consent not recording consent', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        // Enable STT for the room
        RoomRecordingSettings::create([
            'room_id' => $room->id,
            'recording_enabled' => false,
            'stt_enabled' => true,
        ]);

        // Create participant with recording consent but NO STT consent
        RoomParticipant::create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'character_name' => 'Test Character',
            'character_class' => 'Warrior',
            'stt_consent_given' => null,        // NO STT consent
            'recording_consent_given' => true, // Recording consent granted (irrelevant)
            'recording_consent_at' => now(),
        ]);

        // Test STT consent status endpoint
        $response = $this->actingAs($user)
            ->getJson("/api/rooms/{$room->id}/stt-consent");

        $response->assertStatus(200)
            ->assertJson([
                'stt_enabled' => true,
                'requires_consent' => true,
                'consent_given' => false,  // Should be false because STT consent not given
                'consent_denied' => false,
            ]);
    });

    test('video recording endpoints check recording consent not STT consent', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        // Enable recording for the room
        RoomRecordingSettings::create([
            'room_id' => $room->id,
            'recording_enabled' => true,
            'storage_provider' => 'local_device',
        ]);

        // Create participant with STT consent but NO recording consent
        RoomParticipant::create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'character_name' => 'Test Character',
            'character_class' => 'Warrior',
            'stt_consent_given' => true,         // STT consent granted (irrelevant)
            'stt_consent_at' => now(),
            'recording_consent_given' => null,  // NO recording consent
        ]);

        // Test recording consent status endpoint
        $response = $this->actingAs($user)
            ->getJson("/api/rooms/{$room->id}/recording-consent");

        $response->assertStatus(200)
            ->assertJson([
                'recording_enabled' => true,
                'requires_consent' => true,
                'consent_given' => false,  // Should be false because recording consent not given
                'consent_denied' => false,
            ]);
    });
});
