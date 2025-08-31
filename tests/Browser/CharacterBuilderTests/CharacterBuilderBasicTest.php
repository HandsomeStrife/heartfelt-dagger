<?php

declare(strict_types=1);

test('character builder loads correctly and shows classes', function () {
    $page = visit('/character-builder');
    
    // Wait for any redirects and loading
    $page->wait(3);
    
    // Should see character builder content 
    $page->assertSee('Choose a Class');
    
    // Should see our new classes
    $page->assertSee('Brawler')
        ->assertSee('Assassin')
        ->assertSee('Witch')
        ->assertSee('Warlock');
    
    // Should also see original classes
    $page->assertSee('Warrior')
        ->assertSee('Sorcerer')
        ->assertSee('Wizard');
    
    $page->screenshot(filename: 'character-builder-loaded');
});