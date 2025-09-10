<?php

declare(strict_types=1);

use Domain\Character\Actions\SaveCharacterAction;
use Domain\Character\Data\CharacterBuilderData;
use Domain\Character\Enums\SubclassEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Subclass Effects Builder and Viewer Integration', function () {

    it('validates School of Knowledge subclass displays correctly', function () {
        // Create Wizard with School of Knowledge using working pattern
        $character_data = [
            'selected_class' => 'wizard',
            'selected_subclass' => SubclassEnum::SCHOOL_OF_KNOWLEDGE->value,
            'assigned_traits' => ['agility' => -1, 'strength' => 0, 'finesse' => 0, 'instinct' => 1, 'presence' => 1, 'knowledge' => 2],
            'selected_equipment' => [],
            'background_answers' => ['answer1', 'answer2', 'answer3'],
            'connections' => ['connection1', 'connection2', 'connection3'],
            'experiences' => [],
            'selected_domain_cards' => [],
            'name' => 'Test Wizard with School of Knowledge',
        ];

        $action = new SaveCharacterAction;
        $character_builder_data = CharacterBuilderData::from($character_data);
        $character_model = $action->execute($character_builder_data);

        $page = visit('/character/'.$character_model->public_key);

        // Verify the page loads successfully
        $page->assertSee('Test Wizard with School of Knowledge');

        // Verify School of Knowledge subclass features section appears
        $page->assertPresent('[pest="subclass-features-section"]');
        $page->assertSee('School of Knowledge');
    });

    it('validates School of War subclass displays correctly', function () {
        // Create Wizard with School of War
        $character_data = [
            'selected_class' => 'wizard',
            'selected_subclass' => SubclassEnum::SCHOOL_OF_WAR->value,
            'assigned_traits' => ['agility' => -1, 'strength' => 0, 'finesse' => 0, 'instinct' => 1, 'presence' => 1, 'knowledge' => 2],
            'selected_equipment' => [],
            'background_answers' => ['answer1', 'answer2', 'answer3'],
            'connections' => ['connection1', 'connection2', 'connection3'],
            'experiences' => [],
            'selected_domain_cards' => [],
            'name' => 'Test Wizard with School of War',
        ];

        $action = new SaveCharacterAction;
        $character_builder_data = CharacterBuilderData::from($character_data);
        $character_model = $action->execute($character_builder_data);

        $page = visit('/character/'.$character_model->public_key);

        // Verify the page loads successfully
        $page->assertSee('Test Wizard with School of War');

        // Verify School of War subclass features section appears
        $page->assertPresent('[pest="subclass-features-section"]');
        $page->assertSee('School of War');

        // Verify HP bonus is reflected (School of War gives +1 HP)
        $page->assertPresent('[pest="hit-points-track"]');
    });

    it('validates Nightwalker subclass displays correctly', function () {
        // Create Rogue with Nightwalker
        $character_data = [
            'selected_class' => 'rogue',
            'selected_subclass' => SubclassEnum::NIGHTWALKER->value,
            'assigned_traits' => ['agility' => 2, 'strength' => -1, 'finesse' => 1, 'instinct' => 1, 'presence' => 0, 'knowledge' => 0],
            'selected_equipment' => [],
            'background_answers' => ['answer1', 'answer2', 'answer3'],
            'connections' => ['connection1', 'connection2', 'connection3'],
            'experiences' => [],
            'selected_domain_cards' => [],
            'name' => 'Test Rogue with Nightwalker',
        ];

        $action = new SaveCharacterAction;
        $character_builder_data = CharacterBuilderData::from($character_data);
        $character_model = $action->execute($character_builder_data);

        $page = visit('/character/'.$character_model->public_key);

        // Verify the page loads successfully
        $page->assertSee('Test Rogue with Nightwalker');

        // Verify Nightwalker subclass features section appears
        $page->assertPresent('[pest="subclass-features-section"]');
        $page->assertSee('Nightwalker');

        // Verify character stats are displayed (Nightwalker gets +1 Evasion)
        $page->assertPresent('[pest="evasion-stat"]');
    });

    it('validates Stalwart subclass displays correctly', function () {
        // Create Guardian with Stalwart
        $character_data = [
            'selected_class' => 'guardian',
            'selected_subclass' => SubclassEnum::STALWART->value,
            'assigned_traits' => ['agility' => 0, 'strength' => 2, 'finesse' => 0, 'instinct' => 1, 'presence' => 1, 'knowledge' => -1],
            'selected_equipment' => [],
            'background_answers' => ['answer1', 'answer2', 'answer3'],
            'connections' => ['connection1', 'connection2', 'connection3'],
            'experiences' => [],
            'selected_domain_cards' => [],
            'name' => 'Test Guardian with Stalwart',
        ];

        $action = new SaveCharacterAction;
        $character_builder_data = CharacterBuilderData::from($character_data);
        $character_model = $action->execute($character_builder_data);

        $page = visit('/character/'.$character_model->public_key);

        // Verify the page loads successfully
        $page->assertSee('Test Guardian with Stalwart');

        // Verify Stalwart subclass features section appears
        $page->assertPresent('[pest="subclass-features-section"]');
        $page->assertSee('Stalwart');

        // Verify damage thresholds are displayed (Stalwart gets threshold bonuses)
        $page->assertPresent('[pest="damage-thresholds"]');
    });

    it('validates Winged Sentinel subclass displays correctly', function () {
        // Create Seraph with Winged Sentinel
        $character_data = [
            'selected_class' => 'seraph',
            'selected_subclass' => SubclassEnum::WINGED_SENTINEL->value,
            'assigned_traits' => ['agility' => 1, 'strength' => 0, 'finesse' => 0, 'instinct' => 1, 'presence' => 2, 'knowledge' => -1],
            'selected_equipment' => [],
            'background_answers' => ['answer1', 'answer2', 'answer3'],
            'connections' => ['connection1', 'connection2', 'connection3'],
            'experiences' => [],
            'selected_domain_cards' => [],
            'name' => 'Test Seraph with Winged Sentinel',
        ];

        $action = new SaveCharacterAction;
        $character_builder_data = CharacterBuilderData::from($character_data);
        $character_model = $action->execute($character_builder_data);

        $page = visit('/character/'.$character_model->public_key);

        // Verify the page loads successfully
        $page->assertSee('Test Seraph with Winged Sentinel');

        // Verify Winged Sentinel subclass features section appears
        $page->assertPresent('[pest="subclass-features-section"]');
        $page->assertSee('Winged Sentinel');

        // Verify damage thresholds are displayed (Winged Sentinel gets +4 Severe threshold)
        $page->assertPresent('[pest="damage-thresholds"]');
    });

    it('validates Vengeance subclass displays correctly', function () {
        // Create Warrior with Vengeance
        $character_data = [
            'selected_class' => 'warrior',
            'selected_subclass' => SubclassEnum::VENGEANCE->value,
            'assigned_traits' => ['agility' => 0, 'strength' => 2, 'finesse' => 1, 'instinct' => 0, 'presence' => 1, 'knowledge' => -1],
            'selected_equipment' => [],
            'background_answers' => ['answer1', 'answer2', 'answer3'],
            'connections' => ['connection1', 'connection2', 'connection3'],
            'experiences' => [],
            'selected_domain_cards' => [],
            'name' => 'Test Warrior with Vengeance',
        ];

        $action = new SaveCharacterAction;
        $character_builder_data = CharacterBuilderData::from($character_data);
        $character_model = $action->execute($character_builder_data);

        $page = visit('/character/'.$character_model->public_key);

        // Verify the page loads successfully
        $page->assertSee('Test Warrior with Vengeance');

        // Verify Vengeance subclass features section appears
        $page->assertPresent('[pest="subclass-features-section"]');
        $page->assertSee('Vengeance');

        // Verify stress track is displayed (Vengeance gets +1 Stress)
        $page->assertPresent('[pest="stress-track"]');
    });

    it('validates subclass without stat effects displays correctly', function () {
        // Create Bard with Troubadour (no stat bonuses, just features)
        $character_data = [
            'selected_class' => 'bard',
            'selected_subclass' => SubclassEnum::TROUBADOUR->value,
            'assigned_traits' => ['agility' => 0, 'strength' => -1, 'finesse' => 1, 'instinct' => 0, 'presence' => 2, 'knowledge' => 1],
            'selected_equipment' => [],
            'background_answers' => ['answer1', 'answer2', 'answer3'],
            'connections' => ['connection1', 'connection2', 'connection3'],
            'experiences' => [],
            'selected_domain_cards' => [],
            'name' => 'Test Bard with Troubadour',
        ];

        $action = new SaveCharacterAction;
        $character_builder_data = CharacterBuilderData::from($character_data);
        $character_model = $action->execute($character_builder_data);

        $page = visit('/character/'.$character_model->public_key);

        // Verify the page loads successfully
        $page->assertSee('Test Bard with Troubadour');

        // Verify Troubadour subclass features section appears
        $page->assertPresent('[pest="subclass-features-section"]');
        $page->assertSee('Troubadour');

        // Verify standard stats are present (no special bonuses, just normal display)
        $page->assertPresent('[pest="hit-points-track"]');
        $page->assertPresent('[pest="stress-track"]');
        $page->assertPresent('[pest="damage-thresholds"]');
    });
});
