<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};

test('room creator can access password-protected room overview without password', function () {
    $user = User::factory()->create();
    $password = 'testpassword';
    $room = Room::factory()->create([
        'creator_id' => $user->id,
        'password' => Hash::make($password),
        'campaign_id' => null,
    ]);

    // Creator should be able to access room overview without providing password
    $response = actingAs($user)->get("/rooms/{$room->invite_code}");
    
    $response->assertOk();
    $response->assertViewIs('rooms.show');
    $response->assertViewHas('room');
    $response->assertViewHas('user_is_creator', true);
});

test('non-creator cannot access password-protected room overview without password', function () {
    $creator = User::factory()->create();
    $otherUser = User::factory()->create();
    $password = 'testpassword';
    $room = Room::factory()->create([
        'creator_id' => $creator->id,
        'password' => Hash::make($password),
        'campaign_id' => null,
    ]);

    // Non-creator should be redirected to join page
    $response = actingAs($otherUser)->get("/rooms/{$room->invite_code}");
    
    $response->assertRedirect("/rooms/join/{$room->invite_code}");
});

test('non-creator can access password-protected room overview with correct password in URL', function () {
    $creator = User::factory()->create();
    $otherUser = User::factory()->create();
    $password = 'testpassword';
    $room = Room::factory()->create([
        'creator_id' => $creator->id,
        'password' => Hash::make($password),
        'campaign_id' => null,
    ]);

    // Non-creator should be able to access with password in URL
    $response = actingAs($otherUser)->get("/rooms/{$room->invite_code}?password={$password}");
    
    $response->assertOk();
    $response->assertViewIs('rooms.show');
    $response->assertViewHas('room');
    $response->assertViewHas('user_is_creator', false);
});

test('room creator can access room overview for non-password-protected room', function () {
    $user = User::factory()->create();
    $room = Room::factory()->passwordless()->create([
        'creator_id' => $user->id,
        'campaign_id' => null,
    ]);

    $response = actingAs($user)->get("/rooms/{$room->invite_code}");
    
    $response->assertOk();
    $response->assertViewIs('rooms.show');
    $response->assertViewHas('user_is_creator', true);
});
