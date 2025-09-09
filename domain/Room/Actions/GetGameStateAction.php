<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Campaign\Models\Campaign;
use Domain\Room\Data\GameStateData;
use Domain\Room\Models\Room;

class GetGameStateAction
{
    /**
     * Get the complete game state (fear + countdown trackers) for a room session
     * Uses campaign data if the room belongs to a campaign, otherwise falls back to room data
     */
    public function execute(Room $room): GameStateData
    {
        // If room belongs to a campaign, use campaign game state
        if ($room->campaign_id && $room->campaign) {
            return GameStateData::fromCampaign($room->campaign);
        }

        // Otherwise use room's own game state
        return GameStateData::fromRoom($room);
    }

    /**
     * Get game state specifically from a campaign
     */
    public function executeForCampaign(Campaign $campaign): GameStateData
    {
        return GameStateData::fromCampaign($campaign);
    }

    /**
     * Get game state specifically from a room (regardless of campaign association)
     */
    public function executeForRoom(Room $room): GameStateData
    {
        return GameStateData::fromRoom($room);
    }

    /**
     * Determine the source of truth for game state (campaign or room)
     */
    public function getGameStateSource(Room $room): array
    {
        if ($room->campaign_id && $room->campaign) {
            return [
                'type' => 'campaign',
                'id' => $room->campaign->id,
                'model' => $room->campaign,
            ];
        }

        return [
            'type' => 'room',
            'id' => $room->id,
            'model' => $room,
        ];
    }
}
