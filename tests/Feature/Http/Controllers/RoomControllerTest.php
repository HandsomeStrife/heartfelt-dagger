<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use Domain\Character\Models\Character;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RoomControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guest_cannot_access_room_routes(): void
    {
        $room = Room::factory()->create();

        $this->get(route('rooms.index'))->assertRedirect(route('login'));
        $this->get(route('rooms.create'))->assertRedirect(route('login'));
        $this->post(route('rooms.store'))->assertRedirect(route('login'));
        $this->get(route('rooms.show', $room))->assertRedirect(route('login'));
        $this->post(route('rooms.join', $room))->assertRedirect(route('login'));
        $this->delete(route('rooms.leave', $room))->assertRedirect(route('login'));
        $this->get(route('rooms.session', $room))->assertRedirect(route('login'));
    }

    #[Test]
    public function guest_can_access_invite_route(): void
    {
        $room = Room::factory()->create();

        $response = $this->get(route('rooms.invite', $room->invite_code));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function authenticated_user_can_view_rooms_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('rooms.index'));

        $response->assertOk();
        $response->assertViewIs('rooms.index');
        $response->assertViewHas(['created_rooms', 'joined_rooms']);
    }

    #[Test]
    public function rooms_index_displays_created_and_joined_rooms(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        
        // Rooms created by user
        $createdRoom = Room::factory()->create(['creator_id' => $user->id, 'name' => 'My Room']);
        
        // Rooms joined by user
        $joinedRoom = Room::factory()->create(['creator_id' => $otherUser->id, 'name' => 'Joined Room']);
        RoomParticipant::factory()->create([
            'room_id' => $joinedRoom->id,
            'user_id' => $user->id,
            'left_at' => null
        ]);
        
        // Other room not related to user
        Room::factory()->create(['creator_id' => $otherUser->id, 'name' => 'Other Room']);

        $response = $this->actingAs($user)->get(route('rooms.index'));

        $response->assertSee('My Room');
        $response->assertSee('Joined Room');
        $response->assertDontSee('Other Room');
    }

    #[Test]
    public function authenticated_user_can_view_create_room_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('rooms.create'));

        $response->assertOk();
        $response->assertViewIs('rooms.create');
    }

    #[Test]
    public function user_can_create_room_with_valid_data(): void
    {
        $user = User::factory()->create();
        
        $roomData = [
            'name' => 'Epic Adventure Room',
            'description' => 'A room for our weekly session',
            'password' => 'secret123',
            'guest_count' => 4
        ];

        $response = $this->actingAs($user)->post(route('rooms.store'), $roomData);

        $response->assertRedirect();
        $this->assertDatabaseHas('rooms', [
            'name' => 'Epic Adventure Room',
            'description' => 'A room for our weekly session',
            'guest_count' => 4,
            'creator_id' => $user->id,
            'status' => 'active'
        ]);
        
        $room = Room::where('name', 'Epic Adventure Room')->first();
        $response->assertRedirect(route('rooms.show', $room));
        $response->assertSessionHas('success', 'Room created successfully!');
    }

    #[Test]
    public function room_creation_validates_required_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('rooms.store'), []);

        $response->assertSessionHasErrors(['name', 'description', 'password', 'guest_count']);
    }

    #[Test]
    public function room_creation_validates_field_lengths(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('rooms.store'), [
            'name' => str_repeat('A', 101), // Too long
            'description' => str_repeat('B', 501), // Too long
            'password' => str_repeat('P', 256), // Too long
            'guest_count' => 2
        ]);

        $response->assertSessionHasErrors(['name', 'description', 'password']);
    }

    #[Test]
    public function room_creation_validates_guest_count_range(): void
    {
        $user = User::factory()->create();

        // Test minimum
        $response = $this->actingAs($user)->post(route('rooms.store'), [
            'name' => 'Test Room',
            'description' => 'Test Description',
            'password' => 'password',
            'guest_count' => 0
        ]);
        $response->assertSessionHasErrors(['guest_count']);

        // Test maximum
        $response = $this->actingAs($user)->post(route('rooms.store'), [
            'name' => 'Test Room',
            'description' => 'Test Description',
            'password' => 'password',
            'guest_count' => 6
        ]);
        $response->assertSessionHasErrors(['guest_count']);
    }

    #[Test]
    public function user_can_view_room_details(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['name' => 'Test Room']);
        RoomParticipant::factory()->count(2)->create(['room_id' => $room->id, 'left_at' => null]);

        $response = $this->actingAs($user)->get(route('rooms.show', $room));

        $response->assertOk();
        $response->assertViewIs('rooms.show');
        $response->assertViewHas(['room', 'participants', 'user_is_creator', 'user_is_participant']);
        $response->assertSee('Test Room');
    }

    #[Test]
    public function room_show_displays_participant_information(): void
    {
        $user = User::factory()->create(['username' => 'testuser']);
        $character = Character::factory()->create(['name' => 'Hero', 'class' => 'Warrior']);
        $room = Room::factory()->create();
        
        RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'character_id' => $character->id,
            'left_at' => null
        ]);

        $response = $this->actingAs($user)->get(route('rooms.show', $room));

        $response->assertSee('Hero');
        $response->assertSee('Warrior');
        $response->assertSee('testuser');
    }

    #[Test]
    public function user_can_view_invite_page_with_valid_code(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['name' => 'Invite Room']);

        $response = $this->actingAs($user)->get(route('rooms.invite', $room->invite_code));

        $response->assertOk();
        $response->assertViewIs('rooms.join');
        $response->assertViewHas(['room', 'characters']);
        $response->assertSee('Invite Room');
    }

    #[Test]
    public function invite_page_shows_404_for_invalid_code(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('rooms.invite', 'INVALID1'));

        $response->assertNotFound();
    }

    #[Test]
    public function invite_page_redirects_if_already_participating(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create();
        RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'left_at' => null
        ]);

        $response = $this->actingAs($user)->get(route('rooms.invite', $room->invite_code));

        $response->assertRedirect(route('rooms.show', $room));
        $response->assertSessionHas('info', 'You are already participating in this room.');
    }

    #[Test]
    public function invite_page_redirects_if_room_at_capacity(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['guest_count' => 1]);
        
        // Fill the room
        RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'left_at' => null
        ]);

        $response = $this->actingAs($user)->get(route('rooms.invite', $room->invite_code));

        $response->assertRedirect(route('rooms.index'));
        $response->assertSessionHas('error', 'This room is at capacity.');
    }

    #[Test]
    public function user_can_join_room_with_character(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);
        $room = Room::factory()->create();

        $response = $this->actingAs($user)->post(route('rooms.join', $room), [
            'password' => 'password', // Factory default
            'character_id' => $character->id
        ]);

        $response->assertRedirect(route('rooms.session', $room));
        $response->assertSessionHas('success', 'Successfully joined the room!');
        
        $this->assertDatabaseHas('room_participants', [
            'room_id' => $room->id,
            'user_id' => $user->id,
            'character_id' => $character->id
        ]);
    }

    #[Test]
    public function user_can_join_room_with_temporary_character(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create();

        $response = $this->actingAs($user)->post(route('rooms.join', $room), [
            'password' => 'password',
            'character_name' => 'Temp Hero',
            'character_class' => 'Rogue'
        ]);

        $response->assertRedirect(route('rooms.session', $room));
        
        $this->assertDatabaseHas('room_participants', [
            'room_id' => $room->id,
            'user_id' => $user->id,
            'character_id' => null,
            'character_name' => 'Temp Hero',
            'character_class' => 'Rogue'
        ]);
    }

    #[Test]
    public function user_can_join_room_without_character(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create();

        $response = $this->actingAs($user)->post(route('rooms.join', $room), [
            'password' => 'password'
        ]);

        $response->assertRedirect(route('rooms.session', $room));
        
        $this->assertDatabaseHas('room_participants', [
            'room_id' => $room->id,
            'user_id' => $user->id,
            'character_id' => null,
            'character_name' => null,
            'character_class' => null
        ]);
    }

    #[Test]
    public function joining_room_validates_password(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create();

        $response = $this->actingAs($user)->post(route('rooms.join', $room), [
            'password' => 'wrong_password'
        ]);

        $response->assertSessionHasErrors(['password' => 'Invalid room password.']);
        $this->assertDatabaseMissing('room_participants', [
            'room_id' => $room->id,
            'user_id' => $user->id
        ]);
    }

    #[Test]
    public function joining_room_validates_character_ownership(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherCharacter = Character::factory()->create(['user_id' => $otherUser->id]);
        $room = Room::factory()->create();

        $response = $this->actingAs($user)->post(route('rooms.join', $room), [
            'password' => 'password',
            'character_id' => $otherCharacter->id
        ]);

        $response->assertNotFound(); // ModelNotFoundException results in 404
    }

    #[Test]
    public function user_cannot_join_room_twice(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create();
        
        // Join once
        RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'left_at' => null
        ]);

        $response = $this->actingAs($user)->post(route('rooms.join', $room), [
            'password' => 'password'
        ]);

        $response->assertSessionHasErrors(['error' => 'You are already an active participant in this room.']);
    }

    #[Test]
    public function user_cannot_join_full_room(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['guest_count' => 1]);
        
        // Fill the room
        RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'left_at' => null
        ]);

        $response = $this->actingAs($user)->post(route('rooms.join', $room), [
            'password' => 'password'
        ]);

        $response->assertSessionHasErrors(['error' => 'This room is at capacity.']);
    }

    #[Test]
    public function user_can_leave_room_they_joined(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create();
        $participant = RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'left_at' => null
        ]);

        $response = $this->actingAs($user)->delete(route('rooms.leave', $room));

        $response->assertRedirect(route('rooms.index'));
        $response->assertSessionHas('success', 'Successfully left the room.');
        
        $participant->refresh();
        $this->assertNotNull($participant->left_at);
    }

    #[Test]
    public function user_cannot_leave_room_they_havent_joined(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create();

        $response = $this->actingAs($user)->delete(route('rooms.leave', $room));

        $response->assertSessionHasErrors(['error' => 'You are not an active participant in this room.']);
    }

    #[Test]
    public function user_can_access_session_if_participating(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create();
        RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'left_at' => null
        ]);

        $response = $this->actingAs($user)->get(route('rooms.session', $room));

        $response->assertOk();
        $response->assertViewIs('rooms.session');
        $response->assertViewHas(['room', 'participants']);
    }

    #[Test]
    public function user_cannot_access_session_if_not_participating(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create();

        $response = $this->actingAs($user)->get(route('rooms.session', $room));

        $response->assertRedirect(route('rooms.show', $room));
        $response->assertSessionHas('error', 'You must join the room first.');
    }

    #[Test]
    public function room_session_includes_javascript_context(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['name' => 'JS Test Room']);
        RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'left_at' => null
        ]);

        $response = $this->actingAs($user)->get(route('rooms.session', $room));

        $response->assertSee('window.roomData');
        $response->assertSee('window.currentUserId');
        $response->assertSee('JS Test Room');
        $response->assertSee('room-webrtc.js');
    }

    #[Test]
    public function rooms_with_different_guest_counts_show_correctly(): void
    {
        $user = User::factory()->create();
        
        foreach ([1, 2, 3, 4, 5] as $guestCount) {
            $room = Room::factory()->create([
                'guest_count' => $guestCount,
                'name' => "Room {$guestCount}"
            ]);

            $response = $this->actingAs($user)->get(route('rooms.show', $room));
            
            $response->assertSee("Max Guests: {$guestCount}");
        }
    }

    #[Test]
    public function room_invite_url_generation_works(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create();

        $response = $this->actingAs($user)->get(route('rooms.show', $room));

        $expectedInviteUrl = route('rooms.invite', ['invite_code' => $room->invite_code]);
        $response->assertSee($expectedInviteUrl);
    }

    #[Test]
    public function room_creator_sees_appropriate_buttons(): void
    {
        $creator = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $creator->id]);

        $response = $this->actingAs($creator)->get(route('rooms.show', $room));

        $response->assertSee('Start Session');
        $response->assertDontSee('Join Room');
    }

    #[Test]
    public function room_participant_sees_appropriate_buttons(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create();
        RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'left_at' => null
        ]);

        $response = $this->actingAs($user)->get(route('rooms.show', $room));

        $response->assertSee('Join Session');
        $response->assertSee('Leave Room');
        $response->assertDontSee('Join Room');
    }

    #[Test]
    public function non_participant_sees_join_button(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create();

        $response = $this->actingAs($user)->get(route('rooms.show', $room));

        $response->assertSee('Join Room');
        $response->assertDontSee('Start Session');
        $response->assertDontSee('Join Session');
        $response->assertDontSee('Leave Room');
    }
}
