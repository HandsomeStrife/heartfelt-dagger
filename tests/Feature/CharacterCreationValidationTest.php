<?php

declare(strict_types=1);

use Domain\Character\Actions\SaveCharacterAction;
use Domain\Character\Data\CharacterBuilderData;
use Domain\User\Models\User;

describe('Character Creation Validation', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->action = new SaveCharacterAction();
    });

    test('blocks character creation with missing tier achievement experience at level 2', function () {
        $builderData = CharacterBuilderData::from([
            'name' => 'Test Character',
            'selected_class' => 'guardian',
            'selected_ancestry' => 'human',
            'selected_community' => 'city',
            'starting_level' => 2,
            'abilities' => [
                'might' => 1, 'dexterity' => 1, 'insight' => 1,
                'instinct' => 1, 'presence' => 0, 'knowledge' => 1,
            ],
            'assigned_traits' => ['brave' => 1, 'loyal' => 1, 'strong' => 1],
            'selected_equipment' => [
                ['type' => 'weapon', 'key' => 'longsword', 'tier' => 1],
                ['type' => 'armor', 'key' => 'leather_armor', 'tier' => 1],
            ],
            'experiences' => [
                ['name' => 'Street Fighting', 'description' => 'test'],
                ['name' => 'Blacksmith', 'description' => 'test'],
            ],
            'creation_domain_cards' => [
                1 => 'get back up',
                2 => 'not good enough',
            ],
            'background_answers' => ['a1', 'a2', 'a3'],
            'connection_answers' => ['c1', 'c2'],
            'physical_description' => 'Tall',
            'personality_traits' => 'Brave',
            'personal_history' => 'Soldier',
            'motivations' => 'Protect',
            'clank_bonus_experience' => null,
            'manual_step_completions' => [],
            'profile_image_path' => null,
            'creation_advancements' => [
                2 => [
                    ['type' => 'hit_point', 'index' => 0],
                    ['type' => 'evasion', 'index' => 1],
                ],
            ],
            // Missing tier achievement experience for level 2!
            'creation_tier_experiences' => [],
        ]);

        expect(fn () => $this->action->createCharacterWithAdvancements($builderData, $this->user))
            ->toThrow(\Exception::class); // Validation should throw exception
    });

    test('blocks character creation with incorrect advancement count', function () {
        $builderData = CharacterBuilderData::from([
            'name' => 'Test Character',
            'selected_class' => 'guardian',
            'selected_ancestry' => 'human',
            'selected_community' => 'city',
            'starting_level' => 2,
            'abilities' => [
                'might' => 1, 'dexterity' => 1, 'insight' => 1,
                'instinct' => 1, 'presence' => 0, 'knowledge' => 1,
            ],
            'assigned_traits' => ['brave' => 1, 'loyal' => 1, 'strong' => 1],
            'selected_equipment' => [
                ['type' => 'weapon', 'key' => 'longsword', 'tier' => 1],
            ],
            'experiences' => [
                ['name' => 'Street Fighting', 'description' => 'test'],
                ['name' => 'Blacksmith', 'description' => 'test'],
            ],
            'creation_domain_cards' => [
                1 => 'get back up',
                2 => 'not good enough',
            ],
            'background_answers' => ['a1', 'a2', 'a3'],
            'connection_answers' => ['c1', 'c2'],
            'physical_description' => 'Tall',
            'personality_traits' => 'Brave',
            'personal_history' => 'Soldier',
            'motivations' => 'Protect',
            'clank_bonus_experience' => null,
            'manual_step_completions' => [],
            'profile_image_path' => null,
            'creation_tier_experiences' => [
                2 => ['name' => 'Combat Veteran', 'description' => 'Survived battles'],
            ],
            // Only 1 advancement instead of 2!
            'creation_advancements' => [
                2 => [
                    ['type' => 'hit_point', 'index' => 0],
                ],
            ],
        ]);

        expect(fn () => $this->action->createCharacterWithAdvancements($builderData, $this->user))
            ->toThrow(\Exception::class); // Validation should throw exception
    });

    test('blocks character creation with missing domain card', function () {
        $builderData = CharacterBuilderData::from([
            'name' => 'Test Character',
            'selected_class' => 'guardian',
            'selected_ancestry' => 'human',
            'selected_community' => 'city',
            'starting_level' => 2,
            'abilities' => [
                'might' => 1, 'dexterity' => 1, 'insight' => 1,
                'instinct' => 1, 'presence' => 0, 'knowledge' => 1,
            ],
            'assigned_traits' => ['brave' => 1, 'loyal' => 1, 'strong' => 1],
            'selected_equipment' => [
                ['type' => 'weapon', 'key' => 'longsword', 'tier' => 1],
            ],
            'experiences' => [
                ['name' => 'Street Fighting', 'description' => 'test'],
                ['name' => 'Blacksmith', 'description' => 'test'],
            ],
            // Missing domain card for level 2!
            'creation_domain_cards' => [
                1 => 'get back up',
            ],
            'background_answers' => ['a1', 'a2', 'a3'],
            'connection_answers' => ['c1', 'c2'],
            'physical_description' => 'Tall',
            'personality_traits' => 'Brave',
            'personal_history' => 'Soldier',
            'motivations' => 'Protect',
            'clank_bonus_experience' => null,
            'manual_step_completions' => [],
            'profile_image_path' => null,
            'creation_tier_experiences' => [
                2 => ['name' => 'Combat Veteran', 'description' => 'Survived battles'],
            ],
            'creation_advancements' => [
                2 => [
                    ['type' => 'hit_point', 'index' => 0],
                    ['type' => 'evasion', 'index' => 1],
                ],
            ],
        ]);

        expect(fn () => $this->action->createCharacterWithAdvancements($builderData, $this->user))
            ->toThrow(\Exception::class); // Validation should throw exception
    });

    test('blocks character creation with duplicate domain cards', function () {
        $builderData = CharacterBuilderData::from([
            'name' => 'Test Character',
            'selected_class' => 'guardian',
            'selected_ancestry' => 'human',
            'selected_community' => 'city',
            'starting_level' => 3,
            'abilities' => [
                'might' => 1, 'dexterity' => 1, 'insight' => 1,
                'instinct' => 1, 'presence' => 0, 'knowledge' => 1,
            ],
            'assigned_traits' => ['brave' => 1, 'loyal' => 1, 'strong' => 1],
            'selected_equipment' => [
                ['type' => 'weapon', 'key' => 'longsword', 'tier' => 1],
            ],
            'experiences' => [
                ['name' => 'Street Fighting', 'description' => 'test'],
                ['name' => 'Blacksmith', 'description' => 'test'],
            ],
            // Duplicate domain card!
            'creation_domain_cards' => [
                1 => 'get back up',
                2 => 'get back up', // Same card twice
                3 => 'not good enough',
            ],
            'background_answers' => ['a1', 'a2', 'a3'],
            'connection_answers' => ['c1', 'c2'],
            'physical_description' => 'Tall',
            'personality_traits' => 'Brave',
            'personal_history' => 'Soldier',
            'motivations' => 'Protect',
            'clank_bonus_experience' => null,
            'manual_step_completions' => [],
            'profile_image_path' => null,
            'creation_tier_experiences' => [
                2 => ['name' => 'Combat Veteran', 'description' => 'Survived battles'],
            ],
            'creation_advancements' => [
                2 => [
                    ['type' => 'hit_point', 'index' => 0],
                    ['type' => 'evasion', 'index' => 1],
                ],
                3 => [
                    ['type' => 'stress', 'index' => 0],
                    ['type' => 'experience_bonus', 'index' => 1],
                ],
            ],
        ]);

        expect(fn () => $this->action->createCharacterWithAdvancements($builderData, $this->user))
            ->toThrow(\Exception::class); // Validation should throw exception
    });

    test('blocks character creation with invalid advancement type', function () {
        $builderData = CharacterBuilderData::from([
            'name' => 'Test Character',
            'selected_class' => 'guardian',
            'selected_ancestry' => 'human',
            'selected_community' => 'city',
            'starting_level' => 2,
            'abilities' => [
                'might' => 1, 'dexterity' => 1, 'insight' => 1,
                'instinct' => 1, 'presence' => 0, 'knowledge' => 1,
            ],
            'assigned_traits' => ['brave' => 1, 'loyal' => 1, 'strong' => 1],
            'selected_equipment' => [
                ['type' => 'weapon', 'key' => 'longsword', 'tier' => 1],
            ],
            'experiences' => [
                ['name' => 'Street Fighting', 'description' => 'test'],
                ['name' => 'Blacksmith', 'description' => 'test'],
            ],
            'creation_domain_cards' => [
                1 => 'get back up',
                2 => 'not good enough',
            ],
            'background_answers' => ['a1', 'a2', 'a3'],
            'connection_answers' => ['c1', 'c2'],
            'physical_description' => 'Tall',
            'personality_traits' => 'Brave',
            'personal_history' => 'Soldier',
            'motivations' => 'Protect',
            'clank_bonus_experience' => null,
            'manual_step_completions' => [],
            'profile_image_path' => null,
            'creation_tier_experiences' => [
                2 => ['name' => 'Combat Veteran', 'description' => 'Survived battles'],
            ],
            // Invalid advancement type!
            'creation_advancements' => [
                2 => [
                    ['type' => 'invalid_type', 'index' => 0], // Bad type
                    ['type' => 'evasion', 'index' => 1],
                ],
            ],
        ]);

        expect(fn () => $this->action->createCharacterWithAdvancements($builderData, $this->user))
            ->toThrow(\Exception::class); // Validation should throw exception
    });

    test('blocks character creation with experience name too long', function () {
        $builderData = CharacterBuilderData::from([
            'name' => 'Test Character',
            'selected_class' => 'guardian',
            'selected_ancestry' => 'human',
            'selected_community' => 'city',
            'starting_level' => 2,
            'abilities' => [
                'might' => 1, 'dexterity' => 1, 'insight' => 1,
                'instinct' => 1, 'presence' => 0, 'knowledge' => 1,
            ],
            'assigned_traits' => ['brave' => 1, 'loyal' => 1, 'strong' => 1],
            'selected_equipment' => [
                ['type' => 'weapon', 'key' => 'longsword', 'tier' => 1],
            ],
            'experiences' => [
                ['name' => 'Street Fighting', 'description' => 'test'],
                ['name' => 'Blacksmith', 'description' => 'test'],
            ],
            'creation_domain_cards' => [
                1 => 'get back up',
                2 => 'not good enough',
            ],
            'background_answers' => ['a1', 'a2', 'a3'],
            'connection_answers' => ['c1', 'c2'],
            'physical_description' => 'Tall',
            'personality_traits' => 'Brave',
            'personal_history' => 'Soldier',
            'motivations' => 'Protect',
            'clank_bonus_experience' => null,
            'manual_step_completions' => [],
            'profile_image_path' => null,
            // Experience name over 255 characters!
            'creation_tier_experiences' => [
                2 => [
                    'name' => str_repeat('A', 300), // Way too long
                    'description' => 'Survived battles',
                ],
            ],
            'creation_advancements' => [
                2 => [
                    ['type' => 'hit_point', 'index' => 0],
                    ['type' => 'evasion', 'index' => 1],
                ],
            ],
        ]);

        expect(fn () => $this->action->createCharacterWithAdvancements($builderData, $this->user))
            ->toThrow(\Exception::class); // Validation should throw exception
    });

    test('blocks character creation with empty experience name', function () {
        $builderData = CharacterBuilderData::from([
            'name' => 'Test Character',
            'selected_class' => 'guardian',
            'selected_ancestry' => 'human',
            'selected_community' => 'city',
            'starting_level' => 2,
            'abilities' => [
                'might' => 1, 'dexterity' => 1, 'insight' => 1,
                'instinct' => 1, 'presence' => 0, 'knowledge' => 1,
            ],
            'assigned_traits' => ['brave' => 1, 'loyal' => 1, 'strong' => 1],
            'selected_equipment' => [
                ['type' => 'weapon', 'key' => 'longsword', 'tier' => 1],
            ],
            'experiences' => [
                ['name' => 'Street Fighting', 'description' => 'test'],
                ['name' => 'Blacksmith', 'description' => 'test'],
            ],
            'creation_domain_cards' => [
                1 => 'get back up',
                2 => 'not good enough',
            ],
            'background_answers' => ['a1', 'a2', 'a3'],
            'connection_answers' => ['c1', 'c2'],
            'physical_description' => 'Tall',
            'personality_traits' => 'Brave',
            'personal_history' => 'Soldier',
            'motivations' => 'Protect',
            'clank_bonus_experience' => null,
            'manual_step_completions' => [],
            'profile_image_path' => null,
            // Empty experience name!
            'creation_tier_experiences' => [
                2 => [
                    'name' => '', // Empty!
                    'description' => 'Survived battles',
                ],
            ],
            'creation_advancements' => [
                2 => [
                    ['type' => 'hit_point', 'index' => 0],
                    ['type' => 'evasion', 'index' => 1],
                ],
            ],
        ]);

        expect(fn () => $this->action->createCharacterWithAdvancements($builderData, $this->user))
            ->toThrow(\Exception::class); // Validation should throw exception
    });

    test('blocks character creation with invalid starting level', function () {
        $builderData = CharacterBuilderData::from([
            'name' => 'Test Character',
            'selected_class' => 'guardian',
            'selected_ancestry' => 'human',
            'selected_community' => 'city',
            'starting_level' => 15, // Invalid! Max is 10
            'abilities' => [
                'might' => 1, 'dexterity' => 1, 'insight' => 1,
                'instinct' => 1, 'presence' => 0, 'knowledge' => 1,
            ],
            'assigned_traits' => ['brave' => 1, 'loyal' => 1, 'strong' => 1],
            'selected_equipment' => [
                ['type' => 'weapon', 'key' => 'longsword', 'tier' => 1],
            ],
            'experiences' => [
                ['name' => 'Street Fighting', 'description' => 'test'],
                ['name' => 'Blacksmith', 'description' => 'test'],
            ],
            'creation_domain_cards' => [
                1 => 'get back up',
            ],
            'background_answers' => ['a1', 'a2', 'a3'],
            'connection_answers' => ['c1', 'c2'],
            'physical_description' => 'Tall',
            'personality_traits' => 'Brave',
            'personal_history' => 'Soldier',
            'motivations' => 'Protect',
            'clank_bonus_experience' => null,
            'manual_step_completions' => [],
            'profile_image_path' => null,
            'creation_tier_experiences' => [],
            'creation_advancements' => [],
        ]);

        expect(fn () => $this->action->createCharacterWithAdvancements($builderData, $this->user))
            ->toThrow(\Exception::class); // Validation should throw exception
    });

    test('blocks character creation with trait bonus advancement missing trait selection', function () {
        $builderData = CharacterBuilderData::from([
            'name' => 'Test Character',
            'selected_class' => 'guardian',
            'selected_ancestry' => 'human',
            'selected_community' => 'city',
            'starting_level' => 2,
            'abilities' => [
                'might' => 1, 'dexterity' => 1, 'insight' => 1,
                'instinct' => 1, 'presence' => 0, 'knowledge' => 1,
            ],
            'assigned_traits' => ['brave' => 1, 'loyal' => 1, 'strong' => 1],
            'selected_equipment' => [
                ['type' => 'weapon', 'key' => 'longsword', 'tier' => 1],
            ],
            'experiences' => [
                ['name' => 'Street Fighting', 'description' => 'test'],
                ['name' => 'Blacksmith', 'description' => 'test'],
            ],
            'creation_domain_cards' => [
                1 => 'get back up',
                2 => 'not good enough',
            ],
            'background_answers' => ['a1', 'a2', 'a3'],
            'connection_answers' => ['c1', 'c2'],
            'physical_description' => 'Tall',
            'personality_traits' => 'Brave',
            'personal_history' => 'Soldier',
            'motivations' => 'Protect',
            'clank_bonus_experience' => null,
            'manual_step_completions' => [],
            'profile_image_path' => null,
            'creation_tier_experiences' => [
                2 => ['name' => 'Combat Veteran', 'description' => 'Survived battles'],
            ],
            // Trait bonus without trait selection!
            'creation_advancements' => [
                2 => [
                    [
                        'type' => 'trait_bonus',
                        'index' => 0,
                        'selection' => ['traits' => []], // Empty traits!
                    ],
                    ['type' => 'evasion', 'index' => 1],
                ],
            ],
        ]);

        expect(fn () => $this->action->createCharacterWithAdvancements($builderData, $this->user))
            ->toThrow(\Exception::class); // Validation should throw exception
    });
});

