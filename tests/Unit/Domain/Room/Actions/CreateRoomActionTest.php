<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Room\Actions;

use Domain\Room\Actions\CreateRoomAction;
use Domain\Room\Data\CreateRoomData;
use Domain\Room\Models\Room;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateRoomActionTest extends TestCase
{
    use RefreshDatabase;

    protected CreateRoomAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new CreateRoomAction();
    }

    #[Test]
    public function it_creates_room_successfully(): void
    {
        $user = User::factory()->create();
        $createData = new CreateRoomData(
            name: 'Epic Adventure Room',
            description: 'A room for our weekly session',
            password: 'secret123',
            guest_count: 4
        );

        $roomData = $this->action->execute($createData, $user);

        $this->assertNotNull($roomData->id);
        $this->assertEquals('Epic Adventure Room', $roomData->name);
        $this->assertEquals('A room for our weekly session', $roomData->description);
        $this->assertEquals(4, $roomData->guest_count);
        $this->assertEquals($user->id, $roomData->creator_id);
        $this->assertNotNull($roomData->invite_code);
    }

    #[Test]
    public function it_auto_generates_invite_code(): void
    {
        $user = User::factory()->create();
        $createData = new CreateRoomData(
            name: 'Test Room',
            description: 'Test Description',
            password: 'password',
            guest_count: 2
        );

        $roomData = $this->action->execute($createData, $user);

        $this->assertNotNull($roomData->invite_code);
        $this->assertEquals(8, strlen($roomData->invite_code));
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{8}$/', $roomData->invite_code);
    }

    #[Test]
    public function it_hashes_password(): void
    {
        $user = User::factory()->create();
        $createData = new CreateRoomData(
            name: 'Test Room',
            description: 'Test Description',
            password: 'plaintext',
            guest_count: 2
        );

        $roomData = $this->action->execute($createData, $user);

        // Password should be hashed, not plain text
        $this->assertNotEquals('plaintext', $roomData->password);
        
        // Verify the password can be verified
        $room = Room::find($roomData->id);
        $this->assertTrue(password_verify('plaintext', $room->password));
    }

    #[Test]
    public function it_persists_room_to_database(): void
    {
        $user = User::factory()->create();
        $createData = new CreateRoomData(
            name: 'Persistent Room',
            description: 'This should be saved',
            password: 'test123',
            guest_count: 3
        );

        $roomData = $this->action->execute($createData, $user);

        $this->assertDatabaseHas('rooms', [
            'id' => $roomData->id,
            'name' => 'Persistent Room',
            'description' => 'This should be saved',
            'guest_count' => 3,
            'creator_id' => $user->id,
            'invite_code' => $roomData->invite_code,
            'status' => 'active'
        ]);
    }

    #[Test]
    public function it_loads_creator_relationship(): void
    {
        $user = User::factory()->create();
        $createData = new CreateRoomData(
            name: 'Test Room',
            description: 'Test Description',
            password: 'password',
            guest_count: 1
        );

        $roomData = $this->action->execute($createData, $user);

        $this->assertNotNull($roomData->creator);
        $this->assertEquals($user->id, $roomData->creator->id);
        $this->assertEquals($user->username, $roomData->creator->username);
    }

    #[Test]
    public function it_creates_rooms_with_unique_codes(): void
    {
        $user = User::factory()->create();
        $createData1 = new CreateRoomData(
            name: 'Room 1',
            description: 'First room',
            password: 'password',
            guest_count: 2
        );
        $createData2 = new CreateRoomData(
            name: 'Room 2',
            description: 'Second room',
            password: 'password',
            guest_count: 3
        );

        $roomData1 = $this->action->execute($createData1, $user);
        $roomData2 = $this->action->execute($createData2, $user);

        $this->assertNotEquals($roomData1->invite_code, $roomData2->invite_code);
    }

    #[Test]
    public function it_handles_different_guest_counts(): void
    {
        $user = User::factory()->create();

        foreach ([1, 2, 3, 4, 5] as $guestCount) {
            $createData = new CreateRoomData(
                name: "Room for {$guestCount}",
                description: "Room with {$guestCount} guests",
                password: 'password',
                guest_count: $guestCount
            );

            $roomData = $this->action->execute($createData, $user);

            $this->assertEquals($guestCount, $roomData->guest_count);
        }
    }

    #[Test]
    public function it_associates_creator_correctly(): void
    {
        $creator1 = User::factory()->create();
        $creator2 = User::factory()->create();

        $createData1 = new CreateRoomData(
            name: 'Creator 1 Room',
            description: 'Room by creator 1',
            password: 'password',
            guest_count: 2
        );

        $createData2 = new CreateRoomData(
            name: 'Creator 2 Room',
            description: 'Room by creator 2',
            password: 'password',
            guest_count: 3
        );

        $roomData1 = $this->action->execute($createData1, $creator1);
        $roomData2 = $this->action->execute($createData2, $creator2);

        $this->assertEquals($creator1->id, $roomData1->creator_id);
        $this->assertEquals($creator2->id, $roomData2->creator_id);
    }

    #[Test]
    public function it_initializes_participant_count_as_null(): void
    {
        $user = User::factory()->create();
        $createData = new CreateRoomData(
            name: 'Empty Room',
            description: 'No participants yet',
            password: 'password',
            guest_count: 5
        );

        $roomData = $this->action->execute($createData, $user);

        $this->assertEquals(0, $roomData->active_participant_count);
    }

    #[Test]
    public function it_handles_long_names_and_descriptions(): void
    {
        $user = User::factory()->create();
        $createData = new CreateRoomData(
            name: str_repeat('A', 100), // Max length
            description: str_repeat('B', 500), // Max length
            password: 'password',
            guest_count: 2
        );

        $roomData = $this->action->execute($createData, $user);

        $this->assertEquals(str_repeat('A', 100), $roomData->name);
        $this->assertEquals(str_repeat('B', 500), $roomData->description);
    }
}
