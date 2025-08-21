<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Campaign\Models;

use Domain\Campaign\Enums\CampaignStatus;
use Domain\Campaign\Models\Campaign;
use Domain\Campaign\Models\CampaignMember;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CampaignTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_generates_unique_invite_code_on_creation(): void
    {
        $campaign = Campaign::factory()->create();

        $this->assertNotNull($campaign->invite_code);
        $this->assertEquals(8, strlen($campaign->invite_code));
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{8}$/', $campaign->invite_code);
    }

    #[Test]
    public function it_generates_unique_campaign_code_on_creation(): void
    {
        $campaign = Campaign::factory()->create();

        $this->assertNotNull($campaign->campaign_code);
        $this->assertEquals(8, strlen($campaign->campaign_code));
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{8}$/', $campaign->campaign_code);
    }

    #[Test]
    public function it_generates_unique_codes_across_multiple_campaigns(): void
    {
        $campaigns = Campaign::factory()->count(100)->create();

        $inviteCodes = $campaigns->pluck('invite_code')->toArray();
        $campaignCodes = $campaigns->pluck('campaign_code')->toArray();

        $this->assertEquals(100, count(array_unique($inviteCodes)));
        $this->assertEquals(100, count(array_unique($campaignCodes)));
        
        // Ensure invite codes and campaign codes are different
        $this->assertEmpty(array_intersect($inviteCodes, $campaignCodes));
    }

    #[Test]
    public function it_has_proper_default_status(): void
    {
        $campaign = Campaign::factory()->create();

        $this->assertEquals(CampaignStatus::ACTIVE, $campaign->status);
    }

    #[Test]
    public function it_casts_status_to_enum(): void
    {
        $campaign = Campaign::factory()->create(['status' => 'archived']);

        $this->assertInstanceOf(CampaignStatus::class, $campaign->status);
        $this->assertEquals(CampaignStatus::ARCHIVED, $campaign->status);
    }

    #[Test]
    public function it_uses_campaign_code_as_route_key(): void
    {
        $campaign = Campaign::factory()->create();

        $this->assertEquals('campaign_code', $campaign->getRouteKeyName());
    }

    #[Test]
    public function it_belongs_to_creator(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

        $this->assertInstanceOf(User::class, $campaign->creator);
        $this->assertEquals($user->id, $campaign->creator->id);
    }

    #[Test]
    public function it_has_many_members(): void
    {
        $campaign = Campaign::factory()->create();
        $members = CampaignMember::factory()->count(3)->create([
            'campaign_id' => $campaign->id,
        ]);

        $this->assertCount(3, $campaign->members);
        $this->assertInstanceOf(CampaignMember::class, $campaign->members->first());
    }

    #[Test]
    public function it_checks_if_user_is_creator(): void
    {
        $creator = User::factory()->create();
        $otherUser = User::factory()->create();
        $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

        $this->assertTrue($campaign->isCreator($creator));
        $this->assertFalse($campaign->isCreator($otherUser));
    }

    #[Test]
    public function it_checks_if_user_is_member(): void
    {
        $campaign = Campaign::factory()->create();
        $member = User::factory()->create();
        $nonMember = User::factory()->create();

        CampaignMember::factory()->create([
            'campaign_id' => $campaign->id,
            'user_id' => $member->id,
        ]);

        $this->assertTrue($campaign->hasMember($member));
        $this->assertFalse($campaign->hasMember($nonMember));
    }

    #[Test]
    public function it_gets_member_count(): void
    {
        $campaign = Campaign::factory()->create();
        CampaignMember::factory()->count(5)->create([
            'campaign_id' => $campaign->id,
        ]);

        $this->assertEquals(5, $campaign->getMemberCount());
    }

    #[Test]
    public function it_generates_invite_url(): void
    {
        $campaign = Campaign::factory()->create();

        $expectedUrl = route('campaigns.invite', ['invite_code' => $campaign->invite_code]);
        $this->assertEquals($expectedUrl, $campaign->getInviteUrl());
    }

    #[Test]
    public function it_scopes_campaigns_by_creator(): void
    {
        $creator = User::factory()->create();
        $otherUser = User::factory()->create();
        
        $creatorCampaigns = Campaign::factory()->count(3)->create(['creator_id' => $creator->id]);
        Campaign::factory()->count(2)->create(['creator_id' => $otherUser->id]);

        $result = Campaign::byCreator($creator)->get();

        $this->assertCount(3, $result);
        $this->assertTrue($result->every(fn($campaign) => $campaign->creator_id === $creator->id));
    }

    #[Test]
    public function it_scopes_campaigns_by_invite_code(): void
    {
        $campaign = Campaign::factory()->create();
        Campaign::factory()->create(); // Another campaign

        $result = Campaign::byInviteCode($campaign->invite_code)->first();

        $this->assertNotNull($result);
        $this->assertEquals($campaign->id, $result->id);
    }

    #[Test]
    public function it_scopes_campaigns_by_campaign_code(): void
    {
        $campaign = Campaign::factory()->create();
        Campaign::factory()->create(); // Another campaign

        $result = Campaign::byCampaignCode($campaign->campaign_code)->first();

        $this->assertNotNull($result);
        $this->assertEquals($campaign->id, $result->id);
    }

    #[Test]
    public function it_scopes_active_campaigns(): void
    {
        Campaign::factory()->count(2)->create(['status' => CampaignStatus::ACTIVE]);
        Campaign::factory()->archived()->create();
        Campaign::factory()->completed()->create();

        $result = Campaign::active()->get();

        $this->assertCount(2, $result);
        $this->assertTrue($result->every(fn($campaign) => $campaign->status === CampaignStatus::ACTIVE));
    }

    #[Test]
    public function it_generates_unique_invite_codes_when_duplicates_exist(): void
    {
        // Test that the method handles collisions properly by creating many campaigns
        $campaigns = Campaign::factory()->count(50)->create();
        $codes = $campaigns->pluck('invite_code');
        
        // All codes should be unique
        $this->assertEquals(50, $codes->unique()->count());
    }

    #[Test]
    public function it_generates_unique_campaign_codes_when_duplicates_exist(): void
    {
        // Test that the method handles collisions properly by creating many campaigns
        $campaigns = Campaign::factory()->count(50)->create();
        $codes = $campaigns->pluck('campaign_code');
        
        // All codes should be unique
        $this->assertEquals(50, $codes->unique()->count());
    }
}
