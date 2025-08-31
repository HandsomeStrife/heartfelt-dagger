<?php

declare(strict_types=1);

namespace Domain\Room\Services;

use Domain\Room\Models\Room;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;
use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use Google\Service\Drive\DriveFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class GoogleDriveService
{
    private GoogleClient $client;
    private GoogleDrive $driveService;
    private UserStorageAccount $storageAccount;

    public function __construct(UserStorageAccount $storageAccount)
    {
        if ($storageAccount->provider !== 'google_drive') {
            throw new \InvalidArgumentException('Storage account must be for Google Drive provider');
        }

        $this->storageAccount = $storageAccount;
        $this->initializeGoogleClient();
    }

    /**
     * Initialize Google Client with OAuth2 credentials
     */
    private function initializeGoogleClient(): void
    {
        $this->client = new GoogleClient();
        
        // Set application credentials from config
        $this->client->setClientId(config('services.google_drive.client_id'));
        $this->client->setClientSecret(config('services.google_drive.client_secret'));
        $this->client->setRedirectUri(config('services.google_drive.redirect_uri'));
        
        // Set required scopes for Drive API
        $this->client->addScope(GoogleDrive::DRIVE_FILE);
        
        // Set access type to offline to get refresh tokens
        $this->client->setAccessType('offline');
        $this->client->setApprovalPrompt('force');
        
        // Set stored credentials if available
        $credentials = $this->storageAccount->encrypted_credentials;
        if (isset($credentials['refresh_token'])) {
            // Try to refresh the access token using the refresh token
            try {
                $token = $this->client->fetchAccessTokenWithRefreshToken($credentials['refresh_token']);
                
                // If we got a new access token, we can optionally store it
                if (isset($token['access_token'])) {
                    // Token successfully refreshed
                    Log::debug('Google Drive access token refreshed', [
                        'storage_account_id' => $this->storageAccount->id,
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to refresh Google Drive access token', [
                    'storage_account_id' => $this->storageAccount->id,
                    'error' => $e->getMessage(),
                ]);
            }
        } elseif (isset($credentials['access_token'])) {
            // If we have an access token but no refresh token, set it directly
            $this->client->setAccessToken($credentials['access_token']);
        }
        
        $this->driveService = new GoogleDrive($this->client);
    }

    /**
     * Get OAuth2 authorization URL for user to authorize the application
     */
    public static function getAuthorizationUrl(): string
    {
        $client = new GoogleClient();
        $client->setClientId(config('services.google_drive.client_id'));
        $client->setClientSecret(config('services.google_drive.client_secret'));
        $client->setRedirectUri(config('services.google_drive.redirect_uri'));
        $client->addScope(GoogleDrive::DRIVE_FILE);
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');
        
        return $client->createAuthUrl();
    }

    /**
     * Exchange authorization code for access and refresh tokens
     */
    public static function exchangeAuthorizationCode(string $code): array
    {
        $client = new GoogleClient();
        $client->setClientId(config('services.google_drive.client_id'));
        $client->setClientSecret(config('services.google_drive.client_secret'));
        $client->setRedirectUri(config('services.google_drive.redirect_uri'));
        
        $token = $client->fetchAccessTokenWithAuthCode($code);
        
        if (isset($token['error'])) {
            throw new \Exception('Failed to exchange authorization code: ' . $token['error_description']);
        }
        
        return $token;
    }

    /**
     * Get an authenticated HTTP client for making requests to Google APIs
     */
    private function getAuthenticatedClient(): \GuzzleHttp\Client
    {
        return new \GuzzleHttp\Client([
            'timeout' => 30,
        ]);
    }

    /**
     * Get a valid access token, refreshing if necessary
     */
    private function getAccessToken(): string
    {
        $credentials = $this->storageAccount->encrypted_credentials;
        
        // Check if we have a valid access token
        if (isset($credentials['access_token']) && 
            isset($credentials['expires_in']) && 
            isset($credentials['created_at'])) {
            
            $expiresAt = $credentials['created_at'] + $credentials['expires_in'];
            $now = now()->timestamp;
            
            // If token is still valid (with 5 minute buffer), use it
            if ($expiresAt > ($now + 300)) {
                return $credentials['access_token'];
            }
        }
        
        // Token is expired or missing, refresh it
        return $this->refreshAccessToken();
    }

    /**
     * Refresh the access token using the stored refresh token
     */
    private function refreshAccessToken(): string
    {
        $credentials = $this->storageAccount->encrypted_credentials;
        
        if (!isset($credentials['refresh_token'])) {
            throw new \Exception('No refresh token available. User needs to re-authorize.');
        }

        $client = new GoogleClient();
        $client->setClientId(config('services.google_drive.client_id'));
        $client->setClientSecret(config('services.google_drive.client_secret'));
        $client->refreshToken($credentials['refresh_token']);

        $newToken = $client->getAccessToken();
        
        if (isset($newToken['error'])) {
            throw new \Exception('Failed to refresh access token: ' . $newToken['error_description']);
        }

        // Update stored credentials with new token
        $updatedCredentials = array_merge($credentials, [
            'access_token' => $newToken['access_token'],
            'expires_in' => $newToken['expires_in'] ?? 3600,
            'created_at' => now()->timestamp,
        ]);

        // If we got a new refresh token, store it too
        if (isset($newToken['refresh_token'])) {
            $updatedCredentials['refresh_token'] = $newToken['refresh_token'];
        }

        $this->storageAccount->update([
            'encrypted_credentials' => $updatedCredentials
        ]);

        return $newToken['access_token'];
    }

    /**
     * Generate a direct upload URL for Google Drive using Resumable Upload API
     * This allows clients to upload directly to Google Drive without going through our server
     */
    public function generateDirectUploadUrl(
        string $filename,
        string $contentType,
        int $fileSize,
        array $metadata = []
    ): array {
        try {
            // Create file metadata for Google Drive
            $fileMetadata = [
                'name' => $filename,
            ];
            
            // Set parent folder if specified in metadata
            if (isset($metadata['folder_id'])) {
                $fileMetadata['parents'] = [$metadata['folder_id']];
            }
            
            // Add description with room and user info
            if (isset($metadata['room_id'], $metadata['user_id'])) {
                $description = "DaggerHeart recording from Room {$metadata['room_id']} by User {$metadata['user_id']}";
                if (isset($metadata['started_at_ms'])) {
                    $startTime = date('Y-m-d H:i:s', $metadata['started_at_ms'] / 1000);
                    $description .= " recorded at {$startTime}";
                }
                $fileMetadata['description'] = $description;
            }

            // Initialize resumable upload session
            $uploadUrl = 'https://www.googleapis.com/upload/drive/v3/files?uploadType=resumable';
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
                'Content-Type' => 'application/json; charset=UTF-8',
                'X-Upload-Content-Type' => $contentType,
                'X-Upload-Content-Length' => $fileSize,
            ])->post($uploadUrl, $fileMetadata);

            $sessionUri = $response->header('Location');
            
            if (empty($sessionUri)) {
                throw new \Exception('Failed to get upload session URI from Google Drive');
            }

            Log::info('Generated Google Drive direct upload URL', [
                'storage_account_id' => $this->storageAccount->id,
                'filename' => $filename,
                'file_size' => $fileSize,
                'content_type' => $contentType,
            ]);

            return [
                'success' => true,
                'upload_url' => $sessionUri,
                'session_uri' => $sessionUri,
                'filename' => $filename,
                'content_type' => $contentType,
                'expires_at' => now()->addHours(24)->toISOString(), // Google Drive sessions last 24 hours
            ];

        } catch (\Exception $e) {
            Log::error('Failed to generate Google Drive upload URL', [
                'error' => $e->getMessage(),
                'storage_account_id' => $this->storageAccount->id,
                'filename' => $filename,
            ]);

            throw new \Exception('Failed to generate upload URL: ' . $e->getMessage());
        }
    }

    /**
     * Verify upload completion and get file information from Google Drive
     * This should be called after a direct upload to confirm success and get file metadata
     */
    public function verifyUploadCompletion(string $sessionUri): array
    {
        try {
            // Query the session URI to get upload status
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
                'Content-Length' => '0',
                'Content-Range' => 'bytes */*', // Query current status
            ])->put($sessionUri);

            $fileData = $response->json();

            if (!$fileData || !isset($fileData['id'])) {
                throw new \Exception('Upload verification failed: No file ID returned');
            }

            Log::info('Verified Google Drive upload completion', [
                'storage_account_id' => $this->storageAccount->id,
                'file_id' => $fileData['id'],
                'filename' => $fileData['name'] ?? 'unknown',
            ]);

            return [
                'success' => true,
                'file_id' => $fileData['id'],
                'filename' => $fileData['name'] ?? 'unknown',
                'size' => $fileData['size'] ?? 0,
                'web_view_link' => $fileData['webViewLink'] ?? null,
                'web_content_link' => $fileData['webContentLink'] ?? null,
                'created_time' => $fileData['createdTime'] ?? null,
                'mime_type' => $fileData['mimeType'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to verify Google Drive upload', [
                'error' => $e->getMessage(),
                'storage_account_id' => $this->storageAccount->id,
                'session_uri' => $sessionUri,
            ]);

            throw new \Exception('Failed to verify upload completion: ' . $e->getMessage());
        }
    }

    /**
     * Upload a file to Google Drive (legacy method - now deprecated in favor of direct uploads)
     * @deprecated Use generateDirectUploadUrl() for better performance and bandwidth efficiency
     */
    public function uploadFile(
        UploadedFile $file,
        string $filename,
        array $metadata = []
    ): array {
        try {
            // Create file metadata
            $driveFile = new DriveFile();
            $driveFile->setName($filename);
            
            // Set parent folder if specified in metadata
            if (isset($metadata['folder_id'])) {
                $driveFile->setParents([$metadata['folder_id']]);
            }
            
            // Add description with room and user info
            if (isset($metadata['room_id'], $metadata['user_id'])) {
                $description = "DaggerHeart recording from Room {$metadata['room_id']} by User {$metadata['user_id']}";
                if (isset($metadata['started_at_ms'])) {
                    $startTime = date('Y-m-d H:i:s', $metadata['started_at_ms'] / 1000);
                    $description .= " recorded at {$startTime}";
                }
                $driveFile->setDescription($description);
            }

            // Upload the file
            $result = $this->driveService->files->create(
                $driveFile,
                [
                    'data' => $file->get(),
                    'mimeType' => $file->getMimeType(),
                    'uploadType' => 'multipart',
                ]
            );

            Log::info('Successfully uploaded file to Google Drive', [
                'storage_account_id' => $this->storageAccount->id,
                'file_id' => $result->getId(),
                'filename' => $filename,
                'size' => $file->getSize(),
            ]);

            return [
                'success' => true,
                'file_id' => $result->getId(),
                'filename' => $result->getName(),
                'size' => $result->getSize(),
                'web_view_link' => $result->getWebViewLink(),
                'web_content_link' => $result->getWebContentLink(),
                'created_time' => $result->getCreatedTime(),
            ];

        } catch (\Exception $e) {
            Log::error('Failed to upload file to Google Drive', [
                'storage_account_id' => $this->storageAccount->id,
                'filename' => $filename,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Failed to upload to Google Drive: ' . $e->getMessage());
        }
    }

    /**
     * Get download URL for a file
     */
    public function getDownloadUrl(string $fileId): array
    {
        try {
            $file = $this->driveService->files->get($fileId, [
                'fields' => 'id,name,size,webViewLink,webContentLink,createdTime'
            ]);

            return [
                'success' => true,
                'download_url' => $file->getWebContentLink(),
                'web_view_link' => $file->getWebViewLink(),
                'filename' => $file->getName(),
                'size' => $file->getSize(),
                'created_time' => $file->getCreatedTime(),
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get Google Drive download URL', [
                'storage_account_id' => $this->storageAccount->id,
                'file_id' => $fileId,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Failed to get download URL: ' . $e->getMessage());
        }
    }

    /**
     * Delete a file from Google Drive
     */
    public function deleteFile(string $fileId): bool
    {
        try {
            $this->driveService->files->delete($fileId);

            Log::info('Successfully deleted file from Google Drive', [
                'storage_account_id' => $this->storageAccount->id,
                'file_id' => $fileId,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to delete file from Google Drive', [
                'storage_account_id' => $this->storageAccount->id,
                'file_id' => $fileId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Test the connection to Google Drive
     */
    public function testConnection(): bool
    {
        try {
            // Try to get information about the user's Drive
            $about = $this->driveService->about->get(['fields' => 'user']);
            
            Log::info('Google Drive connection test successful', [
                'storage_account_id' => $this->storageAccount->id,
                'user_email' => $about->getUser()->getEmailAddress(),
            ]);
            
            return true;

        } catch (\Exception $e) {
            Log::warning('Google Drive connection test failed', [
                'storage_account_id' => $this->storageAccount->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get user info from Google Drive account
     */
    public function getUserInfo(): ?array
    {
        try {
            $about = $this->driveService->about->get(['fields' => 'user,storageQuota']);
            $user = $about->getUser();
            $quota = $about->getStorageQuota();

            return [
                'email' => $user->getEmailAddress(),
                'display_name' => $user->getDisplayName(),
                'photo_link' => $user->getPhotoLink(),
                'storage_limit' => $quota->getLimit(),
                'storage_usage' => $quota->getUsage(),
            ];

        } catch (\Exception $e) {
            Log::warning('Failed to get Google Drive user info', [
                'storage_account_id' => $this->storageAccount->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Create a folder for room recordings
     */
    public function createRoomFolder(Room $room): ?string
    {
        try {
            $folderName = "DaggerHeart Room {$room->id} - {$room->name}";
            
            $driveFile = new DriveFile();
            $driveFile->setName($folderName);
            $driveFile->setMimeType('application/vnd.google-apps.folder');
            $driveFile->setDescription("Recordings for DaggerHeart room: {$room->name}");

            $result = $this->driveService->files->create($driveFile);

            Log::info('Created Google Drive folder for room', [
                'storage_account_id' => $this->storageAccount->id,
                'room_id' => $room->id,
                'folder_id' => $result->getId(),
                'folder_name' => $folderName,
            ]);

            return $result->getId();

        } catch (\Exception $e) {
            Log::error('Failed to create Google Drive folder', [
                'storage_account_id' => $this->storageAccount->id,
                'room_id' => $room->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Download a file from Google Drive
     */
    public function downloadFile(string $fileId): ?string
    {
        try {
            $response = $this->driveService->files->get($fileId, ['alt' => 'media']);
            
            Log::info('Downloaded file from Google Drive', [
                'storage_account_id' => $this->storageAccount->id,
                'file_id' => $fileId,
            ]);

            return $response->getBody()->getContents();

        } catch (\Exception $e) {
            Log::error('Failed to download file from Google Drive', [
                'storage_account_id' => $this->storageAccount->id,
                'file_id' => $fileId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get the storage account being used
     */
    public function getStorageAccount(): UserStorageAccount
    {
        return $this->storageAccount;
    }
}
