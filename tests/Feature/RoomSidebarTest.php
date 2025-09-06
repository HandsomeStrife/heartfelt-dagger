<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\Campaign\Models\CampaignMember;
use Domain\CampaignPage\Models\CampaignPage;
use Domain\Character\Models\Character;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->gm = User::factory()->create();
});

test('campaign room shows sidebar layout for authenticated users', function () {
    $campaign = Campaign::factory()->create(['creator_id' => $this->gm->id]);
    $room = Room::factory()->create([
        'creator_id' => $this->gm->id,
        'campaign_id' => $campaign->id,
    ]);

    // Join as GM
    $this->actingAs($this->gm)
        ->get(route('rooms.session', $room))
        ->assertOk()
        ->assertSee('GM Dashboard')
        ->assertSee('sidebarVisible: true');
});

test('normal room shows traditional layout without sidebar', function () {
    $room = Room::factory()->create(['creator_id' => $this->gm->id]);

    $this->actingAs($this->gm)
        ->get(route('rooms.session', $room))
        ->assertOk()
        ->assertDontSee('GM Dashboard')
        ->assertDontSee('sidebarVisible');
});

test('gm sees gm sidebar with campaign pages', function () {
    $campaign = Campaign::factory()->create(['creator_id' => $this->gm->id]);
    $room = Room::factory()->create([
        'creator_id' => $this->gm->id,
        'campaign_id' => $campaign->id,
    ]);
    
    // Create some campaign pages
    $page1 = CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'creator_id' => $this->gm->id,
        'title' => 'Test Page 1',
    ]);
    
    $page2 = CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'creator_id' => $this->gm->id,
        'title' => 'Test Page 2',
    ]);

    $this->actingAs($this->gm)
        ->get(route('rooms.session', $room))
        ->assertOk()
        ->assertSee('GM Dashboard')
        ->assertSee('Test Page 1')
        ->assertSee('Test Page 2')
        ->assertSee('Campaign Pages')
        ->assertSee('Session Notes');
});

test('player sees player sidebar with character details', function () {
    $campaign = Campaign::factory()->create(['creator_id' => $this->gm->id]);
    
    // Add user as campaign member
    CampaignMember::factory()->create([
        'campaign_id' => $campaign->id,
        'user_id' => $this->user->id,
    ]);
    
    $room = Room::factory()->create([
        'creator_id' => $this->gm->id,
        'campaign_id' => $campaign->id,
    ]);
    
    // Create a character for the player
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Test Character',
        'class' => 'warrior',
        'ancestry' => 'human',
    ]);
    
    // Join the room as a player with character
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $this->user->id,
        'character_id' => $character->id,
        'left_at' => null, // Make sure participant is active
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('rooms.session', $room));
        
    if ($response->status() !== 200) {
        dump('Response status: ' . $response->status());
        dump('Redirect location: ' . $response->headers->get('Location'));
    }
        
    $response->assertOk()
        ->assertSee('Test Character')
        ->assertSee('warrior')
        ->assertSee('Health')
        ->assertSee('Equipment')
        ->assertSee('Abilities')
        ->assertSee('Notes');
});

test('player without character sees empty sidebar', function () {
    $campaign = Campaign::factory()->create(['creator_id' => $this->gm->id]);
    
    // Add user as campaign member
    CampaignMember::factory()->create([
        'campaign_id' => $campaign->id,
        'user_id' => $this->user->id,
    ]);
    
    $room = Room::factory()->create([
        'creator_id' => $this->gm->id,
        'campaign_id' => $campaign->id,
    ]);
    
    // Join the room as a player without character
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $this->user->id,
        'character_id' => null,
        'character_name' => 'Temp Character',
        'character_class' => 'warrior',
    ]);

    $this->actingAs($this->user)
        ->get(route('rooms.session', $room))
        ->assertOk()
        ->assertSee('No Character Linked')
        ->assertSee('Temp Character')
        ->assertSee('Temporary Character Info');
});

test('gm sidebar shows player summaries', function () {
    $campaign = Campaign::factory()->create(['creator_id' => $this->gm->id]);
    $room = Room::factory()->create([
        'creator_id' => $this->gm->id,
        'campaign_id' => $campaign->id,
    ]);
    
    // Create characters for players
    $character1 = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Player One',
        'class' => 'warrior',
        'ancestry' => 'human',
    ]);
    
    $player2 = User::factory()->create();
    $character2 = Character::factory()->create([
        'user_id' => $player2->id,
        'name' => 'Player Two',
        'class' => 'wizard',
        'ancestry' => 'elf',
    ]);
    
    // Join the room as players
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $this->user->id,
        'character_id' => $character1->id,
    ]);
    
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $player2->id,
        'character_id' => $character2->id,
    ]);

    $this->actingAs($this->gm)
        ->get(route('rooms.session', $room))
        ->assertOk()
        ->assertSee('Player Characters')
        ->assertSee('Player One')
        ->assertSee('Player Two')
        ->assertSee('warrior')
        ->assertSee('wizard');
});

test('sidebar toggle button is present in campaign rooms', function () {
    $campaign = Campaign::factory()->create(['creator_id' => $this->gm->id]);
    $room = Room::factory()->create([
        'creator_id' => $this->gm->id,
        'campaign_id' => $campaign->id,
    ]);

    $this->actingAs($this->gm)
        ->get(route('rooms.session', $room))
        ->assertOk()
        ->assertSee('sidebarVisible = !sidebarVisible');
});

test('room session loads campaign pages for gm', function () {
    $campaign = Campaign::factory()->create(['creator_id' => $this->gm->id]);
    $room = Room::factory()->create([
        'creator_id' => $this->gm->id,
        'campaign_id' => $campaign->id,
    ]);
    
    // Create campaign pages
    CampaignPage::factory()->count(3)->create([
        'campaign_id' => $campaign->id,
        'creator_id' => $this->gm->id,
    ]);

    $response = $this->actingAs($this->gm)
        ->get(route('rooms.session', $room));
        
    $response->assertOk();
    
    // Check that campaign pages were loaded in the view data
    $response->assertViewHas('campaign_pages');
    $campaignPages = $response->viewData('campaign_pages');
    expect($campaignPages)->toHaveCount(3);
});

test('room session does not load campaign pages for players', function () {
    $campaign = Campaign::factory()->create(['creator_id' => $this->gm->id]);
    
    // Add user as campaign member
    CampaignMember::factory()->create([
        'campaign_id' => $campaign->id,
        'user_id' => $this->user->id,
    ]);
    
    $room = Room::factory()->create([
        'creator_id' => $this->gm->id,
        'campaign_id' => $campaign->id,
    ]);
    
    // Join as player
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $this->user->id,
    ]);
    
    // Create campaign pages
    CampaignPage::factory()->count(3)->create([
        'campaign_id' => $campaign->id,
        'creator_id' => $this->gm->id,
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('rooms.session', $room));
        
    $response->assertOk();
    
    // Check that campaign pages collection is empty for players
    $response->assertViewHas('campaign_pages');
    $campaignPages = $response->viewData('campaign_pages');
    expect($campaignPages)->toHaveCount(0);
});
