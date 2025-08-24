<?php

declare(strict_types=1);

test('brawler class selection works correctly', function () {
    $page = visit('/character-builder')
        ->assertSee('Choose a Class')
        ->assertSee('Brawler')
        ->click('[dusk="class-card-brawler"]')
        ->wait(3) // Wait for class details to load
        ->assertSee('Selected Class')
        ->assertSee('Brawler')
        ->assertSee('As a brawler, you can use your fists just as well as any weapon')
        ->assertSee('Domains')
        ->assertSee('Bone')
        ->assertSee('Valor')
        ->assertSee('Choose Your Subclass');
    
    $page->screenshot(filename: 'working-brawler-test');
});

test('assassin class selection works correctly', function () {
    $page = visit('/character-builder')
        ->assertSee('Choose a Class')
        ->assertSee('Assassin')
        ->click('[dusk="class-card-assassin"]')
        ->wait(3) // Wait for class details to load
        ->assertSee('Selected Class')
        ->assertSee('Assassin')
        ->assertSee('As an assassin, you utilize unmatched stealth and precision')
        ->assertSee('Domains')
        ->assertSee('Midnight')
        ->assertSee('Blade')
        ->assertSee('Choose Your Subclass');
    
    $page->screenshot(filename: 'working-assassin-test');
});
