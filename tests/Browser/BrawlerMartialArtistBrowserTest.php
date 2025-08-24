<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

test('brawler martial artist subclass has correct stats and features', function () {
    // Create a test character with Brawler class and Martial Artist subclass
    $character = Character::factory()->create([
        'class' => 'brawler',
        'subclass' => 'martial artist',
    ]);

    // Verify the character was created correctly
    expect($character->class)->toBe('brawler');
    expect($character->subclass)->toBe('martial artist');

    // Verify shared base stats with Juggernaut
    $classesPath = resource_path('json/classes.json');
    $classes = json_decode(file_get_contents($classesPath), true);
    expect($classes['brawler']['startingEvasion'])->toBe(10);
    expect($classes['brawler']['startingHitPoints'])->toBe(6);
});

test('brawler martial artist character builder integration works correctly', function () {
    $page = visit('/')
        ->assertSee('Character Builder')
        ->click('[dusk="class-card-brawler"]')
        ->assertSee('Brawler')
        ->assertSee('Void - Playtest v1.5')
        ->assertSee('As a brawler, you can use your fists just as well as any weapon')
        ->assertSee('Starting Evasion: 10')
        ->assertSee('Starting Hit Points: 6')
        ->assertSee('Domains: Bone, Valor')
        ->click('[dusk="subclass-card-martial artist"]')
        ->assertSee('Martial Artist')
        ->assertSee('Play the Martial Artist if you want stance-based combat')
        ->assertSee('Martial Form')
        ->assertSee('Focus Mechanics')
        ->assertSee('Keen Defenses')
        ->assertSee('Spirit Blast')
        ->assertSee('Limit Breaker')
        ->assertSee('Void - Playtest v1.5');
});

test('martial artist stance system is displayed in browser', function () {
    $page = visit('/')
        ->click('[dusk="class-card-brawler"]')
        ->click('[dusk="subclass-card-martial artist"]')
        ->assertSee('Martial Artist')
        ->assertSee('two martial stances from Tier 1')
        ->assertSee('new tier, take two martial stances')
        ->assertSee('16 different stances across 4 tiers')
        ->assertSee('customizable fighting styles');
});

test('martial artist focus mechanics are shown', function () {
    $page = visit('/')
        ->click('[dusk="class-card-brawler"]')
        ->click('[dusk="subclass-card-martial artist"]')
        ->assertSee('Martial Artist')
        ->assertSee('roll a number of d6s equal to your Instinct')
        ->assertSee('highest value rolled')
        ->assertSee('Spend a Focus to shift into a stance')
        ->assertSee('until you take Severe damage');
});

test('martial artist tactical options are displayed', function () {
    $page = visit('/')
        ->click('[dusk="class-card-brawler"]')
        ->click('[dusk="subclass-card-martial artist"]')
        ->assertSee('Martial Artist')
        ->assertSee('targeted by an attack')
        ->assertSee('spend a Focus to give the attack roll disadvantage')
        ->assertSee('d20+3 magic damage')
        ->assertSee('additional Focus to make them temporarily Vulnerable');
});

test('martial artist supernatural abilities are shown', function () {
    $page = visit('/')
        ->click('[dusk="class-card-brawler"]')
        ->click('[dusk="subclass-card-martial artist"]')
        ->assertSee('Martial Artist')
        ->assertSee('Once per rest')
        ->assertSee('unbelievable feat')
        ->assertSee('running across water')
        ->assertSee('leaping between distant rooftops')
        ->assertSee('scaling the side of a building')
        ->assertSee('gain a Hope and clear a Stress');
});

test('martial artist emphasizes instinct trait for focus generation', function () {
    $page = visit('/')
        ->click('[dusk="class-card-brawler"]')
        ->assertSee('Brawler')
        ->assertSee('Instinct: +2') // Highest suggested trait for Focus generation
        ->click('[dusk="subclass-card-martial artist"]')
        ->assertSee('Martial Artist')
        ->assertSee('d6s equal to your Instinct') // Focus generation scales with Instinct
        ->assertSee('Instinct Roll against an adversary'); // Spirit Blast uses Instinct
});

test('both brawler subclasses share core mechanics but differ in approach', function () {
    // Test shared Brawler features
    $page = visit('/')
        ->click('[dusk="class-card-brawler"]')
        ->assertSee('Brawler')
        ->assertSee('I Am the Weapon')
        ->assertSee('Combo Strikes')
        ->assertSee('Staggering Strike');

    // Test Juggernaut approach (raw power)
    $page->click('[dusk="subclass-card-juggernaut"]')
        ->assertSee('Juggernaut')
        ->assertSee('target two creatures')
        ->assertSee('three creatures instead of two')
        ->assertSee('d8 damage dice for your unarmed attack to d10');

    // Test Martial Artist approach (tactical flexibility)
    $page->click('[dusk="subclass-card-martial artist"]')
        ->assertSee('Martial Artist')
        ->assertSee('16 different stances')
        ->assertSee('roll a number of d6s equal to your Instinct')
        ->assertSee('spend a Focus');
});

test('martial artist supernatural progression makes thematic sense', function () {
    $page = visit('/')
        ->click('[dusk="class-card-brawler"]')
        ->click('[dusk="subclass-card-martial artist"]')
        ->assertSee('Martial Artist')
        // Foundation: Basic stance and resource system
        ->assertSee('when you rest')
        ->assertSee('16 different stances')
        // Specialization: Magical combat techniques  
        ->assertSee('magic damage')
        ->assertSee('d20+3')
        // Mastery: Impossible physical feats
        ->assertSee('running across water')
        ->assertSee('leaping between distant rooftops')
        ->assertSee('unbelievable feat');
});

test('martial artist focus system creates strategic gameplay', function () {
    $page = visit('/')
        ->click('[dusk="class-card-brawler"]')
        ->click('[dusk="subclass-card-martial artist"]')
        ->assertSee('Martial Artist')
        // Limited Focus tokens force strategic choices
        ->assertSee('highest value rolled')
        // Multiple spending options create decision points
        ->assertSee('Spend a Focus to shift into a stance') // Option 1: Stance activation
        ->assertSee('spend a Focus to give the attack roll disadvantage') // Option 2: Defense
        ->assertSee('Spend a Focus') // Option 3: Spirit Blast magical attack
        ->assertSee('additional Focus'); // Can spend multiple Focus for enhanced effects
});

test('martial artist complements brawler base class mechanics', function () {
    $page = visit('/')
        ->click('[dusk="class-card-brawler"]')
        ->assertSee('Brawler')
        ->assertSee('d8+6 phy damage') // Base class unarmed foundation
        ->assertSee('trait of your choice'); // Flexibility in combat traits

    $page->click('[dusk="subclass-card-martial artist"]')
        ->assertSee('Martial Artist')
        ->assertSee('16 different stances') // Adds tactical flexibility through stances
        ->assertSee('customizable fighting styles'); // Creates highly customizable character
});

test('martial artist stance system provides extensive customization', function () {
    $page = visit('/')
        ->click('[dusk="class-card-brawler"]')
        ->click('[dusk="subclass-card-martial artist"]')
        ->assertSee('Martial Artist')
        ->assertSee('two martial stances from Tier 1') // Starting selection
        ->assertSee('new tier, take two martial stances') // Progression selection
        ->assertSee('16 different stances across 4 tiers') // Total available options
        ->assertSee('tier or lower') // Can choose from previous tiers
        ->assertSee('customizable fighting styles'); // End result is customization
});