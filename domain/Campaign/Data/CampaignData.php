<?php

declare(strict_types=1);

namespace Domain\Campaign\Data;

use Domain\Campaign\Enums\CampaignStatus;
use Domain\CampaignFrame\Data\CampaignFrameData;
use Domain\User\Data\UserData;
use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class CampaignData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public int $creator_id,
        public string $invite_code,
        public string $campaign_code,
        public CampaignStatus $status,
        public ?int $campaign_frame_id = null,
        public ?string $created_at = null,
        public ?string $updated_at = null,
        public ?UserData $creator = null,
        public ?CampaignFrameData $campaign_frame = null,
        public ?int $member_count = null,
    ) {}
}
