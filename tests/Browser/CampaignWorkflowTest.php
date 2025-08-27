<?php

declare(strict_types=1);
use Domain\Campaign\Models\Campaign;
use Domain\Character\Models\Character;
use Domain\User\Models\User;
uses(\Illuminate\Foundation\Testing\DatabaseMigrations::class);

test('user can complete full campaign creation workflow', function () {
    $creator = User::factory()->create([
        'username' => 'gamemaster',
        'email' => 'gm@example.com',
        'password' => bcrypt('password'),
    ]);

    $page = visit('/');
    
    auth()->login($creator);\n    $page
            ->visit('/campaigns')
            ->assertSee('Campaigns')
            ->assertSee('No campaigns yet')
            ->click('@create-campaign-button')
            ->assertPathIs('/campaigns/create')
            ->assertSee('Create Campaign')
            ->type('name', 'The Dragon\'s Lair')
            ->type('description', 'An epic adventure where heroes must face the ancient red dragon Smaug in his mountain lair.')
            ->press('Create Campaign')
            ->assertPathBeginsWith('/campaigns/')
            ->assertSee('Campaign created successfully!')
            ->assertSee('The Dragon\'s Lair')
            ->assertSee('An epic adventure where heroes must face the ancient red dragon');
});

test('campaign creator can share invite link', function () {
    $creator = User::factory()->create(['password' => bcrypt('password')]);
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    $page = visit('/');
    
    auth()->login($creator);\n    $page
            ->visit("/campaigns/{$campaign->campaign_code}")
            ->assertSee($campaign->name)
            ->assertSee('Share Invite')
            ->click('@share-invite-button')
            ->wait(1000) // Wait for copy action
            ->assertSee('Copied!'); // Button should show success state
});

test('user can join campaign via invite link', function () {
    $creator = User::factory()->create();
    $player = User::factory()->create([
        'username' => 'player1',
        'password' => bcrypt('password'),
    ]);
    $campaign = Campaign::factory()->create([
        'name' => 'Campaign to Join',
        'creator_id' => $creator->id,
    ]);
    $character = Character::factory()->create([
        'name' => 'Legolas',
        'user_id' => $player->id,
        'class' => 'Ranger',
        'subclass' => 'Beast Master',
        'ancestry' => 'Elf',
        'community' => 'Wildborne',
    ]);

    $page = visit('/');
    
    auth()->login($player);\n    $page
            ->visit("/join/{$campaign->invite_code}")
            ->assertSee('Join Campaign')
            ->assertSee('Campaign to Join')
            ->assertSee('Choose a Character')
            ->assertSee('Legolas')
            ->assertSee('Ranger / Beast Master')
            ->click("input[value='{$character->id}']")
            ->press('Join Campaign')
            ->assertPathIs("/campaigns/{$campaign->campaign_code}")
            ->assertSee('Successfully joined the campaign!')
            ->assertSee('Legolas')
            ->assertSee('player1');
});

test('user can join campaign without character', function () {
    $creator = User::factory()->create();
    $player = User::factory()->create(['password' => bcrypt('password')]);
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    $page = visit('/');
    
    auth()->login($player);\n    $page
            ->visit("/join/{$campaign->invite_code}")
            ->assertSee('Join Campaign')
            ->assertSee('Empty Character')
            ->click('input[value=""]') // Select empty character option
            ->press('Join Campaign')
            ->assertPathIs("/campaigns/{$campaign->campaign_code}")
            ->assertSee('Successfully joined the campaign!')
            ->assertSee('Empty Character');
});

test('campaign member can leave campaign', function () {
    $creator = User::factory()->create();
    $member = User::factory()->create(['password' => bcrypt('password')]);
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    // Add member to campaign
    $campaign->members()->create([
        'user_id' => $member->id,
        'joined_at' => now(),
    ]);

    $page = visit('/');
    
    auth()->login($member);\n    $page
            ->visit("/campaigns/{$campaign->campaign_code}")
            ->assertSee('Leave Campaign')
            ->press('Leave Campaign')
            ->acceptDialog() // Confirm the leave action
            ->assertPathIs('/campaigns')
            ->assertSee('Successfully left the campaign.');
});

test('campaign dashboard shows created and joined campaigns', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    // Campaigns created by user
    $createdCampaign = Campaign::factory()->create([
        'name' => 'My Created Campaign',
        'creator_id' => $user->id,
    ]);

    // Campaign joined by user
    $joinedCampaign = Campaign::factory()->create(['name' => 'Campaign I Joined']);
    $joinedCampaign->members()->create([
        'user_id' => $user->id,
        'joined_at' => now(),
    ]);

    $page = visit('/');
    
    auth()->login($user);\n    $page
            ->visit('/campaigns')
            ->assertSee('My Campaigns')
            ->assertSee('My Created Campaign')
            ->assertSee('Joined Campaigns') 
            ->assertSee('Campaign I Joined')
            ->assertSee('1 members') // Should show member count
            ->assertSee('0 members'); // Created campaign has no members
});

test('multiple users can join same campaign', function () {
    $creator = User::factory()->create();
    $player1 = User::factory()->create([
        'username' => 'player1',
        'password' => bcrypt('password'),
    ]);
    $player2 = User::factory()->create([
        'username' => 'player2', 
        'password' => bcrypt('password'),
    ]);
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    $this->browse(function (Browser $browser1, Browser $browser2) use ($player1, $player2, $campaign) {
        // Player 1 joins
        $browser1->loginAs($player1)
            ->visit("/join/{$campaign->invite_code}")
            ->click('input[value=""]') // Empty character
            ->press('Join Campaign')
            ->assertSee('Successfully joined the campaign!');

        // Player 2 joins
        $browser2->loginAs($player2)
            ->visit("/join/{$campaign->invite_code}")
            ->click('input[value=""]') // Empty character  
            ->press('Join Campaign')
            ->assertSee('Successfully joined the campaign!')
            ->assertSee('player1') // Should see other member
            ->assertSee('player2'); // Should see themselves
});

test('campaign creation form validates required fields', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    $page = visit('/');
    
    auth()->login($user);\n    $page
            ->visit('/campaigns/create')
            ->press('Create Campaign') // Submit without filling fields
            ->assertPathIs('/campaigns/create') // Should stay on form
            ->assertPresent('.text-red-400'); // Should show validation errors
});

test('user cannot join campaign twice', function () {
    $creator = User::factory()->create();
    $player = User::factory()->create(['password' => bcrypt('password')]);
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    // Add player as member
    $campaign->members()->create([
        'user_id' => $player->id,
        'joined_at' => now(),
    ]);

    $page = visit('/');
    
    auth()->login($player);\n    $page
            ->visit("/join/{$campaign->invite_code}")
            ->assertPathIs("/campaigns/{$campaign->campaign_code}")
            ->assertSee('You are already a member of this campaign.');
});

test('campaign navigation works correctly', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    $page = visit('/');
    
    auth()->login($user);\n    $page
            ->visit('/dashboard')
            ->click('@campaigns-link') // Click campaigns in dashboard
            ->assertPathIs('/campaigns')
            ->click('@create-campaign-button')
            ->assertPathIs('/campaigns/create')
            ->click('@cancel-button') // Cancel creation
            ->assertPathIs('/campaigns')
            ->click("@campaign-{$campaign->id}") // Click on specific campaign
            ->assertPathIs("/campaigns/{$campaign->campaign_code}")
            ->click('@back-to-campaigns')
            ->assertPathIs('/campaigns');
});

test('responsive design works on mobile', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    $page = visit('/');
    
    $page->resize(375, 667) // iPhone 6/7/8 size
            ->loginAs($user)
            ->visit('/campaigns')
            ->assertSee('Campaigns')
            ->click('@create-campaign-button')
            ->assertPathIs('/campaigns/create')
            ->type('name', 'Mobile Campaign')
            ->type('description', 'Testing mobile responsiveness')
            ->press('Create Campaign')
            ->assertSee('Campaign created successfully!')
            ->visit("/campaigns/{$campaign->campaign_code}")
            ->assertSee($campaign->name);
});