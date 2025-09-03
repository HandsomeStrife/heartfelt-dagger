<?php

declare(strict_types=1);

// Test that playtest classes are properly labeled
it('shows playtest indicators on class selection grid', function () {
    $page = visit('/character-builder');
    $page->assertPathBeginsWith('/character-builder/');

    // Check for playtest labels on the grid view
    // Based on the JSON data, there should be playtest classes with "Void - Playtest v1.5" labels
    $page->assertSee('Void - Playtest v1.5');
    
    // Verify that playtest badges are visible and styled correctly
    $page->waitForText('Void - Playtest v1.5');
});

it('shows playtest indicator in detailed class view', function () {
    $page = visit('/character-builder');
    $page->assertPathBeginsWith('/character-builder/');

    // Look for a playtest class in the JSON and click it
    // We need to find which classes are playtest from the JSON structure
    $gameData = json_decode(file_get_contents(resource_path('json/classes.json')), true);
    
    $playtestClass = null;
    foreach ($gameData as $classKey => $classData) {
        if (isset($classData['playtest']['isPlaytest']) && $classData['playtest']['isPlaytest']) {
            $playtestClass = $classKey;
            break;
        }
    }
    
    expect($playtestClass)->not->toBeNull('No playtest class found in JSON data');
    
    // Click on the playtest class
    $page->click("[pest=\"class-card-{$playtestClass}\"]");
    $page->wait(1);
    
    // Verify the playtest badge appears in the detailed view
    $page->assertSee('Void - Playtest v1.5');
});

it('distinguishes playtest from non-playtest classes', function () {
    $page = visit('/character-builder');
    $page->assertPathBeginsWith('/character-builder/');

    $gameData = json_decode(file_get_contents(resource_path('json/classes.json')), true);
    
    $regularClass = null;
    $playtestClass = null;
    
    foreach ($gameData as $classKey => $classData) {
        if (isset($classData['playtest']['isPlaytest']) && $classData['playtest']['isPlaytest']) {
            $playtestClass = $classKey;
        } elseif (!isset($classData['playtest']['isPlaytest'])) {
            $regularClass = $classKey;
        }
        
        if ($playtestClass && $regularClass) {
            break;
        }
    }
    
    expect($playtestClass)->not->toBeNull('No playtest class found');
    expect($regularClass)->not->toBeNull('No regular class found');
    
    // Click on regular class first
    $page->click("[pest=\"class-card-{$regularClass}\"]");
    $page->wait(1);
    
    // Should NOT see playtest indicator
    $page->assertDontSee('Void - Playtest v1.5');
    
    // Go back and select playtest class
    $page->click('[pest="change-class-button"]');
    $page->wait(1);
    $page->click("[pest=\"class-card-{$playtestClass}\"]");
    $page->wait(1);
    
    // Should see playtest indicator
    $page->assertSee('Void - Playtest v1.5');
});
