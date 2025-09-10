<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Room\Models\RoomRecording;
use Domain\Room\Services\GoogleDriveService;
use Domain\Room\Services\WasabiS3Service;
use Domain\User\Models\UserStorageAccount;
use Illuminate\Support\Facades\Log;

class FinalizeRecording
{
    public function execute(RoomRecording $recording): bool
    {
        if (! $recording->canBeFinalized()) {
            Log::warning('Recording cannot be finalized', [
                'recording_id' => $recording->id,
                'status' => $recording->status->value,
                'multipart_upload_id' => $recording->multipart_upload_id,
            ]);

            return false;
        }

        try {
            // Mark as finalizing
            $recording->markAsFinalizing();

            // Get the storage account for this recording
            // The storage account is associated with the room creator through recording settings
            $recording->load('room.recordingSettings');

            if (! $recording->room->recordingSettings) {
                throw new \Exception('Room recording settings not found for recording finalization');
            }

            $storageAccount = UserStorageAccount::find($recording->room->recordingSettings->storage_account_id);

            if (! $storageAccount) {
                throw new \Exception('Storage account not found for recording finalization');
            }

            // Verify the storage account provider matches the recording provider
            if ($storageAccount->provider !== $recording->provider) {
                throw new \Exception("Storage account provider ({$storageAccount->provider}) does not match recording provider ({$recording->provider})");
            }

            // Complete the multipart upload based on provider
            if ($recording->provider === 'wasabi') {
                $this->finalizeWasabiRecording($recording, $storageAccount);
            } elseif ($recording->provider === 'google_drive') {
                $this->finalizeGoogleDriveRecording($recording, $storageAccount);
            } else {
                throw new \Exception('Unsupported provider for recording finalization: '.$recording->provider);
            }

            // Mark as completed
            $recording->markAsCompleted();

            Log::info('Recording finalized successfully', [
                'recording_id' => $recording->id,
                'provider' => $recording->provider,
                'multipart_upload_id' => $recording->multipart_upload_id,
                'final_size' => $recording->size_bytes,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to finalize recording', [
                'recording_id' => $recording->id,
                'error' => $e->getMessage(),
                'multipart_upload_id' => $recording->multipart_upload_id,
            ]);

            $recording->markAsFailed();

            return false;
        }
    }

    private function finalizeWasabiRecording(RoomRecording $recording, UserStorageAccount $storageAccount): void
    {
        $wasabiService = new WasabiS3Service($storageAccount);

        $parts = $recording->getUploadedPartsForCompletion();

        if (empty($parts)) {
            throw new \Exception('No uploaded parts found for multipart upload completion');
        }

        $result = $wasabiService->completeMultipartUpload(
            $recording->provider_file_id,
            $recording->multipart_upload_id,
            $parts
        );

        if (! $result['success']) {
            throw new \Exception('Wasabi multipart upload completion returned failure');
        }

        Log::info('Wasabi recording finalized', [
            'recording_id' => $recording->id,
            'location' => $result['location'],
            'etag' => $result['etag'],
            'parts_count' => count($parts),
        ]);
    }

    private function finalizeGoogleDriveRecording(RoomRecording $recording, UserStorageAccount $storageAccount): void
    {
        $googleDriveService = new GoogleDriveService($storageAccount);

        // For Google Drive, we need to finalize the resumable upload session
        // The multipart_upload_id contains the session URI
        $sessionUri = $recording->multipart_upload_id;

        if (empty($sessionUri)) {
            throw new \Exception('No session URI found for Google Drive recording');
        }

        try {
            // Check if we already have a real Google Drive file ID (not empty and not a session URI)
            if (! empty($recording->provider_file_id) && ! str_contains($recording->provider_file_id, 'uploadType=resumable')) {
                Log::info('Google Drive recording already has file ID, verifying completion', [
                    'recording_id' => $recording->id,
                    'file_id' => $recording->provider_file_id,
                ]);

                // Just verify the file exists
                $fileInfo = $googleDriveService->getFileInfo($recording->provider_file_id);
                if ($fileInfo && isset($fileInfo['size'])) {
                    $recording->update(['size_bytes' => (int) $fileInfo['size']]);
                }

                return;
            }

            // Attempt to finalize the resumable session
            Log::info('Attempting to finalize Google Drive resumable session', [
                'recording_id' => $recording->id,
                'session_uri_length' => strlen($sessionUri),
                'current_size_bytes' => $recording->size_bytes,
            ]);

            $result = $googleDriveService->finalizeResumableSession($sessionUri, $recording->size_bytes ?? 0);

            if ($result['success']) {
                // Update recording with file ID first
                $recording->update([
                    'provider_file_id' => $result['file_id'],
                ]);

                // Get accurate file info from Google Drive (finalization response size can be 0)
                $fileInfo = $googleDriveService->getFileInfo($result['file_id']);
                $actualSize = 0;

                if ($fileInfo && isset($fileInfo['size']) && $fileInfo['size'] > 0) {
                    $actualSize = (int) $fileInfo['size'];
                    $recording->update(['size_bytes' => $actualSize]);
                } else {
                    // Fallback to finalization response or current size
                    $actualSize = (int) ($result['size'] ?? $recording->size_bytes ?? 0);
                    if ($actualSize > 0) {
                        $recording->update(['size_bytes' => $actualSize]);
                    }
                }

                Log::info('Google Drive recording finalized successfully', [
                    'recording_id' => $recording->id,
                    'file_id' => $result['file_id'],
                    'finalization_size' => $result['size'],
                    'actual_size' => $actualSize,
                    'file_name' => $result['filename'],
                ]);
            } else {
                throw new \Exception('Google Drive finalization returned failure');
            }

        } catch (\Exception $e) {
            Log::warning('Failed to finalize Google Drive recording', [
                'recording_id' => $recording->id,
                'session_uri' => $sessionUri,
                'current_size_bytes' => $recording->size_bytes,
                'error' => $e->getMessage(),
            ]);

            // If finalization fails, the session might be expired or corrupted
            // Mark as failed so it doesn't keep retrying indefinitely
            throw new \Exception('Google Drive recording finalization failed: '.$e->getMessage());
        }
    }
}
