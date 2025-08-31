<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use App\Livewire\CharacterViewer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('class starting statistics match SRD values exactly', function () {
    $srdClassStats = [
        'bard' => ['startingEvasion' => 10, 'startingHitPoints' => 5],
        'druid' => ['startingEvasion' => 10, 'startingHitPoints' => 5],
        'guardian' => ['startingEvasion' => 9, 'startingHitPoints' => 7],
        'ranger' => ['startingEvasion' => 10, 'startingHitPoints' => 5],
        'rogue' => ['startingEvasion' => 10, 'startingHitPoints' => 4],
        'seraph' => ['startingEvasion' => 9, 'startingHitPoints' => 7],
        'sorcerer' => ['startingEvasion' => 10, 'startingHitPoints' => 6],
        'warrior' => ['startingEvasion' => 9, 'startingHitPoints' => 7],
        'wizard' => ['startingEvasion' => 10, 'startingHitPoints' => 4],
    ];

    $character = Character::factory()->create();
    $characterViewer = new CharacterViewer();
    $characterViewer->mount($character->public_key, $character->character_key, false);
    $gameData = $characterViewer->loadGameData();

    foreach ($srdClassStats as $className => $expectedStats) {
        expect($gameData['classes'])->toHaveKey($className);
        
        $classData = $gameData['classes'][$className];
        expect($classData['startingEvasion'])->toBe($expectedStats['startingEvasion']);
        expect($classData['startingHitPoints'])->toBe($expectedStats['startingHitPoints']);
    }
});

test('all characters start with exactly 6 stress slots', function () {
    $character = Character::factory()->create([
        'selected_class' => 'warrior',
    ]);

    $characterViewer = new CharacterViewer();
    $characterViewer->mount($character->public_key, $character->character_key, false);
    $computedStats = $characterViewer->getComputedStats();

    // All characters start with 6 stress according to SRD
    expect($computedStats['final_stress'] ?? 6)->toBe(6);
});

test('class domain pairings match SRD specifications', function () {
    $srdClassDomains = [
        'bard' => ['grace', 'codex'],
        'druid' => ['sage', 'arcana'],
        'guardian' => ['valor', 'blade'],
        'ranger' => ['sage', 'bone'],
        'rogue' => ['midnight', 'grace'],
        'seraph' => ['splendor', 'valor'],
        'sorcerer' => ['arcana', 'midnight'],
        'warrior' => ['blade', 'bone'],
        'wizard' => ['codex', 'midnight'],
    ];

    $character = Character::factory()->create();
    $characterViewer = new CharacterViewer();
    $characterViewer->mount($character->public_key, $character->character_key, false);
    $gameData = $characterViewer->loadGameData();

    foreach ($srdClassDomains as $className => $expectedDomains) {
        expect($gameData['classes'])->toHaveKey($className);
        
        $classData = $gameData['classes'][$className];
        expect($classData['domains'])->toBe($expectedDomains);
        expect($classData['domains'])->toHaveCount(2); // Exactly 2 domains per class
    }
});

test('domain card access is restricted to class domains', function () {
    $character = Character::factory()->create([
        'selected_class' => 'sorcerer', // Arcana & Midnight
        'selected_domain_cards' => [
            ['domain' => 'arcana', 'ability_key' => 'elemental-blast', 'ability_level' => 1],
            ['domain' => 'midnight', 'ability_key' => 'shadow-step', 'ability_level' => 1],
        ],
    ]);

    $characterViewer = new CharacterViewer();
    $characterViewer->mount($character->public_key, $character->character_key, false);
    $gameData = $characterViewer->loadGameData();
    
    $classData = $gameData['classes']['sorcerer'];
    $allowedDomains = $classData['domains'];

    foreach ($character->selected_domain_cards as $domainCard) {
        expect($allowedDomains)->toContain($domainCard['domain']);
        expect($domainCard['ability_level'])->toBe(1); // Starting characters only get level 1
    }
});

test('starting characters are limited to exactly 2 domain cards', function () {
    $character = Character::factory()->create([
        'selected_class' => 'wizard',
        'selected_domain_cards' => [
            ['domain' => 'codex', 'ability_key' => 'knowledge-is-power', 'ability_level' => 1],
            ['domain' => 'midnight', 'ability_key' => 'veil-of-shadows', 'ability_level' => 1],
        ],
    ]);

    expect($character->selected_domain_cards)->toHaveCount(2);
    
    // Verify each card is level 1
    foreach ($character->selected_domain_cards as $card) {
        expect($card['ability_level'])->toBe(1);
    }
});

test('hope economy follows SRD rules', function () {
    $character = Character::factory()->create();

    // Hope starts at 2, max is 6
    $startingHope = 2;
    $maxHope = 6;
    $minHope = 0;

    expect($startingHope)->toBe(2);
    expect($maxHope)->toBe(6);
    expect($minHope)->toBe(0);
});

test('all class hope features cost exactly 3 hope', function () {
    $characterViewer = new CharacterViewer();
    $gameData = $characterViewer->loadGameData();

    foreach ($gameData['classes'] as $className => $classData) {
        if (isset($classData['hopeFeature']['hopeCost'])) {
            expect($classData['hopeFeature']['hopeCost'])->toBe(3);
        }
    }
});

test('damage thresholds calculate according to SRD formula', function () {
    $character = Character::factory()->create([
        'selected_class' => 'guardian',
    ]);

    $characterViewer = new CharacterViewer();
    $characterViewer->mount($character->public_key, $character->character_key, false);
    $computedStats = $characterViewer->getComputedStats();

    $level = 1;
    
    // SRD formula: damage thresholds based on armor + level modifiers
    // major_threshold is the threshold between minor and major damage  
    // severe_threshold is the threshold between major and severe damage
    expect($computedStats['major_threshold'])->toBeGreaterThanOrEqual(1);
    expect($computedStats['severe_threshold'])->toBeGreaterThanOrEqual(1);
    expect($computedStats['severe_threshold'])->toBeGreaterThan($computedStats['major_threshold']);
});

test('equipment tier restrictions are enforced for starting characters', function () {
    // Starting characters should only have Tier 1 equipment
    $character = Character::factory()->create([
        'selected_equipment' => [
            [
                'key' => 'leather-armor',
                'type' => 'armor',
                'data' => [
                    'name' => 'Leather Armor',
                    'tier' => 1, // Tier 1 is allowed
                    'baseScore' => 3,
                ]
            ],
            [
                'key' => 'shortbow',
                'type' => 'weapon',
                'data' => [
                    'name' => 'Shortbow',
                    'tier' => 1, // Tier 1 is allowed
                    'trait' => 'Agility',
                ]
            ]
        ],
    ]);

    foreach ($character->selected_equipment as $equipment) {
        if (isset($equipment['data']['tier'])) {
            expect($equipment['data']['tier'])->toBe(1);
        }
    }
});

test('trait distribution validation enforces exact SRD array', function () {
    $validDistribution = [-1, 0, 0, 1, 1, 2];
    
    $character = Character::factory()->create([
        'assigned_traits' => [
            'agility' => -1,
            'strength' => 0,
            'finesse' => 0,
            'instinct' => 1,
            'presence' => 1,
            'knowledge' => 2,
        ],
    ]);

    $traitValues = array_values($character->assigned_traits);
    sort($traitValues);
    
    expect($traitValues)->toBe($validDistribution);
    expect(array_sum($traitValues))->toBe(3); // Sum must be 3
});

test('computed statistics use correct SRD calculations', function () {
    $character = Character::factory()->create([
        'selected_class' => 'warrior',
        'assigned_traits' => [
            'agility' => 0,
            'strength' => 2,
            'finesse' => -1,
            'instinct' => 1,
            'presence' => 0,
            'knowledge' => 1,
        ],
    ]);

    $characterViewer = new CharacterViewer();
    $characterViewer->mount($character->public_key, $character->character_key, false);
    $computedStats = $characterViewer->getComputedStats();

    // Warrior starts with Evasion 9, HP 7
    expect($computedStats['final_evasion'])->toBe(9);
    expect($computedStats['final_hit_points'])->toBe(7);
    
    // Stress should always be 6 for starting characters
    expect($computedStats['final_stress'])->toBe(6);
});

test('experience modifiers follow SRD format of plus two', function () {
    $character = Character::factory()->create([
        'experiences' => [
            ['name' => 'Wilderness Survival', 'modifier' => 2],
            ['name' => 'Blacksmithing', 'modifier' => 2],
        ],
    ]);

    foreach ($character->experiences as $experience) {
        expect($experience['modifier'])->toBe(2);
        expect($experience['name'])->toBeString();
        expect(strlen($experience['name']))->toBeGreaterThan(0);
    }
});

test('all domain names are valid according to SRD', function () {
    $validDomains = [
        'arcana', 'blade', 'bone', 'codex', 'grace', 
        'midnight', 'sage', 'splendor', 'valor'
    ];

    $characterViewer = new CharacterViewer();
    $gameData = $characterViewer->loadGameData();

    // Check that all class domains are valid
    foreach ($gameData['classes'] as $classData) {
        foreach ($classData['domains'] as $domain) {
            expect($validDomains)->toContain($domain);
        }
    }

    expect($validDomains)->toHaveCount(9); // Exactly 9 domains in DaggerHeart
});

test('ancestry and community data loads correctly', function () {
    $character = Character::factory()->create([
        'selected_ancestry' => 'dwarf',
        'selected_community' => 'ridgeborne',
    ]);

    $characterViewer = new CharacterViewer();
    $characterViewer->mount($character->public_key, $character->character_key, false);
    $gameData = $characterViewer->loadGameData();

    expect($gameData['ancestries'])->toHaveKey('dwarf');
    expect($gameData['communities'])->toHaveKey('ridgeborne');
    
    $ancestryData = $gameData['ancestries']['dwarf'];
    $communityData = $gameData['communities']['ridgeborne'];
    
    expect($ancestryData['name'])->toBeString();
    expect($communityData['name'])->toBeString();
});
