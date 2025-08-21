<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Room\Data;

use Domain\Room\Data\RoomData;
use Domain\Room\Enums\RoomStatus;
use Domain\Room\Models\Room;
use Domain\User\Models\User;
use Livewire\Wireable;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RoomDataTest extends TestCase
{
    #[Test]
    public function it_implements_wireable_interface(): void
    {
        $room = Room::factory()->create();
        $roomData = RoomData::from($room);

        $this->assertInstanceOf(Wireable::class, $roomData);
    }

    #[Test]
    public function it_creates_from_array(): void
    {
        $data = [
            'id' => 1,
            'name' => 'Test Room',
            'description' => 'A test room',
            'password' => 'hashed_password',
            'guest_count' => 4,
            'creator_id' => 1,
            'invite_code' => 'ABC12345',
            'status' => RoomStatus::Active,
            'created_at' => '2023-01-01 12:00:00',
            'updated_at' => '2023-01-01 12:00:00',
            'active_participant_count' => 3,
        ];

        $roomData = RoomData::from($data);

        $this->assertEquals(1, $roomData->id);
        $this->assertEquals('Test Room', $roomData->name);
        $this->assertEquals('A test room', $roomData->description);
        $this->assertEquals('hashed_password', $roomData->password);
        $this->assertEquals(4, $roomData->guest_count);
        $this->assertEquals(1, $roomData->creator_id);
        $this->assertEquals('ABC12345', $roomData->invite_code);
        $this->assertEquals(RoomStatus::Active, $roomData->status);
        $this->assertEquals('2023-01-01 12:00:00', $roomData->created_at);
        $this->assertEquals('2023-01-01 12:00:00', $roomData->updated_at);
        $this->assertEquals(3, $roomData->active_participant_count);
    }

    #[Test]
    public function it_creates_from_model(): void
    {
        $room = Room::factory()->create([
            'name' => 'Model Room',
            'description' => 'Created from model',
            'guest_count' => 2,
        ]);

        $roomData = RoomData::from($room);

        $this->assertEquals($room->id, $roomData->id);
        $this->assertEquals('Model Room', $roomData->name);
        $this->assertEquals('Created from model', $roomData->description);
        $this->assertEquals(2, $roomData->guest_count);
        $this->assertEquals($room->creator_id, $roomData->creator_id);
        $this->assertEquals($room->invite_code, $roomData->invite_code);
        $this->assertEquals($room->status, $roomData->status);
    }

    #[Test]
    public function it_creates_from_model_with_relationships(): void
    {
        $creator = User::factory()->create(['username' => 'room_creator']);
        $room = Room::factory()->create(['creator_id' => $creator->id]);
        $room->load('creator');

        $roomData = RoomData::from($room);

        $this->assertNotNull($roomData->creator);
        $this->assertEquals('room_creator', $roomData->creator->username);
    }

    #[Test]
    public function it_handles_null_optional_fields(): void
    {
        $data = [
            'id' => null,
            'name' => 'Test Room',
            'description' => 'Description',
            'password' => 'password',
            'guest_count' => 1,
            'creator_id' => 1,
            'invite_code' => 'TESTCODE',
            'status' => RoomStatus::Active,
            'created_at' => null,
            'updated_at' => null,
            'creator' => null,
            'participants' => null,
            'active_participant_count' => null,
        ];

        $roomData = RoomData::from($data);

        $this->assertNull($roomData->id);
        $this->assertNull($roomData->created_at);
        $this->assertNull($roomData->updated_at);
        $this->assertNull($roomData->creator);
        $this->assertNull($roomData->participants);
        $this->assertNull($roomData->active_participant_count);
    }

    #[Test]
    public function it_handles_all_status_types(): void
    {
        foreach ([RoomStatus::Active, RoomStatus::Completed, RoomStatus::Archived] as $status) {
            $data = [
                'id' => 1,
                'name' => 'Test Room',
                'description' => 'Description',
                'password' => 'password',
                'guest_count' => 1,
                'creator_id' => 1,
                'invite_code' => 'TESTCODE',
                'status' => $status,
                'created_at' => '2023-01-01 12:00:00',
                'updated_at' => '2023-01-01 12:00:00',
            ];

            $roomData = RoomData::from($data);
            $this->assertEquals($status, $roomData->status);
        }
    }

    #[Test]
    public function it_creates_with_participant_count_from_array(): void
    {
        $data = [
            'id' => 1,
            'name' => 'Test Room',
            'description' => 'Description',
            'password' => 'password',
            'guest_count' => 5,
            'creator_id' => 1,
            'invite_code' => 'TESTCODE',
            'status' => RoomStatus::Active,
            'created_at' => '2023-01-01 12:00:00',
            'updated_at' => '2023-01-01 12:00:00',
            'active_participant_count' => 3,
        ];

        $roomData = RoomData::from($data);

        $this->assertEquals(3, $roomData->active_participant_count);
    }

    #[Test]
    public function it_preserves_string_status_from_array(): void
    {
        $data = [
            'id' => 1,
            'name' => 'Test Room',
            'description' => 'Description',
            'password' => 'password',
            'guest_count' => 1,
            'creator_id' => 1,
            'invite_code' => 'TESTCODE',
            'status' => 'active', // String status
            'created_at' => '2023-01-01 12:00:00',
            'updated_at' => '2023-01-01 12:00:00',
        ];

        $roomData = RoomData::from($data);

        $this->assertEquals(RoomStatus::Active, $roomData->status);
    }

    #[Test]
    public function it_works_with_livewire_to_livewire(): void
    {
        $originalData = [
            'id' => 1,
            'name' => 'Livewire Room',
            'description' => 'Test livewire',
            'password' => 'password',
            'guest_count' => 3,
            'creator_id' => 1,
            'invite_code' => 'LIVEWIRE1',
            'status' => RoomStatus::Active,
            'created_at' => '2023-01-01 12:00:00',
            'updated_at' => '2023-01-01 12:00:00',
        ];

        $roomData = RoomData::from($originalData);
        $livewireArray = $roomData->toLivewire();
        $restoredData = RoomData::fromLivewire($livewireArray);

        $this->assertEquals($roomData->id, $restoredData->id);
        $this->assertEquals($roomData->name, $restoredData->name);
        $this->assertEquals($roomData->description, $restoredData->description);
        $this->assertEquals($roomData->guest_count, $restoredData->guest_count);
        $this->assertEquals($roomData->status, $restoredData->status);
    }

    #[Test]
    public function it_works_with_livewire_from_livewire(): void
    {
        $room = Room::factory()->create();
        $roomData = RoomData::from($room);

        $livewireArray = $roomData->toLivewire();
        $restoredData = RoomData::fromLivewire($livewireArray);

        $this->assertEquals($roomData->id, $restoredData->id);
        $this->assertEquals($roomData->name, $restoredData->name);
        $this->assertEquals($roomData->guest_count, $restoredData->guest_count);
        $this->assertEquals($roomData->invite_code, $restoredData->invite_code);
    }

    #[Test]
    public function it_handles_large_participant_counts(): void
    {
        $data = [
            'id' => 1,
            'name' => 'Big Room',
            'description' => 'Many participants',
            'password' => 'password',
            'guest_count' => 5,
            'creator_id' => 1,
            'invite_code' => 'BIGROOM1',
            'status' => RoomStatus::Active,
            'created_at' => '2023-01-01 12:00:00',
            'updated_at' => '2023-01-01 12:00:00',
            'active_participant_count' => 5,
        ];

        $roomData = RoomData::from($data);

        $this->assertEquals(5, $roomData->active_participant_count);
        $this->assertEquals(5, $roomData->guest_count);
    }

    #[Test]
    public function it_handles_zero_participant_count(): void
    {
        $data = [
            'id' => 1,
            'name' => 'Empty Room',
            'description' => 'No participants',
            'password' => 'password',
            'guest_count' => 3,
            'creator_id' => 1,
            'invite_code' => 'EMPTY001',
            'status' => RoomStatus::Active,
            'created_at' => '2023-01-01 12:00:00',
            'updated_at' => '2023-01-01 12:00:00',
            'active_participant_count' => 0,
        ];

        $roomData = RoomData::from($data);

        $this->assertEquals(0, $roomData->active_participant_count);
    }

    #[Test]
    public function it_handles_different_guest_counts(): void
    {
        foreach ([1, 2, 3, 4, 5] as $guestCount) {
            $data = [
                'id' => $guestCount,
                'name' => "Room {$guestCount}",
                'description' => "Room for {$guestCount} guests",
                'password' => 'password',
                'guest_count' => $guestCount,
                'creator_id' => 1,
                'invite_code' => "ROOM{$guestCount}001",
                'status' => RoomStatus::Active,
                'created_at' => '2023-01-01 12:00:00',
                'updated_at' => '2023-01-01 12:00:00',
            ];

            $roomData = RoomData::from($data);
            $this->assertEquals($guestCount, $roomData->guest_count);
        }
    }
}
