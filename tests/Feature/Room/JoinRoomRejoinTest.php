<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use Domain\Room\Actions\JoinRoomAction;
use Domain\Room\Actions\LeaveRoomAction;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;

describe('Room Rejoin Flow', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->character = Character::factory()->create(['user_id' => $this->user->id]);
        $this->room = Room::factory()->create();
        $this->joinAction = new JoinRoomAction();
        $this->leaveAction = new LeaveRoomAction();
    });

    test('user can rejoin room after leaving', function () {
        // Initial join
        $participantData = $this->joinAction->execute(
            $this->room,
            $this->user,
            $this->character
        );

        expect($participantData->user_id)->toBe($this->user->id);
        expect($participantData->character_id)->toBe($this->character->id);
        expect($participantData->left_at)->toBeNull();

        // Verify participant record exists and is active
        $participant = RoomParticipant::find($participantData->id);
        expect($participant->left_at)->toBeNull();
        expect($this->room->hasActiveParticipant($this->user))->toBeTrue();

        // Leave the room
        $this->leaveAction->execute($this->room, $this->user);

        // Verify participant is no longer active
        $participant->refresh();
        expect($participant->left_at)->not()->toBeNull();
        expect($this->room->hasActiveParticipant($this->user))->toBeFalse();

        // Rejoin the room
        $rejoinData = $this->joinAction->execute(
            $this->room,
            $this->user,
            $this->character
        );

        // Should reuse the same participant record
        expect($rejoinData->id)->toBe($participantData->id);
        expect($rejoinData->left_at)->toBeNull();

        // Verify participant is active again
        $participant->refresh();
        expect($participant->left_at)->toBeNull();
        expect($this->room->hasActiveParticipant($this->user))->toBeTrue();

        // Verify only one participant record exists for this user/room combination
        $participantCount = RoomParticipant::where('room_id', $this->room->id)
            ->where('user_id', $this->user->id)
            ->count();
        expect($participantCount)->toBe(1);
    });

    test('user can rejoin room with different character', function () {
        $newCharacter = Character::factory()->create(['user_id' => $this->user->id]);

        // Initial join with first character
        $participantData = $this->joinAction->execute(
            $this->room,
            $this->user,
            $this->character
        );

        expect($participantData->character_id)->toBe($this->character->id);

        // Leave the room
        $this->leaveAction->execute($this->room, $this->user);

        // Rejoin with different character
        $rejoinData = $this->joinAction->execute(
            $this->room,
            $this->user,
            $newCharacter
        );

        // Should reuse the same participant record but update character
        expect($rejoinData->id)->toBe($participantData->id);
        expect($rejoinData->character_id)->toBe($newCharacter->id);
        expect($rejoinData->left_at)->toBeNull();

        // Verify participant record was updated
        $participant = RoomParticipant::find($participantData->id);
        expect($participant->character_id)->toBe($newCharacter->id);
        expect($participant->left_at)->toBeNull();
    });

    test('user can rejoin room with temporary character', function () {
        // Initial join with permanent character
        $participantData = $this->joinAction->execute(
            $this->room,
            $this->user,
            $this->character
        );

        // Leave the room
        $this->leaveAction->execute($this->room, $this->user);

        // Rejoin with temporary character
        $rejoinData = $this->joinAction->execute(
            $this->room,
            $this->user,
            null,
            'Test Character',
            'Warrior'
        );

        // Should reuse the same participant record but update to temporary character
        expect($rejoinData->id)->toBe($participantData->id);
        expect($rejoinData->character_id)->toBeNull();
        expect($rejoinData->character_name)->toBe('Test Character');
        expect($rejoinData->character_class)->toBe('Warrior');
        expect($rejoinData->left_at)->toBeNull();
    });

    test('multiple users can join and rejoin same room', function () {
        $user2 = User::factory()->create();
        $character2 = Character::factory()->create(['user_id' => $user2->id]);

        // Both users join
        $participant1Data = $this->joinAction->execute($this->room, $this->user, $this->character);
        $participant2Data = $this->joinAction->execute($this->room, $user2, $character2);

        expect($participant1Data->id)->not()->toBe($participant2Data->id);
        expect($this->room->hasActiveParticipant($this->user))->toBeTrue();
        expect($this->room->hasActiveParticipant($user2))->toBeTrue();

        // First user leaves
        $this->leaveAction->execute($this->room, $this->user);
        expect($this->room->hasActiveParticipant($this->user))->toBeFalse();
        expect($this->room->hasActiveParticipant($user2))->toBeTrue();

        // First user rejoins
        $rejoinData = $this->joinAction->execute($this->room, $this->user, $this->character);
        expect($rejoinData->id)->toBe($participant1Data->id);
        expect($this->room->hasActiveParticipant($this->user))->toBeTrue();
        expect($this->room->hasActiveParticipant($user2))->toBeTrue();

        // Verify both participants have different records
        expect($participant1Data->id)->not()->toBe($participant2Data->id);
    });
});
