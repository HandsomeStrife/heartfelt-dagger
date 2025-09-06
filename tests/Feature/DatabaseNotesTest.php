<?php

declare(strict_types=1);

use Domain\Character\Actions\LoadCharacterNotesAction;
use Domain\Character\Actions\SaveCharacterNotesAction;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterNotes;
use Domain\Room\Actions\LoadRoomSessionNotesAction;
use Domain\Room\Actions\SaveRoomSessionNotesAction;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomSessionNotes;
use Domain\User\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->room = Room::factory()->create();
    $this->character = Character::factory()->create(['user_id' => $this->user->id]);
});

test('room session notes can be saved and loaded', function () {
    $notes_content = 'These are my GM session notes for this room.';
    
    // Save notes
    $save_action = new SaveRoomSessionNotesAction();
    $saved_notes = $save_action->execute($this->room, $this->user, $notes_content);
    
    expect($saved_notes)->toBeInstanceOf(RoomSessionNotes::class);
    expect($saved_notes->notes)->toBe($notes_content);
    expect($saved_notes->room_id)->toBe($this->room->id);
    expect($saved_notes->user_id)->toBe($this->user->id);
    
    // Load notes
    $load_action = new LoadRoomSessionNotesAction();
    $loaded_notes = $load_action->execute($this->room, $this->user);
    
    expect($loaded_notes->notes)->toBe($notes_content);
});

test('room session notes are unique per room and user', function () {
    $user2 = User::factory()->create();
    $room2 = Room::factory()->create();
    
    $notes1 = 'Notes for user 1 in room 1';
    $notes2 = 'Notes for user 2 in room 1';
    $notes3 = 'Notes for user 1 in room 2';
    
    $save_action = new SaveRoomSessionNotesAction();
    
    // Save different notes for different user/room combinations
    $save_action->execute($this->room, $this->user, $notes1);
    $save_action->execute($this->room, $user2, $notes2);
    $save_action->execute($room2, $this->user, $notes3);
    
    $load_action = new LoadRoomSessionNotesAction();
    
    // Verify each user gets their own notes for each room
    expect($load_action->execute($this->room, $this->user)->notes)->toBe($notes1);
    expect($load_action->execute($this->room, $user2)->notes)->toBe($notes2);
    expect($load_action->execute($room2, $this->user)->notes)->toBe($notes3);
});

test('room session notes can be updated', function () {
    $original_notes = 'Original session notes';
    $updated_notes = 'Updated session notes with more content';
    
    $save_action = new SaveRoomSessionNotesAction();
    $load_action = new LoadRoomSessionNotesAction();
    
    // Save original notes
    $save_action->execute($this->room, $this->user, $original_notes);
    expect($load_action->execute($this->room, $this->user)->notes)->toBe($original_notes);
    
    // Update notes
    $save_action->execute($this->room, $this->user, $updated_notes);
    expect($load_action->execute($this->room, $this->user)->notes)->toBe($updated_notes);
    
    // Verify only one record exists
    $count = RoomSessionNotes::where('room_id', $this->room->id)
        ->where('user_id', $this->user->id)
        ->count();
    expect($count)->toBe(1);
});

test('character notes can be saved and loaded', function () {
    $notes_content = 'These are my character notes with backstory and details.';
    
    // Save notes
    $save_action = new SaveCharacterNotesAction();
    $saved_notes = $save_action->execute($this->character, $this->user, $notes_content);
    
    expect($saved_notes)->toBeInstanceOf(CharacterNotes::class);
    expect($saved_notes->notes)->toBe($notes_content);
    expect($saved_notes->character_id)->toBe($this->character->id);
    expect($saved_notes->user_id)->toBe($this->user->id);
    
    // Load notes
    $load_action = new LoadCharacterNotesAction();
    $loaded_notes = $load_action->execute($this->character, $this->user);
    
    expect($loaded_notes->notes)->toBe($notes_content);
});

test('character notes are unique per character and user', function () {
    $user2 = User::factory()->create();
    $character2 = Character::factory()->create(['user_id' => $user2->id]);
    
    $notes1 = 'Notes for user 1 character 1';
    $notes2 = 'Notes for user 2 character 2';
    
    $save_action = new SaveCharacterNotesAction();
    
    // Save notes for different user/character combinations
    $save_action->execute($this->character, $this->user, $notes1);
    $save_action->execute($character2, $user2, $notes2);
    
    $load_action = new LoadCharacterNotesAction();
    
    // Verify each user gets their own character notes
    expect($load_action->execute($this->character, $this->user)->notes)->toBe($notes1);
    expect($load_action->execute($character2, $user2)->notes)->toBe($notes2);
});

test('character notes can be updated', function () {
    $original_notes = 'Original character backstory';
    $updated_notes = 'Updated character backstory with more details';
    
    $save_action = new SaveCharacterNotesAction();
    $load_action = new LoadCharacterNotesAction();
    
    // Save original notes
    $save_action->execute($this->character, $this->user, $original_notes);
    expect($load_action->execute($this->character, $this->user)->notes)->toBe($original_notes);
    
    // Update notes
    $save_action->execute($this->character, $this->user, $updated_notes);
    expect($load_action->execute($this->character, $this->user)->notes)->toBe($updated_notes);
    
    // Verify only one record exists
    $count = CharacterNotes::where('character_id', $this->character->id)
        ->where('user_id', $this->user->id)
        ->count();
    expect($count)->toBe(1);
});

test('notes are created with empty content when none exist', function () {
    // Load notes that don't exist yet
    $load_room_action = new LoadRoomSessionNotesAction();
    $room_notes = $load_room_action->execute($this->room, $this->user);
    
    expect($room_notes->notes)->toBe('');
    expect($room_notes->room_id)->toBe($this->room->id);
    expect($room_notes->user_id)->toBe($this->user->id);
    
    $load_character_action = new LoadCharacterNotesAction();
    $character_notes = $load_character_action->execute($this->character, $this->user);
    
    expect($character_notes->notes)->toBe('');
    expect($character_notes->character_id)->toBe($this->character->id);
    expect($character_notes->user_id)->toBe($this->user->id);
});

test('notes models have proper relationships', function () {
    $room_notes = RoomSessionNotes::factory()->create([
        'room_id' => $this->room->id,
        'user_id' => $this->user->id,
    ]);
    
    $character_notes = CharacterNotes::factory()->create([
        'character_id' => $this->character->id,
        'user_id' => $this->user->id,
    ]);
    
    // Test room notes relationships
    expect($room_notes->room)->toBeInstanceOf(Room::class);
    expect($room_notes->user)->toBeInstanceOf(User::class);
    expect($room_notes->room->id)->toBe($this->room->id);
    expect($room_notes->user->id)->toBe($this->user->id);
    
    // Test character notes relationships
    expect($character_notes->character)->toBeInstanceOf(Character::class);
    expect($character_notes->user)->toBeInstanceOf(User::class);
    expect($character_notes->character->id)->toBe($this->character->id);
    expect($character_notes->user->id)->toBe($this->user->id);
});
