<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use Domain\User\Models\User;
use function Pest\Laravel\actingAs;

test('authenticated user can access characters page', function () {
    $user = User::factory()->create();
    
    actingAs($user);
    $page = visit('/characters');
    
    $page->wait(3)
        ->assertSee('Characters'); // Should see characters page content
});

test('authenticated user can view character builder', function () {
    $user = User::factory()->create();
    
    actingAs($user);
    $page = visit('/character-builder');
    
    $page->wait(3)
        ->assertSee('Character Builder');
});

test('authenticated user can view their character', function () {
    $user = User::factory()->create();
    
    // Create a character owned by the user
    $character = Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'My Character',
        'class' => 'warrior',
    ]);
    
    actingAs($user);
    $page = visit("/character/{$character->public_key}");
    
    $page->wait(3)
        ->assertSee('My Character');
});
