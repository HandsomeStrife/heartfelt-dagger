<?php

declare(strict_types=1);

test('homepage loads correctly', function () {
    $page = visit('/');
    
    // Check for the actual title we see
    $page->assertTitle('HeartfeltDagger');
    
    // Wait for any JavaScript to finish and check for basic content
    $page->wait(2);
    
    // Try to find some content that should be on the character builder
    $page->assertSee('Character Builder');
});