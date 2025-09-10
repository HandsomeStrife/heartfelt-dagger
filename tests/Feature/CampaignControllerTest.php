<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\CampaignFrame\Models\CampaignFrame;
use Domain\User\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

describe('CampaignController', function () {

    describe('index', function () {
        it('displays campaigns dashboard for authenticated user', function () {
            $user = User::factory()->create();
            $createdCampaign = Campaign::factory()->create(['creator_id' => $user->id]);
            $joinedCampaign = Campaign::factory()->create();
            $joinedCampaign->members()->create(['user_id' => $user->id]);

            actingAs($user)
                ->get(route('campaigns.index'))
                ->assertOk()
                ->assertViewIs('campaigns.index')
                ->assertViewHas('created_campaigns')
                ->assertViewHas('joined_campaigns');
        });

        it('requires authentication', function () {
            get(route('campaigns.index'))
                ->assertRedirect(route('login'));
        });
    });

    describe('create', function () {
        it('displays create campaign form with available frames', function () {
            $user = User::factory()->create();

            actingAs($user)
                ->get(route('campaigns.create'))
                ->assertOk()
                ->assertViewIs('campaigns.create')
                ->assertViewHas('available_frames');
        });

        it('requires authentication', function () {
            get(route('campaigns.create'))
                ->assertRedirect(route('login'));
        });
    });

    describe('store', function () {
        it('creates campaign with name only (description optional)', function () {
            $user = User::factory()->create();

            actingAs($user)
                ->post(route('campaigns.store'), [
                    'name' => 'Test Campaign',
                ])
                ->assertRedirect()
                ->assertSessionHas('success', 'Campaign created successfully!');

            expect(Campaign::where('name', 'Test Campaign')->exists())->toBeTrue();

            $campaign = Campaign::where('name', 'Test Campaign')->first();
            expect($campaign->description)->toBeNull();
            expect($campaign->creator_id)->toBe($user->id);
        });

        it('creates campaign with name and description', function () {
            $user = User::factory()->create();

            actingAs($user)
                ->post(route('campaigns.store'), [
                    'name' => 'Test Campaign',
                    'description' => 'Test Description',
                ])
                ->assertRedirect()
                ->assertSessionHas('success', 'Campaign created successfully!');

            $campaign = Campaign::where('name', 'Test Campaign')->first();
            expect($campaign->description)->toBe('Test Description');
            expect($campaign->creator_id)->toBe($user->id);
        });

        it('creates campaign with optional campaign frame', function () {
            $user = User::factory()->create();

            // Create a simple campaign frame manually without factory
            $frame = CampaignFrame::create([
                'name' => 'Test Frame',
                'description' => 'Test frame description',
                'complexity_rating' => 1,
                'is_public' => true,
                'creator_id' => $user->id,
            ]);

            actingAs($user)
                ->post(route('campaigns.store'), [
                    'name' => 'Test Campaign',
                    'description' => 'Test Description',
                    'campaign_frame_id' => $frame->id,
                ])
                ->assertRedirect()
                ->assertSessionHas('success', 'Campaign created successfully!');

            $campaign = Campaign::where('name', 'Test Campaign')->first();
            expect($campaign->campaign_frame_id)->toBe($frame->id);
        });

        it('creates campaign without campaign frame', function () {
            $user = User::factory()->create();

            actingAs($user)
                ->post(route('campaigns.store'), [
                    'name' => 'Test Campaign',
                    'description' => 'Test Description',
                ])
                ->assertRedirect()
                ->assertSessionHas('success', 'Campaign created successfully!');

            $campaign = Campaign::where('name', 'Test Campaign')->first();
            expect($campaign->campaign_frame_id)->toBeNull();
        });

        it('validates required name field', function () {
            $user = User::factory()->create();

            actingAs($user)
                ->post(route('campaigns.store'), [
                    'description' => 'Test Description',
                ])
                ->assertSessionHasErrors(['name']);
        });

        it('validates name max length', function () {
            $user = User::factory()->create();

            actingAs($user)
                ->post(route('campaigns.store'), [
                    'name' => str_repeat('a', 101), // Exceeds 100 character limit
                ])
                ->assertSessionHasErrors(['name']);
        });

        it('validates description max length', function () {
            $user = User::factory()->create();

            actingAs($user)
                ->post(route('campaigns.store'), [
                    'name' => 'Test Campaign',
                    'description' => str_repeat('a', 1001), // Exceeds 1000 character limit
                ])
                ->assertSessionHasErrors(['description']);
        });

        it('validates campaign frame exists', function () {
            $user = User::factory()->create();

            actingAs($user)
                ->post(route('campaigns.store'), [
                    'name' => 'Test Campaign',
                    'campaign_frame_id' => 99999, // Non-existent frame ID
                ])
                ->assertSessionHasErrors(['campaign_frame_id']);
        });

        it('accepts null campaign frame', function () {
            $user = User::factory()->create();

            actingAs($user)
                ->post(route('campaigns.store'), [
                    'name' => 'Test Campaign',
                    'campaign_frame_id' => null,
                ])
                ->assertRedirect()
                ->assertSessionHas('success');
        });

        it('accepts empty string campaign frame', function () {
            $user = User::factory()->create();

            actingAs($user)
                ->post(route('campaigns.store'), [
                    'name' => 'Test Campaign',
                    'campaign_frame_id' => '',
                ])
                ->assertRedirect()
                ->assertSessionHas('success');
        });

        it('requires authentication', function () {
            post(route('campaigns.store'), [
                'name' => 'Test Campaign',
            ])
                ->assertRedirect(route('login'));
        });

        it('redirects to campaign show page after creation', function () {
            $user = User::factory()->create();

            $response = actingAs($user)
                ->post(route('campaigns.store'), [
                    'name' => 'Test Campaign',
                ]);

            $campaign = Campaign::where('name', 'Test Campaign')->first();
            $response->assertRedirect(route('campaigns.show', ['campaign' => $campaign->campaign_code]));
        });
    });

    describe('show', function () {
        it('displays campaign details for creator', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

            actingAs($user)
                ->get(route('campaigns.show', ['campaign' => $campaign->campaign_code]))
                ->assertOk()
                ->assertViewIs('campaigns.show')
                ->assertViewHas('campaign')
                ->assertViewHas('members')
                ->assertViewHas('campaign_rooms')
                ->assertViewHas('user_is_creator', true);
        });

        it('displays campaign details for member', function () {
            $creator = User::factory()->create();
            $member = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);
            $campaign->members()->create(['user_id' => $member->id]);

            actingAs($member)
                ->get(route('campaigns.show', ['campaign' => $campaign->campaign_code]))
                ->assertOk()
                ->assertViewIs('campaigns.show')
                ->assertViewHas('user_is_member', true)
                ->assertViewHas('user_is_creator', false);
        });

        it('displays campaign details for non-member', function () {
            $creator = User::factory()->create();
            $visitor = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

            actingAs($visitor)
                ->get(route('campaigns.show', ['campaign' => $campaign->campaign_code]))
                ->assertOk()
                ->assertViewIs('campaigns.show')
                ->assertViewHas('user_is_member', false)
                ->assertViewHas('user_is_creator', false);
        });

        it('requires authentication', function () {
            $campaign = Campaign::factory()->create();

            get(route('campaigns.show', ['campaign' => $campaign->campaign_code]))
                ->assertRedirect(route('login'));
        });

        it('returns 404 for non-existent campaign', function () {
            $user = User::factory()->create();

            actingAs($user)
                ->get(route('campaigns.show', ['campaign' => 'INVALID']))
                ->assertNotFound();
        });
    });

    describe('showJoin', function () {
        it('displays join form for valid invite code', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create();

            actingAs($user)
                ->get(route('campaigns.invite', ['invite_code' => $campaign->invite_code]))
                ->assertOk()
                ->assertViewIs('campaigns.join')
                ->assertViewHas('campaign')
                ->assertViewHas('characters');
        });

        it('redirects existing member to campaign', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create();
            $campaign->members()->create(['user_id' => $user->id]);

            actingAs($user)
                ->get(route('campaigns.invite', ['invite_code' => $campaign->invite_code]))
                ->assertRedirect(route('campaigns.show', ['campaign' => $campaign]))
                ->assertSessionHas('info', 'You are already a member of this campaign.');
        });

        it('returns 404 for invalid invite code', function () {
            $user = User::factory()->create();

            actingAs($user)
                ->get(route('campaigns.invite', ['invite_code' => 'INVALID']))
                ->assertNotFound();
        });

        it('requires authentication', function () {
            $campaign = Campaign::factory()->create();

            get(route('campaigns.invite', ['invite_code' => $campaign->invite_code]))
                ->assertRedirect(route('login'));
        });
    });

    describe('joinByCode', function () {
        it('joins campaign with valid invite code', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create();

            actingAs($user)
                ->post(route('campaigns.join'), [
                    'invite_code' => $campaign->invite_code,
                ])
                ->assertRedirect(route('campaigns.show', $campaign->campaign_code))
                ->assertSessionHas('success', 'Successfully joined the campaign!');

            expect($campaign->hasMember($user))->toBeTrue();
        });

        it('redirects existing member to campaign', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create();
            $campaign->members()->create(['user_id' => $user->id]);

            actingAs($user)
                ->post(route('campaigns.join'), [
                    'invite_code' => $campaign->invite_code,
                ])
                ->assertRedirect(route('campaigns.show', $campaign->campaign_code))
                ->assertSessionHas('info', 'You are already a member of this campaign.');
        });

        it('validates invite code format', function () {
            $user = User::factory()->create();

            actingAs($user)
                ->post(route('campaigns.join'), [
                    'invite_code' => 'INVALID',
                ])
                ->assertSessionHasErrors(['invite_code']);
        });

        it('handles invalid invite code', function () {
            $user = User::factory()->create();

            actingAs($user)
                ->post(route('campaigns.join'), [
                    'invite_code' => 'INVALID1',
                ])
                ->assertRedirect(route('campaigns.index'))
                ->assertSessionHasErrors(['invite_code' => 'Invalid invite code. Please check the code and try again.']);
        });

        it('requires authentication', function () {
            $campaign = Campaign::factory()->create();

            post(route('campaigns.join'), [
                'invite_code' => $campaign->invite_code,
            ])
                ->assertRedirect(route('login'));
        });
    });

    describe('join', function () {
        it('joins campaign without character', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create();

            actingAs($user)
                ->post(route('campaigns.join_campaign', ['campaign' => $campaign]), [])
                ->assertRedirect(route('campaigns.show', ['campaign' => $campaign]))
                ->assertSessionHas('success', 'Successfully joined the campaign!');

            expect($campaign->hasMember($user))->toBeTrue();
        });

        it('joins campaign with character', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create();
            $character = \Domain\Character\Models\Character::factory()->create(['user_id' => $user->id]);

            actingAs($user)
                ->post(route('campaigns.join_campaign', ['campaign' => $campaign]), [
                    'character_id' => $character->id,
                ])
                ->assertRedirect(route('campaigns.show', ['campaign' => $campaign]))
                ->assertSessionHas('success', 'Successfully joined the campaign!');

            expect($campaign->hasMember($user))->toBeTrue();
        });

        it('validates character belongs to user', function () {
            $user = User::factory()->create();
            $otherUser = User::factory()->create();
            $campaign = Campaign::factory()->create();
            $character = \Domain\Character\Models\Character::factory()->create(['user_id' => $otherUser->id]);

            actingAs($user)
                ->post(route('campaigns.join_campaign', ['campaign' => $campaign]), [
                    'character_id' => $character->id,
                ])
                ->assertNotFound();
        });

        it('validates character exists', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create();

            actingAs($user)
                ->post(route('campaigns.join_campaign', ['campaign' => $campaign]), [
                    'character_id' => 99999,
                ])
                ->assertSessionHasErrors(['character_id']);
        });

        it('requires authentication', function () {
            $campaign = Campaign::factory()->create();

            post(route('campaigns.join_campaign', ['campaign' => $campaign]), [])
                ->assertRedirect(route('login'));
        });
    });

    describe('leave', function () {
        it('leaves campaign successfully', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create();
            $campaign->members()->create(['user_id' => $user->id]);

            expect($campaign->hasMember($user))->toBeTrue();

            actingAs($user)
                ->delete(route('campaigns.leave', ['campaign' => $campaign]))
                ->assertRedirect(route('campaigns.index'))
                ->assertSessionHas('success', 'Successfully left the campaign.');

            expect($campaign->fresh()->hasMember($user))->toBeFalse();
        });

        it('requires authentication', function () {
            $campaign = Campaign::factory()->create();

            delete(route('campaigns.leave', ['campaign' => $campaign]))
                ->assertRedirect(route('login'));
        });

        it('returns 404 for non-existent campaign', function () {
            $user = User::factory()->create();

            actingAs($user)
                ->delete('/campaigns/99999/leave')
                ->assertNotFound();
        });
    });
});
