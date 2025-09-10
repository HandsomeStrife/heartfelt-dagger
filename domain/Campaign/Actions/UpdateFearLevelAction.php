<?php

declare(strict_types=1);

namespace Domain\Campaign\Actions;

use Domain\Campaign\Data\FearTrackerData;
use Domain\Campaign\Models\Campaign;
use Domain\Room\Models\Room;

class UpdateFearLevelAction
{
    /**
     * Updates fear level for a campaign or room (with fallback logic)
     * If a campaign is provided, updates campaign fear level
     * If only room is provided and room has no campaign, updates room fear level
     */
    public function execute(?Campaign $campaign, ?Room $room, int $new_fear_level): FearTrackerData
    {
        if (! $campaign && ! $room) {
            throw new \InvalidArgumentException('Either campaign or room must be provided');
        }

        // Primary source is campaign if available
        if ($campaign) {
            $campaign->setFearLevel($new_fear_level);
            $campaign->save();

            return FearTrackerData::fromCampaign($campaign);
        }

        // Fallback to room if no campaign
        if ($room && ! $room->campaign_id) {
            $room->setFearLevel($new_fear_level);
            $room->save();

            return FearTrackerData::fromRoom($room);
        }

        throw new \InvalidArgumentException('Room has an associated campaign, use campaign for fear tracking');
    }

    /**
     * Increase fear level by specified amount
     */
    public function increaseFear(?Campaign $campaign, ?Room $room, int $amount = 1): FearTrackerData
    {
        if (! $campaign && ! $room) {
            throw new \InvalidArgumentException('Either campaign or room must be provided');
        }

        if ($campaign) {
            $campaign->increaseFear($amount);
            $campaign->save();

            return FearTrackerData::fromCampaign($campaign);
        }

        if ($room && ! $room->campaign_id) {
            $room->increaseFear($amount);
            $room->save();

            return FearTrackerData::fromRoom($room);
        }

        throw new \InvalidArgumentException('Room has an associated campaign, use campaign for fear tracking');
    }

    /**
     * Decrease fear level by specified amount
     */
    public function decreaseFear(?Campaign $campaign, ?Room $room, int $amount = 1): FearTrackerData
    {
        if (! $campaign && ! $room) {
            throw new \InvalidArgumentException('Either campaign or room must be provided');
        }

        if ($campaign) {
            $campaign->decreaseFear($amount);
            $campaign->save();

            return FearTrackerData::fromCampaign($campaign);
        }

        if ($room && ! $room->campaign_id) {
            $room->decreaseFear($amount);
            $room->save();

            return FearTrackerData::fromRoom($room);
        }

        throw new \InvalidArgumentException('Room has an associated campaign, use campaign for fear tracking');
    }
}
