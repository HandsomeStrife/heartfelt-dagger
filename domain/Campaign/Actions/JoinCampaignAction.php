<?php

declare(strict_types=1);

namespace Domain\Campaign\Actions;

use Domain\Campaign\Data\CampaignMemberData;
use Domain\Campaign\Models\Campaign;
use Domain\Campaign\Models\CampaignMember;
use Domain\Character\Models\Character;
use Domain\User\Models\User;
use Exception;

class JoinCampaignAction
{
    public function execute(Campaign $campaign, User $user, ?Character $character = null): CampaignMemberData
    {
        // Check if user is already a member
        if ($campaign->hasMember($user)) {
            throw new Exception('User is already a member of this campaign');
        }

        // Validate character belongs to user if provided
        if ($character && $character->user_id !== $user->id) {
            throw new Exception('Character does not belong to the user');
        }

        $joined_at = now();
        $campaign_member = CampaignMember::create([
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
            'character_id' => $character?->id,
            'joined_at' => $joined_at,
        ]);

        $campaign_member->load(['user', 'character', 'campaign']);

        return CampaignMemberData::from($campaign_member);
    }
}
