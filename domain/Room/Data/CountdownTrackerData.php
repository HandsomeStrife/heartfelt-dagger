<?php

declare(strict_types=1);

namespace Domain\Room\Data;

use Carbon\Carbon;
use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class CountdownTrackerData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        public string $id,
        public string $name,
        public int $value,
        public Carbon $updated_at,
        public bool $can_increase = true,
        public bool $can_decrease = true,
    ) {
        // Ensure value is not negative
        $this->value = max(0, $this->value);

        // Set capability flags
        $this->can_decrease = $this->value > 0;
        $this->can_increase = true; // Can always increase
    }

    /**
     * Create from array data (typically from database JSON)
     */
    public static function fromArray(string $id, array $data): self
    {
        return new self(
            id: $id,
            name: $data['name'],
            value: $data['value'],
            updated_at: Carbon::parse($data['updated_at'])
        );
    }

    /**
     * Create a new countdown tracker
     */
    public static function create(string $id, string $name, int $value): self
    {
        return new self(
            id: $id,
            name: $name,
            value: max(0, $value),
            updated_at: now()
        );
    }

    /**
     * Convert to array format for database storage
     */
    public function toStorageArray(): array
    {
        return [
            'name' => $this->name,
            'value' => $this->value,
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
