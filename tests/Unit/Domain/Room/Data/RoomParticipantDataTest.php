<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Room\Data;

use Domain\Character\Models\Character;
use Domain\Room\Data\RoomParticipantData;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;
use Livewire\Wireable;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RoomParticipantDataTest extends TestCase
{
    #[Test]
    public function it_implements_wireable_interface(): void
    {
        $participant = RoomParticipant::factory()->create();
        $participantData = RoomParticipantData::from($participant);

        $this->assertInstanceOf(Wireable::class, $participantData);
    }

    #[Test]
    public function it_creates_from_array(): void
    {
        $data = [
            'id' => 1,
            'room_id' => 1,
            'user_id' => 1,
            'character_id' => 1,
            'character_name' => 'Temp Hero',
            'character_class' => 'Warrior',
            'joined_at' => '2023-01-01 12:00:00',
            'left_at' => null,
            'created_at' => '2023-01-01 12:00:00',
            'updated_at' => '2023-01-01 12:00:00',
        ];

        $participantData = RoomParticipantData::from($data);

        $this->assertEquals(1, $participantData->id);
        $this->assertEquals(1, $participantData->room_id);
        $this->assertEquals(1, $participantData->user_id);
        $this->assertEquals(1, $participantData->character_id);
        $this->assertEquals('Temp Hero', $participantData->character_name);
        $this->assertEquals('Warrior', $participantData->character_class);
        $this->assertEquals('2023-01-01 12:00:00', $participantData->joined_at);
        $this->assertNull($participantData->left_at);
    }

    #[Test]
    public function it_creates_from_model(): void
    {
        $participant = RoomParticipant::factory()->create();

        $participantData = RoomParticipantData::from($participant);

        $this->assertEquals($participant->id, $participantData->id);
        $this->assertEquals($participant->room_id, $participantData->room_id);
        $this->assertEquals($participant->user_id, $participantData->user_id);
        $this->assertEquals($participant->character_id, $participantData->character_id);
        $this->assertEquals($participant->character_name, $participantData->character_name);
        $this->assertEquals($participant->character_class, $participantData->character_class);
    }

    #[Test]
    public function it_creates_from_model_with_relationships(): void
    {
        $user = User::factory()->create(['username' => 'test_user']);
        $character = Character::factory()->create(['name' => 'Hero']);
        $participant = RoomParticipant::factory()->create([
            'user_id' => $user->id,
            'character_id' => $character->id,
        ]);
        $participant->load(['user', 'character']);

        $participantData = RoomParticipantData::from($participant);

        $this->assertNotNull($participantData->user);
        $this->assertNotNull($participantData->character);
        $this->assertEquals('test_user', $participantData->user->username);
        $this->assertEquals('Hero', $participantData->character->name);
    }

    #[Test]
    public function it_handles_null_character(): void
    {
        $participant = RoomParticipant::factory()->withoutCharacter()->create();

        $participantData = RoomParticipantData::from($participant);

        $this->assertNull($participantData->character_id);
        $this->assertNull($participantData->character);
    }

    #[Test]
    public function it_handles_temporary_character_data(): void
    {
        $participant = RoomParticipant::factory()->withTemporaryCharacter()->create([
            'character_name' => 'Temporary Hero',
            'character_class' => 'Rogue',
        ]);

        $participantData = RoomParticipantData::from($participant);

        $this->assertNull($participantData->character_id);
        $this->assertEquals('Temporary Hero', $participantData->character_name);
        $this->assertEquals('Rogue', $participantData->character_class);
    }

    #[Test]
    public function it_handles_null_optional_fields(): void
    {
        $data = [
            'id' => null,
            'room_id' => 1,
            'user_id' => 1,
            'character_id' => null,
            'character_name' => null,
            'character_class' => null,
            'joined_at' => null,
            'left_at' => null,
            'created_at' => null,
            'updated_at' => null,
            'user' => null,
            'character' => null,
        ];

        $participantData = RoomParticipantData::from($data);

        $this->assertNull($participantData->id);
        $this->assertNull($participantData->character_id);
        $this->assertNull($participantData->character_name);
        $this->assertNull($participantData->character_class);
        $this->assertNull($participantData->joined_at);
        $this->assertNull($participantData->left_at);
        $this->assertNull($participantData->user);
        $this->assertNull($participantData->character);
    }

    #[Test]
    public function it_preserves_datetime_strings(): void
    {
        $joinedAt = '2023-01-01 10:00:00';
        $leftAt = '2023-01-01 12:00:00';
        
        $data = [
            'id' => 1,
            'room_id' => 1,
            'user_id' => 1,
            'character_id' => null,
            'character_name' => null,
            'character_class' => null,
            'joined_at' => $joinedAt,
            'left_at' => $leftAt,
            'created_at' => '2023-01-01 09:00:00',
            'updated_at' => '2023-01-01 11:00:00',
        ];

        $participantData = RoomParticipantData::from($data);

        $this->assertEquals($joinedAt, $participantData->joined_at);
        $this->assertEquals($leftAt, $participantData->left_at);
    }

    #[Test]
    public function it_works_with_livewire_to_livewire(): void
    {
        $originalData = [
            'id' => 1,
            'room_id' => 2,
            'user_id' => 3,
            'character_id' => 4,
            'character_name' => 'Livewire Hero',
            'character_class' => 'Wizard',
            'joined_at' => '2023-01-01 12:00:00',
            'left_at' => null,
            'created_at' => '2023-01-01 12:00:00',
            'updated_at' => '2023-01-01 12:00:00',
        ];

        $participantData = RoomParticipantData::from($originalData);
        $livewireArray = $participantData->toLivewire();
        $restoredData = RoomParticipantData::fromLivewire($livewireArray);

        $this->assertEquals($participantData->id, $restoredData->id);
        $this->assertEquals($participantData->room_id, $restoredData->room_id);
        $this->assertEquals($participantData->user_id, $restoredData->user_id);
        $this->assertEquals($participantData->character_id, $restoredData->character_id);
        $this->assertEquals($participantData->character_name, $restoredData->character_name);
        $this->assertEquals($participantData->character_class, $restoredData->character_class);
    }

    #[Test]
    public function it_works_with_livewire_from_livewire(): void
    {
        $participant = RoomParticipant::factory()->create();
        $participantData = RoomParticipantData::from($participant);

        $livewireArray = $participantData->toLivewire();
        $restoredData = RoomParticipantData::fromLivewire($livewireArray);

        $this->assertEquals($participantData->id, $restoredData->id);
        $this->assertEquals($participantData->room_id, $restoredData->room_id);
        $this->assertEquals($participantData->user_id, $restoredData->user_id);
        $this->assertEquals($participantData->character_id, $restoredData->character_id);
    }

    #[Test]
    public function it_handles_active_participants(): void
    {
        $participant = RoomParticipant::factory()->create(['left_at' => null]);

        $participantData = RoomParticipantData::from($participant);

        $this->assertNull($participantData->left_at);
    }

    #[Test]
    public function it_handles_left_participants(): void
    {
        $leftAt = now();
        $participant = RoomParticipant::factory()->leftAt($leftAt)->create();

        $participantData = RoomParticipantData::from($participant);

        $this->assertNotNull($participantData->left_at);
    }

    #[Test]
    public function it_handles_mixed_character_scenarios(): void
    {
        // Scenario 1: Full character
        $user1 = User::factory()->create();
        $character1 = Character::factory()->create(['user_id' => $user1->id]);
        $participant1 = RoomParticipant::factory()->create([
            'user_id' => $user1->id,
            'character_id' => $character1->id,
            'character_name' => null,
            'character_class' => null,
        ]);

        $data1 = RoomParticipantData::from($participant1);
        $this->assertEquals($character1->id, $data1->character_id);
        $this->assertNull($data1->character_name);

        // Scenario 2: Temporary character
        $participant2 = RoomParticipant::factory()->withTemporaryCharacter()->create([
            'character_name' => 'Temp',
            'character_class' => 'Bard',
        ]);

        $data2 = RoomParticipantData::from($participant2);
        $this->assertNull($data2->character_id);
        $this->assertEquals('Temp', $data2->character_name);
        $this->assertEquals('Bard', $data2->character_class);

        // Scenario 3: No character
        $participant3 = RoomParticipant::factory()->withoutCharacter()->create();

        $data3 = RoomParticipantData::from($participant3);
        $this->assertNull($data3->character_id);
        $this->assertNull($data3->character_name);
        $this->assertNull($data3->character_class);
    }

    #[Test]
    public function it_preserves_all_relationship_data(): void
    {
        $user = User::factory()->create(['username' => 'unique_player_456']);
        $character = Character::factory()->create([
            'name' => 'Heroic Character',
            'class' => 'Ranger',
        ]);
        
        $participant = RoomParticipant::factory()->create([
            'user_id' => $user->id,
            'character_id' => $character->id,
        ]);
        $participant->load(['user', 'character']);

        $participantData = RoomParticipantData::from($participant);

        $this->assertEquals('unique_player_456', $participantData->user->username);
        $this->assertEquals('Heroic Character', $participantData->character->name);
        $this->assertEquals('Ranger', $participantData->character->class);
    }

    #[Test]
    public function it_handles_unicode_character_names(): void
    {
        $data = [
            'id' => 1,
            'room_id' => 1,
            'user_id' => 1,
            'character_id' => null,
            'character_name' => '勇者 中文 🗡️',
            'character_class' => 'Samurai 武士',
            'joined_at' => '2023-01-01 12:00:00',
            'left_at' => null,
            'created_at' => '2023-01-01 12:00:00',
            'updated_at' => '2023-01-01 12:00:00',
        ];

        $participantData = RoomParticipantData::from($data);

        $this->assertEquals('勇者 中文 🗡️', $participantData->character_name);
        $this->assertEquals('Samurai 武士', $participantData->character_class);
    }

    #[Test]
    public function it_maintains_data_consistency_across_serialization(): void
    {
        $participant = RoomParticipant::factory()->withTemporaryCharacter()->create([
            'character_name' => 'Consistent Hero',
            'character_class' => 'Druid',
        ]);

        $originalData = RoomParticipantData::from($participant);
        $serialized = json_encode($originalData->toArray());
        $unserialized = json_decode($serialized, true);
        $restoredData = RoomParticipantData::from($unserialized);

        $this->assertEquals($originalData->character_name, $restoredData->character_name);
        $this->assertEquals($originalData->character_class, $restoredData->character_class);
        $this->assertEquals($originalData->room_id, $restoredData->room_id);
        $this->assertEquals($originalData->user_id, $restoredData->user_id);
    }
}
