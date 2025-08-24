<?php

declare(strict_types=1);

test('debug available classes on homepage', function () {
    $page = visit('/')
        ->assertSee('Character Builder')
        ->wait(3); // Wait for content to load
    
    // Take a screenshot to see what's rendered
    $page->screenshot(filename: 'debug-available-classes');
    
    // Try to find some classes that should exist (original ones)
    try {
        $page->assertPresent('[dusk="class-card-warrior"]');
        echo "✅ Warrior class found\n";
    } catch (Exception $e) {
        echo "❌ Warrior class not found\n";
    }
    
    try {
        $page->assertPresent('[dusk="class-card-brawler"]');
        echo "✅ Brawler class found\n";
    } catch (Exception $e) {
        echo "❌ Brawler class not found\n";
    }
    
    try {
        $page->assertPresent('[dusk="class-card-assassin"]');
        echo "✅ Assassin class found\n";
    } catch (Exception $e) {
        echo "❌ Assassin class not found\n";
    }
    
    expect(true)->toBe(true);
});
