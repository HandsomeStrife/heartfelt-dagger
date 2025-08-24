<?php

declare(strict_types=1);

test('assassin class is added to classes.json correctly', function () {
    $classesPath = resource_path('json/classes.json');
    expect($classesPath)->toBeFile();
    
    $classes = json_decode(file_get_contents($classesPath), true);
    expect($classes)->toHaveKey('assassin');
    
    $assassin = $classes['assassin'];
    expect($assassin['name'])->toBe('Assassin');
    expect($assassin['domains'])->toContain('midnight');
    expect($assassin['domains'])->toContain('blade');
    expect($assassin['startingEvasion'])->toBe(12);
    expect($assassin['startingHitPoints'])->toBe(5);
    expect($assassin['playtest']['isPlaytest'])->toBe(true);
    expect($assassin['playtest']['version'])->toBe('1.5');
});

test('assassin subclasses are added to subclasses.json correctly', function () {
    $subclassesPath = resource_path('json/subclasses.json');
    expect($subclassesPath)->toBeFile();
    
    $subclasses = json_decode(file_get_contents($subclassesPath), true);
    expect($subclasses)->toHaveKey('executioners guild');
    expect($subclasses)->toHaveKey('poisoners guild');
    
    $executioners = $subclasses['executioners guild'];
    expect($executioners['name'])->toBe('Executioners Guild');
    expect($executioners['spellcastTrait'])->toBe('Agility');
    expect($executioners['playtest']['isPlaytest'])->toBe(true);
    expect($executioners['playtest']['version'])->toBe('1.5');
    
    $poisoners = $subclasses['poisoners guild'];
    expect($poisoners['name'])->toBe('Poisoners Guild');
    expect($poisoners['spellcastTrait'])->toBe('Knowledge');
    expect($poisoners['playtest']['isPlaytest'])->toBe(true);
    expect($poisoners['playtest']['version'])->toBe('1.5');
});

test('assassin has unique hope feature', function () {
    $classesPath = resource_path('json/classes.json');
    $classes = json_decode(file_get_contents($classesPath), true);
    $assassin = $classes['assassin'];
    
    $hopeFeature = $assassin['hopeFeature'];
    expect($hopeFeature['name'])->toBe('Grim Resolve');
    expect($hopeFeature['hopeCost'])->toBe(3);
    expect($hopeFeature['description'])->toBe('Spend 3 Hope to clear 2 Stress.');
});

test('assassin class features include signature mechanics', function () {
    $classesPath = resource_path('json/classes.json');
    $classes = json_decode(file_get_contents($classesPath), true);
    $assassin = $classes['assassin'];
    
    $classFeatures = collect($assassin['classFeatures']);
    expect($classFeatures->pluck('name'))->toContain('Marked for Death');
    expect($classFeatures->pluck('name'))->toContain('Get In & Get Out');
    
    $markedForDeathFeature = $classFeatures->where('name', 'Marked for Death')->first();
    expect($markedForDeathFeature['description'])->toContain('mark a Stress to make the target Marked for Death');
    expect($markedForDeathFeature['description'])->toContain('+1d4 per tier');
    expect($markedForDeathFeature['description'])->toContain('one adversary Marked for Death at a time');
    
    $infiltrationFeature = $classFeatures->where('name', 'Get In & Get Out')->first();
    expect($infiltrationFeature['description'])->toContain('Spend a Hope');
    expect($infiltrationFeature['description'])->toContain('quick or inconspicuous way');
    expect($infiltrationFeature['description'])->toContain('advantage');
});

test('executioners guild features progress damage escalation', function () {
    $subclassesPath = resource_path('json/subclasses.json');
    $subclasses = json_decode(file_get_contents($subclassesPath), true);
    
    $executioners = $subclasses['executioners guild'];
    expect($executioners)->toHaveKey('foundationFeatures');
    expect($executioners)->toHaveKey('specializationFeatures');
    expect($executioners)->toHaveKey('masteryFeatures');
    
    // Foundation: d4 -> d6 escalation + scene opener
    $foundationFeatures = collect($executioners['foundationFeatures']);
    expect($foundationFeatures->pluck('name'))->toContain('First Strike');
    expect($foundationFeatures->pluck('name'))->toContain('Ambush');
    
    $firstStrike = $foundationFeatures->where('name', 'First Strike')->first();
    expect($firstStrike['description'])->toContain('first time in a scene');
    expect($firstStrike['description'])->toContain('double the damage');
    
    $ambush = $foundationFeatures->where('name', 'Ambush')->first();
    expect($ambush['description'])->toContain('d6s instead of d4s');
    
    // Specialization: survivability and lethality
    $specializationFeatures = collect($executioners['specializationFeatures']);
    expect($specializationFeatures->pluck('name'))->toContain('Death Strike');
    expect($specializationFeatures->pluck('name'))->toContain('Scorpion\'s Poise');
    
    $deathStrike = $specializationFeatures->where('name', 'Death Strike')->first();
    expect($deathStrike['description'])->toContain('severe damage');
    expect($deathStrike['description'])->toContain('additional Hit Point');
    
    $scorpionsPoise = $specializationFeatures->where('name', 'Scorpion\'s Poise')->first();
    expect($scorpionsPoise['description'])->toContain('+2 bonus to your Evasion');
    expect($scorpionsPoise['description'])->toContain('Marked for Death');
    
    // Mastery: d6 -> d8 escalation + reliability
    $masteryFeatures = collect($executioners['masteryFeatures']);
    expect($masteryFeatures->pluck('name'))->toContain('True Strike');
    expect($masteryFeatures->pluck('name'))->toContain('Backstab');
    
    $trueStrike = $masteryFeatures->where('name', 'True Strike')->first();
    expect($trueStrike['description'])->toContain('fail an attack roll');
    expect($trueStrike['description'])->toContain('spend a Hope to make it a success');
    
    $backstab = $masteryFeatures->where('name', 'Backstab')->first();
    expect($backstab['description'])->toContain('d8s instead of d6s');
});

test('poisoners guild features implement toxin mechanics', function () {
    $subclassesPath = resource_path('json/subclasses.json');
    $subclasses = json_decode(file_get_contents($subclassesPath), true);
    
    $poisoners = $subclasses['poisoners guild'];
    expect($poisoners)->toHaveKey('foundationFeatures');
    expect($poisoners)->toHaveKey('specializationFeatures');
    expect($poisoners)->toHaveKey('masteryFeatures');
    
    // Foundation: toxin token system
    $foundationFeatures = collect($poisoners['foundationFeatures']);
    expect($foundationFeatures->pluck('name'))->toContain('Toxic Concoctions');
    
    $toxicConcoctions = $foundationFeatures->where('name', 'Toxic Concoctions')->first();
    expect($toxicConcoctions['description'])->toContain('1d4+1 toxins');
    expect($toxicConcoctions['description'])->toContain('Beguile Toxin');
    expect($toxicConcoctions['description'])->toContain('Grave Mold');
    expect($toxicConcoctions['description'])->toContain('Leech Weed');
    expect($toxicConcoctions['description'])->toContain('Envenomate');
    
    // Specialization: advanced poisons
    $specializationFeatures = collect($poisoners['specializationFeatures']);
    expect($specializationFeatures->pluck('name'))->toContain('Poison Compendium');
    
    $poisonCompendium = $specializationFeatures->where('name', 'Poison Compendium')->first();
    expect($poisonCompendium['description'])->toContain('Midnight\'s Veil');
    expect($poisonCompendium['description'])->toContain('Ghost Petal');
    expect($poisonCompendium['description'])->toContain('Adder\'s Blessing');
    expect($poisonCompendium['description'])->toContain('permanent -2 penalty');
    expect($poisonCompendium['description'])->toContain('damage dice by one step');
    
    // Mastery: master-level toxins
    $masteryFeatures = collect($poisoners['masteryFeatures']);
    expect($masteryFeatures->pluck('name'))->toContain('Venomancer');
    
    $venomancer = $masteryFeatures->where('name', 'Venomancer')->first();
    expect($venomancer['description'])->toContain('Blight Seed');
    expect($venomancer['description'])->toContain('Fear Leaf');
    expect($venomancer['description'])->toContain('Twin Fang');
    expect($venomancer['description'])->toContain('-3 penalty to damage threshold');
    expect($venomancer['description'])->toContain('second known poison');
});

test('assassin suggested equipment emphasizes dual wielding', function () {
    $classesPath = resource_path('json/classes.json');
    $classes = json_decode(file_get_contents($classesPath), true);
    $assassin = $classes['assassin'];
    
    $primary = $assassin['suggestedWeapons']['primary'];
    expect($primary['name'])->toBe('Broadsword');
    expect($primary['trait'])->toBe('Agility');
    expect($primary['range'])->toBe('Melee');
    expect($primary['damage'])->toBe('d8 phy');
    expect($primary['handedness'])->toBe('One-Handed');
    expect($primary['properties'][0])->toContain('Reliable');
    
    $secondary = $assassin['suggestedWeapons']['secondary'];
    expect($secondary['name'])->toBe('Short Sword');
    expect($secondary['trait'])->toBe('Agility');
    expect($secondary['range'])->toBe('Melee');
    expect($secondary['damage'])->toBe('d8 phy');
    expect($secondary['handedness'])->toBe('One-Handed');
    expect($secondary['properties'][0])->toContain('Paired');
});

test('assassin inventory includes thematic choices', function () {
    $classesPath = resource_path('json/classes.json');
    $classes = json_decode(file_get_contents($classesPath), true);
    $assassin = $classes['assassin'];
    
    $inventory = $assassin['startingInventory'];
    expect($inventory['always'])->toContain('torch');
    expect($inventory['always'])->toContain('50 feet of rope');
    expect($inventory['always'])->toContain('basic supplies');
    expect($inventory['always'])->toContain('handful of gold');
    
    expect($inventory['chooseOne'])->toContain('Minor Health Potion');
    expect($inventory['chooseOne'])->toContain('Minor Stamina Potion');
    
    expect($inventory['chooseExtra'])->toContain('list of names with sins marked off');
    expect($inventory['chooseExtra'])->toContain('mortal and pestle inscribed with mysterious insignia');
});

test('assassin background questions focus on professional killer theme', function () {
    $classesPath = resource_path('json/classes.json');
    $classes = json_decode(file_get_contents($classesPath), true);
    $assassin = $classes['assassin'];
    
    $backgroundQuestions = $assassin['backgroundQuestions'];
    expect($backgroundQuestions[0])->toContain('organization trained you in the art of killing');
    expect($backgroundQuestions[1])->toContain('one target has eluded you');
    expect($backgroundQuestions[1])->toContain('slip through your fingers');
    expect($backgroundQuestions[2])->toContain('one line that you will never cross');
});

test('assassin connections emphasize fear and dark secrets', function () {
    $classesPath = resource_path('json/classes.json');
    $classes = json_decode(file_get_contents($classesPath), true);
    $assassin = $classes['assassin'];
    
    $connections = $assassin['connections'];
    expect($connections[0])->toContain('What about me frightens you?');
    expect($connections[1])->toContain('keeps you up at night');
    expect($connections[2])->toContain('secret about myself');
    expect($connections[2])->toContain('change your view of me');
});

test('assassin suggested traits emphasize agility and stealth', function () {
    $classesPath = resource_path('json/classes.json');
    $classes = json_decode(file_get_contents($classesPath), true);
    $assassin = $classes['assassin'];
    
    $suggestedTraits = $assassin['suggestedTraits'];
    expect($suggestedTraits['agility'])->toBe(2); // Highest suggested trait
    expect($suggestedTraits['strength'])->toBe(1);
    expect($suggestedTraits['finesse'])->toBe(1);
    expect($suggestedTraits['instinct'])->toBe(0);
    expect($suggestedTraits['presence'])->toBe(0);
    expect($suggestedTraits['knowledge'])->toBe(1);
});

test('assassin has highest starting evasion for glass cannon archetype', function () {
    $classesPath = resource_path('json/classes.json');
    $classes = json_decode(file_get_contents($classesPath), true);
    
    $startingEvasions = [];
    foreach ($classes as $className => $classData) {
        if (isset($classData['startingEvasion'])) {
            $startingEvasions[$className] = $classData['startingEvasion'];
        }
    }
    
    expect($startingEvasions['assassin'])->toBe(12);
    
    // Verify it's the highest
    $maxEvasion = max($startingEvasions);
    expect($maxEvasion)->toBe(12);
    
    // Should have lower HP to balance the high evasion
    expect($classes['assassin']['startingHitPoints'])->toBe(5);
});
