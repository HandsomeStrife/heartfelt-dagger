<?php

declare(strict_types=1);

test('reference search works in browser', function () {
    visit('/reference')
        ->assertSee('Search reference pages...')
        ->type('input[dusk="searchInput"]', 'strength')
        ->pause(1000) // Wait for debounced search and results
        ->waitFor('[dusk="searchResult"]', 5) // Wait up to 5 seconds for search results
        ->assertSee('result')
        ->screenshot('reference-search-working');
})->tags(['browser']);

test('reference search shows dropdown results', function () {
    visit('/reference')
        ->type('input[dusk="searchInput"]', 'combat')
        ->pause(1000)
        ->waitFor('[dusk="searchResult"]', 5)
        ->assertSee('Combat')
        ->click('[dusk="searchResult"]')
        ->pause(500)
        ->screenshot('reference-search-clicked');
})->tags(['browser']);
