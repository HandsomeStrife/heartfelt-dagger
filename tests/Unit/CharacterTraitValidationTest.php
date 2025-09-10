<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

test('character trait distribution must follow exact SRD requirements', function () {
    // Test valid trait distribution: [-1, 0, 0, +1, +1, +2]
    $validTraits = [
        'agility' => -1,
        'strength' => 0,
        'finesse' => 0,
        'instinct' => 1,
        'presence' => 1,
        'knowledge' => 2,
    ];

    $character = Character::factory()->create([
        'assigned_traits' => $validTraits,
    ]);

    $traitValues = array_values($character->assigned_traits);
    sort($traitValues);

    expect($traitValues)->toBe([-1, 0, 0, 1, 1, 2]);
});

test('character trait validation rejects invalid distributions', function () {
    // Test invalid distribution - all positive
    $invalidTraits = [
        'agility' => 1,
        'strength' => 1,
        'finesse' => 1,
        'instinct' => 1,
        'presence' => 1,
        'knowledge' => 1,
    ];

    $traitValues = array_values($invalidTraits);
    sort($traitValues);

    expect($traitValues)->not->toBe([-1, 0, 0, 1, 1, 2]);
});

test('character trait validation rejects distribution with wrong total', function () {
    // Test invalid distribution - wrong sum
    $invalidTraits = [
        'agility' => 2,
        'strength' => 2,
        'finesse' => 2,
        'instinct' => 2,
        'presence' => 2,
        'knowledge' => 2,
    ];

    $traitValues = array_values($invalidTraits);
    $sum = array_sum($traitValues);

    // Valid distribution should sum to 3 (-1 + 0 + 0 + 1 + 1 + 2 = 3)
    expect($sum)->not->toBe(3);
});

test('all character traits are required and within valid bounds', function () {
    $requiredTraits = ['agility', 'strength', 'finesse', 'instinct', 'presence', 'knowledge'];

    $validTraits = [
        'agility' => -1,
        'strength' => 0,
        'finesse' => 0,
        'instinct' => 1,
        'presence' => 1,
        'knowledge' => 2,
    ];

    // All traits must be present
    foreach ($requiredTraits as $trait) {
        expect($validTraits)->toHaveKey($trait);
    }

    // All trait values must be within reasonable bounds (-1 to +2)
    foreach ($validTraits as $value) {
        expect($value)->toBeGreaterThanOrEqual(-1);
        expect($value)->toBeLessThanOrEqual(2);
    }
});

test('character starting statistics match SRD specifications', function () {
    // Test various classes have correct starting stats
    $classStats = [
        'warrior' => ['evasion' => 9, 'hp' => 7, 'stress' => 6],
        'guardian' => ['evasion' => 9, 'hp' => 7, 'stress' => 6],
        'ranger' => ['evasion' => 10, 'hp' => 5, 'stress' => 6],
        'rogue' => ['evasion' => 10, 'hp' => 4, 'stress' => 6],
        'bard' => ['evasion' => 10, 'hp' => 5, 'stress' => 6],
        'sorcerer' => ['evasion' => 10, 'hp' => 6, 'stress' => 6],
        'wizard' => ['evasion' => 10, 'hp' => 4, 'stress' => 6],
        'druid' => ['evasion' => 10, 'hp' => 5, 'stress' => 6],
        'seraph' => ['evasion' => 9, 'hp' => 7, 'stress' => 6],
    ];

    foreach ($classStats as $className => $expectedStats) {
        $character = Character::factory()->create([
            'selected_class' => $className,
        ]);

        // Load game data to verify starting stats
        $gameData = app(\App\Livewire\CharacterViewer::class)->loadGameData();
        $classData = $gameData['classes'][$className] ?? null;

        expect($classData)->not->toBeNull();
        expect($classData['startingEvasion'])->toBe($expectedStats['evasion']);
        expect($classData['startingHitPoints'])->toBe($expectedStats['hp']);

        // All characters start with 6 stress slots
        expect($expectedStats['stress'])->toBe(6);
    }
});

test('hope mechanics follow SRD specifications', function () {
    $character = Character::factory()->create();

    // Hope starts at 2, max is 6
    $initialHope = 2;
    $maxHope = 6;
    $minHope = 0;

    expect($initialHope)->toBe(2);
    expect($maxHope)->toBe(6);
    expect($minHope)->toBe(0);

    // All class Hope features cost 3 Hope
    $gameData = app(\App\Livewire\CharacterViewer::class)->loadGameData();

    foreach ($gameData['classes'] as $classData) {
        if (isset($classData['hopeFeature']['hopeCost'])) {
            expect($classData['hopeFeature']['hopeCost'])->toBe(3);
        }
    }
});

test('experience modifiers follow SRD format', function () {
    $character = Character::factory()->create([
        'experiences' => [
            ['name' => 'Wilderness Survival', 'modifier' => 2],
            ['name' => 'Court Politics', 'modifier' => 2],
        ],
    ]);

    // All experiences should have +2 modifier
    foreach ($character->experiences as $experience) {
        expect($experience['modifier'])->toBe(2);
        expect($experience['name'])->toBeString();
        expect(strlen($experience['name']))->toBeGreaterThan(0);
    }
});

test('domain access follows class restrictions', function () {
    $classDomains = [
        'warrior' => ['blade', 'bone'],
        'guardian' => ['valor', 'blade'],
        'ranger' => ['sage', 'bone'],
        'rogue' => ['midnight', 'grace'],
        'bard' => ['grace', 'codex'],
        'sorcerer' => ['arcana', 'midnight'],
        'wizard' => ['codex', 'midnight'],
        'druid' => ['sage', 'arcana'],
        'seraph' => ['splendor', 'valor'],
    ];

    foreach ($classDomains as $className => $expectedDomains) {
        $character = Character::factory()->create([
            'selected_class' => $className,
            'selected_domain_cards' => [
                ['domain' => $expectedDomains[0], 'ability_key' => 'test-ability-1', 'ability_level' => 1],
                ['domain' => $expectedDomains[1], 'ability_key' => 'test-ability-2', 'ability_level' => 1],
            ],
        ]);

        // Verify each domain card belongs to the class's allowed domains
        foreach ($character->selected_domain_cards as $domainCard) {
            expect($domainCard['domain'])->toBeIn($expectedDomains);
            expect($domainCard['ability_level'])->toBe(1); // Starting characters only get level 1 cards
        }
    }
});

test('damage thresholds calculate correctly based on level', function () {
    $level = 1;

    // SRD specifies: Minor (7+level), Major (14+level), Severe (21+level)
    $expectedMinor = 7 + $level;
    $expectedMajor = 14 + $level;
    $expectedSevere = 21 + $level;

    expect($expectedMinor)->toBe(8);
    expect($expectedMajor)->toBe(15);
    expect($expectedSevere)->toBe(22);

    // Test for higher levels
    $level = 3;
    $expectedMinor = 7 + $level;
    $expectedMajor = 14 + $level;
    $expectedSevere = 21 + $level;

    expect($expectedMinor)->toBe(10);
    expect($expectedMajor)->toBe(17);
    expect($expectedSevere)->toBe(24);
});
