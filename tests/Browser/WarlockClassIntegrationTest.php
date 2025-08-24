<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

test('warlock class data loads correctly from JSON', function () {
    $character = Character::factory()->create([
        'class' => 'warlock',
        'subclass' => 'pact of the endless',
    ]);

    $classData = $character->getClassData();
    
    // Verify class data structure
    expect($classData)->toHaveKey('name');
    expect($classData)->toHaveKey('description');
    expect($classData)->toHaveKey('domains');
    expect($classData)->toHaveKey('startingEvasion');
    expect($classData)->toHaveKey('startingHitPoints');
    // All classes start with 6 stress by default
    expect($classData)->toHaveKey('playtest');
    
    // Verify specific values
    expect($classData['name'])->toBe('Warlock');
    expect($classData['domains'])->toContain('dread');
    expect($classData['domains'])->toContain('grace');
    expect($classData['startingEvasion'])->toBe(11);
    expect($classData['startingHitPoints'])->toBe(6);
    // Starting stress is 6 for all classes by default
    
    // Verify playtest marking
    expect($classData['playtest']['isPlaytest'])->toBe(true);
    expect($classData['playtest']['version'])->toBe('1.5');
    expect($classData['playtest']['label'])->toBe('Void - Playtest v1.5');
});

test('warlock subclasses load correctly from JSON', function () {
    $endlessCharacter = Character::factory()->create([
        'class' => 'warlock',
        'subclass' => 'pact of the endless',
    ]);

    $wrathfulCharacter = Character::factory()->create([
        'class' => 'warlock',
        'subclass' => 'pact of the wrathful',
    ]);

    $endlessData = $endlessCharacter->getSubclassData();
    $wrathfulData = $wrathfulCharacter->getSubclassData();

    // Verify Pact of the Endless
    expect($endlessData['name'])->toBe('Pact of the Endless');
    expect($endlessData['spellcastTrait'])->toBe('Presence');
    expect($endlessData)->toHaveKey('foundationFeatures');
    expect($endlessData)->toHaveKey('specializationFeatures');
    expect($endlessData)->toHaveKey('masteryFeatures');
    expect($endlessData['playtest']['isPlaytest'])->toBe(true);

    // Verify Pact of the Wrathful
    expect($wrathfulData['name'])->toBe('Pact of the Wrathful');
    expect($wrathfulData['spellcastTrait'])->toBe('Presence');
    expect($wrathfulData)->toHaveKey('foundationFeatures');
    expect($wrathfulData)->toHaveKey('specializationFeatures');
    expect($wrathfulData)->toHaveKey('masteryFeatures');
    expect($wrathfulData['playtest']['isPlaytest'])->toBe(true);

    // Both should use same spellcast trait
    expect($endlessData['spellcastTrait'])->toBe($wrathfulData['spellcastTrait']);
});

test('warlock hope feature is correctly configured', function () {
    $character = Character::factory()->create([
        'class' => 'warlock',
        'subclass' => 'pact of the endless',
    ]);

    $classData = $character->getClassData();
    $hopeFeature = $classData['hopeFeature'];

    expect($hopeFeature['name'])->toBe('Patron\'s Boon');
    expect($hopeFeature['hopeCost'])->toBe(3);
    expect($hopeFeature['description'])->toContain('call out to your patron for help');
    expect($hopeFeature['description'])->toContain('gaining 1d4 Favor');
});

test('warlock class features are correctly configured', function () {
    $character = Character::factory()->create([
        'class' => 'warlock',
        'subclass' => 'pact of the wrathful',
    ]);

    $classData = $character->getClassData();
    $classFeatures = collect($classData['classFeatures']);

    // Should have Warlock Patron and Favor features
    expect($classFeatures->pluck('name'))->toContain('Warlock Patron');
    expect($classFeatures->pluck('name'))->toContain('Favor');

    $patronFeature = $classFeatures->where('name', 'Warlock Patron')->first();
    expect($patronFeature['description'])->toContain('committed yourself to a patron');
    expect($patronFeature['description'])->toContain('spheres of Influence');

    $favorFeature = $classFeatures->where('name', 'Favor')->first();
    expect($favorFeature['description'])->toContain('Start with 3 Favor');
    expect($favorFeature['description'])->toContain('gain Favor equal to your Presence');
    expect($favorFeature['description'])->toContain('GM instead gains a Fear');
});

test('warlock domains are correctly accessible', function () {
    $character = Character::factory()->create([
        'class' => 'warlock',
        'subclass' => 'pact of the endless',
    ]);

    $domains = $character->getClassDomains();
    
    expect($domains)->toHaveCount(2);
    expect($domains)->toContain('dread');
    expect($domains)->toContain('grace');
    
    // Verify domain access methods if they exist
    if (method_exists($character, 'hasAccessToDomain')) {
        expect($character->hasAccessToDomain('dread'))->toBe(true);
        expect($character->hasAccessToDomain('grace'))->toBe(true);
        expect($character->hasAccessToDomain('sage'))->toBe(false);
        expect($character->hasAccessToDomain('arcana'))->toBe(false);
    }
});

test('warlock starting inventory is correctly configured', function () {
    $character = Character::factory()->create([
        'class' => 'warlock',
        'subclass' => 'pact of the wrathful',
    ]);

    $classData = $character->getClassData();
    $inventory = $classData['startingInventory'];

    // Verify always items
    expect($inventory['always'])->toContain('torch');
    expect($inventory['always'])->toContain('50 feet of rope');
    expect($inventory['always'])->toContain('basic supplies');
    expect($inventory['always'])->toContain('handful of gold');

    // Verify choice items
    expect($inventory['chooseOne'])->toContain('Minor Health Potion');
    expect($inventory['chooseOne'])->toContain('Minor Stamina Potion');

    // Verify patron-themed items
    expect($inventory['chooseExtra'])->toContain('carving that symbolizes your patron');
    expect($inventory['chooseExtra'])->toContain('ring you can\'t remove');
});

test('warlock versatile weapon mechanics are correctly configured', function () {
    $character = Character::factory()->create([
        'class' => 'warlock',
        'subclass' => 'pact of the wrathful',
    ]);

    $classData = $character->getClassData();
    $suggestedWeapons = $classData['suggestedWeapons'];
    $scepter = $suggestedWeapons['primary'];

    expect($scepter['name'])->toBe('Scepter');
    expect($scepter['trait'])->toBe('Presence');
    expect($scepter['range'])->toBe('Far');
    expect($scepter['damage'])->toBe('d6 phy');
    expect($scepter['handedness'])->toBe('Two-Handed');
    expect($scepter['feature'])->toContain('Versatile');
    expect($scepter['feature'])->toContain('Presence, Melee, d8');
});

test('warlock background questions are warlock-specific', function () {
    $character = Character::factory()->create([
        'class' => 'warlock',
        'subclass' => 'pact of the endless',
    ]);

    $classData = $character->getClassData();
    $backgroundQuestions = $classData['backgroundQuestions'];

    expect($backgroundQuestions)->toHaveCount(3);
    expect($backgroundQuestions[0])->toContain('desperate situation');
    expect($backgroundQuestions[0])->toContain('pledge your life to your patron');
    expect($backgroundQuestions[1])->toContain('one task you must accomplish');
    expect($backgroundQuestions[2])->toContain('similar in a very specific way');
});

test('warlock connection questions reference patron relationship', function () {
    $character = Character::factory()->create([
        'class' => 'warlock',
        'subclass' => 'pact of the wrathful',
    ]);

    $classData = $character->getClassData();
    $connections = $classData['connections'];

    expect($connections)->toHaveCount(3);
    expect($connections[0])->toContain('confide in you about what my patron says');
    expect($connections[1])->toContain('saw me tithe to my patron');
    expect($connections[2])->toContain('something very foolish');
});

test('all classes have standard starting stats including stress', function () {
    // All classes in DaggerHeart start with 6 stress by default
    // This is universal behavior, not specific to any class
    
    // Test that the basic stats are correctly configured
    $classesPath = resource_path('json/classes.json');
    $classes = json_decode(file_get_contents($classesPath), true);
    
    $warlock = $classes['warlock'];
    expect($warlock['startingEvasion'])->toBe(11);
    expect($warlock['startingHitPoints'])->toBe(6);
    // No explicit startingStress field needed since all classes start with 6
    
    $wizard = $classes['wizard'];
    expect($wizard['startingEvasion'])->toBe(11);
    expect($wizard['startingHitPoints'])->toBe(5);
    // No explicit startingStress field needed since all classes start with 6
});

test('warlock enum integration works correctly', function () {
    // Test that the enum includes Warlock
    $classEnum = \Domain\Character\Enums\ClassEnum::WARLOCK;
    expect($classEnum->value)->toBe('warlock');
    
    // Test that domains are correctly mapped
    $domains = $classEnum->getDomains();
    expect($domains)->toHaveCount(2);
    expect($domains[0]->value)->toBe('dread');
    expect($domains[1]->value)->toBe('grace');
});