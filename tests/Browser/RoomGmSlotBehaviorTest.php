<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use Domain\Room\Models\Room;
use Domain\Room\Actions\JoinRoomAction;
use Domain\User\Models\User;
use function Pest\Laravel\{actingAs};

uses()->group('browser');

beforeEach(function () {
    $this->gmUser = User::factory()->create(['username' => 'test_gm']);
    $this->playerUser = User::factory()->create(['username' => 'test_player']);
});

test('GM slot shows correct GM name and class', function () {
    $room = Room::factory()->create([
        'name' => 'Test Room',
        'creator_id' => $this->gmUser->id,
        'guest_count' => 3,
    ]);

    // Create a participant for the GM (they join their own room)
    (new JoinRoomAction())->execute(
        room: $room,
        user: $this->gmUser,
        character: null,
        temporaryCharacterName: null,
        temporaryCharacterClass: null
    );

    actingAs($this->gmUser);
    
    visit(route('rooms.session', $room))
        ->assertSee('GAME MASTER')
        ->assertSee('NARRATOR OF TALES');
});

test('non-GM users see Reserved in GM slot when GM not present', function () {
    $room = Room::factory()->create([
        'name' => 'Test Room',
        'creator_id' => $this->gmUser->id,
        'guest_count' => 3,
    ]);

    // Join as a player (GM is not present)
    (new JoinRoomAction())->execute(
        room: $room,
        user: $this->playerUser,
        character: null,
        temporaryCharacterName: 'Test Player',
        temporaryCharacterClass: 'Warrior'
    );

    actingAs($this->playerUser);
    
    visit(route('rooms.session', $room))
        ->assertSee('Reserved')
        ->assertSee('GM Slot');
});

test('player join buttons show Join text not Join Quest', function () {
    $room = Room::factory()->create([
        'name' => 'Test Room',
        'creator_id' => $this->gmUser->id,
        'guest_count' => 3,
    ]);

    actingAs($this->playerUser);
    
    visit(route('rooms.session', $room))
        ->assertSee('Join')
        ->assertDontSee('Join Quest');
});
