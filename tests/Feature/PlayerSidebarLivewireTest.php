<?php

declare(strict_types=1);

use App\Livewire\RoomSidebar\PlayerSidebar;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterDomainCard;
use Domain\Character\Models\CharacterEquipment;
use Domain\Character\Models\CharacterExperience;
use Domain\Room\Data\RoomParticipantData;
use Domain\User\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('player sidebar renders with character data', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Test Hero',
        'class' => 'warrior',
        'ancestry' => 'human',
        'community' => 'wildborne',
    ]);

    $participant = RoomParticipantData::from([
        'id' => 1,
        'room_id' => 1,
        'user_id' => $this->user->id,
        'character_id' => $character->id,
        'character_name' => null,
        'character_class' => null,
        'joined_at' => now()->toDateTimeString(),
        'left_at' => null,
        'created_at' => now()->toDateTimeString(),
        'updated_at' => now()->toDateTimeString(),
    ]);

    Livewire::test(PlayerSidebar::class, ['currentParticipant' => $participant])
        ->assertSee('Test Hero')
        ->assertSee('warrior')
        ->assertSee('human')
        ->assertSee('Health')
        ->assertSee('Equipment')
        ->assertSee('Abilities')
        ->assertSee('Notes');
});

test('player sidebar renders empty state when no character', function () {
    $participant = RoomParticipantData::from([
        'id' => 1,
        'room_id' => 1,
        'user_id' => $this->user->id,
        'character_id' => null,
        'character_name' => 'Temp Character',
        'character_class' => 'warrior',
        'joined_at' => now()->toDateTimeString(),
        'left_at' => null,
        'created_at' => now()->toDateTimeString(),
        'updated_at' => now()->toDateTimeString(),
    ]);

    Livewire::test(PlayerSidebar::class, ['currentParticipant' => $participant])
        ->assertSee('No Character Linked')
        ->assertSee('Temp Character')
        ->assertSee('Temporary Character Info');
});

test('player sidebar displays character stats correctly', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Test Hero',
        'class' => 'warrior',
    ]);

    $participant = RoomParticipantData::from([
        'id' => 1,
        'room_id' => 1,
        'user_id' => $this->user->id,
        'character_id' => $character->id,
        'character_name' => null,
        'character_class' => null,
        'joined_at' => now()->toDateTimeString(),
        'left_at' => null,
        'created_at' => now()->toDateTimeString(),
        'updated_at' => now()->toDateTimeString(),
    ]);

    $component = Livewire::test(PlayerSidebar::class, ['currentParticipant' => $participant]);
    
    // Check that computed stats are available
    $computedStats = $component->instance()->getComputedStats();
    expect($computedStats)->toHaveKeys(['evasion', 'hit_points', 'stress', 'hope']);
    
    $component->assertSee('Evasion')
        ->assertSee('Armor Score');
});

test('player sidebar shows equipment when character has equipment', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Test Hero',
        'class' => 'warrior',
    ]);

    // Add some equipment
    CharacterEquipment::factory()->create([
        'character_id' => $character->id,
        'equipment_type' => 'weapon',
        'equipment_key' => 'sword',
        'equipment_data' => ['name' => 'Iron Sword', 'damage' => '1d8'],
        'is_equipped' => true,
    ]);

    $participant = RoomParticipantData::from([
        'id' => 1,
        'room_id' => 1,
        'user_id' => $this->user->id,
        'character_id' => $character->id,
        'character_name' => null,
        'character_class' => null,
        'joined_at' => now()->toDateTimeString(),
        'left_at' => null,
        'created_at' => now()->toDateTimeString(),
        'updated_at' => now()->toDateTimeString(),
    ]);

    Livewire::test(PlayerSidebar::class, ['currentParticipant' => $participant])
        ->assertSee('Iron Sword')
        ->assertSee('Equipped')
        ->assertSee('All Equipment');
});

test('player sidebar shows domain cards when character has them', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Test Hero',
        'class' => 'warrior',
    ]);

    // Add a domain card
    CharacterDomainCard::factory()->create([
        'character_id' => $character->id,
        'domain' => 'blade',
        'ability_key' => 'blade-strike',
        'level' => 1,
    ]);

    $participant = RoomParticipantData::from([
        'id' => 1,
        'room_id' => 1,
        'user_id' => $this->user->id,
        'character_id' => $character->id,
        'character_name' => null,
        'character_class' => null,
        'joined_at' => now()->toDateTimeString(),
        'left_at' => null,
        'created_at' => now()->toDateTimeString(),
        'updated_at' => now()->toDateTimeString(),
    ]);

    Livewire::test(PlayerSidebar::class, ['currentParticipant' => $participant])
        ->assertSee('Domain Cards')
        ->assertSee('blade');
});

test('player sidebar shows experiences when character has them', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Test Hero',
        'class' => 'warrior',
    ]);

    // Add experiences
    CharacterExperience::factory()->create([
        'character_id' => $character->id,
        'experience_name' => 'Combat Training',
    ]);

    CharacterExperience::factory()->create([
        'character_id' => $character->id,
        'experience_name' => 'Wilderness Survival',
    ]);

    $participant = RoomParticipantData::from([
        'id' => 1,
        'room_id' => 1,
        'user_id' => $this->user->id,
        'character_id' => $character->id,
        'character_name' => null,
        'character_class' => null,
        'joined_at' => now()->toDateTimeString(),
        'left_at' => null,
        'created_at' => now()->toDateTimeString(),
        'updated_at' => now()->toDateTimeString(),
    ]);

    Livewire::test(PlayerSidebar::class, ['currentParticipant' => $participant])
        ->assertSee('Combat Training')
        ->assertSee('Wilderness Survival');
});

test('player sidebar loads game data correctly', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Test Hero',
        'class' => 'warrior',
    ]);

    $participant = RoomParticipantData::from([
        'id' => 1,
        'room_id' => 1,
        'user_id' => $this->user->id,
        'character_id' => $character->id,
        'character_name' => null,
        'character_class' => null,
        'joined_at' => now()->toDateTimeString(),
        'left_at' => null,
        'created_at' => now()->toDateTimeString(),
        'updated_at' => now()->toDateTimeString(),
    ]);

    $component = Livewire::test(PlayerSidebar::class, ['currentParticipant' => $participant]);
    
    // Check that game data is loaded
    $gameData = $component->instance()->game_data;
    expect($gameData)->toHaveKeys(['classes', 'ancestries', 'communities']);
});

test('player sidebar handles null participant gracefully', function () {
    Livewire::test(PlayerSidebar::class, ['currentParticipant' => null])
        ->assertSee('No participant information available');
});
