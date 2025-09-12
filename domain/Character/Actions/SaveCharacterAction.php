<?php

declare(strict_types=1);

namespace Domain\Character\Actions;

use Domain\Character\Data\CharacterBuilderData;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterDomainCard;
use Domain\Character\Models\CharacterEquipment;
use Domain\Character\Models\CharacterExperience;
use Domain\Character\Models\CharacterTrait;
use Domain\User\Models\User;
use Illuminate\Support\Facades\DB;

class SaveCharacterAction
{
    public function execute(CharacterBuilderData $builder_data, ?User $user = null, ?string $pronouns = null): Character
    {
        return DB::transaction(function () use ($builder_data, $user, $pronouns) {
            // Create the main character record
            $character = Character::create([
                'character_key' => Character::generateUniqueKey(),
                'user_id' => $user?->id,
                'name' => $builder_data->name,
                'pronouns' => $pronouns,
                'class' => $builder_data->selected_class,
                'subclass' => $builder_data->selected_subclass,
                'ancestry' => $builder_data->selected_ancestry,
                'community' => $builder_data->selected_community,
                'level' => 1,
                'proficiency' => 1, // DaggerHeart SRD: characters start with proficiency 1
                'profile_image_path' => $builder_data->profile_image_path,
                'character_data' => [
                    'background' => [
                        'answers' => $builder_data->background_answers,
                        'physicalDescription' => $builder_data->physical_description,
                        'personalityTraits' => $builder_data->personality_traits,
                        'personalHistory' => $builder_data->personal_history,
                        'motivations' => $builder_data->motivations,
                    ],
                    'connections' => $builder_data->connection_answers,
                    'manualStepCompletions' => $builder_data->manual_step_completions,
                    'clank_bonus_experience' => $builder_data->clank_bonus_experience,
                    'creation_date' => now()->toISOString(),
                    'builder_version' => '1.0',
                ],
                'is_public' => false,
            ]);

            // Save character traits
            foreach ($builder_data->assigned_traits as $trait_name => $trait_value) {
                CharacterTrait::create([
                    'character_id' => $character->id,
                    'trait_name' => $trait_name,
                    'trait_value' => $trait_value,
                ]);
            }

            // Save character equipment
            foreach ($builder_data->selected_equipment as $equipment) {
                CharacterEquipment::create([
                    'character_id' => $character->id,
                    'equipment_type' => $equipment['type'],
                    'equipment_key' => $equipment['key'],
                    'equipment_data' => $equipment['data'],
                    'is_equipped' => true,
                ]);
            }

            // Save character domain cards
            foreach ($builder_data->selected_domain_cards as $card) {
                CharacterDomainCard::create([
                    'character_id' => $character->id,
                    'domain' => $card['domain'],
                    'ability_key' => $card['ability_key'],
                    'ability_level' => $card['ability_level'] ?? 1,
                ]);
            }

            // Save character experiences
            foreach ($builder_data->experiences as $experience) {
                CharacterExperience::create([
                    'character_id' => $character->id,
                    'experience_name' => $experience['name'],
                    'experience_description' => $experience['description'] ?? '',
                    'modifier' => $experience['modifier'] ?? 2,
                ]);
            }

            return $character;
        });
    }

    public function updateCharacter(Character $character, CharacterBuilderData $builder_data, ?string $pronouns = null): Character
    {
        return DB::transaction(function () use ($character, $builder_data, $pronouns) {
            // Update the main character record
            $character->update([
                'name' => $builder_data->name,
                'pronouns' => $pronouns,
                'class' => $builder_data->selected_class,
                'subclass' => $builder_data->selected_subclass,
                'ancestry' => $builder_data->selected_ancestry,
                'community' => $builder_data->selected_community,
                'profile_image_path' => $builder_data->profile_image_path,
                'character_data' => array_merge($character->character_data ?? [], [
                    'background' => [
                        'answers' => $builder_data->background_answers,
                        'physicalDescription' => $builder_data->physical_description,
                        'personalityTraits' => $builder_data->personality_traits,
                        'personalHistory' => $builder_data->personal_history,
                        'motivations' => $builder_data->motivations,
                    ],
                    'connections' => $builder_data->connection_answers,
                    'manualStepCompletions' => $builder_data->manual_step_completions,
                    'clank_bonus_experience' => $builder_data->clank_bonus_experience,
                    'last_updated' => now()->toISOString(),
                ]),
            ]);

            // Clear and recreate traits
            $character->traits()->delete();
            foreach ($builder_data->assigned_traits as $trait_name => $trait_value) {
                CharacterTrait::create([
                    'character_id' => $character->id,
                    'trait_name' => $trait_name,
                    'trait_value' => $trait_value,
                ]);
            }

            // Clear and recreate equipment
            $character->equipment()->delete();

            // Debug: Log equipment data being saved
            logger('=== SaveCharacterAction Equipment Debug ===');
            logger('Builder data selected_equipment count: '.count($builder_data->selected_equipment));
            foreach ($builder_data->selected_equipment as $equipment) {
                logger("  - Saving equipment: {$equipment['type']} - {$equipment['key']}");
                CharacterEquipment::create([
                    'character_id' => $character->id,
                    'equipment_type' => $equipment['type'],
                    'equipment_key' => $equipment['key'],
                    'equipment_data' => $equipment['data'],
                    'is_equipped' => true,
                ]);
            }

            // Clear and recreate domain cards
            $character->domainCards()->delete();
            foreach ($builder_data->selected_domain_cards as $card) {
                CharacterDomainCard::create([
                    'character_id' => $character->id,
                    'domain' => $card['domain'],
                    'ability_key' => $card['ability_key'],
                    'ability_level' => $card['ability_level'] ?? 1,
                ]);
            }

            // Clear and recreate experiences
            $character->experiences()->delete();
            foreach ($builder_data->experiences as $experience) {
                CharacterExperience::create([
                    'character_id' => $character->id,
                    'experience_name' => $experience['name'],
                    'experience_description' => $experience['description'] ?? '',
                    'modifier' => $experience['modifier'] ?? 2,
                ]);
            }

            return $character->fresh();
        });
    }
}
