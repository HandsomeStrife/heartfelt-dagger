<?php

declare(strict_types=1);

namespace Domain\Campaign\Actions;

use Domain\Campaign\Models\Campaign;
use Domain\User\Models\User;
use Exception;

class LeaveCampaignAction
{
    public function execute(Campaign $campaign, User $user): bool
    {
        // Check if user is the creator (they cannot leave their own campaign)
        if ($campaign->isCreator($user)) {
            throw new Exception('Campaign creator cannot leave their own campaign');
        }

        // Find and delete the membership
        $membership = $campaign->members()->where('user_id', $user->id)->first();

        if (! $membership) {
            throw new Exception('User is not a member of this campaign');
        }

        return $membership->delete();
    }
}
