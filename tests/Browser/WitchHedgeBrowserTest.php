<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

test('witch hedge subclass has correct stats and features', function () {
    // Create a test character with Witch class and Hedge subclass
    $character = Character::factory()->create([
        'selected_class' => 'witch',
        'selected_subclass' => 'hedge',
    ]);

    // Verify base stats
    expect($character->getBaseEvasion())->toBe(10);
    expect($character->getBaseHitPoints())->toBe(6);

    // Verify domains
    $domains = $character->getClassDomains();
    expect($domains)->toHaveCount(2);
    expect($domains)->toContain('dread');
    expect($domains)->toContain('sage');

    // Verify spellcast trait for Hedge subclass
    expect($character->getSpellcastTrait())->toBe('knowledge');

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

test('witch hedge subclass features are correctly loaded', function () {
    $character = Character::factory()->create([
        'selected_class' => 'witch',
        'selected_subclass' => 'hedge',
    ]);

    $classData = $character->getClassData();
    $subclassData = $character->getSubclassData();

    // Verify class features
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

    // Verify Hedge foundation features
    $foundationFeatures = collect($subclassData['foundationFeatures']);
    expect($foundationFeatures->where('name', 'Herbal Remedies'))->toHaveCount(1);
    expect($foundationFeatures->where('name', 'Tethered Talisman'))->toHaveCount(1);

    $herbalFeature = $foundationFeatures->where('name', 'Herbal Remedies')->first();
    expect($herbalFeature['description'])->toContain('increase the number cleared by one');

    $talismanFeature = $foundationFeatures->where('name', 'Tethered Talisman')->first();
    expect($talismanFeature['description'])->toContain('imbue a small item with your protective essence');

    // Verify Hedge specialization features
    $specializationFeatures = collect($subclassData['specializationFeatures']);
    expect($specializationFeatures->where('name', 'Walk Between Worlds'))->toHaveCount(1);
    expect($specializationFeatures->where('name', 'Enhanced Hex'))->toHaveCount(1);

    $walkFeature = $specializationFeatures->where('name', 'Walk Between Worlds')->first();
    expect($walkFeature['description'])->toContain('step beyond the veil of death');

    $enhancedHexFeature = $specializationFeatures->where('name', 'Enhanced Hex')->first();
    expect($enhancedHexFeature['description'])->toContain('gain a damage bonus equal to your Proficiency');

    // Verify Hedge mastery features
    $masteryFeatures = collect($subclassData['masteryFeatures']);
    expect($masteryFeatures->where('name', 'Circle of Power'))->toHaveCount(1);

    $circleFeature = $masteryFeatures->where('name', 'Circle of Power')->first();
    expect($circleFeature['description'])->toContain('mark a circle on the ground');
    expect($circleFeature['description'])->toContain('+4 bonus to your damage thresholds');
    expect($circleFeature['description'])->toContain('+2 bonus to your attack rolls');
    expect($circleFeature['description'])->toContain('+1 bonus to your Evasion');
});

test('witch hedge character builder integration works correctly', function () {
    $this->browse(function ($browser) {
        $browser->visit('/')
            ->assertSee('DaggerHeart Character Builder')
            ->click('[data-class="witch"]')
            ->waitForText('Witch')
            ->assertSee('Void - Playtest v1.5')
            ->assertSee('As a witch, you weave together the mysterious powers of earth, sky, and spirit')
            ->assertSee('Starting Evasion: 10')
            ->assertSee('Starting Hit Points: 6')
            ->assertSee('Domains: Dread, Sage')
            ->click('[data-subclass="hedge"]')
            ->waitForText('Hedge')
            ->assertSee('Play the Hedge if you want to craft protective charms')
            ->assertSee('Spellcast Trait: Knowledge')
            ->assertSee('Herbal Remedies')
            ->assertSee('Tethered Talisman')
            ->assertSee('Walk Between Worlds')
            ->assertSee('Enhanced Hex')
            ->assertSee('Circle of Power')
            ->assertSee('Void - Playtest v1.5');
    });
});

test('witch hedge character stats calculate correctly', function () {
    $character = Character::factory()->create([
        'selected_class' => 'witch',
        'selected_subclass' => 'hedge',
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

    // Verify spellcast trait value
    expect($character->getTraitValue('knowledge'))->toBe(1);
    expect($character->getSpellcastTraitValue())->toBe(1);
});

test('witch class inventory and equipment suggestions are correct', function () {
    $character = Character::factory()->create([
        'selected_class' => 'witch',
        'selected_subclass' => 'hedge',
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

    expect($inventory['chooseExtra'])->toContain('small harmless pet');
    expect($inventory['chooseExtra'])->toContain('talking skull');

    expect($inventory['special'])->toContain('handwritten journal');
    expect($inventory['special'])->toContain('runestones');

    // Verify suggested equipment
    $suggestedWeapons = $classData['suggestedWeapons'];
    expect($suggestedWeapons['primary']['name'])->toBe('Dualstaff');
    expect($suggestedWeapons['primary']['trait'])->toBe('Instinct');
    expect($suggestedWeapons['primary']['range'])->toBe('Far');
    expect($suggestedWeapons['primary']['damage'])->toBe('d6+3 mag');
    expect($suggestedWeapons['primary']['handedness'])->toBe('Two-Handed');

    $suggestedArmor = $classData['suggestedArmor'];
    expect($suggestedArmor['name'])->toBe('Gambeson Armor');
    expect($suggestedArmor['thresholds'])->toBe('5/11');
    expect($suggestedArmor['score'])->toBe(3);
    expect($suggestedArmor['feature'])->toBe('Flexible: +1 to Evasion');
});

test('witch hedge background and connection questions are loaded', function () {
    $character = Character::factory()->create([
        'selected_class' => 'witch',
        'selected_subclass' => 'hedge',
    ]);

    $classData = $character->getClassData();

    // Verify background questions
    $backgroundQuestions = $classData['backgroundQuestions'];
    expect($backgroundQuestions)->toHaveCount(3);
    expect($backgroundQuestions[0])->toBe('How did you first discover your affinity for magical craft?');
    expect($backgroundQuestions[1])->toBe('You once used your power to help someone in a dire situation. Who were they and why did they come to you?');
    expect($backgroundQuestions[2])->toBe('Your magic once opened a door best left closed. Who or what was on the other side?');

    // Verify connections
    $connections = $classData['connections'];
    expect($connections)->toHaveCount(3);
    expect($connections[0])->toBe('What about my magical practice makes you most ill at ease?');
    expect($connections[1])->toBe('I once appeared to you in a dream and shared a vision of the future. What did I tell you?');
    expect($connections[2])->toBe('Why do you come to me for advice?');
});
