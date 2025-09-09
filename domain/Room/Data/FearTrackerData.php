<?php

declare(strict_types=1);

namespace Domain\Room\Data;

use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class FearTrackerData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        public int $fear_level,
        public int $max_fear_level = 255,
        public bool $can_increase = true,
        public bool $can_decrease = true,
    ) {
        // Ensure fear level is within bounds
        $this->fear_level = max(0, min($this->max_fear_level, $this->fear_level));
        
        // Set capability flags based on current level
        $this->can_increase = $this->fear_level < $this->max_fear_level;
        $this->can_decrease = $this->fear_level > 0;
    }

    /**
     * Create from a Campaign model
     */
    public static function fromCampaign(\Domain\Campaign\Models\Campaign $campaign): self
    {
        return new self(
            fear_level: $campaign->getFearLevel()
        );
    }

    /**
     * Create from a Room model
     */
    public static function fromRoom(\Domain\Room\Models\Room $room): self
    {
        return new self(
            fear_level: $room->getFearLevel()
        );
    }

    /**
     * Create a default instance with zero fear
     */
    public static function default(): self
    {
        return new self(fear_level: 0);
    }
}
