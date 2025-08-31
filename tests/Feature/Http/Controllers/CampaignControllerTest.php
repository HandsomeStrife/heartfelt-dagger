<?php

declare(strict_types=1);
use Domain\Campaign\Models\Campaign;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};
use Domain\Campaign\Models\CampaignMember;
use Domain\Character\Models\Character;
use Domain\Room\Models\Room;
use Domain\User\Models\User;
use PHPUnit\Framework\Attributes\Test;
use function Pest\Laravel\{assertDatabaseHas, assertDatabaseMissing, assertDatabaseCount};
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('campaigns index requires authentication', function () {
    $response = get(route('campaigns.index'));

    $response->assertRedirect(route('login'));
});
test('authenticated user can view campaigns index', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->get(route('campaigns.index'));

    $response->assertOk();
    $response->assertViewIs('campaigns.index');
});
test('campaigns index displays created campaigns', function () {
    $user = User::factory()->create();
    $createdCampaigns = Campaign::factory()->count(2)->create(['creator_id' => $user->id]);
    Campaign::factory()->create();

    // Other user's campaign
    $response = actingAs($user)->get(route('campaigns.index'));

    $response->assertOk();
    $response->assertViewHas('created_campaigns');
    $viewData = $response->viewData('created_campaigns');
    expect($viewData)->toHaveCount(2);
});
test('campaigns index displays joined campaigns', function () {
    $user = User::factory()->create();
    $joinedCampaign = Campaign::factory()->create();
    CampaignMember::factory()->create([
        'campaign_id' => $joinedCampaign->id,
        'user_id' => $user->id,
    ]);

    $response = actingAs($user)->get(route('campaigns.index'));

    $response->assertOk();
    $response->assertViewHas('joined_campaigns');
    $viewData = $response->viewData('joined_campaigns');
    expect($viewData)->toHaveCount(1);
});
test('create campaign form requires authentication', function () {
    $response = get(route('campaigns.create'));

    $response->assertRedirect(route('login'));
});
test('authenticated user can view create form', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->get(route('campaigns.create'));

    $response->assertOk();
    $response->assertViewIs('campaigns.create');
});
test('user can create campaign with valid data', function () {
    $user = User::factory()->create();
    $campaignData = [
        'name' => 'The Lost Mines of Phandelver',
        'description' => 'A classic adventure for new heroes.',
    ];

    $response = actingAs($user)->post(route('campaigns.store'), $campaignData);

    assertDatabaseHas('campaigns', [
        'name' => 'The Lost Mines of Phandelver',
        'description' => 'A classic adventure for new heroes.',
        'creator_id' => $user->id,
    ]);

    $campaign = Campaign::where('name', 'The Lost Mines of Phandelver')->first();
    $response->assertRedirect(route('campaigns.show', $campaign->campaign_code));
    $response->assertSessionHas('success', 'Campaign created successfully!');
});
test('campaign creation requires authentication', function () {
    $campaignData = [
        'name' => 'Unauthorized Campaign',
        'description' => 'This should not be created.',
    ];

    $response = post(route('campaigns.store'), $campaignData);

    $response->assertRedirect(route('login'));
    assertDatabaseMissing('campaigns', ['name' => 'Unauthorized Campaign']);
});
test('campaign creation validates required fields', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->post(route('campaigns.store'), []);

    $response->assertSessionHasErrors(['name', 'description']);
    assertDatabaseCount('campaigns', 0);
});
test('campaign creation validates field lengths', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->post(route('campaigns.store'), [
        'name' => str_repeat('A', 101), // Too long
        'description' => str_repeat('B', 1001), // Too long
    ]);

    $response->assertSessionHasErrors(['name', 'description']);
});
test('user can view their own campaign', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    $response = actingAs($user)->get(route('campaigns.show', $campaign->campaign_code));

    $response->assertOk();
    $response->assertViewIs('campaigns.show');
    $response->assertViewHas('campaign');
    $response->assertViewHas('user_is_creator', true);
});
test('user can view other users campaign', function () {
    $creator = User::factory()->create();
    $viewer = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    $response = actingAs($viewer)->get(route('campaigns.show', $campaign->campaign_code));

    $response->assertOk();
    $response->assertViewHas('user_is_creator', false);
    $response->assertViewHas('user_is_member', false);
});
test('campaign show requires authentication', function () {
    $campaign = Campaign::factory()->create();

    $response = get(route('campaigns.show', $campaign->campaign_code));

    $response->assertRedirect(route('login'));
});
test('campaign show returns 404 for invalid code', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->get(route('campaigns.show', 'INVALID1'));

    $response->assertNotFound();
});
test('user can view join form via invite code', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create();

    $response = actingAs($user)->get(route('campaigns.invite', $campaign->invite_code));

    $response->assertOk();
    $response->assertViewIs('campaigns.join');
    $response->assertViewHas('campaign');
    $response->assertViewHas('characters');
});
test('join form requires authentication', function () {
    $campaign = Campaign::factory()->create();

    $response = get(route('campaigns.invite', $campaign->invite_code));

    $response->assertRedirect(route('login'));
});
test('existing member redirected from join form', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create();
    CampaignMember::factory()->create([
        'campaign_id' => $campaign->id,
        'user_id' => $user->id,
    ]);

    $response = actingAs($user)->get(route('campaigns.invite', $campaign->invite_code));

    $response->assertRedirect(route('campaigns.show', $campaign->campaign_code));
    $response->assertSessionHas('info', 'You are already a member of this campaign.');
});
test('user can join campaign with character', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create();
    $character = Character::factory()->create(['user_id' => $user->id]);

    $response = actingAs($user)->post(route('campaigns.join_campaign', $campaign), [
        'character_id' => $character->id,
    ]);

    assertDatabaseHas('campaign_members', [
        'campaign_id' => $campaign->id,
        'user_id' => $user->id,
        'character_id' => $character->id,
    ]);

    $response->assertRedirect(route('campaigns.show', $campaign->campaign_code));
    $response->assertSessionHas('success', 'Successfully joined the campaign!');
});
test('user can join campaign without character', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create();

    $response = actingAs($user)->post(route('campaigns.join_campaign', $campaign), [
        'character_id' => null,
    ]);

    assertDatabaseHas('campaign_members', [
        'campaign_id' => $campaign->id,
        'user_id' => $user->id,
        'character_id' => null,
    ]);

    $response->assertRedirect(route('campaigns.show', $campaign->campaign_code));
});
test('join campaign requires authentication', function () {
    $campaign = Campaign::factory()->create();

    $response = post(route('campaigns.join_campaign', $campaign), []);

    $response->assertRedirect(route('login'));
});
test('join campaign validates character ownership', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $campaign = Campaign::factory()->create();
    $otherCharacter = Character::factory()->create(['user_id' => $otherUser->id]);

    $response = actingAs($user)->post(route('campaigns.join_campaign', $campaign), [
        'character_id' => $otherCharacter->id,
    ]);

    $response->assertNotFound();
    // Character not found for this user
    assertDatabaseMissing('campaign_members', [
        'campaign_id' => $campaign->id,
        'user_id' => $user->id,
    ]);
});
test('user cannot join campaign twice', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create();

    // Join once
    actingAs($user)->post(route('campaigns.join_campaign', $campaign), []);

    // Try to join again
    $response = actingAs($user)->post(route('campaigns.join_campaign', $campaign), []);

    $response->assertSessionHasErrors(['error']);
    assertDatabaseCount('campaign_members', 1);
});
test('member can leave campaign', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create();
    CampaignMember::factory()->create([
        'campaign_id' => $campaign->id,
        'user_id' => $user->id,
    ]);

    $response = actingAs($user)->delete(route('campaigns.leave', $campaign->campaign_code));

    assertDatabaseMissing('campaign_members', [
        'campaign_id' => $campaign->id,
        'user_id' => $user->id,
    ]);

    $response->assertRedirect(route('campaigns.index'));
    $response->assertSessionHas('success', 'Successfully left the campaign.');
});
test('leave campaign requires authentication', function () {
    $campaign = Campaign::factory()->create();

    $response = delete(route('campaigns.leave', $campaign->campaign_code));

    $response->assertRedirect(route('login'));
});
test('creator cannot leave own campaign', function () {
    $creator = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    $response = actingAs($creator)->delete(route('campaigns.leave', $campaign->campaign_code));

    $response->assertSessionHasErrors(['error']);
});
test('non member cannot leave campaign', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create();

    $response = actingAs($user)->delete(route('campaigns.leave', $campaign->campaign_code));

    $response->assertSessionHasErrors(['error']);
});
test('campaign show displays members', function () {
    $creator = User::factory()->create(['username' => 'gamemaster']);
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);
    $member = User::factory()->create(['username' => 'player1']);
    $character = Character::factory()->create([
        'user_id' => $member->id,
        'name' => 'Aragorn',
    ]);

    CampaignMember::factory()->create([
        'campaign_id' => $campaign->id,
        'user_id' => $member->id,
        'character_id' => $character->id,
    ]);

    $response = actingAs($creator)->get(route('campaigns.show', $campaign->campaign_code));

    $response->assertOk();
    $response->assertViewHas('members');
    $viewData = $response->viewData('members');
    expect($viewData)->toHaveCount(1);
});
test('join form displays user characters', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create();
    $characters = Character::factory()->count(3)->create(['user_id' => $user->id]);

    $response = actingAs($user)->get(route('campaigns.invite', $campaign->invite_code));

    $response->assertOk();
    $response->assertViewHas('characters');
    $viewData = $response->viewData('characters');
    expect($viewData)->toHaveCount(3);
});
test('routes use correct parameter binding', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create();

    // Show route uses campaign_code
    $response = actingAs($user)->get("/campaigns/{$campaign->campaign_code}");
    $response->assertOk();

    // Join form uses invite_code  
    $response = actingAs($user)->get("/join/{$campaign->invite_code}");
    $response->assertOk();
});
test('user can join campaign by invite code', function () {
    $creator = User::factory()->create();
    $joiner = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    $response = actingAs($joiner)->post(route('campaigns.join'), [
        'invite_code' => $campaign->invite_code,
    ]);

    $response->assertRedirect(route('campaigns.show', $campaign->campaign_code));
    $response->assertSessionHas('success', 'Successfully joined the campaign!');

    expect($campaign->fresh()->hasMember($joiner))->toBeTrue();
});
test('joining with invalid invite code shows error', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->post(route('campaigns.join'), [
        'invite_code' => 'INVALID1',
    ]);

    $response->assertRedirect(route('campaigns.index'));
    $response->assertSessionHasErrors(['invite_code']);
});
test('user cannot join campaign twice via invite code', function () {
    $creator = User::factory()->create();
    $joiner = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    // Join first time
    actingAs($joiner)->post(route('campaigns.join'), [
        'invite_code' => $campaign->invite_code,
    ]);

    // Try to join again
    $response = actingAs($joiner)->post(route('campaigns.join'), [
        'invite_code' => $campaign->invite_code,
    ]);

    $response->assertRedirect(route('campaigns.show', $campaign->campaign_code));
    $response->assertSessionHas('info', 'You are already a member of this campaign.');
});
test('campaign show displays campaign rooms', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    // Create rooms - one for this campaign and one for another campaign
    $campaignRoom1 = Room::factory()->create([
        'campaign_id' => $campaign->id,
        'creator_id' => $user->id,
        'name' => 'Campaign Room 1',
    ]);

    $campaignRoom2 = Room::factory()->create([
        'campaign_id' => $campaign->id,
        'creator_id' => $user->id,
        'name' => 'Campaign Room 2',
    ]);

    // Create a room for a different campaign to ensure it's not shown
    $otherCampaign = Campaign::factory()->create();
    $otherRoom = Room::factory()->create([
        'campaign_id' => $otherCampaign->id,
        'name' => 'Other Campaign Room',
    ]);

    // Create a regular room (no campaign) to ensure it's not shown
    $regularRoom = Room::factory()->create([
        'campaign_id' => null,
        'name' => 'Regular Room',
    ]);

    $response = actingAs($user)->get(route('campaigns.show', $campaign->campaign_code));

    $response->assertOk();
    $response->assertSee('Campaign Room 1');
    $response->assertSee('Campaign Room 2');
    $response->assertDontSee('Other Campaign Room');
    $response->assertDontSee('Regular Room');
    $response->assertSee('Campaign Rooms');
    $response->assertSee('No Password');
    $response->assertSee('Campaign');
    // The badge text
    $response->assertViewHas('campaign_rooms');

    $viewData = $response->viewData('campaign_rooms');
    expect($viewData)->toHaveCount(2);
});
