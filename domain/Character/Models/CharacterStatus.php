<?php

declare(strict_types=1);

namespace Domain\Character\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharacterStatus extends Model
{
    protected $table = 'character_status';

    protected $fillable = [
        'character_id',
        'hit_points',
        'stress',
        'hope',
        'armor_slots',
        'gold_handfuls',
        'gold_bags',
        'gold_chest',
    ];

    protected $casts = [
        'hit_points' => 'array',
        'stress' => 'array',
        'hope' => 'array',
        'armor_slots' => 'array',
        'gold_handfuls' => 'array',
        'gold_bags' => 'array',
        'gold_chest' => 'boolean',
    ];

    /**
     * Get the character that owns this status
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Create default status arrays based on character's computed stats
     */
    public static function createDefaultStatus(Character $character, array $computed_stats): array
    {
        return [
            'character_id' => $character->id,
            'hit_points' => array_fill(0, $computed_stats['final_hit_points'] ?? 6, false),
            'stress' => array_fill(0, $computed_stats['stress'] ?? 6, false),
            'hope' => array_fill(0, 6, false), // Hope is always 6 slots
            'armor_slots' => array_fill(0, $computed_stats['armor_score'] ?? 1, false),
            'gold_handfuls' => array_fill(0, 9, false), // Always 9 handfuls
            'gold_bags' => array_fill(0, 9, false), // Always 9 bags
            'gold_chest' => false,
        ];
    }

    /**
     * Update status arrays to match current computed stats (in case stats changed)
     */
    public function adjustToComputedStats(array $computed_stats): void
    {
        $hit_points_needed = $computed_stats['final_hit_points'] ?? 6;
        $stress_needed = $computed_stats['stress'] ?? 6;
        $armor_slots_needed = $computed_stats['armor_score'] ?? 1;

        // Adjust hit points array
        $current_hp = $this->hit_points ?? [];
        if (count($current_hp) !== $hit_points_needed) {
            $new_hp = array_fill(0, $hit_points_needed, false);
            // Copy over existing marks that still fit
            for ($i = 0; $i < min(count($current_hp), $hit_points_needed); $i++) {
                $new_hp[$i] = $current_hp[$i] ?? false;
            }
            $this->hit_points = $new_hp;
        }

        // Adjust stress array
        $current_stress = $this->stress ?? [];
        if (count($current_stress) !== $stress_needed) {
            $new_stress = array_fill(0, $stress_needed, false);
            // Copy over existing marks that still fit
            for ($i = 0; $i < min(count($current_stress), $stress_needed); $i++) {
                $new_stress[$i] = $current_stress[$i] ?? false;
            }
            $this->stress = $new_stress;
        }

        // Adjust armor slots array
        $current_armor = $this->armor_slots ?? [];
        if (count($current_armor) !== $armor_slots_needed) {
            $new_armor = array_fill(0, $armor_slots_needed, false);
            // Copy over existing marks that still fit
            for ($i = 0; $i < min(count($current_armor), $armor_slots_needed); $i++) {
                $new_armor[$i] = $current_armor[$i] ?? false;
            }
            $this->armor_slots = $new_armor;
        }

        // Hope is always 6 slots, gold is always 9+9+1, so no adjustment needed
        if (! $this->hope) {
            $this->hope = array_fill(0, 6, false);
        }
        if (! $this->gold_handfuls) {
            $this->gold_handfuls = array_fill(0, 9, false);
        }
        if (! $this->gold_bags) {
            $this->gold_bags = array_fill(0, 9, false);
        }
    }
}
