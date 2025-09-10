<?php

declare(strict_types=1);

namespace Domain\Character\Repositories;

use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterAdvancement;
use Illuminate\Support\Collection;

class CharacterAdvancementRepository
{
    /**
     * Get all advancements for a character
     */
    public function getCharacterAdvancements(int $character_id): Collection
    {
        return CharacterAdvancement::where('character_id', $character_id)
            ->orderBy('tier')
            ->orderBy('advancement_number')
            ->get();
    }

    /**
     * Get advancements for a specific tier
     */
    public function getAdvancementsForTier(int $character_id, int $tier): Collection
    {
        return CharacterAdvancement::where('character_id', $character_id)
            ->where('tier', $tier)
            ->orderBy('advancement_number')
            ->get();
    }

    /**
     * Get available advancement slots for a tier
     */
    public function getAvailableSlots(int $character_id, int $tier): array
    {
        $existing = $this->getAdvancementsForTier($character_id, $tier);
        $used_slots = $existing->pluck('advancement_number')->toArray();

        $available_slots = [];
        for ($i = 1; $i <= 2; $i++) {
            if (! in_array($i, $used_slots)) {
                $available_slots[] = $i;
            }
        }

        return $available_slots;
    }

    /**
     * Check if character can advance to next level
     */
    public function canLevelUp(Character $character): bool
    {
        $current_tier = $character->getTier();
        $available_slots = $this->getAvailableSlots($character->id, $current_tier);

        // Character can level up if they have advancement slots available for their current tier
        return count($available_slots) > 0;
    }

    /**
     * Get advancement selection counts by type across all tiers
     */
    public function getAdvancementCounts(int $character_id): array
    {
        $advancements = $this->getCharacterAdvancements($character_id);
        $counts = [];

        foreach ($advancements as $advancement) {
            $type = $advancement->advancement_type;
            if (! isset($counts[$type])) {
                $counts[$type] = 0;
            }
            $counts[$type]++;
        }

        return $counts;
    }

    /**
     * Get advancement selections for a specific type and tier
     */
    public function getAdvancementCountsForTier(int $character_id, int $tier, string $type): int
    {
        return CharacterAdvancement::where('character_id', $character_id)
            ->where('tier', $tier)
            ->where('advancement_type', $type)
            ->count();
    }

    /**
     * Check if a specific advancement type has been selected in a tier
     */
    public function hasAdvancementInTier(int $character_id, int $tier, string $type): bool
    {
        return $this->getAdvancementCountsForTier($character_id, $tier, $type) > 0;
    }

    /**
     * Get trait bonuses from all advancements
     */
    public function getTraitBonuses(int $character_id): array
    {
        $advancements = CharacterAdvancement::where('character_id', $character_id)
            ->where('advancement_type', 'trait_bonus')
            ->get();

        $bonuses = [
            'agility' => 0,
            'strength' => 0,
            'finesse' => 0,
            'instinct' => 0,
            'presence' => 0,
            'knowledge' => 0,
        ];

        foreach ($advancements as $advancement) {
            $advancement_bonuses = $advancement->getTraitBonuses();
            foreach ($advancement_bonuses as $trait => $bonus) {
                $bonuses[$trait] += $bonus;
            }
        }

        return $bonuses;
    }

    /**
     * Get total evasion bonus from advancements
     */
    public function getEvasionBonus(int $character_id): int
    {
        return (int) (CharacterAdvancement::where('character_id', $character_id)
            ->where('advancement_type', 'evasion')
            ->sum('advancement_data->bonus') ?? 0);
    }

    /**
     * Get total hit point bonus from advancements
     */
    public function getHitPointBonus(int $character_id): int
    {
        return (int) (CharacterAdvancement::where('character_id', $character_id)
            ->where('advancement_type', 'hit_point')
            ->sum('advancement_data->bonus') ?? 0);
    }

    /**
     * Get total stress bonus from advancements
     */
    public function getStressBonus(int $character_id): int
    {
        return (int) (CharacterAdvancement::where('character_id', $character_id)
            ->where('advancement_type', 'stress')
            ->sum('advancement_data->bonus') ?? 0);
    }

    /**
     * Get total proficiency bonus from advancements
     */
    public function getProficiencyBonus(int $character_id): int
    {
        return (int) (CharacterAdvancement::where('character_id', $character_id)
            ->where('advancement_type', 'proficiency')
            ->sum('advancement_data->bonus') ?? 0);
    }

    /**
     * Get marked traits (traits that have been advanced and can't be advanced again until tier achievement clears them)
     */
    public function getMarkedTraits(Character $character): array
    {
        $trait_advancements = CharacterAdvancement::where('character_id', $character->id)
            ->where('advancement_type', 'trait_bonus')
            ->get();

        $marked_traits = [];
        $current_tier = $character->getTier();

        foreach ($trait_advancements as $advancement) {
            $advancement_tier = $advancement->tier;
            $traits = $advancement->advancement_data['traits'] ?? [];

            // Traits are marked if they were advanced in current tier or later
            // Tier achievements at levels 5 and 8 clear marks
            $trait_cleared = false;
            if ($character->level >= 8 && $advancement_tier <= 3) {
                $trait_cleared = true; // Level 8 achievement clears tier 1-3 marks
            } elseif ($character->level >= 5 && $advancement_tier <= 2) {
                $trait_cleared = true; // Level 5 achievement clears tier 1-2 marks
            }

            if (! $trait_cleared) {
                foreach ($traits as $trait) {
                    $marked_traits[] = $trait;
                }
            }
        }

        return array_unique($marked_traits);
    }
}
