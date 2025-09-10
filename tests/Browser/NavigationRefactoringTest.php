<?php

declare(strict_types=1);

use Domain\User\Models\User;
use Domain\Campaign\Models\Campaign;
use Domain\CampaignFrame\Models\CampaignFrame;
use Domain\Room\Models\Room;

use function Pest\Laravel\actingAs;

test('dashboard page loads correctly', function () {
    $user = User::factory()->create();

    actingAs($user);
    
    visit('/dashboard')
        ->assertSee('Welcome')
        ->assertSee('Characters')
        ->assertSee('Campaigns') 
        ->assertSee('Rooms')
        ->assertNoJavaScriptErrors();
})->group('navigation', 'browser');

test('campaigns pages load correctly', function () {
    $user = User::factory()->create();
    actingAs($user);

    // Index page
    visit('/campaigns')
        ->assertSee('Campaigns')
        ->assertSee('Join Campaign')
        ->assertSee('Create Campaign')
        ->assertPresent('[title="Back to dashboard"]')
        ->assertNoJavaScriptErrors();

    // Create page
    visit('/campaigns/create')
        ->assertSee('Create Campaign')
        ->assertPresent('[title="Back to campaigns"]')
        ->assertNoJavaScriptErrors();
})->group('navigation', 'browser');

test('campaign frames pages load correctly', function () {
    $user = User::factory()->create();
    actingAs($user);

    // Index page
    visit('/campaign-frames')
        ->assertSee('Campaign Frames')
        ->assertSee('Create Frame')
        ->assertSee('Browse Public')
        ->assertPresent('[title="Back to dashboard"]')
        ->assertNoJavaScriptErrors();

    // Create page
    visit('/campaign-frames/create')
        ->assertSee('Create Campaign Frame')
        ->assertPresent('[title="Back to campaign frames"]')
        ->assertNoJavaScriptErrors();

    // Browse page
    visit('/campaign-frames/browse')
        ->assertSee('Browse Public Campaign Frames')
        ->assertPresent('[title="Back to my frames"]')
        ->assertSee('Search')
        ->assertNoJavaScriptErrors();
})->group('navigation', 'browser');

test('rooms pages load correctly', function () {
    $user = User::factory()->create();
    actingAs($user);

    // Index page
    visit('/rooms')
        ->assertSee('Rooms')
        ->assertSee('Create Room')
        ->assertPresent('[title="Back to dashboard"]')
        ->assertNoJavaScriptErrors();

    // Create page
    visit('/rooms/create')
        ->assertSee('Create New Room')
        ->assertPresent('[title="Back to rooms"]')
        ->assertNoJavaScriptErrors();

    // Skip room show page test for now due to factory complexity
})->group('navigation', 'browser');

test('storage and service pages load correctly', function () {
    $user = User::factory()->create();
    actingAs($user);

    // Storage accounts
    visit('/storage-accounts')
        ->assertSee('Storage')
        ->assertSee('Services')
        ->assertPresent('[title="Back to dashboard"]')
        ->assertSee('Cloud Storage')
        ->assertSee('Transcription')
        ->assertNoJavaScriptErrors();

    // Wasabi setup
    visit('/wasabi/connect')
        ->assertSee('Connect Wasabi Account')
        ->assertPresent('[title="Back to storage accounts"]')
        ->assertNoJavaScriptErrors();

    // AssemblyAI setup
    visit('/assemblyai/connect')
        ->assertSee('Connect AssemblyAI Account')
        ->assertPresent('[title="Back to storage accounts"]')
        ->assertNoJavaScriptErrors();
})->group('navigation', 'browser');

test('utility pages load correctly', function () {
    $user = User::factory()->create();
    actingAs($user);

    // Video library
    visit('/video-library')
        ->assertSee('Video Library')
        ->assertPresent('[title="Back to dashboard"]')
        ->assertSee('List')
        ->assertSee('Grid')
        ->assertNoJavaScriptErrors();

    // Range check (public page)
    visit('/range-check')
        ->assertSee('DaggerHeart Range Viewer')
        ->assertPresent('[title="Back to dashboard"]')
        ->assertNoJavaScriptErrors();

    // Actual plays (public page)
    visit('/actual-plays')
        ->assertSee('Actual Plays')
        ->assertPresent('[title="Back to dashboard"]')
        ->assertSee('DaggerHeart')
        ->assertNoJavaScriptErrors();

    // Skip TipTap test for now due to layout issues
})->group('navigation', 'browser');

test('legal pages load correctly', function () {
    // Terms of service
    visit('/terms-of-service')
        ->assertSee('Terms of Service')
        ->assertPresent('[title="Back to home"]')
        ->assertSee('Legal Documents')
        ->assertSee('Acceptance of Terms')
        ->assertNoJavaScriptErrors();

    // Privacy policy
    visit('/privacy-policy')
        ->assertSee('Privacy Policy')
        ->assertPresent('[title="Back to home"]')
        ->assertSee('Legal Documents')
        ->assertSee('Introduction')
        ->assertNoJavaScriptErrors();
})->group('navigation', 'browser');

test('navigation consistency across pages', function () {
    $user = User::factory()->create();
    actingAs($user);
    
    $pageTests = [
        ['/dashboard', 'Welcome', null],
        ['/campaigns', 'Campaigns', '[title="Back to dashboard"]'],
        ['/campaigns/create', 'Create Campaign', '[title="Back to campaigns"]'],
        ['/campaign-frames', 'Campaign Frames', '[title="Back to dashboard"]'],
        ['/campaign-frames/create', 'Create Campaign Frame', '[title="Back to campaign frames"]'],
        ['/rooms', 'Rooms', '[title="Back to dashboard"]'],
        ['/rooms/create', 'Create New Room', '[title="Back to rooms"]'],
        ['/storage-accounts', 'Storage', '[title="Back to dashboard"]'],
        ['/video-library', 'Video Library', '[title="Back to dashboard"]'],
        ['/range-check', 'DaggerHeart Range Viewer', '[title="Back to dashboard"]'],
        ['/actual-plays', 'Actual Plays', '[title="Back to dashboard"]'],
    ];

    foreach ($pageTests as [$url, $title, $backButton]) {
        $browser = visit($url);
        
        // Authentication is already set above with actingAs($user)
        
        $browser->assertSee($title);
        
        if ($backButton) {
            $browser->assertPresent($backButton);
        }
        
        $browser->assertNoJavaScriptErrors();
    }
})->group('navigation', 'browser');

test('key pages load without JavaScript errors', function () {
    $user = User::factory()->create();
    actingAs($user);
    
    // Test key navigation pages for JavaScript errors
    visit('/campaigns/create')
        ->assertSee('Create Campaign')
        ->assertNoJavaScriptErrors();
        
    visit('/rooms/create')
        ->assertSee('Create New Room')
        ->assertNoJavaScriptErrors();
        
    visit('/storage-accounts')
        ->assertSee('Storage')
        ->assertNoJavaScriptErrors();
})->group('navigation', 'browser');