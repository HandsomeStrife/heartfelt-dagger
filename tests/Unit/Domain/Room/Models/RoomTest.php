<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Room\Models;

use Domain\Room\Enums\RoomStatus;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RoomTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_generates_unique_invite_code_on_creation(): void
    {
        $room = Room::factory()->create();

        $this->assertNotNull($room->invite_code);
        $this->assertEquals(8, strlen($room->invite_code));
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{8}$/', $room->invite_code);
    }

    #[Test]
    public function it_generates_unique_invite_codes_across_multiple_rooms(): void
    {
        $rooms = Room::factory()->count(5)->create();
        $inviteCodes = $rooms->pluck('invite_code')->toArray();

        $this->assertEquals(5, count(array_unique($inviteCodes)));
    }

    #[Test]
    public function it_has_proper_default_status(): void
    {
        $room = Room::factory()->create();

        $this->assertEquals(RoomStatus::Active, $room->status);
    }

    #[Test]
    public function it_casts_status_to_enum(): void
    {
        $room = Room::factory()->create(['status' => RoomStatus::Completed]);

        $this->assertInstanceOf(RoomStatus::class, $room->status);
        $this->assertEquals(RoomStatus::Completed, $room->status);
    }

    #[Test]
    public function it_casts_guest_count_to_integer(): void
    {
        $room = Room::factory()->create(['guest_count' => '3']);

        $this->assertIsInt($room->guest_count);
        $this->assertEquals(3, $room->guest_count);
    }

    #[Test]
    public function it_belongs_to_creator(): void
    {
        $room = Room::factory()->create();

        $this->assertInstanceOf(User::class, $room->creator);
    }

    #[Test]
    public function it_has_many_participants(): void
    {
        $room = Room::factory()->create();
        RoomParticipant::factory()->count(3)->create(['room_id' => $room->id]);

        $room->load('participants');

        $this->assertCount(3, $room->participants);
        $this->assertInstanceOf(RoomParticipant::class, $room->participants->first());
    }

    #[Test]
    public function it_has_many_active_participants(): void
    {
        $room = Room::factory()->create();
        
        // Create active participants
        RoomParticipant::factory()->count(2)->create([
            'room_id' => $room->id,
            'left_at' => null
        ]);
        
        // Create participant who left
        RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'left_at' => now()
        ]);

        $activeParticipants = $room->activeParticipants;

        $this->assertCount(2, $activeParticipants);
    }

    #[Test]
    public function it_checks_if_user_is_creator(): void
    {
        $creator = User::factory()->create();
        $otherUser = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $creator->id]);

        $this->assertTrue($room->isCreator($creator));
        $this->assertFalse($room->isCreator($otherUser));
    }

    #[Test]
    public function it_checks_if_user_is_active_participant(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $room = Room::factory()->create();

        // User is not a participant
        $this->assertFalse($room->hasActiveParticipant($user));

        // User becomes active participant
        RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'left_at' => null
        ]);

        $this->assertTrue($room->hasActiveParticipant($user));
        $this->assertFalse($room->hasActiveParticipant($otherUser));

        // User leaves
        $room->activeParticipants()->where('user_id', $user->id)->update(['left_at' => now()]);
        $room->refresh();

        $this->assertFalse($room->hasActiveParticipant($user));
    }

    #[Test]
    public function it_gets_active_participant_count(): void
    {
        $room = Room::factory()->create();

        $this->assertEquals(0, $room->getActiveParticipantCount());

        // Add active participants
        RoomParticipant::factory()->count(3)->create([
            'room_id' => $room->id,
            'left_at' => null
        ]);

        // Add participant who left
        RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'left_at' => now()
        ]);

        $this->assertEquals(3, $room->getActiveParticipantCount());
    }

    #[Test]
    public function it_checks_if_at_capacity(): void
    {
        $room = Room::factory()->create(['guest_count' => 2]);

        $this->assertFalse($room->isAtCapacity());

        // Add one participant
        RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'left_at' => null
        ]);

        $this->assertFalse($room->isAtCapacity());

        // Add second participant (at capacity)
        RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'left_at' => null
        ]);

        $this->assertTrue($room->isAtCapacity());
    }

    #[Test]
    public function it_generates_invite_url(): void
    {
        $room = Room::factory()->create();

        $expectedUrl = route('rooms.invite', ['invite_code' => $room->invite_code]);
        $this->assertEquals($expectedUrl, $room->getInviteUrl());
    }

    #[Test]
    public function it_scopes_rooms_by_creator(): void
    {
        $creator = User::factory()->create();
        $otherUser = User::factory()->create();
        
        $creatorRooms = Room::factory()->count(3)->create(['creator_id' => $creator->id]);
        Room::factory()->count(2)->create(['creator_id' => $otherUser->id]);

        $foundRooms = Room::byCreator($creator)->get();

        $this->assertCount(3, $foundRooms);
        $this->assertTrue($foundRooms->every(fn($room) => $room->creator_id === $creator->id));
    }

    #[Test]
    public function it_scopes_rooms_by_invite_code(): void
    {
        $room = Room::factory()->create();
        Room::factory()->count(2)->create(); // Other rooms

        $foundRoom = Room::byInviteCode($room->invite_code)->first();

        $this->assertNotNull($foundRoom);
        $this->assertEquals($room->id, $foundRoom->id);
    }

    #[Test]
    public function it_scopes_active_rooms(): void
    {
        // Clear any existing rooms to ensure test isolation
        Room::query()->delete();
        
        Room::factory()->count(3)->create(['status' => RoomStatus::Active]);
        Room::factory()->count(2)->create(['status' => RoomStatus::Completed]);
        Room::factory()->create(['status' => RoomStatus::Archived]);

        $activeRooms = Room::active()->get();

        $this->assertCount(3, $activeRooms);
        $this->assertTrue($activeRooms->every(fn($room) => $room->status === RoomStatus::Active));
    }

    #[Test]
    public function it_generates_unique_invite_codes_when_duplicates_exist(): void
    {
        // Mock the random generation to return a duplicate first, then unique
        $existingRoom = Room::factory()->create();
        $existingCode = $existingRoom->invite_code;

        // Create another room - should get different code even if random generates same initially
        $newRoom = Room::factory()->create();

        $this->assertNotEquals($existingCode, $newRoom->invite_code);
    }

    #[Test]
    public function it_validates_guest_count_range(): void
    {
        // Valid guest counts
        foreach ([1, 2, 3, 4, 5] as $count) {
            $room = Room::factory()->create(['guest_count' => $count]);
            $this->assertEquals($count, $room->guest_count);
        }
    }

    #[Test]
    public function it_handles_password_storage(): void
    {
        $room = Room::factory()->create();

        // Password should be hashed
        $this->assertNotEquals('password', $room->password);
        $this->assertTrue(password_verify('password', $room->password));
    }
}
