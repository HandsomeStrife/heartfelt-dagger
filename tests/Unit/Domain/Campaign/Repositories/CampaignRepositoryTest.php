<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Campaign\Repositories;

use Domain\Campaign\Data\CampaignData;
use Domain\Campaign\Data\CampaignMemberData;
use Domain\Campaign\Enums\CampaignStatus;
use Domain\Campaign\Models\Campaign;
use Domain\Campaign\Models\CampaignMember;
use Domain\Campaign\Repositories\CampaignRepository;
use Domain\Character\Models\Character;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CampaignRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private CampaignRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CampaignRepository();
    }

    #[Test]
    public function it_finds_campaign_by_id_with_member_count(): void
    {
        $creator = User::factory()->create(['username' => 'gamemaster']);
        $campaign = Campaign::factory()->create([
            'creator_id' => $creator->id,
            'name' => 'Test Campaign',
        ]);
        
        CampaignMember::factory()->count(3)->create(['campaign_id' => $campaign->id]);

        $result = $this->repository->findById($campaign->id);

        $this->assertInstanceOf(CampaignData::class, $result);
        $this->assertEquals($campaign->id, $result->id);
        $this->assertEquals('Test Campaign', $result->name);
        $this->assertEquals(3, $result->member_count);
        $this->assertNotNull($result->creator);
        $this->assertEquals('gamemaster', $result->creator->username);
    }

    #[Test]
    public function it_returns_null_for_non_existent_campaign(): void
    {
        $result = $this->repository->findById(999);

        $this->assertNull($result);
    }

    #[Test]
    public function it_finds_campaign_by_invite_code(): void
    {
        $campaign = Campaign::factory()->create(['name' => 'Invite Test']);
        CampaignMember::factory()->count(2)->create(['campaign_id' => $campaign->id]);

        $result = $this->repository->findByInviteCode($campaign->invite_code);

        $this->assertInstanceOf(CampaignData::class, $result);
        $this->assertEquals($campaign->id, $result->id);
        $this->assertEquals('Invite Test', $result->name);
        $this->assertEquals(2, $result->member_count);
    }

    #[Test]
    public function it_returns_null_for_invalid_invite_code(): void
    {
        $result = $this->repository->findByInviteCode('INVALID1');

        $this->assertNull($result);
    }

    #[Test]
    public function it_gets_campaigns_created_by_user(): void
    {
        $creator = User::factory()->create();
        $otherUser = User::factory()->create();
        
        $createdCampaigns = Campaign::factory()->count(3)->create(['creator_id' => $creator->id]);
        Campaign::factory()->count(2)->create(['creator_id' => $otherUser->id]);

        $result = $this->repository->getCreatedByUser($creator);

        $this->assertCount(3, $result);
        $this->assertTrue($result->every(fn($campaign) => $campaign->creator_id === $creator->id));
        $this->assertTrue($result->every(fn($campaign) => $campaign instanceof CampaignData));
    }

    #[Test]
    public function it_orders_created_campaigns_by_newest_first(): void
    {
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

        $this->assertEquals($newest->id, $result->first()->id);
        $this->assertEquals($oldest->id, $result->last()->id);
    }

    #[Test]
    public function it_gets_campaigns_joined_by_user(): void
    {
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

        $this->assertCount(2, $result);
        $this->assertTrue($result->every(fn($campaign) => $campaign instanceof CampaignData));
        $this->assertTrue($result->contains('id', $joinedCampaigns[0]->id));
        $this->assertTrue($result->contains('id', $joinedCampaigns[1]->id));
        $this->assertFalse($result->contains('id', $notJoinedCampaign->id));
    }

    #[Test]
    public function it_gets_campaign_members_with_relationships(): void
    {
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

        $this->assertCount(2, $result);
        $this->assertTrue($result->every(fn($member) => $member instanceof CampaignMemberData));
        
        // Check first member (should be ordered by joined_at)
        $firstMember = $result->first();
        $this->assertEquals($member1->id, $firstMember->id);
        $this->assertNotNull($firstMember->user);
        $this->assertEquals('player1', $firstMember->user->username);
        $this->assertNotNull($firstMember->character);
        $this->assertEquals('Aragorn', $firstMember->character->name);
        
        // Check second member
        $secondMember = $result->last();
        $this->assertEquals($member2->id, $secondMember->id);
        $this->assertNotNull($secondMember->user);
        $this->assertEquals('player2', $secondMember->user->username);
        $this->assertNull($secondMember->character);
    }

    #[Test]
    public function it_orders_members_by_joined_at_ascending(): void
    {
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

        $this->assertEquals($earliestMember->id, $result->first()->id);
        $this->assertEquals($latestMember->id, $result->last()->id);
    }

    #[Test]
    public function it_gets_active_campaigns(): void
    {
        Campaign::factory()->count(2)->create(['status' => CampaignStatus::ACTIVE]);
        Campaign::factory()->archived()->create();
        Campaign::factory()->completed()->create();
        Campaign::factory()->paused()->create();

        $result = $this->repository->getActiveCampaigns();

        $this->assertCount(2, $result);
        $this->assertTrue($result->every(fn($campaign) => $campaign->status === CampaignStatus::ACTIVE));
    }

    #[Test]
    public function it_gets_all_user_campaigns_combined(): void
    {
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

        $this->assertCount(3, $result);
        
        // Should be ordered by created_at desc (newest first)
        $this->assertEquals($joinedCampaign->id, $result->first()->id);
        
        // Should include both created and joined campaigns
        $campaignIds = $result->pluck('id')->toArray();
        $this->assertContains($createdCampaigns[0]->id, $campaignIds);
        $this->assertContains($createdCampaigns[1]->id, $campaignIds);
        $this->assertContains($joinedCampaign->id, $campaignIds);
    }

    #[Test]
    public function it_includes_member_count_in_all_queries(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
        CampaignMember::factory()->count(5)->create(['campaign_id' => $campaign->id]);

        $resultById = $this->repository->findById($campaign->id);
        $resultByInvite = $this->repository->findByInviteCode($campaign->invite_code);
        $resultCreated = $this->repository->getCreatedByUser($user);
        $resultActive = $this->repository->getActiveCampaigns();

        $this->assertEquals(5, $resultById->member_count);
        $this->assertEquals(5, $resultByInvite->member_count);
        $this->assertEquals(5, $resultCreated->first()->member_count);
        $this->assertEquals(5, $resultActive->first()->member_count);
    }

    #[Test]
    public function it_handles_campaigns_with_zero_members(): void
    {
        $campaign = Campaign::factory()->create();

        $result = $this->repository->findById($campaign->id);

        $this->assertEquals(0, $result->member_count);
    }

    #[Test]
    public function it_returns_empty_collection_for_user_with_no_campaigns(): void
    {
        $user = User::factory()->create();

        $created = $this->repository->getCreatedByUser($user);
        $joined = $this->repository->getJoinedByUser($user);
        $all = $this->repository->getAllUserCampaigns($user);

        $this->assertCount(0, $created);
        $this->assertCount(0, $joined);
        $this->assertCount(0, $all);
    }
}
