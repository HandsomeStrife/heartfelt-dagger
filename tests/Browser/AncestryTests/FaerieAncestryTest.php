<?php

declare(strict_types=1);

require_once __DIR__ . '/../Helpers/CharacterBuilderHelpers.php';

use function Tests\Browser\Helpers\{
    waitForCharacterBuilderToLoad,
    selectClass,
    selectAncestry
};

test('faerie ancestry shows luckbender and wings features', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    // Select a class first to establish baseline stats
    selectClass($page, 'warrior');
    
    // Navigate to ancestry selection and select Faerie
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
        
    selectAncestry($page, 'faerie');
    
    // Should see Faerie-specific features
    $page->assertSee('Faerie')
        ->assertSee('Luckbender')
        ->assertSee('Wings');
});

test('faerie ancestry luckbender feature is displayed correctly', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    selectClass($page, 'warrior');
    
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
        
    selectAncestry($page, 'faerie');
    
    // Should see the Luckbender feature description
    $page->assertSee('Luckbender')
        ->assertSee('Once per session')
        ->assertSee('spend 3 Hope')
        ->assertSee('reroll the Duality Dice');
});

test('faerie ancestry wings feature is displayed correctly', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    selectClass($page, 'warrior');
    
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
        
    selectAncestry($page, 'faerie');
    
    // Should see the Wings feature description
    $page->assertSee('Wings')
        ->assertSee('You can fly')
        ->assertSee('mark a Stress')
        ->assertSee('+2 bonus to your Evasion');
});

test('faerie ancestry shows correct description', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    selectClass($page, 'warrior');
    
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
        
    selectAncestry($page, 'faerie');
    
    // Should see Faerie description
    $page->assertSee('Faeries are winged humanoid creatures with insectile features');
});

