<?php

declare(strict_types=1);

namespace Domain\Campaign\Actions;

use Domain\Campaign\Data\CampaignData;
use Domain\Campaign\Data\CreateCampaignData;
use Domain\Campaign\Enums\CampaignStatus;
use Domain\Campaign\Models\Campaign;
use Domain\User\Data\UserData;
use Domain\User\Models\User;

class CreateCampaignAction
{
    public function execute(CreateCampaignData $create_data, User $creator): CampaignData
    {
        $campaign = Campaign::create([
            'name' => $create_data->name,
            'description' => $create_data->description,
            'creator_id' => $creator->id,
            'campaign_frame_id' => $create_data->campaign_frame_id,
            'status' => CampaignStatus::ACTIVE,
        ]);

        $campaign->load(['creator', 'campaignFrame']);

        return CampaignData::from([
            'id' => $campaign->id,
            'name' => $campaign->name,
            'description' => $campaign->description,
            'creator_id' => $campaign->creator_id,
            'campaign_frame_id' => $campaign->campaign_frame_id,
            'invite_code' => $campaign->invite_code,
            'campaign_code' => $campaign->campaign_code,
            'status' => $campaign->status,
            'created_at' => $campaign->created_at?->toDateTimeString(),
            'updated_at' => $campaign->updated_at?->toDateTimeString(),
            'creator' => $campaign->creator ? UserData::from($campaign->creator) : null,
            'campaign_frame' => $campaign->campaignFrame ? \Domain\CampaignFrame\Data\CampaignFrameData::from($campaign->campaignFrame) : null,
            'member_count' => null,
        ]);
    }
}
