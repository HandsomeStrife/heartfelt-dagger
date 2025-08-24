<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

test('assassin executioners guild subclass has correct stats and features', function () {
    // Create a test character with Assassin class and Executioners Guild subclass
    $character = Character::factory()->create([
        'class' => 'assassin',
        'subclass' => 'executioners guild',
    ]);

    // Verify base stats - Assassin has highest evasion (glass cannon)
    expect($character->class)->toBe('assassin');
    expect($character->subclass)->toBe('executioners guild');

    // Load JSON data to verify correct structure
    $classesPath = resource_path('json/classes.json');
    $subclassesPath = resource_path('json/subclasses.json');
    
    $classes = json_decode(file_get_contents($classesPath), true);
    $subclasses = json_decode(file_get_contents($subclassesPath), true);

    expect($classes['assassin']['startingEvasion'])->toBe(12);
    expect($classes['assassin']['startingHitPoints'])->toBe(5);
    expect($subclasses['executioners guild']['spellcastTrait'])->toBe('Agility');
});

test('assassin executioners guild character builder integration works correctly', function () {
    $page = visit('/')
        ->assertSee('Character Builder')
        ->click('[dusk="class-card-assassin"]')
        ->assertSee('Assassin')
        ->assertSee('Void - Playtest v1.5')
        ->assertSee('As an assassin, you utilize unmatched stealth and precision to ambush the unwary')
        ->assertSee('Starting Evasion: 12')
        ->assertSee('Starting Hit Points: 5')
        ->assertSee('Domains: Midnight, Blade')
        ->click('[dusk="subclass-card-executioners guild"]')
        ->assertSee('Executioners Guild')
        ->assertSee('Play the Executioners Guild if you want to focus on pure combat effectiveness')
        ->assertSee('Spellcast Trait: Agility')
        ->assertSee('First Strike')
        ->assertSee('Ambush')
        ->assertSee('Death Strike')
        ->assertSee('Scorpion\'s Poise')
        ->assertSee('True Strike')
        ->assertSee('Backstab')
        ->assertSee('Void - Playtest v1.5');
});

test('assassin class features are displayed correctly in browser', function () {
    $page = visit('/')
        ->click('[dusk="class-card-assassin"]')
        ->assertSee('Assassin')
        ->assertSee('Marked for Death')
        ->assertSee('Get In & Get Out')
        ->assertSee('Grim Resolve')
        ->assertSee('Spend 3 Hope to clear 2 Stress')
        ->assertSee('+1d4 per tier');
});

test('executioners guild damage escalation features are shown', function () {
    $page = visit('/')
        ->click('[dusk="class-card-assassin"]')
        ->click('[dusk="subclass-card-executioners guild"]')
        ->assertSee('Executioners Guild')
        ->assertSee('double the damage of the attack')
        ->assertSee('d6s instead of d4s')
        ->assertSee('+2 bonus to your Evasion')
        ->assertSee('d8s instead of d6s')
        ->assertSee('spend a Hope to make it a success');
});

test('assassin suggested equipment is displayed correctly', function () {
    $page = visit('/')
        ->click('[dusk="class-card-assassin"]')
        ->assertSee('Assassin')
        ->assertSee('Broadsword')
        ->assertSee('Short Sword')
        ->assertSee('Reliable')
        ->assertSee('Paired')
        ->assertSee('Leather Armor')
        ->assertSee('Agility');
});

test('assassin background questions are displayed', function () {
    $page = visit('/')
        ->click('[dusk="class-card-assassin"]')
        ->assertSee('Assassin')
        ->assertSee('organization trained you in the art of killing')
        ->assertSee('one target has eluded you')
        ->assertSee('one line that you will never cross');
});

test('assassin connections are displayed', function () {
    $page = visit('/')
        ->click('[dusk="class-card-assassin"]')
        ->assertSee('Assassin')
        ->assertSee('What about me frightens you?')
        ->assertSee('keeps you up at night')
        ->assertSee('secret about myself');
});

test('assassin inventory choices are displayed', function () {
    $page = visit('/')
        ->click('[dusk="class-card-assassin"]')
        ->assertSee('Assassin')
        ->assertSee('list of names with sins marked off')
        ->assertSee('mortal and pestle inscribed with mysterious insignia')
        ->assertSee('Minor Health Potion')
        ->assertSee('Minor Stamina Potion');
});

test('assassin character stats calculate correctly in browser', function () {
    $character = Character::factory()->create([
        'class' => 'assassin',
        'subclass' => 'executioners guild',
    ]);

    // Load class data to verify stats
    $classesPath = resource_path('json/classes.json');
    $classes = json_decode(file_get_contents($classesPath), true);
    $assassinData = $classes['assassin'];

    expect($assassinData['startingEvasion'])->toBe(12);
    expect($assassinData['startingHitPoints'])->toBe(5);
    expect($assassinData['domains'])->toContain('midnight');
    expect($assassinData['domains'])->toContain('blade');
    expect($assassinData['hopeFeature']['name'])->toBe('Grim Resolve');
    expect($assassinData['hopeFeature']['hopeCost'])->toBe(3);
});