<?php

declare(strict_types=1);

use Domain\Character\Actions\SaveCharacterAction;
use Domain\Character\Data\CharacterBuilderData;
use Domain\Character\Enums\AncestryEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Ancestry Effects Builder and Viewer Integration', function () {

    it('validates Clank ancestry displays correctly with experience bonus', function () {
        // Create character with Clank ancestry
        $character_data = [
            'selected_class' => 'rogue',
            'selected_subclass' => 'nightwalker',
            'selected_ancestry' => AncestryEnum::CLANK->value,
            'selected_community' => 'slyborne',
            'assigned_traits' => ['agility' => 2, 'strength' => 0, 'finesse' => 1, 'instinct' => 1, 'presence' => 0, 'knowledge' => -1],
            'selected_equipment' => [],
            'background_answers' => ['answer1', 'answer2', 'answer3'],
            'connections' => ['connection1', 'connection2', 'connection3'],
            'experiences' => [],
            'selected_domain_cards' => [],
            'name' => 'Test Clank Rogue',
        ];

        $action = new SaveCharacterAction;
        $character_builder_data = CharacterBuilderData::from($character_data);
        $character_model = $action->execute($character_builder_data);

        $page = visit('/character/'.$character_model->public_key);

        // Verify the page loads successfully
        $page->assertSee('Test Clank Rogue');

        // Verify Clank ancestry features section appears
        $page->assertPresent('[pest="ancestry-features-section"]');
        $page->assertSee('Clank');
        $page->assertSee('Purposeful Design');
    });

    it('validates Galapa ancestry displays correctly with threshold bonuses', function () {
        // Create character with Galapa ancestry
        $character_data = [
            'selected_class' => 'guardian',
            'selected_subclass' => 'stalwart',
            'selected_ancestry' => AncestryEnum::GALAPA->value,
            'selected_community' => 'ridgeborne',
            'assigned_traits' => ['agility' => 0, 'strength' => 2, 'finesse' => 0, 'instinct' => 1, 'presence' => 1, 'knowledge' => -1],
            'selected_equipment' => [],
            'background_answers' => ['answer1', 'answer2', 'answer3'],
            'connections' => ['connection1', 'connection2', 'connection3'],
            'experiences' => [],
            'selected_domain_cards' => [],
            'name' => 'Test Galapa Guardian',
        ];

        $action = new SaveCharacterAction;
        $character_builder_data = CharacterBuilderData::from($character_data);
        $character_model = $action->execute($character_builder_data);

        $page = visit('/character/'.$character_model->public_key);

        // Verify the page loads successfully
        $page->assertSee('Test Galapa Guardian');

        // Verify Galapa ancestry features section appears
        $page->assertPresent('[pest="ancestry-features-section"]');
        $page->assertSee('Galapa');

        // Verify damage thresholds are displayed (Galapa gets +2 to both thresholds)
        $page->assertPresent('[pest="damage-thresholds"]');
    });

    it('validates Giant ancestry displays correctly with HP bonus', function () {
        // Create character with Giant ancestry
        $character_data = [
            'selected_class' => 'warrior',
            'selected_subclass' => 'vengeance',
            'selected_ancestry' => AncestryEnum::GIANT->value,
            'selected_community' => 'wildborne',
            'assigned_traits' => ['agility' => 0, 'strength' => 2, 'finesse' => 1, 'instinct' => 0, 'presence' => 1, 'knowledge' => -1],
            'selected_equipment' => [],
            'background_answers' => ['answer1', 'answer2', 'answer3'],
            'connections' => ['connection1', 'connection2', 'connection3'],
            'experiences' => [],
            'selected_domain_cards' => [],
            'name' => 'Test Giant Warrior',
        ];

        $action = new SaveCharacterAction;
        $character_builder_data = CharacterBuilderData::from($character_data);
        $character_model = $action->execute($character_builder_data);

        $page = visit('/character/'.$character_model->public_key);

        // Verify the page loads successfully
        $page->assertSee('Test Giant Warrior');

        // Verify Giant ancestry features section appears
        $page->assertPresent('[pest="ancestry-features-section"]');
        $page->assertSee('Giant');

        // Verify HP display is present (Giant gets +1 HP)
        $page->assertPresent('[pest="hit-points-track"]');
    });

    it('validates Human ancestry displays correctly with stress bonus', function () {
        // Create character with Human ancestry
        $character_data = [
            'selected_class' => 'bard',
            'selected_subclass' => 'troubadour',
            'selected_ancestry' => AncestryEnum::HUMAN->value,
            'selected_community' => 'loreborne',
            'assigned_traits' => ['agility' => 0, 'strength' => -1, 'finesse' => 1, 'instinct' => 0, 'presence' => 2, 'knowledge' => 1],
            'selected_equipment' => [],
            'background_answers' => ['answer1', 'answer2', 'answer3'],
            'connections' => ['connection1', 'connection2', 'connection3'],
            'experiences' => [],
            'selected_domain_cards' => [],
            'name' => 'Test Human Bard',
        ];

        $action = new SaveCharacterAction;
        $character_builder_data = CharacterBuilderData::from($character_data);
        $character_model = $action->execute($character_builder_data);

        $page = visit('/character/'.$character_model->public_key);

        // Verify the page loads successfully
        $page->assertSee('Test Human Bard');

        // Verify Human ancestry features section appears
        $page->assertPresent('[pest="ancestry-features-section"]');
        $page->assertSee('Human');

        // Verify stress display is present (Human gets +1 Stress)
        $page->assertPresent('[pest="stress-track"]');
    });

    it('validates Simiah ancestry displays correctly with evasion bonus', function () {
        // Create character with Simiah ancestry
        $character_data = [
            'selected_class' => 'ranger',
            'selected_subclass' => 'wayfinder',
            'selected_ancestry' => AncestryEnum::SIMIAH->value,
            'selected_community' => 'wildborne',
            'assigned_traits' => ['agility' => 2, 'strength' => 0, 'finesse' => 1, 'instinct' => 1, 'presence' => 0, 'knowledge' => -1],
            'selected_equipment' => [],
            'background_answers' => ['answer1', 'answer2', 'answer3'],
            'connections' => ['connection1', 'connection2', 'connection3'],
            'experiences' => [],
            'selected_domain_cards' => [],
            'name' => 'Test Simiah Ranger',
        ];

        $action = new SaveCharacterAction;
        $character_builder_data = CharacterBuilderData::from($character_data);
        $character_model = $action->execute($character_builder_data);

        $page = visit('/character/'.$character_model->public_key);

        // Verify the page loads successfully
        $page->assertSee('Test Simiah Ranger');

        // Verify Simiah ancestry features section appears
        $page->assertPresent('[pest="ancestry-features-section"]');
        $page->assertSee('Simiah');

        // Verify evasion display is present (Simiah gets +1 Evasion)
        $page->assertPresent('[pest="evasion-stat"]');
    });

    it('validates Earthkin playtest ancestry displays correctly with playtest labeling', function () {
        // Create character with Earthkin (playtest) ancestry
        $character_data = [
            'selected_class' => 'guardian',
            'selected_subclass' => 'stalwart',
            'selected_ancestry' => AncestryEnum::EARTHKIN->value,
            'selected_community' => 'ridgeborne',
            'assigned_traits' => ['agility' => 0, 'strength' => 2, 'finesse' => 0, 'instinct' => 1, 'presence' => 1, 'knowledge' => -1],
            'selected_equipment' => [],
            'background_answers' => ['answer1', 'answer2', 'answer3'],
            'connections' => ['connection1', 'connection2', 'connection3'],
            'experiences' => [],
            'selected_domain_cards' => [],
            'name' => 'Test Earthkin Guardian',
        ];

        $action = new SaveCharacterAction;
        $character_builder_data = CharacterBuilderData::from($character_data);
        $character_model = $action->execute($character_builder_data);

        $page = visit('/character/'.$character_model->public_key);

        // Verify the page loads successfully
        $page->assertSee('Test Earthkin Guardian');

        // Verify Earthkin ancestry features section appears
        $page->assertPresent('[pest="ancestry-features-section"]');
        $page->assertSee('Earthkin');

        // Verify playtest labeling appears (should show "Void - Playtest v1.3")
        $page->assertSee('Void - Playtest');

        // Verify armor and threshold bonuses are reflected
        $page->assertPresent('[pest="armor-stat"]');
        $page->assertPresent('[pest="damage-thresholds"]');
    });

    it('validates ancestry without numerical effects displays correctly', function () {
        // Create character with Dwarf ancestry (no numerical bonuses, just features)
        $character_data = [
            'selected_class' => 'druid',
            'selected_subclass' => 'warden of renewal',
            'selected_ancestry' => AncestryEnum::DWARF->value,
            'selected_community' => 'ridgeborne',
            'assigned_traits' => ['agility' => 0, 'strength' => 1, 'finesse' => 0, 'instinct' => 2, 'presence' => 1, 'knowledge' => -1],
            'selected_equipment' => [],
            'background_answers' => ['answer1', 'answer2', 'answer3'],
            'connections' => ['connection1', 'connection2', 'connection3'],
            'experiences' => [],
            'selected_domain_cards' => [],
            'name' => 'Test Dwarf Druid',
        ];

        $action = new SaveCharacterAction;
        $character_builder_data = CharacterBuilderData::from($character_data);
        $character_model = $action->execute($character_builder_data);

        $page = visit('/character/'.$character_model->public_key);

        // Verify the page loads successfully
        $page->assertSee('Test Dwarf Druid');

        // Verify Dwarf ancestry features section appears
        $page->assertPresent('[pest="ancestry-features-section"]');
        $page->assertSee('Dwarf');

        // Verify standard stats are present (no special bonuses, just normal display)
        $page->assertPresent('[pest="hit-points-track"]');
        $page->assertPresent('[pest="stress-track"]');
        $page->assertPresent('[pest="damage-thresholds"]');
    });
});
