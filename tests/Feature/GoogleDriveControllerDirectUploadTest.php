<?php

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->room = Room::factory()->create(['creator_id' => $this->user->id]);

    // Create recording settings for Google Drive
    $this->recordingSettings = RoomRecordingSettings::factory()->create([
        'room_id' => $this->room->id,
        'recording_enabled' => true,
        'stt_enabled' => true,
        'storage_provider' => 'google_drive',
        'storage_account_id' => null,
    ]);

    // Create Google Drive storage account
    $this->storageAccount = UserStorageAccount::factory()->create([
        'user_id' => $this->user->id,
        'provider' => 'google_drive',
        'is_active' => true,
        'encrypted_credentials' => [
            'refresh_token' => 'test_refresh_token',
            'access_token' => 'test_access_token',
            'expires_in' => 3600,
            'created_at' => now()->timestamp - 1800,
        ],
    ]);

    $this->recordingSettings->update(['storage_account_id' => $this->storageAccount->id]);

    // Create participant with recording consent
    RoomParticipant::factory()->create([
        'room_id' => $this->room->id,
        'user_id' => $this->user->id,
        'recording_consent_given' => true,
        'recording_consent_at' => now(),
        'left_at' => null,
    ]);
});

describe('Google Drive Upload URL Generation API', function () {
    test('generates upload URL successfully', function () {
        Http::fake([
            'https://www.googleapis.com/upload/drive/v3/files*' => Http::response('', 200, [
                'Location' => 'https://www.googleapis.com/upload/drive/v3/files/resumable?uploadType=resumable&upload_id=test123',
            ]),
            'https://www.googleapis.com/oauth2/v4/token' => Http::response([
                'access_token' => 'new_access_token',
                'expires_in' => 3600,
            ]),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/rooms/{$this->room->id}/recordings/google-drive-upload-url", [
                'filename' => 'test-recording.webm',
                'content_type' => 'video/webm',
                'size' => 1048576,
                'metadata' => [
                    'started_at_ms' => 1000,
                    'ended_at_ms' => 2000,
                ],
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'upload_url',
                'session_uri',
                'filename',
                'expires_at',
                'metadata' => [
                    'provider',
                    'storage_account_id',
                    'room_id',
                    'user_id',
                    'filename',
                    'size_bytes',
                    'content_type',
                    'session_uri',
                ],
            ])
            ->assertJson([
                'success' => true,
                'metadata' => [
                    'provider' => 'google_drive',
                    'room_id' => $this->room->id,
                    'user_id' => $this->user->id,
                ],
            ]);

        expect($response->json('upload_url'))->toContain('googleapis.com');
        expect($response->json('session_uri'))->toContain('upload_id=test123');
    });

    test('validates required fields', function () {
        $response = $this->actingAs($this->user)
            ->postJson("/api/rooms/{$this->room->id}/recordings/google-drive-upload-url", []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'error',
                'messages' => [
                    'filename',
                    'content_type',
                    'size',
                ],
            ]);
    });

    test('validates content type', function () {
        $response = $this->actingAs($this->user)
            ->postJson("/api/rooms/{$this->room->id}/recordings/google-drive-upload-url", [
                'filename' => 'test.txt',
                'content_type' => 'text/plain',
                'size' => 1048576,
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['error', 'messages']);
    });

    test('validates file size limits', function () {
        $response = $this->actingAs($this->user)
            ->postJson("/api/rooms/{$this->room->id}/recordings/google-drive-upload-url", [
                'filename' => 'huge.webm',
                'content_type' => 'video/webm',
                'size' => 3 * 1024 * 1024 * 1024, // 3GB
            ]);

        $response->assertStatus(422);
    });

    test('requires authentication', function () {
        $response = $this->postJson("/api/rooms/{$this->room->id}/recordings/google-drive-upload-url", [
            'filename' => 'test.webm',
            'content_type' => 'video/webm',
            'size' => 1048576,
        ]);

        $response->assertStatus(403); // Laravel returns 403 when room access check fails before auth check
    });

    test('requires room access', function () {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->postJson("/api/rooms/{$this->room->id}/recordings/google-drive-upload-url", [
                'filename' => 'test.webm',
                'content_type' => 'video/webm',
                'size' => 1048576,
            ]);

        $response->assertStatus(403)
            ->assertJson(['error' => 'Only room participants can upload recordings']);
    });

    test('requires recording consent', function () {
        // Create user without consent
        $userWithoutConsent = User::factory()->create();
        RoomParticipant::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $userWithoutConsent->id,
            'recording_consent_given' => false,
            'recording_consent_at' => null,
            'left_at' => null,
        ]);

        $response = $this->actingAs($userWithoutConsent)
            ->postJson("/api/rooms/{$this->room->id}/recordings/google-drive-upload-url", [
                'filename' => 'test.webm',
                'content_type' => 'video/webm',
                'size' => 1048576,
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Video recording consent required',
                'requires_consent' => true,
            ]);
    });

    test('requires recording to be enabled', function () {
        $this->recordingSettings->update(['recording_enabled' => false]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/rooms/{$this->room->id}/recordings/google-drive-upload-url", [
                'filename' => 'test.webm',
                'content_type' => 'video/webm',
                'size' => 1048576,
            ]);

        $response->assertStatus(500)
            ->assertJsonStructure(['error']);
    });

    test('requires Google Drive storage provider', function () {
        $this->recordingSettings->update(['storage_provider' => 'wasabi']);

        $response = $this->actingAs($this->user)
            ->postJson("/api/rooms/{$this->room->id}/recordings/google-drive-upload-url", [
                'filename' => 'test.webm',
                'content_type' => 'video/webm',
                'size' => 1048576,
            ]);

        $response->assertStatus(500)
            ->assertJsonStructure(['error']);
    });
});

describe('Google Drive Upload Confirmation API', function () {
    test('confirms upload successfully', function () {
        Http::fake([
            'https://www.googleapis.com/upload/drive/v3/files/resumable*' => Http::response([
                'id' => 'drive_file_123',
                'name' => 'test-recording.webm',
                'size' => '1048576',
                'webViewLink' => 'https://drive.google.com/file/d/drive_file_123/view',
                'webContentLink' => 'https://drive.google.com/uc?id=drive_file_123',
                'createdTime' => '2024-01-01T12:00:00.000Z',
                'mimeType' => 'video/webm',
            ]),
            'https://www.googleapis.com/oauth2/v4/token' => Http::response([
                'access_token' => 'new_access_token',
                'expires_in' => 3600,
            ]),
        ]);

        $sessionUri = 'https://www.googleapis.com/upload/drive/v3/files/resumable?uploadType=resumable&upload_id=test123';

        $response = $this->actingAs($this->user)
            ->postJson("/api/rooms/{$this->room->id}/recordings/confirm-google-drive", [
                'session_uri' => $sessionUri,
                'metadata' => [
                    'filename' => 'test-recording.webm',
                    'size_bytes' => 1048576,
                    'content_type' => 'video/webm',
                    'started_at_ms' => 1000,
                    'ended_at_ms' => 2000,
                ],
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'recording_id',
                'provider_file_id',
                'web_view_link',
            ])
            ->assertJson([
                'success' => true,
                'provider_file_id' => 'drive_file_123',
            ]);

        // Verify database record was created
        $this->assertDatabaseHas('room_recordings', [
            'room_id' => $this->room->id,
            'user_id' => $this->user->id,
            'provider' => 'google_drive',
            'provider_file_id' => 'drive_file_123',
            'status' => 'uploaded',
        ]);
    });

    test('validates required fields', function () {
        $response = $this->actingAs($this->user)
            ->postJson("/api/rooms/{$this->room->id}/recordings/confirm-google-drive", []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'error',
                'messages' => [
                    'session_uri',
                ],
            ]);
    });

    test('validates session URI format', function () {
        $response = $this->actingAs($this->user)
            ->postJson("/api/rooms/{$this->room->id}/recordings/confirm-google-drive", [
                'session_uri' => 'invalid-uri',
            ]);

        $response->assertStatus(422);
    });

    test('handles Google Drive API errors', function () {
        Http::fake([
            'https://www.googleapis.com/upload/drive/v3/files/resumable*' => Http::response([
                'error' => ['message' => 'File not found'],
            ], 404),
            'https://www.googleapis.com/oauth2/v4/token' => Http::response([
                'access_token' => 'new_access_token',
                'expires_in' => 3600,
            ]),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/rooms/{$this->room->id}/recordings/confirm-google-drive", [
                'session_uri' => 'https://googleapis.com/invalid',
            ]);

        $response->assertStatus(500)
            ->assertJsonStructure(['error']);
    });

    test('requires authentication', function () {
        $response = $this->postJson("/api/rooms/{$this->room->id}/recordings/confirm-google-drive", [
            'session_uri' => 'https://googleapis.com/test',
        ]);

        $response->assertStatus(403); // Laravel returns 403 when room access check fails before auth check
    });

    test('requires room access', function () {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->postJson("/api/rooms/{$this->room->id}/recordings/confirm-google-drive", [
                'session_uri' => 'https://googleapis.com/test',
            ]);

        $response->assertStatus(403)
            ->assertJson(['error' => 'Only room participants can confirm uploads']);
    });

    test('requires recording consent', function () {
        // Create user without consent
        $userWithoutConsent = User::factory()->create();
        RoomParticipant::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $userWithoutConsent->id,
            'recording_consent_given' => false,
            'recording_consent_at' => null,
            'left_at' => null,
        ]);

        $response = $this->actingAs($userWithoutConsent)
            ->postJson("/api/rooms/{$this->room->id}/recordings/confirm-google-drive", [
                'session_uri' => 'https://googleapis.com/test',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Video recording consent required',
                'requires_consent' => true,
            ]);
    });
});
