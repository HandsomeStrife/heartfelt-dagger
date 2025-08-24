<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\CampaignFrame\Models\CampaignFrame;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

uses(DuskTestCase::class, DatabaseMigrations::class);

#[Test]
it('shows available campaign frames when creating a campaign', function () {
    $user = User::factory()->create();
    
    // Create some frames
    $public_frame = CampaignFrame::factory()->create([
        'name' => 'Public Adventure Frame',
        'is_public' => true,
    ]);
    
    $user_frame = CampaignFrame::factory()->create([
        'creator_id' => $user->id,
        'name' => 'My Private Frame',
        'is_public' => false,
    ]);
    
    $other_private_frame = CampaignFrame::factory()->create([
        'name' => 'Other Private Frame',
        'is_public' => false,
    ]);
    
    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/campaigns/create')
            ->assertSee('Public Adventure Frame') // Should see public frames
            ->assertSee('My Private Frame')      // Should see own private frames
            ->assertDontSee('Other Private Frame'); // Should not see others' private frames
    });
});

#[Test]
it('can create a campaign with a selected campaign frame', function () {
    $user = User::factory()->create();
    
    $frame = CampaignFrame::factory()->create([
        'creator_id' => $user->id,
        'name' => 'Test Campaign Frame',
        'is_public' => false,
    ]);
    
    $this->browse(function (Browser $browser) use ($user, $frame) {
        $browser->loginAs($user)
            ->visit('/campaigns/create')
            ->type('name', 'Test Campaign with Frame')
            ->type('description', 'A campaign using a specific frame')
            ->select('campaign_frame_id', $frame->id)
            ->press('Create Campaign')
            ->waitForLocation('/campaigns/*')
            ->assertSee('Test Campaign with Frame');
    });
    
    // Verify the campaign was created with the frame
    $campaign = Campaign::where('name', 'Test Campaign with Frame')->first();
    expect($campaign)->not->toBeNull();
    expect($campaign->campaign_frame_id)->toBe($frame->id);
});

#[Test]
it('can create a campaign without selecting a frame', function () {
    $user = User::factory()->create();
    
    // Create a frame to ensure options are available but not selected
    CampaignFrame::factory()->create([
        'creator_id' => $user->id,
        'name' => 'Available Frame',
    ]);
    
    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/campaigns/create')
            ->type('name', 'Frameless Campaign')
            ->type('description', 'A campaign without a specific frame')
            // Don't select a campaign_frame_id
            ->press('Create Campaign')
            ->waitForLocation('/campaigns/*')
            ->assertSee('Frameless Campaign');
    });
    
    // Verify the campaign was created without a frame
    $campaign = Campaign::where('name', 'Frameless Campaign')->first();
    expect($campaign)->not->toBeNull();
    expect($campaign->campaign_frame_id)->toBeNull();
});

#[Test] 
it('shows frame information in campaign details when frame is selected', function () {
    $user = User::factory()->create();
    
    $frame = CampaignFrame::factory()->create([
        'creator_id' => $user->id,
        'name' => 'Epic Fantasy Frame',
        'description' => 'A frame for epic fantasy adventures',
    ]);
    
    $campaign = Campaign::factory()->create([
        'creator_id' => $user->id,
        'name' => 'Epic Fantasy Campaign',
        'campaign_frame_id' => $frame->id,
    ]);
    
    $this->browse(function (Browser $browser) use ($user, $campaign) {
        $browser->loginAs($user)
            ->visit("/campaigns/{$campaign->campaign_code}")
            ->assertSee('Epic Fantasy Campaign')
            ->assertSee('Epic Fantasy Frame')
            ->assertSee('A frame for epic fantasy adventures');
    });
});

#[Test]
it('prevents deletion of campaign frames that are in use', function () {
    $user = User::factory()->create();
    
    $frame = CampaignFrame::factory()->create([
        'creator_id' => $user->id,
        'name' => 'Frame in Use',
    ]);
    
    // Create a campaign using this frame
    Campaign::factory()->create([
        'creator_id' => $user->id,
        'name' => 'Campaign Using Frame',
        'campaign_frame_id' => $frame->id,
    ]);
    
    $this->browse(function (Browser $browser) use ($user, $frame) {
        $browser->loginAs($user)
            ->visit("/campaign-frames/{$frame->id}")
            ->assertSee('Frame in Use')
            ->assertSee('Used in 1 campaign') // Should show usage count
            ->press('@delete-frame-button')
            ->waitForText('Cannot delete campaign frame that is being used by active campaigns');
    });
    
    // Verify frame still exists
    $frame->refresh();
    expect($frame->exists)->toBe(true);
});

#[Test]
it('allows deletion of unused campaign frames', function () {
    $user = User::factory()->create();
    
    $frame = CampaignFrame::factory()->create([
        'creator_id' => $user->id,
        'name' => 'Unused Frame',
    ]);
    
    $this->browse(function (Browser $browser) use ($user, $frame) {
        $browser->loginAs($user)
            ->visit("/campaign-frames/{$frame->id}")
            ->assertSee('Unused Frame')
            ->assertDontSee('Used in') // Should not show usage count
            ->press('@delete-frame-button')
            ->acceptDialog()
            ->waitForLocation('/campaign-frames')
            ->assertSee('Campaign frame deleted successfully');
    });
    
    // Verify frame was deleted
    expect(CampaignFrame::find($frame->id))->toBeNull();
});

#[Test]
it('displays complexity ratings correctly across the interface', function () {
    $user = User::factory()->create();
    
    $frames = [
        CampaignFrame::factory()->create([
            'creator_id' => $user->id,
            'name' => 'Simple Frame',
            'complexity_rating' => 1,
            'is_public' => true,
        ]),
        CampaignFrame::factory()->create([
            'creator_id' => $user->id,
            'name' => 'Moderate Frame', 
            'complexity_rating' => 2,
            'is_public' => true,
        ]),
        CampaignFrame::factory()->create([
            'creator_id' => $user->id,
            'name' => 'Complex Frame',
            'complexity_rating' => 3,
            'is_public' => true,
        ]),
        CampaignFrame::factory()->create([
            'creator_id' => $user->id,
            'name' => 'Very Complex Frame',
            'complexity_rating' => 4,
            'is_public' => true,
        ]),
    ];
    
    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/campaign-frames')
            ->assertSee('Simple')
            ->assertSee('Moderate')
            ->assertSee('Complex')
            ->assertSee('Very Complex');
    });
    
    // Check individual frame pages
    $this->browse(function (Browser $browser) use ($user, $frames) {
        foreach ($frames as $frame) {
            $expected_complexity = ['', 'Simple', 'Moderate', 'Complex', 'Very Complex'][$frame->complexity_rating];
            
            $browser->visit("/campaign-frames/{$frame->id}")
                ->assertSee($frame->name)
                ->assertSee($expected_complexity . ' Complexity');
        }
    });
});
