<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};
use Domain\Room\Models\Room;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};
use Domain\User\Models\User;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};

test('character dropdown shows compact character list with class info', function () {
    $user = User::factory()->create();
    $room = Room::factory()->passwordless()->create();
    
    // Create characters with different classes and subclasses
    Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Bob the Orc',
        'class' => 'Warrior',
        'subclass' => null,
    ]);
    
    Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Tony the Tiger',
        'class' => 'Ranger',
        'subclass' => 'beastbound',
    ]);

    $response = actingAs($user)->get("/rooms/join/{$room->invite_code}");
    
    $response->assertOk();
    
    // Should show dropdown with character info
    $response->assertSee('Bob the Orc (Warrior)'); // Character without subclass
    $response->assertSee('Tony the Tiger (Ranger) - beastbound'); // Character with subclass
    $response->assertSee('Create temporary character'); // Temporary option
});

test('character dropdown includes temporary character option', function () {
    $user = User::factory()->create();
    $room = Room::factory()->passwordless()->create();
    
    Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Test Character',
        'class' => 'Wizard',
    ]);

    $response = actingAs($user)->get("/rooms/join/{$room->invite_code}");
    
    $response->assertOk();
    
    // Should include both existing character and temporary option
    $content = $response->getContent();
    expect($content)->toContain('<option value="">Select an existing character</option>');
    expect($content)->toContain('Test Character (Wizard)');
    expect($content)->toContain('<option value="temporary">Create temporary character</option>');
});

test('dropdown form submission works with existing character selection', function () {
    $user = User::factory()->create();
    $room = Room::factory()->passwordless()->create();
    
    $character = Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Selected Character',
        'class' => 'Bard',
    ]);

    // Submit form with character selected from dropdown
    $response = actingAs($user)->post(route('rooms.join', $room), [
        'character_id' => $character->id
    ]);

    $response->assertRedirect(route('rooms.session', $room));
    
    assertDatabaseHas('room_participants', [
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_id' => $character->id
    ]);
});

test('dropdown form submission works with temporary character option', function () {
    $user = User::factory()->create();
    $room = Room::factory()->passwordless()->create();
    
    // Create an existing character but choose temporary instead
    Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Existing Character',
        'class' => 'Sorcerer',
    ]);

    // Submit form with temporary character (empty character_id)
    $response = actingAs($user)->post(route('rooms.join', $room), [
        'character_id' => '', // Empty for temporary
        'character_name' => 'Temp Character',
        'character_class' => 'Guardian'
    ]);

    $response->assertRedirect(route('rooms.session', $room));
    
    assertDatabaseHas('room_participants', [
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_name' => 'Temp Character',
        'character_class' => 'Guardian',
        'character_id' => null
    ]);
});
