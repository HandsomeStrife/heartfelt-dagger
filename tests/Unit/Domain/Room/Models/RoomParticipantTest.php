<?php

declare(strict_types=1);
use Domain\Character\Models\Character;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('belongs to room', function () {
    $roomParticipant = RoomParticipant::factory()->create();

    expect($roomParticipant->room)->toBeInstanceOf(Room::class);
});
it('belongs to user', function () {
    $roomParticipant = RoomParticipant::factory()->create();

    expect($roomParticipant->user)->toBeInstanceOf(User::class);
});
it('belongs to character', function () {
    $roomParticipant = RoomParticipant::factory()->create();

    expect($roomParticipant->character)->toBeInstanceOf(Character::class);
});
it('can have null character', function () {
    $roomParticipant = RoomParticipant::factory()->withoutCharacter()->create();

    expect($roomParticipant->character_id)->toBeNull();
    expect($roomParticipant->character)->toBeNull();
});
it('checks if has character', function () {
    $withCharacter = RoomParticipant::factory()->create();
    $withoutCharacter = RoomParticipant::factory()->withoutCharacter()->create();

    expect($withCharacter->hasCharacter())->toBeTrue();
    expect($withoutCharacter->hasCharacter())->toBeFalse();
});
it('gets display name with character', function () {
    $character = Character::factory()->create(['name' => 'Aragorn']);
    $participant = RoomParticipant::factory()->create(['character_id' => $character->id]);

    expect($participant->getDisplayName())->toEqual('Aragorn');
});
it('gets display name with temporary character', function () {
    $participant = RoomParticipant::factory()->withTemporaryCharacter()->create([
        'character_name' => 'Legolas',
    ]);

    expect($participant->getDisplayName())->toEqual('Legolas');
});
it('gets display name without character', function () {
    $user = User::factory()->create(['username' => 'unique_player_123']);
    $participant = RoomParticipant::factory()->withoutCharacter()->create([
        'user_id' => $user->id,
        'character_name' => null,
    ]);

    expect($participant->getDisplayName())->toEqual('unique_player_123');
});
it('gets character class with character', function () {
    $character = Character::factory()->create(['class' => 'Warrior']);
    $participant = RoomParticipant::factory()->create(['character_id' => $character->id]);

    expect($participant->getCharacterClass())->toEqual('Warrior');
});
it('gets character class with temporary character', function () {
    $participant = RoomParticipant::factory()->withTemporaryCharacter()->create([
        'character_class' => 'Rogue',
    ]);

    expect($participant->getCharacterClass())->toEqual('Rogue');
});
it('gets null character class without character', function () {
    $participant = RoomParticipant::factory()->withoutCharacter()->create();

    expect($participant->getCharacterClass())->toBeNull();
});
it('gets character subclass with character', function () {
    $character = Character::factory()->create(['subclass' => 'Vengeance']);
    $participant = RoomParticipant::factory()->create(['character_id' => $character->id]);

    expect($participant->getCharacterSubclass())->toEqual('Vengeance');
});
it('gets null character subclass without character', function () {
    $participant = RoomParticipant::factory()->withoutCharacter()->create();

    expect($participant->getCharacterSubclass())->toBeNull();
});
it('gets character ancestry with character', function () {
    $character = Character::factory()->create(['ancestry' => 'Human']);
    $participant = RoomParticipant::factory()->create(['character_id' => $character->id]);

    expect($participant->getCharacterAncestry())->toEqual('Human');
});
it('gets null character ancestry without character', function () {
    $participant = RoomParticipant::factory()->withoutCharacter()->create();

    expect($participant->getCharacterAncestry())->toBeNull();
});
it('gets character community with character', function () {
    $character = Character::factory()->create(['community' => 'Wildborne']);
    $participant = RoomParticipant::factory()->create(['character_id' => $character->id]);

    expect($participant->getCharacterCommunity())->toEqual('Wildborne');
});
it('gets null character community without character', function () {
    $participant = RoomParticipant::factory()->withoutCharacter()->create();

    expect($participant->getCharacterCommunity())->toBeNull();
});
it('checks if active', function () {
    $activeParticipant = RoomParticipant::factory()->create(['left_at' => null]);
    $inactiveParticipant = RoomParticipant::factory()->leftAt(now())->create();

    expect($activeParticipant->isActive())->toBeTrue();
    expect($inactiveParticipant->isActive())->toBeFalse();
});
it('scopes active participants', function () {
    $room = Room::factory()->create();

    RoomParticipant::factory()->count(3)->create([
        'room_id' => $room->id,
        'left_at' => null,
    ]);

    RoomParticipant::factory()->count(2)->leftAt(now())->create([
        'room_id' => $room->id,
    ]);

    $activeParticipants = RoomParticipant::active()->where('room_id', $room->id)->get();

    expect($activeParticipants)->toHaveCount(3);
    expect($activeParticipants->every(fn ($p) => $p->left_at === null))->toBeTrue();
});
it('scopes participants with characters', function () {
    $room = Room::factory()->create();

    RoomParticipant::factory()->count(2)->create(['room_id' => $room->id]);
    RoomParticipant::factory()->count(3)->withoutCharacter()->create(['room_id' => $room->id]);

    $withCharacters = RoomParticipant::withCharacters()->where('room_id', $room->id)->get();

    expect($withCharacters)->toHaveCount(2);
    expect($withCharacters->every(fn ($p) => $p->character_id !== null))->toBeTrue();
});
it('scopes participants without characters', function () {
    $room = Room::factory()->create();

    RoomParticipant::factory()->count(2)->create(['room_id' => $room->id]);
    RoomParticipant::factory()->count(3)->withoutCharacter()->create(['room_id' => $room->id]);

    $withoutCharacters = RoomParticipant::withoutCharacters()->where('room_id', $room->id)->get();

    expect($withoutCharacters)->toHaveCount(3);
    expect($withoutCharacters->every(fn ($p) => $p->character_id === null))->toBeTrue();
});
it('casts joined at to datetime', function () {
    $participant = RoomParticipant::factory()->create();

    expect($participant->joined_at)->toBeInstanceOf(\Carbon\Carbon::class);
});
it('casts left at to datetime', function () {
    $participant = RoomParticipant::factory()->leftAt(now())->create();

    expect($participant->left_at)->toBeInstanceOf(\Carbon\Carbon::class);
});
it('handles missing character gracefully', function () {
    $participant = RoomParticipant::factory()->withoutCharacter()->create([
        'character_name' => null,
        'character_class' => null,
    ]);

    expect($participant->getCharacterClass())->toBeNull();
    expect($participant->getCharacterSubclass())->toBeNull();
    expect($participant->getCharacterAncestry())->toBeNull();
    expect($participant->getCharacterCommunity())->toBeNull();
    expect($participant->getDisplayName())->toEqual($participant->user->username);
});
