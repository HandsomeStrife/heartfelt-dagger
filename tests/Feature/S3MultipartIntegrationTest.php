<?php

declare(strict_types=1);

use Aws\S3\S3Client;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecording;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;
use GuzzleHttp\Client as HttpClient;

use function Pest\Laravel\actingAs;

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

describe('S3 Multipart Integration Tests', function () {
    test('complete multipart upload flow with 3 parts', function () {
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

        // Add user as participant with consent
        $room->participants()->create([
            'user_id' => $user->id,
            'character_name' => 'Test Character',
            'stt_consent_given' => true,
            'stt_consent_at' => now(),
        ]);

        // Step 1: Create multipart upload
        $createResponse = actingAs($user)
            ->withHeaders(['X-CSRF-TOKEN' => csrf_token()])
            ->postJson('/api/uploads/s3/multipart/create', [
                'filename' => 'integration_test.webm',
                'type' => 'video/webm',
                'size' => 3 * 1024, // 3KB total
                'room_id' => $room->id,
                'started_at_ms' => 1234567890000,
                'ended_at_ms' => 1234567920000,
            ]);

        $createResponse->assertStatus(200);
        $createData = $createResponse->json();

        $uploadId = $createData['uploadId'];
        $key = $createData['key'];

        expect($uploadId)->toBeString();
        expect($key)->toStartWith("rooms/{$room->id}/users/{$user->id}/");

        // Step 2: Sign and upload 3 parts
        $httpClient = new HttpClient;
        $parts = [];

        for ($partNumber = 1; $partNumber <= 3; $partNumber++) {
            // Get presigned URL for this part
            $signResponse = actingAs($user)
                ->withHeaders(['X-CSRF-TOKEN' => csrf_token()])
                ->postJson('/api/uploads/s3/multipart/sign', [
                    'uploadId' => $uploadId,
                    'key' => $key,
                    'partNumber' => $partNumber,
                    'room_id' => $room->id,
                ]);

            $signResponse->assertStatus(200);
            $signData = $signResponse->json();

            expect($signData['url'])->toBeString();
            expect($signData['url'])->toContain('minio:9000');

            // Upload part data
            $partData = str_repeat("Part {$partNumber} data ", 32); // ~512 bytes per part
            $uploadResponse = $httpClient->put($signData['url'], [
                'body' => $partData,
                'headers' => [
                    'Content-Type' => 'video/webm',
                ],
            ]);

            expect($uploadResponse->getStatusCode())->toBe(200);

            // Extract ETag from response
            $etag = $uploadResponse->getHeader('ETag')[0] ?? '';
            expect($etag)->not()->toBeEmpty();

            $parts[] = [
                'PartNumber' => $partNumber,
                'ETag' => $etag, // Keep quotes as AWS expects them
            ];
        }

        // Step 3: Complete multipart upload
        $completeResponse = actingAs($user)
            ->withHeaders(['X-CSRF-TOKEN' => csrf_token()])
            ->postJson('/api/uploads/s3/multipart/complete', [
                'uploadId' => $uploadId,
                'key' => $key,
                'parts' => $parts,
                'room_id' => $room->id,
                'filename' => 'integration_test.webm',
                'mime' => 'video/webm',
                'started_at_ms' => 1234567890000,
                'ended_at_ms' => 1234567920000,
            ]);

        // Debug completion failure
        if ($completeResponse->status() !== 200) {
            dump('Complete response status: '.$completeResponse->status());
            dump('Complete response content: '.$completeResponse->content());
            dump('Parts submitted:', $parts);
        }

        $completeResponse->assertStatus(200);
        $completeData = $completeResponse->json();

        expect($completeData['key'])->toBe($key);
        expect($completeData['size'])->toBeGreaterThan(0);
        expect($completeData['recording_id'])->toBeInt();

        // Step 4: Verify database record was created
        $recording = RoomRecording::find($completeData['recording_id']);
        expect($recording)->not()->toBeNull();
        expect($recording->room_id)->toBe($room->id);
        expect($recording->user_id)->toBe($user->id);
        expect($recording->provider)->toBe('wasabi');
        expect($recording->provider_file_id)->toBe($key);
        expect($recording->size_bytes)->toBeGreaterThan(0);
        expect($recording->status)->toBe('uploaded');

        // Step 5: Verify object exists in MinIO
        $s3Client = new S3Client([
            'version' => 'latest',
            'region' => 'us-east-1',
            'endpoint' => 'http://minio:9000',
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key' => 'minioadmin',
                'secret' => 'minioadmin',
            ],
        ]);

        $headResponse = $s3Client->headObject([
            'Bucket' => 'recordings',
            'Key' => $key,
        ]);

        expect($headResponse['ContentLength'])->toBeGreaterThan(0);
        expect($headResponse['ContentType'])->toBe('video/webm');
    });

    test('abort flow prevents completion', function () {
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

        // Add user as participant with consent
        $room->participants()->create([
            'user_id' => $user->id,
            'character_name' => 'Test Character',
            'stt_consent_given' => true,
            'stt_consent_at' => now(),
        ]);

        // Step 1: Create multipart upload
        $createResponse = actingAs($user)
            ->withHeaders(['X-CSRF-TOKEN' => csrf_token()])
            ->postJson('/api/uploads/s3/multipart/create', [
                'filename' => 'abort_test.webm',
                'type' => 'video/webm',
                'room_id' => $room->id,
            ]);

        $createResponse->assertStatus(200);
        $createData = $createResponse->json();

        $uploadId = $createData['uploadId'];
        $key = $createData['key'];

        // Step 2: Abort the upload
        $abortResponse = actingAs($user)
            ->withHeaders(['X-CSRF-TOKEN' => csrf_token()])
            ->postJson('/api/uploads/s3/multipart/abort', [
                'uploadId' => $uploadId,
                'key' => $key,
                'room_id' => $room->id,
            ]);

        $abortResponse->assertStatus(200)
            ->assertJson(['aborted' => true]);

        // Step 3: Try to complete (should fail)
        $completeResponse = actingAs($user)
            ->withHeaders(['X-CSRF-TOKEN' => csrf_token()])
            ->postJson('/api/uploads/s3/multipart/complete', [
                'uploadId' => $uploadId,
                'key' => $key,
                'parts' => [
                    ['PartNumber' => 1, 'ETag' => 'fake-etag'],
                ],
                'room_id' => $room->id,
            ]);

        $completeResponse->assertStatus(502); // Should fail at S3 level

        // Step 4: Verify no database record was created
        $recording = RoomRecording::where('provider_file_id', $key)->first();
        expect($recording)->toBeNull();

        // Step 5: Verify object does not exist in MinIO
        $s3Client = new S3Client([
            'version' => 'latest',
            'region' => 'us-east-1',
            'endpoint' => 'http://minio:9000',
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key' => 'minioadmin',
                'secret' => 'minioadmin',
            ],
        ]);

        try {
            $s3Client->headObject([
                'Bucket' => 'recordings',
                'Key' => $key,
            ]);
            // Should not reach here
            expect(false)->toBeTrue('Object should not exist after abort');
        } catch (\Aws\S3\Exception\S3Exception $e) {
            // Expected - object should not exist
            expect($e->getStatusCode())->toBe(404);
        }
    });

    test('handles large multipart upload with many parts', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);
        $storageAccount = UserStorageAccount::create([
            'user_id' => $user->id,
            'provider' => 'wasabi',
            'display_name' => 'Test MinIO Storage',
            'is_active' => true,
            'encrypted_credentials' => [
                'access_key_id' => 'minioadmin',
                'secret_access_key' => 'minioadmin',
                'bucket_name' => 'recordings',
                'region' => 'us-east-1',
                'endpoint' => 'http://minio:9000',
            ],
        ]);

        RoomRecordingSettings::create([
            'room_id' => $room->id,
            'recording_enabled' => true,
            'storage_provider' => 'wasabi',
            'storage_account_id' => $storageAccount->id,
        ]);

        // Add user as participant with consent
        $room->participants()->create([
            'user_id' => $user->id,
            'character_name' => 'Test Character',
            'stt_consent_given' => true,
            'stt_consent_at' => now(),
        ]);

        // Create multipart upload
        $createResponse = actingAs($user)
            ->withHeaders(['X-CSRF-TOKEN' => csrf_token()])
            ->postJson('/api/uploads/s3/multipart/create', [
                'filename' => 'large_test.webm',
                'type' => 'video/webm',
                'room_id' => $room->id,
            ]);

        $createResponse->assertStatus(200);
        $createData = $createResponse->json();

        $uploadId = $createData['uploadId'];
        $key = $createData['key'];

        // Upload 20 small parts to test sorting and stability
        $httpClient = new HttpClient;
        $parts = [];

        for ($partNumber = 1; $partNumber <= 20; $partNumber++) {
            $signResponse = actingAs($user)
                ->withHeaders(['X-CSRF-TOKEN' => csrf_token()])
                ->postJson('/api/uploads/s3/multipart/sign', [
                    'uploadId' => $uploadId,
                    'key' => $key,
                    'partNumber' => $partNumber,
                    'room_id' => $room->id,
                ]);

            $signResponse->assertStatus(200);
            $signData = $signResponse->json();

            // Upload part data (small parts for testing)
            $partData = str_repeat("Part {$partNumber} ", 128); // ~1KB per part
            $uploadResponse = $httpClient->put($signData['url'], [
                'body' => $partData,
                'headers' => ['Content-Type' => 'video/webm'],
            ]);

            expect($uploadResponse->getStatusCode())->toBe(200);

            $etag = $uploadResponse->getHeader('ETag')[0] ?? '';
            $parts[] = [
                'PartNumber' => $partNumber,
                'ETag' => trim($etag, '"'),
            ];
        }

        // Shuffle parts to test sorting in completion
        shuffle($parts);

        // Complete upload
        $completeResponse = actingAs($user)
            ->withHeaders(['X-CSRF-TOKEN' => csrf_token()])
            ->postJson('/api/uploads/s3/multipart/complete', [
                'uploadId' => $uploadId,
                'key' => $key,
                'parts' => $parts,
                'room_id' => $room->id,
                'filename' => 'large_test.webm',
                'mime' => 'video/webm',
            ]);

        $completeResponse->assertStatus(200);
        $completeData = $completeResponse->json();

        expect($completeData['size'])->toBeGreaterThan(20000); // ~20KB total
        expect($completeData['recording_id'])->toBeInt();

        // Verify database record
        $recording = RoomRecording::find($completeData['recording_id']);
        expect($recording)->not()->toBeNull();
        expect($recording->size_bytes)->toBeGreaterThan(20000);
    });
});
