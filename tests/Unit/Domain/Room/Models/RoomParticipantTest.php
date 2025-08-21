<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Room\Models;

use Domain\Character\Models\Character;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RoomParticipantTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_belongs_to_room(): void
    {
        $roomParticipant = RoomParticipant::factory()->create();

        $this->assertInstanceOf(Room::class, $roomParticipant->room);
    }

    #[Test]
    public function it_belongs_to_user(): void
    {
        $roomParticipant = RoomParticipant::factory()->create();

        $this->assertInstanceOf(User::class, $roomParticipant->user);
    }

    #[Test]
    public function it_belongs_to_character(): void
    {
        $roomParticipant = RoomParticipant::factory()->create();

        $this->assertInstanceOf(Character::class, $roomParticipant->character);
    }

    #[Test]
    public function it_can_have_null_character(): void
    {
        $roomParticipant = RoomParticipant::factory()->withoutCharacter()->create();

        $this->assertNull($roomParticipant->character_id);
        $this->assertNull($roomParticipant->character);
    }

    #[Test]
    public function it_checks_if_has_character(): void
    {
        $withCharacter = RoomParticipant::factory()->create();
        $withoutCharacter = RoomParticipant::factory()->withoutCharacter()->create();

        $this->assertTrue($withCharacter->hasCharacter());
        $this->assertFalse($withoutCharacter->hasCharacter());
    }

    #[Test]
    public function it_gets_display_name_with_character(): void
    {
        $character = Character::factory()->create(['name' => 'Aragorn']);
        $participant = RoomParticipant::factory()->create(['character_id' => $character->id]);

        $this->assertEquals('Aragorn', $participant->getDisplayName());
    }

    #[Test]
    public function it_gets_display_name_with_temporary_character(): void
    {
        $participant = RoomParticipant::factory()->withTemporaryCharacter()->create([
            'character_name' => 'Legolas'
        ]);

        $this->assertEquals('Legolas', $participant->getDisplayName());
    }

    #[Test]
    public function it_gets_display_name_without_character(): void
    {
        $user = User::factory()->create(['username' => 'unique_player_123']);
        $participant = RoomParticipant::factory()->withoutCharacter()->create([
            'user_id' => $user->id,
            'character_name' => null
        ]);

        $this->assertEquals('unique_player_123', $participant->getDisplayName());
    }

    #[Test]
    public function it_gets_character_class_with_character(): void
    {
        $character = Character::factory()->create(['class' => 'Warrior']);
        $participant = RoomParticipant::factory()->create(['character_id' => $character->id]);

        $this->assertEquals('Warrior', $participant->getCharacterClass());
    }

    #[Test]
    public function it_gets_character_class_with_temporary_character(): void
    {
        $participant = RoomParticipant::factory()->withTemporaryCharacter()->create([
            'character_class' => 'Rogue'
        ]);

        $this->assertEquals('Rogue', $participant->getCharacterClass());
    }

    #[Test]
    public function it_gets_null_character_class_without_character(): void
    {
        $participant = RoomParticipant::factory()->withoutCharacter()->create();

        $this->assertNull($participant->getCharacterClass());
    }

    #[Test]
    public function it_gets_character_subclass_with_character(): void
    {
        $character = Character::factory()->create(['subclass' => 'Vengeance']);
        $participant = RoomParticipant::factory()->create(['character_id' => $character->id]);

        $this->assertEquals('Vengeance', $participant->getCharacterSubclass());
    }

    #[Test]
    public function it_gets_null_character_subclass_without_character(): void
    {
        $participant = RoomParticipant::factory()->withoutCharacter()->create();

        $this->assertNull($participant->getCharacterSubclass());
    }

    #[Test]
    public function it_gets_character_ancestry_with_character(): void
    {
        $character = Character::factory()->create(['ancestry' => 'Human']);
        $participant = RoomParticipant::factory()->create(['character_id' => $character->id]);

        $this->assertEquals('Human', $participant->getCharacterAncestry());
    }

    #[Test]
    public function it_gets_null_character_ancestry_without_character(): void
    {
        $participant = RoomParticipant::factory()->withoutCharacter()->create();

        $this->assertNull($participant->getCharacterAncestry());
    }

    #[Test]
    public function it_gets_character_community_with_character(): void
    {
        $character = Character::factory()->create(['community' => 'Wildborne']);
        $participant = RoomParticipant::factory()->create(['character_id' => $character->id]);

        $this->assertEquals('Wildborne', $participant->getCharacterCommunity());
    }

    #[Test]
    public function it_gets_null_character_community_without_character(): void
    {
        $participant = RoomParticipant::factory()->withoutCharacter()->create();

        $this->assertNull($participant->getCharacterCommunity());
    }

    #[Test]
    public function it_checks_if_active(): void
    {
        $activeParticipant = RoomParticipant::factory()->create(['left_at' => null]);
        $inactiveParticipant = RoomParticipant::factory()->leftAt(now())->create();

        $this->assertTrue($activeParticipant->isActive());
        $this->assertFalse($inactiveParticipant->isActive());
    }

    #[Test]
    public function it_scopes_active_participants(): void
    {
        $room = Room::factory()->create();
        
        RoomParticipant::factory()->count(3)->create([
            'room_id' => $room->id,
            'left_at' => null
        ]);
        
        RoomParticipant::factory()->count(2)->leftAt(now())->create([
            'room_id' => $room->id
        ]);

        $activeParticipants = RoomParticipant::active()->where('room_id', $room->id)->get();

        $this->assertCount(3, $activeParticipants);
        $this->assertTrue($activeParticipants->every(fn($p) => $p->left_at === null));
    }

    #[Test]
    public function it_scopes_participants_with_characters(): void
    {
        $room = Room::factory()->create();
        
        RoomParticipant::factory()->count(2)->create(['room_id' => $room->id]);
        RoomParticipant::factory()->count(3)->withoutCharacter()->create(['room_id' => $room->id]);

        $withCharacters = RoomParticipant::withCharacters()->where('room_id', $room->id)->get();

        $this->assertCount(2, $withCharacters);
        $this->assertTrue($withCharacters->every(fn($p) => $p->character_id !== null));
    }

    #[Test]
    public function it_scopes_participants_without_characters(): void
    {
        $room = Room::factory()->create();
        
        RoomParticipant::factory()->count(2)->create(['room_id' => $room->id]);
        RoomParticipant::factory()->count(3)->withoutCharacter()->create(['room_id' => $room->id]);

        $withoutCharacters = RoomParticipant::withoutCharacters()->where('room_id', $room->id)->get();

        $this->assertCount(3, $withoutCharacters);
        $this->assertTrue($withoutCharacters->every(fn($p) => $p->character_id === null));
    }

    #[Test]
    public function it_casts_joined_at_to_datetime(): void
    {
        $participant = RoomParticipant::factory()->create();

        $this->assertInstanceOf(\Carbon\Carbon::class, $participant->joined_at);
    }

    #[Test]
    public function it_casts_left_at_to_datetime(): void
    {
        $participant = RoomParticipant::factory()->leftAt(now())->create();

        $this->assertInstanceOf(\Carbon\Carbon::class, $participant->left_at);
    }

    #[Test]
    public function it_handles_missing_character_gracefully(): void
    {
        $participant = RoomParticipant::factory()->withoutCharacter()->create([
            'character_name' => null,
            'character_class' => null
        ]);

        $this->assertNull($participant->getCharacterClass());
        $this->assertNull($participant->getCharacterSubclass());
        $this->assertNull($participant->getCharacterAncestry());
        $this->assertNull($participant->getCharacterCommunity());
        $this->assertEquals($participant->user->username, $participant->getDisplayName());
    }
}
