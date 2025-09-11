<?php

declare(strict_types=1);

namespace Domain\CampaignHandout\Actions;

use Domain\CampaignHandout\Data\CampaignHandoutData;
use Domain\CampaignHandout\Data\CreateCampaignHandoutData;
use Domain\CampaignHandout\Models\CampaignHandout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CreateCampaignHandoutAction
{
    public function execute(CreateCampaignHandoutData $data): CampaignHandoutData
    {
        return DB::transaction(function () use ($data) {
            // Generate file paths and metadata
            $fileName = $data->generateFileName();
            $filePath = $data->generateFilePath();
            $fullPath = "{$filePath}/{$fileName}";
            $metadata = $data->extractMetadata();

            // Store the file
            $storedPath = $this->storeFile($data, $filePath, $fileName);

            // Create the handout record
            $handout = CampaignHandout::create([
                'campaign_id' => $data->campaign_id,
                'creator_id' => $data->creator_id,
                'title' => $data->title,
                'description' => $data->description,
                'file_name' => $fileName,
                'original_file_name' => $data->getOriginalFileName(),
                'file_path' => $storedPath,
                'file_type' => $data->getFileType(),
                'mime_type' => $data->getMimeType(),
                'file_size' => $data->getFileSize(),
                'metadata' => $metadata,
                'access_level' => $data->access_level,
                'is_visible_in_sidebar' => $data->is_visible_in_sidebar,
                'display_order' => $this->getNextDisplayOrder($data->campaign_id),
                'is_published' => true,
            ]);

            // Sync authorized users if specific access
            if ($data->access_level->requiresSpecificAccess() && !empty($data->authorized_user_ids)) {
                $handout->authorizedUsers()->sync($data->authorized_user_ids);
            }

            // Load relationships for the response
            $handout->load(['campaign', 'creator', 'authorizedUsers']);

            Log::info('Campaign handout created', [
                'handout_id' => $handout->id,
                'campaign_id' => $data->campaign_id,
                'creator_id' => $data->creator_id,
                'file_name' => $fileName,
                'file_size' => $data->getFileSize(),
                'file_type' => $data->getFileType()->value,
            ]);

            return CampaignHandoutData::fromModel($handout);
        });
    }

    private function storeFile(CreateCampaignHandoutData $data, string $filePath, string $fileName): string
    {
        // Check if S3 is configured
        if (config('filesystems.disks.s3.key') && config('filesystems.disks.s3.secret')) {
            return $this->storeToS3($data, $filePath, $fileName);
        }

        return $this->storeToLocal($data, $filePath, $fileName);
    }

    private function storeToLocal(CreateCampaignHandoutData $data, string $filePath, string $fileName): string
    {
        $storedPath = $data->file->storeAs($filePath, $fileName, 'public');

        if (!$storedPath) {
            throw new \Exception('Failed to store file locally');
        }

        Log::info('Handout file stored locally', [
            'path' => $storedPath,
            'size' => $data->getFileSize(),
        ]);

        return $storedPath;
    }

    private function storeToS3(CreateCampaignHandoutData $data, string $filePath, string $fileName): string
    {
        $storedPath = $data->file->storeAs($filePath, $fileName, 's3');

        if (!$storedPath) {
            throw new \Exception('Failed to store file to S3');
        }

        // Verify file exists
        if (!Storage::disk('s3')->exists($storedPath)) {
            throw new \Exception('File upload to S3 failed - file does not exist after upload');
        }

        Log::info('Handout file stored to S3', [
            'path' => $storedPath,
            'size' => $data->getFileSize(),
        ]);

        return $storedPath;
    }

    private function getNextDisplayOrder(int $campaignId): int
    {
        $maxOrder = CampaignHandout::where('campaign_id', $campaignId)
            ->max('display_order');

        return ($maxOrder ?? 0) + 1;
    }
}
