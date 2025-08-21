<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Campaign\Models;

use Domain\Campaign\Models\Campaign;
use Domain\Campaign\Models\CampaignMember;
use Domain\Character\Models\Character;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CampaignMemberTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_belongs_to_campaign(): void
    {
        $campaign = Campaign::factory()->create();
        $member = CampaignMember::factory()->create(['campaign_id' => $campaign->id]);

        $this->assertInstanceOf(Campaign::class, $member->campaign);
        $this->assertEquals($campaign->id, $member->campaign->id);
    }

    #[Test]
    public function it_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $member = CampaignMember::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $member->user);
        $this->assertEquals($user->id, $member->user->id);
    }

    #[Test]
    public function it_belongs_to_character(): void
    {
        $character = Character::factory()->create();
        $member = CampaignMember::factory()->create(['character_id' => $character->id]);

        $this->assertInstanceOf(Character::class, $member->character);
        $this->assertEquals($character->id, $member->character->id);
    }

    #[Test]
    public function it_can_have_null_character(): void
    {
        $member = CampaignMember::factory()->withoutCharacter()->create();

        $this->assertNull($member->character_id);
        $this->assertNull($member->character);
    }

    #[Test]
    public function it_checks_if_has_character(): void
    {
        $memberWithCharacter = CampaignMember::factory()->create();
        $memberWithoutCharacter = CampaignMember::factory()->withoutCharacter()->create();

        $this->assertTrue($memberWithCharacter->hasCharacter());
        $this->assertFalse($memberWithoutCharacter->hasCharacter());
    }

    #[Test]
    public function it_gets_display_name_with_character(): void
    {
        $character = Character::factory()->create(['name' => 'Aragorn']);
        $member = CampaignMember::factory()->create(['character_id' => $character->id]);

        $this->assertEquals('Aragorn', $member->getDisplayName());
    }

    #[Test]
    public function it_gets_display_name_without_character(): void
    {
        $member = CampaignMember::factory()->withoutCharacter()->create();

        $this->assertEquals('Empty Character', $member->getDisplayName());
    }

    #[Test]
    public function it_gets_character_class_with_character(): void
    {
        $character = Character::factory()->create(['class' => 'Ranger']);
        $member = CampaignMember::factory()->create(['character_id' => $character->id]);

        $this->assertEquals('Ranger', $member->getCharacterClass());
    }

    #[Test]
    public function it_gets_null_character_class_without_character(): void
    {
        $member = CampaignMember::factory()->withoutCharacter()->create();

        $this->assertNull($member->getCharacterClass());
    }

    #[Test]
    public function it_gets_character_subclass_with_character(): void
    {
        $character = Character::factory()->create(['subclass' => 'Beast Master']);
        $member = CampaignMember::factory()->create(['character_id' => $character->id]);

        $this->assertEquals('Beast Master', $member->getCharacterSubclass());
    }

    #[Test]
    public function it_gets_null_character_subclass_without_character(): void
    {
        $member = CampaignMember::factory()->withoutCharacter()->create();

        $this->assertNull($member->getCharacterSubclass());
    }

    #[Test]
    public function it_gets_character_ancestry_with_character(): void
    {
        $character = Character::factory()->create(['ancestry' => 'Human']);
        $member = CampaignMember::factory()->create(['character_id' => $character->id]);

        $this->assertEquals('Human', $member->getCharacterAncestry());
    }

    #[Test]
    public function it_gets_null_character_ancestry_without_character(): void
    {
        $member = CampaignMember::factory()->withoutCharacter()->create();

        $this->assertNull($member->getCharacterAncestry());
    }

    #[Test]
    public function it_gets_character_community_with_character(): void
    {
        $character = Character::factory()->create(['community' => 'Wildborne']);
        $member = CampaignMember::factory()->create(['character_id' => $character->id]);

        $this->assertEquals('Wildborne', $member->getCharacterCommunity());
    }

    #[Test]
    public function it_gets_null_character_community_without_character(): void
    {
        $member = CampaignMember::factory()->withoutCharacter()->create();

        $this->assertNull($member->getCharacterCommunity());
    }

    #[Test]
    public function it_scopes_members_with_characters(): void
    {
        $campaign = Campaign::factory()->create();
        
        CampaignMember::factory()->count(3)->create(['campaign_id' => $campaign->id]);
        CampaignMember::factory()->count(2)->withoutCharacter()->create(['campaign_id' => $campaign->id]);

        $membersWithCharacters = CampaignMember::withCharacters()->get();

        $this->assertCount(3, $membersWithCharacters);
        $this->assertTrue($membersWithCharacters->every(fn($member) => $member->character_id !== null));
    }

    #[Test]
    public function it_scopes_members_without_characters(): void
    {
        $campaign = Campaign::factory()->create();
        
        CampaignMember::factory()->count(3)->create(['campaign_id' => $campaign->id]);
        CampaignMember::factory()->count(2)->withoutCharacter()->create(['campaign_id' => $campaign->id]);

        $membersWithoutCharacters = CampaignMember::withoutCharacters()->get();

        $this->assertCount(2, $membersWithoutCharacters);
        $this->assertTrue($membersWithoutCharacters->every(fn($member) => $member->character_id === null));
    }

    #[Test]
    public function it_casts_joined_at_to_datetime(): void
    {
        $timestamp = now()->subDays(5);
        $member = CampaignMember::factory()->joinedAt($timestamp)->create();

        $this->assertInstanceOf(\Carbon\Carbon::class, $member->joined_at);
        $this->assertEquals($timestamp->format('Y-m-d H:i:s'), $member->joined_at->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_handles_missing_character_gracefully(): void
    {
        // Create member with character, then delete the character
        $character = Character::factory()->create(['name' => 'Test Character']);
        $member = CampaignMember::factory()->create(['character_id' => $character->id]);
        
        // Delete the character
        $character->delete();
        
        // Refresh the member to clear loaded relationships
        $member->refresh();

        $this->assertNull($member->character);
        $this->assertEquals('Empty Character', $member->getDisplayName());
        $this->assertNull($member->getCharacterClass());
    }
}
