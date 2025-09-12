<?php

declare(strict_types=1);

use function Pest\Laravel\get;

test('reference page loads with search functionality', function () {
    visit('/reference')
        ->assertSee('DaggerHeart System Reference')
        ->assertSee('Search reference pages...')
        ->type('@searchInput', 'combat')
        ->pause(500) // Wait for debounced search
        ->assertSee('result')
        ->screenshot('reference-search-results');
})->tags(['browser']);

test('reference search shows relevant results', function () {
    visit('/reference')
        ->type('@searchInput', 'warrior')
        ->pause(500)
        ->assertSee('Warrior')
        ->click('@searchResult')
        ->assertUrlContains('/reference/classes');
})->tags(['browser']);
