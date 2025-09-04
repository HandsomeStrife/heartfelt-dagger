<?php

declare(strict_types=1);

use Domain\Character\Actions\SaveCharacterAction;
use Domain\Character\Data\CharacterBuilderData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('displays subclass features and domain cards correctly in character viewer', function () {
    // Create a test character with subclass and domain cards
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
        'name' => 'Test Druid',
        'selected_domain_cards' => [
            ['domain' => 'arcana', 'ability_key' => 'unleash-chaos', 'ability_level' => 1],
            ['domain' => 'sage', 'ability_key' => 'vicious-entangle', 'ability_level' => 1],
        ],
        'background_answers' => [],
        'experiences' => [],
        'selected_equipment' => [],
    ];

    $action = new SaveCharacterAction();
    $character_data = CharacterBuilderData::from($character);
    $character_model = $action->execute($character_data);

    // Visit the character viewer
    $page = visit("/character/{$character_model->public_key}");

    // Check that the page loads
    $page->assertSee('Test Druid');

    // Check that subclass features section exists and shows content
    $page->assertVisible('[pest="subclass-features-section"]');
    $page->assertSee('Warden of the Elements Features');
    $page->assertSee('Foundation Features');
    $page->assertSee('Elemental Incarnation');
    $page->assertSee('Specialization Features');
    $page->assertSee('Elemental Aura');
    $page->assertSee('Mastery Features');
    $page->assertSee('Elemental Dominion');

    // Check that processed markdown is displayed (should show HTML, not raw markdown)
    $page->assertSee('Fire:');
    $page->assertSee('Earth:');
    $page->assertSee('Water:');
    $page->assertSee('Air:');
    $page->assertDontSee('**Fire:**'); // Should not see raw markdown

    // Check that domain cards section exists and shows content
    $page->assertVisible('[pest="domain-cards-section"]');
    $page->assertVisible('[pest="domain-card-0"]');
    $page->assertVisible('[pest="domain-card-1"]');

    // Check domain card content
    $page->assertSee('Unleash Chaos');
    $page->assertSee('Vicious Entangle');
    $page->assertSee('Arcana Domain');
    $page->assertSee('Sage Domain');
});
