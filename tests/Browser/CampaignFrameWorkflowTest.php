<?php

declare(strict_types=1);



use Domain\CampaignFrame\Models\CampaignFrame;
use Domain\User\Models\User;
test('authenticated user can access campaign frames index', function () {
    $user = User::factory()->create();
    
    $page = visit('/');
    
    auth()->login($user);\n    $page
            ->visit('/campaign-frames')
            ->assertSee('Campaign Frames')
            ->assertSee('Craft and discover inspiring campaign foundations');
});

test('shows create frame button when no frames exist', function () {
    $user = User::factory()->create();
    
    $page = visit('/');
    
    auth()->login($user);\n    $page
            ->visit('/campaign-frames')
            ->assertSee('No Campaign Frames Yet')
            ->assertSee('Create Your First Frame')
            ->click('a:contains("Create Your First Frame")')
            ->assertPathIs('/campaign-frames/create');
});

test('can create a basic campaign frame', function () {
    $user = User::factory()->create();
    
    $page = visit('/');
    
    auth()->login($user);\n    $page
            ->visit('/campaign-frames/create')
            ->assertSee('Create Campaign Frame')
            ->type('create_form.name', 'Test Fantasy Campaign')
            ->type('create_form.description', 'A test campaign focused on fantasy elements')
            ->select('create_form.complexity_rating', '2') // Moderate
            ->type('create_form.background_overview', 'This is a world of magic and adventure where heroes rise to face ancient evils.')
            ->type('create_form.inciting_incident', 'A dark crystal appears in the town square, corrupting everything around it.')
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

test('can create and make a campaign frame public', function () {
    $user = User::factory()->create();
    
    $page = visit('/');
    
    auth()->login($user);\n    $page
            ->visit('/campaign-frames/create')
            ->type('create_form.name', 'Public Fantasy Campaign')
            ->type('create_form.description', 'A publicly shared fantasy campaign')
            ->check('create_form.is_public') // Make it public
            ->type('create_form.background_overview', 'A shared world for everyone to enjoy.')
            ->press('Create Frame')
            ->waitForLocation('/campaign-frames/*')
            ->assertSee('Public Fantasy Campaign')
            ->assertSee('Public'); // Should show public badge
    });
    
    $frame = CampaignFrame::where('name', 'Public Fantasy Campaign')->first();
    expect($frame->is_public)->toBe(true);
});

test('can edit an existing campaign frame', function () {
    $user = User::factory()->create();
    $frame = CampaignFrame::factory()->create([
        'creator_id' => $user->id,
        'name' => 'Original Frame Name',
        'description' => 'Original description',
    ]);
    
    $page = visit('/');
    
    auth()->login($user);\n    $page
            ->visit("/campaign-frames/{$frame->id}")
            ->assertSee('Original Frame Name')
            ->click('a:contains("Edit")')
            ->assertPathIs("/campaign-frames/{$frame->id}/edit")
            ->assertInputValue('edit_form.name', 'Original Frame Name')
            ->clear('edit_form.name')
            ->type('edit_form.name', 'Updated Frame Name')
            ->clear('edit_form.description')
            ->type('edit_form.description', 'Updated description with new content')
            ->press('Update Frame')
            ->waitForLocation("/campaign-frames/{$frame->id}")
            ->assertSee('Updated Frame Name')
            ->assertSee('Updated description with new content');
    });
    
    $frame->refresh();
    expect($frame->name)->toBe('Updated Frame Name');
    expect($frame->description)->toBe('Updated description with new content');
});

test('can browse public campaign frames', function () {
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
    
    $page = visit('/');
    
    auth()->login($user2);\n    $page
            ->visit('/campaign-frames/browse')
            ->assertSee('Browse Public Campaign Frames')
            ->assertSee('Public Frame 1')
            ->assertSee('Public Frame 2')
            ->assertDontSee('Private Frame')
            ->assertSee('by creator1'); // Should show creator names
});

test('can search public campaign frames', function () {
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
    
    $page = visit('/');
    
    auth()->login($user2);\n    $page
            ->visit('/campaign-frames/browse')
            ->type('search', 'Fantasy')
            ->press('Search')
            ->assertSee('Fantasy Adventure')
            ->assertDontSee('Sci-Fi Campaign')
            ->click('a:contains("Clear")')
            ->assertSee('Fantasy Adventure')
            ->assertSee('Sci-Fi Campaign');
});

test('shows validation errors for empty required fields', function () {
    $user = User::factory()->create();
    
    $page = visit('/');
    
    auth()->login($user);\n    $page
            ->visit('/campaign-frames/create')
            ->press('Create Frame')
            ->assertSee('required');
});

test('prevents non-creators from editing frames', function () {
    $creator = User::factory()->create();
    $other_user = User::factory()->create();
    
    $frame = CampaignFrame::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Test Frame',
    ]);
    
    $page = visit('/');
    
    auth()->login($other_user);\n    $page
            ->visit("/campaign-frames/{$frame->id}")
            ->assertSee('Test Frame')
            ->assertMissing('a:contains("Edit")'); // Should not see edit button
});

test('shows campaign frames on dashboard', function () {
    $user = User::factory()->create();
    
    $page = visit('/');
    
    auth()->login($user);\n    $page
            ->visit('/dashboard')
            ->assertSee('Frames') // Should be in the quick actions  
            ->assertSee('Campaign templates')
            ->click('a[href*="/campaign-frames"]')
            ->assertPathIs('/campaign-frames');
});

test('can view detailed campaign frame information', function () {
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
    
    $page = visit('/');
    
    auth()->login($user);\n    $page
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