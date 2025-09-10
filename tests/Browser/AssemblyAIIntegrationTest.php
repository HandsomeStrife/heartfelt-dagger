<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\User\Models\User;

use function Pest\Laravel\actingAs;

test('user can access assemblyai account setup page', function () {
    $user = User::factory()->create();

    actingAs($user);

    visit('/assemblyai/connect')
        ->assertSee('Connect AssemblyAI Account')
        ->assertSee('Add your AssemblyAI API key')
        ->assertSee('Account Name')
        ->assertSee('API Key');
})->group('browser');

test('room settings page loads correctly for room creator', function () {
    $user = User::factory()->create();

    $room = Room::factory()->create([
        'creator_id' => $user->id,
        'name' => 'Provider Test Room',
    ]);

    actingAs($user);

    visit("/rooms/{$room->invite_code}")
        ->assertSee('Provider Test Room')
        ->assertSee('Room Settings'); // This should be visible on the room show page
})->group('browser');
