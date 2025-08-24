<?php

declare(strict_types=1);

use Domain\CampaignFrame\Models\CampaignFrame;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

uses(DuskTestCase::class, DatabaseMigrations::class);

#[Test]
it('allows authenticated user to access campaign frames index', function () {
    $user = User::factory()->create();
    
    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/campaign-frames')
            ->assertSee('Campaign Frames')
            ->assertSee('Craft and discover inspiring campaign foundations');
    });
});

#[Test]
it('shows create frame button when no frames exist', function () {
    $user = User::factory()->create();
    
    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/campaign-frames')
            ->assertSee('No Campaign Frames Yet')
            ->assertSee('Create Your First Frame')
            ->click('@create-first-frame-button')
            ->assertPathIs('/campaign-frames/create');
    });
});

#[Test]
it('can create a basic campaign frame', function () {
    $user = User::factory()->create();
    
    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/campaign-frames/create')
            ->assertSee('Create Campaign Frame')
            ->type('name', 'Test Fantasy Campaign')
            ->type('description', 'A test campaign focused on fantasy elements')
            ->select('complexity_rating', '2') // Moderate
            ->type('background_overview', 'This is a world of magic and adventure where heroes rise to face ancient evils.')
            ->type('inciting_incident', 'A dark crystal appears in the town square, corrupting everything around it.')
            ->press('Create Frame')
            ->waitForLocation('/campaign-frames/*')
            ->assertSee('Test Fantasy Campaign')
            ->assertSee('A test campaign focused on fantasy elements');
    });
    
    // Verify frame was saved to database
    $frame = CampaignFrame::where('name', 'Test Fantasy Campaign')->first();
    expect($frame)->not->toBeNull();
    expect($frame->creator_id)->toBe($user->id);
    expect($frame->complexity_rating)->toBe(2);
    expect($frame->is_public)->toBe(false); // Default private
});

#[Test]
it('can create and make a campaign frame public', function () {
    $user = User::factory()->create();
    
    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/campaign-frames/create')
            ->type('name', 'Public Fantasy Campaign')
            ->type('description', 'A publicly shared fantasy campaign')
            ->check('is_public') // Make it public
            ->type('background_overview', 'A shared world for everyone to enjoy.')
            ->press('Create Frame')
            ->waitForLocation('/campaign-frames/*')
            ->assertSee('Public Fantasy Campaign')
            ->assertSee('Public'); // Should show public badge
    });
    
    $frame = CampaignFrame::where('name', 'Public Fantasy Campaign')->first();
    expect($frame->is_public)->toBe(true);
});

#[Test]
it('can edit an existing campaign frame', function () {
    $user = User::factory()->create();
    $frame = CampaignFrame::factory()->create([
        'creator_id' => $user->id,
        'name' => 'Original Frame Name',
        'description' => 'Original description',
    ]);
    
    $this->browse(function (Browser $browser) use ($user, $frame) {
        $browser->loginAs($user)
            ->visit("/campaign-frames/{$frame->id}")
            ->assertSee('Original Frame Name')
            ->click('@edit-frame-button')
            ->assertPathIs("/campaign-frames/{$frame->id}/edit")
            ->assertInputValue('name', 'Original Frame Name')
            ->clear('name')
            ->type('name', 'Updated Frame Name')
            ->clear('description')
            ->type('description', 'Updated description with new content')
            ->press('Update Frame')
            ->waitForLocation("/campaign-frames/{$frame->id}")
            ->assertSee('Updated Frame Name')
            ->assertSee('Updated description with new content');
    });
    
    $frame->refresh();
    expect($frame->name)->toBe('Updated Frame Name');
    expect($frame->description)->toBe('Updated description with new content');
});

#[Test]
it('can browse public campaign frames', function () {
    $user1 = User::factory()->create(['username' => 'creator1']);
    $user2 = User::factory()->create(['username' => 'browser2']);
    
    // Create public and private frames
    CampaignFrame::factory()->create([
        'creator_id' => $user1->id,
        'name' => 'Public Frame 1',
        'description' => 'This is a public frame',
        'is_public' => true,
    ]);
    
    CampaignFrame::factory()->create([
        'creator_id' => $user1->id,
        'name' => 'Private Frame',
        'description' => 'This is private',
        'is_public' => false,
    ]);
    
    CampaignFrame::factory()->create([
        'creator_id' => $user1->id,
        'name' => 'Public Frame 2',
        'description' => 'Another public frame',
        'is_public' => true,
    ]);
    
    $this->browse(function (Browser $browser) use ($user2) {
        $browser->loginAs($user2)
            ->visit('/campaign-frames/browse')
            ->assertSee('Browse Public Campaign Frames')
            ->assertSee('Public Frame 1')
            ->assertSee('Public Frame 2')
            ->assertDontSee('Private Frame')
            ->assertSee('by creator1'); // Should show creator names
    });
});

#[Test]
it('can search public campaign frames', function () {
    $user1 = User::factory()->create(['username' => 'creator1']);
    $user2 = User::factory()->create(['username' => 'searcher']);
    
    CampaignFrame::factory()->create([
        'creator_id' => $user1->id,
        'name' => 'Fantasy Adventure',
        'description' => 'A magical quest in a fantasy world',
        'is_public' => true,
    ]);
    
    CampaignFrame::factory()->create([
        'creator_id' => $user1->id,
        'name' => 'Sci-Fi Campaign',
        'description' => 'Space exploration and aliens',
        'is_public' => true,
    ]);
    
    $this->browse(function (Browser $browser) use ($user2) {
        $browser->loginAs($user2)
            ->visit('/campaign-frames/browse')
            ->type('search', 'Fantasy')
            ->press('Search')
            ->assertSee('Fantasy Adventure')
            ->assertDontSee('Sci-Fi Campaign')
            ->click('@clear-search-button')
            ->assertSee('Fantasy Adventure')
            ->assertSee('Sci-Fi Campaign');
    });
});

#[Test]
it('shows validation errors for empty required fields', function () {
    $user = User::factory()->create();
    
    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/campaign-frames/create')
            ->press('Create Frame')
            ->waitForText('The name field is required')
            ->waitForText('The description field is required');
    });
});

#[Test]
it('prevents non-creators from editing frames', function () {
    $creator = User::factory()->create();
    $other_user = User::factory()->create();
    
    $frame = CampaignFrame::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Test Frame',
    ]);
    
    $this->browse(function (Browser $browser) use ($other_user, $frame) {
        $browser->loginAs($other_user)
            ->visit("/campaign-frames/{$frame->id}")
            ->assertSee('Test Frame')
            ->assertDontSee('Edit'); // Should not see edit button
    });
});

#[Test]
it('shows campaign frames on dashboard', function () {
    $user = User::factory()->create();
    
    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/dashboard')
            ->assertSee('Campaign Frames') // Should be in the quick actions  
            ->assertSee('Frames')
            ->assertSee('Campaign templates')
            ->click('@campaign-frames-link')
            ->assertPathIs('/campaign-frames');
    });
});

#[Test]
it('can view detailed campaign frame information', function () {
    $user = User::factory()->create(['username' => 'testcreator']);
    
    $frame = CampaignFrame::factory()->create([
        'creator_id' => $user->id,
        'name' => 'Detailed Test Frame',
        'description' => 'A comprehensive test frame',
        'complexity_rating' => 3,
        'is_public' => true,
        'background_overview' => 'This is the detailed background of the campaign world.',
        'inciting_incident' => 'A mysterious portal opens in the village square.',
        'pitch' => ['Epic fantasy adventure', 'Political intrigue'],
        'tone_and_themes' => ['Dark', 'Mystery', 'Heroic'],
        'principles' => ['Player choice matters', 'Story over combat'],
        'setting_distinctions' => ['Magic is rare', 'Gods are distant'],
    ]);
    
    $this->browse(function (Browser $browser) use ($user, $frame) {
        $browser->loginAs($user)
            ->visit("/campaign-frames/{$frame->id}")
            ->assertSee('Detailed Test Frame')
            ->assertSee('A comprehensive test frame')
            ->assertSee('Complex') // Complexity rating
            ->assertSee('Public') // Public badge
            ->assertSee('by testcreator')
            ->assertSee('This is the detailed background')
            ->assertSee('A mysterious portal opens')
            ->assertSee('Epic fantasy adventure')
            ->assertSee('Political intrigue')
            ->assertSee('Dark')
            ->assertSee('Mystery')
            ->assertSee('Heroic')
            ->assertSee('Player choice matters')
            ->assertSee('Story over combat')
            ->assertSee('Magic is rare')
            ->assertSee('Gods are distant');
    });
});
