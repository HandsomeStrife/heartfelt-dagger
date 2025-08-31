<?php

declare(strict_types=1);

test('basic playwright setup works', function () {
    $page = visit('/');
    
    // Test that basic browser operations work
    $page->assertSee('Character Builder')
        ->assertNoJavaScriptErrors();
});

test('javascript evaluation works', function () {
    $page = visit('/');
    
    // Test JavaScript execution (must be wrapped in a function)
    $result = $page->script('() => "test"');
    expect($result)->toBe('test');
});

test('page navigation and interaction works', function () {
    $page = visit('/');
    
    $page->wait(2)
        ->assertSee('Character Builder');
    
    // Test navigation
    $page->navigate('/login')
        ->wait(1)
        ->assertSee('Enter the Realm');
});

test('multiple operations work in sequence', function () {
    $page = visit('/');
    
    // Chain multiple operations
    $page->assertSee('Character Builder')
        ->wait(1);
    
    // Test script execution (separate since it returns a value)
    $title = $page->script('() => document.title');
    expect($title)->toBe('HeartfeltDagger');
    
    $page->navigate('/login')
        ->wait(1)
        ->assertSee('Enter the Realm');
});
