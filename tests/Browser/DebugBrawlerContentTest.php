<?php

declare(strict_types=1);

test('debug brawler content and playtest badges', function () {
    $page = visit('/character-builder')
        ->assertSee('Choose a Class')
        ->click('[dusk="class-card-brawler"]')
        ->assertSee('Brawler')
        ->wait(2);
    
    // Check what content is actually visible
    $html = $page->content();
    $hasPlaytest = strpos($html, 'Playtest') !== false;
    $hasVoid = strpos($html, 'Void') !== false;
    $hasV15 = strpos($html, 'v1.5') !== false;
    $has15 = strpos($html, '1.5') !== false;
    
    echo "Content analysis after clicking Brawler:\n";
    echo "- Contains 'Playtest': " . ($hasPlaytest ? "YES" : "NO") . "\n";
    echo "- Contains 'Void': " . ($hasVoid ? "YES" : "NO") . "\n";
    echo "- Contains 'v1.5': " . ($hasV15 ? "YES" : "NO") . "\n";
    echo "- Contains '1.5': " . ($has15 ? "YES" : "NO") . "\n";
    
    // Look for other expected content
    $hasDescription = strpos($html, 'fists just as well as any weapon') !== false;
    $hasEvasion = strpos($html, 'Starting Evasion') !== false;
    $hasHitPoints = strpos($html, 'Starting Hit Points') !== false;
    
    echo "- Contains expected description: " . ($hasDescription ? "YES" : "NO") . "\n";
    echo "- Contains 'Starting Evasion': " . ($hasEvasion ? "YES" : "NO") . "\n"; 
    echo "- Contains 'Starting Hit Points': " . ($hasHitPoints ? "YES" : "NO") . "\n";
    
    $page->screenshot(filename: 'debug-brawler-content');
    
    expect(true)->toBe(true);
});