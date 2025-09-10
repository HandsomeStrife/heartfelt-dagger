<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Room\Services\GoogleDriveService;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;
use Illuminate\Support\Facades\Log;

class CreateGoogleDriveStorageAccount
{
    public function execute(User $user, string $authorizationCode, string $displayName): UserStorageAccount
    {
        try {
            // Exchange authorization code for tokens
            $tokens = GoogleDriveService::exchangeAuthorizationCode($authorizationCode);

            if (! isset($tokens['refresh_token'])) {
                throw new \Exception('No refresh token received. User may need to re-authorize with forced approval.');
            }

            // Prepare credentials to store
            $credentials = [
                'refresh_token' => $tokens['refresh_token'],
                'access_token' => $tokens['access_token'] ?? null,
                'expires_in' => $tokens['expires_in'] ?? null,
                'token_type' => $tokens['token_type'] ?? 'Bearer',
                'scope' => $tokens['scope'] ?? null,
                'created_at' => now()->timestamp,
            ];

            // Create storage account
            $storageAccount = UserStorageAccount::create([
                'user_id' => $user->id,
                'provider' => 'google_drive',
                'encrypted_credentials' => $credentials,
                'display_name' => $displayName,
                'is_active' => true,
            ]);

            // Test the connection to ensure it works
            $driveService = new GoogleDriveService($storageAccount);
            if (! $driveService->testConnection()) {
                // If connection fails, delete the account and throw exception
                $storageAccount->delete();
                throw new \Exception('Failed to connect to Google Drive with provided credentials');
            }

            // Get user info to enhance the display name if needed
            $userInfo = $driveService->getUserInfo();
            if ($userInfo && empty($displayName)) {
                $storageAccount->update([
                    'display_name' => $userInfo['email'].' (Google Drive)',
                ]);
            }

            Log::info('Created Google Drive storage account', [
                'user_id' => $user->id,
                'storage_account_id' => $storageAccount->id,
                'google_email' => $userInfo['email'] ?? 'unknown',
            ]);

            return $storageAccount;

        } catch (\Exception $e) {
            Log::error('Failed to create Google Drive storage account', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Failed to create Google Drive account: '.$e->getMessage());
        }
    }
}
