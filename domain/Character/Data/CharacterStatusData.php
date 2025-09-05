<?php

declare(strict_types=1);

namespace Domain\Character\Data;

use Domain\Character\Models\CharacterStatus;
use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Carbon\Carbon;
use Spatie\LaravelData\Data;

class CharacterStatusData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        public ?int $id,
        public int $character_id,
        public array $hit_points,
        public array $stress,
        public array $hope,
        public array $armor_slots,
        public array $gold_handfuls,
        public array $gold_bags,
        public bool $gold_chest,
        public ?Carbon $created_at,
        public ?Carbon $updated_at,
    ) {}

    public static function fromModel(CharacterStatus $status): self
    {
        return new self(
            id: $status->id,
            character_id: $status->character_id,
            hit_points: $status->hit_points ?? [],
            stress: $status->stress ?? [],
            hope: $status->hope ?? [],
            armor_slots: $status->armor_slots ?? [],
            gold_handfuls: $status->gold_handfuls ?? [],
            gold_bags: $status->gold_bags ?? [],
            gold_chest: $status->gold_chest ?? false,
            created_at: $status->created_at,
            updated_at: $status->updated_at,
        );
    }

    /**
     * Create default status data with empty arrays based on computed stats
     */
    public static function createDefault(int $character_id, array $computed_stats): self
    {
        return new self(
            id: null,
            character_id: $character_id,
            hit_points: array_fill(0, $computed_stats['final_hit_points'] ?? 6, false),
            stress: array_fill(0, $computed_stats['stress'] ?? 6, false),
            hope: array_fill(0, 6, false), // Hope is always 6 slots
            armor_slots: array_fill(0, $computed_stats['armor_score'] ?? 1, false),
            gold_handfuls: array_fill(0, 9, false), // Always 9 handfuls
            gold_bags: array_fill(0, 9, false), // Always 9 bags
            gold_chest: false,
            created_at: null,
            updated_at: null,
        );
    }

    /**
     * Get the total number of marked hit points
     */
    public function getMarkedHitPoints(): int
    {
        return count(array_filter($this->hit_points));
    }

    /**
     * Get the total number of marked stress
     */
    public function getMarkedStress(): int
    {
        return count(array_filter($this->stress));
    }

    /**
     * Get the total number of marked hope
     */
    public function getMarkedHope(): int
    {
        return count(array_filter($this->hope));
    }

    /**
     * Get the total number of marked armor slots
     */
    public function getMarkedArmorSlots(): int
    {
        return count(array_filter($this->armor_slots));
    }

    /**
     * Get the total number of marked gold handfuls
     */
    public function getMarkedGoldHandfuls(): int
    {
        return count(array_filter($this->gold_handfuls));
    }

    /**
     * Get the total number of marked gold bags
     */
    public function getMarkedGoldBags(): int
    {
        return count(array_filter($this->gold_bags));
    }

    /**
     * Convert to array format expected by Alpine.js
     */
    public function toAlpineState(): array
    {
        return [
            'hitPoints' => $this->hit_points,
            'stress' => $this->stress,
            'hope' => $this->hope,
            'armorSlots' => $this->armor_slots,
            'goldHandfuls' => $this->gold_handfuls,
            'goldBags' => $this->gold_bags,
            'goldChest' => $this->gold_chest,
        ];
    }

    /**
     * Create from Alpine.js state format
     */
    public static function fromAlpineState(int $character_id, array $state): self
    {
        return new self(
            id: null,
            character_id: $character_id,
            hit_points: $state['hitPoints'] ?? [],
            stress: $state['stress'] ?? [],
            hope: $state['hope'] ?? [],
            armor_slots: $state['armorSlots'] ?? [],
            gold_handfuls: $state['goldHandfuls'] ?? [],
            gold_bags: $state['goldBags'] ?? [],
            gold_chest: $state['goldChest'] ?? false,
            created_at: null,
            updated_at: null,
        );
    }
}


