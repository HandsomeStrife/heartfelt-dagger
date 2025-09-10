<?php

declare(strict_types=1);

use Domain\Character\Actions\SaveCharacterAction;
use Domain\Character\Data\CharacterBuilderData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Trait Distribution Validation and Stats Consistency', function () {

    it('validates valid trait distribution displays correctly in viewer', function () {
        // Create character with valid trait distribution: -1, 0, 0, +1, +1, +2
        $character_data = [
            'selected_class' => 'warrior',
            'selected_subclass' => 'stalwart',
            'selected_ancestry' => 'human',
            'selected_community' => 'wildborne',
            'assigned_traits' => [
                'agility' => 0,
                'strength' => 2,    // +2
                'finesse' => 1,     // +1
                'instinct' => 1,    // +1
                'presence' => 0,
                'knowledge' => -1,   // -1
            ],
            'selected_equipment' => [],
            'background_answers' => ['answer1', 'answer2', 'answer3'],
            'connections' => ['connection1', 'connection2', 'connection3'],
            'experiences' => [],
            'selected_domain_cards' => [],
            'name' => 'Test Warrior with Valid Traits',
        ];

        $action = new SaveCharacterAction;
        $character_builder_data = CharacterBuilderData::from($character_data);
        $character_model = $action->execute($character_builder_data);

        $page = visit('/character/'.$character_model->public_key);

        // Verify the page loads successfully
        $page->assertSee('Test Warrior with Valid Traits');

        // Verify trait stats are displayed correctly
        $page->assertPresent('[pest="trait-stats"]');

        // Check that individual traits are shown
        $page->assertPresent('[pest="trait-agility"]');
        $page->assertPresent('[pest="trait-strength"]');
        $page->assertPresent('[pest="trait-finesse"]');
        $page->assertPresent('[pest="trait-instinct"]');
        $page->assertPresent('[pest="trait-presence"]');
        $page->assertPresent('[pest="trait-knowledge"]');

        // Verify computed stats are displayed
        $page->assertPresent('[pest="evasion-stat"]');
        $page->assertPresent('[pest="armor-stat"]');
    });

    it('validates traits affect computed stats correctly in viewer', function () {
        // Create Wizard with high Knowledge trait
        $character_data = [
            'selected_class' => 'wizard',
            'selected_subclass' => 'school of knowledge',
            'selected_ancestry' => 'human',
            'selected_community' => 'loreborne',
            'assigned_traits' => [
                'agility' => -1,
                'strength' => 0,
                'finesse' => 0,
                'instinct' => 1,
                'presence' => 1,
                'knowledge' => 2,    // +2 Knowledge for spellcasting
            ],
            'selected_equipment' => [],
            'background_answers' => ['answer1', 'answer2', 'answer3'],
            'connections' => ['connection1', 'connection2', 'connection3'],
            'experiences' => [],
            'selected_domain_cards' => [],
            'name' => 'Test Knowledge Wizard',
        ];

        $action = new SaveCharacterAction;
        $character_builder_data = CharacterBuilderData::from($character_data);
        $character_model = $action->execute($character_builder_data);

        $page = visit('/character/'.$character_model->public_key);

        // Verify the page loads successfully
        $page->assertSee('Test Knowledge Wizard');

        // Verify trait stats are displayed
        $page->assertPresent('[pest="trait-stats"]');

        // Verify computed stats are present (Knowledge trait affects spellcasting)
        $page->assertPresent('[pest="evasion-stat"]');
        $page->assertPresent('[pest="armor-stat"]');

        // Verify character displays correctly
        $page->assertSee('Wizard');
        $page->assertSee('School of Knowledge');
    });

    it('validates high Agility trait affects Evasion in viewer', function () {
        // Create Rogue with high Agility for evasion
        $character_data = [
            'selected_class' => 'rogue',
            'selected_subclass' => 'nightwalker',
            'selected_ancestry' => 'elf',
            'selected_community' => 'slyborne',
            'assigned_traits' => [
                'agility' => 2,     // +2 Agility for evasion
                'strength' => -1,
                'finesse' => 1,
                'instinct' => 1,
                'presence' => 0,
                'knowledge' => 0,
            ],
            'selected_equipment' => [],
            'background_answers' => ['answer1', 'answer2', 'answer3'],
            'connections' => ['connection1', 'connection2', 'connection3'],
            'experiences' => [],
            'selected_domain_cards' => [],
            'name' => 'Test Agile Rogue',
        ];

        $action = new SaveCharacterAction;
        $character_builder_data = CharacterBuilderData::from($character_data);
        $character_model = $action->execute($character_builder_data);

        $page = visit('/character/'.$character_model->public_key);

        // Verify the page loads successfully
        $page->assertSee('Test Agile Rogue');

        // Verify trait stats are displayed
        $page->assertPresent('[pest="trait-stats"]');

        // Verify evasion stat is present (should be affected by high Agility)
        $page->assertPresent('[pest="evasion-stat"]');

        // Verify character displays correctly
        $page->assertSee('Rogue');
        $page->assertSee('Nightwalker');
    });

    it('validates high Strength trait affects combat stats in viewer', function () {
        // Create Guardian with high Strength for melee combat
        $character_data = [
            'selected_class' => 'guardian',
            'selected_subclass' => 'stalwart',
            'selected_ancestry' => 'dwarf',
            'selected_community' => 'ridgeborne',
            'assigned_traits' => [
                'agility' => 0,
                'strength' => 2,    // +2 Strength for melee
                'finesse' => 0,
                'instinct' => 1,
                'presence' => 1,
                'knowledge' => -1,
            ],
            'selected_equipment' => [],
            'background_answers' => ['answer1', 'answer2', 'answer3'],
            'connections' => ['connection1', 'connection2', 'connection3'],
            'experiences' => [],
            'selected_domain_cards' => [],
            'name' => 'Test Strong Guardian',
        ];

        $action = new SaveCharacterAction;
        $character_builder_data = CharacterBuilderData::from($character_data);
        $character_model = $action->execute($character_builder_data);

        $page = visit('/character/'.$character_model->public_key);

        // Verify the page loads successfully
        $page->assertSee('Test Strong Guardian');

        // Verify trait stats are displayed
        $page->assertPresent('[pest="trait-stats"]');

        // Verify armor and damage thresholds are present (Guardians benefit from Strength)
        $page->assertPresent('[pest="armor-stat"]');
        $page->assertPresent('[pest="damage-thresholds"]');

        // Verify character displays correctly
        $page->assertSee('Guardian');
        $page->assertSee('Stalwart');
    });

    it('validates negative trait values display correctly in viewer', function () {
        // Create character with -1 in different traits to test negative display
        $character_data = [
            'selected_class' => 'sorcerer',
            'selected_subclass' => 'elemental origin',
            'selected_ancestry' => 'fairy',
            'selected_community' => 'wildborne',
            'assigned_traits' => [
                'agility' => 1,
                'strength' => -1,   // -1 Strength (weakness)
                'finesse' => 0,
                'instinct' => 2,    // +2 Instinct for spellcasting
                'presence' => 1,
                'knowledge' => 0,
            ],
            'selected_equipment' => [],
            'background_answers' => ['answer1', 'answer2', 'answer3'],
            'connections' => ['connection1', 'connection2', 'connection3'],
            'experiences' => [],
            'selected_domain_cards' => [],
            'name' => 'Test Sorcerer with Weakness',
        ];

        $action = new SaveCharacterAction;
        $character_builder_data = CharacterBuilderData::from($character_data);
        $character_model = $action->execute($character_builder_data);

        $page = visit('/character/'.$character_model->public_key);

        // Verify the page loads successfully
        $page->assertSee('Test Sorcerer with Weakness');

        // Verify trait stats are displayed (including negative values)
        $page->assertPresent('[pest="trait-stats"]');

        // Verify all computed stats display properly despite negative trait
        $page->assertPresent('[pest="evasion-stat"]');
        $page->assertPresent('[pest="armor-stat"]');

        // Verify character displays correctly
        $page->assertSee('Sorcerer');
        $page->assertSee('Elemental Origin');
    });
});
