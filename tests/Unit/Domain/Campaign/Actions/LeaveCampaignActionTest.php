<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Campaign\Actions;

use Domain\Campaign\Actions\LeaveCampaignAction;
use Domain\Campaign\Models\Campaign;
use Domain\Campaign\Models\CampaignMember;
use Domain\User\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LeaveCampaignActionTest extends TestCase
{
    use RefreshDatabase;

    private LeaveCampaignAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new LeaveCampaignAction();
    }

    #[Test]
    public function it_allows_member_to_leave_campaign(): void
    {
        $campaign = Campaign::factory()->create();
        $member = User::factory()->create();
        
        CampaignMember::factory()->create([
            'campaign_id' => $campaign->id,
            'user_id' => $member->id,
        ]);

        $result = $this->action->execute($campaign, $member);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('campaign_members', [
            'campaign_id' => $campaign->id,
            'user_id' => $member->id,
        ]);
    }

    #[Test]
    public function it_removes_membership_from_database(): void
    {
        $campaign = Campaign::factory()->create();
        $member = User::factory()->create();
        
        $membership = CampaignMember::factory()->create([
            'campaign_id' => $campaign->id,
            'user_id' => $member->id,
        ]);

        $this->assertDatabaseHas('campaign_members', [
            'id' => $membership->id,
            'campaign_id' => $campaign->id,
            'user_id' => $member->id,
        ]);

        $this->action->execute($campaign, $member);

        $this->assertDatabaseMissing('campaign_members', [
            'id' => $membership->id,
        ]);
    }

    #[Test]
    public function it_prevents_creator_from_leaving_own_campaign(): void
    {
        $creator = User::factory()->create();
        $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Campaign creator cannot leave their own campaign');

        $this->action->execute($campaign, $creator);
    }

    #[Test]
    public function it_prevents_non_member_from_leaving(): void
    {
        $campaign = Campaign::factory()->create();
        $nonMember = User::factory()->create();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User is not a member of this campaign');

        $this->action->execute($campaign, $nonMember);
    }

    #[Test]
    public function it_allows_multiple_members_to_leave_independently(): void
    {
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

        $this->assertTrue($result1);
        $this->assertDatabaseMissing('campaign_members', [
            'campaign_id' => $campaign->id,
            'user_id' => $member1->id,
        ]);
        $this->assertDatabaseHas('campaign_members', [
            'campaign_id' => $campaign->id,
            'user_id' => $member2->id,
        ]);

        // Member 2 leaves
        $result2 = $this->action->execute($campaign, $member2);

        $this->assertTrue($result2);
        $this->assertDatabaseMissing('campaign_members', [
            'campaign_id' => $campaign->id,
            'user_id' => $member2->id,
        ]);
    }

    #[Test]
    public function it_handles_member_in_multiple_campaigns(): void
    {
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

        $this->assertTrue($result);
        $this->assertDatabaseMissing('campaign_members', [
            'campaign_id' => $campaign1->id,
            'user_id' => $member->id,
        ]);
        $this->assertDatabaseHas('campaign_members', [
            'campaign_id' => $campaign2->id,
            'user_id' => $member->id,
        ]);
    }

    #[Test]
    public function it_does_not_affect_other_members_when_one_leaves(): void
    {
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
        $this->assertDatabaseHas('campaign_members', [
            'id' => $membership2->id,
            'campaign_id' => $campaign->id,
            'user_id' => $member2->id,
        ]);
        $this->assertDatabaseHas('campaign_members', [
            'id' => $membership3->id,
            'campaign_id' => $campaign->id,
            'user_id' => $member3->id,
        ]);
    }

    #[Test]
    public function it_handles_leaving_with_character_attached(): void
    {
        $campaign = Campaign::factory()->create();
        $member = User::factory()->create();
        
        $membership = CampaignMember::factory()->create([
            'campaign_id' => $campaign->id,
            'user_id' => $member->id,
            // character_id will be set by factory
        ]);

        $result = $this->action->execute($campaign, $member);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('campaign_members', [
            'id' => $membership->id,
        ]);
    }

    #[Test]
    public function it_handles_leaving_without_character(): void
    {
        $campaign = Campaign::factory()->create();
        $member = User::factory()->create();
        
        $membership = CampaignMember::factory()->withoutCharacter()->create([
            'campaign_id' => $campaign->id,
            'user_id' => $member->id,
        ]);

        $result = $this->action->execute($campaign, $member);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('campaign_members', [
            'id' => $membership->id,
        ]);
    }

    #[Test]
    public function it_prevents_double_leaving(): void
    {
        $campaign = Campaign::factory()->create();
        $member = User::factory()->create();
        
        CampaignMember::factory()->create([
            'campaign_id' => $campaign->id,
            'user_id' => $member->id,
        ]);

        // First leave should work
        $result1 = $this->action->execute($campaign, $member);
        $this->assertTrue($result1);

        // Second leave should fail
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User is not a member of this campaign');

        $this->action->execute($campaign, $member);
    }

    #[Test]
    public function it_maintains_campaign_integrity_after_leave(): void
    {
        $creator = User::factory()->create();
        $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);
        $member = User::factory()->create();
        
        CampaignMember::factory()->create([
            'campaign_id' => $campaign->id,
            'user_id' => $member->id,
        ]);

        $this->action->execute($campaign, $member);

        // Campaign should still exist and be accessible
        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'creator_id' => $creator->id,
        ]);

        $campaign->refresh();
        $this->assertNotNull($campaign);
        $this->assertEquals($creator->id, $campaign->creator_id);
    }
}
