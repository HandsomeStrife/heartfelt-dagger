<?php

declare(strict_types=1);

namespace Domain\CampaignHandout\Actions;

use Domain\CampaignHandout\Data\CampaignHandoutData;
use Domain\CampaignHandout\Data\UpdateCampaignHandoutData;
use Domain\CampaignHandout\Enums\HandoutAccessLevel;
use Domain\CampaignHandout\Models\CampaignHandout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateCampaignHandoutAction
{
    public function execute(UpdateCampaignHandoutData $data): CampaignHandoutData
    {
        return DB::transaction(function () use ($data) {
            $handout = CampaignHandout::findOrFail($data->id);

            $handout->update([
                'title' => $data->title,
                'description' => $data->description,
                'access_level' => $data->access_level,
                'is_visible_in_sidebar' => $data->is_visible_in_sidebar,
                'is_published' => $data->is_published,
            ]);

            // Sync authorized users if specific access
            if ($data->access_level === HandoutAccessLevel::SPECIFIC_PLAYERS) {
                $handout->authorizedUsers()->sync($data->authorized_user_ids);
            } else {
                // Clear specific access if not needed
                $handout->authorizedUsers()->detach();
            }

            // Load relationships for the response
            $handout->load(['campaign', 'creator', 'authorizedUsers']);

            Log::info('Campaign handout updated', [
                'handout_id' => $handout->id,
                'title' => $data->title,
                'access_level' => $data->access_level->value,
                'is_visible_in_sidebar' => $data->is_visible_in_sidebar,
            ]);

            return CampaignHandoutData::from($handout);
        });
    }
}
