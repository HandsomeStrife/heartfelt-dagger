<?php

declare(strict_types=1);

use Aws\S3\S3Client;
use Domain\Room\Services\WasabiS3Service;
use Domain\User\Models\UserStorageAccount;

test('can connect to MinIO and list buckets', function () {
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

    // Try to list buckets
    $buckets = $s3Client->listBuckets();

    dump('Buckets:', $buckets['Buckets'] ?? []);

    // Check if recordings bucket exists
    $bucketNames = array_column($buckets['Buckets'] ?? [], 'Name');
    expect($bucketNames)->toContain('recordings');
});

test('can create a UserStorageAccount and test WasabiS3Service', function () {
    $user = \Domain\User\Models\User::factory()->create();

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

    dump('Storage Account Created:', $storageAccount->toArray());

    // Try to create WasabiS3Service
    $service = new WasabiS3Service($storageAccount);

    // Try to test connection
    $canConnect = $service->testConnection();

    dump('Can connect:', $canConnect);

    expect($canConnect)->toBeTrue();
});
