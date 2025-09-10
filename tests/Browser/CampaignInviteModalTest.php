<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\User\Models\User;

use function Pest\Laravel\actingAs;

uses()->group('browser');

it('displays correct invite URL in campaign invite modal', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    actingAs($user);

    visit("/campaigns/{$campaign->campaign_code}")
        ->click('Share Invite')
        ->assertVisible('#campaignInviteModal')
        ->assertAttribute('[readonly]', 'value', route('campaigns.invite', $campaign->invite_code));
});

it('allows user to join campaign via invite link', function () {
    $creator = User::factory()->create();
    $joiner = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    $inviteUrl = route('campaigns.invite', $campaign->invite_code);

    actingAs($joiner);

    visit($inviteUrl)
        ->assertSee($campaign->name)
        ->assertSee('Join Campaign');
});
