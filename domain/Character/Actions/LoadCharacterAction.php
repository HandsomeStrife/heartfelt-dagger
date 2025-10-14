<?php

declare(strict_types=1);

namespace Domain\Character\Actions;

use Domain\Character\Data\CharacterBuilderData;
use Domain\Character\Models\Character;

class LoadCharacterAction
{
    public function execute(string $character_key): ?CharacterBuilderData
    {
        // Try to find by character_key first
        $character = Character::where('character_key', $character_key)->first();

        // If not found, try to find by public_key
        if (! $character) {
            $character = Character::where('public_key', $character_key)->first();
        }

        if (! $character) {
            return null;
        }

        return $this->convertToBuilderData($character);
    }

    public function executeById(int $character_id): ?CharacterBuilderData
    {
        $character = Character::find($character_id);

        if (! $character) {
            return null;
        }

        return $this->convertToBuilderData($character);
    }

    private function convertToBuilderData(Character $character): CharacterBuilderData
    {
        // Load related data
        $traits = $character->traits()->pluck('trait_value', 'trait_name')->toArray();
        $equipment = $character->equipment()->get()->map(function ($item) {
            return [
                'key' => $item->equipment_key,
                'type' => $item->equipment_type,
                'data' => $item->equipment_data,
            ];
        })->toArray();

        $domain_cards = $character->domainCards()->get()->map(function ($card) {
            return [
                'domain' => $card->domain,
                'ability_key' => $card->ability_key,
                'ability_level' => $card->ability_level,
            ];
        })->toArray();

        $experiences = $character->experiences()->get()->map(function ($exp) {
            return [
                'name' => $exp->experience_name,
                'description' => $exp->experience_description,
                'modifier' => $exp->modifier,
            ];
        })->toArray();

        $character_data = $character->character_data ?? [];
        $background_answers = $character_data['background']['answers'] ?? [];
        $connection_answers = $character_data['connections'] ?? [];
        $manual_step_completions = $character_data['manualStepCompletions'] ?? [];
        $clank_bonus_experience = $character_data['clank_bonus_experience'] ?? null;

        // Load advancement data for higher-level characters
        [$creation_advancements, $creation_tier_experiences, $creation_domain_cards] = $this->loadAdvancementData($character, $character_data);

        return new CharacterBuilderData(
            character_key: $character->character_key,
            public_key: $character->public_key,
            name: $character->name,
            selected_class: $character->class,
            selected_subclass: $character->subclass,
            selected_ancestry: $character->ancestry,
            selected_community: $character->community,
            assigned_traits: $traits,
            selected_equipment: $equipment,
            experiences: $experiences,
            selected_domain_cards: $domain_cards,
            background_answers: $background_answers,
            connection_answers: $connection_answers,
            profile_image_path: $character->profile_image_path,
            physical_description: $character_data['background']['physicalDescription'] ?? null,
            personality_traits: $character_data['background']['personalityTraits'] ?? null,
            personal_history: $character_data['background']['personalHistory'] ?? null,
            motivations: $character_data['background']['motivations'] ?? null,
            manual_step_completions: $manual_step_completions,
            clank_bonus_experience: $clank_bonus_experience,
            starting_level: $character->level,
            creation_advancements: $creation_advancements,
            creation_tier_experiences: $creation_tier_experiences,
            creation_domain_cards: $creation_domain_cards,
        );
    }

    /**
     * Load advancement data for higher-level characters
     * 
     * @param array<string, mixed> $character_data
     * @return array{0: array<int, array>, 1: array<int, array>, 2: array<int, array>}
     */
    private function loadAdvancementData(Character $character, array $character_data): array
    {
        // If level 1, no advancements to load
        if ($character->level === 1) {
            return [[], [], []];
        }

        // Load all advancements ordered by level and advancement number
        $advancements = $character->advancements()
            ->orderBy('level')
            ->orderBy('advancement_number')
            ->get();

        $creation_advancements = [];
        $creation_tier_experiences = [];
        
        // Group advancements by level
        foreach ($advancements as $advancement) {
            $level = $advancement->level;
            
            if (! isset($creation_advancements[$level])) {
                $creation_advancements[$level] = [];
            }

            $creation_advancements[$level][] = [
                'type' => $advancement->advancement_type,
                'data' => $advancement->advancement_data,
            ];
        }

        // Extract tier achievement experiences from character_data
        // These are stored in the character_data JSON field
        $tier_achievement_data = $character_data['tier_achievements'] ?? [];
        if (! empty($tier_achievement_data)) {
            foreach ($tier_achievement_data as $level => $achievements) {
                if (in_array($level, [2, 5, 8])) {
                    $creation_tier_experiences[$level] = $achievements['experiences'] ?? [];
                }
            }
        }

        // Organize domain cards by level
        // Domain cards are already in the database with their level
        $creation_domain_cards = [];
        $domain_card_records = $character->domainCards()->orderBy('id')->get();
        
        // Distribute domain cards across levels (1 per level)
        // The first card belongs to level 1, second to level 2, etc.
        foreach ($domain_card_records as $index => $card) {
            $card_level = $index + 1; // Levels are 1-indexed
            $creation_domain_cards[$card_level] = $card->ability_key;
        }

        return [$creation_advancements, $creation_tier_experiences, $creation_domain_cards];
    }

    public function loadForUser(int $user_id): array
    {
        $characters = Character::where('user_id', $user_id)
            ->orderBy('updated_at', 'desc')
            ->get();

        return $characters->map(function ($character) {
            return [
                'id' => $character->id,
                'character_key' => $character->character_key,
                'name' => $character->name,
                'class' => $character->class,
                'ancestry' => $character->ancestry,
                'level' => $character->level,
                'created_at' => $character->created_at,
                'updated_at' => $character->updated_at,
                'is_public' => $character->is_public,
                'share_url' => $character->getShareUrl(),
                'banner' => $character->getBanner(),
                'profile_image' => $character->getProfileImage(),
            ];
        })->toArray();
    }

    public function loadPublicCharacters(int $limit = 20): array
    {
        $characters = Character::public()
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return $characters->map(function ($character) {
            return [
                'id' => $character->id,
                'character_key' => $character->character_key,
                'name' => $character->name,
                'class' => $character->class,
                'ancestry' => $character->ancestry,
                'level' => $character->level,
                'created_at' => $character->created_at,
                'creator' => $character->user ? $character->user->username : 'Anonymous',
                'share_url' => $character->getShareUrl(),
                'banner' => $character->getBanner(),
                'profile_image' => $character->getProfileImage(),
            ];
        })->toArray();
    }
}
