<?php

declare(strict_types=1);
use Domain\Campaign\Data\CampaignData;
use Domain\Campaign\Data\CampaignMemberData;
use Domain\Campaign\Enums\CampaignStatus;
use Domain\Campaign\Models\Campaign;
use Domain\Campaign\Models\CampaignMember;
use Domain\Campaign\Repositories\CampaignRepository;
use Domain\Character\Models\Character;
use Domain\User\Models\User;
use PHPUnit\Framework\Attributes\Test;
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->repository = new CampaignRepository();
});
it('finds campaign by id with member count', function () {
    $creator = User::factory()->create(['username' => 'gamemaster']);
    $campaign = Campaign::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Test Campaign',
    ]);

    CampaignMember::factory()->count(3)->create(['campaign_id' => $campaign->id]);

    $result = $this->repository->findById($campaign->id);

    expect($result)->toBeInstanceOf(CampaignData::class);
    expect($result->id)->toEqual($campaign->id);
    expect($result->name)->toEqual('Test Campaign');
    expect($result->member_count)->toEqual(3);
    expect($result->creator)->not->toBeNull();
    expect($result->creator->username)->toEqual('gamemaster');
});
it('returns null for non existent campaign', function () {
    $result = $this->repository->findById(999);

    expect($result)->toBeNull();
});
it('finds campaign by invite code', function () {
    $campaign = Campaign::factory()->create(['name' => 'Invite Test']);
    CampaignMember::factory()->count(2)->create(['campaign_id' => $campaign->id]);

    $result = $this->repository->findByInviteCode($campaign->invite_code);

    expect($result)->toBeInstanceOf(CampaignData::class);
    expect($result->id)->toEqual($campaign->id);
    expect($result->name)->toEqual('Invite Test');
    expect($result->member_count)->toEqual(2);
});
it('returns null for invalid invite code', function () {
    $result = $this->repository->findByInviteCode('INVALID1');

    expect($result)->toBeNull();
});
it('gets campaigns created by user', function () {
    $creator = User::factory()->create();
    $otherUser = User::factory()->create();

    $createdCampaigns = Campaign::factory()->count(3)->create(['creator_id' => $creator->id]);
    Campaign::factory()->count(2)->create(['creator_id' => $otherUser->id]);

    $result = $this->repository->getCreatedByUser($creator);

    expect($result)->toHaveCount(3);
    expect($result->every(fn($campaign) => $campaign->creator_id === $creator->id))->toBeTrue();
    expect($result->every(fn($campaign) => $campaign instanceof CampaignData))->toBeTrue();
});
it('orders created campaigns by newest first', function () {
    $creator = User::factory()->create();

    $oldest = Campaign::factory()->create([
        'creator_id' => $creator->id,
        'created_at' => now()->subDays(3),
    ]);
    $newest = Campaign::factory()->create([
        'creator_id' => $creator->id,
        'created_at' => now()->subDay(),
    ]);
    $middle = Campaign::factory()->create([
        'creator_id' => $creator->id,
        'created_at' => now()->subDays(2),
    ]);

    $result = $this->repository->getCreatedByUser($creator);

    expect($result->first()->id)->toEqual($newest->id);
    expect($result->last()->id)->toEqual($oldest->id);
});
it('gets campaigns joined by user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $joinedCampaigns = Campaign::factory()->count(2)->create();
    $notJoinedCampaign = Campaign::factory()->create();

    foreach ($joinedCampaigns as $campaign) {
        CampaignMember::factory()->create([
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
        ]);
    }

    CampaignMember::factory()->create([
        'campaign_id' => $notJoinedCampaign->id,
        'user_id' => $otherUser->id,
    ]);

    $result = $this->repository->getJoinedByUser($user);

    expect($result)->toHaveCount(2);
    expect($result->every(fn($campaign) => $campaign instanceof CampaignData))->toBeTrue();
    expect($result->contains('id', $joinedCampaigns[0]->id))->toBeTrue();
    expect($result->contains('id', $joinedCampaigns[1]->id))->toBeTrue();
    expect($result->contains('id', $notJoinedCampaign->id))->toBeFalse();
});
it('gets campaign members with relationships', function () {
    $campaign = Campaign::factory()->create();
    $user1 = User::factory()->create(['username' => 'player1']);
    $user2 = User::factory()->create(['username' => 'player2']);
    $character1 = Character::factory()->create(['name' => 'Aragorn', 'user_id' => $user1->id]);

    $member1 = CampaignMember::factory()->create([
        'campaign_id' => $campaign->id,
        'user_id' => $user1->id,
        'character_id' => $character1->id,
        'joined_at' => now()->subDays(2),
    ]);
    $member2 = CampaignMember::factory()->withoutCharacter()->create([
        'campaign_id' => $campaign->id,
        'user_id' => $user2->id,
        'joined_at' => now()->subDay(),
    ]);

    $result = $this->repository->getCampaignMembers($campaign);

    expect($result)->toHaveCount(2);
    expect($result->every(fn($member) => $member instanceof CampaignMemberData))->toBeTrue();

    // Check first member (should be ordered by joined_at)
    $firstMember = $result->first();
    expect($firstMember->id)->toEqual($member1->id);
    expect($firstMember->user)->not->toBeNull();
    expect($firstMember->user->username)->toEqual('player1');
    expect($firstMember->character)->not->toBeNull();
    expect($firstMember->character->name)->toEqual('Aragorn');

    // Check second member
    $secondMember = $result->last();
    expect($secondMember->id)->toEqual($member2->id);
    expect($secondMember->user)->not->toBeNull();
    expect($secondMember->user->username)->toEqual('player2');
    expect($secondMember->character)->toBeNull();
});
it('orders members by joined at ascending', function () {
    $campaign = Campaign::factory()->create();

    $latestMember = CampaignMember::factory()->create([
        'campaign_id' => $campaign->id,
        'joined_at' => now(),
    ]);
    $earliestMember = CampaignMember::factory()->create([
        'campaign_id' => $campaign->id,
        'joined_at' => now()->subDays(2),
    ]);

    $result = $this->repository->getCampaignMembers($campaign);

    expect($result->first()->id)->toEqual($earliestMember->id);
    expect($result->last()->id)->toEqual($latestMember->id);
});
it('gets active campaigns', function () {
    Campaign::factory()->count(2)->create(['status' => CampaignStatus::ACTIVE]);
    Campaign::factory()->archived()->create();
    Campaign::factory()->completed()->create();
    Campaign::factory()->paused()->create();

    $result = $this->repository->getActiveCampaigns();

    expect($result)->toHaveCount(2);
    expect($result->every(fn($campaign) => $campaign->status === CampaignStatus::ACTIVE))->toBeTrue();
});
it('gets all user campaigns combined', function () {
    $user = User::factory()->create();

    // Campaigns created by user
    $createdCampaigns = Campaign::factory()->count(2)->create([
        'creator_id' => $user->id,
        'created_at' => now()->subDays(3),
    ]);

    // Campaigns joined by user
    $joinedCampaign = Campaign::factory()->create(['created_at' => now()->subDay()]);
    CampaignMember::factory()->create([
        'campaign_id' => $joinedCampaign->id,
        'user_id' => $user->id,
    ]);

    // Campaign not related to user
    Campaign::factory()->create();

    $result = $this->repository->getAllUserCampaigns($user);

    expect($result)->toHaveCount(3);

    // Should be ordered by created_at desc (newest first)
    expect($result->first()->id)->toEqual($joinedCampaign->id);

    // Should include both created and joined campaigns
    $campaignIds = $result->pluck('id')->toArray();
    expect($campaignIds)->toContain($createdCampaigns[0]->id);
    expect($campaignIds)->toContain($createdCampaigns[1]->id);
    expect($campaignIds)->toContain($joinedCampaign->id);
});
it('includes member count in all queries', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    CampaignMember::factory()->count(5)->create(['campaign_id' => $campaign->id]);

    $resultById = $this->repository->findById($campaign->id);
    $resultByInvite = $this->repository->findByInviteCode($campaign->invite_code);
    $resultCreated = $this->repository->getCreatedByUser($user);
    $resultActive = $this->repository->getActiveCampaigns();

    expect($resultById->member_count)->toEqual(5);
    expect($resultByInvite->member_count)->toEqual(5);
    expect($resultCreated->first()->member_count)->toEqual(5);
    expect($resultActive->first()->member_count)->toEqual(5);
});
it('handles campaigns with zero members', function () {
    $campaign = Campaign::factory()->create();

    $result = $this->repository->findById($campaign->id);

    expect($result->member_count)->toEqual(0);
});
it('returns empty collection for user with no campaigns', function () {
    $user = User::factory()->create();

    $created = $this->repository->getCreatedByUser($user);
    $joined = $this->repository->getJoinedByUser($user);
    $all = $this->repository->getAllUserCampaigns($user);

    expect($created)->toHaveCount(0);
    expect($joined)->toHaveCount(0);
    expect($all)->toHaveCount(0);
});
