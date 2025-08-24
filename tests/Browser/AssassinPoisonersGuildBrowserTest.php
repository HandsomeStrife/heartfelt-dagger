<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

test('assassin poisoners guild subclass has correct stats and features', function () {
    // Create a test character with Assassin class and Poisoners Guild subclass
    $character = Character::factory()->create([
        'class' => 'assassin',
        'subclass' => 'poisoners guild',
    ]);

    // Verify the character was created correctly
    expect($character->class)->toBe('assassin');
    expect($character->subclass)->toBe('poisoners guild');

    // Load JSON data to verify correct structure
    $subclassesPath = resource_path('json/subclasses.json');
    $subclasses = json_decode(file_get_contents($subclassesPath), true);
    expect($subclasses['poisoners guild']['spellcastTrait'])->toBe('Knowledge');
});

test('assassin poisoners guild character builder integration works correctly', function () {
    $page = visit('/')
        ->assertSee('Character Builder')
        ->click('[dusk="class-card-assassin"]')
        ->assertSee('Assassin')
        ->assertSee('Void - Playtest v1.5')
        ->assertSee('As an assassin, you utilize unmatched stealth and precision to ambush the unwary')
        ->assertSee('Starting Evasion: 12')
        ->assertSee('Starting Hit Points: 5')
        ->assertSee('Domains: Midnight, Blade')
        ->click('[dusk="subclass-card-poisoners guild"]')
        ->assertSee('Poisoners Guild')
        ->assertSee('Play the Poisoners Guild if you want to specialize in toxin application')
        ->assertSee('Spellcast Trait: Knowledge')
        ->assertSee('Toxic Concoctions')
        ->assertSee('Poison Compendium')
        ->assertSee('Venomancer')
        ->assertSee('Void - Playtest v1.5');
});

test('poisoners guild toxin mechanics are displayed in browser', function () {
    $page = visit('/')
        ->click('[dusk="class-card-assassin"]')
        ->click('[dusk="subclass-card-poisoners guild"]')
        ->assertSee('Poisoners Guild')
        ->assertSee('1d4+1 toxins')
        ->assertSee('Beguile Toxin')
        ->assertSee('Grave Mold')
        ->assertSee('Leech Weed')
        ->assertSee('Envenomate')
        ->assertSee('+1d6 damage bonus');
});

test('poisoners guild advanced poisons are shown', function () {
    $page = visit('/')
        ->click('[dusk="class-card-assassin"]')
        ->click('[dusk="subclass-card-poisoners guild"]')
        ->assertSee('Poisoners Guild')
        ->assertSee('Midnight\'s Veil')
        ->assertSee('Ghost Petal')
        ->assertSee('Adder\'s Blessing')
        ->assertSee('permanent -2 penalty to attack rolls')
        ->assertSee('damage dice by one step')
        ->assertSee('immune to poisons');
});

test('poisoners guild master-level toxins are displayed', function () {
    $page = visit('/')
        ->click('[dusk="class-card-assassin"]')
        ->click('[dusk="subclass-card-poisoners guild"]')
        ->assertSee('Poisoners Guild')
        ->assertSee('Blight Seed')
        ->assertSee('Fear Leaf')
        ->assertSee('Twin Fang')
        ->assertSee('permanent -3 penalty to damage threshold')
        ->assertSee('second known poison');
});

test('poisoners guild uses knowledge vs executioners guild agility', function () {
    // Test Poisoners Guild uses Knowledge
    $page = visit('/')
        ->click('[dusk="class-card-assassin"]')
        ->click('[dusk="subclass-card-poisoners guild"]')
        ->assertSee('Poisoners Guild')
        ->assertSee('Spellcast Trait: Knowledge');

    // Test Executioners Guild uses Agility
    $page->click('[dusk="subclass-card-executioners guild"]')
        ->assertSee('Executioners Guild')
        ->assertSee('Spellcast Trait: Agility');
});

test('assassin inventory supports both subclass approaches', function () {
    $page = visit('/')
        ->click('[dusk="class-card-assassin"]')
        ->assertSee('Assassin')
        ->assertSee('mortal and pestle inscribed with mysterious insignia') // Perfect for poisoners
        ->assertSee('list of names with sins marked off'); // Good for executioners
});

test('poisoners guild progression creates debuff specialist', function () {
    $page = visit('/')
        ->click('[dusk="class-card-assassin"]')
        ->click('[dusk="subclass-card-poisoners guild"]')
        ->assertSee('Poisoners Guild')
        // Foundation: Basic debuffs
        ->assertSee('-1 penalty')
        // Specialization: Permanent debuffs  
        ->assertSee('permanent -2 penalty')
        ->assertSee('permanently decrease')
        // Mastery: Devastating effects + multi-poison
        ->assertSee('permanent -3 penalty')
        ->assertSee('second known poison');
});

test('both assassin subclasses share core mechanics but differ in approach', function () {
    // Test shared Assassin features
    $page = visit('/')
        ->click('[dusk="class-card-assassin"]')
        ->assertSee('Assassin')
        ->assertSee('Marked for Death')
        ->assertSee('Get In & Get Out')
        ->assertSee('Grim Resolve');

    // Test Executioners Guild approach (combat focused)
    $page->click('[dusk="subclass-card-executioners guild"]')
        ->assertSee('Executioners Guild')
        ->assertSee('double the damage')
        ->assertSee('d6s instead of d4s')
        ->assertSee('d8s instead of d6s');

    // Test Poisoners Guild approach (debuff focused)
    $page->click('[dusk="subclass-card-poisoners guild"]')
        ->assertSee('Poisoners Guild')
        ->assertSee('1d4+1 toxins')
        ->assertSee('permanent -2 penalty')
        ->assertSee('permanent -3 penalty');
});