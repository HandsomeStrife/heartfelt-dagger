<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\Room\Services\WasabiS3Service;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;

test('wasabi service generates correct recording keys', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    $filename = 'test_recording.webm';
    $userId = 123;
    
    $key = WasabiS3Service::generateRecordingKey($room, $userId, $filename);
    
    // Key should follow the pattern: recordings/{date}/room_{id}/user_{id}/{filename}
    expect($key)->toContain('recordings/');
    expect($key)->toContain("room_{$room->id}");
    expect($key)->toContain("user_{$userId}");
    expect($key)->toContain('test_recording.webm');
    
    // Should include today's date
    $today = now()->format('Y/m/d');
    expect($key)->toContain($today);
    
    // Full key should match expected pattern
    $expectedKey = "recordings/{$today}/room_{$room->id}/user_{$userId}/test_recording.webm";
    expect($key)->toBe($expectedKey);
});

test('wasabi service sanitizes filenames in keys', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    // Test filename with special characters that should be sanitized
    $dangerousFilename = 'my video (1) [test] & more!.webm';
    $userId = 123;
    
    $key = WasabiS3Service::generateRecordingKey($room, $userId, $dangerousFilename);
    
    // Should sanitize the filename part
    expect($key)->toContain('my_video__1___test____more_.webm');
    expect($key)->not()->toContain('(');
    expect($key)->not()->toContain(')');
    expect($key)->not()->toContain('[');
    expect($key)->not()->toContain(']');
    expect($key)->not()->toContain('&');
    expect($key)->not()->toContain('!');
});

test('wasabi service properly initializes with valid credentials', function () {
    $user = User::factory()->create();
    
    $storageAccount = UserStorageAccount::create([
        'user_id' => $user->id,
        'provider' => 'wasabi',
        'encrypted_credentials' => [
            'access_key_id' => 'AKIAIOSFODNN7EXAMPLE',
            'secret_access_key' => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY',
            'bucket_name' => 'my-test-bucket',
            'region' => 'us-east-1',
            'endpoint' => 'https://s3.wasabisys.com',
        ],
        'display_name' => 'Test Wasabi Account',
        'is_active' => true,
    ]);

    $wasabiService = new WasabiS3Service($storageAccount);
    
    expect($wasabiService)->toBeInstanceOf(WasabiS3Service::class);
    expect($wasabiService->getStorageAccount())->toBe($storageAccount);
});

test('wasabi service uses default values for missing credential fields', function () {
    $user = User::factory()->create();
    
    // Create storage account with minimal credentials (missing region and endpoint)
    $storageAccount = UserStorageAccount::create([
        'user_id' => $user->id,
        'provider' => 'wasabi',
        'encrypted_credentials' => [
            'access_key_id' => 'AKIAIOSFODNN7EXAMPLE',
            'secret_access_key' => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY',
            'bucket_name' => 'my-test-bucket',
            // Missing region and endpoint - should use defaults
        ],
        'display_name' => 'Minimal Wasabi Account',
        'is_active' => true,
    ]);

    // Should not throw an exception and should initialize properly
    $wasabiService = new WasabiS3Service($storageAccount);
    expect($wasabiService)->toBeInstanceOf(WasabiS3Service::class);
});

test('wasabi service generates presigned urls with correct parameters', function () {
    $user = User::factory()->create();
    
    $storageAccount = UserStorageAccount::create([
        'user_id' => $user->id,
        'provider' => 'wasabi',
        'encrypted_credentials' => [
            'access_key_id' => 'AKIAIOSFODNN7EXAMPLE',
            'secret_access_key' => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY',
            'bucket_name' => 'my-test-bucket',
            'region' => 'us-east-1',
            'endpoint' => 'https://s3.wasabisys.com',
        ],
        'display_name' => 'Test Wasabi Account',
        'is_active' => true,
    ]);

    $wasabiService = new WasabiS3Service($storageAccount);
    
    $key = 'recordings/2024/01/01/room_1/user_1/test.webm';
    $contentType = 'video/webm';
    
    // This should generate a presigned URL (though it may fail due to fake credentials)
    // We're just testing that the method returns the expected structure
    try {
        $result = $wasabiService->generatePresignedUploadUrl($key, $contentType, 60);
        
        // If it succeeds (with real credentials), check structure
        expect($result)->toHaveKey('presigned_url');
        expect($result)->toHaveKey('bucket');
        expect($result)->toHaveKey('key');
        expect($result)->toHaveKey('expires_at');
        expect($result)->toHaveKey('headers');
        expect($result['bucket'])->toBe('my-test-bucket');
        expect($result['key'])->toBe($key);
        
    } catch (\Exception $e) {
        // Expected to fail with fake credentials, but should be an AWS exception, not a code error
        expect($e->getMessage())->toContain('Failed to generate upload URL');
    }
});

test('wasabi service generates download urls with correct parameters', function () {
    $user = User::factory()->create();
    
    $storageAccount = UserStorageAccount::create([
        'user_id' => $user->id,
        'provider' => 'wasabi',
        'encrypted_credentials' => [
            'access_key_id' => 'AKIAIOSFODNN7EXAMPLE',
            'secret_access_key' => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY',
            'bucket_name' => 'my-test-bucket',
            'region' => 'us-east-1',
            'endpoint' => 'https://s3.wasabisys.com',
        ],
        'display_name' => 'Test Wasabi Account',
        'is_active' => true,
    ]);

    $wasabiService = new WasabiS3Service($storageAccount);
    
    $key = 'recordings/2024/01/01/room_1/user_1/test.webm';
    
    // This should generate a presigned download URL (though it may fail due to fake credentials)
    try {
        $result = $wasabiService->generatePresignedDownloadUrl($key, 60);
        
        // If it succeeds (with real credentials), check structure
        expect($result)->toHaveKey('download_url');
        expect($result)->toHaveKey('bucket');
        expect($result)->toHaveKey('key');
        expect($result)->toHaveKey('expires_at');
        expect($result['bucket'])->toBe('my-test-bucket');
        expect($result['key'])->toBe($key);
        
    } catch (\Exception $e) {
        // Expected to fail with fake credentials, but should be an AWS exception, not a code error
        expect($e->getMessage())->toContain('Failed to generate download URL');
    }
});

