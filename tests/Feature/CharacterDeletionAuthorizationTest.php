<?php

declare(strict_types=1);

use function Pest\Laravel\actingAs;

test('authenticated user can delete their own character via API', function () {
    $user = \Domain\User\Models\User::factory()->create();
    $character = \Domain\Character\Models\Character::factory()->create([
        'user_id' => $user->id,
    ]);

    actingAs($user);

    $response = $this->delete("/api/character/{$character->character_key}");

    $response->assertStatus(200)
             ->assertJson(['message' => 'Character deleted successfully']);

    $this->assertDatabaseMissing('characters', ['id' => $character->id]);
});

test('authenticated user cannot delete character owned by another user via API', function () {
    $user = \Domain\User\Models\User::factory()->create();
    $otherUser = \Domain\User\Models\User::factory()->create();
    $character = \Domain\Character\Models\Character::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    actingAs($user);

    $response = $this->delete("/api/character/{$character->character_key}");

    $response->assertStatus(403)
             ->assertJson(['error' => 'Unauthorized']);

    $this->assertDatabaseHas('characters', ['id' => $character->id]);
});

test('anonymous user can delete anonymous character via API', function () {
    $character = \Domain\Character\Models\Character::factory()->create([
        'user_id' => null, // Anonymous character
    ]);

    $response = $this->delete("/api/character/{$character->character_key}");

    $response->assertStatus(200)
             ->assertJson(['message' => 'Character deleted successfully']);

    $this->assertDatabaseMissing('characters', ['id' => $character->id]);
});

test('anonymous user cannot delete character owned by authenticated user via API', function () {
    $user = \Domain\User\Models\User::factory()->create();
    $character = \Domain\Character\Models\Character::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->delete("/api/character/{$character->character_key}");

    $response->assertStatus(403)
             ->assertJson(['error' => 'Unauthorized']);

    $this->assertDatabaseHas('characters', ['id' => $character->id]);
});

test('API returns 404 for non-existent character', function () {
    $user = \Domain\User\Models\User::factory()->create();
    actingAs($user);

    $response = $this->delete('/api/character/non-existent-key');

    $response->assertStatus(404)
             ->assertJson(['error' => 'Character not found']);
});
