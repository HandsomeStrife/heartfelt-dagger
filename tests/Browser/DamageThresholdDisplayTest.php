<?php

declare(strict_types=1);

use Domain\Character\Enums\EquipmentType;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterEquipment;

it('displays correct damage thresholds for unarmored character', function () {
    // Create an unarmored level 3 character
    $character = Character::factory()->create([
        'name' => 'Unarmored Browser Test',
        'class' => 'wizard',
        'level' => 3,
        'is_public' => true,
    ]);

    // Visit the character viewer
    $page = visit("/character/{$character->public_key}");

    // According to SRD: Unarmored major threshold = level (3), severe threshold = 2×level (6)
    $page->assertSee('3') // Major threshold
        ->assertSee('6'); // Severe threshold
});

it('displays correct damage thresholds for armored character', function () {
    // Create a level 5 character with leather armor
    $character = Character::factory()->create([
        'name' => 'Armored Browser Test',
        'class' => 'guardian',
        'level' => 5,
        'is_public' => true,
    ]);

    // Add equipped leather armor (base thresholds: 6/13)
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

    // Visit the character viewer
    $page = visit("/character/{$character->public_key}");

    // Level 5 with leather armor: base (6/13) + level bonus (4) = 10/17
    $page->assertSee('10') // Major threshold
        ->assertSee('17'); // Severe threshold
});

it('displays thresholds for different armor types correctly', function () {
    // Test cases for different armor types
    $testCases = [
        [
            'armorKey' => 'gambeson armor',
            'armorData' => [
                'name' => 'Gambeson Armor',
                'baseScore' => 3,
                'baseThresholds' => ['lower' => 5, 'higher' => 11],
            ],
            'expectedMajor' => '5',
            'expectedSevere' => '11',
            'description' => 'Gambeson Armor',
        ],
        [
            'armorKey' => 'chainmail armor',
            'armorData' => [
                'name' => 'Chainmail Armor',
                'baseScore' => 4,
                'baseThresholds' => ['lower' => 7, 'higher' => 15],
            ],
            'expectedMajor' => '7',
            'expectedSevere' => '15',
            'description' => 'Chainmail Armor',
        ],
    ];

    foreach ($testCases as $case) {
        // Create level 1 character for each armor type
        $character = Character::factory()->create([
            'name' => "Browser Test - {$case['description']}",
            'class' => 'warrior',
            'level' => 1,
            'is_public' => true,
        ]);

        // Add the specific armor
        CharacterEquipment::factory()->create([
            'character_id' => $character->id,
            'equipment_type' => EquipmentType::ARMOR->value,
            'equipment_key' => $case['armorKey'],
            'equipment_data' => $case['armorData'],
            'is_equipped' => true,
        ]);

        // Visit and verify the thresholds display
        $page = visit("/character/{$character->public_key}");

        $page->assertSee($case['expectedMajor'])
            ->assertSee($case['expectedSevere']);
    }
});
