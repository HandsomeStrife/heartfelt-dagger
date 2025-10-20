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
     *
     * @return Collection<CharacterAdvancement>
     */
    public function getForCharacter(int $characterId): Collection
    {
        return CharacterAdvancement::where('character_id', $characterId)
            ->orderBy('level')
            ->orderBy('advancement_number')
            ->get();
    }

    /**
     * Get advancements for a specific level
     *
     * @return Collection<CharacterAdvancement>
     */
    public function getForLevel(int $characterId, int $level): Collection
    {
        return CharacterAdvancement::where('character_id', $characterId)
            ->where('level', $level)
            ->orderBy('advancement_number')
            ->get();
    }

    /**
     * Get advancements for a specific tier
     *
     * @return Collection<CharacterAdvancement>
     */
    public function getForTier(int $characterId, int $tier): Collection
    {
        return CharacterAdvancement::where('character_id', $characterId)
            ->where('tier', $tier)
            ->orderBy('level')
            ->orderBy('advancement_number')
            ->get();
    }

    /**
     * Get advancements up to a specific level (inclusive)
     *
     * @return Collection<CharacterAdvancement>
     */
    public function getUpToLevel(int $characterId, int $maxLevel): Collection
    {
        return CharacterAdvancement::where('character_id', $characterId)
            ->where('level', '<=', $maxLevel)
            ->orderBy('level')
            ->orderBy('advancement_number')
            ->get();
    }

    /**
     * Get advancements of a specific type for a character
     *
     * @return Collection<CharacterAdvancement>
     */
    public function getByType(int $characterId, string $advancementType): Collection
    {
        return CharacterAdvancement::where('character_id', $characterId)
            ->where('advancement_type', $advancementType)
            ->orderBy('level')
            ->get();
    }

    /**
     * Get advancements of a specific type for a tier
     *
     * @return Collection<CharacterAdvancement>
     */
    public function getByTypeForTier(int $characterId, string $advancementType, int $tier): Collection
    {
        return CharacterAdvancement::where('character_id', $characterId)
            ->where('advancement_type', $advancementType)
            ->where('tier', $tier)
            ->orderBy('level')
            ->get();
    }

    /**
     * Find a specific advancement
     */
    public function find(int $characterId, int $level, int $advancementNumber): ?CharacterAdvancement
    {
        return CharacterAdvancement::where('character_id', $characterId)
            ->where('level', $level)
            ->where('advancement_number', $advancementNumber)
            ->first();
    }

    /**
     * Check if an advancement exists at a level/slot
     */
    public function exists(int $characterId, int $level, int $advancementNumber): bool
    {
        return CharacterAdvancement::where('character_id', $characterId)
            ->where('level', $level)
            ->where('advancement_number', $advancementNumber)
            ->exists();
    }

    /**
     * Count advancements at a specific level
     */
    public function countForLevel(int $characterId, int $level): int
    {
        return CharacterAdvancement::where('character_id', $characterId)
            ->where('level', $level)
            ->count();
    }

    /**
     * Get all trait bonuses for a character (aggregated)
     * Returns array like ['agility' => 2, 'strength' => 1]
     */
    public function getTraitBonuses(int $characterId): array
    {
        $advancements = $this->getByType($characterId, 'trait_bonus');

        $bonuses = [];
        foreach ($advancements as $advancement) {
            $traits = $advancement->advancement_data['traits'] ?? [];
            $bonus = $advancement->advancement_data['bonus'] ?? 1;

            foreach ($traits as $trait) {
                $bonuses[$trait] = ($bonuses[$trait] ?? 0) + $bonus;
            }
        }

        return $bonuses;
    }

    /**
     * Get total evasion bonus from advancements
     */
    public function getEvasionBonus(int $characterId): int
    {
        return CharacterAdvancement::where('character_id', $characterId)
            ->where('advancement_type', 'evasion')
            ->sum('advancement_data->bonus') ?? 0;
    }

    /**
     * Get total hit point bonus from advancements
     */
    public function getHitPointBonus(int $characterId): int
    {
        return CharacterAdvancement::where('character_id', $characterId)
            ->where('advancement_type', 'hit_point')
            ->sum('advancement_data->bonus') ?? 0;
    }

    /**
     * Get total stress bonus from advancements
     */
    public function getStressBonus(int $characterId): int
    {
        return CharacterAdvancement::where('character_id', $characterId)
            ->where('advancement_type', 'stress')
            ->sum('advancement_data->bonus') ?? 0;
    }

    /**
     * Get total proficiency bonus from advancements
     */
    public function getProficiencyBonus(int $characterId): int
    {
        return CharacterAdvancement::where('character_id', $characterId)
            ->where('advancement_type', 'proficiency')
            ->sum('advancement_data->bonus') ?? 0;
    }

    /**
     * Get all experience bonuses for a character
     * Returns array like ['Tactics' => 1, 'Survival' => 2]
     */
    public function getExperienceBonuses(int $characterId): array
    {
        $advancements = $this->getByType($characterId, 'experience_bonus');

        $bonuses = [];
        foreach ($advancements as $advancement) {
            $experiences = $advancement->advancement_data['experiences'] ?? [];
            $bonus = $advancement->advancement_data['bonus'] ?? 1;

            foreach ($experiences as $experience) {
                $bonuses[$experience] = ($bonuses[$experience] ?? 0) + $bonus;
            }
        }

        return $bonuses;
    }

    /**
     * Get tier achievement experiences
     * Returns array like [2 => ['name' => 'Tactics', 'description' => '...'], ...]
     */
    public function getTierExperiences(int $characterId): array
    {
        $advancements = $this->getByType($characterId, 'tier_experience');

        $experiences = [];
        foreach ($advancements as $advancement) {
            $experiences[$advancement->level] = [
                'name' => $advancement->advancement_data['name'] ?? '',
                'description' => $advancement->advancement_data['description'] ?? '',
            ];
        }

        return $experiences;
    }

    /**
     * Get additional domain cards granted by advancements
     *
     * @return Collection<CharacterAdvancement>
     */
    public function getDomainCardAdvancements(int $characterId): Collection
    {
        return $this->getByType($characterId, 'domain_card');
    }

    /**
     * Delete all advancements for a character at a specific level
     */
    public function deleteForLevel(int $characterId, int $level): int
    {
        return CharacterAdvancement::where('character_id', $characterId)
            ->where('level', $level)
            ->delete();
    }

    /**
     * Delete all advancements for a character above a specific level
     */
    public function deleteAboveLevel(int $characterId, int $level): int
    {
        return CharacterAdvancement::where('character_id', $characterId)
            ->where('level', '>', $level)
            ->delete();
    }

    /**
     * Delete a specific advancement
     */
    public function delete(int $characterId, int $level, int $advancementNumber): bool
    {
        return CharacterAdvancement::where('character_id', $characterId)
            ->where('level', $level)
            ->where('advancement_number', $advancementNumber)
            ->delete() > 0;
    }

    /**
     * Get available advancement slots for a character at a specific level
     * 
     * @return array Array of available slot numbers (1 or 2)
     */
    public function getAvailableSlots(int $characterId, int $level): array
    {
        $existingAdvancements = CharacterAdvancement::where('character_id', $characterId)
            ->where('level', $level)
            ->pluck('advancement_number')
            ->toArray();

        $allSlots = [1, 2];
        return array_values(array_diff($allSlots, $existingAdvancements));
    }

    /**
     * Check if a character can level up
     * A character can level up if there are available slots at the next level
     */
    public function canLevelUp(Character $character): bool
    {
        $nextLevel = $character->level + 1;
        
        // Characters can level up to level 10
        if ($nextLevel > 10) {
            return false;
        }

        $availableSlots = $this->getAvailableSlots($character->id, $nextLevel);
        return count($availableSlots) > 0;
    }

    /**
     * Get all advancements for a character (alias for getForCharacter)
     * 
     * @return Collection<CharacterAdvancement>
     */
    public function getCharacterAdvancements(int $characterId): Collection
    {
        return $this->getForCharacter($characterId);
    }
}
