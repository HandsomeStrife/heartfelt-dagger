<?php

declare(strict_types=1);

use Domain\User\Models\User;
use Domain\Room\Models\Room;

describe('Reference Sidebar Integration', function () {
    test('GM sidebar includes quick reference section', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('rooms.show', $room->invite_code));

        $response->assertStatus(200)
            ->assertSee('GM Quick Reference')
            ->assertSee('Core GM Mechanics')
            ->assertSee('Adversaries')
            ->assertSee('GM Guidance');
    });

    test('player sidebar includes quick reference section', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create();
        
        // Join the room as a participant
        $room->participants()->create([
            'user_id' => $user->id,
            'character_key' => 'test-char-123',
        ]);

        $response = $this->actingAs($user)->get(route('rooms.show', $room->invite_code));

        $response->assertStatus(200)
            ->assertSee('Player Quick Reference')
            ->assertSee('Action Rolls')
            ->assertSee('Combat Basics')
            ->assertSee('Conditions');
    });

    test('reference links in sidebar open in new tabs', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('rooms.show', $room->invite_code));

        // Check that reference links have target="_blank"
        $response->assertSee('target="_blank"');
    });

    test('sidebar quick reference includes proper route links', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('rooms.show', $room->invite_code));

        $response->assertSee(route('reference.page', 'core-gm-mechanics'))
            ->assertSee(route('reference.page', 'adversaries'))
            ->assertSee(route('reference.index'));
    });
});
