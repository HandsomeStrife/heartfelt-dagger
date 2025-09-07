<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Room\Models\RoomRecording;
use Domain\Room\Services\WasabiS3Service;
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
}
