<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\Campaign\Models\CampaignMember;
use Domain\Character\Models\Character;
use Domain\User\Models\User;
use function Pest\Laravel\{actingAs, assertDatabaseHas};

it('allows user to update their campaign character', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create();
    
    // Create characters for the user
    $character1 = Character::factory()->forUser($user)->create(['name' => 'First Character']);
    $character2 = Character::factory()->forUser($user)->create(['name' => 'Second Character']);
    
    // Create campaign member with first character
    $member = CampaignMember::create([
        'campaign_id' => $campaign->id,
        'user_id' => $user->id,
        'character_id' => $character1->id,
        'joined_at' => now(),
    ]);
    
    // Update to second character
    $response = actingAs($user)->patch(route('campaigns.update_character', $campaign), [
        'member_id' => $member->id,
        'character_id' => $character2->id,
    ]);
    
    $response->assertRedirect(route('campaigns.show', $campaign->campaign_code));
    $response->assertSessionHas('success', 'Character updated successfully!');
    
    assertDatabaseHas('campaign_members', [
        'id' => $member->id,
        'character_id' => $character2->id,
    ]);
});

it('prevents user from updating another users campaign character', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $campaign = Campaign::factory()->create();
    
    $character1 = Character::factory()->forUser($user1)->create();
    $character2 = Character::factory()->forUser($user2)->create();
    
    // Create campaign member for user1
    $member = CampaignMember::create([
        'campaign_id' => $campaign->id,
        'user_id' => $user1->id,
        'character_id' => $character1->id,
        'joined_at' => now(),
    ]);
    
    // Try to update user1's member as user2
    $response = actingAs($user2)->patch(route('campaigns.update_character', $campaign), [
        'member_id' => $member->id,
        'character_id' => $character2->id,
    ]);
    
    $response->assertRedirect();
    $response->assertSessionHasErrors(['error' => 'You can only update your own character.']);
    
    // Should remain unchanged
    assertDatabaseHas('campaign_members', [
        'id' => $member->id,
        'character_id' => $character1->id,
    ]);
});

it('prevents user from assigning another users character', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $campaign = Campaign::factory()->create();
    
    $character1 = Character::factory()->forUser($user1)->create();
    $character2 = Character::factory()->forUser($user2)->create(); // Different user's character
    
    $member = CampaignMember::create([
        'campaign_id' => $campaign->id,
        'user_id' => $user1->id,
        'character_id' => $character1->id,
        'joined_at' => now(),
    ]);
    
    // Try to assign user2's character to user1's member
    $response = actingAs($user1)->patch(route('campaigns.update_character', $campaign), [
        'member_id' => $member->id,
        'character_id' => $character2->id, // This character belongs to user2
    ]);
    
    $response->assertRedirect();
    $response->assertSessionHasErrors(['error' => 'Character not found or does not belong to you.']);
    
    // Should remain unchanged
    assertDatabaseHas('campaign_members', [
        'id' => $member->id,
        'character_id' => $character1->id,
    ]);
});
