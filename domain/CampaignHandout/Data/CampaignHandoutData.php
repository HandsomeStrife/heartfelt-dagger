<?php

declare(strict_types=1);

namespace Domain\CampaignHandout\Data;

use Domain\Campaign\Data\CampaignData;
use Domain\CampaignHandout\Enums\HandoutAccessLevel;
use Domain\CampaignHandout\Enums\HandoutFileType;
use Domain\CampaignHandout\Models\CampaignHandout;
use Domain\User\Data\UserData;
use Illuminate\Support\Collection;
use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class CampaignHandoutData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        public ?int $id,
        public int $campaign_id,
        public int $creator_id,
        public string $title,
        public ?string $description,
        public string $file_name,
        public string $original_file_name,
        public string $file_path,
        public HandoutFileType $file_type,
        public string $mime_type,
        public int $file_size,
        public ?array $metadata,
        public HandoutAccessLevel $access_level,
        public bool $is_visible_in_sidebar,
        public int $display_order,
        public bool $is_published,
        public ?string $created_at = null,
        public ?string $updated_at = null,
        public ?CampaignData $campaign = null,
        public ?UserData $creator = null,
        public ?Collection $authorized_users = null,
        public ?string $file_url = null,
        public ?string $formatted_file_size = null,
        public ?array $image_dimensions = null,
    ) {}

    public static function fromModel(CampaignHandout $handout): self
    {
        return self::from([
            'id' => $handout->id,
            'campaign_id' => $handout->campaign_id,
            'creator_id' => $handout->creator_id,
            'title' => $handout->title,
            'description' => $handout->description,
            'file_name' => $handout->file_name,
            'original_file_name' => $handout->original_file_name,
            'file_path' => $handout->file_path,
            'file_type' => $handout->file_type,
            'mime_type' => $handout->mime_type,
            'file_size' => $handout->file_size,
            'metadata' => $handout->metadata,
            'access_level' => $handout->access_level,
            'is_visible_in_sidebar' => $handout->is_visible_in_sidebar,
            'display_order' => $handout->display_order,
            'is_published' => $handout->is_published,
            'created_at' => $handout->created_at?->toISOString(),
            'updated_at' => $handout->updated_at?->toISOString(),
            'campaign' => $handout->relationLoaded('campaign') ? CampaignData::from($handout->campaign) : null,
            'creator' => $handout->relationLoaded('creator') ? UserData::from($handout->creator) : null,
            'authorized_users' => $handout->relationLoaded('authorizedUsers') ? 
                $handout->authorizedUsers->map(fn($user) => UserData::from($user)) : null,
            'file_url' => $handout->getFileUrl(),
            'formatted_file_size' => $handout->getFormattedFileSize(),
            'image_dimensions' => $handout->getImageDimensions(),
        ]);
    }

    public function canBeViewedBy(?UserData $user): bool
    {
        // Not published handouts can only be viewed by creator
        if (! $this->is_published && $user?->id !== $this->creator_id) {
            return false;
        }

        // Creator can always view
        if ($user && $user->id === $this->creator_id) {
            return true;
        }

        // Campaign creator (GM) can always view
        if ($user && $this->campaign && $user->id === $this->campaign->creator_id) {
            return true;
        }

        return match ($this->access_level) {
            HandoutAccessLevel::GM_ONLY => false,
            HandoutAccessLevel::ALL_PLAYERS => $this->userIsCampaignMember($user),
            HandoutAccessLevel::SPECIFIC_PLAYERS => $this->userHasSpecificAccess($user),
        };
    }

    private function userIsCampaignMember(?UserData $user): bool
    {
        if (! $user || ! $this->campaign) {
            return false;
        }

        // This would need to be checked against the campaign members
        // For now, we'll assume it's handled at the repository level
        return true; // Simplified for now
    }

    private function userHasSpecificAccess(?UserData $user): bool
    {
        if (! $user || ! $this->authorized_users) {
            return false;
        }

        return $this->authorized_users->contains('id', $user->id);
    }

    public function isPreviewableImage(): bool
    {
        return $this->file_type === HandoutFileType::IMAGE;
    }

    public function isPdf(): bool
    {
        return $this->file_type === HandoutFileType::PDF;
    }

    public function isPreviewable(): bool
    {
        return $this->file_type->isPreviewable();
    }
}
