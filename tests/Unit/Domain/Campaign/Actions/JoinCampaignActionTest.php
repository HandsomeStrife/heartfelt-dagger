<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Campaign\Actions;

use Domain\Campaign\Actions\JoinCampaignAction;
use Domain\Campaign\Data\CampaignMemberData;
use Domain\Campaign\Models\Campaign;
use Domain\Campaign\Models\CampaignMember;
use Domain\Character\Models\Character;
use Domain\User\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class JoinCampaignActionTest extends TestCase
{
    use RefreshDatabase;

    private JoinCampaignAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new JoinCampaignAction();
    }

    #[Test]
    public function it_joins_campaign_with_character_successfully(): void
    {
        $campaign = Campaign::factory()->create();
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        $result = $this->action->execute($campaign, $user, $character);

        $this->assertInstanceOf(CampaignMemberData::class, $result);
        $this->assertEquals($campaign->id, $result->campaign_id);
        $this->assertEquals($user->id, $result->user_id);
        $this->assertEquals($character->id, $result->character_id);
    }

    #[Test]
    public function it_joins_campaign_without_character_successfully(): void
    {
        $campaign = Campaign::factory()->create();
        $user = User::factory()->create();

        $result = $this->action->execute($campaign, $user, null);

        $this->assertInstanceOf(CampaignMemberData::class, $result);
        $this->assertEquals($campaign->id, $result->campaign_id);
        $this->assertEquals($user->id, $result->user_id);
        $this->assertNull($result->character_id);
    }

    #[Test]
    public function it_persists_membership_to_database(): void
    {
        $campaign = Campaign::factory()->create();
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        $result = $this->action->execute($campaign, $user, $character);

        $this->assertDatabaseHas('campaign_members', [
            'id' => $result->id,
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
            'character_id' => $character->id,
        ]);
    }

    #[Test]
    public function it_sets_joined_at_timestamp(): void
    {
        $campaign = Campaign::factory()->create();
        $user = User::factory()->create();

        $beforeJoin = now()->subSecond(); // Give 1 second buffer
        $result = $this->action->execute($campaign, $user, null);
        $afterJoin = now()->addSecond(); // Give 1 second buffer

        $joinedAt = \Carbon\Carbon::parse($result->joined_at);
        $this->assertTrue($joinedAt->between($beforeJoin, $afterJoin));
    }

    #[Test]
    public function it_loads_all_relationships(): void
    {
        $campaign = Campaign::factory()->create(['name' => 'Test Campaign']);
        $user = User::factory()->create(['username' => 'player1']);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'name' => 'Legolas',
        ]);

        $result = $this->action->execute($campaign, $user, $character);

        $this->assertNotNull($result->user);
        $this->assertEquals('player1', $result->user->username);
        
        $this->assertNotNull($result->character);
        $this->assertEquals('Legolas', $result->character->name);
        
        $this->assertNotNull($result->campaign);
        $this->assertEquals('Test Campaign', $result->campaign->name);
    }

    #[Test]
    public function it_prevents_duplicate_membership(): void
    {
        $campaign = Campaign::factory()->create();
        $user = User::factory()->create();

        // Create existing membership
        CampaignMember::factory()->create([
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User is already a member of this campaign');

        $this->action->execute($campaign, $user, null);
    }

    #[Test]
    public function it_validates_character_ownership(): void
    {
        $campaign = Campaign::factory()->create();
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $otherUser->id]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Character does not belong to the user');

        $this->action->execute($campaign, $user, $character);
    }

    #[Test]
    public function it_allows_creator_to_join_their_own_campaign(): void
    {
        $creator = User::factory()->create();
        $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);
        $character = Character::factory()->create(['user_id' => $creator->id]);

        $result = $this->action->execute($campaign, $creator, $character);

        $this->assertEquals($campaign->id, $result->campaign_id);
        $this->assertEquals($creator->id, $result->user_id);
        $this->assertEquals($character->id, $result->character_id);
    }

    #[Test]
    public function it_allows_multiple_users_to_join_same_campaign(): void
    {
        $campaign = Campaign::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $result1 = $this->action->execute($campaign, $user1, null);
        $result2 = $this->action->execute($campaign, $user2, null);

        $this->assertEquals($campaign->id, $result1->campaign_id);
        $this->assertEquals($campaign->id, $result2->campaign_id);
        $this->assertNotEquals($result1->user_id, $result2->user_id);
    }

    #[Test]
    public function it_allows_user_to_join_different_campaigns(): void
    {
        $campaign1 = Campaign::factory()->create();
        $campaign2 = Campaign::factory()->create();
        $user = User::factory()->create();
        $character1 = Character::factory()->create(['user_id' => $user->id]);
        $character2 = Character::factory()->create(['user_id' => $user->id]);

        $result1 = $this->action->execute($campaign1, $user, $character1);
        $result2 = $this->action->execute($campaign2, $user, $character2);

        $this->assertEquals($user->id, $result1->user_id);
        $this->assertEquals($user->id, $result2->user_id);
        $this->assertNotEquals($result1->campaign_id, $result2->campaign_id);
    }

    #[Test]
    public function it_handles_null_character_gracefully(): void
    {
        $campaign = Campaign::factory()->create();
        $user = User::factory()->create();

        $result = $this->action->execute($campaign, $user, null);

        $this->assertNull($result->character_id);
        $this->assertNull($result->character);
    }

    #[Test]
    public function it_maintains_separate_memberships_per_campaign(): void
    {
        $campaign1 = Campaign::factory()->create();
        $campaign2 = Campaign::factory()->create();
        $user = User::factory()->create();

        $this->action->execute($campaign1, $user, null);
        $this->action->execute($campaign2, $user, null);

        $this->assertDatabaseCount('campaign_members', 2);
        $this->assertEquals(1, $campaign1->members()->count());
        $this->assertEquals(1, $campaign2->members()->count());
    }

    #[Test]
    public function it_validates_character_exists_when_provided(): void
    {
        $campaign = Campaign::factory()->create();
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $otherUser->id]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Character does not belong to the user');

        $this->action->execute($campaign, $user, $character);
    }
}
