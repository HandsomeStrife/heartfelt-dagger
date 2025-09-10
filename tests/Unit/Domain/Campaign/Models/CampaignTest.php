<?php

declare(strict_types=1);
use Domain\Campaign\Enums\CampaignStatus;
use Domain\Campaign\Models\Campaign;
use Domain\Campaign\Models\CampaignMember;
use Domain\User\Models\User;
use PHPUnit\Framework\Attributes\Test;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('generates unique invite code on creation', function () {
    $campaign = Campaign::factory()->create();

    expect($campaign->invite_code)->not->toBeNull();
    expect(strlen($campaign->invite_code))->toEqual(8);
    expect($campaign->invite_code)->toMatch('/^[A-Z0-9]{8}$/');
});
it('generates unique campaign code on creation', function () {
    $campaign = Campaign::factory()->create();

    expect($campaign->campaign_code)->not->toBeNull();
    expect(strlen($campaign->campaign_code))->toEqual(8);
    expect($campaign->campaign_code)->toMatch('/^[A-Z0-9]{8}$/');
});
it('generates unique codes across multiple campaigns', function () {
    $campaigns = Campaign::factory()->count(100)->create();

    $inviteCodes = $campaigns->pluck('invite_code')->toArray();
    $campaignCodes = $campaigns->pluck('campaign_code')->toArray();

    expect(count(array_unique($inviteCodes)))->toEqual(100);
    expect(count(array_unique($campaignCodes)))->toEqual(100);

    // Ensure invite codes and campaign codes are different
    expect(array_intersect($inviteCodes, $campaignCodes))->toBeEmpty();
});
it('has proper default status', function () {
    $campaign = Campaign::factory()->create();

    expect($campaign->status)->toEqual(CampaignStatus::ACTIVE);
});
it('casts status to enum', function () {
    $campaign = Campaign::factory()->create(['status' => 'archived']);

    expect($campaign->status)->toBeInstanceOf(CampaignStatus::class);
    expect($campaign->status)->toEqual(CampaignStatus::ARCHIVED);
});
it('uses campaign code as route key', function () {
    $campaign = Campaign::factory()->create();

    expect($campaign->getRouteKeyName())->toEqual('campaign_code');
});
it('belongs to creator', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    expect($campaign->creator)->toBeInstanceOf(User::class);
    expect($campaign->creator->id)->toEqual($user->id);
});
it('has many members', function () {
    $campaign = Campaign::factory()->create();
    $members = CampaignMember::factory()->count(3)->create([
        'campaign_id' => $campaign->id,
    ]);

    expect($campaign->members)->toHaveCount(3);
    expect($campaign->members->first())->toBeInstanceOf(CampaignMember::class);
});
it('checks if user is creator', function () {
    $creator = User::factory()->create();
    $otherUser = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    expect($campaign->isCreator($creator))->toBeTrue();
    expect($campaign->isCreator($otherUser))->toBeFalse();
});
it('checks if user is member', function () {
    $campaign = Campaign::factory()->create();
    $member = User::factory()->create();
    $nonMember = User::factory()->create();

    CampaignMember::factory()->create([
        'campaign_id' => $campaign->id,
        'user_id' => $member->id,
    ]);

    expect($campaign->hasMember($member))->toBeTrue();
    expect($campaign->hasMember($nonMember))->toBeFalse();
});
it('gets member count', function () {
    $campaign = Campaign::factory()->create();
    CampaignMember::factory()->count(5)->create([
        'campaign_id' => $campaign->id,
    ]);

    expect($campaign->getMemberCount())->toEqual(5);
});
it('generates invite url', function () {
    $campaign = Campaign::factory()->create();

    $expectedUrl = route('campaigns.invite', ['invite_code' => $campaign->invite_code]);
    expect($campaign->getInviteUrl())->toEqual($expectedUrl);
});
it('scopes campaigns by creator', function () {
    $creator = User::factory()->create();
    $otherUser = User::factory()->create();

    $creatorCampaigns = Campaign::factory()->count(3)->create(['creator_id' => $creator->id]);
    Campaign::factory()->count(2)->create(['creator_id' => $otherUser->id]);

    $result = Campaign::byCreator($creator)->get();

    expect($result)->toHaveCount(3);
    expect($result->every(fn ($campaign) => $campaign->creator_id === $creator->id))->toBeTrue();
});
it('scopes campaigns by invite code', function () {
    $campaign = Campaign::factory()->create();
    Campaign::factory()->create();

    // Another campaign
    $result = Campaign::byInviteCode($campaign->invite_code)->first();

    expect($result)->not->toBeNull();
    expect($result->id)->toEqual($campaign->id);
});
it('scopes campaigns by campaign code', function () {
    $campaign = Campaign::factory()->create();
    Campaign::factory()->create();

    // Another campaign
    $result = Campaign::byCampaignCode($campaign->campaign_code)->first();

    expect($result)->not->toBeNull();
    expect($result->id)->toEqual($campaign->id);
});
it('scopes active campaigns', function () {
    Campaign::factory()->count(2)->create(['status' => CampaignStatus::ACTIVE]);
    Campaign::factory()->archived()->create();
    Campaign::factory()->completed()->create();

    $result = Campaign::active()->get();

    expect($result)->toHaveCount(2);
    expect($result->every(fn ($campaign) => $campaign->status === CampaignStatus::ACTIVE))->toBeTrue();
});
it('generates unique invite codes when duplicates exist', function () {
    // Test that the method handles collisions properly by creating many campaigns
    $campaigns = Campaign::factory()->count(50)->create();
    $codes = $campaigns->pluck('invite_code');

    // All codes should be unique
    expect($codes->unique()->count())->toEqual(50);
});
it('generates unique campaign codes when duplicates exist', function () {
    // Test that the method handles collisions properly by creating many campaigns
    $campaigns = Campaign::factory()->count(50)->create();
    $codes = $campaigns->pluck('campaign_code');

    // All codes should be unique
    expect($codes->unique()->count())->toEqual(50);
});
