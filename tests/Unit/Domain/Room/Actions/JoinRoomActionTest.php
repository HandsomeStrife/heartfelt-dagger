<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Room\Actions;

use Domain\Character\Models\Character;
use Domain\Room\Actions\JoinRoomAction;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class JoinRoomActionTest extends TestCase
{
    use RefreshDatabase;

    protected JoinRoomAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new JoinRoomAction();
    }

    #[Test]
    public function it_joins_room_with_character_successfully(): void
    {
        $room = Room::factory()->create(['guest_count' => 5]);
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        $result = $this->action->execute($room, $user, $character);

        $this->assertDatabaseHas('room_participants', [
            'room_id' => $room->id,
            'user_id' => $user->id,
            'character_id' => $character->id,
            'character_name' => null,
            'character_class' => null,
        ]);

        $this->assertEquals($room->id, $result->room_id);
        $this->assertEquals($user->id, $result->user_id);
        $this->assertEquals($character->id, $result->character_id);
    }

    #[Test]
    public function it_joins_room_with_temporary_character_successfully(): void
    {
        $room = Room::factory()->create(['guest_count' => 5]);
        $user = User::factory()->create();

        $result = $this->action->execute(
            $room,
            $user,
            null,
            'Gandalf',
            'Wizard'
        );

        $this->assertDatabaseHas('room_participants', [
            'room_id' => $room->id,
            'user_id' => $user->id,
            'character_id' => null,
            'character_name' => 'Gandalf',
            'character_class' => 'Wizard',
        ]);

        $this->assertEquals($room->id, $result->room_id);
        $this->assertEquals($user->id, $result->user_id);
        $this->assertNull($result->character_id);
        $this->assertEquals('Gandalf', $result->character_name);
        $this->assertEquals('Wizard', $result->character_class);
    }

    #[Test]
    public function it_joins_room_without_character(): void
    {
        $room = Room::factory()->create(['guest_count' => 5]);
        $user = User::factory()->create();

        $result = $this->action->execute($room, $user);

        $this->assertDatabaseHas('room_participants', [
            'room_id' => $room->id,
            'user_id' => $user->id,
            'character_id' => null,
            'character_name' => null,
            'character_class' => null,
        ]);

        $this->assertEquals($room->id, $result->room_id);
        $this->assertEquals($user->id, $result->user_id);
        $this->assertNull($result->character_id);
    }

    #[Test]
    public function it_persists_participation_to_database(): void
    {
        $room = Room::factory()->create();
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        $this->action->execute($room, $user, $character);

        $this->assertDatabaseCount('room_participants', 1);
        $participant = RoomParticipant::first();
        
        $this->assertEquals($room->id, $participant->room_id);
        $this->assertEquals($user->id, $participant->user_id);
        $this->assertEquals($character->id, $participant->character_id);
        $this->assertNotNull($participant->joined_at);
        $this->assertNull($participant->left_at);
    }

    #[Test]
    public function it_sets_joined_at_timestamp(): void
    {
        $room = Room::factory()->create();
        $user = User::factory()->create();

        $beforeJoin = now()->subSecond(); // Add buffer for timing
        $result = $this->action->execute($room, $user);
        $afterJoin = now()->addSecond(); // Add buffer for timing

        $this->assertNotNull($result->joined_at);
        $joinedAt = \Carbon\Carbon::parse($result->joined_at);
        $this->assertTrue($joinedAt->between($beforeJoin, $afterJoin));
    }

    #[Test]
    public function it_loads_all_relationships(): void
    {
        $room = Room::factory()->create();
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        $result = $this->action->execute($room, $user, $character);

        $this->assertNotNull($result->user);
        $this->assertNotNull($result->character);
        $this->assertEquals($user->id, $result->user->id);
        $this->assertEquals($character->id, $result->character->id);
    }

    #[Test]
    public function it_prevents_duplicate_participation(): void
    {
        $room = Room::factory()->create();
        $user = User::factory()->create();

        // Join once
        $this->action->execute($room, $user);

        // Try to join again
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('You are already an active participant in this room.');

        $this->action->execute($room, $user);
    }

    #[Test]
    public function it_prevents_joining_full_room(): void
    {
        $room = Room::factory()->create(['guest_count' => 1]);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Fill the room
        $this->action->execute($room, $user1);

        // Try to join full room
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('This room is at capacity.');

        $this->action->execute($room, $user2);
    }

    #[Test]
    public function it_validates_character_ownership(): void
    {
        $room = Room::factory()->create();
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherCharacter = Character::factory()->create(['user_id' => $otherUser->id]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Character does not belong to the user.');

        $this->action->execute($room, $user, $otherCharacter);
    }

    #[Test]
    public function it_allows_multiple_users_to_join_same_room(): void
    {
        $room = Room::factory()->create(['guest_count' => 3]);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $result1 = $this->action->execute($room, $user1);
        $result2 = $this->action->execute($room, $user2);

        $this->assertDatabaseCount('room_participants', 2);
        $this->assertNotEquals($result1->user_id, $result2->user_id);
    }

    #[Test]
    public function it_allows_user_to_join_different_rooms(): void
    {
        $room1 = Room::factory()->create();
        $room2 = Room::factory()->create();
        $user = User::factory()->create();

        $result1 = $this->action->execute($room1, $user);
        $result2 = $this->action->execute($room2, $user);

        $this->assertDatabaseCount('room_participants', 2);
        $this->assertNotEquals($result1->room_id, $result2->room_id);
    }

    #[Test]
    public function it_handles_null_character_gracefully(): void
    {
        $room = Room::factory()->create();
        $user = User::factory()->create();

        $result = $this->action->execute($room, $user, null);

        $this->assertNull($result->character_id);
        $this->assertNull($result->character);
    }

    #[Test]
    public function it_maintains_separate_participations_per_room(): void
    {
        $room1 = Room::factory()->create();
        $room2 = Room::factory()->create();
        $user = User::factory()->create();
        $character1 = Character::factory()->create(['user_id' => $user->id]);
        $character2 = Character::factory()->create(['user_id' => $user->id]);

        $result1 = $this->action->execute($room1, $user, $character1);
        $result2 = $this->action->execute($room2, $user, $character2);

        $this->assertEquals($character1->id, $result1->character_id);
        $this->assertEquals($character2->id, $result2->character_id);
        $this->assertDatabaseCount('room_participants', 2);
    }

    #[Test]
    public function it_handles_mixed_character_types(): void
    {
        $room = Room::factory()->create(['guest_count' => 3]);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user1->id]);

        // Join with full character
        $result1 = $this->action->execute($room, $user1, $character);
        
        // Join with temporary character
        $result2 = $this->action->execute($room, $user2, null, 'Temp Hero', 'Ranger');
        
        // Join without character
        $result3 = $this->action->execute($room, $user3);

        $this->assertEquals($character->id, $result1->character_id);
        $this->assertNull($result2->character_id);
        $this->assertEquals('Temp Hero', $result2->character_name);
        $this->assertNull($result3->character_id);
        $this->assertNull($result3->character_name);
    }
}
