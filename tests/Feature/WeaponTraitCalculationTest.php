<?php

declare(strict_types=1);

use Domain\Character\Enums\EquipmentType;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterEquipment;
use Domain\Character\Models\CharacterTrait;

test('weapon trait values display correctly in character viewer', function () {
    // Create a character with specific trait values
    $character = Character::factory()->create([
        'name' => 'Test Warrior',
        'class' => 'warrior',
        'ancestry' => 'human',
        'community' => 'wanderborne',
    ]);

    // Set trait values: strength = 2, agility = 1, others = 0, -1, 0, 1
    CharacterTrait::factory()->create([
        'character_id' => $character->id,
        'trait_name' => 'strength',
        'trait_value' => 2,
    ]);

    CharacterTrait::factory()->create([
        'character_id' => $character->id,
        'trait_name' => 'agility',
        'trait_value' => 1,
    ]);

    CharacterTrait::factory()->create([
        'character_id' => $character->id,
        'trait_name' => 'finesse',
        'trait_value' => 1,
    ]);

    CharacterTrait::factory()->create([
        'character_id' => $character->id,
        'trait_name' => 'instinct',
        'trait_value' => 0,
    ]);

    CharacterTrait::factory()->create([
        'character_id' => $character->id,
        'trait_name' => 'presence',
        'trait_value' => 0,
    ]);

    CharacterTrait::factory()->create([
        'character_id' => $character->id,
        'trait_name' => 'knowledge',
        'trait_value' => -1,
    ]);

    // Add a strength-based weapon
    CharacterEquipment::factory()->create([
        'character_id' => $character->id,
        'equipment_type' => EquipmentType::WEAPON->value,
        'equipment_key' => 'sword',
        'is_equipped' => true,
        'equipment_data' => [
            'name' => 'Sword',
            'type' => 'Primary',
            'trait' => 'strength',
            'range' => 'Melee',
            'damage' => [
                'dice' => 'd8',
                'modifier' => 0,
                'type' => 'Physical',
            ],
            'burden' => 'One-Handed',
            'tier' => 1,
        ],
    ]);

    // Visit the character viewer page
    $response = $this->get(route('character.show', [
        'public_key' => $character->public_key,
    ]));

    $response->assertSuccessful();

    // The strength trait should show as +2 for the weapon
    $response->assertSee('+2'); // This should appear in the weapon trait display
    $response->assertSee('Strength'); // The trait label should be shown
});

test('weapon trait values are correctly calculated in livewire component', function () {
    $character = Character::factory()->create();

    // Create traits
    CharacterTrait::factory()->create([
        'character_id' => $character->id,
        'trait_name' => 'strength',
        'trait_value' => 2,
    ]);

    CharacterTrait::factory()->create([
        'character_id' => $character->id,
        'trait_name' => 'finesse',
        'trait_value' => 1,
    ]);

    // Create the livewire component
    $component = Livewire::test(\App\Livewire\CharacterViewer::class, [
        'publicKey' => $character->public_key,
        'characterKey' => $character->character_key,
        'canEdit' => false,
    ]);

    // Get the formatted trait values
    $trait_values = $component->instance()->getFormattedTraitValues();

    expect($trait_values['strength'])->toBe('+2');
    expect($trait_values['finesse'])->toBe('+1');
});

test('player sidebar weapon trait values are correctly calculated', function () {
    $character = Character::factory()->create([
        'name' => 'Test Character',
        'class' => 'warrior',
        'ancestry' => 'human',
        'community' => 'wanderborne',
    ]);

    // Create traits with specific values
    CharacterTrait::factory()->create([
        'character_id' => $character->id,
        'trait_name' => 'strength',
        'trait_value' => 2,
    ]);

    CharacterTrait::factory()->create([
        'character_id' => $character->id,
        'trait_name' => 'finesse',
        'trait_value' => -1,
    ]);
    
    // Fill in other required traits
    foreach (['agility', 'instinct', 'presence', 'knowledge'] as $trait) {
        CharacterTrait::factory()->create([
            'character_id' => $character->id,
            'trait_name' => $trait,
            'trait_value' => 0,
        ]);
    }

    // Create the player sidebar component with CharacterData
    $characterData = \Domain\Character\Data\CharacterData::fromModel($character);
    $component = Livewire::test(\App\Livewire\RoomSidebar\PlayerSidebar::class, [
        'character' => $characterData,
        'can_edit' => true,
    ]);

    // Get the formatted trait values
    $trait_values = $component->instance()->getFormattedTraitValues();

    expect($trait_values['strength'])->toBe('+2');
    expect($trait_values['finesse'])->toBe('-1');
    expect($trait_values['agility'])->toBe('+0');
});
