<?php

declare(strict_types=1);

test('brawler class data structure is valid', function () {
    // Load the JSON data directly
    $classesPath = resource_path('json/classes.json');
    $subclassesPath = resource_path('json/subclasses.json');
    
    $classes = json_decode(file_get_contents($classesPath), true);
    $subclasses = json_decode(file_get_contents($subclassesPath), true);

    // Verify brawler class data exists and is correct
    expect($classes)->toHaveKey('brawler');
    $brawlerData = $classes['brawler'];
    
    // Verify base stats - balanced combat specialist
    expect($brawlerData['startingEvasion'])->toBe(10);
    expect($brawlerData['startingHitPoints'])->toBe(6);
    expect($brawlerData['domains'])->toContain('bone');
    expect($brawlerData['domains'])->toContain('valor');
    
    // Verify class is marked as playtest
    expect($brawlerData['playtest']['isPlaytest'])->toBe(true);
    expect($brawlerData['playtest']['version'])->toBe('1.5');
    expect($brawlerData['playtest']['label'])->toBe('Void - Playtest v1.5');

    // Verify both subclasses exist
    expect($subclasses)->toHaveKey('juggernaut');
    expect($subclasses)->toHaveKey('martial artist');
    
    $juggernautData = $subclasses['juggernaut'];
    $martialArtistData = $subclasses['martial artist'];
    
    expect($juggernautData['playtest']['version'])->toBe('1.5');
    expect($martialArtistData['playtest']['version'])->toBe('1.5');
});

test('brawler class features implement unique unarmed combat', function () {
    $classesPath = resource_path('json/classes.json');
    $classes = json_decode(file_get_contents($classesPath), true);
    $brawlerData = $classes['brawler'];

    $classFeatures = collect($brawlerData['classFeatures']);

    // Verify core class features
    expect($classFeatures->pluck('name'))->toContain('I Am the Weapon');
    expect($classFeatures->pluck('name'))->toContain('Combo Strikes');

    // Verify "I Am the Weapon" - unarmed combat mastery
    $iAmTheWeapon = $classFeatures->where('name', 'I Am the Weapon')->first();
    expect($iAmTheWeapon['description'])->toContain('d8+6 phy damage');
    expect($iAmTheWeapon['description'])->toContain('+1 bonus to Evasion');

    // Verify "Combo Strikes" - escalating damage system
    $comboStrikes = $classFeatures->where('name', 'Combo Strikes')->first();
    expect($comboStrikes['description'])->toContain('starts as a d4');
    expect($comboStrikes['description'])->toContain('mark a Stress to start a combo strike');

    // Verify hope feature
    $hopeFeature = $brawlerData['hopeFeature'];
    expect($hopeFeature['name'])->toBe('Staggering Strike');
    expect($hopeFeature['hopeCost'])->toBe(3);
});

test('juggernaut features enhance raw power and multi-target combat', function () {
    $subclassesPath = resource_path('json/subclasses.json');
    $subclasses = json_decode(file_get_contents($subclassesPath), true);
    $juggernautData = $subclasses['juggernaut'];

    // Foundation: Enhanced damage and 2-target attacks
    $foundationFeatures = collect($juggernautData['foundationFeatures']);
    expect($foundationFeatures->pluck('name'))->toContain('Powerhouse');

    $powerhouse = $foundationFeatures->where('name', 'Powerhouse')->first();
    expect($powerhouse['description'])->toContain('d8 damage dice for your unarmed attack to d10');
    expect($powerhouse['description'])->toContain('target two creatures');

    // Specialization: Enhanced survivability and 3-target attacks
    $specializationFeatures = collect($juggernautData['specializationFeatures']);
    expect($specializationFeatures->pluck('name'))->toContain('Rugged');

    $rugged = $specializationFeatures->where('name', 'Rugged')->first();
    expect($rugged['description'])->toContain('+3 bonus to your Severe damage threshold');
    expect($rugged['description'])->toContain('three creatures instead of two');

    // Mastery: Combat excellence and recovery
    $masteryFeatures = collect($juggernautData['masteryFeatures']);
    expect($masteryFeatures->pluck('name'))->toContain('Pummeljoy');
    expect($masteryFeatures->pluck('name'))->toContain('Not Done Yet');
});

test('martial artist features implement stance system and focus mechanics', function () {
    $subclassesPath = resource_path('json/subclasses.json');
    $subclasses = json_decode(file_get_contents($subclassesPath), true);
    $martialArtistData = $subclasses['martial artist'];

    // Foundation: Stance system and Focus resource
    $foundationFeatures = collect($martialArtistData['foundationFeatures']);
    expect($foundationFeatures->pluck('name'))->toContain('Martial Form');
    expect($foundationFeatures->pluck('name'))->toContain('Focus Mechanics');

    $martialForm = $foundationFeatures->where('name', 'Martial Form')->first();
    expect($martialForm['description'])->toContain('16 different stances across 4 tiers');

    $focusMechanics = $foundationFeatures->where('name', 'Focus Mechanics')->first();
    expect($focusMechanics['description'])->toContain('roll a number of d6s equal to your Instinct');
    expect($focusMechanics['description'])->toContain('Spend a Focus to shift into a stance');

    // Specialization: Tactical options
    $specializationFeatures = collect($martialArtistData['specializationFeatures']);
    expect($specializationFeatures->pluck('name'))->toContain('Keen Defenses');
    expect($specializationFeatures->pluck('name'))->toContain('Spirit Blast');

    $spiritBlast = $specializationFeatures->where('name', 'Spirit Blast')->first();
    expect($spiritBlast['description'])->toContain('d20+3 magic damage');

    // Mastery: Supernatural abilities
    $masteryFeatures = collect($martialArtistData['masteryFeatures']);
    expect($masteryFeatures->pluck('name'))->toContain('Limit Breaker');

    $limitBreaker = $masteryFeatures->where('name', 'Limit Breaker')->first();
    expect($limitBreaker['description'])->toContain('running across water');
    expect($limitBreaker['description'])->toContain('unbelievable feat');
});

test('brawler equipment and traits support instinct-based combat', function () {
    $classesPath = resource_path('json/classes.json');
    $classes = json_decode(file_get_contents($classesPath), true);
    $brawlerData = $classes['brawler'];
    
    // Instinct-focused traits (for Focus generation)
    $suggestedTraits = $brawlerData['suggestedTraits'];
    expect($suggestedTraits['instinct'])->toBe(2); // Highest suggested trait

    // Versatile weapon suggestion
    $primaryWeapon = $brawlerData['suggestedWeapons']['primary'];
    expect($primaryWeapon['name'])->toBe('Quarterstaff');
    expect($primaryWeapon['trait'])->toBe('Instinct');

    // Martial arts themed inventory
    $inventory = $brawlerData['startingInventory'];
    expect($inventory['chooseExtra'])->toContain('hand wraps from a mentor');
    expect($inventory['chooseExtra'])->toContain('book about your secret hobby');
});

test('both brawler subclasses share core identity but differ in approach', function () {
    $subclassesPath = resource_path('json/subclasses.json');
    $subclasses = json_decode(file_get_contents($subclassesPath), true);

    $juggernautData = $subclasses['juggernaut'];
    $martialArtistData = $subclasses['martial artist'];

    // Both are playtest content
    expect($juggernautData['playtest']['version'])->toBe('1.5');
    expect($martialArtistData['playtest']['version'])->toBe('1.5');

    // Juggernaut focuses on raw power and multi-target
    $juggernautFoundation = collect($juggernautData['foundationFeatures']);
    expect($juggernautFoundation->pluck('name'))->toContain('Powerhouse');

    // Martial Artist focuses on tactical flexibility and resource management
    $martialArtistFoundation = collect($martialArtistData['foundationFeatures']);
    expect($martialArtistFoundation->pluck('name'))->toContain('Martial Form');
    expect($martialArtistFoundation->pluck('name'))->toContain('Focus Mechanics');
});

test('brawler background questions focus on martial training theme', function () {
    $classesPath = resource_path('json/classes.json');
    $classes = json_decode(file_get_contents($classesPath), true);
    $brawlerData = $classes['brawler'];

    $backgroundQuestions = $brawlerData['backgroundQuestions'];
    expect($backgroundQuestions)->toHaveCount(3);
    expect($backgroundQuestions[0])->toContain('fight in the style you use');
    expect($backgroundQuestions[1])->toContain('organization has always had your back');
    expect($backgroundQuestions[2])->toContain('desperate for a rematch');

    $connections = $brawlerData['connections'];
    expect($connections[0])->toContain('both afraid of');
    expect($connections[1])->toContain('rely on you for something important');
});
