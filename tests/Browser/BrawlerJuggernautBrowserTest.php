<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

test('brawler juggernaut subclass has correct stats and features', function () {
    // Create a test character with Brawler class and Juggernaut subclass
    $character = Character::factory()->create([
        'class' => 'brawler',
        'subclass' => 'juggernaut',
    ]);

    // Verify the character was created correctly
    expect($character->class)->toBe('brawler');
    expect($character->subclass)->toBe('juggernaut');

    // Load JSON data to verify correct structure
    $classesPath = resource_path('json/classes.json');
    $classes = json_decode(file_get_contents($classesPath), true);
    expect($classes['brawler']['startingEvasion'])->toBe(10);
    expect($classes['brawler']['startingHitPoints'])->toBe(6);
});

test('brawler juggernaut character builder integration works correctly', function () {
    $page = visit('/character-builder')
        ->assertSee('Choose a Class')
        ->click('[dusk="class-card-brawler"]')
        ->wait(2) // Wait for class details to load
        ->assertSee('Brawler')
        ->assertSee('As a brawler, you can use your fists just as well as any weapon')
        ->assertSee('Bone')
        ->assertSee('Valor')
        ->click('[dusk="subclass-card-juggernaut"]')
        ->wait(2) // Wait for subclass details to load  
        ->assertSee('Juggernaut')
        ->assertSee('Play the Juggernaut if you want to focus on raw power')
        ->assertSee('Powerhouse');
});

test('brawler class features are displayed correctly in browser', function () {
    $page = visit('/character-builder')
        ->click('[dusk="class-card-brawler"]')
        ->assertSee('Brawler')
        ->assertSee('I Am the Weapon')
        ->assertSee('Combo Strikes')
        ->assertSee('Staggering Strike')
        ->assertSee('d8+6 phy damage')
        ->assertSee('+1 bonus to Evasion')
        ->assertSee('starts as a d4')
        ->assertSee('temporarily Stagger');
});

test('juggernaut multi-target progression is displayed', function () {
    $page = visit('/character-builder')
        ->click('[dusk="class-card-brawler"]')
        ->click('[dusk="subclass-card-juggernaut"]')
        ->assertSee('Juggernaut')
        ->assertSee('d8 damage dice for your unarmed attack to d10') // Foundation upgrade
        ->assertSee('target two creatures within Melee range') // Foundation: 2 targets
        ->assertSee('three creatures instead of two'); // Specialization: 3 targets
});

test('juggernaut survivability features are shown', function () {
    $page = visit('/character-builder')
        ->click('[dusk="class-card-brawler"]')
        ->click('[dusk="subclass-card-juggernaut"]')
        ->assertSee('Juggernaut')
        ->assertSee('+3 bonus to your Severe damage threshold')
        ->assertSee('more than one Hit Point from an attack')
        ->assertSee('gain a Hope or clear a Stress');
});

test('juggernaut combat excellence features are displayed', function () {
    $page = visit('/character-builder')
        ->click('[dusk="class-card-brawler"]')
        ->click('[dusk="subclass-card-juggernaut"]')
        ->assertSee('Juggernaut')
        ->assertSee('critical success on a weapon attack')
        ->assertSee('additional Hope')
        ->assertSee('clear an additional Stress')
        ->assertSee('+1 bonus to your Proficiency');
});

test('brawler suggested equipment supports versatile combat', function () {
    $page = visit('/character-builder')
        ->click('[dusk="class-card-brawler"]')
        ->assertSee('Brawler')
        ->assertSee('Quarterstaff')
        ->assertSee('Instinct')
        ->assertSee('d10+3 phy')
        ->assertSee('Two-Handed')
        ->assertSee('Leather Armor');
});

test('brawler traits emphasize instinct for combo mechanics', function () {
    $page = visit('/character-builder')
        ->click('[dusk="class-card-brawler"]')
        ->assertSee('Brawler')
        ->assertSee('Instinct: +2'); // Should show highest suggested trait
});

test('brawler background questions focus on martial training', function () {
    $page = visit('/character-builder')
        ->click('[dusk="class-card-brawler"]')
        ->assertSee('Brawler')
        ->assertSee('formative years that taught you')
        ->assertSee('fight in the style you use')
        ->assertSee('organization has always had your back')
        ->assertSee('lose a fight to long ago')
        ->assertSee('desperate for a rematch');
});

test('brawler connections emphasize personal relationships', function () {
    $page = visit('/character-builder')
        ->click('[dusk="class-card-brawler"]')
        ->assertSee('Brawler')
        ->assertSee('What is one thing we\'re both afraid of?')
        ->assertSee('I rely on you for something important')
        ->assertSee('haven\'t forgiven you for something you said');
});

test('brawler inventory includes martial arts themed items', function () {
    $page = visit('/character-builder')
        ->click('[dusk="class-card-brawler"]')
        ->assertSee('Brawler')
        ->assertSee('hand wraps from a mentor')
        ->assertSee('book about your secret hobby')
        ->assertSee('Minor Health Potion')
        ->assertSee('Minor Stamina Potion');
});

test('juggernaut damage escalation with base class mechanics', function () {
    $page = visit('/character-builder')
        ->click('[dusk="class-card-brawler"]')
        ->assertSee('Brawler')
        ->assertSee('d8+6 phy damage'); // Base class unarmed damage

    $page->click('[dusk="subclass-card-juggernaut"]')
        ->assertSee('Juggernaut')
        ->assertSee('d8 damage dice for your unarmed attack to d10'); // Foundation upgrade

    // This creates d10+6 unarmed damage with multi-target capability
});

test('juggernaut creates tank-like character that gets stronger when wounded', function () {
    $page = visit('/character-builder')
        ->click('[dusk="class-card-brawler"]')
        ->click('[dusk="subclass-card-juggernaut"]')
        ->assertSee('Juggernaut')
        // Enhanced damage threshold for survivability
        ->assertSee('+3 bonus to your Severe damage threshold')
        // Recovery mechanics when taking damage
        ->assertSee('more than one Hit Point from an attack')
        ->assertSee('gain a Hope or clear a Stress')
        // Combat bonuses on critical hits
        ->assertSee('critical success on a weapon attack')
        ->assertSee('additional Hope');
});