<?php

declare(strict_types=1);

use Domain\Room\Actions\GenerateWasabiPresignedUrl;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('GenerateWasabiPresignedUrl Action', function () {
    test('can generate presigned URL for room with Wasabi storage', function () {
        // Skip if no real Wasabi credentials (this test requires live credentials)
        if (empty(config('services.wasabi.access_key')) || empty(config('services.wasabi.secret_key'))) {
            $this->markTestSkipped('Wasabi credentials not available - test requires live credentials for bucket auto-creation');
        }

        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        $storageAccount = UserStorageAccount::factory()->wasabi()->create([
            'user_id' => $user->id,
            'display_name' => 'Test Wasabi Storage',
            'encrypted_credentials' => [
                'access_key_id' => config('services.wasabi.access_key'),
                'secret_access_key' => config('services.wasabi.secret_key'),
                'region' => env('WASABI_REGION', 'us-east-1'),
                'endpoint' => env('WASABI_ENDPOINT', 'https://s3.wasabisys.com'),
                'bucket_name' => 'test-action-bucket-'.time().'-'.rand(1000, 9999),
            ],
        ]);

        $recordingSettings = RoomRecordingSettings::factory()->create([
            'room_id' => $room->id,
            'recording_enabled' => true,
            'storage_provider' => 'wasabi',
            'storage_account_id' => $storageAccount->id,
        ]);

        $room->refresh();

        $action = new GenerateWasabiPresignedUrl;

        $result = $action->execute(
            $room,
            $user,
            'test-recording.webm',
            'video/webm',
            1024 * 1024, // 1MB
            ['custom_meta' => 'test_value']
        );

        expect($result)->toBeArray();
        expect($result['success'])->toBeTrue();
        expect($result)->toHaveKeys([
            'presigned_url', 'key', 'bucket', 'expires_at', 'headers', 'metadata',
        ]);

        // Verify URL structure
        expect($result['presigned_url'])->toContain('https://');
        expect($result['presigned_url'])->toContain($storageAccount->encrypted_credentials['bucket_name']);
        expect($result['bucket'])->toBe($storageAccount->encrypted_credentials['bucket_name']);

        // Verify key format follows expected pattern
        expect($result['key'])->toContain('recordings/');
        expect($result['key'])->toContain("room_{$room->id}");
        expect($result['key'])->toContain("user_{$user->id}");
        expect($result['key'])->toContain('test-recording.webm');

        // Verify headers
        expect($result['headers'])->toHaveKey('Content-Type');
        expect($result['headers']['Content-Type'])->toBe('video/webm');

        // Verify metadata
        expect($result['metadata'])->toHaveKeys([
            'provider', 'provider_file_id', 'storage_account_id',
            'room_id', 'user_id', 'filename', 'size_bytes', 'content_type', 'custom_meta',
        ]);
        expect($result['metadata']['provider'])->toBe('wasabi');
        expect($result['metadata']['room_id'])->toBe($room->id);
        expect($result['metadata']['user_id'])->toBe($user->id);
        expect($result['metadata']['custom_meta'])->toBe('test_value');
    })->skip(fn () => app()->environment('testing') && empty(config('services.wasabi.access_key')),
        'Wasabi credentials not configured for testing');

    test('fails when recording is not enabled', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        $storageAccount = UserStorageAccount::factory()->wasabi()->create(['user_id' => $user->id]);

        RoomRecordingSettings::factory()->create([
            'room_id' => $room->id,
            'recording_enabled' => false, // Recording disabled
            'storage_provider' => 'wasabi',
            'storage_account_id' => $storageAccount->id,
        ]);

        $room->refresh();
        $action = new GenerateWasabiPresignedUrl;

        expect(fn () => $action->execute($room, $user, 'test.webm', 'video/webm', 1024))
            ->toThrow(\Exception::class, 'Video recording is not enabled for this room');
    });

    test('fails when storage provider is not wasabi', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        RoomRecordingSettings::factory()->create([
            'room_id' => $room->id,
            'recording_enabled' => true,
            'storage_provider' => 'google_drive', // Not Wasabi
            'storage_account_id' => null,
        ]);

        $room->refresh();
        $action = new GenerateWasabiPresignedUrl;

        expect(fn () => $action->execute($room, $user, 'test.webm', 'video/webm', 1024))
            ->toThrow(\Exception::class, 'Room is not configured for Wasabi storage');
    });

    test('fails when storage account does not belong to room creator', function () {
        $creator = User::factory()->create();
        $otherUser = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $creator->id]);

        $storageAccount = UserStorageAccount::factory()->wasabi()->create([
            'user_id' => $otherUser->id, // Different user
        ]);

        RoomRecordingSettings::factory()->create([
            'room_id' => $room->id,
            'recording_enabled' => true,
            'storage_provider' => 'wasabi',
            'storage_account_id' => $storageAccount->id,
        ]);

        $room->refresh();
        $action = new GenerateWasabiPresignedUrl;

        expect(fn () => $action->execute($room, $creator, 'test.webm', 'video/webm', 1024))
            ->toThrow(\Exception::class, 'Storage account does not belong to room creator');
    });

    test('fails when file size exceeds limit', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        $storageAccount = UserStorageAccount::factory()->wasabi()->create(['user_id' => $user->id]);

        RoomRecordingSettings::factory()->create([
            'room_id' => $room->id,
            'recording_enabled' => true,
            'storage_provider' => 'wasabi',
            'storage_account_id' => $storageAccount->id,
        ]);

        $room->refresh();
        $action = new GenerateWasabiPresignedUrl;

        $oversizedFile = 101 * 1024 * 1024; // 101MB (over 100MB limit)

        expect(fn () => $action->execute($room, $user, 'huge.webm', 'video/webm', $oversizedFile))
            ->toThrow(\Exception::class, 'File size exceeds maximum allowed size of 100MB');
    });

    test('fails when content type is not allowed', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        $storageAccount = UserStorageAccount::factory()->wasabi()->create(['user_id' => $user->id]);

        RoomRecordingSettings::factory()->create([
            'room_id' => $room->id,
            'recording_enabled' => true,
            'storage_provider' => 'wasabi',
            'storage_account_id' => $storageAccount->id,
        ]);

        $room->refresh();
        $action = new GenerateWasabiPresignedUrl;

        expect(fn () => $action->execute($room, $user, 'test.txt', 'text/plain', 1024))
            ->toThrow(\Exception::class, 'File type not allowed. Only WebM, MP4, and QuickTime videos are supported.');
    });

    test('validates filename constraints', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        $storageAccount = UserStorageAccount::factory()->wasabi()->create(['user_id' => $user->id]);

        RoomRecordingSettings::factory()->create([
            'room_id' => $room->id,
            'recording_enabled' => true,
            'storage_provider' => 'wasabi',
            'storage_account_id' => $storageAccount->id,
        ]);

        $room->refresh();
        $action = new GenerateWasabiPresignedUrl;

        // Test empty filename
        expect(fn () => $action->execute($room, $user, '', 'video/webm', 1024))
            ->toThrow(\Exception::class, 'Invalid filename. Must be between 1 and 255 characters.');

        // Test filename with path traversal
        expect(fn () => $action->execute($room, $user, '../test.webm', 'video/webm', 1024))
            ->toThrow(\Exception::class, 'Invalid filename. Path separators are not allowed.');

        // Test filename with forward slash
        expect(fn () => $action->execute($room, $user, 'folder/test.webm', 'video/webm', 1024))
            ->toThrow(\Exception::class, 'Invalid filename. Path separators are not allowed.');
    });

    test('works with all allowed content types', function () {
        // Skip if no real Wasabi credentials (this test requires live credentials)
        if (empty(config('services.wasabi.access_key')) || empty(config('services.wasabi.secret_key'))) {
            $this->markTestSkipped('Wasabi credentials not available - test requires live credentials for bucket auto-creation');
        }

        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        $storageAccount = UserStorageAccount::factory()->wasabi()->create([
            'user_id' => $user->id,
            'encrypted_credentials' => [
                'access_key_id' => config('services.wasabi.access_key'),
                'secret_access_key' => config('services.wasabi.secret_key'),
                'region' => env('WASABI_REGION', 'us-east-1'),
                'endpoint' => env('WASABI_ENDPOINT', 'https://s3.wasabisys.com'),
                'bucket_name' => 'test-content-types-'.time().'-'.rand(1000, 9999),
            ],
        ]);

        RoomRecordingSettings::factory()->create([
            'room_id' => $room->id,
            'recording_enabled' => true,
            'storage_provider' => 'wasabi',
            'storage_account_id' => $storageAccount->id,
        ]);

        $room->refresh();
        $action = new GenerateWasabiPresignedUrl;

        $allowedTypes = [
            ['filename' => 'test.webm', 'content_type' => 'video/webm'],
            ['filename' => 'test.mp4', 'content_type' => 'video/mp4'],
            ['filename' => 'test.mov', 'content_type' => 'video/quicktime'],
        ];

        foreach ($allowedTypes as $fileType) {
            $result = $action->execute(
                $room,
                $user,
                $fileType['filename'],
                $fileType['content_type'],
                1024 * 1024
            );

            expect($result['success'])->toBeTrue();
            expect($result['headers']['Content-Type'])->toBe($fileType['content_type']);
            expect($result['metadata']['content_type'])->toBe($fileType['content_type']);
        }
    })->skip(fn () => app()->environment('testing') && empty(config('services.wasabi.access_key')),
        'Wasabi credentials not configured for testing');
});
