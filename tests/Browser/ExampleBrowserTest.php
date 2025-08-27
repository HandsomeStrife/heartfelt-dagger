<?php

test('can visit homepage', function () {
    $page = visit('/');
    
    $page->assertSee('Daggerheart');
});

test('can visit character builder', function () {
    $page = visit('/character-builder');
    
    $page
        ->assertSee('Character Builder')
        ->assertPresent('[dusk="progress-bar"]');
});
