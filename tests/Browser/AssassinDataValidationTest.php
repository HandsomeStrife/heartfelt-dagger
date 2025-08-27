<?php

declare(strict_types=1);

test('assassin class data structure is valid', function () {
    // Load the JSON data directly
    $classesPath = resource_path('json/classes.json');
    $subclassesPath = resource_path('json/subclasses.json');
    
    $classes = json_decode(file_get_contents($classesPath), true);
    $subclasses = json_decode(file_get_contents($subclassesPath), true);

    // Verify assassin class data exists and is correct
    expect($classes)->toHaveKey('assassin');
    $assassinData = $classes['assassin'];
    
    // Verify base stats - Assassin has highest evasion (glass cannon)
    expect($assassinData['startingEvasion'])->toBe(12);
    expect($assassinData['startingHitPoints'])->toBe(5);
    expect($assassinData['domains'])->toContain('midnight');
    expect($assassinData['domains'])->toContain('blade');
    
    // Verify class is marked as playtest
    expect($assassinData['playtest']['isPlaytest'])->toBe(true);
    expect($assassinData['playtest']['version'])->toBe('1.5');
    expect($assassinData['playtest']['label'])->toBe('Void - Playtest v1.5');

    // Verify both subclasses exist
    expect($subclasses)->toHaveKey('executioners guild');
    expect($subclasses)->toHaveKey('poisoners guild');
    
    $executionersData = $subclasses['executioners guild'];
    $poisonersData = $subclasses['poisoners guild'];
    
    expect($executionersData['spellcastTrait'])->toBe('Agility');
    expect($poisonersData['spellcastTrait'])->toBe('Knowledge');
});

test('assassin class features are correctly structured', function () {
    $classesPath = resource_path('json/classes.json');
    $classes = json_decode(file_get_contents($classesPath), true);
    $assassinData = $classes['assassin'];

    // Verify class features
    $classFeatures = collect($assassinData['classFeatures']);
    expect($classFeatures->pluck('name'))->toContain('Marked for Death');
    expect($classFeatures->pluck('name'))->toContain('Get In & Get Out');

    $markedForDeath = $classFeatures->where('name', 'Marked for Death')->first();
    expect($markedForDeath['description'])->toContain('mark a Stress to make the target Marked for Death');
    expect($markedForDeath['description'])->toContain('+1d4 per tier');

    // Verify hope feature
    $hopeFeature = $assassinData['hopeFeature'];
    expect($hopeFeature['name'])->toBe('Grim Resolve');
    expect($hopeFeature['hopeCost'])->toBe(3);
    expect($hopeFeature['description'])->toBe('Spend 3 Hope to clear 2 Stress.');
});

test('executioners guild features implement damage escalation', function () {
    $subclassesPath = resource_path('json/subclasses.json');
    $subclasses = json_decode(file_get_contents($subclassesPath), true);
    $executionersData = $subclasses['executioners guild'];

    // Foundation: d4 -> d6 (Ambush) + scene opener (First Strike)
    $foundationFeatures = collect($executionersData['foundationFeatures']);
    expect($foundationFeatures->pluck('name'))->toContain('First Strike');
    expect($foundationFeatures->pluck('name'))->toContain('Ambush');

    $ambush = $foundationFeatures->where('name', 'Ambush')->first();
    expect($ambush['description'])->toContain('d6s instead of d4s');

    // Mastery: d6 -> d8 (Backstab) + reliability (True Strike)
    $masteryFeatures = collect($executionersData['masteryFeatures']);
    expect($masteryFeatures->pluck('name'))->toContain('Backstab');
    expect($masteryFeatures->pluck('name'))->toContain('True Strike');

    $backstab = $masteryFeatures->where('name', 'Backstab')->first();
    expect($backstab['description'])->toContain('d8s instead of d6s');
});

test('poisoners guild features implement toxin mechanics', function () {
    $subclassesPath = resource_path('json/subclasses.json');
    $subclasses = json_decode(file_get_contents($subclassesPath), true);
    $poisonersData = $subclasses['poisoners guild'];

    // Foundation: Toxin token system
    $foundationFeatures = collect($poisonersData['foundationFeatures']);
    expect($foundationFeatures->pluck('name'))->toContain('Toxic Concoctions');

    $toxicConcoctions = $foundationFeatures->where('name', 'Toxic Concoctions')->first();
    expect($toxicConcoctions['description'])->toContain('1d4+1 toxins');
    expect($toxicConcoctions['description'])->toContain('Beguile Toxin');
    expect($toxicConcoctions['description'])->toContain('Envenomate');

    // Specialization: Advanced poisons
    $specializationFeatures = collect($poisonersData['specializationFeatures']);
    expect($specializationFeatures->pluck('name'))->toContain('Poison Compendium');

    $poisonCompendium = $specializationFeatures->where('name', 'Poison Compendium')->first();
    expect($poisonCompendium['description'])->toContain('Midnight\'s Veil');
    expect($poisonCompendium['description'])->toContain('permanent -2 penalty');

    // Mastery: Master-level toxins
    $masteryFeatures = collect($poisonersData['masteryFeatures']);
    expect($masteryFeatures->pluck('name'))->toContain('Venomancer');

    $venomancer = $masteryFeatures->where('name', 'Venomancer')->first();
    expect($venomancer['description'])->toContain('permanent -3 penalty');
    expect($venomancer['description'])->toContain('second known poison');
});

test('assassin equipment emphasizes dual wielding', function () {
    $classesPath = resource_path('json/classes.json');
    $classes = json_decode(file_get_contents($classesPath), true);
    $assassinData = $classes['assassin'];
    
    // Primary weapon - Reliable for consistent hits
    $primaryWeapon = $assassinData['suggestedWeapons']['primary'];
    expect($primaryWeapon['name'])->toBe('Broadsword');
    expect($primaryWeapon['properties'][0])->toContain('Reliable');

    // Secondary weapon - Paired for damage synergy
    $secondaryWeapon = $assassinData['suggestedWeapons']['secondary'];
    expect($secondaryWeapon['name'])->toBe('Short Sword');
    expect($secondaryWeapon['properties'][0])->toContain('Paired');
});

test('assassin traits and background support stealth theme', function () {
    $classesPath = resource_path('json/classes.json');
    $classes = json_decode(file_get_contents($classesPath), true);
    $assassinData = $classes['assassin'];
    
    // Agility-focused traits
    $suggestedTraits = $assassinData['suggestedTraits'];
    expect($suggestedTraits['agility'])->toBe(2); // Primary trait

    // Professional killer background
    $backgroundQuestions = $assassinData['backgroundQuestions'];
    expect($backgroundQuestions[0])->toContain('art of killing');
    expect($backgroundQuestions[2])->toContain('one line that you will never cross');

    // Dark connections
    $connections = $assassinData['connections'];
    expect($connections[0])->toBe('What about me frightens you?');
});