<?php

declare(strict_types=1);
use Domain\Character\Actions\AssociateCharactersWithUserAction;
use Domain\Character\Models\Character;
use Domain\User\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->action = new AssociateCharactersWithUserAction;
});
it('associates anonymous characters with user', function () {
    // Create a user
    $user = User::factory()->create();

    // Create some anonymous characters (user_id = null)
    $character1 = Character::factory()->create(['user_id' => null]);
    $character2 = Character::factory()->create(['user_id' => null]);
    $character3 = Character::factory()->create(['user_id' => null]);

    // Create a character that already belongs to another user
    $another_user = User::factory()->create();
    $character4 = Character::factory()->create(['user_id' => $another_user->id]);

    $character_keys = [
        $character1->character_key,
        $character2->character_key,
        $character3->character_key,
        $character4->character_key, // This should not be associated
    ];

    // Execute the action
    $updated_count = $this->action->execute($user, $character_keys);

    // Assert that only 3 characters were updated (not the one that already had a user)
    expect($updated_count)->toEqual(3);

    // Assert that the characters are now associated with the user
    expect($character1->fresh()->user_id)->toEqual($user->id);
    expect($character2->fresh()->user_id)->toEqual($user->id);
    expect($character3->fresh()->user_id)->toEqual($user->id);

    // Assert that the character that already had a user is unchanged
    expect($character4->fresh()->user_id)->toEqual($another_user->id);
});
it('returns zero when no character keys provided', function () {
    $user = User::factory()->create();

    $updated_count = $this->action->execute($user, []);

    expect($updated_count)->toEqual(0);
});
it('returns zero when no matching characters found', function () {
    $user = User::factory()->create();

    $updated_count = $this->action->execute($user, ['non-existent-key-1', 'non-existent-key-2']);

    expect($updated_count)->toEqual(0);
});
it('only associates characters with null user id', function () {
    $user = User::factory()->create();
    $another_user = User::factory()->create();

    // Create a character that already belongs to another user
    $existing_character = Character::factory()->create(['user_id' => $another_user->id]);

    $updated_count = $this->action->execute($user, [$existing_character->character_key]);

    // Should not update any characters since it already has a user
    expect($updated_count)->toEqual(0);

    // Character should still belong to the original user
    expect($existing_character->fresh()->user_id)->toEqual($another_user->id);
});
it('handles mixed valid and invalid character keys', function () {
    $user = User::factory()->create();

    // Create some characters
    $anonymous_character = Character::factory()->create(['user_id' => null]);
    $owned_character = Character::factory()->create(['user_id' => $user->id]);

    $character_keys = [
        $anonymous_character->character_key,
        'non-existent-key',
        $owned_character->character_key,
    ];

    $updated_count = $this->action->execute($user, $character_keys);

    // Only the anonymous character should be updated
    expect($updated_count)->toEqual(1);
    expect($anonymous_character->fresh()->user_id)->toEqual($user->id);
});
