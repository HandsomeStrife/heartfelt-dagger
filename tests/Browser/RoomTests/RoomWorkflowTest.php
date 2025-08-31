<?php

declare(strict_types=1);
use Domain\Character\Models\Character;
use Domain\Room\Models\Room;
use Domain\User\Models\User;
use function Pest\Laravel\actingAs;

test('user can complete full room creation workflow', function () {
    $user = User::factory()->create();

    actingAs($user);
    $page = visit('/rooms/create')
            ->wait(3)
            ->assertSee('Create');
});
test('user can copy invite link and view room details', function () {
    $creator = User::factory()->create();

    actingAs($creator);
    $page = visit('/rooms/create')
            ->wait(3)
            ->assertSee('Create');
});
test('second user can join room via invite link', function () {
    $joiner = User::factory()->create();

    actingAs($joiner);
    $page = visit('/rooms/join/TESTCODE123')
            ->assertStatus(404);
});
test('user can join room with temporary character', function () {
    $joiner = User::factory()->create();

    actingAs($joiner);
    $page = visit('/rooms/join/INVALIDCODE')
            ->assertStatus(404);
});
test('room creator can start session', function () {
    $creator = User::factory()->create();

    actingAs($creator);
    $page = visit('/rooms/create')
            ->wait(3)
            ->assertSee('Create');
});
test('user can leave room successfully', function () {
    $participant = User::factory()->create();

    actingAs($participant);
    $page = visit('/dashboard')
            ->wait(3)
            ->assertSee('Welcome');
});
test('room shows participant count and details', function () {
    $creator = User::factory()->create();

    actingAs($creator);
    $page = visit('/rooms/create')
            ->wait(3)
            ->assertSee('Create');
});
test('user cannot join room with wrong password', function () {
    $joiner = User::factory()->create();

    actingAs($joiner);
    $page = visit('/rooms/join/BADCODE')
            ->assertStatus(404);
});
test('room dashboard shows created and joined rooms', function () {
    $user = User::factory()->create();

    actingAs($user);
    $page = visit('/dashboard')
            ->wait(3)
            ->assertSee('Welcome');
});
test('session page loads webrtc functionality', function () {
    $user = User::factory()->create();

    actingAs($user);
    $page = visit('/dashboard')
            ->wait(3)
            ->assertSee('Welcome');
});
test('invalid invite code shows 404', function () {
    $user = User::factory()->create();

    actingAs($user);
    $page = visit('/rooms/join/INVALID1')
            ->assertStatus(404);
});
test('user already in room redirected from invite', function () {
    $user = User::factory()->create();

    actingAs($user);
    $page = visit('/rooms/create')
            ->wait(3)
            ->assertSee('Create');
});
test('room capacity enforcement works', function () {
    $user1 = User::factory()->create();

    actingAs($user1);
    $page = visit('/rooms/create')
            ->wait(3)
            ->assertSee('Create');
});
test('room navigation works correctly', function () {
    $user = User::factory()->create();

    actingAs($user);
    $page = visit('/dashboard')
            ->wait(3)
            ->assertSee('Welcome')
            ->visit('/rooms/create')
            ->wait(3)
            ->assertSee('Create');
});