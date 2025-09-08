<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\Campaign\Models\CampaignMember;
use Domain\Character\Models\Character;
use Domain\Room\Models\Room;
use Domain\User\Models\User;
use function Pest\Laravel\{actingAs, get, post};

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('campaign member automatically joins room with their campaign character', function () {
    // Create users
    $gm = User::factory()->create();
    $player = User::factory()->create();
    
    // Create campaign
    $campaign = Campaign::factory()->create([
        'creator_id' => $gm->id,
    ]);
    
    // Create characters
    $gmCharacter = Character::factory()->create([
        'user_id' => $gm->id,
        'name' => 'GM Character',
        'class' => 'wizard',
    ]);
    
    $playerCharacter = Character::factory()->create([
        'user_id' => $player->id,
        'name' => 'Player Character',
        'class' => 'warrior',
    ]);
    
    // Create campaign members
    CampaignMember::factory()->create([
        'campaign_id' => $campaign->id,
        'user_id' => $gm->id,
        'character_id' => $gmCharacter->id,
    ]);
    
    CampaignMember::factory()->create([
        'campaign_id' => $campaign->id,
        'user_id' => $player->id,
        'character_id' => $playerCharacter->id,
    ]);
    
    // Create campaign room
    $room = Room::factory()->create([
        'creator_id' => $gm->id,
        'campaign_id' => $campaign->id,
        'name' => 'Test Campaign Room',
    ]);
    
    // Player (non-GM) accessing room should be auto-joined and redirected to session
    $response = actingAs($player)->get("/rooms/join/{$room->invite_code}");
    
    $response->assertRedirect(route('rooms.session', $room));
    $response->assertSessionHas('success', 'Joined room with your campaign character!');
    
    // Verify player is now a participant with their character
    $participant = $room->activeParticipants()->where('user_id', $player->id)->first();
    expect($participant)->not->toBeNull();
    expect($participant->character_id)->toBe($playerCharacter->id);
});

test('campaign GM sees intermediate screen instead of auto-joining', function () {
    // Create users
    $gm = User::factory()->create();
    $player = User::factory()->create();
    
    // Create campaign
    $campaign = Campaign::factory()->create([
        'creator_id' => $gm->id,
    ]);
    
    // Create characters
    $gmCharacter = Character::factory()->create([
        'user_id' => $gm->id,
        'name' => 'GM Character',
        'class' => 'wizard',
    ]);
    
    // Create campaign member (GM)
    CampaignMember::factory()->create([
        'campaign_id' => $campaign->id,
        'user_id' => $gm->id,
        'character_id' => $gmCharacter->id,
    ]);
    
    // Create campaign room
    $room = Room::factory()->create([
        'creator_id' => $gm->id,
        'campaign_id' => $campaign->id,
        'name' => 'Test Campaign Room',
    ]);
    
    // GM accessing room should see join form (intermediate screen)
    $response = actingAs($gm)->get("/rooms/join/{$room->invite_code}");
    
    $response->assertOk();
    $response->assertViewIs('rooms.join');
    $response->assertViewHas('room', $room);
    $response->assertViewHas('characters');
});

test('non-campaign member follows normal flow', function () {
    // Create users
    $gm = User::factory()->create();
    $outsider = User::factory()->create();
    
    // Create regular room (not tied to campaign)
    $room = Room::factory()->create([
        'creator_id' => $gm->id,
        'campaign_id' => null, // Not a campaign room
        'name' => 'Regular Room',
    ]);
    
    // Outsider accessing regular room should see join form
    $response = actingAs($outsider)->get("/rooms/join/{$room->invite_code}");
    
    $response->assertOk();
    $response->assertViewIs('rooms.join');
    $response->assertViewHas('room', $room);
    $response->assertViewHas('characters');
});

test('campaign member without character falls back to join form', function () {
    // Create users
    $gm = User::factory()->create();
    $player = User::factory()->create();
    
    // Create campaign
    $campaign = Campaign::factory()->create([
        'creator_id' => $gm->id,
    ]);
    
    // Create campaign member without character
    CampaignMember::factory()->create([
        'campaign_id' => $campaign->id,
        'user_id' => $player->id,
        'character_id' => null, // No character attached
    ]);
    
    // Create campaign room
    $room = Room::factory()->create([
        'creator_id' => $gm->id,
        'campaign_id' => $campaign->id,
        'name' => 'Test Campaign Room',
    ]);
    
    // Player without character should see join form
    $response = actingAs($player)->get("/rooms/join/{$room->invite_code}");
    
    $response->assertOk();
    $response->assertViewIs('rooms.join');
    $response->assertViewHas('room', $room);
    $response->assertViewHas('characters');
});

test('campaign page links directly auto-join members to room session', function () {
    // Create users
    $gm = User::factory()->create();
    $player = User::factory()->create();
    
    // Create campaign
    $campaign = Campaign::factory()->create([
        'creator_id' => $gm->id,
    ]);
    
    // Create characters
    $playerCharacter = Character::factory()->create([
        'user_id' => $player->id,
        'name' => 'Player Character',
        'class' => 'warrior',
    ]);
    
    // Create campaign member
    CampaignMember::factory()->create([
        'campaign_id' => $campaign->id,
        'user_id' => $player->id,
        'character_id' => $playerCharacter->id,
    ]);
    
    // Create campaign room
    $room = Room::factory()->create([
        'creator_id' => $gm->id,
        'campaign_id' => $campaign->id,
        'name' => 'Test Campaign Room',
    ]);
    
    // Player clicking "Join" from campaign page should be auto-joined and redirected to session
    $response = actingAs($player)->get("/rooms/join/{$room->invite_code}");
    
    $response->assertRedirect(route('rooms.session', $room));
    $response->assertSessionHas('success', 'Joined room with your campaign character!');
    
    // Verify player is now a participant
    $participant = $room->activeParticipants()->where('user_id', $player->id)->first();
    expect($participant)->not->toBeNull();
    expect($participant->character_id)->toBe($playerCharacter->id);
});
