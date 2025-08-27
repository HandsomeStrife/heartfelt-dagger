<?php

declare(strict_types=1);

it('can load the homepage successfully', function () {
    $page = visit('/');
    
    $page->assertSee('DaggerHeart');
});

it('can navigate and interact with basic elements', function () {
    $page = visit('/');
    
    // Basic smoke test - ensure no console errors
    $page->assertNoSmoke();
    
    // Ensure no JavaScript errors
    $page->assertNoJavaScriptErrors();
});
