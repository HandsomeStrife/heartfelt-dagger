<?php

declare(strict_types=1);
use Domain\Campaign\Actions\JoinCampaignAction;
use Domain\Campaign\Data\CampaignMemberData;
use Domain\Campaign\Models\Campaign;
use Domain\Campaign\Models\CampaignMember;
use Domain\Character\Models\Character;
use Domain\User\Models\User;
use PHPUnit\Framework\Attributes\Test;
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->action = new JoinCampaignAction();
});
it('joins campaign with character successfully', function () {
    $campaign = Campaign::factory()->create();
    $user = User::factory()->create();
    $character = Character::factory()->create(['user_id' => $user->id]);

    $result = $this->action->execute($campaign, $user, $character);

    expect($result)->toBeInstanceOf(CampaignMemberData::class);
    expect($result->campaign_id)->toEqual($campaign->id);
    expect($result->user_id)->toEqual($user->id);
    expect($result->character_id)->toEqual($character->id);
});
it('joins campaign without character successfully', function () {
    $campaign = Campaign::factory()->create();
    $user = User::factory()->create();

    $result = $this->action->execute($campaign, $user, null);

    expect($result)->toBeInstanceOf(CampaignMemberData::class);
    expect($result->campaign_id)->toEqual($campaign->id);
    expect($result->user_id)->toEqual($user->id);
    expect($result->character_id)->toBeNull();
});
it('persists membership to database', function () {
    $campaign = Campaign::factory()->create();
    $user = User::factory()->create();
    $character = Character::factory()->create(['user_id' => $user->id]);

    $result = $this->action->execute($campaign, $user, $character);

    \Pest\Laravel\assertDatabaseHas('campaign_members', [
        'id' => $result->id,
        'campaign_id' => $campaign->id,
        'user_id' => $user->id,
        'character_id' => $character->id,
    ]);
});
it('sets joined at timestamp', function () {
    $campaign = Campaign::factory()->create();
    $user = User::factory()->create();

    $beforeJoin = now()->subSecond();
    // Give 1 second buffer
    $result = $this->action->execute($campaign, $user, null);
    $afterJoin = now()->addSecond();

    // Give 1 second buffer
    $joinedAt = \Carbon\Carbon::parse($result->joined_at);
    expect($joinedAt->between($beforeJoin, $afterJoin))->toBeTrue();
});
it('loads all relationships', function () {
    $campaign = Campaign::factory()->create(['name' => 'Test Campaign']);
    $user = User::factory()->create(['username' => 'player1']);
    $character = Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Legolas',
    ]);

    $result = $this->action->execute($campaign, $user, $character);

    expect($result->user)->not->toBeNull();
    expect($result->user->username)->toEqual('player1');

    expect($result->character)->not->toBeNull();
    expect($result->character->name)->toEqual('Legolas');

    expect($result->campaign)->not->toBeNull();
    expect($result->campaign->name)->toEqual('Test Campaign');
});
it('prevents duplicate membership', function () {
    $campaign = Campaign::factory()->create();
    $user = User::factory()->create();

    // Create existing membership
    CampaignMember::factory()->create([
        'campaign_id' => $campaign->id,
        'user_id' => $user->id,
    ]);

    expect(fn() => $this->action->execute($campaign, $user, null))
        ->toThrow(Exception::class, 'User is already a member of this campaign');
});
it('validates character ownership', function () {
    $campaign = Campaign::factory()->create();
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $character = Character::factory()->create(['user_id' => $otherUser->id]);

    expect(fn() => $this->action->execute($campaign, $user, $character))
        ->toThrow(Exception::class, 'Character does not belong to the user');
});
it('allows creator to join their own campaign', function () {
    $creator = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);
    $character = Character::factory()->create(['user_id' => $creator->id]);

    $result = $this->action->execute($campaign, $creator, $character);

    expect($result->campaign_id)->toEqual($campaign->id);
    expect($result->user_id)->toEqual($creator->id);
    expect($result->character_id)->toEqual($character->id);
});
it('allows multiple users to join same campaign', function () {
    $campaign = Campaign::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $result1 = $this->action->execute($campaign, $user1, null);
    $result2 = $this->action->execute($campaign, $user2, null);

    expect($result1->campaign_id)->toEqual($campaign->id);
    expect($result2->campaign_id)->toEqual($campaign->id);
    expect($result1->user_id)->not->toEqual($result2->user_id);
});
it('allows user to join different campaigns', function () {
    $campaign1 = Campaign::factory()->create();
    $campaign2 = Campaign::factory()->create();
    $user = User::factory()->create();
    $character1 = Character::factory()->create(['user_id' => $user->id]);
    $character2 = Character::factory()->create(['user_id' => $user->id]);

    $result1 = $this->action->execute($campaign1, $user, $character1);
    $result2 = $this->action->execute($campaign2, $user, $character2);

    expect($result1->user_id)->toEqual($user->id);
    expect($result2->user_id)->toEqual($user->id);
    expect($result1->campaign_id)->not->toEqual($result2->campaign_id);
});
it('handles null character gracefully', function () {
    $campaign = Campaign::factory()->create();
    $user = User::factory()->create();

    $result = $this->action->execute($campaign, $user, null);

    expect($result->character_id)->toBeNull();
    expect($result->character)->toBeNull();
});
it('maintains separate memberships per campaign', function () {
    $campaign1 = Campaign::factory()->create();
    $campaign2 = Campaign::factory()->create();
    $user = User::factory()->create();

    $this->action->execute($campaign1, $user, null);
    $this->action->execute($campaign2, $user, null);

    \Pest\Laravel\assertDatabaseCount('campaign_members', 2);
    expect($campaign1->members()->count())->toEqual(1);
    expect($campaign2->members()->count())->toEqual(1);
});
it('validates character exists when provided', function () {
    $campaign = Campaign::factory()->create();
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $character = Character::factory()->create(['user_id' => $otherUser->id]);

    expect(fn() => $this->action->execute($campaign, $user, $character))
        ->toThrow(Exception::class, 'Character does not belong to the user');
});
