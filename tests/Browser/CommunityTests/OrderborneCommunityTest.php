<?php

declare(strict_types=1);

require_once __DIR__ . '/../Helpers/CharacterBuilderHelpers.php';

use function Tests\Browser\Helpers\{
    waitForCharacterBuilderToLoad,
    selectClass,
    selectAncestry,
    selectCommunity
};

test('orderborne community shows dedicated feature', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    selectClass($page, 'warrior');
    
    // Navigate through steps to community selection  
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
    selectAncestry($page, 'human');
    
    selectCommunity($page, 'orderborne');
    
    // Should see Orderborne-specific features
    $page->assertSee('Orderborne')
        ->assertSee('d20');
});

test('orderborne community dedicated feature shows value recording', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    selectClass($page, 'warrior');
    
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
    selectAncestry($page, 'human');
    
    selectCommunity($page, 'orderborne');
    
    // Should see the Dedicated feature description with values recording
    $page->assertSee('three sayings')
        ->assertSee('d20');
});

test('orderborne community shows correct description', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    selectClass($page, 'warrior');
    
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
    selectAncestry($page, 'human');
    
    selectCommunity($page, 'orderborne');
    
    // Should see Orderborne description
    $page->assertSee('Being part of an orderborne community means you\'re from a collective that focuses on discipline or faith');
});
