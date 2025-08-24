<?php

declare(strict_types=1);

test('debug homepage content', function () {
    $page = visit('/');
    
    // Take a screenshot to see what's rendered
    $page->screenshot(filename: 'debug-homepage');
    
    // Get the page title
    $title = $page->script('document.title');
    echo "Page title: " . $title . "\n";
    
    // Check if we can find any common text
    $bodyText = $page->text('body');
    echo "Body contains: " . substr($bodyText, 0, 200) . "...\n";
    
    // Just assert something basic
    expect(true)->toBe(true);
});
