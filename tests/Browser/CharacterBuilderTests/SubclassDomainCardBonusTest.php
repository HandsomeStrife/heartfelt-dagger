<?php

declare(strict_types=1);
use Domain\Character\Models\Character;
use Domain\User\Models\User;


test('school of knowledge character shows domain card bonus in ui', function () {
    $user = User::factory()->create();
    $character = Character::factory()->for($user)->create([
        'character_key' => 'TEST123456',
        'class' => 'wizard',
        'subclass' => 'school of knowledge',
    ]);

    $page = visit('/character-builder/TEST123456');
    
    $page
            ->assertSee('Select Domain Cards')
            ->assertSee('Choose 5 starting domain cards')
            ->assertSee('includes 3 bonus cards from School of knowledge');
});

test('regular subclass character shows normal domain card limit', function () {
    $user = User::factory()->create();
    $character = Character::factory()->for($user)->create([
        'character_key' => 'TEST789012',
        'class' => 'warrior',
        'subclass' => 'stalwart',
    ]);

    $page = visit('/character-builder/TEST789012');
    
    $page
            ->assertSee('Select Domain Cards')
            ->assertSee('Choose 2 starting domain cards')
            ->assertDontSee('bonus cards from');
});

test('school of knowledge character can navigate to domain card step', function () {
    $user = User::factory()->create();
    $character = Character::factory()->for($user)->create([
        'character_key' => 'TEST345678',
        'class' => 'wizard',
        'subclass' => 'school of knowledge',
    ]);

    $page = visit('/character-builder/TEST345678');
    
    $page
            ->assertSee('Character Builder')
            ->assertPresent('.character-builder-content');

        // Navigate through steps to reach domain cards
        if ($page->element('[data-step="class"]')) {
            $page->click('[data-step="class"]');
        }
        
        // Just verify we can get to the domain card selection page
        $page->assertSee('domain', 10); // Wait up to 10 seconds for domain-related content
});