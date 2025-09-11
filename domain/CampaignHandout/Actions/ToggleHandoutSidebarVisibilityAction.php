<?php

declare(strict_types=1);

namespace Domain\CampaignHandout\Actions;

use Domain\CampaignHandout\Data\CampaignHandoutData;
use Domain\CampaignHandout\Models\CampaignHandout;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Log;

class ToggleHandoutSidebarVisibilityAction
{
    public function execute(int $handoutId, User $user): CampaignHandoutData
    {
        $handout = CampaignHandout::findOrFail($handoutId);

        // Check if user can modify this handout
        if (!$this->canUserModify($handout, $user)) {
            throw new \Exception('You do not have permission to modify this handout.');
        }

        $wasVisible = $handout->is_visible_in_sidebar;
        $handout->update([
            'is_visible_in_sidebar' => !$wasVisible,
        ]);

        // Load relationships for the response
        $handout->load(['campaign', 'creator', 'authorizedUsers']);

        Log::info('Handout sidebar visibility toggled', [
            'handout_id' => $handoutId,
            'previous_visibility' => $wasVisible,
            'new_visibility' => !$wasVisible,
            'modified_by' => $user->id,
        ]);

        return CampaignHandoutData::from($handout);
    }

    private function canUserModify(CampaignHandout $handout, User $user): bool
    {
        // Creator can modify their own handouts
        if ($handout->creator_id === $user->id) {
            return true;
        }

        // Campaign creator (GM) can modify any handout in their campaign
        if ($handout->campaign->creator_id === $user->id) {
            return true;
        }

        return false;
    }
}
