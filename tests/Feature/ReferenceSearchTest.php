<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

describe('Reference Search', function () {
    test('search returns empty array for short queries', function () {
        $response = $this->get('/reference/search?q=a');
        
        $response->assertStatus(200);
        $response->assertJson([]);
    });
    
    test('search returns results for valid queries', function () {
        $response = $this->get('/reference/search?q=combat');
        
        $response->assertStatus(200);
        $data = $response->json();
        
        expect($data)->toBeArray();
        // Should find combat-related pages
        $combatResults = collect($data)->filter(function ($result) {
            return str_contains(strtolower($result['title'] ?? ''), 'combat');
        });
        
        expect($combatResults)->not->toBeEmpty();
    });
    
    test('search handles class names correctly', function () {
        $response = $this->get('/reference/search?q=warrior');
        
        $response->assertStatus(200);
        $data = $response->json();
        
        expect($data)->toBeArray();
        
        // Should find warrior class
        $warriorResults = collect($data)->filter(function ($result) {
            return str_contains(strtolower($result['title'] ?? ''), 'warrior');
        });
        
        expect($warriorResults)->not->toBeEmpty();
    });
    
    test('search handles domain abilities', function () {
        $response = $this->get('/reference/search?q=blade strike');
        
        $response->assertStatus(200);
        $data = $response->json();
        
        expect($data)->toBeArray();
        // May find blade domain abilities
    });
    
    test('search returns structured results', function () {
        $response = $this->get('/reference/search?q=weapon');
        
        $response->assertStatus(200);
        $data = $response->json();
        
        expect($data)->toBeArray();
        
        if (count($data) > 0) {
            $firstResult = $data[0];
            expect($firstResult)->toHaveKeys(['key', 'title', 'type', 'score']);
            expect($firstResult['type'])->toBeIn(['page', 'section', 'content', 'environment', 'frame']);
        }
    });
});
