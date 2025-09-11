<?php

declare(strict_types=1);

namespace Domain\CampaignHandout\Data;

use Domain\CampaignHandout\Enums\HandoutAccessLevel;
use Domain\CampaignHandout\Enums\HandoutFileType;
use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Data;

class CreateCampaignHandoutData extends Data
{
    public function __construct(
        public int $campaign_id,
        public int $creator_id,
        public string $title,
        public ?string $description,
        public UploadedFile $file,
        public HandoutAccessLevel $access_level,
        public bool $is_visible_in_sidebar = false,
        public array $authorized_user_ids = [],
    ) {}

    public function getFileType(): HandoutFileType
    {
        return HandoutFileType::fromMimeType($this->file->getMimeType() ?? 'application/octet-stream');
    }

    public function getOriginalFileName(): string
    {
        return $this->file->getClientOriginalName() ?? 'unknown';
    }

    public function getFileSize(): int
    {
        return $this->file->getSize() ?? 0;
    }

    public function getMimeType(): string
    {
        return $this->file->getMimeType() ?? 'application/octet-stream';
    }

    public function generateFileName(): string
    {
        $extension = $this->file->getClientOriginalExtension();
        $sanitized_name = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($this->getOriginalFileName(), PATHINFO_FILENAME));
        $timestamp = now()->format('His');
        $unique_id = substr(md5(uniqid()), 0, 8);
        
        return "{$sanitized_name}_{$timestamp}_{$unique_id}.{$extension}";
    }

    public function generateFilePath(): string
    {
        $date = now();
        $year = $date->format('Y');
        $month = $date->format('m');
        $day = $date->format('d');
        
        return "campaign-handouts/{$this->campaign_id}/{$year}/{$month}/{$day}";
    }

    public function extractMetadata(): array
    {
        $metadata = [];
        
        // Extract image dimensions if it's an image
        if ($this->getFileType() === HandoutFileType::IMAGE) {
            $imageSizeInfo = getimagesize($this->file->getPathname());
            if ($imageSizeInfo !== false) {
                $metadata['width'] = $imageSizeInfo[0];
                $metadata['height'] = $imageSizeInfo[1];
            }
        }
        
        return $metadata;
    }
}
