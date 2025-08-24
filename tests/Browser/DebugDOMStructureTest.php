<?php

declare(strict_types=1);

test('debug DOM structure and class cards', function () {
    $page = visit('/')
        ->assertSee('Character Builder')
        ->wait(3);
    
    // Take a screenshot
    $page->screenshot(filename: 'debug-dom-structure');
    
    // Try to find any class cards with various selectors
    $html = $page->content();
    echo "DOM contains dusk attributes: " . (strpos($html, 'dusk=') !== false ? "YES" : "NO") . "\n";
    echo "DOM contains class-card: " . (strpos($html, 'class-card-') !== false ? "YES" : "NO") . "\n";
    echo "DOM contains 'Brawler' text: " . (strpos($html, 'Brawler') !== false ? "YES" : "NO") . "\n";
    echo "DOM contains 'Assassin' text: " . (strpos($html, 'Assassin') !== false ? "YES" : "NO") . "\n";
    
    // Try to find elements using different approaches
    try {
        $page->assertSee('Brawler');
        echo "✅ Can see Brawler text\n";
    } catch (Exception $e) {
        echo "❌ Cannot see Brawler text\n";
    }
    
    // Try different selector approaches
    try {
        $page->click('Brawler'); // Click by text
        echo "✅ Can click Brawler by text\n";
    } catch (Exception $e) {
        echo "❌ Cannot click Brawler by text: " . $e->getMessage() . "\n";
    }
    
    expect(true)->toBe(true);
});
