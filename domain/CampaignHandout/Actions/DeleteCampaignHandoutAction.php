<?php

declare(strict_types=1);

namespace Domain\CampaignHandout\Actions;

use Domain\CampaignHandout\Models\CampaignHandout;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DeleteCampaignHandoutAction
{
    public function execute(int $handoutId, User $user): void
    {
        $handout = CampaignHandout::findOrFail($handoutId);

        // Check if user can delete this handout
        if (!$this->canUserDelete($handout, $user)) {
            throw new \Exception('You do not have permission to delete this handout.');
        }

        // Delete the file from storage
        $this->deleteFile($handout);

        // Delete the handout record (cascade will handle authorized users)
        $handout->delete();

        Log::info('Campaign handout deleted', [
            'handout_id' => $handoutId,
            'deleted_by' => $user->id,
            'file_path' => $handout->file_path,
        ]);
    }

    private function canUserDelete(CampaignHandout $handout, User $user): bool
    {
        // Creator can delete their own handouts
        if ($handout->creator_id === $user->id) {
            return true;
        }

        // Campaign creator (GM) can delete any handout in their campaign
        if ($handout->campaign->creator_id === $user->id) {
            return true;
        }

        return false;
    }

    private function deleteFile(CampaignHandout $handout): void
    {
        try {
            // Check if S3 is configured
            if (config('filesystems.disks.s3.key') && config('filesystems.disks.s3.secret')) {
                if (Storage::disk('s3')->exists($handout->file_path)) {
                    Storage::disk('s3')->delete($handout->file_path);
                    Log::info('Handout file deleted from S3', ['path' => $handout->file_path]);
                }
            } else {
                if (Storage::disk('public')->exists($handout->file_path)) {
                    Storage::disk('public')->delete($handout->file_path);
                    Log::info('Handout file deleted from local storage', ['path' => $handout->file_path]);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to delete handout file', [
                'handout_id' => $handout->id,
                'file_path' => $handout->file_path,
                'error' => $e->getMessage(),
            ]);
            // Don't throw the exception - we still want to delete the database record
        }
    }
}
