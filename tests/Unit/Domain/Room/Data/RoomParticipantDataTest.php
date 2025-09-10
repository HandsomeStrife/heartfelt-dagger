<?php

declare(strict_types=1);
use Domain\Character\Models\Character;
use Domain\Room\Data\RoomParticipantData;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;
use Livewire\Wireable;

it('implements wireable interface', function () {
    $participant = RoomParticipant::factory()->create();
    $participantData = RoomParticipantData::from($participant);

    expect($participantData)->toBeInstanceOf(Wireable::class);
});
it('creates from array', function () {
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

    expect($participantData->id)->toEqual(1);
    expect($participantData->room_id)->toEqual(1);
    expect($participantData->user_id)->toEqual(1);
    expect($participantData->character_id)->toEqual(1);
    expect($participantData->character_name)->toEqual('Temp Hero');
    expect($participantData->character_class)->toEqual('Warrior');
    expect($participantData->joined_at)->toEqual('2023-01-01 12:00:00');
    expect($participantData->left_at)->toBeNull();
});
it('creates from model', function () {
    $participant = RoomParticipant::factory()->create();

    $participantData = RoomParticipantData::from($participant);

    expect($participantData->id)->toEqual($participant->id);
    expect($participantData->room_id)->toEqual($participant->room_id);
    expect($participantData->user_id)->toEqual($participant->user_id);
    expect($participantData->character_id)->toEqual($participant->character_id);
    expect($participantData->character_name)->toEqual($participant->character_name);
    expect($participantData->character_class)->toEqual($participant->character_class);
});
it('creates from model with relationships', function () {
    $user = User::factory()->create(['username' => 'test_user']);
    $character = Character::factory()->create(['name' => 'Hero']);
    $participant = RoomParticipant::factory()->create([
        'user_id' => $user->id,
        'character_id' => $character->id,
    ]);
    $participant->load(['user', 'character']);

    $participantData = RoomParticipantData::from($participant);

    expect($participantData->user)->not->toBeNull();
    expect($participantData->character)->not->toBeNull();
    expect($participantData->user->username)->toEqual('test_user');
    expect($participantData->character->name)->toEqual('Hero');
});
it('handles null character', function () {
    $participant = RoomParticipant::factory()->withoutCharacter()->create();

    $participantData = RoomParticipantData::from($participant);

    expect($participantData->character_id)->toBeNull();
    expect($participantData->character)->toBeNull();
});
it('handles temporary character data', function () {
    $participant = RoomParticipant::factory()->withTemporaryCharacter()->create([
        'character_name' => 'Temporary Hero',
        'character_class' => 'Rogue',
    ]);

    $participantData = RoomParticipantData::from($participant);

    expect($participantData->character_id)->toBeNull();
    expect($participantData->character_name)->toEqual('Temporary Hero');
    expect($participantData->character_class)->toEqual('Rogue');
});
it('handles null optional fields', function () {
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

    expect($participantData->id)->toBeNull();
    expect($participantData->character_id)->toBeNull();
    expect($participantData->character_name)->toBeNull();
    expect($participantData->character_class)->toBeNull();
    expect($participantData->joined_at)->toBeNull();
    expect($participantData->left_at)->toBeNull();
    expect($participantData->user)->toBeNull();
    expect($participantData->character)->toBeNull();
});
it('preserves datetime strings', function () {
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

    expect($participantData->joined_at)->toEqual($joinedAt);
    expect($participantData->left_at)->toEqual($leftAt);
});
it('works with livewire to livewire', function () {
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

    expect($restoredData->id)->toEqual($participantData->id);
    expect($restoredData->room_id)->toEqual($participantData->room_id);
    expect($restoredData->user_id)->toEqual($participantData->user_id);
    expect($restoredData->character_id)->toEqual($participantData->character_id);
    expect($restoredData->character_name)->toEqual($participantData->character_name);
    expect($restoredData->character_class)->toEqual($participantData->character_class);
});
it('works with livewire from livewire', function () {
    $participant = RoomParticipant::factory()->create();
    $participantData = RoomParticipantData::from($participant);

    $livewireArray = $participantData->toLivewire();
    $restoredData = RoomParticipantData::fromLivewire($livewireArray);

    expect($restoredData->id)->toEqual($participantData->id);
    expect($restoredData->room_id)->toEqual($participantData->room_id);
    expect($restoredData->user_id)->toEqual($participantData->user_id);
    expect($restoredData->character_id)->toEqual($participantData->character_id);
});
it('handles active participants', function () {
    $participant = RoomParticipant::factory()->create(['left_at' => null]);

    $participantData = RoomParticipantData::from($participant);

    expect($participantData->left_at)->toBeNull();
});
it('handles left participants', function () {
    $leftAt = now();
    $participant = RoomParticipant::factory()->leftAt($leftAt)->create();

    $participantData = RoomParticipantData::from($participant);

    expect($participantData->left_at)->not->toBeNull();
});
it('handles mixed character scenarios', function () {
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
    expect($data1->character_id)->toEqual($character1->id);
    expect($data1->character_name)->toBeNull();

    // Scenario 2: Temporary character
    $participant2 = RoomParticipant::factory()->withTemporaryCharacter()->create([
        'character_name' => 'Temp',
        'character_class' => 'Bard',
    ]);

    $data2 = RoomParticipantData::from($participant2);
    expect($data2->character_id)->toBeNull();
    expect($data2->character_name)->toEqual('Temp');
    expect($data2->character_class)->toEqual('Bard');

    // Scenario 3: No character
    $participant3 = RoomParticipant::factory()->withoutCharacter()->create();

    $data3 = RoomParticipantData::from($participant3);
    expect($data3->character_id)->toBeNull();
    expect($data3->character_name)->toBeNull();
    expect($data3->character_class)->toBeNull();
});
it('preserves all relationship data', function () {
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

    expect($participantData->user->username)->toEqual('unique_player_456');
    expect($participantData->character->name)->toEqual('Heroic Character');
    expect($participantData->character->class)->toEqual('Ranger');
});
it('handles unicode character names', function () {
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

    expect($participantData->character_name)->toEqual('勇者 中文 🗡️');
    expect($participantData->character_class)->toEqual('Samurai 武士');
});
it('maintains data consistency across serialization', function () {
    $participant = RoomParticipant::factory()->withTemporaryCharacter()->create([
        'character_name' => 'Consistent Hero',
        'character_class' => 'Druid',
    ]);

    $originalData = RoomParticipantData::from($participant);
    $serialized = json_encode($originalData->toArray());
    $unserialized = json_decode($serialized, true);
    $restoredData = RoomParticipantData::from($unserialized);

    expect($restoredData->character_name)->toEqual($originalData->character_name);
    expect($restoredData->character_class)->toEqual($originalData->character_class);
    expect($restoredData->room_id)->toEqual($originalData->room_id);
    expect($restoredData->user_id)->toEqual($originalData->user_id);
});
