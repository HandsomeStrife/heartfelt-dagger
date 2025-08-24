<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

test('warlock pact of the endless has correct stats and features', function () {
    // Create a test character with Warlock class and Pact of the Endless subclass
    $character = Character::factory()->create([
        'class' => 'warlock',
        'subclass' => 'pact of the endless',
    ]);

    // Verify base stats
    expect($character->getBaseEvasion())->toBe(11);
    expect($character->getBaseHitPoints())->toBe(6);
    expect($character->getBaseStress())->toBe(6); // Unique to Warlock - starts with stress

    // Verify domains
    $domains = $character->getClassDomains();
    expect($domains)->toHaveCount(2);
    expect($domains)->toContain('dread');
    expect($domains)->toContain('grace');

    // Verify spellcast trait for Pact of the Endless subclass
    expect($character->getSpellcastTrait())->toBe('presence');

    // Verify class is marked as playtest
    $classData = $character->getClassData();
    expect($classData)->toHaveKey('playtest');
    expect($classData['playtest']['isPlaytest'])->toBe(true);
    expect($classData['playtest']['version'])->toBe('1.5');
    expect($classData['playtest']['label'])->toBe('Void - Playtest v1.5');

    // Verify subclass is marked as playtest
    $subclassData = $character->getSubclassData();
    expect($subclassData)->toHaveKey('playtest');
    expect($subclassData['playtest']['isPlaytest'])->toBe(true);
    expect($subclassData['playtest']['version'])->toBe('1.5');
    expect($subclassData['playtest']['label'])->toBe('Void - Playtest v1.5');
});

test('warlock pact of the endless features are correctly loaded', function () {
    $character = Character::factory()->create([
        'class' => 'warlock',
        'subclass' => 'pact of the endless',
    ]);

    $classData = $character->getClassData();
    $subclassData = $character->getSubclassData();

    // Verify class features
    $classFeatures = collect($classData['classFeatures']);
    expect($classFeatures->where('name', 'Warlock Patron'))->toHaveCount(1);
    expect($classFeatures->where('name', 'Favor'))->toHaveCount(1);

    $patronFeature = $classFeatures->where('name', 'Warlock Patron')->first();
    expect($patronFeature['description'])->toContain('committed yourself to a patron');
    expect($patronFeature['description'])->toContain('spheres of Influence');
    expect($patronFeature['description'])->toContain('spend a Favor to call on them');

    $favorFeature = $classFeatures->where('name', 'Favor')->first();
    expect($favorFeature['description'])->toContain('Start with 3 Favor');
    expect($favorFeature['description'])->toContain('gain Favor equal to your Presence');
    expect($favorFeature['description'])->toContain('GM instead gains a Fear');

    // Verify Hope feature
    $hopeFeature = $classData['hopeFeature'];
    expect($hopeFeature['name'])->toBe('Patron\'s Boon');
    expect($hopeFeature['hopeCost'])->toBe(3);
    expect($hopeFeature['description'])->toContain('gaining 1d4 Favor');

    // Verify Pact of the Endless foundation features
    $foundationFeatures = collect($subclassData['foundationFeatures']);
    expect($foundationFeatures->where('name', 'Patron\'s Mantle'))->toHaveCount(1);
    expect($foundationFeatures->where('name', 'Deathly Devotion'))->toHaveCount(1);

    $mantleFeature = $foundationFeatures->where('name', 'Patron\'s Mantle')->first();
    expect($mantleFeature['description'])->toContain('terrifying aspect of your Patron');
    expect($mantleFeature['description'])->toContain('spend 2 Favor instead');
    expect($mantleFeature['description'])->toContain('intimidate a target');

    $devotionFeature = $foundationFeatures->where('name', 'Deathly Devotion')->first();
    expect($devotionFeature['description'])->toContain('spend a Favor to gain a +1 bonus to your Evasion');

    // Verify Pact of the Endless specialization features
    $specializationFeatures = collect($subclassData['specializationFeatures']);
    expect($specializationFeatures->where('name', 'Draining Invocation'))->toHaveCount(1);

    $drainingFeature = $specializationFeatures->where('name', 'Draining Invocation')->first();
    expect($drainingFeature['description'])->toContain('use a d12 instead of a d20');
    expect($drainingFeature['description'])->toContain('adversary must mark a Stress');
    expect($drainingFeature['description'])->toContain('you can clear a Stress');

    // Verify Pact of the Endless mastery features
    $masteryFeatures = collect($subclassData['masteryFeatures']);
    expect($masteryFeatures->where('name', 'Dark Aegis'))->toHaveCount(1);
    expect($masteryFeatures->where('name', 'Draining Bane'))->toHaveCount(1);

    $aegisFeature = $masteryFeatures->where('name', 'Dark Aegis')->first();
    expect($aegisFeature['description'])->toContain('spend a Favor instead');

    $baneFeature = $masteryFeatures->where('name', 'Draining Bane')->first();
    expect($baneFeature['description'])->toContain('spend 2 Favor to temporarily Drain them');
    expect($baneFeature['description'])->toContain('d12 instead of a d20 for attack rolls');
});

test('warlock endless basic data structure is correct', function () {
    $character = Character::factory()->create([
        'class' => 'warlock',
        'subclass' => 'pact of the endless',
    ]);

    // Verify basic data structure
    expect($character->class)->toBe('warlock');
    expect($character->subclass)->toBe('pact of the endless');
    expect($character->getClassData())->toHaveKey('name');
    expect($character->getSubclassData())->toHaveKey('name');
});

test('warlock endless character stats calculate correctly', function () {
    $character = Character::factory()->create([
        'class' => 'warlock',
        'subclass' => 'pact of the endless',
        'agility' => 1,
        'strength' => -1,
        'finesse' => 0,
        'instinct' => 1,
        'presence' => 2,
        'knowledge' => 0,
    ]);

    // Test with suggested traits
    expect($character->getFinalEvasion())->toBe(11); // Base 11 + no modifiers
    expect($character->getFinalHitPoints())->toBe(6); // Base 6 + no modifiers
    expect($character->getFinalStress())->toBe(6); // Unique - starts with 6 stress
    expect($character->getHopeStart())->toBe(2); // Standard starting hope
    expect($character->getMaxDomainCards())->toBe(2); // Base domain cards

    // Verify spellcast trait value
    expect($character->getTraitValue('presence'))->toBe(2);
    expect($character->getSpellcastTraitValue())->toBe(2);
});

test('warlock class inventory and equipment suggestions are correct', function () {
    $character = Character::factory()->create([
        'class' => 'warlock',
        'subclass' => 'pact of the endless',
    ]);

    $classData = $character->getClassData();

    // Verify starting inventory
    $inventory = $classData['startingInventory'];
    
    expect($inventory['always'])->toContain('torch');
    expect($inventory['always'])->toContain('50 feet of rope');
    expect($inventory['always'])->toContain('basic supplies');
    expect($inventory['always'])->toContain('handful of gold');

    expect($inventory['chooseOne'])->toContain('Minor Health Potion');
    expect($inventory['chooseOne'])->toContain('Minor Stamina Potion');

    expect($inventory['chooseExtra'])->toContain('carving that symbolizes your patron');
    expect($inventory['chooseExtra'])->toContain('ring you can\'t remove');

    expect($inventory['special'])->toContain('patron\'s contract');
    expect($inventory['special'])->toContain('ritual components');

    // Verify suggested equipment
    $suggestedWeapons = $classData['suggestedWeapons'];
    expect($suggestedWeapons['primary']['name'])->toBe('Scepter');
    expect($suggestedWeapons['primary']['trait'])->toBe('Presence');
    expect($suggestedWeapons['primary']['range'])->toBe('Far');
    expect($suggestedWeapons['primary']['damage'])->toBe('d6 phy');
    expect($suggestedWeapons['primary']['handedness'])->toBe('Two-Handed');
    expect($suggestedWeapons['primary']['feature'])->toContain('Versatile');
    expect($suggestedWeapons['primary']['feature'])->toContain('Melee, d8');

    $suggestedArmor = $classData['suggestedArmor'];
    expect($suggestedArmor['name'])->toBe('Leather Armor');
    expect($suggestedArmor['thresholds'])->toBe('6/13');
    expect($suggestedArmor['score'])->toBe(3);
});

test('warlock endless background and connection questions are loaded', function () {
    $character = Character::factory()->create([
        'class' => 'warlock',
        'subclass' => 'pact of the endless',
    ]);

    $classData = $character->getClassData();

    // Verify background questions
    $backgroundQuestions = $classData['backgroundQuestions'];
    expect($backgroundQuestions)->toHaveCount(3);
    expect($backgroundQuestions[0])->toBe('What desperate situation led you to pledge your life to your patron?');
    expect($backgroundQuestions[1])->toBe('Your patron has given you one task you must accomplish above all else. What is it, and why does it worry you?');
    expect($backgroundQuestions[2])->toBe('You and your patron are similar in a very specific way. What characteristic do you share, and how do you feel about it?');

    // Verify connections
    $connections = $classData['connections'];
    expect($connections)->toHaveCount(3);
    expect($connections[0])->toBe('I confide in you about what my patron says and does. Why?');
    expect($connections[1])->toBe('You once saw me tithe to my patron and it\'s changed how you interact with me. What did you see and how has it affected you?');
    expect($connections[2])->toBe('I once did something very foolish, and you have never let me live it down. What was it?');
});

test('warlock favor system mechanics are correctly described', function () {
    $character = Character::factory()->create([
        'class' => 'warlock',
        'subclass' => 'pact of the endless',
    ]);

    $classData = $character->getClassData();
    $classFeatures = collect($classData['classFeatures']);
    
    // Verify Favor system mechanics
    $favorFeature = $classFeatures->where('name', 'Favor')->first();
    expect($favorFeature['description'])->toContain('Start with 3 Favor');
    expect($favorFeature['description'])->toContain('spend one of your downtime moves to tithe');
    expect($favorFeature['description'])->toContain('gain Favor equal to your Presence');
    expect($favorFeature['description'])->toContain('If you choose to forgo this offering');
    expect($favorFeature['description'])->toContain('the GM instead gains a Fear');

    // Verify Patron mechanics
    $patronFeature = $classFeatures->where('name', 'Warlock Patron')->first();
    expect($patronFeature['description'])->toContain('god, demon, fae, or other supernatural entity');
    expect($patronFeature['description'])->toContain('spheres of Influence');
    expect($patronFeature['description'])->toContain('Protection & Mischief, Love & War, Knowledge & Shadow');
    expect($patronFeature['description'])->toContain('set their values to 0');
    expect($patronFeature['description'])->toContain('permanent +1 bonus');
    expect($patronFeature['description'])->toContain('spend a Favor to call on them');
});

test('warlock unique starting stress is properly implemented', function () {
    $character = Character::factory()->create([
        'class' => 'warlock',
        'subclass' => 'pact of the endless',
    ]);

    // Warlock should be the only class that starts with stress
    expect($character->getBaseStress())->toBe(6);

    // Compare with other classes that start with 0 stress
    $otherClassCharacter = Character::factory()->create([
        'class' => 'wizard',
        'subclass' => 'school of knowledge',
    ]);
    expect($otherClassCharacter->getBaseStress())->toBe(0);
});
