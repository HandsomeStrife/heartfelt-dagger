<?php

declare(strict_types=1);

test('brawler class is added to classes.json correctly', function () {
    $classesPath = resource_path('json/classes.json');
    expect($classesPath)->toBeFile();
    
    $classes = json_decode(file_get_contents($classesPath), true);
    expect($classes)->toHaveKey('brawler');
    
    $brawler = $classes['brawler'];
    expect($brawler['name'])->toBe('Brawler');
    expect($brawler['domains'])->toContain('bone');
    expect($brawler['domains'])->toContain('valor');
    expect($brawler['startingEvasion'])->toBe(10);
    expect($brawler['startingHitPoints'])->toBe(6);
    expect($brawler['playtest']['isPlaytest'])->toBe(true);
    expect($brawler['playtest']['version'])->toBe('1.5');
});

test('brawler subclasses are added to subclasses.json correctly', function () {
    $subclassesPath = resource_path('json/subclasses.json');
    expect($subclassesPath)->toBeFile();
    
    $subclasses = json_decode(file_get_contents($subclassesPath), true);
    expect($subclasses)->toHaveKey('juggernaut');
    expect($subclasses)->toHaveKey('martial artist');
    
    $juggernaut = $subclasses['juggernaut'];
    expect($juggernaut['name'])->toBe('Juggernaut');
    expect($juggernaut['playtest']['isPlaytest'])->toBe(true);
    expect($juggernaut['playtest']['version'])->toBe('1.5');
    
    $martialArtist = $subclasses['martial artist'];
    expect($martialArtist['name'])->toBe('Martial Artist');
    expect($martialArtist['playtest']['isPlaytest'])->toBe(true);
    expect($martialArtist['playtest']['version'])->toBe('1.5');
});

test('brawler enum is updated correctly', function () {
    $classEnum = \Domain\Character\Enums\ClassEnum::BRAWLER;
    expect($classEnum->value)->toBe('brawler');
    
    $domains = $classEnum->getDomains();
    expect($domains)->toHaveCount(2);
    expect($domains[0]->value)->toBe('bone');
    expect($domains[1]->value)->toBe('valor');
});

test('brawler class has correct hope feature', function () {
    $classesPath = resource_path('json/classes.json');
    $classes = json_decode(file_get_contents($classesPath), true);
    $brawler = $classes['brawler'];
    
    $hopeFeature = $brawler['hopeFeature'];
    expect($hopeFeature['name'])->toBe('Staggering Strike');
    expect($hopeFeature['hopeCost'])->toBe(3);
    expect($hopeFeature['description'])->toContain('temporarily Stagger');
    expect($hopeFeature['description'])->toContain('disadvantage on attack rolls');
});

test('brawler class features include unique mechanics', function () {
    $classesPath = resource_path('json/classes.json');
    $classes = json_decode(file_get_contents($classesPath), true);
    $brawler = $classes['brawler'];
    
    $classFeatures = collect($brawler['classFeatures']);
    expect($classFeatures->pluck('name'))->toContain('I Am the Weapon');
    expect($classFeatures->pluck('name'))->toContain('Combo Strikes');
    
    $weaponFeature = $classFeatures->where('name', 'I Am the Weapon')->first();
    expect($weaponFeature['description'])->toContain('+1 bonus to Evasion');
    expect($weaponFeature['description'])->toContain('d8+6 phy damage');
    
    $comboFeature = $classFeatures->where('name', 'Combo Strikes')->first();
    expect($comboFeature['description'])->toContain('mark a Stress to start a combo strike');
    expect($comboFeature['description'])->toContain('starts as a d4');
});

test('juggernaut subclass features are correctly structured', function () {
    $subclassesPath = resource_path('json/subclasses.json');
    $subclasses = json_decode(file_get_contents($subclassesPath), true);
    
    $juggernaut = $subclasses['juggernaut'];
    expect($juggernaut)->toHaveKey('foundationFeatures');
    expect($juggernaut)->toHaveKey('specializationFeatures');
    expect($juggernaut)->toHaveKey('masteryFeatures');
    
    // Check foundation features
    $foundationFeatures = collect($juggernaut['foundationFeatures']);
    expect($foundationFeatures->pluck('name'))->toContain('Powerhouse');
    
    $powerhouseFeature = $foundationFeatures->where('name', 'Powerhouse')->first();
    expect($powerhouseFeature['description'])->toContain('d8 damage dice for your unarmed attack to d10');
    expect($powerhouseFeature['description'])->toContain('target two creatures');
    
    // Check specialization features
    $specializationFeatures = collect($juggernaut['specializationFeatures']);
    expect($specializationFeatures->pluck('name'))->toContain('Rugged');
    
    // Check mastery features
    $masteryFeatures = collect($juggernaut['masteryFeatures']);
    expect($masteryFeatures->pluck('name'))->toContain('Pummeljoy');
    expect($masteryFeatures->pluck('name'))->toContain('Not Done Yet');
});

test('martial artist subclass features are correctly structured', function () {
    $subclassesPath = resource_path('json/subclasses.json');
    $subclasses = json_decode(file_get_contents($subclassesPath), true);
    
    $martialArtist = $subclasses['martial artist'];
    expect($martialArtist)->toHaveKey('foundationFeatures');
    expect($martialArtist)->toHaveKey('specializationFeatures');
    expect($martialArtist)->toHaveKey('masteryFeatures');
    
    // Check foundation features
    $foundationFeatures = collect($martialArtist['foundationFeatures']);
    expect($foundationFeatures->pluck('name'))->toContain('Martial Form');
    expect($foundationFeatures->pluck('name'))->toContain('Focus Mechanics');
    
    $focusFeature = $foundationFeatures->where('name', 'Focus Mechanics')->first();
    expect($focusFeature['description'])->toContain('roll a number of d6s equal to your Instinct');
    expect($focusFeature['description'])->toContain('highest value rolled');
    
    // Check specialization features
    $specializationFeatures = collect($martialArtist['specializationFeatures']);
    expect($specializationFeatures->pluck('name'))->toContain('Keen Defenses');
    expect($specializationFeatures->pluck('name'))->toContain('Spirit Blast');
    
    $spiritBlastFeature = $specializationFeatures->where('name', 'Spirit Blast')->first();
    expect($spiritBlastFeature['description'])->toContain('d20+3 magic damage');
    
    // Check mastery features
    $masteryFeatures = collect($martialArtist['masteryFeatures']);
    expect($masteryFeatures->pluck('name'))->toContain('Limit Breaker');
    
    $limitBreakerFeature = $masteryFeatures->where('name', 'Limit Breaker')->first();
    expect($limitBreakerFeature['description'])->toContain('unbelievable feat');
    expect($limitBreakerFeature['description'])->toContain('running across water');
});

test('brawler suggested equipment is correctly configured', function () {
    $classesPath = resource_path('json/classes.json');
    $classes = json_decode(file_get_contents($classesPath), true);
    $brawler = $classes['brawler'];
    
    $quarterstaff = $brawler['suggestedWeapons']['primary'];
    expect($quarterstaff['name'])->toBe('Quarterstaff');
    expect($quarterstaff['trait'])->toBe('Instinct');
    expect($quarterstaff['range'])->toBe('Melee');
    expect($quarterstaff['damage'])->toBe('d10+3 phy');
    expect($quarterstaff['handedness'])->toBe('Two-Handed');
    
    $armor = $brawler['suggestedArmor'];
    expect($armor['name'])->toBe('Leather Armor');
    expect($armor['thresholds'])->toBe('6/13');
    expect($armor['score'])->toBe(3);
});

test('brawler inventory includes martial arts themed items', function () {
    $classesPath = resource_path('json/classes.json');
    $classes = json_decode(file_get_contents($classesPath), true);
    $brawler = $classes['brawler'];
    
    $inventory = $brawler['startingInventory'];
    expect($inventory['always'])->toContain('torch');
    expect($inventory['always'])->toContain('50 feet of rope');
    expect($inventory['always'])->toContain('basic supplies');
    expect($inventory['always'])->toContain('handful of gold');
    
    expect($inventory['chooseOne'])->toContain('Minor Health Potion');
    expect($inventory['chooseOne'])->toContain('Minor Stamina Potion');
    
    expect($inventory['chooseExtra'])->toContain('hand wraps from a mentor');
    expect($inventory['chooseExtra'])->toContain('book about your secret hobby');
});

test('brawler background questions are martial arts focused', function () {
    $classesPath = resource_path('json/classes.json');
    $classes = json_decode(file_get_contents($classesPath), true);
    $brawler = $classes['brawler'];
    
    $backgroundQuestions = $brawler['backgroundQuestions'];
    expect($backgroundQuestions[0])->toContain('formative years that taught you');
    expect($backgroundQuestions[0])->toContain('how to fight in the style you use');
    expect($backgroundQuestions[1])->toContain('organization has always had your back');
    expect($backgroundQuestions[2])->toContain('lose a fight to long ago');
    expect($backgroundQuestions[2])->toContain('desperate for a rematch');
});

test('brawler suggested traits emphasize instinct', function () {
    $classesPath = resource_path('json/classes.json');
    $classes = json_decode(file_get_contents($classesPath), true);
    $brawler = $classes['brawler'];
    
    $suggestedTraits = $brawler['suggestedTraits'];
    expect($suggestedTraits['instinct'])->toBe(2); // Highest suggested trait
    expect($suggestedTraits['agility'])->toBe(1);
    expect($suggestedTraits['strength'])->toBe(1);
    expect($suggestedTraits['finesse'])->toBe(0);
    expect($suggestedTraits['presence'])->toBe(0);
    expect($suggestedTraits['knowledge'])->toBe(1);
});
