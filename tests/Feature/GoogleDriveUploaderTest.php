<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('google drive upload url generation succeeds with valid storage account', function () {
    // Create test data
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    // Create Google Drive storage account
    $storageAccount = UserStorageAccount::factory()->create([
        'user_id' => $user->id,
        'provider' => 'google_drive',
        'encrypted_credentials' => [
            'access_token' => 'fake_access_token',
            'refresh_token' => 'fake_refresh_token',
            'expires_in' => 3600,
            'token_type' => 'Bearer',
            'created_at' => time(),
        ],
        'is_active' => true,
    ]);

    // Create room recording settings with Google Drive
    RoomRecordingSettings::factory()->create([
        'room_id' => $room->id,
        'storage_provider' => 'google_drive',
        'storage_account_id' => $storageAccount->id,
    ]);

    // Mock Google Drive API responses
    Http::fake([
        'https://www.googleapis.com/upload/drive/v3/files*' => Http::response('', 200, [
            'Location' => 'https://www.googleapis.com/upload/drive/v3/files/upload/session_abc123'
        ]),
        'https://www.googleapis.com/drive/v3/files*' => Http::response([
            'files' => [
                ['id' => 'folder_123', 'name' => 'Test Folder']
            ]
        ], 200),
    ]);

    // Test the API endpoint
    $response = $this->actingAs($user)
        ->postJson("/api/rooms/{$room->id}/recordings/google-drive-upload-url", [
            'filename' => 'test-recording.webm',
            'content_type' => 'video/webm',
            'size' => 1000000,
            'metadata' => [
                'started_at_ms' => time() * 1000,
                'ended_at_ms' => (time() + 60) * 1000,
            ]
        ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'upload_url',
        'session_uri',
        'filename',
        'expires_at',
        'metadata'
    ]);

    expect($response->json('success'))->toBeTrue();
    expect($response->json('session_uri'))->toContain('googleapis.com');
    expect($response->json('upload_url'))->toContain('googleapis.com');
});

test('google drive upload url generation fails without storage account', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);

    // No recording settings created

    $response = $this->actingAs($user)
        ->postJson("/api/rooms/{$room->id}/recordings/google-drive-upload-url", [
            'filename' => 'test-recording.webm',
            'content_type' => 'video/webm',
            'size' => 1000000,
        ]);

    $response->assertStatus(422);
});

test('google drive upload url generation fails with invalid credentials', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    // Create storage account with invalid credentials
    $storageAccount = UserStorageAccount::factory()->create([
        'user_id' => $user->id,
        'provider' => 'google_drive',
        'encrypted_credentials' => [
            'access_token' => 'invalid_token',
            'refresh_token' => 'invalid_refresh_token',
            'created_at' => time(),
        ],
        'is_active' => true,
    ]);

    RoomRecordingSettings::factory()->create([
        'room_id' => $room->id,
        'storage_provider' => 'google_drive',
        'storage_account_id' => $storageAccount->id,
    ]);

    // Mock Google Drive API to return 401
    Http::fake([
        'https://www.googleapis.com/upload/drive/v3/files*' => Http::response([
            'error' => [
                'code' => 401,
                'message' => 'Invalid credentials'
            ]
        ], 401),
    ]);

    $response = $this->actingAs($user)
        ->postJson("/api/rooms/{$room->id}/recordings/google-drive-upload-url", [
            'filename' => 'test-recording.webm',
            'content_type' => 'video/webm',
            'size' => 1000000,
        ]);

    $response->assertStatus(500);
});

test('google drive service generates valid upload url', function () {
    $storageAccount = UserStorageAccount::factory()->create([
        'provider' => 'google_drive',
        'encrypted_credentials' => [
            'access_token' => 'test_access_token',
            'refresh_token' => 'test_refresh_token',
            'expires_in' => 3600,
            'created_at' => time(),
        ],
    ]);

    // Mock HTTP response for resumable upload session
    Http::fake([
        'https://www.googleapis.com/upload/drive/v3/files*' => Http::response('', 200, [
            'Location' => 'https://www.googleapis.com/upload/drive/v3/files/upload/session_xyz789'
        ]),
        'https://oauth2.googleapis.com/token' => Http::response([
            'access_token' => 'new_access_token',
            'expires_in' => 3600,
        ], 200),
    ]);

    $service = new \Domain\Room\Services\GoogleDriveService($storageAccount);
    
    $result = $service->generateDirectUploadUrl(
        'test-file.webm',
        'video/webm',
        1000000,
        ['folder_id' => 'test_folder_id']
    );

    expect($result)->toHaveKeys(['session_uri', 'upload_url', 'expires_at', 'metadata']);
    expect($result['session_uri'])->toContain('session_xyz789');
    expect($result['upload_url'])->toBe($result['session_uri']);
    expect($result['expires_at'])->toBeGreaterThan(time());
    expect($result['metadata']['folder_id'])->toBe('test_folder_id');
});
