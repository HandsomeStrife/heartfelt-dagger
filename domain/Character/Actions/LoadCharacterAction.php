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
        );
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
