<?php

declare(strict_types=1);

use App\Livewire\ReferenceSearch;
use Livewire\Livewire;

describe('Reference Search Livewire Component', function () {
    test('component renders correctly', function () {
        Livewire::test(ReferenceSearch::class)
            ->assertViewIs('livewire.reference-search')
            ->assertSee('Search reference pages...');
    });
    
    test('component handles sidebar mode', function () {
        Livewire::test(ReferenceSearch::class, ['isSidebar' => true])
            ->assertSet('is_sidebar', true);
    });
    
    test('search query updates trigger search', function () {
        Livewire::test(ReferenceSearch::class)
            ->set('search_query', 'combat')
            ->assertSet('search_query', 'combat');
    });
    
    test('clear search resets component state', function () {
        Livewire::test(ReferenceSearch::class)
            ->set('search_query', 'combat')
            ->set('show_results', true)
            ->call('clearSearch')
            ->assertSet('search_query', '')
            ->assertSet('show_results', false)
            ->assertSet('selected_page_key', '');
    });
    
    test('component initializes with correct defaults', function () {
        Livewire::test(ReferenceSearch::class)
            ->assertSet('search_query', '')
            ->assertSet('search_results', [])
            ->assertSet('show_results', false)
            ->assertSet('selected_page_key', '')
            ->assertSet('selected_page_content', [])
            ->assertSet('is_sidebar', false);
    });
});
