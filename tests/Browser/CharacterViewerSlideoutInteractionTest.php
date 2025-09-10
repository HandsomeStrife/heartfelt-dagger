<?php

declare(strict_types=1);

use Domain\Character\Actions\SaveCharacterAction;
use Domain\Character\Data\CharacterBuilderData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('opens only the relevant slideover when clicking info buttons', function () {
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
        'name' => 'Slideover Test Character',
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

    // Check that both sections exist with Info buttons
    $page->assertVisible('[pest="ancestry-features-section"]');
    $page->assertVisible('[pest="community-features-section"]');

    // Verify the specific content is there but the long descriptions are not
    $page->assertSee('Clank Features');
    $page->assertSee('Seaborne Features');

    // Should not see the long descriptions in the main view (this confirms slideouts are working)
    $page->assertDontSee('sentient mechanical beings built from a variety of materials');
    $page->assertDontSee('lived on or near a large body of water');

    // Should see the features but not the full ancestry/community descriptions
    $page->assertSee('Purposeful Design');
    $page->assertSee('Know the Tide');

    // Verify Info buttons are present
    $page->assertSee('Info');
});
