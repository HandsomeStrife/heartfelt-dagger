<?php

declare(strict_types=1);

require_once __DIR__ . '/../Helpers/CharacterBuilderHelpers.php';

use function Tests\Browser\Helpers\{
    waitForCharacterBuilderToLoad,
    selectClass,
    selectAncestry,
    selectCommunity
};

test('slyborne community shows scoundrel feature', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    selectClass($page, 'warrior');
    
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
    selectAncestry($page, 'human');
    
    selectCommunity($page, 'slyborne');
    
    // Should see Slyborne-specific features
    $page->assertSee('Slyborne')
        ->assertSee('criminals');
});

test('slyborne community scoundrel shows advantage mechanics', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    selectClass($page, 'warrior');
    
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
    selectAncestry($page, 'human');
    
    selectCommunity($page, 'slyborne');
    
    // Should see the Scoundrel feature with advantage mechanics
    $page->assertSee('advantage')
        ->assertSee('criminals');
});

test('slyborne community shows correct description', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    selectClass($page, 'warrior');
    
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
    selectAncestry($page, 'human');
    
    selectCommunity($page, 'slyborne');
    
    // Should see Slyborne description
    $page->assertSee('Being part of a slyborne community means you come from a group that operates outside the law');
});
