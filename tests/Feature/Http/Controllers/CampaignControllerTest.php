<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use Domain\Campaign\Models\Campaign;
use Domain\Campaign\Models\CampaignMember;
use Domain\Character\Models\Character;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CampaignControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function campaigns_index_requires_authentication(): void
    {
        $response = $this->get(route('campaigns.index'));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function authenticated_user_can_view_campaigns_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('campaigns.index'));

        $response->assertOk();
        $response->assertViewIs('campaigns.index');
    }

    #[Test]
    public function campaigns_index_displays_created_campaigns(): void
    {
        $user = User::factory()->create();
        $createdCampaigns = Campaign::factory()->count(2)->create(['creator_id' => $user->id]);
        Campaign::factory()->create(); // Other user's campaign

        $response = $this->actingAs($user)->get(route('campaigns.index'));

        $response->assertOk();
        $response->assertViewHas('created_campaigns');
        $viewData = $response->viewData('created_campaigns');
        $this->assertCount(2, $viewData);
    }

    #[Test]
    public function campaigns_index_displays_joined_campaigns(): void
    {
        $user = User::factory()->create();
        $joinedCampaign = Campaign::factory()->create();
        CampaignMember::factory()->create([
            'campaign_id' => $joinedCampaign->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('campaigns.index'));

        $response->assertOk();
        $response->assertViewHas('joined_campaigns');
        $viewData = $response->viewData('joined_campaigns');
        $this->assertCount(1, $viewData);
    }

    #[Test]
    public function create_campaign_form_requires_authentication(): void
    {
        $response = $this->get(route('campaigns.create'));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function authenticated_user_can_view_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('campaigns.create'));

        $response->assertOk();
        $response->assertViewIs('campaigns.create');
    }

    #[Test]
    public function user_can_create_campaign_with_valid_data(): void
    {
        $user = User::factory()->create();
        $campaignData = [
            'name' => 'The Lost Mines of Phandelver',
            'description' => 'A classic adventure for new heroes.',
        ];

        $response = $this->actingAs($user)->post(route('campaigns.store'), $campaignData);

        $this->assertDatabaseHas('campaigns', [
            'name' => 'The Lost Mines of Phandelver',
            'description' => 'A classic adventure for new heroes.',
            'creator_id' => $user->id,
        ]);

        $campaign = Campaign::where('name', 'The Lost Mines of Phandelver')->first();
        $response->assertRedirect(route('campaigns.show', $campaign->campaign_code));
        $response->assertSessionHas('success', 'Campaign created successfully!');
    }

    #[Test]
    public function campaign_creation_requires_authentication(): void
    {
        $campaignData = [
            'name' => 'Unauthorized Campaign',
            'description' => 'This should not be created.',
        ];

        $response = $this->post(route('campaigns.store'), $campaignData);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('campaigns', ['name' => 'Unauthorized Campaign']);
    }

    #[Test]
    public function campaign_creation_validates_required_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('campaigns.store'), []);

        $response->assertSessionHasErrors(['name', 'description']);
        $this->assertDatabaseCount('campaigns', 0);
    }

    #[Test]
    public function campaign_creation_validates_field_lengths(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('campaigns.store'), [
            'name' => str_repeat('A', 101), // Too long
            'description' => str_repeat('B', 1001), // Too long
        ]);

        $response->assertSessionHasErrors(['name', 'description']);
    }

    #[Test]
    public function user_can_view_their_own_campaign(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('campaigns.show', $campaign->campaign_code));

        $response->assertOk();
        $response->assertViewIs('campaigns.show');
        $response->assertViewHas('campaign');
        $response->assertViewHas('user_is_creator', true);
    }

    #[Test]
    public function user_can_view_other_users_campaign(): void
    {
        $creator = User::factory()->create();
        $viewer = User::factory()->create();
        $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

        $response = $this->actingAs($viewer)->get(route('campaigns.show', $campaign->campaign_code));

        $response->assertOk();
        $response->assertViewHas('user_is_creator', false);
        $response->assertViewHas('user_is_member', false);
    }

    #[Test]
    public function campaign_show_requires_authentication(): void
    {
        $campaign = Campaign::factory()->create();

        $response = $this->get(route('campaigns.show', $campaign->campaign_code));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function campaign_show_returns_404_for_invalid_code(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('campaigns.show', 'INVALID1'));

        $response->assertNotFound();
    }

    #[Test]
    public function user_can_view_join_form_via_invite_code(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create();

        $response = $this->actingAs($user)->get(route('campaigns.invite', $campaign->invite_code));

        $response->assertOk();
        $response->assertViewIs('campaigns.join');
        $response->assertViewHas('campaign');
        $response->assertViewHas('characters');
    }

    #[Test]
    public function join_form_requires_authentication(): void
    {
        $campaign = Campaign::factory()->create();

        $response = $this->get(route('campaigns.invite', $campaign->invite_code));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function existing_member_redirected_from_join_form(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create();
        CampaignMember::factory()->create([
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('campaigns.invite', $campaign->invite_code));

        $response->assertRedirect(route('campaigns.show', $campaign->campaign_code));
        $response->assertSessionHas('info', 'You are already a member of this campaign.');
    }

    #[Test]
    public function user_can_join_campaign_with_character(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('campaigns.join', $campaign->campaign_code), [
            'character_id' => $character->id,
        ]);

        $this->assertDatabaseHas('campaign_members', [
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
            'character_id' => $character->id,
        ]);

        $response->assertRedirect(route('campaigns.show', $campaign->campaign_code));
        $response->assertSessionHas('success', 'Successfully joined the campaign!');
    }

    #[Test]
    public function user_can_join_campaign_without_character(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create();

        $response = $this->actingAs($user)->post(route('campaigns.join', $campaign->campaign_code), [
            'character_id' => null,
        ]);

        $this->assertDatabaseHas('campaign_members', [
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
            'character_id' => null,
        ]);

        $response->assertRedirect(route('campaigns.show', $campaign->campaign_code));
    }

    #[Test]
    public function join_campaign_requires_authentication(): void
    {
        $campaign = Campaign::factory()->create();

        $response = $this->post(route('campaigns.join', $campaign->campaign_code), []);

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function join_campaign_validates_character_ownership(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $campaign = Campaign::factory()->create();
        $otherCharacter = Character::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->post(route('campaigns.join', $campaign->campaign_code), [
            'character_id' => $otherCharacter->id,
        ]);

        $response->assertNotFound(); // Character not found for this user
        $this->assertDatabaseMissing('campaign_members', [
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
        ]);
    }

    #[Test]
    public function user_cannot_join_campaign_twice(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create();
        
        // Join once
        $this->actingAs($user)->post(route('campaigns.join', $campaign->campaign_code), []);

        // Try to join again
        $response = $this->actingAs($user)->post(route('campaigns.join', $campaign->campaign_code), []);

        $response->assertSessionHasErrors(['error']);
        $this->assertDatabaseCount('campaign_members', 1);
    }

    #[Test]
    public function member_can_leave_campaign(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create();
        CampaignMember::factory()->create([
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete(route('campaigns.leave', $campaign->campaign_code));

        $this->assertDatabaseMissing('campaign_members', [
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
        ]);

        $response->assertRedirect(route('campaigns.index'));
        $response->assertSessionHas('success', 'Successfully left the campaign.');
    }

    #[Test]
    public function leave_campaign_requires_authentication(): void
    {
        $campaign = Campaign::factory()->create();

        $response = $this->delete(route('campaigns.leave', $campaign->campaign_code));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function creator_cannot_leave_own_campaign(): void
    {
        $creator = User::factory()->create();
        $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

        $response = $this->actingAs($creator)->delete(route('campaigns.leave', $campaign->campaign_code));

        $response->assertSessionHasErrors(['error']);
    }

    #[Test]
    public function non_member_cannot_leave_campaign(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create();

        $response = $this->actingAs($user)->delete(route('campaigns.leave', $campaign->campaign_code));

        $response->assertSessionHasErrors(['error']);
    }

    #[Test]
    public function campaign_show_displays_members(): void
    {
        $creator = User::factory()->create(['username' => 'gamemaster']);
        $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);
        $member = User::factory()->create(['username' => 'player1']);
        $character = Character::factory()->create([
            'user_id' => $member->id,
            'name' => 'Aragorn',
        ]);
        
        CampaignMember::factory()->create([
            'campaign_id' => $campaign->id,
            'user_id' => $member->id,
            'character_id' => $character->id,
        ]);

        $response = $this->actingAs($creator)->get(route('campaigns.show', $campaign->campaign_code));

        $response->assertOk();
        $response->assertViewHas('members');
        $viewData = $response->viewData('members');
        $this->assertCount(1, $viewData);
    }

    #[Test]
    public function join_form_displays_user_characters(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create();
        $characters = Character::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('campaigns.invite', $campaign->invite_code));

        $response->assertOk();
        $response->assertViewHas('characters');
        $viewData = $response->viewData('characters');
        $this->assertCount(3, $viewData);
    }

    #[Test]
    public function routes_use_correct_parameter_binding(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create();

        // Show route uses campaign_code
        $response = $this->actingAs($user)->get("/campaigns/{$campaign->campaign_code}");
        $response->assertOk();

        // Join form uses invite_code  
        $response = $this->actingAs($user)->get("/join/{$campaign->invite_code}");
        $response->assertOk();
    }
}
