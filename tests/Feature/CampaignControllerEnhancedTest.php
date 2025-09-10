<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\CampaignFrame\Models\CampaignFrame;
use Domain\User\Models\User;

use function Pest\Laravel\actingAs;

it('can create campaign without description', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->post(route('campaigns.store'), [
            'name' => 'Test Campaign',
            // No description provided
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Campaign created successfully!');

    $campaign = Campaign::where('name', 'Test Campaign')->first();
    expect($campaign)->not->toBeNull();
    expect($campaign->description)->toBeNull();
    expect($campaign->creator_id)->toBe($user->id);
});

it('can_create campaign with optional description', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->post(route('campaigns.store'), [
            'name' => 'Test Campaign',
            'description' => 'This is a test campaign description.',
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Campaign created successfully!');

    $campaign = Campaign::where('name', 'Test Campaign')->first();
    expect($campaign)->not->toBeNull();
    expect($campaign->description)->toBe('This is a test campaign description.');
});

it('can_create campaign with campaign frame', function () {
    $user = User::factory()->create();
    $campaignFrame = CampaignFrame::factory()->create();

    actingAs($user)
        ->post(route('campaigns.store'), [
            'name' => 'Test Campaign',
            'description' => 'Test description',
            'campaign_frame_id' => $campaignFrame->id,
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Campaign created successfully!');

    $campaign = Campaign::where('name', 'Test Campaign')->first();
    expect($campaign)->not->toBeNull();
    expect($campaign->campaign_frame_id)->toBe($campaignFrame->id);
});

it('validates campaign frame exists', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->post(route('campaigns.store'), [
            'name' => 'Test Campaign',
            'campaign_frame_id' => 999999, // Non-existent frame
        ])
        ->assertSessionHasErrors(['campaign_frame_id']);
});

it('requires campaign name', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->post(route('campaigns.store'), [
            // No name provided
            'description' => 'Test description',
        ])
        ->assertSessionHasErrors(['name']);
});

it('validates campaign name length', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->post(route('campaigns.store'), [
            'name' => str_repeat('a', 101), // Exceeds 100 character limit
            'description' => 'Test description',
        ])
        ->assertSessionHasErrors(['name']);
});

it('validates description length', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->post(route('campaigns.store'), [
            'name' => 'Test Campaign',
            'description' => str_repeat('a', 1001), // Exceeds 1000 character limit
        ])
        ->assertSessionHasErrors(['description']);
});

it('accepts empty description', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->post(route('campaigns.store'), [
            'name' => 'Test Campaign',
            'description' => '', // Empty string
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Campaign created successfully!');

    $campaign = Campaign::where('name', 'Test Campaign')->first();
    expect($campaign->description)->toBe('');
});

it('shows_campaign pages link for members', function () {
    $creator = User::factory()->create();
    $campaign = Campaign::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Test Campaign',
    ]);

    actingAs($creator)
        ->get(route('campaigns.show', $campaign->campaign_code))
        ->assertSuccessful()
        ->assertSee('Campaign Pages')
        ->assertSee('Manage Pages')
        ->assertSee(route('campaigns.pages', $campaign->campaign_code));
});

it('can access campaign pages route', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    actingAs($user)
        ->get(route('campaigns.pages', $campaign->campaign_code))
        ->assertSuccessful()
        ->assertSee('Campaign Pages & Lore')
        ->assertSee($campaign->name);
});

it('prevents_unauthorized access to campaign pages', function () {
    $creator = User::factory()->create();
    $otherUser = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    actingAs($otherUser)
        ->get(route('campaigns.pages', $campaign->campaign_code))
        ->assertForbidden();
});

it('handles_campaign_creation with all optional fields', function () {
    $user = User::factory()->create();
    $campaignFrame = CampaignFrame::factory()->create();

    actingAs($user)
        ->post(route('campaigns.store'), [
            'name' => 'Complete Campaign',
            'description' => 'Full description here',
            'campaign_frame_id' => $campaignFrame->id,
        ])
        ->assertRedirect();

    $campaign = Campaign::where('name', 'Complete Campaign')->first();
    expect($campaign)->not->toBeNull();
    expect($campaign->name)->toBe('Complete Campaign');
    expect($campaign->description)->toBe('Full description here');
    expect($campaign->campaign_frame_id)->toBe($campaignFrame->id);
    expect($campaign->creator_id)->toBe($user->id);
});

it('creates campaign with proper status', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->post(route('campaigns.store'), [
            'name' => 'Status Test Campaign',
        ])
        ->assertRedirect();

    $campaign = Campaign::where('name', 'Status Test Campaign')->first();
    expect($campaign->status->value)->toBe('active'); // Default status
});

it('generates unique campaign codes', function () {
    $user = User::factory()->create();

    // Create first campaign
    actingAs($user)
        ->post(route('campaigns.store'), [
            'name' => 'First Campaign',
        ]);

    // Create second campaign
    actingAs($user)
        ->post(route('campaigns.store'), [
            'name' => 'Second Campaign',
        ]);

    $firstCampaign = Campaign::where('name', 'First Campaign')->first();
    $secondCampaign = Campaign::where('name', 'Second Campaign')->first();

    expect($firstCampaign->campaign_code)->not->toBe($secondCampaign->campaign_code);
});

it('generates unique invite codes', function () {
    $user = User::factory()->create();

    // Create first campaign
    actingAs($user)
        ->post(route('campaigns.store'), [
            'name' => 'First Campaign',
        ]);

    // Create second campaign
    actingAs($user)
        ->post(route('campaigns.store'), [
            'name' => 'Second Campaign',
        ]);

    $firstCampaign = Campaign::where('name', 'First Campaign')->first();
    $secondCampaign = Campaign::where('name', 'Second Campaign')->first();

    expect($firstCampaign->invite_code)->not->toBe($secondCampaign->invite_code);
});

it('redirects_to_campaign show page after creation', function () {
    $user = User::factory()->create();

    $response = actingAs($user)
        ->post(route('campaigns.store'), [
            'name' => 'Redirect Test Campaign',
        ]);

    $campaign = Campaign::where('name', 'Redirect Test Campaign')->first();

    $response->assertRedirect(route('campaigns.show', $campaign->campaign_code));
});
