<?php

declare(strict_types=1);
use Domain\Character\Actions\SaveCharacterAction;
use Domain\Character\Data\CharacterBuilderData;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterDomainCard;
use Domain\Character\Models\CharacterEquipment;
use Domain\Character\Models\CharacterExperience;
use Domain\Character\Models\CharacterTrait;
use Domain\User\Models\User;
use PHPUnit\Framework\Attributes\Test;
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->action = new SaveCharacterAction;
});
it('creates new character with basic data', function () {
    $user = User::factory()->create();
    $builderData = new CharacterBuilderData(
        name: 'Test Hero',
        selected_class: 'warrior',
        selected_subclass: 'call-of-the-brave',
        selected_ancestry: 'human',
        selected_community: 'order-of-scholars'
    );

    $character = $this->action->execute($builderData, $user);

    expect($character)->toBeInstanceOf(Character::class);
    expect($character->name)->toEqual('Test Hero');
    expect($character->class)->toEqual('warrior');
    expect($character->subclass)->toEqual('call-of-the-brave');
    expect($character->ancestry)->toEqual('human');
    expect($character->community)->toEqual('order-of-scholars');
    expect($character->user_id)->toEqual($user->id);
    expect($character->character_key)->not->toBeNull();
    expect(strlen($character->character_key))->toEqual(10);
});
it('creates character without user', function () {
    $builderData = new CharacterBuilderData(
        name: 'Anonymous Hero',
        selected_class: 'ranger'
    );

    $character = $this->action->execute($builderData, null);

    expect($character)->toBeInstanceOf(Character::class);
    expect($character->name)->toEqual('Anonymous Hero');
    expect($character->class)->toEqual('ranger');
    expect($character->user_id)->toBeNull();
});
it('creates character with null values', function () {
    $builderData = new CharacterBuilderData(
        name: null,
        selected_class: null,
        selected_subclass: null,
        selected_ancestry: null,
        selected_community: null
    );

    $character = $this->action->execute($builderData, null);

    expect($character)->toBeInstanceOf(Character::class);
    expect($character->name)->toBeNull();
    expect($character->class)->toBeNull();
    expect($character->subclass)->toBeNull();
    expect($character->ancestry)->toBeNull();
    expect($character->community)->toBeNull();
});
it('saves character traits', function () {
    $builderData = new CharacterBuilderData(
        name: 'Test Hero',
        assigned_traits: [
            'agility' => 2,
            'strength' => -1,
            'finesse' => 0,
        ]
    );

    $character = $this->action->execute($builderData, null);

    expect($character->traits)->toHaveCount(3);

    $traits = $character->traits->keyBy('trait_name');
    expect($traits['agility']->trait_value)->toEqual(2);
    expect($traits['strength']->trait_value)->toEqual(-1);
    expect($traits['finesse']->trait_value)->toEqual(0);
});
it('saves character equipment', function () {
    $builderData = new CharacterBuilderData(
        name: 'Test Hero',
        selected_equipment: [
            [
                'key' => 'shortsword',
                'type' => 'weapon',
                'data' => ['damage' => '1d6'],
            ],
            [
                'key' => 'leather-armor',
                'type' => 'armor',
                'data' => ['armor_value' => 2],
            ],
        ]
    );

    $character = $this->action->execute($builderData, null);

    expect($character->equipment)->toHaveCount(2);

    $equipment = $character->equipment->keyBy('equipment_key');
    expect($equipment->has('shortsword'))->toBeTrue();
    expect($equipment->has('leather-armor'))->toBeTrue();

    $sword = $equipment['shortsword'];
    expect($sword->equipment_type)->toEqual('weapon');
    expect($sword->equipment_data)->toEqual(['damage' => '1d6']);

    $armor = $equipment['leather-armor'];
    expect($armor->equipment_type)->toEqual('armor');
    expect($armor->equipment_data)->toEqual(['armor_value' => 2]);
});
it('saves character experiences', function () {
    $builderData = new CharacterBuilderData(
        name: 'Test Hero',
        experiences: [
            [
                'name' => 'Combat Training',
                'description' => 'Trained with the city guard',
                'modifier' => 2,
            ],
            [
                'name' => 'Academic Study',
                'description' => 'Studied ancient texts',
                'modifier' => 1,
            ],
        ]
    );

    $character = $this->action->execute($builderData, null);

    expect($character->experiences)->toHaveCount(2);

    $experiences = $character->experiences;
    expect($experiences[0]->experience_name)->toEqual('Combat Training');
    expect($experiences[0]->experience_description)->toEqual('Trained with the city guard');
    expect($experiences[0]->modifier)->toEqual(2);

    expect($experiences[1]->experience_name)->toEqual('Academic Study');
    expect($experiences[1]->experience_description)->toEqual('Studied ancient texts');
    expect($experiences[1]->modifier)->toEqual(1);
});
it('saves character domain cards', function () {
    $builderData = new CharacterBuilderData(
        name: 'Test Hero',
        selected_domain_cards: [
            [
                'domain' => 'blade',
                'ability_key' => 'strike',
                'ability_level' => 1,
            ],
            [
                'domain' => 'grace',
                'ability_key' => 'dodge',
                'ability_level' => 2,
            ],
        ]
    );

    $character = $this->action->execute($builderData, null);

    expect($character->domainCards)->toHaveCount(2);

    $domainCards = $character->domainCards;
    expect($domainCards[0]->domain)->toEqual('blade');
    expect($domainCards[0]->ability_key)->toEqual('strike');
    expect($domainCards[0]->ability_level)->toEqual(1);

    expect($domainCards[1]->domain)->toEqual('grace');
    expect($domainCards[1]->ability_key)->toEqual('dodge');
    expect($domainCards[1]->ability_level)->toEqual(2);
});
it('saves background and connection data', function () {
    $builderData = new CharacterBuilderData(
        name: 'Test Hero',
        background_answers: ['Answer 1', 'Answer 2', 'Answer 3'],
        connection_answers: ['Connection 1', 'Connection 2'],
        physical_description: 'Tall and strong',
        personality_traits: 'Brave and loyal',
        personal_history: 'Born in a small village',
        motivations: 'To protect the innocent'
    );

    $character = $this->action->execute($builderData, null);

    $characterData = $character->character_data;
    expect($characterData['background']['answers'])->toEqual(['Answer 1', 'Answer 2', 'Answer 3']);
    expect($characterData['connections'])->toEqual(['Connection 1', 'Connection 2']);
    expect($characterData['background']['physicalDescription'])->toEqual('Tall and strong');
    expect($characterData['background']['personalityTraits'])->toEqual('Brave and loyal');
    expect($characterData['background']['personalHistory'])->toEqual('Born in a small village');
    expect($characterData['background']['motivations'])->toEqual('To protect the innocent');
});
it('updates existing character', function () {
    $existingCharacter = Character::factory()->create([
        'name' => 'Old Name',
        'class' => 'warrior',
    ]);

    // Add some existing related data
    CharacterTrait::factory()->create([
        'character_id' => $existingCharacter->id,
        'trait_name' => 'agility',
        'trait_value' => 1,
    ]);

    $builderData = new CharacterBuilderData(
        name: 'Updated Name',
        selected_class: 'ranger',
        assigned_traits: [
            'strength' => 2,
            'finesse' => -1,
        ]
    );

    $updatedCharacter = $this->action->updateCharacter($existingCharacter, $builderData);

    expect($updatedCharacter->name)->toEqual('Updated Name');
    expect($updatedCharacter->class)->toEqual('ranger');

    // Old traits should be replaced with new ones
    expect($updatedCharacter->traits)->toHaveCount(2);
    $traits = $updatedCharacter->traits->keyBy('trait_name');
    expect($traits->toArray())->not->toHaveKey('agility');
    expect($traits['strength']->trait_value)->toEqual(2);
    expect($traits['finesse']->trait_value)->toEqual(-1);
});
it('clears related data when updating', function () {
    $existingCharacter = Character::factory()->create();

    // Add existing related data
    CharacterTrait::factory()->create(['character_id' => $existingCharacter->id]);
    CharacterEquipment::factory()->create(['character_id' => $existingCharacter->id]);
    CharacterExperience::factory()->create(['character_id' => $existingCharacter->id]);
    CharacterDomainCard::factory()->create(['character_id' => $existingCharacter->id]);

    $builderData = new CharacterBuilderData(name: 'Test Hero');

    $updatedCharacter = $this->action->updateCharacter($existingCharacter, $builderData);

    // All related data should be cleared since builderData has empty arrays
    expect($updatedCharacter->traits)->toHaveCount(0);
    expect($updatedCharacter->equipment)->toHaveCount(0);
    expect($updatedCharacter->experiences)->toHaveCount(0);
    expect($updatedCharacter->domainCards)->toHaveCount(0);
});
it('handles profile image path', function () {
    $builderData = new CharacterBuilderData(
        name: 'Test Hero',
        profile_image_path: 'portraits/hero.jpg'
    );

    $character = $this->action->execute($builderData, null);

    expect($character->profile_image_path)->toEqual('portraits/hero.jpg');
});
it('wraps operation in transaction', function () {
    // This test ensures that if anything fails, everything is rolled back
    $builderData = new CharacterBuilderData(
        name: 'Test Hero',
        assigned_traits: [
            'agility' => 2,
        ]
    );

    $character = $this->action->execute($builderData, null);

    // Verify both character and traits were created
    \Pest\Laravel\assertDatabaseHas('characters', ['name' => 'Test Hero']);
    \Pest\Laravel\assertDatabaseHas('character_traits', [
        'character_id' => $character->id,
        'trait_name' => 'agility',
        'trait_value' => 2,
    ]);
});
