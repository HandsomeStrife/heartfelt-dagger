<?php

declare(strict_types=1);
use Domain\Campaign\Models\Campaign;
use Domain\Campaign\Models\CampaignMember;
use Domain\Character\Models\Character;
use Domain\User\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('belongs to campaign', function () {
    $campaign = Campaign::factory()->create();
    $member = CampaignMember::factory()->create(['campaign_id' => $campaign->id]);

    expect($member->campaign)->toBeInstanceOf(Campaign::class);
    expect($member->campaign->id)->toEqual($campaign->id);
});
it('belongs to user', function () {
    $user = User::factory()->create();
    $member = CampaignMember::factory()->create(['user_id' => $user->id]);

    expect($member->user)->toBeInstanceOf(User::class);
    expect($member->user->id)->toEqual($user->id);
});
it('belongs to character', function () {
    $character = Character::factory()->create();
    $member = CampaignMember::factory()->create(['character_id' => $character->id]);

    expect($member->character)->toBeInstanceOf(Character::class);
    expect($member->character->id)->toEqual($character->id);
});
it('can have null character', function () {
    $member = CampaignMember::factory()->withoutCharacter()->create();

    expect($member->character_id)->toBeNull();
    expect($member->character)->toBeNull();
});
it('checks if has character', function () {
    $memberWithCharacter = CampaignMember::factory()->create();
    $memberWithoutCharacter = CampaignMember::factory()->withoutCharacter()->create();

    expect($memberWithCharacter->hasCharacter())->toBeTrue();
    expect($memberWithoutCharacter->hasCharacter())->toBeFalse();
});
it('gets display name with character', function () {
    $character = Character::factory()->create(['name' => 'Aragorn']);
    $member = CampaignMember::factory()->create(['character_id' => $character->id]);

    expect($member->getDisplayName())->toEqual('Aragorn');
});
it('gets display name without character', function () {
    $member = CampaignMember::factory()->withoutCharacter()->create();

    expect($member->getDisplayName())->toEqual('Empty Character');
});
it('gets character class with character', function () {
    $character = Character::factory()->create(['class' => 'Ranger']);
    $member = CampaignMember::factory()->create(['character_id' => $character->id]);

    expect($member->getCharacterClass())->toEqual('Ranger');
});
it('gets null character class without character', function () {
    $member = CampaignMember::factory()->withoutCharacter()->create();

    expect($member->getCharacterClass())->toBeNull();
});
it('gets character subclass with character', function () {
    $character = Character::factory()->create(['subclass' => 'Beast Master']);
    $member = CampaignMember::factory()->create(['character_id' => $character->id]);

    expect($member->getCharacterSubclass())->toEqual('Beast Master');
});
it('gets null character subclass without character', function () {
    $member = CampaignMember::factory()->withoutCharacter()->create();

    expect($member->getCharacterSubclass())->toBeNull();
});
it('gets character ancestry with character', function () {
    $character = Character::factory()->create(['ancestry' => 'Human']);
    $member = CampaignMember::factory()->create(['character_id' => $character->id]);

    expect($member->getCharacterAncestry())->toEqual('Human');
});
it('gets null character ancestry without character', function () {
    $member = CampaignMember::factory()->withoutCharacter()->create();

    expect($member->getCharacterAncestry())->toBeNull();
});
it('gets character community with character', function () {
    $character = Character::factory()->create(['community' => 'Wildborne']);
    $member = CampaignMember::factory()->create(['character_id' => $character->id]);

    expect($member->getCharacterCommunity())->toEqual('Wildborne');
});
it('gets null character community without character', function () {
    $member = CampaignMember::factory()->withoutCharacter()->create();

    expect($member->getCharacterCommunity())->toBeNull();
});
it('scopes members with characters', function () {
    $campaign = Campaign::factory()->create();

    CampaignMember::factory()->count(3)->create(['campaign_id' => $campaign->id]);
    CampaignMember::factory()->count(2)->withoutCharacter()->create(['campaign_id' => $campaign->id]);

    $membersWithCharacters = CampaignMember::withCharacters()->get();

    expect($membersWithCharacters)->toHaveCount(3);
    expect($membersWithCharacters->every(fn ($member) => $member->character_id !== null))->toBeTrue();
});
it('scopes members without characters', function () {
    $campaign = Campaign::factory()->create();

    CampaignMember::factory()->count(3)->create(['campaign_id' => $campaign->id]);
    CampaignMember::factory()->count(2)->withoutCharacter()->create(['campaign_id' => $campaign->id]);

    $membersWithoutCharacters = CampaignMember::withoutCharacters()->get();

    expect($membersWithoutCharacters)->toHaveCount(2);
    expect($membersWithoutCharacters->every(fn ($member) => $member->character_id === null))->toBeTrue();
});
it('casts joined at to datetime', function () {
    $timestamp = now()->subDays(5);
    $member = CampaignMember::factory()->joinedAt($timestamp)->create();

    expect($member->joined_at)->toBeInstanceOf(\Carbon\Carbon::class);
    expect($member->joined_at->format('Y-m-d H:i:s'))->toEqual($timestamp->format('Y-m-d H:i:s'));
});
it('handles missing character gracefully', function () {
    // Create member with character, then delete the character
    $character = Character::factory()->create(['name' => 'Test Character']);
    $member = CampaignMember::factory()->create(['character_id' => $character->id]);

    // Delete the character
    $character->delete();

    // Refresh the member to clear loaded relationships
    $member->refresh();

    expect($member->character)->toBeNull();
    expect($member->getDisplayName())->toEqual('Empty Character');
    expect($member->getCharacterClass())->toBeNull();
});
