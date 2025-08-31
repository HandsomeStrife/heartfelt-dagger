<?php

declare(strict_types=1);

namespace Domain\Character\Data;

use Domain\Character\Models\Character;
use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
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
        public CharacterEquipmentData $equipment,
        public CharacterExperiencesData $experiences,
        public CharacterDomainCardsData $domain_cards,
        public CharacterBackgroundData $background,
        public CharacterConnectionsData $connections,
        public bool $is_public,
        public ?\DateTimeInterface $created_at,
        public ?\DateTimeInterface $updated_at,
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
            equipment: CharacterEquipmentData::fromModel($character),
            experiences: CharacterExperiencesData::fromModel($character),
            domain_cards: CharacterDomainCardsData::fromModel($character),
            background: CharacterBackgroundData::fromCharacterData($character->character_data),
            connections: CharacterConnectionsData::fromCharacterData($character->character_data),
            is_public: $character->is_public,
            created_at: $character->created_at,
            updated_at: $character->updated_at,
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
                return $s3Disk->temporaryUrl(
                    $this->profile_image_path,
                    now()->addHours(24) // URLs valid for 24 hours
                );
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
               $this->equipment->hasMinimumEquipment() &&
               $this->experiences->hasMinimumExperiences() &&
               $this->domain_cards->hasMinimumCards();
    }
}
