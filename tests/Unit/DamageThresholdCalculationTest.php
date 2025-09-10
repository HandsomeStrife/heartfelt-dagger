<?php

declare(strict_types=1);

use Domain\Character\Data\CharacterData;
use Domain\Character\Enums\EquipmentType;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterEquipment;

it('calculates damage thresholds correctly when unarmored', function () {
    // Test unarmored character at different levels
    $testCases = [
        ['level' => 1, 'expectedMajor' => 1, 'expectedSevere' => 2],
        ['level' => 3, 'expectedMajor' => 3, 'expectedSevere' => 6],
        ['level' => 5, 'expectedMajor' => 5, 'expectedSevere' => 10],
        ['level' => 10, 'expectedMajor' => 10, 'expectedSevere' => 20],
    ];

    foreach ($testCases as $case) {
        $character = Character::factory()->create([
            'name' => "Level {$case['level']} Unarmored Test",
            'class' => 'wizard',
            'level' => $case['level'],
        ]);

        // No armor equipment for this character
        $characterData = CharacterData::fromModel($character);

        expect($characterData->getMajorThreshold())
            ->toBe($case['expectedMajor'], "Level {$case['level']} major threshold should be {$case['expectedMajor']}");

        expect($characterData->getSevereThreshold())
            ->toBe($case['expectedSevere'], "Level {$case['level']} severe threshold should be {$case['expectedSevere']}");
    }
});

it('calculates damage thresholds correctly with armor base thresholds', function () {
    // Test armor threshold calculations based on actual armor.json data
    $armorTestCases = [
        [
            'armorKey' => 'gambeson armor',
            'armorData' => [
                'name' => 'Gambeson Armor',
                'baseScore' => 3,
                'baseThresholds' => ['lower' => 5, 'higher' => 11],
            ],
            'expectedMajor' => 5,
            'expectedSevere' => 11,
        ],
        [
            'armorKey' => 'leather armor',
            'armorData' => [
                'name' => 'Leather Armor',
                'baseScore' => 3,
                'baseThresholds' => ['lower' => 6, 'higher' => 13],
            ],
            'expectedMajor' => 6,
            'expectedSevere' => 13,
        ],
        [
            'armorKey' => 'chainmail armor',
            'armorData' => [
                'name' => 'Chainmail Armor',
                'baseScore' => 4,
                'baseThresholds' => ['lower' => 7, 'higher' => 15],
            ],
            'expectedMajor' => 7,
            'expectedSevere' => 15,
        ],
    ];

    foreach ($armorTestCases as $case) {
        $character = Character::factory()->create([
            'name' => "Armored Test - {$case['armorData']['name']}",
            'class' => 'warrior',
            'level' => 1,
        ]);

        // Create equipped armor
        CharacterEquipment::factory()->create([
            'character_id' => $character->id,
            'equipment_type' => EquipmentType::ARMOR->value,
            'equipment_key' => $case['armorKey'],
            'equipment_data' => $case['armorData'],
            'is_equipped' => true,
        ]);

        $characterData = CharacterData::fromModel($character);

        expect($characterData->getMajorThreshold())
            ->toBe($case['expectedMajor'], "With {$case['armorData']['name']}, major threshold should be {$case['expectedMajor']}");

        expect($characterData->getSevereThreshold())
            ->toBe($case['expectedSevere'], "With {$case['armorData']['name']}, severe threshold should be {$case['expectedSevere']}");
    }
});

it('calculates damage thresholds with level bonuses when armored', function () {
    // Test that level bonuses are added to armor base thresholds
    $character = Character::factory()->create([
        'name' => 'Level 5 Armored Test',
        'class' => 'guardian',
        'level' => 5,
    ]);

    // Leather armor: base 6/13
    CharacterEquipment::factory()->create([
        'character_id' => $character->id,
        'equipment_type' => EquipmentType::ARMOR->value,
        'equipment_key' => 'leather armor',
        'equipment_data' => [
            'name' => 'Leather Armor',
            'baseScore' => 3,
            'baseThresholds' => ['lower' => 6, 'higher' => 13],
        ],
        'is_equipped' => true,
    ]);

    $characterData = CharacterData::fromModel($character);

    // At level 5: armor base (6/13) + level bonus (4) = 10/17
    // Level bonus = level - 1 = 4
    $levelBonus = $character->level - 1; // 4
    $expectedMajor = 6 + $levelBonus; // 6 + 4 = 10
    $expectedSevere = 13 + $levelBonus; // 13 + 4 = 17

    expect($characterData->getMajorThreshold())
        ->toBe($expectedMajor, "Level 5 with leather armor major threshold should be {$expectedMajor}");

    expect($characterData->getSevereThreshold())
        ->toBe($expectedSevere, "Level 5 with leather armor severe threshold should be {$expectedSevere}");
});

it('handles multiple armor pieces by using the highest thresholds', function () {
    $character = Character::factory()->create([
        'name' => 'Multiple Armor Test',
        'class' => 'warrior',
        'level' => 1,
    ]);

    // Create multiple equipped armor pieces
    CharacterEquipment::factory()->create([
        'character_id' => $character->id,
        'equipment_type' => EquipmentType::ARMOR->value,
        'equipment_key' => 'gambeson armor',
        'equipment_data' => [
            'name' => 'Gambeson Armor',
            'baseScore' => 3,
            'baseThresholds' => ['lower' => 5, 'higher' => 11],
        ],
        'is_equipped' => true,
    ]);

    CharacterEquipment::factory()->create([
        'character_id' => $character->id,
        'equipment_type' => EquipmentType::ARMOR->value,
        'equipment_key' => 'chainmail armor',
        'equipment_data' => [
            'name' => 'Chainmail Armor',
            'baseScore' => 4,
            'baseThresholds' => ['lower' => 7, 'higher' => 15],
        ],
        'is_equipped' => true,
    ]);

    $characterData = CharacterData::fromModel($character);

    // Should use the highest thresholds (chainmail: 7/15 vs gambeson: 5/11)
    expect($characterData->getMajorThreshold())
        ->toBe(7, 'Multiple armor should use highest major threshold');

    expect($characterData->getSevereThreshold())
        ->toBe(15, 'Multiple armor should use highest severe threshold');
});

it('correctly calculates thresholds across all character classes', function () {
    $classes = ['bard', 'druid', 'guardian', 'ranger', 'rogue', 'seraph', 'sorcerer', 'warrior', 'wizard'];

    foreach ($classes as $className) {
        // Test unarmored
        $unarmoredCharacter = Character::factory()->create([
            'name' => "Unarmored {$className}",
            'class' => $className,
            'level' => 3,
        ]);

        $unarmoredData = CharacterData::fromModel($unarmoredCharacter);

        expect($unarmoredData->getMajorThreshold())
            ->toBe(3, "Unarmored level 3 {$className} major threshold should be 3");
        expect($unarmoredData->getSevereThreshold())
            ->toBe(6, "Unarmored level 3 {$className} severe threshold should be 6");

        // Test with standard leather armor
        $armoredCharacter = Character::factory()->create([
            'name' => "Armored {$className}",
            'class' => $className,
            'level' => 3,
        ]);

        CharacterEquipment::factory()->create([
            'character_id' => $armoredCharacter->id,
            'equipment_type' => EquipmentType::ARMOR->value,
            'equipment_key' => 'leather armor',
            'equipment_data' => [
                'name' => 'Leather Armor',
                'baseScore' => 3,
                'baseThresholds' => ['lower' => 6, 'higher' => 13],
            ],
            'is_equipped' => true,
        ]);

        $armoredData = CharacterData::fromModel($armoredCharacter);

        // Level 3: armor base (6/13) + level bonus (2) = 8/15
        $levelBonus = $armoredCharacter->level - 1; // 2
        expect($armoredData->getMajorThreshold())
            ->toBe(6 + $levelBonus, "Armored level 3 {$className} major threshold should be ".(6 + $levelBonus));
        expect($armoredData->getSevereThreshold())
            ->toBe(13 + $levelBonus, "Armored level 3 {$className} severe threshold should be ".(13 + $levelBonus));
    }
});

it('matches JSON armor data with calculated thresholds', function () {
    // Load actual armor data from JSON to verify our calculations match
    $armorJsonPath = resource_path('json/armor.json');
    $armorData = json_decode(file_get_contents($armorJsonPath), true);

    // Test a sampling of armors from the JSON file
    $testArmors = [
        'gambeson armor' => ['expectedMajor' => 5, 'expectedSevere' => 11],
        'leather armor' => ['expectedMajor' => 6, 'expectedSevere' => 13],
        'chainmail armor' => ['expectedMajor' => 7, 'expectedSevere' => 15],
    ];

    foreach ($testArmors as $armorKey => $expected) {
        expect($armorData)->toHaveKey($armorKey);

        $armor = $armorData[$armorKey];
        expect($armor['baseThresholds']['lower'])
            ->toBe($expected['expectedMajor']);
        expect($armor['baseThresholds']['higher'])
            ->toBe($expected['expectedSevere']);

        // Test calculation matches JSON data
        $character = Character::factory()->create([
            'class' => 'warrior',
            'level' => 1,
        ]);

        CharacterEquipment::factory()->create([
            'character_id' => $character->id,
            'equipment_type' => EquipmentType::ARMOR->value,
            'equipment_key' => $armorKey,
            'equipment_data' => $armor,
            'is_equipped' => true,
        ]);

        $characterData = CharacterData::fromModel($character);

        expect($characterData->getMajorThreshold())
            ->toBe($expected['expectedMajor']);
        expect($characterData->getSevereThreshold())
            ->toBe($expected['expectedSevere']);
    }
});
