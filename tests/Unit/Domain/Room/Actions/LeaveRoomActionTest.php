<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Room\Actions;

use Domain\Room\Actions\LeaveRoomAction;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LeaveRoomActionTest extends TestCase
{
    use RefreshDatabase;

    protected LeaveRoomAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new LeaveRoomAction();
    }

    #[Test]
    public function it_allows_participant_to_leave_room(): void
    {
        $room = Room::factory()->create();
        $user = User::factory()->create();
        $participant = RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'left_at' => null
        ]);

        $this->assertNull($participant->left_at);

        $this->action->execute($room, $user);

        $participant->refresh();
        $this->assertNotNull($participant->left_at);
    }

    #[Test]
    public function it_sets_left_at_timestamp(): void
    {
        $room = Room::factory()->create();
        $user = User::factory()->create();
        RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'left_at' => null
        ]);

        $beforeLeave = now()->subSecond(); // Add buffer for timing
        $this->action->execute($room, $user);
        $afterLeave = now()->addSecond(); // Add buffer for timing

        $participant = RoomParticipant::where('room_id', $room->id)
            ->where('user_id', $user->id)
            ->first();

        $this->assertNotNull($participant->left_at);
        $this->assertTrue($participant->left_at->between($beforeLeave, $afterLeave));
    }

    #[Test]
    public function it_prevents_non_participant_from_leaving(): void
    {
        $room = Room::factory()->create();
        $user = User::factory()->create();

        // User is not a participant
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('You are not an active participant in this room.');

        $this->action->execute($room, $user);
    }

    #[Test]
    public function it_prevents_already_left_participant_from_leaving_again(): void
    {
        $room = Room::factory()->create();
        $user = User::factory()->create();
        RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'left_at' => now()->subHour() // Already left
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('You are not an active participant in this room.');

        $this->action->execute($room, $user);
    }

    #[Test]
    public function it_allows_multiple_participants_to_leave_independently(): void
    {
        $room = Room::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $participant1 = RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'user_id' => $user1->id,
            'left_at' => null
        ]);
        
        $participant2 = RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'user_id' => $user2->id,
            'left_at' => null
        ]);

        // User1 leaves
        $this->action->execute($room, $user1);
        
        $participant1->refresh();
        $participant2->refresh();
        
        $this->assertNotNull($participant1->left_at);
        $this->assertNull($participant2->left_at);

        // User2 can still leave
        $this->action->execute($room, $user2);
        
        $participant2->refresh();
        $this->assertNotNull($participant2->left_at);
    }

    #[Test]
    public function it_handles_participant_in_multiple_rooms(): void
    {
        $room1 = Room::factory()->create();
        $room2 = Room::factory()->create();
        $user = User::factory()->create();
        
        $participant1 = RoomParticipant::factory()->create([
            'room_id' => $room1->id,
            'user_id' => $user->id,
            'left_at' => null
        ]);
        
        $participant2 = RoomParticipant::factory()->create([
            'room_id' => $room2->id,
            'user_id' => $user->id,
            'left_at' => null
        ]);

        // Leave room1 only
        $this->action->execute($room1, $user);
        
        $participant1->refresh();
        $participant2->refresh();
        
        $this->assertNotNull($participant1->left_at);
        $this->assertNull($participant2->left_at);
    }

    #[Test]
    public function it_does_not_affect_other_participants_when_one_leaves(): void
    {
        $room = Room::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        
        $participant1 = RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'user_id' => $user1->id,
            'left_at' => null
        ]);
        
        $participant2 = RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'user_id' => $user2->id,
            'left_at' => null
        ]);
        
        $participant3 = RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'user_id' => $user3->id,
            'left_at' => null
        ]);

        // User2 leaves
        $this->action->execute($room, $user2);
        
        $participant1->refresh();
        $participant2->refresh();
        $participant3->refresh();
        
        $this->assertNull($participant1->left_at);
        $this->assertNotNull($participant2->left_at);
        $this->assertNull($participant3->left_at);
    }

    #[Test]
    public function it_handles_leaving_with_character_attached(): void
    {
        $room = Room::factory()->create();
        $user = User::factory()->create();
        $participant = RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'left_at' => null
        ]);

        $this->assertNotNull($participant->character_id);

        $this->action->execute($room, $user);

        $participant->refresh();
        $this->assertNotNull($participant->left_at);
        // Character association should remain for historical record
        $this->assertNotNull($participant->character_id);
    }

    #[Test]
    public function it_handles_leaving_without_character(): void
    {
        $room = Room::factory()->create();
        $user = User::factory()->create();
        $participant = RoomParticipant::factory()->withoutCharacter()->create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'left_at' => null
        ]);

        $this->action->execute($room, $user);

        $participant->refresh();
        $this->assertNotNull($participant->left_at);
        $this->assertNull($participant->character_id);
    }

    #[Test]
    public function it_handles_leaving_with_temporary_character(): void
    {
        $room = Room::factory()->create();
        $user = User::factory()->create();
        $participant = RoomParticipant::factory()->withTemporaryCharacter()->create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'left_at' => null,
            'character_name' => 'Temp Hero',
            'character_class' => 'Warrior'
        ]);

        $this->action->execute($room, $user);

        $participant->refresh();
        $this->assertNotNull($participant->left_at);
        // Temporary character info should remain for historical record
        $this->assertEquals('Temp Hero', $participant->character_name);
        $this->assertEquals('Warrior', $participant->character_class);
    }

    #[Test]
    public function it_maintains_room_integrity_after_leave(): void
    {
        $room = Room::factory()->create();
        $user = User::factory()->create();
        RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'left_at' => null
        ]);

        $originalRoomCount = Room::count();
        
        $this->action->execute($room, $user);

        // Room should still exist
        $this->assertEquals($originalRoomCount, Room::count());
        $room->refresh();
        $this->assertNotNull($room);
    }

    #[Test]
    public function it_preserves_participation_record(): void
    {
        $room = Room::factory()->create();
        $user = User::factory()->create();
        RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'left_at' => null
        ]);

        $originalParticipantCount = RoomParticipant::count();
        
        $this->action->execute($room, $user);

        // Participant record should still exist, just marked as left
        $this->assertEquals($originalParticipantCount, RoomParticipant::count());
        
        $participant = RoomParticipant::where('room_id', $room->id)
            ->where('user_id', $user->id)
            ->first();
            
        $this->assertNotNull($participant);
        $this->assertNotNull($participant->left_at);
    }
}
