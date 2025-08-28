<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

test('witch moon subclass has correct stats and features', function () {
    // Create a test character with Witch class and Moon subclass
    $character = Character::factory()->create([
        'selected_class' => 'witch',
        'selected_subclass' => 'moon',
    ]);

    // Verify base stats
    expect($character->getBaseEvasion())->toBe(10);
    expect($character->getBaseHitPoints())->toBe(6);

    // Verify domains
    $domains = $character->getClassDomains();
    expect($domains)->toHaveCount(2);
    expect($domains)->toContain('dread');
    expect($domains)->toContain('sage');

    // Verify spellcast trait for Moon subclass
    expect($character->getSpellcastTrait())->toBe('instinct');

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

test('witch moon subclass features are correctly loaded', function () {
    $character = Character::factory()->create([
        'selected_class' => 'witch',
        'selected_subclass' => 'moon',
    ]);

    $classData = $character->getClassData();
    $subclassData = $character->getSubclassData();

    // Verify class features (same as Hedge)
    $classFeatures = collect($classData['classFeatures']);
    expect($classFeatures->where('name', 'Hex'))->toHaveCount(1);
    expect($classFeatures->where('name', 'Commune'))->toHaveCount(1);

    $hexFeature = $classFeatures->where('name', 'Hex')->first();
    expect($hexFeature['description'])->toContain('mark a Stress to Hex them');
    expect($hexFeature['description'])->toContain('bonus equal to your tier');

    $communeFeature = $classFeatures->where('name', 'Commune')->first();
    expect($communeFeature['description'])->toContain('commune with an ancestor, deity, nature spirit');
    expect($communeFeature['description'])->toContain('roll a number of d6s equal to your Spellcast trait');

    // Verify Hope feature
    $hopeFeature = $classData['hopeFeature'];
    expect($hopeFeature['name'])->toBe('Witch\'s Charm');
    expect($hopeFeature['hopeCost'])->toBe(3);
    expect($hopeFeature['description'])->toContain('spend 3 Hope to change it into a success with Fear');

    // Verify Moon foundation features
    $foundationFeatures = collect($subclassData['foundationFeatures']);
    expect($foundationFeatures->where('name', 'Night\'s Glamour'))->toHaveCount(1);

    $glamourFeature = $foundationFeatures->where('name', 'Night\'s Glamour')->first();
    expect($glamourFeature['description'])->toContain('Mark a Stress to Glamour yourself');
    expect($glamourFeature['description'])->toContain('Disguise yourself to look like any creature');
    expect($glamourFeature['description'])->toContain('advantage on Presence Rolls');

    // Verify Moon specialization features
    $specializationFeatures = collect($subclassData['specializationFeatures']);
    expect($specializationFeatures->where('name', 'Moonbeam'))->toHaveCount(1);
    expect($specializationFeatures->where('name', 'Ire of Pale Light'))->toHaveCount(1);

    $moonbeamFeature = $specializationFeatures->where('name', 'Moonbeam')->first();
    expect($moonbeamFeature['description'])->toContain('conjure a column of moonlight');
    expect($moonbeamFeature['description'])->toContain('+1 bonus to Spellcast Rolls');
    expect($moonbeamFeature['description'])->toContain('advantage on rolls to see through illusions');

    $ireFeature = $specializationFeatures->where('name', 'Ire of Pale Light')->first();
    expect($ireFeature['description'])->toContain('When a Hexed creature within Far range fails an attack roll');
    expect($ireFeature['description'])->toContain('they must mark a Stress');

    // Verify Moon mastery features
    $masteryFeatures = collect($subclassData['masteryFeatures']);
    expect($masteryFeatures->where('name', 'Lunar Phases'))->toHaveCount(1);

    $lunarFeature = $masteryFeatures->where('name', 'Lunar Phases')->first();
    expect($lunarFeature['description'])->toContain('Your spirit ebbs and flows like the phases of the moon');
    expect($lunarFeature['description'])->toContain('roll a d4 and gain the matching effect');
    expect($lunarFeature['description'])->toContain('1: New - You can always spend a Hope to reduce Minor damage to None');
    expect($lunarFeature['description'])->toContain('2: Waxing - Gain a +2 bonus to your damage rolls');
    expect($lunarFeature['description'])->toContain('3: Full - Gain a +2 bonus to your damage thresholds');
    expect($lunarFeature['description'])->toContain('4: Waning - Gain a +1 bonus to your Evasion');
});

test('witch moon character builder integration works correctly', function () {
    browse(function ($browser) {
        $browser->visit('/')
            ->assertSee('DaggerHeart Character Builder')
            ->click('[data-class="witch"]')
            ->waitForText('Witch')
            ->assertSee('Void - Playtest v1.5')
            ->assertSee('As a witch, you weave together the mysterious powers of earth, sky, and spirit')
            ->assertSee('Starting Evasion: 10')
            ->assertSee('Starting Hit Points: 6')
            ->assertSee('Domains: Dread, Sage')
            ->click('[data-subclass="moon"]')
            ->waitForText('Moon')
            ->assertSee('Play the Moon if you want to use illusion and lunar magic')
            ->assertSee('Spellcast Trait: Instinct')
            ->assertSee('Night\'s Glamour')
            ->assertSee('Moonbeam')
            ->assertSee('Ire of Pale Light')
            ->assertSee('Lunar Phases')
            ->assertSee('Void - Playtest v1.5');
});

test('witch moon character stats calculate correctly', function () {
    $character = Character::factory()->create([
        'selected_class' => 'witch',
        'selected_subclass' => 'moon',
        'agility' => 0,
        'strength' => -1,
        'finesse' => 0,
        'instinct' => 2,
        'presence' => 1,
        'knowledge' => 1,
    ]);

    // Test with suggested traits
    expect($character->getFinalEvasion())->toBe(10); // Base 10 + no modifiers
    expect($character->getFinalHitPoints())->toBe(6); // Base 6 + no modifiers
    expect($character->getFinalStress())->toBe(0); // No stress bonuses
    expect($character->getHopeStart())->toBe(2); // Standard starting hope
    expect($character->getMaxDomainCards())->toBe(2); // Base domain cards

    // Verify spellcast trait value for Moon subclass
    expect($character->getTraitValue('instinct'))->toBe(2);
    expect($character->getSpellcastTraitValue())->toBe(2);
});

test('witch moon subclass description and spellcast trait are correct', function () {
    $character = Character::factory()->create([
        'selected_class' => 'witch',
        'selected_subclass' => 'moon',
    ]);

    $subclassData = $character->getSubclassData();

    // Verify Moon subclass description
    expect($subclassData['description'])->toBe('Play the Moon if you want to use illusion and lunar magic to confound your enemies.');

    // Verify spellcast trait
    expect($subclassData['spellcastTrait'])->toBe('Instinct');
});

test('witch moon domain access is correct', function () {
    $character = Character::factory()->create([
        'selected_class' => 'witch',
        'selected_subclass' => 'moon',
    ]);

    // Verify Witch has access to Dread and Sage domains
    $domains = $character->getClassDomains();
    expect($domains)->toHaveCount(2);
    expect($domains)->toContain('dread');
    expect($domains)->toContain('sage');

    // Both subclasses should have the same domain access since it's class-based
    expect($character->hasAccessToDomain('dread'))->toBe(true);
    expect($character->hasAccessToDomain('sage'))->toBe(true);
    expect($character->hasAccessToDomain('arcana'))->toBe(false);
    expect($character->hasAccessToDomain('blade'))->toBe(false);
});

test('witch class features are consistent across both subclasses', function () {
    $hedgeCharacter = Character::factory()->create([
        'selected_class' => 'witch',
        'selected_subclass' => 'hedge',
    ]);

    $moonCharacter = Character::factory()->create([
        'selected_class' => 'witch',
        'selected_subclass' => 'moon',
    ]);

    // Both should have the same class features
    $hedgeClassFeatures = $hedgeCharacter->getClassData()['classFeatures'];
    $moonClassFeatures = $moonCharacter->getClassData()['classFeatures'];

    expect($hedgeClassFeatures)->toEqual($moonClassFeatures);

    // Both should have the same Hope feature
    $hedgeHopeFeature = $hedgeCharacter->getClassData()['hopeFeature'];
    $moonHopeFeature = $moonCharacter->getClassData()['hopeFeature'];

    expect($hedgeHopeFeature)->toEqual($moonHopeFeature);

    // Both should have the same domains
    expect($hedgeCharacter->getClassDomains())->toEqual($moonCharacter->getClassDomains());

    // Both should have the same base stats
    expect($hedgeCharacter->getBaseEvasion())->toBe($moonCharacter->getBaseEvasion());
    expect($hedgeCharacter->getBaseHitPoints())->toBe($moonCharacter->getBaseHitPoints());
});

test('witch moon glamour feature mechanics description', function () {
    $character = Character::factory()->create([
        'selected_class' => 'witch',
        'selected_subclass' => 'moon',
    ]);

    $subclassData = $character->getSubclassData();
    $foundationFeatures = collect($subclassData['foundationFeatures']);
    $glamourFeature = $foundationFeatures->where('name', 'Night\'s Glamour')->first();

    // Verify detailed mechanics
    expect($glamourFeature['description'])->toContain('Mark a Stress to Glamour yourself');
    expect($glamourFeature['description'])->toContain('magical facade that lasts until you mark a Hit Point');
    expect($glamourFeature['description'])->toContain('make an attack, or take a rest');
    expect($glamourFeature['description'])->toContain('Disguise yourself to look like any creature');
    expect($glamourFeature['description'])->toContain('approximate size that you\'ve seen');
    expect($glamourFeature['description'])->toContain('Enhance your own appearance');
    expect($glamourFeature['description'])->toContain('advantage on Presence Rolls that leverage this change');
});