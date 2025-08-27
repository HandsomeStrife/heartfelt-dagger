<?php

declare(strict_types=1);

test('warlock class is added to classes.json correctly', function () {
    $classesPath = resource_path('json/classes.json');
    expect($classesPath)->toBeFile();
    
    $classes = json_decode(file_get_contents($classesPath), true);
    expect($classes)->toHaveKey('warlock');
    
    $warlock = $classes['warlock'];
    expect($warlock['name'])->toBe('Warlock');
    expect($warlock['domains'])->toContain('dread');
    expect($warlock['domains'])->toContain('grace');
    expect($warlock['startingEvasion'])->toBe(11);
    expect($warlock['startingHitPoints'])->toBe(6);
    // All classes start with 6 stress by default - no need for explicit field
    expect($warlock['playtest']['isPlaytest'])->toBe(true);
    expect($warlock['playtest']['version'])->toBe('1.5');
});

test('warlock subclasses are added to subclasses.json correctly', function () {
    $subclassesPath = resource_path('json/subclasses.json');
    expect($subclassesPath)->toBeFile();
    
    $subclasses = json_decode(file_get_contents($subclassesPath), true);
    expect($subclasses)->toHaveKey('pact of the endless');
    expect($subclasses)->toHaveKey('pact of the wrathful');
    
    $endless = $subclasses['pact of the endless'];
    expect($endless['name'])->toBe('Pact of the Endless');
    expect($endless['spellcastTrait'])->toBe('Presence');
    expect($endless['playtest']['isPlaytest'])->toBe(true);
    expect($endless['playtest']['version'])->toBe('1.5');
    
    $wrathful = $subclasses['pact of the wrathful'];
    expect($wrathful['name'])->toBe('Pact of the Wrathful');
    expect($wrathful['spellcastTrait'])->toBe('Presence');
    expect($wrathful['playtest']['isPlaytest'])->toBe(true);
    expect($wrathful['playtest']['version'])->toBe('1.5');
});

test('warlock enum is updated correctly', function () {
    $classEnum = \Domain\Character\Enums\ClassEnum::WARLOCK;
    expect($classEnum->value)->toBe('warlock');
    
    $domains = $classEnum->getDomains();
    expect($domains)->toHaveCount(2);
    expect($domains[0]->value)->toBe('dread');
    expect($domains[1]->value)->toBe('grace');
});

test('warlock class has correct hope feature', function () {
    $classesPath = resource_path('json/classes.json');
    $classes = json_decode(file_get_contents($classesPath), true);
    $warlock = $classes['warlock'];
    
    $hopeFeature = $warlock['hopeFeature'];
    expect($hopeFeature['name'])->toBe('Patron\'s Boon');
    expect($hopeFeature['hopeCost'])->toBe(3);
    expect($hopeFeature['description'])->toContain('gaining 1d4 Favor');
});

test('warlock class features include patron and favor mechanics', function () {
    $classesPath = resource_path('json/classes.json');
    $classes = json_decode(file_get_contents($classesPath), true);
    $warlock = $classes['warlock'];
    
    $classFeatures = collect($warlock['classFeatures']);
    expect($classFeatures->pluck('name'))->toContain('Warlock Patron');
    expect($classFeatures->pluck('name'))->toContain('Favor');
    
    $patronFeature = $classFeatures->where('name', 'Warlock Patron')->first();
    expect($patronFeature['description'])->toContain('spheres of Influence');
    
    $favorFeature = $classFeatures->where('name', 'Favor')->first();
    expect($favorFeature['description'])->toContain('Start with 3 Favor');
});

test('warlock subclass features are correctly structured', function () {
    $subclassesPath = resource_path('json/subclasses.json');
    $subclasses = json_decode(file_get_contents($subclassesPath), true);
    
    $endless = $subclasses['pact of the endless'];
    expect($endless)->toHaveKey('foundationFeatures');
    expect($endless)->toHaveKey('specializationFeatures');  
    expect($endless)->toHaveKey('masteryFeatures');
    
    // Check foundation features
    $foundationFeatures = collect($endless['foundationFeatures']);
    expect($foundationFeatures->pluck('name'))->toContain('Patron\'s Mantle');
    expect($foundationFeatures->pluck('name'))->toContain('Deathly Devotion');
    
    $wrathful = $subclasses['pact of the wrathful'];
    $foundationFeatures = collect($wrathful['foundationFeatures']);
    expect($foundationFeatures->pluck('name'))->toContain('Favored Weapon');
    expect($foundationFeatures->pluck('name'))->toContain('Herald of Death');
});

test('warlock has versatile weapon correctly configured', function () {
    $classesPath = resource_path('json/classes.json');
    $classes = json_decode(file_get_contents($classesPath), true);
    $warlock = $classes['warlock'];
    
    $scepter = $warlock['suggestedWeapons']['primary'];
    expect($scepter['name'])->toBe('Scepter');
    expect($scepter['trait'])->toBe('Presence');
    expect($scepter['range'])->toBe('Far');
    expect($scepter['damage'])->toBe('d6 phy');
    expect($scepter['feature'])->toContain('Versatile');
    expect($scepter['feature'])->toContain('Melee, d8');
});

test('warlock inventory includes patron-themed items', function () {
    $classesPath = resource_path('json/classes.json');
    $classes = json_decode(file_get_contents($classesPath), true);
    $warlock = $classes['warlock'];
    
    $inventory = $warlock['startingInventory'];
    expect($inventory['chooseExtra'])->toContain('carving that symbolizes your patron');
    expect($inventory['chooseExtra'])->toContain('ring you can\'t remove');
    expect($inventory['special'])->toContain('patron\'s contract');
    expect($inventory['special'])->toContain('ritual components');
});

test('warlock background questions are patron-focused', function () {
    $classesPath = resource_path('json/classes.json');
    $classes = json_decode(file_get_contents($classesPath), true);
    $warlock = $classes['warlock'];
    
    $backgroundQuestions = $warlock['backgroundQuestions'];
    expect($backgroundQuestions[0])->toContain('desperate situation');
    expect($backgroundQuestions[0])->toContain('pledge your life to your patron');
    expect($backgroundQuestions[1])->toContain('one task you must accomplish');
    expect($backgroundQuestions[2])->toContain('similar in a very specific way');
});

test('all classes start with default stress of 6', function () {
    // All classes in DaggerHeart start with 6 stress by default
    // No need for explicit startingStress field in JSON since it's universal
    $classesPath = resource_path('json/classes.json');
    $classes = json_decode(file_get_contents($classesPath), true);
    
    // Warlock should not have explicit startingStress field since it's default behavior
    expect($classes['warlock'])->not->toHaveKey('startingStress');
    expect($classes['wizard'])->not->toHaveKey('startingStress');
    expect($classes['warrior'])->not->toHaveKey('startingStress');
    expect($classes['sorcerer'])->not->toHaveKey('startingStress');
});