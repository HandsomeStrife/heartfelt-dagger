<?php

declare(strict_types=1);

test('character deletion API works in testing environment', function () {
    $character = \Domain\Character\Models\Character::factory()->create([
        'user_id' => null, // Anonymous character
    ]);

    // Note: Laravel's testing framework bypasses CSRF middleware by default
    // This is expected behavior - CSRF protection works in real browsers
    $response = $this->delete("/api/character/{$character->character_key}", [], [
        'X-Requested-With' => 'XMLHttpRequest',
        'Content-Type' => 'application/json',
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Character deleted successfully']);

    // Verify character was deleted
    $this->assertDatabaseMissing('characters', ['id' => $character->id]);
});

test('character deletion API works with valid CSRF token', function () {
    $character = \Domain\Character\Models\Character::factory()->create([
        'user_id' => null, // Anonymous character
    ]);

    // Request with CSRF token should succeed
    $response = $this->delete("/api/character/{$character->character_key}", [], [
        'X-Requested-With' => 'XMLHttpRequest',
        'Content-Type' => 'application/json',
        'X-CSRF-TOKEN' => csrf_token(),
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Character deleted successfully']);

    // Verify character was deleted
    $this->assertDatabaseMissing('characters', ['id' => $character->id]);
});

test('characters page includes CSRF token meta tag', function () {
    $page = visit('/characters');

    $page->assertSee('Your Characters');

    // Check that CSRF token meta tag exists in the page source
    $content = $page->content();
    expect($content)->toContain('meta name="csrf-token"');
    expect($content)->toContain('content=');
});
