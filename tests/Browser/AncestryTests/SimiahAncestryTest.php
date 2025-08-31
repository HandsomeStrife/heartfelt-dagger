<?php

declare(strict_types=1);

require_once __DIR__ . '/../Helpers/CharacterBuilderHelpers.php';

use function Tests\Browser\Helpers\{
    waitForCharacterBuilderToLoad,
    selectClass,
    selectAncestry
};

test('simiah ancestry provides evasion bonus', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    // Select a class first to establish baseline stats
    selectClass($page, 'warrior');
    
    // Navigate to ancestry selection and select Simiah
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
        
    selectAncestry($page, 'simiah');
    
    // Should see Simiah-specific features
    $page->assertSee('Simiah')
        ->assertSee('Natural Climber')
        ->assertSee('Nimble');
    
    // Check that evasion bonus is mentioned
    $page->assertSee('Evasion');
});

test('simiah ancestry natural climber feature is displayed', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    selectClass($page, 'warrior');
    
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
        
    selectAncestry($page, 'simiah');
    
    // Should see the Natural Climber feature description
    $page->assertSee('Natural Climber')
        ->assertSee('You have advantage on Agility Rolls that involve balancing and climbing');
});

test('simiah ancestry shows correct description', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    selectClass($page, 'warrior');
    
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
        
    selectAncestry($page, 'simiah');
    
    // Should see Simiah description
    $page->assertSee('Simiah resemble anthropomorphic monkeys and apes');
});

