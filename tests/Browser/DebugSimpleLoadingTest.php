<?php

declare(strict_types=1);

test('debug simple content loading', function () {
    $page = visit('/')
        ->assertSee('Character Builder');
    
    echo "Initial page loaded\n";
    
    // Check for Livewire
    $hasLivewire = $page->script('typeof window.Livewire !== "undefined"');
    echo "Livewire available: " . ($hasLivewire ? "YES" : "NO") . "\n";
    
    // Check for Alpine  
    $hasAlpine = $page->script('typeof window.Alpine !== "undefined"');
    echo "Alpine.js available: " . ($hasAlpine ? "YES" : "NO") . "\n";
    
    // Wait longer for content
    echo "Waiting 10 seconds for components to load...\n";
    $page->wait(10);
    
    // Check DOM again
    $html = $page->content();
    $hasBrawler = strpos($html, 'Brawler') !== false;
    $hasAssassin = strpos($html, 'Assassin') !== false;
    $hasClassCards = strpos($html, 'class-card') !== false;
    
    echo "After 10s wait:\n";
    echo "- Contains 'Brawler': " . ($hasBrawler ? "YES" : "NO") . "\n";
    echo "- Contains 'Assassin': " . ($hasAssassin ? "YES" : "NO") . "\n";
    echo "- Contains 'class-card': " . ($hasClassCards ? "YES" : "NO") . "\n";
    
    // Try to find any classes that should definitely be there
    $hasWarrior = strpos($html, 'Warrior') !== false;
    $hasSorcerer = strpos($html, 'Sorcerer') !== false;
    echo "- Contains 'Warrior': " . ($hasWarrior ? "YES" : "NO") . "\n";
    echo "- Contains 'Sorcerer': " . ($hasSorcerer ? "YES" : "NO") . "\n";
    
    $page->screenshot(filename: 'debug-simple-loading');
    
    expect(true)->toBe(true);
});