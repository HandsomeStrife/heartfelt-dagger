<?php

declare(strict_types=1);

use Domain\Character\Data\CharacterData;
use Domain\Character\Data\CharacterEquipmentItemData;
use Domain\Character\Enums\EquipmentType;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterEquipment;

it('creates character equipment collection from model', function () {
    // Create a character
    $character = Character::factory()->create([
        'name' => 'Test Warrior',
        'class' => 'warrior',
        'level' => 5,
    ]);

    // Create equipment items
    $sword = CharacterEquipment::factory()->create([
        'character_id' => $character->id,
        'equipment_type' => EquipmentType::WEAPON->value,
        'equipment_key' => 'longsword',
        'equipment_data' => [
            'name' => 'Longsword',
            'damage' => ['dice' => '1d8', 'modifier' => 0, 'type' => 'physical'],
            'tier' => 1,
        ],
        'is_equipped' => true,
    ]);

    $armor = CharacterEquipment::factory()->create([
        'character_id' => $character->id,
        'equipment_type' => EquipmentType::ARMOR->value,
        'equipment_key' => 'leather-armor',
        'equipment_data' => [
            'name' => 'Leather Armor',
            'armor_score' => 2,
            'tier' => 1,
        ],
        'is_equipped' => true,
    ]);

    $unequippedWeapon = CharacterEquipment::factory()->create([
        'character_id' => $character->id,
        'equipment_type' => EquipmentType::WEAPON->value,
        'equipment_key' => 'dagger',
        'equipment_data' => [
            'name' => 'Dagger',
            'damage' => ['dice' => '1d4', 'modifier' => 0, 'type' => 'physical'],
            'tier' => 1,
        ],
        'is_equipped' => false,
    ]);

    // Create CharacterData
    $characterData = CharacterData::fromModel($character);

    // Verify equipment collection structure
    expect($characterData->equipment)
        ->toBeInstanceOf(\Illuminate\Support\Collection::class)
        ->and($characterData->equipment->count())
        ->toBe(3);

    // Verify each equipment item is a CharacterEquipmentItemData
    expect($characterData->equipment->first())
        ->toBeInstanceOf(CharacterEquipmentItemData::class);

    // Test equipped weapons
    $equippedWeapons = $characterData->getEquippedWeapons();
    expect($equippedWeapons->count())->toBe(1);
    expect($equippedWeapons->first()->equipment_key)->toBe('longsword');
    expect($equippedWeapons->first()->is_equipped)->toBeTrue();

    // Test equipped armor
    $equippedArmor = $characterData->getEquippedArmor();
    expect($equippedArmor->count())->toBe(1);
    expect($equippedArmor->first()->equipment_key)->toBe('leather-armor');
    expect($equippedArmor->first()->is_equipped)->toBeTrue();

    // Test armor score calculation
    expect($characterData->getTotalArmorScore())->toBe(3); // 1 base + 2 from leather armor
});

it('calculates damage thresholds correctly', function () {
    // Create a level 5 character
    $character = Character::factory()->create([
        'name' => 'Test Fighter',
        'class' => 'warrior',
        'level' => 5,
    ]);

    // Create chainmail armor with proper SRD base thresholds
    CharacterEquipment::factory()->create([
        'character_id' => $character->id,
        'equipment_type' => EquipmentType::ARMOR->value,
        'equipment_key' => 'chainmail',
        'equipment_data' => [
            'name' => 'Chainmail',
            'armor_score' => 3,
            'baseThresholds' => ['lower' => 7, 'higher' => 15], // Chainmail SRD values
        ],
        'is_equipped' => true,
    ]);

    $characterData = CharacterData::fromModel($character);

    // SRD calculation for level 5 with chainmail:
    // Total armor score: 1 (base) + 3 (chainmail) = 4
    // Major threshold: 7 (chainmail base) + 4 (level bonus) = 11
    // Severe threshold: 15 (chainmail base) + 4 (level bonus) = 19

    expect($characterData->getTotalArmorScore())->toBe(4);
    expect($characterData->getMajorThreshold())->toBe(11);
    expect($characterData->getSevereThreshold())->toBe(19);
});

it('handles character with no equipment', function () {
    $character = Character::factory()->create([
        'name' => 'Naked Character',
        'class' => 'wizard',
        'level' => 1,
    ]);

    $characterData = CharacterData::fromModel($character);

    expect($characterData->equipment->count())->toBe(0);
    expect($characterData->getEquippedWeapons()->count())->toBe(0);
    expect($characterData->getEquippedArmor()->count())->toBe(0);
    expect($characterData->getTotalArmorScore())->toBe(1); // Just base armor score
    expect($characterData->hasMinimumEquipment())->toBeFalse();
});

it('filters equipment by type correctly', function () {
    $character = Character::factory()->create();

    // Create various equipment types
    CharacterEquipment::factory()->create([
        'character_id' => $character->id,
        'equipment_type' => EquipmentType::WEAPON->value,
        'is_equipped' => true,
    ]);

    CharacterEquipment::factory()->create([
        'character_id' => $character->id,
        'equipment_type' => EquipmentType::ARMOR->value,
        'is_equipped' => true,
    ]);

    CharacterEquipment::factory()->create([
        'character_id' => $character->id,
        'equipment_type' => EquipmentType::ITEM->value,
        'is_equipped' => false,
    ]);

    CharacterEquipment::factory()->create([
        'character_id' => $character->id,
        'equipment_type' => EquipmentType::CONSUMABLE->value,
        'is_equipped' => false,
    ]);

    $characterData = CharacterData::fromModel($character);

    // Test type filtering
    $weapons = $characterData->equipment->filter(fn ($item) => $item->isWeapon());
    $armor = $characterData->equipment->filter(fn ($item) => $item->isArmor());
    $items = $characterData->equipment->filter(fn ($item) => $item->isItem());
    $consumables = $characterData->equipment->filter(fn ($item) => $item->isConsumable());

    expect($weapons->count())->toBe(1);
    expect($armor->count())->toBe(1);
    expect($items->count())->toBe(1);
    expect($consumables->count())->toBe(1);

    // Test equipped filtering
    expect($characterData->getEquippedWeapons()->count())->toBe(1);
    expect($characterData->getEquippedArmor()->count())->toBe(1);
});

it('tests CharacterEquipmentItemData functionality', function () {
    $equipmentData = new CharacterEquipmentItemData(
        id: 1,
        character_id: 123,
        equipment_type: EquipmentType::ARMOR->value,
        equipment_key: 'plate-armor',
        equipment_data: [
            'name' => 'Plate Armor',
            'armor_score' => 5,
            'tier' => 2,
            'feature' => ['name' => 'Heavy Protection'],
        ],
        is_equipped: true,
    );

    expect($equipmentData->getEquipmentName())->toBe('Plate Armor');
    expect($equipmentData->getArmorScore())->toBe(5);
    expect($equipmentData->getTier())->toBe(2);
    expect($equipmentData->isArmor())->toBeTrue();
    expect($equipmentData->isWeapon())->toBeFalse();
    expect($equipmentData->isEquippedArmor())->toBeTrue();
    expect($equipmentData->getFeature())->toBe(['name' => 'Heavy Protection']);
});
