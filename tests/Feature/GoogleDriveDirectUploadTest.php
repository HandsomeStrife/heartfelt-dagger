<?php

use Illuminate\Support\Facades\Http;
use Domain\Room\Actions\GenerateGoogleDriveUploadUrl;
use Domain\Room\Actions\ConfirmGoogleDriveUpload;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecording;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->room = Room::factory()->create(['creator_id' => $this->user->id]);
    
    // Create recording settings for Google Drive
    $this->recordingSettings = RoomRecordingSettings::factory()->create([
        'room_id' => $this->room->id,
        'recording_enabled' => true,
        'stt_enabled' => true,
        'storage_provider' => 'google_drive',
        'storage_account_id' => null, // Will be set in tests
    ]);
    
    $this->room->setRelation('recordingSettings', $this->recordingSettings);
    
    // Create Google Drive storage account
    $this->storageAccount = UserStorageAccount::factory()->create([
        'user_id' => $this->user->id,
        'provider' => 'google_drive',
        'is_active' => true,
        'encrypted_credentials' => [
            'refresh_token' => 'test_refresh_token',
            'access_token' => 'test_access_token',
            'expires_in' => 3600,
            'created_at' => now()->timestamp - 1800, // 30 minutes ago
        ],
    ]);
    
    // Link storage account to room settings
    $this->recordingSettings->update(['storage_account_id' => $this->storageAccount->id]);
    
    // Create participant with consent
    RoomParticipant::factory()->create([
        'room_id' => $this->room->id,
        'user_id' => $this->user->id,
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
        'left_at' => null,
    ]);
});

describe('GenerateGoogleDriveUploadUrl Action', function () {
    test('generates upload URL successfully', function () {
        $action = new GenerateGoogleDriveUploadUrl();
        
        // Mock Google Drive service response
        Http::fake([
            'https://www.googleapis.com/upload/drive/v3/files*' => Http::response('', 200, [
                'Location' => 'https://www.googleapis.com/upload/drive/v3/files/resumable?uploadType=resumable&upload_id=test123'
            ]),
            'https://www.googleapis.com/oauth2/v4/token' => Http::response([
                'access_token' => 'new_access_token',
                'expires_in' => 3600,
            ]),
        ]);

        $result = $action->execute(
            $this->room,
            $this->user,
            'test-recording.webm',
            'video/webm',
            1048576, // 1MB
            ['started_at_ms' => 1000, 'ended_at_ms' => 2000]
        );

        expect($result)->toBeArray();
        expect($result['success'])->toBe(true);
        expect($result)->toHaveKeys(['upload_url', 'session_uri', 'filename', 'expires_at', 'metadata']);
        expect($result['upload_url'])->toContain('googleapis.com');
        expect($result['metadata']['provider'])->toBe('google_drive');
        expect($result['metadata']['room_id'])->toBe($this->room->id);
        expect($result['metadata']['user_id'])->toBe($this->user->id);
    });

    test('validates recording is enabled', function () {
        $this->recordingSettings->update(['recording_enabled' => false]);
        
        $action = new GenerateGoogleDriveUploadUrl();
        
        expect(fn() => $action->execute(
            $this->room,
            $this->user,
            'test.webm',
            'video/webm',
            1048576
        ))->toThrow(Exception::class, 'Video recording is not enabled');
    });

    test('validates storage provider', function () {
        $this->recordingSettings->update(['storage_provider' => 'wasabi']);
        
        $action = new GenerateGoogleDriveUploadUrl();
        
        expect(fn() => $action->execute(
            $this->room,
            $this->user,
            'test.webm',
            'video/webm',
            1048576
        ))->toThrow(Exception::class, 'Room is not configured for Google Drive storage');
    });

    test('validates file constraints', function () {
        $action = new GenerateGoogleDriveUploadUrl();
        
        // Test oversized file
        expect(fn() => $action->execute(
            $this->room,
            $this->user,
            'huge.webm',
            'video/webm',
            3 * 1024 * 1024 * 1024 // 3GB
        ))->toThrow(Exception::class, 'File size exceeds maximum');
        
        // Test invalid content type
        expect(fn() => $action->execute(
            $this->room,
            $this->user,
            'test.txt',
            'text/plain',
            1048576
        ))->toThrow(Exception::class, 'Invalid content type');
        
        // Test empty filename
        expect(fn() => $action->execute(
            $this->room,
            $this->user,
            '',
            'video/webm',
            1048576
        ))->toThrow(Exception::class, 'Invalid filename');
    });

    test('validates storage account ownership', function () {
        // Create storage account owned by different user
        $otherUser = User::factory()->create();
        $otherStorageAccount = UserStorageAccount::factory()->create([
            'user_id' => $otherUser->id,
            'provider' => 'google_drive',
        ]);
        
        $this->recordingSettings->update(['storage_account_id' => $otherStorageAccount->id]);
        
        $action = new GenerateGoogleDriveUploadUrl();
        
        expect(fn() => $action->execute(
            $this->room,
            $this->user,
            'test.webm',
            'video/webm',
            1048576
        ))->toThrow(Exception::class, 'Storage account does not belong to room creator');
    });
});

describe('ConfirmGoogleDriveUpload Action', function () {
    test('confirms upload and creates recording record', function () {
        $action = new ConfirmGoogleDriveUpload();
        
        // Mock Google Drive verification response
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
        $metadata = [
            'filename' => 'test-recording.webm',
            'size_bytes' => 1048576,
            'content_type' => 'video/webm',
            'started_at_ms' => 1000,
            'ended_at_ms' => 2000,
        ];

        $result = $action->execute($this->room, $this->user, $sessionUri, $metadata);

        expect($result)->toBeArray();
        expect($result['success'])->toBe(true);
        expect($result)->toHaveKeys(['recording_id', 'provider_file_id', 'filename', 'web_view_link']);
        expect($result['provider_file_id'])->toBe('drive_file_123');

        // Verify database record was created
        $recording = RoomRecording::find($result['recording_id']);
        expect($recording)->not()->toBeNull();
        expect($recording->provider)->toBe('google_drive');
        expect($recording->provider_file_id)->toBe('drive_file_123');
        expect($recording->room_id)->toBe($this->room->id);
        expect($recording->user_id)->toBe($this->user->id);
        expect($recording->status)->toBe('uploaded');
    });

    test('validates recording is enabled', function () {
        $this->recordingSettings->update(['recording_enabled' => false]);
        
        $action = new ConfirmGoogleDriveUpload();
        
        expect(fn() => $action->execute(
            $this->room,
            $this->user,
            'https://googleapis.com/test',
            []
        ))->toThrow(Exception::class, 'Video recording is not enabled');
    });

    test('handles Google Drive API errors', function () {
        $action = new ConfirmGoogleDriveUpload();
        
        // Mock failed verification response
        Http::fake([
            'https://www.googleapis.com/upload/drive/v3/files/resumable*' => Http::response([
                'error' => ['message' => 'File not found']
            ], 404),
            'https://www.googleapis.com/oauth2/v4/token' => Http::response([
                'access_token' => 'new_access_token',
                'expires_in' => 3600,
            ]),
        ]);

        expect(fn() => $action->execute(
            $this->room,
            $this->user,
            'https://googleapis.com/invalid',
            []
        ))->toThrow(Exception::class);
    });
});

describe('GoogleDriveService Integration', function () {
    test('service generates upload URLs with valid tokens', function () {
        // Use a fresh token that won't need refreshing
        $storageAccount = UserStorageAccount::factory()->create([
            'user_id' => $this->user->id,
            'provider' => 'google_drive',
            'is_active' => true,
            'encrypted_credentials' => [
                'refresh_token' => 'test_refresh_token',
                'access_token' => 'valid_access_token',
                'expires_in' => 3600,
                'created_at' => now()->timestamp - 1800, // 30 minutes ago (still valid)
            ],
        ]);

        Http::fake([
            'https://www.googleapis.com/upload/drive/v3/files*' => Http::response('', 200, [
                'Location' => 'https://www.googleapis.com/upload/drive/v3/files/resumable?uploadType=resumable&upload_id=test123'
            ]),
        ]);

        $service = new \Domain\Room\Services\GoogleDriveService($storageAccount);
        
        $result = $service->generateDirectUploadUrl(
            'test.webm',
            'video/webm',
            1048576,
            ['room_id' => $this->room->id, 'user_id' => $this->user->id]
        );

        expect($result['success'])->toBe(true);
        expect($result['upload_url'])->toContain('googleapis.com');
        expect($result['session_uri'])->toContain('upload_id=test123');
    });
});
