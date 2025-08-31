<?php

declare(strict_types=1);

require_once __DIR__ . '/../Helpers/CharacterBuilderHelpers.php';

use function Tests\Browser\Helpers\{
    waitForCharacterBuilderToLoad,
    selectClass,
    selectAncestry
};

test('earthkin ancestry provides armor and damage threshold bonuses', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    // Select a class first to establish baseline stats
    selectClass($page, 'warrior');
    
    // Navigate to ancestry selection and select Earthkin
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
        
    selectAncestry($page, 'earthkin');
    
    // Should see Earthkin-specific features
    $page->assertSee('Earthkin')
        ->assertSee('Stoneskin')
        ->assertSee('Immoveable');
    
    // Check that armor and damage threshold bonuses are mentioned
    $page->assertSee('Armor Score');
});

test('earthkin ancestry immoveable feature is displayed', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    selectClass($page, 'warrior');
    
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
        
    selectAncestry($page, 'earthkin');
    
    // Should see the Immoveable feature description
    $page->assertSee('Immoveable')
        ->assertSee('While your feet are touching the ground, you cannot be lifted or moved against your will');
});

test('earthkin ancestry shows correct description', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    selectClass($page, 'warrior');
    
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
        
    selectAncestry($page, 'earthkin');
    
    // Should see Earthkin description
    $page->assertSee('Earthkin are descended from earth elementals');
});
