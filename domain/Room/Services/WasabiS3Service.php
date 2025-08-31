<?php

declare(strict_types=1);

namespace Domain\Room\Services;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Domain\User\Models\UserStorageAccount;
use Domain\Room\Models\Room;
use Illuminate\Support\Facades\Log;

class WasabiS3Service
{
    private S3Client $s3Client;
    private UserStorageAccount $storageAccount;

    public function __construct(UserStorageAccount $storageAccount)
    {
        if ($storageAccount->provider !== 'wasabi') {
            throw new \InvalidArgumentException('Storage account must be for Wasabi provider');
        }

        $this->storageAccount = $storageAccount;
        $this->initializeS3Client();
    }

    private function initializeS3Client(): void
    {
        $credentials = $this->storageAccount->encrypted_credentials;
        $this->s3Client = $this->createS3Client($credentials);
    }

    public function createS3ClientWithCredentials(array $credentials): S3Client
    {
        return $this->createS3Client($credentials);
    }

    private function createS3Client(array $credentials): S3Client
    {
        return new S3Client([
            'version' => 'latest',
            'region' => $credentials['region'] ?? 'us-east-1',
            'endpoint' => $credentials['endpoint'] ?? 'https://s3.wasabisys.com',
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key' => $credentials['access_key_id'],
                'secret' => $credentials['secret_access_key'],
            ],
        ]);
    }

    /**
     * Ensure that the specified bucket exists, create it if it doesn't
     */
    private function ensureBucketExists(string $bucketName): void
    {
        try {
            // Check if bucket exists by trying to get its location
            $this->s3Client->getBucketLocation(['Bucket' => $bucketName]);
        } catch (AwsException $e) {
            // If bucket doesn't exist, create it
            if ($e->getAwsErrorCode() === 'NoSuchBucket') {
                $this->createBucket($bucketName);
            } else {
                // Re-throw other AWS errors
                throw $e;
            }
        }
    }

    /**
     * Create a new bucket in Wasabi
     */
    private function createBucket(string $bucketName): void
    {
        try {
            $credentials = $this->storageAccount->encrypted_credentials;
            $region = $credentials['region'] ?? 'us-east-1';

            $createBucketConfig = [];
            
            // For regions other than us-east-1, we need to specify the LocationConstraint
            if ($region !== 'us-east-1') {
                $createBucketConfig['LocationConstraint'] = $region;
            }

            $params = ['Bucket' => $bucketName];
            if (!empty($createBucketConfig)) {
                $params['CreateBucketConfiguration'] = $createBucketConfig;
            }

            $result = $this->s3Client->createBucket($params);

            // Wait for bucket to be available
            $this->s3Client->waitUntil('BucketExists', ['Bucket' => $bucketName]);

            Log::info('Created Wasabi bucket', [
                'bucket_name' => $bucketName,
                'region' => $region,
                'storage_account_id' => $this->storageAccount->id,
            ]);

        } catch (AwsException $e) {
            Log::error('Failed to create Wasabi bucket', [
                'bucket_name' => $bucketName,
                'error' => $e->getMessage(),
                'storage_account_id' => $this->storageAccount->id,
            ]);

            throw new \Exception('Failed to create bucket: ' . $e->getMessage());
        }
    }

    /**
     * Generate a presigned URL for uploading a file to Wasabi
     */
    public function generatePresignedUploadUrl(
        string $key,
        string $contentType,
        int $expirationMinutes = 60
    ): array {
        try {
            $credentials = $this->storageAccount->encrypted_credentials;
            $bucket = $credentials['bucket_name'];

            // Ensure bucket exists before generating presigned URL
            $this->ensureBucketExists($bucket);

            $command = $this->s3Client->getCommand('PutObject', [
                'Bucket' => $bucket,
                'Key' => $key,
                'ContentType' => $contentType,
            ]);

            $request = $this->s3Client->createPresignedRequest(
                $command,
                "+{$expirationMinutes} minutes"
            );

            return [
                'presigned_url' => (string) $request->getUri(),
                'bucket' => $bucket,
                'key' => $key,
                'expires_at' => now()->addMinutes($expirationMinutes)->toISOString(),
                'headers' => [
                    'Content-Type' => $contentType,
                ],
            ];

        } catch (AwsException $e) {
            Log::error('Failed to generate Wasabi presigned URL', [
                'error' => $e->getMessage(),
                'storage_account_id' => $this->storageAccount->id,
                'key' => $key,
            ]);

            throw new \Exception('Failed to generate upload URL: ' . $e->getMessage());
        }
    }

    /**
     * Generate a presigned URL for downloading a file from Wasabi
     */
    public function generatePresignedDownloadUrl(
        string $key,
        int $expirationMinutes = 60
    ): array {
        try {
            $credentials = $this->storageAccount->encrypted_credentials;
            $bucket = $credentials['bucket_name'];

            // Ensure bucket exists before generating presigned URL
            $this->ensureBucketExists($bucket);

            $command = $this->s3Client->getCommand('GetObject', [
                'Bucket' => $bucket,
                'Key' => $key,
            ]);

            $request = $this->s3Client->createPresignedRequest(
                $command,
                "+{$expirationMinutes} minutes"
            );

            return [
                'download_url' => (string) $request->getUri(),
                'bucket' => $bucket,
                'key' => $key,
                'expires_at' => now()->addMinutes($expirationMinutes)->toISOString(),
            ];

        } catch (AwsException $e) {
            Log::error('Failed to generate Wasabi download URL', [
                'error' => $e->getMessage(),
                'storage_account_id' => $this->storageAccount->id,
                'key' => $key,
            ]);

            throw new \Exception('Failed to generate download URL: ' . $e->getMessage());
        }
    }

    /**
     * Test the connection to Wasabi with the current credentials
     */
    public function testConnection(): bool
    {
        try {
            $credentials = $this->storageAccount->encrypted_credentials;
            $bucket = $credentials['bucket_name'];

            // Ensure bucket exists (will create if needed)
            $this->ensureBucketExists($bucket);

            // Try to list objects in the bucket (this validates credentials and bucket access)
            $this->s3Client->listObjectsV2([
                'Bucket' => $bucket,
                'MaxKeys' => 1,
            ]);

            return true;

        } catch (AwsException $e) {
            Log::warning('Wasabi connection test failed', [
                'error' => $e->getMessage(),
                'storage_account_id' => $this->storageAccount->id,
            ]);

            return false;
        }
    }

    /**
     * Get object metadata from Wasabi
     */
    public function getObjectMetadata(string $key): ?array
    {
        try {
            $credentials = $this->storageAccount->encrypted_credentials;
            $bucket = $credentials['bucket_name'];

            // Ensure bucket exists before accessing object
            $this->ensureBucketExists($bucket);

            $result = $this->s3Client->headObject([
                'Bucket' => $bucket,
                'Key' => $key,
            ]);

            return [
                'content_length' => $result['ContentLength'] ?? 0,
                'content_type' => $result['ContentType'] ?? 'application/octet-stream',
                'last_modified' => $result['LastModified'] ?? null,
                'etag' => $result['ETag'] ?? null,
            ];

        } catch (AwsException $e) {
            Log::warning('Failed to get Wasabi object metadata', [
                'error' => $e->getMessage(),
                'storage_account_id' => $this->storageAccount->id,
                'key' => $key,
            ]);

            return null;
        }
    }

    /**
     * Delete an object from Wasabi
     */
    public function deleteObject(string $key): bool
    {
        try {
            $credentials = $this->storageAccount->encrypted_credentials;
            $bucket = $credentials['bucket_name'];

            // Ensure bucket exists before deleting object
            $this->ensureBucketExists($bucket);

            $this->s3Client->deleteObject([
                'Bucket' => $bucket,
                'Key' => $key,
            ]);

            return true;

        } catch (AwsException $e) {
            Log::error('Failed to delete Wasabi object', [
                'error' => $e->getMessage(),
                'storage_account_id' => $this->storageAccount->id,
                'key' => $key,
            ]);

            return false;
        }
    }

    /**
     * Generate a unique key for a room recording
     */
    public static function generateRecordingKey(Room $room, int $userId, string $filename): string
    {
        $timestamp = now()->format('Y/m/d');
        $roomId = $room->id;
        $sanitizedFilename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        
        return "recordings/{$timestamp}/room_{$roomId}/user_{$userId}/{$sanitizedFilename}";
    }

    /**
     * Get the storage account being used
     */
    public function getStorageAccount(): UserStorageAccount
    {
        return $this->storageAccount;
    }
}
