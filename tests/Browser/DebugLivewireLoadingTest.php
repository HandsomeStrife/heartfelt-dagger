<?php

declare(strict_types=1);

test('debug livewire component loading', function () {
    $page = visit('/')
        ->assertSee('Character Builder');
    
    // Wait much longer for Livewire to initialize
    echo "Waiting for Livewire components to load...\n";
    $page->wait(10);
    
    // Try to detect Livewire is loaded
    $hasLivewire = $page->script('typeof window.Livewire !== "undefined"');
    echo "Livewire loaded: " . ($hasLivewire ? "YES" : "NO") . "\n";
    
    // Try to find Livewire components
    $livewireElements = $page->script('document.querySelectorAll("[wire\\:id]").length');
    echo "Livewire components found: " . $livewireElements . "\n";
    
    // Check for Alpine.js
    $hasAlpine = $page->script('typeof window.Alpine !== "undefined"');
    echo "Alpine.js loaded: " . ($hasAlpine ? "YES" : "NO") . "\n";
    
    // Wait even longer and try again
    $page->wait(5);
    
    // Check if content loaded
    $html = $page->content();
    echo "After 15s wait - DOM contains 'Brawler': " . (strpos($html, 'Brawler') !== false ? "YES" : "NO") . "\n";
    
    // Try to interact with any visible elements
    try {
        // Look for any clickable elements
        $clickableElements = $page->script('document.querySelectorAll("[x-on\\:click], [wire\\:click], .cursor-pointer").length');
        echo "Clickable elements found: " . $clickableElements . "\n";
    } catch (Exception $e) {
        echo "Error checking clickable elements: " . $e->getMessage() . "\n";
    }
    
    $page->screenshot(filename: 'debug-livewire-loading');
    
    expect(true)->toBe(true);
});
