<?php

declare(strict_types=1);

test('homepage loads without campaign frame errors', function () {
    $page = visit('/');
    
    $page->assertSee('Daggerheart')
        ->assertNoJavaScriptErrors()
        ->assertNoSmoke();
})->group('browser');

test('character builder loads without campaign frame interference', function () {
    $page = visit('/character-builder');
    
    $page->assertSee('Character Builder')
        ->assertNoJavaScriptErrors();
})->group('browser');
