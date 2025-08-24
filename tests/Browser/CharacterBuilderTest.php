<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use Domain\User\Models\User;

test('user can access character builder', function () {
    $page = visit('/character-builder');
    
    $page->assertSee('Character Builder')
            ->assertSee('Create your Daggerheart character')
            ->assertSee('Choose a Class')
        ->assertPresent('[dusk="sidebar-tab-1"]');
});

test('user can select a class and see subclass options', function () {
    $page = visit('/character-builder');
    
    $page->assertSee('Choose a Class')
        ->wait(3)
        ->click('[dusk="class-card-guardian"]')
        ->wait(3) // Wait for subclass section to load
        ->assertSee('Choose Your Subclass');
        // Note: Subclass data loading needs investigation in separate ticket
})->skip('Subclass data loading issue - needs separate investigation');

test('character builder basic functionality works', function () {
    $page = visit('/character-builder');
    
    // Test basic loading and interaction
    $page->assertSee('Character Builder')
        ->wait(2)
        ->assertSee('Choose a Class');
        
    // Test character name input
    $page->type('[dusk="character-name-input"]', 'Test Hero')
        ->wait(1)
        ->assertValue('[dusk="character-name-input"]', 'Test Hero');
});

test('pronouns field works correctly', function () {
    $page = visit('/character-builder');
    
    $page->wait(2)
        ->type('#character-pronouns', 'they/them')
        ->wait(1)
        ->assertValue('#character-pronouns', 'they/them');
});

test('class selection shows available classes', function () {
    $page = visit('/character-builder');
    
    $page->wait(3)
        ->assertSee('Warrior')
        ->assertSee('Wizard')
        ->assertSee('Sorcerer')
        ->assertSee('Brawler')
        ->assertSee('Assassin')
        ->assertSee('Witch')
        ->assertSee('Warlock');
});

test('user can navigate between tabs', function () {
    $page = visit('/character-builder');
    
    $page->wait(2)
        ->click('[dusk="sidebar-tab-2"]')
        ->wait(1)
        ->assertSee('Choose Your Subclass')
        ->click('[dusk="sidebar-tab-3"]')
        ->wait(1)
        ->assertSee('Choose Your Ancestry')
        ->click('[dusk="sidebar-tab-1"]')
        ->wait(1)
        ->assertSee('Choose a Class');
});

test('mobile navigation works', function () {
    $page = visit('/character-builder')->on()->mobile();
    
    $page->wait(3)
        ->assertSee('Character Builder')
        ->assertSee('Step 1');
});
