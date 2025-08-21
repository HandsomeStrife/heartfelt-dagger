<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Room\Repositories;

use Domain\Room\Data\RoomData;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\Room\Repositories\RoomRepository;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RoomRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected RoomRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new RoomRepository();
    }

    #[Test]
    public function it_finds_room_by_id_with_participant_count(): void
    {
        $room = Room::factory()->create();
        RoomParticipant::factory()->count(3)->create(['room_id' => $room->id, 'left_at' => null]);
        RoomParticipant::factory()->create(['room_id' => $room->id, 'left_at' => now()]); // Inactive

        $foundRoom = $this->repository->findById($room->id);

        $this->assertInstanceOf(RoomData::class, $foundRoom);
        $this->assertEquals($room->id, $foundRoom->id);
        $this->assertEquals(3, $foundRoom->active_participant_count);
        $this->assertNotNull($foundRoom->creator);
    }

    #[Test]
    public function it_returns_null_for_non_existent_room(): void
    {
        $foundRoom = $this->repository->findById(999);

        $this->assertNull($foundRoom);
    }

    #[Test]
    public function it_finds_room_by_invite_code(): void
    {
        $room = Room::factory()->create();
        RoomParticipant::factory()->count(2)->create(['room_id' => $room->id, 'left_at' => null]);

        $foundRoom = $this->repository->findByInviteCode($room->invite_code);

        $this->assertInstanceOf(RoomData::class, $foundRoom);
        $this->assertEquals($room->id, $foundRoom->id);
        $this->assertEquals($room->invite_code, $foundRoom->invite_code);
        $this->assertEquals(2, $foundRoom->active_participant_count);
    }

    #[Test]
    public function it_returns_null_for_invalid_invite_code(): void
    {
        Room::factory()->create();

        $foundRoom = $this->repository->findByInviteCode('INVALID1');

        $this->assertNull($foundRoom);
    }

    #[Test]
    public function it_gets_rooms_created_by_user(): void
    {
        $creator = User::factory()->create();
        $otherUser = User::factory()->create();
        
        $createdRooms = Room::factory()->count(3)->create(['creator_id' => $creator->id]);
        Room::factory()->count(2)->create(['creator_id' => $otherUser->id]);

        // Add participants to first room
        RoomParticipant::factory()->count(2)->create([
            'room_id' => $createdRooms->first()->id,
            'left_at' => null
        ]);

        $foundRooms = $this->repository->getCreatedByUser($creator);

        $this->assertCount(3, $foundRooms);
        $this->assertTrue($foundRooms->every(fn($room) => $room->creator_id === $creator->id));
        $this->assertEquals(2, $foundRooms->first()->active_participant_count);
    }

    #[Test]
    public function it_orders_created_rooms_by_newest_first(): void
    {
        $user = User::factory()->create();
        
        $oldRoom = Room::factory()->create([
            'creator_id' => $user->id,
            'created_at' => now()->subDays(2)
        ]);
        $newRoom = Room::factory()->create([
            'creator_id' => $user->id,
            'created_at' => now()
        ]);

        $foundRooms = $this->repository->getCreatedByUser($user);

        $this->assertEquals($newRoom->id, $foundRooms->first()->id);
        $this->assertEquals($oldRoom->id, $foundRooms->last()->id);
    }

    #[Test]
    public function it_gets_rooms_joined_by_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        
        $joinedRoom = Room::factory()->create();
        $notJoinedRoom = Room::factory()->create();
        
        // User joins one room
        RoomParticipant::factory()->create([
            'room_id' => $joinedRoom->id,
            'user_id' => $user->id,
            'left_at' => null
        ]);
        
        // Other user joins another room
        RoomParticipant::factory()->create([
            'room_id' => $notJoinedRoom->id,
            'user_id' => $otherUser->id,
            'left_at' => null
        ]);

        $foundRooms = $this->repository->getJoinedByUser($user);

        $this->assertCount(1, $foundRooms);
        $this->assertEquals($joinedRoom->id, $foundRooms->first()->id);
    }

    #[Test]
    public function it_excludes_left_rooms_from_joined_by_user(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create();
        
        // User joined but then left
        RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'left_at' => now()
        ]);

        $foundRooms = $this->repository->getJoinedByUser($user);

        $this->assertCount(0, $foundRooms);
    }

    #[Test]
    public function it_gets_room_participants_with_relationships(): void
    {
        $room = Room::factory()->create();
        
        $participants = RoomParticipant::factory()->count(3)->create([
            'room_id' => $room->id,
            'left_at' => null
        ]);
        
        // Add one who left
        RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'left_at' => now()
        ]);

        $foundParticipants = $this->repository->getRoomParticipants($room);

        $this->assertCount(3, $foundParticipants);
        $this->assertTrue($foundParticipants->every(fn($p) => $p->user !== null));
        $this->assertTrue($foundParticipants->every(fn($p) => $p->character !== null));
    }

    #[Test]
    public function it_orders_participants_by_joined_at_ascending(): void
    {
        $room = Room::factory()->create();
        
        $laterParticipant = RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'joined_at' => now(),
            'left_at' => null
        ]);
        
        $earlierParticipant = RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'joined_at' => now()->subHour(),
            'left_at' => null
        ]);

        $foundParticipants = $this->repository->getRoomParticipants($room);

        $this->assertEquals($earlierParticipant->id, $foundParticipants->first()->id);
        $this->assertEquals($laterParticipant->id, $foundParticipants->last()->id);
    }

    #[Test]
    public function it_includes_participant_count_in_all_queries(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);
        
        RoomParticipant::factory()->count(4)->create([
            'room_id' => $room->id,
            'left_at' => null
        ]);

        // Test findById
        $foundById = $this->repository->findById($room->id);
        $this->assertEquals(4, $foundById->active_participant_count);

        // Test findByInviteCode
        $foundByCode = $this->repository->findByInviteCode($room->invite_code);
        $this->assertEquals(4, $foundByCode->active_participant_count);

        // Test getCreatedByUser
        $createdRooms = $this->repository->getCreatedByUser($user);
        $this->assertEquals(4, $createdRooms->first()->active_participant_count);
    }

    #[Test]
    public function it_handles_rooms_with_zero_participants(): void
    {
        $room = Room::factory()->create();
        // No participants added

        $foundRoom = $this->repository->findById($room->id);

        $this->assertEquals(0, $foundRoom->active_participant_count);
    }

    #[Test]
    public function it_returns_empty_collection_for_user_with_no_rooms(): void
    {
        $user = User::factory()->create();
        Room::factory()->count(3)->create(); // Other users' rooms

        $createdRooms = $this->repository->getCreatedByUser($user);
        $joinedRooms = $this->repository->getJoinedByUser($user);

        $this->assertCount(0, $createdRooms);
        $this->assertCount(0, $joinedRooms);
    }

    #[Test]
    public function it_correctly_counts_mixed_participant_states(): void
    {
        $room = Room::factory()->create();
        
        // 3 active participants
        RoomParticipant::factory()->count(3)->create([
            'room_id' => $room->id,
            'left_at' => null
        ]);
        
        // 2 participants who left
        RoomParticipant::factory()->count(2)->create([
            'room_id' => $room->id,
            'left_at' => now()
        ]);

        $foundRoom = $this->repository->findById($room->id);

        $this->assertEquals(3, $foundRoom->active_participant_count);
    }

    #[Test]
    public function it_loads_creator_relationship_in_all_methods(): void
    {
        $creator = User::factory()->create(['username' => 'unique_room_creator_789']);
        $room = Room::factory()->create(['creator_id' => $creator->id]);

        // Test findById
        $foundById = $this->repository->findById($room->id);
        $this->assertEquals('unique_room_creator_789', $foundById->creator->username);

        // Test findByInviteCode
        $foundByCode = $this->repository->findByInviteCode($room->invite_code);
        $this->assertEquals('unique_room_creator_789', $foundByCode->creator->username);

        // Test getCreatedByUser
        $createdRooms = $this->repository->getCreatedByUser($creator);
        $this->assertEquals('unique_room_creator_789', $createdRooms->first()->creator->username);
    }
}
