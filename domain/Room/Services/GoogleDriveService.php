<?php

declare(strict_types=1);

namespace Domain\Room\Services;

use Domain\Room\Models\Room;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;
use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use Google\Service\Drive\DriveFile;
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
        $accessToken = $this->getAccessToken();
        
        return new \GuzzleHttp\Client([
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]);
    }

    /**
     * Get a valid access token for frontend use
     */
    public function getValidAccessToken(): string
    {
        return $this->getAccessToken();
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
        
        if (!$newToken || isset($newToken['error'])) {
            $error = $newToken['error_description'] ?? 'Unknown error refreshing token';
            throw new \Exception('Failed to refresh access token: ' . $error);
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
     * Generate direct upload URL for Google Drive resumable uploads
     */
    public function generateDirectUploadUrl(
        string $filename,
        string $contentType,
        int $estimatedSize,
        array $metadata = [],
        ?string $origin = null
    ): array {
        try {
            // Prepare file metadata
            $fileMetadata = [
                'name' => $filename,
            ];
            
            // Add parent folder if specified
            if (!empty($metadata['folder_id'])) {
                $fileMetadata['parents'] = [$metadata['folder_id']];
            }
            
            // Get authenticated HTTP client
            $httpClient = $this->getAuthenticatedClient();
            
            // Create resumable upload session with Origin header for CORS
            $origin = $origin ?: config('app.url', 'http://localhost:8090');
            
            Log::info('Creating Google Drive resumable session with origin', [
                'origin' => $origin,
                'filename' => $filename,
                'storage_account_id' => $this->storageAccount->id
            ]);
            
            $response = $httpClient->post('https://www.googleapis.com/upload/drive/v3/files', [
                'query' => ['uploadType' => 'resumable'],
                'headers' => [
                    'X-Upload-Content-Type' => $contentType,
                    'X-Upload-Content-Length' => $estimatedSize,
                    'Content-Type' => 'application/json',
                    'Origin' => $origin,
                ],
                'json' => $fileMetadata,
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Failed to create resumable upload session: ' . $response->getBody());
            }

            $sessionUri = $response->getHeader('Location')[0] ?? null;
            
            if (!$sessionUri) {
                throw new \Exception('No session URI returned from Google Drive');
            }

            Log::info('Google Drive resumable upload session created', [
                'storage_account_id' => $this->storageAccount->id,
                'filename' => $filename,
                'session_uri_length' => strlen($sessionUri),
                'estimated_size' => $estimatedSize
            ]);

            return [
                'session_uri' => $sessionUri,
                'upload_url' => $sessionUri, // For compatibility
                'expires_at' => time() + (3600 * 24), // 24 hours from now (Google Drive sessions typically last 1 week)
                'metadata' => $metadata,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to generate Google Drive upload URL', [
                'storage_account_id' => $this->storageAccount->id,
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Google Drive does not support S3-compatible multipart uploads
     * This method should not be used with Google Drive accounts
     */
    public function initializeMultipartUpload(
        string $filename,
        string $contentType,
        array $metadata = []
    ): array {
        throw new \Exception('Google Drive does not support S3-compatible multipart uploads. Use resumable uploads instead.');
    }

    /**
     * Google Drive does not support S3-compatible multipart uploads
     */
    public function signMultipartUploadPart(
        string $uploadId,
        string $key,
        int $partNumber
    ): array {
        throw new \Exception('Google Drive does not support S3-compatible multipart uploads. Use resumable uploads instead.');
    }

    /**
     * Google Drive does not support S3-compatible multipart uploads
     */
    public function completeMultipartUpload(
        string $uploadId,
        string $key,
        array $parts,
        array $metadata = []
    ): array {
        throw new \Exception('Google Drive does not support S3-compatible multipart uploads. Use resumable uploads instead.');
    }

    /**
     * Google Drive does not support S3-compatible multipart uploads
     */
    public function abortMultipartUpload(string $uploadId, string $key): bool
    {
        throw new \Exception('Google Drive does not support S3-compatible multipart uploads. Use resumable uploads instead.');
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

            Log::info('Google Drive verification response', [
                'storage_account_id' => $this->storageAccount->id,
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body_preview' => substr($response->body(), 0, 500)
            ]);

            if ($response->status() === 308) {
                // Upload is still in progress, get the range that's been uploaded
                $rangeHeader = $response->header('Range');
                throw new \Exception("Upload still in progress. Range: " . ($rangeHeader ?? 'unknown'));
            }

            if ($response->status() === 200 || $response->status() === 201) {
                // Upload completed successfully
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
            }

            // Other status codes indicate errors
            throw new \Exception("Upload verification failed with status: {$response->status()}");

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
     * Find or create a folder by name with optional parent
     */
    public function findOrCreateFolder(string $folderName, ?string $parentId = null): ?string
    {
        try {
            // First, try to find an existing folder
            $folderId = $this->findFolder($folderName, $parentId);
            if ($folderId) {
                return $folderId;
            }

            // If not found, create it
            return $this->createFolder($folderName, $parentId);

        } catch (\Exception $e) {
            Log::error('Failed to find or create Google Drive folder', [
                'storage_account_id' => $this->storageAccount->id,
                'folder_name' => $folderName,
                'parent_id' => $parentId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Find an existing folder by name and optional parent
     */
    private function findFolder(string $folderName, ?string $parentId = null): ?string
    {
        try {
            // Build query to find folder
            $query = "mimeType='application/vnd.google-apps.folder' and name='" . str_replace("'", "\\'", $folderName) . "' and trashed=false";
            
            if ($parentId) {
                $query .= " and '{$parentId}' in parents";
            } else {
                // Search in root if no parent specified
                $query .= " and 'root' in parents";
            }

            $response = $this->driveService->files->listFiles([
                'q' => $query,
                'fields' => 'files(id, name)',
                'pageSize' => 1,
            ]);

            $files = $response->getFiles();
            if (count($files) > 0) {
                Log::info('Found existing Google Drive folder', [
                    'storage_account_id' => $this->storageAccount->id,
                    'folder_name' => $folderName,
                    'folder_id' => $files[0]->getId(),
                    'parent_id' => $parentId,
                ]);
                return $files[0]->getId();
            }

            return null;

        } catch (\Exception $e) {
            Log::warning('Failed to search for Google Drive folder', [
                'storage_account_id' => $this->storageAccount->id,
                'folder_name' => $folderName,
                'parent_id' => $parentId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Create a new folder
     */
    private function createFolder(string $folderName, ?string $parentId = null): ?string
    {
        try {
            $driveFile = new DriveFile();
            $driveFile->setName($folderName);
            $driveFile->setMimeType('application/vnd.google-apps.folder');
            
            // Set parent folder if specified
            if ($parentId) {
                $driveFile->setParents([$parentId]);
            }

            $result = $this->driveService->files->create($driveFile);

            Log::info('Created Google Drive folder', [
                'storage_account_id' => $this->storageAccount->id,
                'folder_name' => $folderName,
                'folder_id' => $result->getId(),
                'parent_id' => $parentId,
            ]);

            return $result->getId();

        } catch (\Exception $e) {
            Log::error('Failed to create Google Drive folder', [
                'storage_account_id' => $this->storageAccount->id,
                'folder_name' => $folderName,
                'parent_id' => $parentId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get file information from Google Drive
     */
    public function getFileInfo(string $fileId): ?array
    {
        try {
            $file = $this->driveService->files->get($fileId, [
                'fields' => 'id,name,size,mimeType,createdTime,modifiedTime'
            ]);

            return [
                'id' => $file->getId(),
                'name' => $file->getName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'created_time' => $file->getCreatedTime(),
                'modified_time' => $file->getModifiedTime(),
            ];

        } catch (\Exception $e) {
            Log::warning('Failed to get Google Drive file info', [
                'storage_account_id' => $this->storageAccount->id,
                'file_id' => $fileId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Download a file from Google Drive (returns binary content)
     */
    public function downloadFile(string $fileId): ?string
    {
        try {
            // Use the Drive service to get the file content with 'alt=media'
            $response = $this->driveService->files->get($fileId, ['alt' => 'media']);
            
            Log::info('Downloaded file from Google Drive', [
                'storage_account_id' => $this->storageAccount->id,
                'file_id' => $fileId,
            ]);

            // For Google Drive API v3, with 'alt=media', the response is the file content
            // This should return the raw file content as a string
            return (string) $response;

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
