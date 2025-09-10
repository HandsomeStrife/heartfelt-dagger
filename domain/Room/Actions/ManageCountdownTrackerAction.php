<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Campaign\Models\Campaign;
use Domain\Room\Data\CountdownTrackerData;
use Domain\Room\Models\Room;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ManageCountdownTrackerAction
{
    /**
     * Create a new countdown tracker
     */
    public function createCountdownTracker(?Campaign $campaign, ?Room $room, string $name, int $value): CountdownTrackerData
    {
        if (! $campaign && ! $room) {
            throw new \InvalidArgumentException('Either campaign or room must be provided');
        }

        $id = Str::uuid()->toString();
        $tracker = CountdownTrackerData::create($id, $name, $value);

        if ($campaign) {
            $campaign->setCountdownTracker($id, $name, $value);
            $campaign->save();
        } elseif ($room && ! $room->campaign_id) {
            $room->setCountdownTracker($id, $name, $value);
            $room->save();
        } else {
            throw new \InvalidArgumentException('Room has an associated campaign, use campaign for countdown tracking');
        }

        return $tracker;
    }

    /**
     * Update an existing countdown tracker
     */
    public function updateCountdownTracker(?Campaign $campaign, ?Room $room, string $id, string $name, int $value): CountdownTrackerData
    {
        if (! $campaign && ! $room) {
            throw new \InvalidArgumentException('Either campaign or room must be provided');
        }

        if ($campaign) {
            $existingTracker = $campaign->getCountdownTracker($id);
            if (! $existingTracker) {
                throw new \InvalidArgumentException("Countdown tracker with ID '{$id}' not found");
            }

            $campaign->setCountdownTracker($id, $name, $value);
            $campaign->save();

            return CountdownTrackerData::fromArray($id, $campaign->getCountdownTracker($id));
        }

        if ($room && ! $room->campaign_id) {
            $existingTracker = $room->getCountdownTracker($id);
            if (! $existingTracker) {
                throw new \InvalidArgumentException("Countdown tracker with ID '{$id}' not found");
            }

            $room->setCountdownTracker($id, $name, $value);
            $room->save();

            return CountdownTrackerData::fromArray($id, $room->getCountdownTracker($id));
        }

        throw new \InvalidArgumentException('Room has an associated campaign, use campaign for countdown tracking');
    }

    /**
     * Increase countdown tracker value
     */
    public function increaseCountdownTracker(?Campaign $campaign, ?Room $room, string $id, int $amount = 1): CountdownTrackerData
    {
        if (! $campaign && ! $room) {
            throw new \InvalidArgumentException('Either campaign or room must be provided');
        }

        if ($campaign) {
            $tracker = $campaign->getCountdownTracker($id);
            if (! $tracker) {
                throw new \InvalidArgumentException("Countdown tracker with ID '{$id}' not found");
            }

            $newValue = $tracker['value'] + $amount;
            $campaign->setCountdownTracker($id, $tracker['name'], $newValue);
            $campaign->save();

            return CountdownTrackerData::fromArray($id, $campaign->getCountdownTracker($id));
        }

        if ($room && ! $room->campaign_id) {
            $tracker = $room->getCountdownTracker($id);
            if (! $tracker) {
                throw new \InvalidArgumentException("Countdown tracker with ID '{$id}' not found");
            }

            $newValue = $tracker['value'] + $amount;
            $room->setCountdownTracker($id, $tracker['name'], $newValue);
            $room->save();

            return CountdownTrackerData::fromArray($id, $room->getCountdownTracker($id));
        }

        throw new \InvalidArgumentException('Room has an associated campaign, use campaign for countdown tracking');
    }

    /**
     * Decrease countdown tracker value
     */
    public function decreaseCountdownTracker(?Campaign $campaign, ?Room $room, string $id, int $amount = 1): CountdownTrackerData
    {
        if (! $campaign && ! $room) {
            throw new \InvalidArgumentException('Either campaign or room must be provided');
        }

        if ($campaign) {
            $tracker = $campaign->getCountdownTracker($id);
            if (! $tracker) {
                throw new \InvalidArgumentException("Countdown tracker with ID '{$id}' not found");
            }

            $newValue = max(0, $tracker['value'] - $amount);
            $campaign->setCountdownTracker($id, $tracker['name'], $newValue);
            $campaign->save();

            return CountdownTrackerData::fromArray($id, $campaign->getCountdownTracker($id));
        }

        if ($room && ! $room->campaign_id) {
            $tracker = $room->getCountdownTracker($id);
            if (! $tracker) {
                throw new \InvalidArgumentException("Countdown tracker with ID '{$id}' not found");
            }

            $newValue = max(0, $tracker['value'] - $amount);
            $room->setCountdownTracker($id, $tracker['name'], $newValue);
            $room->save();

            return CountdownTrackerData::fromArray($id, $room->getCountdownTracker($id));
        }

        throw new \InvalidArgumentException('Room has an associated campaign, use campaign for countdown tracking');
    }

    /**
     * Delete a countdown tracker
     */
    public function deleteCountdownTracker(?Campaign $campaign, ?Room $room, string $id): bool
    {
        if (! $campaign && ! $room) {
            throw new \InvalidArgumentException('Either campaign or room must be provided');
        }

        if ($campaign) {
            $tracker = $campaign->getCountdownTracker($id);
            if (! $tracker) {
                return false; // Already doesn't exist
            }

            $campaign->removeCountdownTracker($id);
            $campaign->save();

            return true;
        }

        if ($room && ! $room->campaign_id) {
            $tracker = $room->getCountdownTracker($id);
            if (! $tracker) {
                return false; // Already doesn't exist
            }

            $room->removeCountdownTracker($id);
            $room->save();

            return true;
        }

        throw new \InvalidArgumentException('Room has an associated campaign, use campaign for countdown tracking');
    }

    /**
     * Get all countdown trackers
     */
    public function getCountdownTrackers(?Campaign $campaign, ?Room $room): Collection
    {
        if (! $campaign && ! $room) {
            throw new \InvalidArgumentException('Either campaign or room must be provided');
        }

        if ($campaign) {
            return collect($campaign->getCountdownTrackers())
                ->map(fn (array $data, string $id) => CountdownTrackerData::fromArray($id, $data))
                ->values();
        }

        if ($room && ! $room->campaign_id) {
            return collect($room->getCountdownTrackers())
                ->map(fn (array $data, string $id) => CountdownTrackerData::fromArray($id, $data))
                ->values();
        }

        throw new \InvalidArgumentException('Room has an associated campaign, use campaign for countdown tracking');
    }
}
