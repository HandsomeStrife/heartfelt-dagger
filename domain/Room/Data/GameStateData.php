<?php

declare(strict_types=1);

namespace Domain\Room\Data;

use Illuminate\Support\Collection;
use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class GameStateData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        public FearTrackerData $fear_tracker,
        /** @var Collection<CountdownTrackerData> */
        public Collection $countdown_trackers,
        public string $source_type, // 'campaign' or 'room'
        public int $source_id,
    ) {}

    /**
     * Create from a Campaign model
     */
    public static function fromCampaign(\Domain\Campaign\Models\Campaign $campaign): self
    {
        $countdownTrackers = collect($campaign->getCountdownTrackers())
            ->map(fn (array $data, string $id) => CountdownTrackerData::fromArray($id, $data))
            ->values();

        return new self(
            fear_tracker: FearTrackerData::fromCampaign($campaign),
            countdown_trackers: $countdownTrackers,
            source_type: 'campaign',
            source_id: $campaign->id
        );
    }

    /**
     * Create from a Room model
     */
    public static function fromRoom(\Domain\Room\Models\Room $room): self
    {
        $countdownTrackers = collect($room->getCountdownTrackers())
            ->map(fn (array $data, string $id) => CountdownTrackerData::fromArray($id, $data))
            ->values();

        return new self(
            fear_tracker: FearTrackerData::fromRoom($room),
            countdown_trackers: $countdownTrackers,
            source_type: 'room',
            source_id: $room->id
        );
    }

    /**
     * Create a default empty state
     */
    public static function default(string $sourceType = 'room', int $sourceId = 0): self
    {
        return new self(
            fear_tracker: FearTrackerData::default(),
            countdown_trackers: collect(),
            source_type: $sourceType,
            source_id: $sourceId
        );
    }

    /**
     * Get a specific countdown tracker by ID
     */
    public function getCountdownTracker(string $id): ?CountdownTrackerData
    {
        return $this->countdown_trackers->first(fn (CountdownTrackerData $tracker) => $tracker->id === $id);
    }

    /**
     * Check if a countdown tracker exists
     */
    public function hasCountdownTracker(string $id): bool
    {
        return $this->getCountdownTracker($id) !== null;
    }

    /**
     * Get countdown trackers count
     */
    public function getCountdownTrackersCount(): int
    {
        return $this->countdown_trackers->count();
    }
}
