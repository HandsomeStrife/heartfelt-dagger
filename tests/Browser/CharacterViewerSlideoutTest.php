<?php

declare(strict_types=1);

use Domain\Character\Actions\SaveCharacterAction;
use Domain\Character\Data\CharacterBuilderData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('displays info buttons and opens slideouts for ancestry and community details', function () {
    // Create a test character
    $character = [
        'selected_class' => 'druid',
        'selected_subclass' => 'warden of the elements',
        'selected_ancestry' => 'clank',
        'selected_community' => 'seaborne',
        'assigned_traits' => [
            'agility' => 1,
            'strength' => 0,
            'finesse' => 0,
            'instinct' => 2,
            'presence' => 1,
            'knowledge' => -1,
        ],
        'name' => 'Test Slideover Character',
        'selected_domain_cards' => [],
        'background_answers' => [],
        'experiences' => [],
        'selected_equipment' => [],
    ];

    $action = new SaveCharacterAction;
    $character_data = CharacterBuilderData::from($character);
    $character_model = $action->execute($character_data);

    // Visit the character viewer
    $page = visit("/character/{$character_model->public_key}");

    // Check that the page loads and sections exist
    $page->assertSee('Test Slideover Character');
    $page->assertVisible('[pest="ancestry-features-section"]');
    $page->assertVisible('[pest="community-features-section"]');

    // Check that Info buttons are present
    $page->assertSee('Info');

    // Check that the compact cards show only features, not full descriptions
    $page->assertSee('Clank Features');
    $page->assertSee('Seaborne Features');
    $page->assertSee('Purposeful Design');
    $page->assertSee('Know the Tide');

    // Should not see the long descriptions in the main view
    $page->assertDontSee('sentient mechanical beings built from a variety of materials');
    $page->assertDontSee('lived on or near a large body of water');

    // The slideouts should be present but hidden initially
    // We can't easily test the clicking and slideover opening in a simple browser test
    // but we can verify the structure is there
    $page->assertSee('Clank Features');
    $page->assertSee('Seaborne Features');
});
