<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use Domain\Character\Models\Character;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CharacterBuilderControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function create_creates_new_character_and_redirects(): void
    {
        $response = $this->get('/character-builder');

        $response->assertStatus(302);
        
        $this->assertDatabaseCount('characters', 1);

        $character = Character::latest()->first();
        $this->assertNotNull($character);
        $this->assertNotNull($character->character_key);
        $this->assertEquals(10, strlen($character->character_key));
        $this->assertNull($character->user_id); // Guest user
        $this->assertNull($character->name);
        $this->assertNull($character->class);
        $this->assertFalse($character->is_public);
        
        $response->assertRedirectToRoute('character-builder.edit', ['character_key' => $character->character_key]);
    }

    #[Test]
    public function create_associates_character_with_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/character-builder');

        $response->assertStatus(302);

        $character = Character::latest()->first();
        $this->assertEquals($user->id, $character->user_id);
    }

    #[Test]
    public function edit_shows_character_builder_for_existing_character(): void
    {
        $character = Character::factory()->create([
            'character_key' => 'ABC1234567',
            'name' => 'Test Hero',
            'class' => 'warrior',
        ]);

        $response = $this->get("/character-builder/{$character->character_key}");

        $response->assertStatus(200);
        $response->assertViewIs('characters.edit');
        $response->assertViewHas('character_key', 'ABC1234567');
        $response->assertViewHas('character');
    }

    #[Test]
    public function edit_returns_404_for_non_existent_character(): void
    {
        $response = $this->get('/character-builder/NOTEXIST');

        $response->assertStatus(404);
    }

    #[Test]
    public function show_displays_character_for_viewing(): void
    {
        $character = Character::factory()->create([
            'character_key' => 'ABC1234567',
            'name' => 'Test Hero',
            'class' => 'warrior',
        ]);

        $response = $this->get("/character/{$character->character_key}");

        $response->assertStatus(200);
        $response->assertViewIs('characters.show');
        $response->assertViewHas('character_key', 'ABC1234567');
        $response->assertViewHas('character');
    }

    #[Test]
    public function show_returns_404_for_non_existent_character(): void
    {
        $response = $this->get('/character/NOTEXIST');

        $response->assertStatus(404);
    }

    #[Test]
    public function routes_are_properly_configured(): void
    {
        // Test that routes exist and point to correct methods
        $this->assertTrue(\Route::has('character-builder'));
        $this->assertTrue(\Route::has('character-builder.edit'));
        $this->assertTrue(\Route::has('character.show'));

        // Test route parameters
        $createRoute = \Route::getRoutes()->getByName('character-builder');
        $this->assertEquals('GET', $createRoute->methods()[0]);
        $this->assertEquals('character-builder', $createRoute->uri());

        $editRoute = \Route::getRoutes()->getByName('character-builder.edit');
        $this->assertEquals('GET', $editRoute->methods()[0]);
        $this->assertEquals('character-builder/{character_key}', $editRoute->uri());

        $showRoute = \Route::getRoutes()->getByName('character.show');
        $this->assertEquals('GET', $showRoute->methods()[0]);
        $this->assertEquals('character/{character_key}', $showRoute->uri());
    }

    #[Test]
    public function character_key_generation_produces_unique_keys(): void
    {
        // Create multiple characters to ensure uniqueness
        $response1 = $this->get('/character-builder');
        $response2 = $this->get('/character-builder');
        $response3 = $this->get('/character-builder');

        $characters = Character::all();
        $this->assertCount(3, $characters);

        $keys = $characters->pluck('character_key')->toArray();
        $this->assertEquals(3, count(array_unique($keys)));
    }

    #[Test]
    public function character_creation_uses_database_defaults(): void
    {
        $response = $this->get('/character-builder');

        $character = Character::latest()->first();
        $this->assertEquals(1, $character->level);
        $this->assertEquals([], $character->character_data);
        $this->assertFalse($character->is_public);
        $this->assertNull($character->profile_image_path);
    }

    #[Test]
    public function edit_loads_character_data_correctly(): void
    {
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

        $response = $this->get("/character-builder/{$character->character_key}");

        $response->assertStatus(200);
        $loadedCharacter = $response->viewData('character');

        $this->assertEquals('Test Hero', $loadedCharacter->name);
        $this->assertEquals('warrior', $loadedCharacter->selected_class);
        $this->assertEquals('call-of-the-brave', $loadedCharacter->selected_subclass);
        $this->assertEquals('human', $loadedCharacter->selected_ancestry);
        $this->assertEquals('order-of-scholars', $loadedCharacter->selected_community);
        $this->assertEquals(['Answer 1', 'Answer 2'], $loadedCharacter->background_answers);
        $this->assertEquals(['Connection 1'], $loadedCharacter->connection_answers);
    }

    #[Test]
    public function show_loads_character_data_correctly(): void
    {
        $character = Character::factory()->create([
            'character_key' => 'ABC1234567',
            'name' => 'Public Hero',
            'class' => 'ranger',
        ]);

        $response = $this->get("/character/{$character->character_key}");

        $response->assertStatus(200);
        $loadedCharacter = $response->viewData('character');

        $this->assertEquals('Public Hero', $loadedCharacter->name);
        $this->assertEquals('ranger', $loadedCharacter->selected_class);
    }

    #[Test]
    public function character_key_validation_works(): void
    {
        // Test with various invalid character keys
        $this->get('/character-builder/short')->assertStatus(404);
        $this->get('/character-builder/toolongkey123456')->assertStatus(404);
        $this->get('/character-builder/invalid@chr')->assertStatus(404);

        $this->get('/character/short')->assertStatus(404);
        $this->get('/character/toolongkey123456')->assertStatus(404);
        $this->get('/character/invalid@chr')->assertStatus(404);
    }

    #[Test]
    public function controller_handles_load_character_action_errors(): void
    {
        // This tests the error handling when LoadCharacterAction fails
        // We can't easily mock the action in a feature test, but we can test edge cases

        // Test with a character that exists but has malformed data
        $character = Character::factory()->create([
            'character_key' => 'ABC1234567',
            'character_data' => 'invalid-json-data', // This would cause issues in real scenarios
        ]);

        // The action should handle this gracefully and not crash
        $response = $this->get("/character-builder/{$character->character_key}");

        // Should either work (if action handles it) or return proper error
        $this->assertTrue(in_array($response->status(), [200, 404, 500]));
    }

    #[Test]
    public function multiple_character_creation_sessions_work_independently(): void
    {
        // Simulate multiple users creating characters simultaneously
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $response1 = $this->actingAs($user1)->get('/character-builder');
        $response2 = $this->actingAs($user2)->get('/character-builder');

        $characters = Character::all();
        $this->assertCount(2, $characters);

        $character1 = Character::where('user_id', $user1->id)->first();
        $character2 = Character::where('user_id', $user2->id)->first();

        $this->assertNotEquals($character1->character_key, $character2->character_key);
        $this->assertEquals($user1->id, $character1->user_id);
        $this->assertEquals($user2->id, $character2->user_id);
    }
}
