<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

test('warlock pact of the wrathful has correct stats and features', function () {
    // Create a test character with Warlock class and Pact of the Wrathful subclass
    $character = Character::factory()->create([
        'class' => 'warlock',
        'subclass' => 'pact of the wrathful',
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

    // Verify spellcast trait for Pact of the Wrathful subclass
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

test('warlock pact of the wrathful features are correctly loaded', function () {
    $character = Character::factory()->create([
        'class' => 'warlock',
        'subclass' => 'pact of the wrathful',
    ]);

    $classData = $character->getClassData();
    $subclassData = $character->getSubclassData();

    // Verify class features (same as Endless)
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

    // Verify Pact of the Wrathful foundation features
    $foundationFeatures = collect($subclassData['foundationFeatures']);
    expect($foundationFeatures->where('name', 'Favored Weapon'))->toHaveCount(1);
    expect($foundationFeatures->where('name', 'Herald of Death'))->toHaveCount(1);

    $weaponFeature = $foundationFeatures->where('name', 'Favored Weapon')->first();
    expect($weaponFeature['description'])->toContain('cloak your weapon with your Patron\'s fury');
    expect($weaponFeature['description'])->toContain('spend any number of Favor');
    expect($weaponFeature['description'])->toContain('+1d6 damage bonus for each Favor spent');

    $heraldFeature = $foundationFeatures->where('name', 'Herald of Death')->first();
    expect($heraldFeature['description'])->toContain('spend a Favor to reroll it');
    expect($heraldFeature['description'])->toContain('mark a Stress and take the new result');

    // Verify Pact of the Wrathful specialization features
    $specializationFeatures = collect($subclassData['specializationFeatures']);
    expect($specializationFeatures->where('name', 'Menacing Reach'))->toHaveCount(1);
    expect($specializationFeatures->where('name', 'Diminish My Foes'))->toHaveCount(1);

    $reachFeature = $specializationFeatures->where('name', 'Menacing Reach')->first();
    expect($reachFeature['description'])->toContain('increase its range by one step');
    expect($reachFeature['description'])->toContain('Melee to Very Close, Very Close to Close');

    $diminishFeature = $specializationFeatures->where('name', 'Diminish My Foes')->first();
    expect($diminishFeature['description'])->toContain('succeed with Hope on an action roll');
    expect($diminishFeature['description'])->toContain('spend a Hope to make your target mark a Stress');

    // Verify Pact of the Wrathful mastery features
    $masteryFeatures = collect($subclassData['masteryFeatures']);
    expect($masteryFeatures->where('name', 'Fearsome Attack'))->toHaveCount(1);
    expect($masteryFeatures->where('name', 'Divine Ire'))->toHaveCount(1);

    $fearsomeFeature = $masteryFeatures->where('name', 'Fearsome Attack')->first();
    expect($fearsomeFeature['description'])->toContain('spend a Favor to reroll any number of your damage dice');
    expect($fearsomeFeature['description'])->toContain('continue spending Favor to reroll the same dice');

    $ireFeature = $masteryFeatures->where('name', 'Divine Ire')->first();
    expect($ireFeature['description'])->toContain('spend any number of Favor');
    expect($ireFeature['description'])->toContain('deal that many Hit Points to adversaries');
});

test('warlock wrathful basic data structure is correct', function () {
    $character = Character::factory()->create([
        'class' => 'warlock',
        'subclass' => 'pact of the wrathful',
    ]);

    // Verify basic data structure
    expect($character->class)->toBe('warlock');
    expect($character->subclass)->toBe('pact of the wrathful');
    expect($character->getClassData())->toHaveKey('name');
    expect($character->getSubclassData())->toHaveKey('name');
});

test('warlock wrathful character stats calculate correctly', function () {
    $character = Character::factory()->create([
        'class' => 'warlock',
        'subclass' => 'pact of the wrathful',
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

    // Verify spellcast trait value for Wrathful subclass
    expect($character->getTraitValue('presence'))->toBe(2);
    expect($character->getSpellcastTraitValue())->toBe(2);
});

test('warlock wrathful subclass description and spellcast trait are correct', function () {
    $character = Character::factory()->create([
        'class' => 'warlock',
        'subclass' => 'pact of the wrathful',
    ]);

    $subclassData = $character->getSubclassData();

    // Verify Wrathful subclass description
    expect($subclassData['description'])->toBe('Play the Pact of the Wrathful if you want to enhance your weapon attacks with your patron\'s fury.');

    // Verify spellcast trait
    expect($subclassData['spellcastTrait'])->toBe('Presence');
});

test('warlock wrathful weapon enhancement mechanics', function () {
    $character = Character::factory()->create([
        'class' => 'warlock',
        'subclass' => 'pact of the wrathful',
    ]);

    $subclassData = $character->getSubclassData();
    $foundationFeatures = collect($subclassData['foundationFeatures']);
    $weaponFeature = $foundationFeatures->where('name', 'Favored Weapon')->first();

    // Verify detailed weapon enhancement mechanics
    expect($weaponFeature['description'])->toContain('Mark a Stress to cloak your weapon');
    expect($weaponFeature['description'])->toContain('with your Patron\'s fury');
    expect($weaponFeature['description'])->toContain('until you deal Severe damage');
    expect($weaponFeature['description'])->toContain('successful melee weapon attack');
    expect($weaponFeature['description'])->toContain('spend any number of Favor');
    expect($weaponFeature['description'])->toContain('+1d6 damage bonus for each Favor spent');
});

test('warlock class features are consistent across both subclasses', function () {
    $endlessCharacter = Character::factory()->create([
        'class' => 'warlock',
        'subclass' => 'pact of the endless',
    ]);

    $wrathfulCharacter = Character::factory()->create([
        'class' => 'warlock',
        'subclass' => 'pact of the wrathful',
    ]);

    // Both should have the same class features
    $endlessClassFeatures = $endlessCharacter->getClassData()['classFeatures'];
    $wrathfulClassFeatures = $wrathfulCharacter->getClassData()['classFeatures'];

    expect($endlessClassFeatures)->toEqual($wrathfulClassFeatures);

    // Both should have the same Hope feature
    $endlessHopeFeature = $endlessCharacter->getClassData()['hopeFeature'];
    $wrathfulHopeFeature = $wrathfulCharacter->getClassData()['hopeFeature'];

    expect($endlessHopeFeature)->toEqual($wrathfulHopeFeature);

    // Both should have the same domains
    expect($endlessCharacter->getClassDomains())->toEqual($wrathfulCharacter->getClassDomains());

    // Both should have the same base stats
    expect($endlessCharacter->getBaseEvasion())->toBe($wrathfulCharacter->getBaseEvasion());
    expect($endlessCharacter->getBaseHitPoints())->toBe($wrathfulCharacter->getBaseHitPoints());
    expect($endlessCharacter->getBaseStress())->toBe($wrathfulCharacter->getBaseStress());

    // Both should use Presence as spellcast trait
    expect($endlessCharacter->getSpellcastTrait())->toBe($wrathfulCharacter->getSpellcastTrait());
    expect($endlessCharacter->getSpellcastTrait())->toBe('presence');
});

test('warlock versatile weapon mechanics are correctly described', function () {
    $character = Character::factory()->create([
        'class' => 'warlock',
        'subclass' => 'pact of the wrathful',
    ]);

    $classData = $character->getClassData();
    $suggestedWeapons = $classData['suggestedWeapons'];
    $scepter = $suggestedWeapons['primary'];

    // Verify Scepter versatile mechanics
    expect($scepter['name'])->toBe('Scepter');
    expect($scepter['trait'])->toBe('Presence');
    expect($scepter['range'])->toBe('Far');
    expect($scepter['damage'])->toBe('d6 phy');
    expect($scepter['handedness'])->toBe('Two-Handed');
    expect($scepter['feature'])->toContain('Versatile');
    expect($scepter['feature'])->toContain('Presence, Melee, d8');
    
    // This means the weapon can be used as:
    // - Far range, Two-Handed, d6 damage OR
    // - Melee range, (implied One-Handed), d8 damage
});

test('warlock domain access is correct for dread and grace', function () {
    $character = Character::factory()->create([
        'class' => 'warlock',
        'subclass' => 'pact of the wrathful',
    ]);

    // Verify Warlock has access to Dread and Grace domains
    $domains = $character->getClassDomains();
    expect($domains)->toHaveCount(2);
    expect($domains)->toContain('dread');
    expect($domains)->toContain('grace');

    // Both subclasses should have the same domain access since it's class-based
    expect($character->hasAccessToDomain('dread'))->toBe(true);
    expect($character->hasAccessToDomain('grace'))->toBe(true);
    expect($character->hasAccessToDomain('sage'))->toBe(false);
    expect($character->hasAccessToDomain('arcana'))->toBe(false);
});

test('warlock favor system has correct starting values', function () {
    $character = Character::factory()->create([
        'class' => 'warlock',
        'subclass' => 'pact of the wrathful',
    ]);

    $classData = $character->getClassData();
    $classFeatures = collect($classData['classFeatures']);
    $favorFeature = $classFeatures->where('name', 'Favor')->first();

    // Verify Favor starting amount and mechanics
    expect($favorFeature['description'])->toContain('Start with 3 Favor');
    expect($favorFeature['description'])->toContain('During a rest');
    expect($favorFeature['description'])->toContain('spend one of your downtime moves');
    expect($favorFeature['description'])->toContain('tithe to your patron');
    expect($favorFeature['description'])->toContain('gain Favor equal to your Presence');
    expect($favorFeature['description'])->toContain('forgo this offering');
    expect($favorFeature['description'])->toContain('GM instead gains a Fear');
});
