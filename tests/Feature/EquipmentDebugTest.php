<?php

declare(strict_types=1);

use Domain\Character\Data\CharacterBuilderData;
use Domain\Character\Enums\CharacterBuilderStep;

it('debugs bard equipment requirements', function (): void {
    // Load the actual bard data to see what it expects
    $classesPath = base_path('resources/json/classes.json');
    $classes = json_decode((string) file_get_contents($classesPath), true) ?? [];
    $bardData = $classes['bard'];

    expect($bardData['startingInventory']['chooseOne'])->toBe(['Minor Health Potion', 'Minor Stamina Potion']);
    expect($bardData['startingInventory']['chooseExtra'])->toBe(['romance novel', 'letter never opened']);

    // Create character with exact items from the data
    $character = new CharacterBuilderData(selected_class: 'bard');
    $character->selected_equipment = [
        [
            'key' => 'rapier',
            'name' => 'Rapier',
            'type' => 'weapon',
            'data' => ['type' => 'Primary'],
        ],
        [
            'key' => 'light_armor',
            'name' => 'Light Armor',
            'type' => 'armor',
        ],
        [
            'key' => 'minor health potion', // From chooseOne (lowercased)
            'name' => 'Minor Health Potion',
            'type' => 'consumable',
        ],
        [
            'key' => 'romance novel', // From chooseExtra
            'name' => 'Romance Novel',
            'type' => 'item',
        ],
    ];

    expect($character->isStepComplete(CharacterBuilderStep::EQUIPMENT))->toBeTrue();
});

it('debugs warrior secondary weapon test', function (): void {
    // Check if Warrior requirements are met correctly
    $classesPath = base_path('resources/json/classes.json');
    $classes = json_decode((string) file_get_contents($classesPath), true) ?? [];
    $warriorData = $classes['warrior'];

    // Warrior has both chooseOne and chooseExtra, so secondary weapon test needs ALL requirements
    $character = new CharacterBuilderData(selected_class: 'warrior');
    $character->selected_equipment = [
        [
            'key' => 'longsword',
            'name' => 'Longsword',
            'type' => 'weapon',
            'data' => ['type' => 'Primary'],
        ],
        [
            'key' => 'handaxe',
            'name' => 'Handaxe',
            'type' => 'weapon',
            'data' => ['type' => 'Secondary'],
        ],
        [
            'key' => 'chainmail',
            'name' => 'Chainmail',
            'type' => 'armor',
        ],
        [
            'key' => 'minor health potion', // chooseOne item
            'name' => 'Minor Health Potion',
            'type' => 'consumable',
        ],
        [
            'key' => 'war trophy', // chooseExtra item
            'name' => 'War Trophy',
            'type' => 'item',
        ],
    ];

    expect($character->isStepComplete(CharacterBuilderStep::EQUIPMENT))->toBeTrue();
});
