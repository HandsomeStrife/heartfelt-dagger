<?php

declare(strict_types=1);
use Domain\Campaign\Actions\LeaveCampaignAction;
use Domain\Campaign\Models\Campaign;
use Domain\Campaign\Models\CampaignMember;
use Domain\User\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->action = new LeaveCampaignAction;
});
it('allows member to leave campaign', function () {
    $campaign = Campaign::factory()->create();
    $member = User::factory()->create();

    CampaignMember::factory()->create([
        'campaign_id' => $campaign->id,
        'user_id' => $member->id,
    ]);

    $result = $this->action->execute($campaign, $member);

    expect($result)->toBeTrue();
    \Pest\Laravel\assertDatabaseMissing('campaign_members', [
        'campaign_id' => $campaign->id,
        'user_id' => $member->id,
    ]);
});
it('removes membership from database', function () {
    $campaign = Campaign::factory()->create();
    $member = User::factory()->create();

    $membership = CampaignMember::factory()->create([
        'campaign_id' => $campaign->id,
        'user_id' => $member->id,
    ]);

    \Pest\Laravel\assertDatabaseHas('campaign_members', [
        'id' => $membership->id,
        'campaign_id' => $campaign->id,
        'user_id' => $member->id,
    ]);

    $this->action->execute($campaign, $member);

    \Pest\Laravel\assertDatabaseMissing('campaign_members', [
        'id' => $membership->id,
    ]);
});
it('prevents creator from leaving own campaign', function () {
    $creator = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    expect(fn () => $this->action->execute($campaign, $creator))
        ->toThrow(Exception::class, 'Campaign creator cannot leave their own campaign');
});
it('prevents non member from leaving', function () {
    $campaign = Campaign::factory()->create();
    $nonMember = User::factory()->create();

    expect(fn () => $this->action->execute($campaign, $nonMember))
        ->toThrow(Exception::class, 'User is not a member of this campaign');
});
it('allows multiple members to leave independently', function () {
    $campaign = Campaign::factory()->create();
    $member1 = User::factory()->create();
    $member2 = User::factory()->create();

    CampaignMember::factory()->create([
        'campaign_id' => $campaign->id,
        'user_id' => $member1->id,
    ]);
    CampaignMember::factory()->create([
        'campaign_id' => $campaign->id,
        'user_id' => $member2->id,
    ]);

    // Member 1 leaves
    $result1 = $this->action->execute($campaign, $member1);

    expect($result1)->toBeTrue();
    \Pest\Laravel\assertDatabaseMissing('campaign_members', [
        'campaign_id' => $campaign->id,
        'user_id' => $member1->id,
    ]);
    \Pest\Laravel\assertDatabaseHas('campaign_members', [
        'campaign_id' => $campaign->id,
        'user_id' => $member2->id,
    ]);

    // Member 2 leaves
    $result2 = $this->action->execute($campaign, $member2);

    expect($result2)->toBeTrue();
    \Pest\Laravel\assertDatabaseMissing('campaign_members', [
        'campaign_id' => $campaign->id,
        'user_id' => $member2->id,
    ]);
});
it('handles member in multiple campaigns', function () {
    $campaign1 = Campaign::factory()->create();
    $campaign2 = Campaign::factory()->create();
    $member = User::factory()->create();

    CampaignMember::factory()->create([
        'campaign_id' => $campaign1->id,
        'user_id' => $member->id,
    ]);
    CampaignMember::factory()->create([
        'campaign_id' => $campaign2->id,
        'user_id' => $member->id,
    ]);

    // Leave only campaign1
    $result = $this->action->execute($campaign1, $member);

    expect($result)->toBeTrue();
    \Pest\Laravel\assertDatabaseMissing('campaign_members', [
        'campaign_id' => $campaign1->id,
        'user_id' => $member->id,
    ]);
    \Pest\Laravel\assertDatabaseHas('campaign_members', [
        'campaign_id' => $campaign2->id,
        'user_id' => $member->id,
    ]);
});
it('does not affect other members when one leaves', function () {
    $campaign = Campaign::factory()->create();
    $member1 = User::factory()->create();
    $member2 = User::factory()->create();
    $member3 = User::factory()->create();

    CampaignMember::factory()->create([
        'campaign_id' => $campaign->id,
        'user_id' => $member1->id,
    ]);
    $membership2 = CampaignMember::factory()->create([
        'campaign_id' => $campaign->id,
        'user_id' => $member2->id,
    ]);
    $membership3 = CampaignMember::factory()->create([
        'campaign_id' => $campaign->id,
        'user_id' => $member3->id,
    ]);

    $this->action->execute($campaign, $member1);

    // Other memberships should remain
    \Pest\Laravel\assertDatabaseHas('campaign_members', [
        'id' => $membership2->id,
        'campaign_id' => $campaign->id,
        'user_id' => $member2->id,
    ]);
    \Pest\Laravel\assertDatabaseHas('campaign_members', [
        'id' => $membership3->id,
        'campaign_id' => $campaign->id,
        'user_id' => $member3->id,
    ]);
});
it('handles leaving with character attached', function () {
    $campaign = Campaign::factory()->create();
    $member = User::factory()->create();

    $membership = CampaignMember::factory()->create([
        'campaign_id' => $campaign->id,
        'user_id' => $member->id,
        // character_id will be set by factory
    ]);

    $result = $this->action->execute($campaign, $member);

    expect($result)->toBeTrue();
    \Pest\Laravel\assertDatabaseMissing('campaign_members', [
        'id' => $membership->id,
    ]);
});
it('handles leaving without character', function () {
    $campaign = Campaign::factory()->create();
    $member = User::factory()->create();

    $membership = CampaignMember::factory()->withoutCharacter()->create([
        'campaign_id' => $campaign->id,
        'user_id' => $member->id,
    ]);

    $result = $this->action->execute($campaign, $member);

    expect($result)->toBeTrue();
    \Pest\Laravel\assertDatabaseMissing('campaign_members', [
        'id' => $membership->id,
    ]);
});
it('prevents double leaving', function () {
    $campaign = Campaign::factory()->create();
    $member = User::factory()->create();

    CampaignMember::factory()->create([
        'campaign_id' => $campaign->id,
        'user_id' => $member->id,
    ]);

    // First leave should work
    $result1 = $this->action->execute($campaign, $member);
    expect($result1)->toBeTrue();

    // Second leave should fail
    expect(fn () => $this->action->execute($campaign, $member))
        ->toThrow(Exception::class, 'User is not a member of this campaign');
});
it('maintains campaign integrity after leave', function () {
    $creator = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);
    $member = User::factory()->create();

    CampaignMember::factory()->create([
        'campaign_id' => $campaign->id,
        'user_id' => $member->id,
    ]);

    $this->action->execute($campaign, $member);

    // Campaign should still exist and be accessible
    \Pest\Laravel\assertDatabaseHas('campaigns', [
        'id' => $campaign->id,
        'creator_id' => $creator->id,
    ]);

    $campaign->refresh();
    expect($campaign)->not->toBeNull();
    expect($campaign->creator_id)->toEqual($creator->id);
});
