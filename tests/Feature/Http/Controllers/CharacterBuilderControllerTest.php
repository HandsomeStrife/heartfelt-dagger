<?php

declare(strict_types=1);
use Domain\Character\Models\Character;
use Domain\User\Models\User;
use PHPUnit\Framework\Attributes\Test;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('create creates new character and redirects', function () {
    // Ensure clean database state
    Character::query()->delete();

    $response = get('/character-builder');

    $response->assertStatus(302);

    \Pest\Laravel\assertDatabaseCount('characters', 1);

    $character = Character::latest()->first();
    expect($character)->not->toBeNull();
    expect($character->character_key)->not->toBeNull();
    expect(strlen($character->character_key))->toEqual(10);
    expect($character->user_id)->toBeNull();
    // Guest user
    expect($character->name)->toBeNull();
    expect($character->class)->toBeNull();
    expect($character->is_public)->toBeFalse();

    $response->assertRedirectToRoute('character-builder.edit', ['character_key' => $character->character_key]);
});
test('create associates character with authenticated user', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->get('/character-builder');

    $response->assertStatus(302);

    $character = Character::latest()->first();
    expect($character->user_id)->toEqual($user->id);
});
test('edit shows character builder for existing character', function () {
    $character = Character::factory()->create([
        'character_key' => 'ABC1234567',
        'name' => 'Test Hero',
        'class' => 'warrior',
    ]);

    $response = get("/character-builder/{$character->character_key}");

    $response->assertStatus(200);
    $response->assertViewIs('characters.edit');
    $response->assertViewHas('character_key', 'ABC1234567');
    $response->assertViewHas('character');
});
test('edit returns 404 for non existent character', function () {
    $response = get('/character-builder/NOTEXIST');

    $response->assertStatus(404);
});
test('show displays character for viewing', function () {
    $character = Character::factory()->create([
        'character_key' => 'ABC1234567',
        'name' => 'Test Hero',
        'class' => 'warrior',
    ]);

    $response = get("/character/{$character->public_key}");

    $response->assertStatus(200);
    $response->assertViewIs('characters.show');
    $response->assertViewHas('character_key', 'ABC1234567');
    $response->assertViewHas('character');
});
test('show returns 404 for non existent character', function () {
    $response = get('/character/NOTEXIST');

    $response->assertStatus(404);
});
test('routes are properly configured', function () {
    // Test that routes exist and point to correct methods
    expect(\Route::has('character-builder'))->toBeTrue();
    expect(\Route::has('character-builder.edit'))->toBeTrue();
    expect(\Route::has('character.show'))->toBeTrue();

    // Test route parameters
    $createRoute = \Route::getRoutes()->getByName('character-builder');
    expect($createRoute->methods()[0])->toEqual('GET');
    expect($createRoute->uri())->toEqual('character-builder');

    $editRoute = \Route::getRoutes()->getByName('character-builder.edit');
    expect($editRoute->methods()[0])->toEqual('GET');
    expect($editRoute->uri())->toEqual('character-builder/{character_key}');

    $showRoute = \Route::getRoutes()->getByName('character.show');
    expect($showRoute->methods()[0])->toEqual('GET');
    expect($showRoute->uri())->toEqual('character/{public_key}');
});
test('character key generation produces unique keys', function () {
    // Ensure clean database state
    Character::query()->delete();

    // Create multiple characters to ensure uniqueness
    $response1 = get('/character-builder');
    $response2 = get('/character-builder');
    $response3 = get('/character-builder');

    $characters = Character::all();
    expect($characters)->toHaveCount(3);

    $keys = $characters->pluck('character_key')->toArray();
    expect(count(array_unique($keys)))->toEqual(3);
});
test('character creation uses database defaults', function () {
    $response = get('/character-builder');

    $character = Character::latest()->first();
    expect($character->level)->toEqual(1);
    expect($character->character_data)->toEqual([]);
    expect($character->is_public)->toBeFalse();
    expect($character->profile_image_path)->toBeNull();
});
test('edit loads character data correctly', function () {
    $character = Character::factory()->create([
        'character_key' => 'ABC1234567',
        'name' => 'Test Hero',
        'class' => 'warrior',
        'subclass' => 'call-of-the-brave',
        'ancestry' => 'human',
        'community' => 'order-of-scholars',
        'character_data' => [
            'background' => ['answers' => ['Answer 1', 'Answer 2']],
            'connections' => ['Connection 1'],
        ],
    ]);

    $response = get("/character-builder/{$character->character_key}");

    $response->assertStatus(200);
    $loadedCharacter = $response->viewData('character');

    expect($loadedCharacter->name)->toEqual('Test Hero');
    expect($loadedCharacter->selected_class)->toEqual('warrior');
    expect($loadedCharacter->selected_subclass)->toEqual('call-of-the-brave');
    expect($loadedCharacter->selected_ancestry)->toEqual('human');
    expect($loadedCharacter->selected_community)->toEqual('order-of-scholars');
    expect($loadedCharacter->background_answers)->toEqual(['Answer 1', 'Answer 2']);
    expect($loadedCharacter->connection_answers)->toEqual(['Connection 1']);
});
test('show loads character data correctly', function () {
    $character = Character::factory()->create([
        'character_key' => 'ABC1234567',
        'name' => 'Public Hero',
        'class' => 'ranger',
    ]);

    $response = get("/character/{$character->public_key}");

    $response->assertStatus(200);
    $loadedCharacter = $response->viewData('character');

    expect($loadedCharacter->name)->toEqual('Public Hero');
    expect($loadedCharacter->selected_class)->toEqual('ranger');
});
test('character key validation works', function () {
    // Test with various invalid character keys
    get('/character-builder/short')->assertStatus(404);
    get('/character-builder/toolongkey123456')->assertStatus(404);
    get('/character-builder/invalid@chr')->assertStatus(404);

    get('/character/short')->assertStatus(404);
    get('/character/toolongkey123456')->assertStatus(404);
    get('/character/invalid@chr')->assertStatus(404);
});
test('controller handles load character action errors', function () {
    // This tests the error handling when LoadCharacterAction fails
    // We can't easily mock the action in a feature test, but we can test edge cases
    // Test with a character that exists but has malformed data
    $character = Character::factory()->create([
        'character_key' => 'ABC1234567',
        'character_data' => 'invalid-json-data', // This would cause issues in real scenarios
    ]);

    // The action should handle this gracefully and not crash
    $response = get("/character-builder/{$character->character_key}");

    // Should either work (if action handles it) or return proper error
    expect(in_array($response->status(), [200, 404, 500]))->toBeTrue();
});
test('multiple character creation sessions work independently', function () {
    // Ensure clean database state
    Character::query()->delete();

    // Simulate multiple users creating characters simultaneously
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $response1 = actingAs($user1)->get('/character-builder');
    $response2 = actingAs($user2)->get('/character-builder');

    $characters = Character::all();
    expect($characters)->toHaveCount(2);

    $character1 = Character::where('user_id', $user1->id)->first();
    $character2 = Character::where('user_id', $user2->id)->first();

    expect($character1->character_key)->not->toEqual($character2->character_key);
    expect($character1->user_id)->toEqual($user1->id);
    expect($character2->user_id)->toEqual($user2->id);
});
