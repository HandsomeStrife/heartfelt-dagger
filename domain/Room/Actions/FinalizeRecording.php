<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Room\Models\RoomRecording;
use Domain\Room\Services\WasabiS3Service;
use Domain\Room\Services\GoogleDriveService;
use Domain\User\Models\UserStorageAccount;
use Illuminate\Support\Facades\Log;

class FinalizeRecording
{
    public function execute(RoomRecording $recording): bool
    {
        if (!$recording->canBeFinalized()) {
            Log::warning('Recording cannot be finalized', [
                'recording_id' => $recording->id,
                'status' => $recording->status->value,
                'multipart_upload_id' => $recording->multipart_upload_id
            ]);
            return false;
        }

        try {
            // Mark as finalizing
            $recording->markAsFinalizing();

            // Get the storage account for this recording
            $storageAccount = UserStorageAccount::where('user_id', $recording->user_id)
                ->where('provider', $recording->provider)
                ->first();

            if (!$storageAccount) {
                throw new \Exception('Storage account not found for recording finalization');
            }

            // Complete the multipart upload based on provider
            if ($recording->provider === 'wasabi') {
                $this->finalizeWasabiRecording($recording, $storageAccount);
            } elseif ($recording->provider === 'google_drive') {
                $this->finalizeGoogleDriveRecording($recording, $storageAccount);
            } else {
                throw new \Exception('Unsupported provider for recording finalization: ' . $recording->provider);
            }

            // Mark as completed
            $recording->markAsCompleted();

            Log::info('Recording finalized successfully', [
                'recording_id' => $recording->id,
                'provider' => $recording->provider,
                'multipart_upload_id' => $recording->multipart_upload_id,
                'final_size' => $recording->size_bytes
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to finalize recording', [
                'recording_id' => $recording->id,
                'error' => $e->getMessage(),
                'multipart_upload_id' => $recording->multipart_upload_id
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

        if (!$result['success']) {
            throw new \Exception('Wasabi multipart upload completion returned failure');
        }

        Log::info('Wasabi recording finalized', [
            'recording_id' => $recording->id,
            'location' => $result['location'],
            'etag' => $result['etag'],
            'parts_count' => count($parts)
        ]);
    }

    private function finalizeGoogleDriveRecording(RoomRecording $recording, UserStorageAccount $storageAccount): void
    {
        $googleDriveService = new GoogleDriveService($storageAccount);
        
        // For Google Drive, the recording should already be completed during the upload process
        // The multipart_upload_id contains the session URI, but the upload is likely already finished
        // We just need to verify the file exists and update our records
        
        try {
            // Try to get file info to verify it exists
            $fileInfo = $googleDriveService->getFileInfo($recording->provider_file_id);
            
            if (!$fileInfo) {
                throw new \Exception('Google Drive file not found or upload incomplete');
            }
            
            // Update recording with final file size if available
            if (isset($fileInfo['size']) && $fileInfo['size'] > 0) {
                $recording->update(['size_bytes' => (int) $fileInfo['size']]);
            }
            
            Log::info('Google Drive recording finalized', [
                'recording_id' => $recording->id,
                'file_id' => $recording->provider_file_id,
                'file_size' => $fileInfo['size'] ?? 'unknown',
                'file_name' => $fileInfo['name'] ?? 'unknown'
            ]);
            
        } catch (\Exception $e) {
            // If we can't verify the file, the upload is likely still in progress or failed
            // Don't mark as completed - let it remain in "recording" status for retry
            
            Log::warning('Could not verify Google Drive file during finalization, upload may still be in progress', [
                'recording_id' => $recording->id,
                'provider_file_id' => $recording->provider_file_id,
                'session_uri' => $recording->multipart_upload_id,
                'error' => $e->getMessage()
            ]);
            
            // Throw exception to prevent marking as completed
            throw new \Exception('Google Drive file verification failed, upload may still be in progress: ' . $e->getMessage());
        }
    }
}
