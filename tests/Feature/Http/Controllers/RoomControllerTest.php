<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use Domain\Campaign\Models\Campaign;
use Domain\Campaign\Models\CampaignMember;
use Domain\Character\Models\Character;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
        RoomParticipant::factory()->withoutCharacter()->create([
            'room_id' => $joinedRoom->id,
            'user_id' => $user->id,
            'left_at' => null,
            'character_id' => null,
        ]);
        
        // Other room not related to user
        $otherRoom = Room::factory()->create(['creator_id' => $otherUser->id, 'name' => 'Other Room']);

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

        $response->assertSessionHasErrors(['name', 'description', 'guest_count']);
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

    // ===============================
    // CAMPAIGN ROOM TESTS
    // ===============================

    #[Test]
    public function campaign_creator_can_access_campaign_room_creation_form(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('campaigns.rooms.create', $campaign->campaign_code));

        $response->assertOk();
        $response->assertSee('Create Campaign Room');
        $response->assertSee('No Password Required');
        $response->assertDontSee('Room Password');
    }

    #[Test]
    public function campaign_member_can_access_campaign_room_creation_form(): void
    {
        $creator = User::factory()->create();
        $member = User::factory()->create();
        $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);
        
        CampaignMember::create([
            'campaign_id' => $campaign->id,
            'user_id' => $member->id,
        ]);

        $response = $this->actingAs($member)->get(route('campaigns.rooms.create', $campaign->campaign_code));

        $response->assertOk();
        $response->assertSee('Create Campaign Room');
    }

    #[Test]
    public function non_campaign_member_cannot_access_campaign_room_creation(): void
    {
        $creator = User::factory()->create();
        $outsider = User::factory()->create();
        $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

        $response = $this->actingAs($outsider)->get(route('campaigns.rooms.create', $campaign->campaign_code));

        $response->assertStatus(403);
    }

    #[Test]
    public function campaign_creator_can_create_passwordless_room(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

        $roomData = [
            'name' => 'Test Campaign Room',
            'description' => 'A room for our campaign',
            'guest_count' => 4,
        ];

        $response = $this->actingAs($user)->post(route('campaigns.rooms.store', $campaign->campaign_code), $roomData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Campaign room created successfully!');

        $this->assertDatabaseHas('rooms', [
            'name' => 'Test Campaign Room',
            'campaign_id' => $campaign->id,
            'creator_id' => $user->id,
            'password' => null, // No password for campaign rooms
        ]);
    }

    #[Test]
    public function campaign_member_can_create_passwordless_room(): void
    {
        $creator = User::factory()->create();
        $member = User::factory()->create();
        $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);
        
        CampaignMember::create([
            'campaign_id' => $campaign->id,
            'user_id' => $member->id,
        ]);

        $roomData = [
            'name' => 'Member Campaign Room',
            'description' => 'Created by campaign member',
            'guest_count' => 3,
        ];

        $response = $this->actingAs($member)->post(route('campaigns.rooms.store', $campaign->campaign_code), $roomData);

        $response->assertRedirect();
        $this->assertDatabaseHas('rooms', [
            'name' => 'Member Campaign Room',
            'campaign_id' => $campaign->id,
            'creator_id' => $member->id,
            'password' => null,
        ]);
    }

    #[Test]
    public function non_campaign_member_cannot_create_campaign_room(): void
    {
        $creator = User::factory()->create();
        $outsider = User::factory()->create();
        $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

        $roomData = [
            'name' => 'Unauthorized Room',
            'description' => 'Should not be created',
            'guest_count' => 2,
        ];

        $response = $this->actingAs($outsider)->post(route('campaigns.rooms.store', $campaign->campaign_code), $roomData);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('rooms', [
            'name' => 'Unauthorized Room',
        ]);
    }

    #[Test]
    public function campaign_room_restricts_access_to_campaign_members_only(): void
    {
        $creator = User::factory()->create();
        $member = User::factory()->create();
        $outsider = User::factory()->create();
        $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);
        
        CampaignMember::create([
            'campaign_id' => $campaign->id,
            'user_id' => $member->id,
        ]);

        $room = Room::factory()->create([
            'creator_id' => $creator->id,
            'campaign_id' => $campaign->id,
            'password' => null,
        ]);

        // Campaign creator can access
        $response = $this->actingAs($creator)->get(route('rooms.show', $room));
        $response->assertOk();

        // Campaign member can access
        $response = $this->actingAs($member)->get(route('rooms.show', $room));
        $response->assertOk();

        // Outsider cannot access
        $response = $this->actingAs($outsider)->get(route('rooms.show', $room));
        $response->assertStatus(403);
    }

    #[Test]
    public function campaign_members_can_join_passwordless_campaign_room(): void
    {
        $creator = User::factory()->create();
        $member = User::factory()->create();
        $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);
        
        CampaignMember::create([
            'campaign_id' => $campaign->id,
            'user_id' => $member->id,
        ]);

        $room = Room::factory()->create([
            'creator_id' => $creator->id,
            'campaign_id' => $campaign->id,
            'password' => null,
        ]);

        // Member joins without password
        $response = $this->actingAs($member)->post(route('rooms.join', $room), [
            'character_name' => 'Test Character',
            'character_class' => 'Warrior',
        ]);

        $response->assertRedirect(route('rooms.session', $room));
        $this->assertDatabaseHas('room_participants', [
            'room_id' => $room->id,
            'user_id' => $member->id,
            'character_name' => 'Test Character',
        ]);
    }

    #[Test]
    public function campaign_room_join_form_shows_no_password_field(): void
    {
        $creator = User::factory()->create();
        $member = User::factory()->create();
        $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);
        
        CampaignMember::create([
            'campaign_id' => $campaign->id,
            'user_id' => $member->id,
        ]);

        $room = Room::factory()->create([
            'creator_id' => $creator->id,
            'campaign_id' => $campaign->id,
            'password' => null,
        ]);

        $response = $this->actingAs($member)->get(route('rooms.invite', $room->invite_code));

        $response->assertOk();
        $response->assertSee('No Password Required');
        $response->assertSee('campaign room - access is restricted to campaign members');
        $response->assertDontSee('Room Password');
        $response->assertDontSee('Enter room password');
    }

    // ===============================
    // REGULAR ROOM OPTIONAL PASSWORD TESTS
    // ===============================

    #[Test]
    public function user_can_create_regular_room_without_password(): void
    {
        $user = User::factory()->create();

        $roomData = [
            'name' => 'Open Room',
            'description' => 'Anyone can join',
            'guest_count' => 4,
            // No password provided
        ];

        $response = $this->actingAs($user)->post(route('rooms.store'), $roomData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Room created successfully!');

        $this->assertDatabaseHas('rooms', [
            'name' => 'Open Room',
            'creator_id' => $user->id,
            'campaign_id' => null,
            'password' => null,
        ]);
    }

    #[Test]
    public function user_can_create_regular_room_with_password(): void
    {
        $user = User::factory()->create();

        $roomData = [
            'name' => 'Protected Room',
            'description' => 'Password required',
            'password' => 'secret123',
            'guest_count' => 3,
        ];

        $response = $this->actingAs($user)->post(route('rooms.store'), $roomData);

        $response->assertRedirect();

        $room = Room::where('name', 'Protected Room')->first();
        $this->assertNotNull($room);
        $this->assertNotNull($room->password);
        $this->assertTrue(Hash::check('secret123', $room->password));
    }

    #[Test]
    public function regular_room_creation_form_shows_optional_password_field(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('rooms.create'));

        $response->assertOk();
        $response->assertSee('Room Password');
        $response->assertSee('(Optional)');
        $response->assertSee('leave blank for no password');
    }

    #[Test]
    public function user_can_join_passwordless_regular_room(): void
    {
        $creator = User::factory()->create();
        $joiner = User::factory()->create();
        
        $room = Room::factory()->create([
            'creator_id' => $creator->id,
            'password' => null, // No password
            'campaign_id' => null, // Regular room
        ]);

        $response = $this->actingAs($joiner)->post(route('rooms.join', $room), [
            'character_name' => 'Open Joiner',
            'character_class' => 'Rogue',
        ]);

        $response->assertRedirect(route('rooms.session', $room));
        $this->assertDatabaseHas('room_participants', [
            'room_id' => $room->id,
            'user_id' => $joiner->id,
            'character_name' => 'Open Joiner',
        ]);
    }

    #[Test]
    public function user_can_join_password_protected_regular_room_with_correct_password(): void
    {
        $creator = User::factory()->create();
        $joiner = User::factory()->create();
        
        $room = Room::factory()->create([
            'creator_id' => $creator->id,
            'password' => Hash::make('secret123'),
            'campaign_id' => null,
        ]);

        $response = $this->actingAs($joiner)->post(route('rooms.join', $room), [
            'password' => 'secret123',
            'character_name' => 'Protected Joiner',
            'character_class' => 'Wizard',
        ]);

        $response->assertRedirect(route('rooms.session', $room));
        $this->assertDatabaseHas('room_participants', [
            'room_id' => $room->id,
            'user_id' => $joiner->id,
            'character_name' => 'Protected Joiner',
        ]);
    }

    #[Test]
    public function user_cannot_join_password_protected_room_with_wrong_password(): void
    {
        $creator = User::factory()->create();
        $joiner = User::factory()->create();
        
        $room = Room::factory()->create([
            'creator_id' => $creator->id,
            'password' => Hash::make('secret123'),
            'campaign_id' => null,
        ]);

        $response = $this->actingAs($joiner)->post(route('rooms.join', $room), [
            'password' => 'wrongpassword',
            'character_name' => 'Failed Joiner',
            'character_class' => 'Bard',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertDatabaseMissing('room_participants', [
            'room_id' => $room->id,
            'user_id' => $joiner->id,
        ]);
    }

    #[Test]
    public function user_cannot_join_password_protected_room_without_password(): void
    {
        $creator = User::factory()->create();
        $joiner = User::factory()->create();
        
        $room = Room::factory()->create([
            'creator_id' => $creator->id,
            'password' => Hash::make('secret123'),
            'campaign_id' => null,
        ]);

        $response = $this->actingAs($joiner)->post(route('rooms.join', $room), [
            // No password provided
            'character_name' => 'No Password Joiner',
            'character_class' => 'Druid',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertDatabaseMissing('room_participants', [
            'room_id' => $room->id,
            'user_id' => $joiner->id,
        ]);
    }

    #[Test]
    public function passwordless_room_join_form_shows_no_password_field(): void
    {
        $creator = User::factory()->create();
        $joiner = User::factory()->create();
        
        $room = Room::factory()->create([
            'creator_id' => $creator->id,
            'password' => null,
            'campaign_id' => null,
        ]);

        $response = $this->actingAs($joiner)->get(route('rooms.invite', $room->invite_code));

        $response->assertOk();
        $response->assertSee('No Password Required');
        $response->assertSee('This room is open to all participants');
        $response->assertDontSee('Room Password');
        $response->assertDontSee('Enter room password');
    }

    #[Test]
    public function password_protected_room_join_form_shows_password_field(): void
    {
        $creator = User::factory()->create();
        $joiner = User::factory()->create();
        
        $room = Room::factory()->create([
            'creator_id' => $creator->id,
            'password' => Hash::make('secret123'),
            'campaign_id' => null,
        ]);

        $response = $this->actingAs($joiner)->get(route('rooms.invite', $room->invite_code));

        $response->assertOk();
        $response->assertSee('Room Password');
        $response->assertSee('Enter room password');
        $response->assertDontSee('No Password Required');
    }

    #[Test]
    public function room_sharing_modal_shows_different_messages_for_campaign_vs_regular_rooms(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
        
        // Campaign room
        $campaignRoom = Room::factory()->create([
            'creator_id' => $user->id,
            'campaign_id' => $campaign->id,
            'password' => null,
        ]);

        $response = $this->actingAs($user)->get(route('rooms.show', $campaignRoom));
        $response->assertSee('Campaign members can join');

        // Regular room
        $regularRoom = Room::factory()->create([
            'creator_id' => $user->id,
            'campaign_id' => null,
            'password' => null,
        ]);

        $response = $this->actingAs($user)->get(route('rooms.show', $regularRoom));
        $response->assertSee('Anyone with this link can join');
    }
}
