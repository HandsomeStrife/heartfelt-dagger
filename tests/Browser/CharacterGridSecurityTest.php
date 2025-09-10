<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use Domain\User\Models\User;

it('prevents guest users from seeing characters owned by authenticated users', function () {
    // Create a user and character owned by that user
    $user = User::factory()->create();
    $ownedCharacter = Character::factory()->create(['user_id' => $user->id]);

    // Create an anonymous character
    $anonymousCharacter = Character::factory()->create(['user_id' => null]);

    // Visit the character grid as a guest user with localStorage containing both character keys
    $page = visit('/characters');
    $page->script("
        localStorage.setItem('daggerheart_characters', JSON.stringify(['{$ownedCharacter->character_key}', '{$anonymousCharacter->character_key}']));
        window.dispatchEvent(new CustomEvent('load-characters-from-storage'));
    ");
    $page->wait(2);

    // Should only see the anonymous character, not the owned character
    $page->assertDontSee($ownedCharacter->name);
    $page->assertSee($anonymousCharacter->name);
});

it('prevents authenticated users from seeing characters owned by other users', function () {
    // Create two users
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    // Create characters owned by each user
    $user1Character = Character::factory()->create(['user_id' => $user1->id]);
    $user2Character = Character::factory()->create(['user_id' => $user2->id]);
    $anonymousCharacter = Character::factory()->create(['user_id' => null]);

    // Log in as user1
    $this->actingAs($user1);

    // Visit the character grid
    $page = visit('/characters');
    $page->wait(2);

    // Should only see user1's character, not user2's character or anonymous character
    $page->assertSee($user1Character->name);
    $page->assertDontSee($user2Character->name);
    $page->assertDontSee($anonymousCharacter->name);
});

it('allows authenticated users to see only their own characters from database', function () {
    // Create a user
    $user = User::factory()->create();

    // Create multiple characters owned by the user
    $character1 = Character::factory()->create(['user_id' => $user->id]);
    $character2 = Character::factory()->create(['user_id' => $user->id]);

    // Create characters owned by another user
    $otherUser = User::factory()->create();
    $otherCharacter = Character::factory()->create(['user_id' => $otherUser->id]);

    // Log in as the first user
    $this->actingAs($user);

    // Visit the character grid
    $page = visit('/characters');
    $page->wait(2);

    // Should see both of user's characters, but not the other user's character
    $page->assertSee($character1->name);
    $page->assertSee($character2->name);
    $page->assertDontSee($otherCharacter->name);
});

it('allows guest users to see only anonymous characters from localStorage', function () {
    // Create an anonymous character and an owned character
    $anonymousCharacter = Character::factory()->create(['user_id' => null]);
    $ownedCharacter = Character::factory()->create(['user_id' => User::factory()->create()->id]);

    // Visit the character grid as guest with both keys in localStorage
    $page = visit('/characters');
    $page->script("
        localStorage.setItem('daggerheart_characters', JSON.stringify(['{$anonymousCharacter->character_key}', '{$ownedCharacter->character_key}']));
        window.dispatchEvent(new CustomEvent('load-characters-from-storage'));
    ");
    $page->wait(2);

    // Should only see the anonymous character
    $page->assertSee($anonymousCharacter->name);
    $page->assertDontSee($ownedCharacter->name);
});

it('shows no characters for guests when localStorage contains only owned characters', function () {
    // Create characters owned by users
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $character1 = Character::factory()->create(['user_id' => $user1->id]);
    $character2 = Character::factory()->create(['user_id' => $user2->id]);

    // Visit the character grid as guest with only owned character keys
    $page = visit('/characters');
    $page->script("
        localStorage.setItem('daggerheart_characters', JSON.stringify(['{$character1->character_key}', '{$character2->character_key}']));
        window.dispatchEvent(new CustomEvent('load-characters-from-storage'));
    ");
    $page->wait(2);

    // Should show "No Characters Yet" message
    $page->assertSee('No Characters Yet');
    $page->assertDontSee($character1->name);
    $page->assertDontSee($character2->name);
});
