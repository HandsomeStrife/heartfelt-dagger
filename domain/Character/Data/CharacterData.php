<?php

declare(strict_types=1);

namespace Domain\Character\Data;

use Domain\Character\Models\Character;
use Illuminate\Support\Collection;
use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Carbon\Carbon;
use Spatie\LaravelData\Data;

class CharacterData extends Data implements Wireable
{
    use WireableData;
    
    public function __construct(
        public ?int $id,
        public string $character_key,
        public ?int $user_id,
        public string $name,
        public string $class,
        public ?string $pronouns,
        public ?string $subclass,
        public string $ancestry,
        public string $community,
        public int $level,
        public ?string $profile_image_path,
        public CharacterStatsData $stats,
        public CharacterTraitsData $traits,
        /** @var Collection<int, CharacterEquipmentItemData> */
        public Collection $equipment,
        /** @var Collection<int, CharacterExperienceData> */
        public Collection $experiences,
        /** @var Collection<int, CharacterDomainCardData> */
        public Collection $domain_cards,
        public CharacterBackgroundData $background,
        /** @var Collection<int, CharacterConnectionData> */
        public Collection $connections,
        public bool $is_public,
        public ?Carbon $created_at,
        public ?Carbon $updated_at,
    ) {}

    public static function fromModel(Character $character): self
    {
        return new self(
            id: $character->id,
            character_key: $character->character_key,
            user_id: $character->user_id,
            name: $character->name ?: 'Unnamed Character',
            class: $character->class ?: 'Warrior',
            subclass: $character->subclass,
            ancestry: $character->ancestry ?: 'Human',
            community: $character->community ?: 'Wanderborne',
            level: $character->level,
            profile_image_path: $character->profile_image_path,
            stats: CharacterStatsData::fromModel($character),
            traits: CharacterTraitsData::fromModel($character),
            equipment: self::buildEquipmentCollection($character),
            experiences: self::buildExperiencesCollection($character),
            domain_cards: self::buildDomainCardsCollection($character),
            background: CharacterBackgroundData::fromCharacterData($character->character_data),
            connections: self::buildConnectionsCollection($character->character_data),
            is_public: $character->is_public,
            created_at: $character->created_at,
            updated_at: $character->updated_at,
            pronouns: $character->pronouns,
        );
    }

    public function getBanner(): string
    {
        return asset('img/banners/'.strtolower($this->class).'.webp');
    }

    public function getProfileImage(): string
    {
        if ($this->profile_image_path) {
            $s3Disk = \Illuminate\Support\Facades\Storage::disk('s3');
            if ($s3Disk->exists($this->profile_image_path)) {
                return $s3Disk->url($this->profile_image_path);
            }
        }

        return asset('img/default-avatar.png');
    }

    public function getShareUrl(): string
    {
        return route('character-builder', ['characterKey' => $this->character_key]);
    }

    public function isComplete(): bool
    {
        return ! empty($this->name) &&
               ! empty($this->class) &&
               ! empty($this->ancestry) &&
               ! empty($this->community) &&
               $this->traits->isComplete() &&
               $this->hasMinimumEquipment() &&
               $this->experiences->count() >= 2 &&
               $this->domain_cards->count() >= 2;
    }

    /**
     * Check if character has minimum equipment for completion
     */
    public function hasMinimumEquipment(): bool
    {
        return $this->getEquippedWeapons()->isNotEmpty();
    }

    /**
     * Get all equipped weapons
     */
    public function getEquippedWeapons(): Collection
    {
        return $this->equipment->filter(fn ($item) => $item->isEquippedWeapon());
    }

    /**
     * Get all equipped armor
     */
    public function getEquippedArmor(): Collection
    {
        return $this->equipment->filter(fn ($item) => $item->isEquippedArmor());
    }

    /**
     * Get total armor score from all equipped armor
     */
    public function getTotalArmorScore(): int
    {
        $baseArmorScore = 1; // Base armor score for all characters
        $equippedArmorScore = $this->getEquippedArmor()->sum(fn ($armor) => $armor->getArmorScore());
        
        return $baseArmorScore + $equippedArmorScore;
    }

    /**
     * Get major damage threshold according to DaggerHeart SRD
     */
    public function getMajorThreshold(): int
    {
        $equippedArmor = $this->getEquippedArmor();
        
        if ($equippedArmor->isEmpty()) {
            // Unarmored: Major threshold = character level
            return max(1, $this->level);
        }
        
        // With armor: Use armor's base threshold + level bonus (level - 1) + other bonuses
        $baseThreshold = $this->getHighestArmorMajorThreshold($equippedArmor);
        $levelBonus = max(0, $this->level - 1); // Level bonus starts at 0 for level 1
        
        // Get bonuses from ancestry, subclass, and advancements (if any)
        $damageThresholdBonus = 0; // Placeholder for future implementation
        
        return max(1, $baseThreshold + $levelBonus + $damageThresholdBonus);
    }

    /**
     * Get severe damage threshold according to DaggerHeart SRD
     */
    public function getSevereThreshold(): int
    {
        $equippedArmor = $this->getEquippedArmor();
        
        if ($equippedArmor->isEmpty()) {
            // Unarmored: Severe threshold = 2 × character level
            return max(1, $this->level * 2);
        }
        
        // With armor: Use armor's base severe threshold + level bonus + other bonuses
        $baseThreshold = $this->getHighestArmorSevereThreshold($equippedArmor);
        $levelBonus = max(0, $this->level - 1); // Level bonus starts at 0 for level 1
        
        // Get severe threshold bonuses from subclass (if any)
        $severeThresholdBonus = 0; // Placeholder for future implementation
        
        return max(1, $baseThreshold + $levelBonus + $severeThresholdBonus);
    }

    /**
     * Get the highest major threshold from equipped armor
     */
    private function getHighestArmorMajorThreshold(Collection $equippedArmor): int
    {
        $highestThreshold = 0;
        
        foreach ($equippedArmor as $armor) {
            $thresholds = $armor->getThresholds();
            if ($thresholds && isset($thresholds['lower'])) {
                $highestThreshold = max($highestThreshold, $thresholds['lower']);
            }
        }
        
        return $highestThreshold;
    }

    /**
     * Get the highest severe threshold from equipped armor
     */
    private function getHighestArmorSevereThreshold(Collection $equippedArmor): int
    {
        $highestThreshold = 0;
        
        foreach ($equippedArmor as $armor) {
            $thresholds = $armor->getThresholds();
            if ($thresholds && isset($thresholds['higher'])) {
                $highestThreshold = max($highestThreshold, $thresholds['higher']);
            }
        }
        
        return $highestThreshold;
    }

    /**
     * Get proficiency bonus based on level
     */
    private function getProficiencyBonus(): int
    {
        return match (true) {
            $this->level >= 17 => 6,
            $this->level >= 13 => 5,
            $this->level >= 9 => 4,
            $this->level >= 5 => 3,
            default => 2,
        };
    }

    /**
     * @return Collection<int, CharacterEquipmentItemData>
     */
    private static function buildEquipmentCollection(Character $character): Collection
    {
        return $character->equipment()->get()->map(function ($equipment) {
            return new CharacterEquipmentItemData(
                id: $equipment->id,
                character_id: $equipment->character_id,
                equipment_type: $equipment->equipment_type,
                equipment_key: $equipment->equipment_key,
                equipment_data: $equipment->equipment_data ?? [],
                is_equipped: $equipment->is_equipped ?? false,
            );
        });
    }

    /**
     * @return Collection<int, CharacterExperienceData>
     */
    private static function buildExperiencesCollection(Character $character): Collection
    {
        return $character->experiences()->get()->map(function ($experience) {
            return new CharacterExperienceData(
                name: $experience->experience_name ?? $experience->experience_data['name'] ?? '',
                description: $experience->experience_description ?? $experience->experience_data['description'] ?? '',
                modifier: $experience->modifier ?? $experience->experience_data['modifier'] ?? 2,
                category: $experience->getCategory(),
                is_clank_bonus: $experience->experience_data['is_clank_bonus'] ?? false,
            );
        });
    }

    /**
     * @return Collection<int, CharacterDomainCardData>
     */
    private static function buildDomainCardsCollection(Character $character): Collection
    {
        return $character->domainCards()->get()->map(function ($card) {
            return new CharacterDomainCardData(
                ability_key: $card->ability_key ?? '',
                domain: $card->domain ?? '',
                level: $card->level ?? 1,
                name: $card->card_data['name'] ?? '',
                type: $card->card_data['type'] ?? '',
                recall_cost: $card->card_data['recall_cost'] ?? 0,
                descriptions: $card->card_data['descriptions'] ?? [],
            );
        });
    }

    /**
     * @return Collection<int, CharacterConnectionData>
     */
    private static function buildConnectionsCollection(array $characterData): Collection
    {
        $connections = $characterData['connections'] ?? [];
        
        return collect($connections)->map(function ($connection) {
            return new CharacterConnectionData(
                character_name: $connection['character_name'] ?? '',
                connection_type: $connection['connection_type'] ?? '',
                description: $connection['description'] ?? '',
            );
        });
    }
}
