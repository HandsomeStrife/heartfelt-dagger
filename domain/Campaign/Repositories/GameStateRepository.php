<?php

declare(strict_types=1);

namespace Domain\Campaign\Repositories;

use Domain\Campaign\Data\CountdownTrackerData;
use Domain\Campaign\Data\FearTrackerData;
use Domain\Campaign\Data\GameStateData;
use Domain\Campaign\Models\Campaign;
use Domain\Room\Models\Room;
use Illuminate\Support\Collection;

class GameStateRepository
{
    /**
     * Get the complete game state for a room session
     * Uses campaign as primary source if room belongs to one, otherwise falls back to room
     */
    public function getGameStateForRoom(Room $room): GameStateData
    {
        // Load campaign if room belongs to one
        $room->load('campaign');
        
        // If room belongs to a campaign, use campaign game state
        if ($room->campaign_id && $room->campaign) {
            return GameStateData::fromCampaign($room->campaign);
        }

        // Otherwise use room's own game state (converted to campaign data format)
        return GameStateData::fromRoom($room);
    }

    /**
     * Get game state for a specific campaign
     */
    public function getGameStateForCampaign(Campaign $campaign): GameStateData
    {
        return GameStateData::fromCampaign($campaign);
    }

    /**
     * Get fear tracker data for a room session
     */
    public function getFearTrackerForRoom(Room $room): FearTrackerData
    {
        $room->load('campaign');
        
        if ($room->campaign_id && $room->campaign) {
            return FearTrackerData::fromCampaign($room->campaign);
        }

        return FearTrackerData::fromRoom($room);
    }

    /**
     * Get countdown trackers for a room session
     * 
     * @return Collection<CountdownTrackerData>
     */
    public function getCountdownTrackersForRoom(Room $room): Collection
    {
        $room->load('campaign');
        
        if ($room->campaign_id && $room->campaign) {
            return collect($room->campaign->getCountdownTrackers())
                ->map(fn (array $data, string $id) => CountdownTrackerData::fromArray($id, $data))
                ->values();
        }

        return collect($room->getCountdownTrackers())
            ->map(fn (array $data, string $id) => CountdownTrackerData::fromArray($id, $data))
            ->values();
    }

    /**
     * Get countdown trackers for a campaign
     * 
     * @return Collection<CountdownTrackerData>
     */
    public function getCountdownTrackersForCampaign(Campaign $campaign): Collection
    {
        return collect($campaign->getCountdownTrackers())
            ->map(fn (array $data, string $id) => CountdownTrackerData::fromArray($id, $data))
            ->values();
    }

    /**
     * Find a specific countdown tracker by ID for a room session
     */
    public function findCountdownTrackerForRoom(Room $room, string $trackerId): ?CountdownTrackerData
    {
        $room->load('campaign');
        
        if ($room->campaign_id && $room->campaign) {
            $tracker = $room->campaign->getCountdownTracker($trackerId);
            return $tracker ? CountdownTrackerData::fromArray($trackerId, $tracker) : null;
        }

        $tracker = $room->getCountdownTracker($trackerId);
        return $tracker ? CountdownTrackerData::fromArray($trackerId, $tracker) : null;
    }

    /**
     * Check if a room session has any active countdown trackers
     */
    public function hasActiveCountdownTrackers(Room $room): bool
    {
        return $this->getCountdownTrackersForRoom($room)->isNotEmpty();
    }

    /**
     * Get the source of truth for game state (campaign or room)
     */
    public function getGameStateSource(Room $room): array
    {
        $room->load('campaign');
        
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
