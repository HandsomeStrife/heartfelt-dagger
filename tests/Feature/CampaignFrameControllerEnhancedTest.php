<?php

declare(strict_types=1);

use Domain\CampaignFrame\Models\CampaignFrame;
use Domain\User\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;

test('can create campaign frame via controller with enhanced fields', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->post('/campaign-frames', [
        'name' => 'Controller Test Frame',
        'description' => 'Testing enhanced creation via controller',
        'complexity_rating' => 3,
        'is_public' => true,
        'pitch' => [
            'An epic adventure awaits',
            'Heroes must unite to save the realm',
        ],
        'touchstones' => [
            'Game of Thrones',
            'Final Fantasy',
            'The Chronicles of Narnia',
        ],
        'tone' => [
            'Epic',
            'Dark',
            'Heroic',
        ],
        'themes' => [
            'Power corrupts',
            'Friendship conquers all',
            'Sacrifice for the greater good',
        ],
        'player_principles' => [
            'Always stay true to your character\'s beliefs',
            'Embrace consequences of your actions',
        ],
        'gm_principles' => [
            'Show don\'t tell',
            'Make every choice matter',
            'Build tension through uncertainty',
        ],
        'community_guidance' => [
            'Highborne communities wield political power',
            'Wildborne live in harmony with nature',
        ],
        'ancestry_guidance' => [
            'Elves have long memories and longer grudges',
            'Dwarves value craftsmanship above all else',
        ],
        'class_guidance' => [
            'Warriors often struggle with the morality of violence',
            'Wizards must deal with the temptation of forbidden knowledge',
        ],
        'background_overview' => 'In a world where ancient magic stirs once more, kingdoms that have known peace for centuries now face an unprecedented threat...',
        'setting_guidance' => [
            'Magic has a price that must always be paid',
            'Political intrigue is as dangerous as any monster',
        ],
        'setting_distinctions' => [
            'Magic leaves visible marks on the landscape',
            'Ancient ruins hold both treasure and danger',
            'The gods walk among mortals, but rarely intervene',
        ],
        'inciting_incident' => 'A mysterious plague begins turning people to stone, starting with the most powerful mages in the kingdom...',
        'special_mechanics' => [
            'Reputation system affects NPC interactions',
        ],
        'campaign_mechanics' => [
            'Magic corruption accumulates with powerful spell use',
            'Political standing affects available resources and allies',
            'Ancient knowledge unlocks new capabilities but attracts danger',
        ],
        'session_zero_questions' => [
            'What event in your past shaped your current worldview?',
            'How does your character feel about magic and those who wield it?',
            'What would your character sacrifice everything to protect?',
        ],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Campaign frame created successfully!');

    // Verify the campaign frame was created with all enhanced fields
    $frame = CampaignFrame::where('name', 'Controller Test Frame')->first();
    expect($frame)->not->toBeNull();
    expect($frame->name)->toBe('Controller Test Frame');
    expect($frame->complexity_rating)->toBe(3);
    expect($frame->is_public)->toBeTrue();
    expect($frame->creator_id)->toBe($user->id);

    // Verify all enhanced fields
    expect($frame->pitch)->toHaveCount(2);
    expect($frame->touchstones)->toHaveCount(3);
    expect($frame->tone)->toHaveCount(3);
    expect($frame->themes)->toHaveCount(3);
    expect($frame->player_principles)->toHaveCount(2);
    expect($frame->gm_principles)->toHaveCount(3);
    expect($frame->community_guidance)->toHaveCount(2);
    expect($frame->ancestry_guidance)->toHaveCount(2);
    expect($frame->class_guidance)->toHaveCount(2);
    expect($frame->setting_guidance)->toHaveCount(2);
    expect($frame->setting_distinctions)->toHaveCount(3);
    expect($frame->campaign_mechanics)->toHaveCount(3);
    expect($frame->session_zero_questions)->toHaveCount(3);

    // Verify content of arrays
    expect($frame->pitch[0])->toBe('An epic adventure awaits');
    expect($frame->touchstones[0])->toBe('Game of Thrones');
    expect($frame->tone[0])->toBe('Epic');
    expect($frame->themes[0])->toBe('Power corrupts');
    expect($frame->player_principles[0])->toBe('Always stay true to your character\'s beliefs');
    expect($frame->gm_principles[0])->toBe('Show don\'t tell');

    // Verify text fields
    expect($frame->background_overview)->toContain('In a world where ancient magic stirs');
    expect($frame->inciting_incident)->toContain('A mysterious plague begins turning people to stone');
});

test('can update campaign frame via controller with enhanced fields', function () {
    $user = User::factory()->create();

    $frame = CampaignFrame::create([
        'name' => 'Original Frame Name',
        'description' => 'Original description',
        'complexity_rating' => 1,
        'is_public' => false,
        'creator_id' => $user->id,
        'pitch' => ['Original pitch'],
        'touchstones' => ['Original touchstone'],
        'tone' => ['Original'],
        'themes' => ['Original theme'],
        'player_principles' => ['Original player principle'],
        'gm_principles' => ['Original GM principle'],
        'community_guidance' => [],
        'ancestry_guidance' => [],
        'class_guidance' => [],
        'background_overview' => 'Original background',
        'setting_guidance' => [],
        'setting_distinctions' => [],
        'inciting_incident' => 'Original incident',
        'special_mechanics' => [],
        'campaign_mechanics' => [],
        'session_zero_questions' => [],
    ]);

    $response = actingAs($user)->put("/campaign-frames/{$frame->id}", [
        'name' => 'Updated Frame Name',
        'description' => 'Updated description with more detail',
        'complexity_rating' => 4,
        'is_public' => true,
        'pitch' => [
            'Updated pitch line one',
            'Updated pitch line two',
            'A compelling third pitch line',
        ],
        'touchstones' => [
            'The Matrix',
            'Blade Runner',
            'Cyberpunk 2077',
        ],
        'tone' => [
            'Cyberpunk',
            'Noir',
            'Dystopian',
        ],
        'themes' => [
            'Technology vs Humanity',
            'Corporate Control',
            'Identity and Reality',
        ],
        'player_principles' => [
            'Question everything you\'re told',
            'Technology has a human cost',
        ],
        'gm_principles' => [
            'Atmosphere is as important as action',
            'Make technology feel lived-in and worn',
        ],
        'community_guidance' => [
            'Corporate communities prioritize efficiency over individual welfare',
            'Street communities form tight-knit families of necessity',
        ],
        'ancestry_guidance' => [
            'Humans struggle with cybernetic augmentation',
            'AI descendants question their place in society',
        ],
        'class_guidance' => [
            'Hackers navigate digital and physical dangers',
            'Corporate agents balance loyalty and conscience',
        ],
        'background_overview' => 'In 2087, mega-corporations rule through technology and data, while the streets below pulse with neon and rebellion...',
        'setting_guidance' => [
            'Every piece of technology should feel dangerous',
            'Information is the most valuable currency',
        ],
        'setting_distinctions' => [
            'Neural interfaces connect minds to the digital realm',
            'Augmented reality overlays the physical world',
            'Corporate arcologies tower over urban sprawl',
        ],
        'inciting_incident' => 'A massive data breach exposes the darkest secrets of every major corporation, triggering a war for information control...',
        'special_mechanics' => [
            'Cybernetic overheating from extended use',
        ],
        'campaign_mechanics' => [
            'Hacking requires risk vs reward calculations',
            'Corporate heat levels affect available safe houses',
            'Neural stress accumulates from interface overuse',
        ],
        'session_zero_questions' => [
            'What drove your character to the streets?',
            'How much of your body are you willing to replace with technology?',
            'Which corporation wronged you, and how?',
        ],
    ]);

    $response->assertRedirect("/campaign-frames/{$frame->id}");
    $response->assertSessionHas('success', 'Campaign frame updated successfully!');

    // Reload and verify updates
    $frame->refresh();
    expect($frame->name)->toBe('Updated Frame Name');
    expect($frame->description)->toBe('Updated description with more detail');
    expect($frame->complexity_rating)->toBe(4);
    expect($frame->is_public)->toBeTrue();

    // Verify enhanced fields were updated
    expect($frame->pitch)->toHaveCount(3);
    expect($frame->touchstones)->toHaveCount(3);
    expect($frame->tone)->toHaveCount(3);
    expect($frame->themes)->toHaveCount(3);
    expect($frame->player_principles)->toHaveCount(2);
    expect($frame->gm_principles)->toHaveCount(2);
    expect($frame->community_guidance)->toHaveCount(2);
    expect($frame->ancestry_guidance)->toHaveCount(2);
    expect($frame->class_guidance)->toHaveCount(2);
    expect($frame->setting_guidance)->toHaveCount(2);
    expect($frame->setting_distinctions)->toHaveCount(3);
    expect($frame->campaign_mechanics)->toHaveCount(3);
    expect($frame->session_zero_questions)->toHaveCount(3);

    // Verify content changed
    expect($frame->pitch[0])->toBe('Updated pitch line one');
    expect($frame->touchstones[0])->toBe('The Matrix');
    expect($frame->tone[0])->toBe('Cyberpunk');
    expect($frame->themes[0])->toBe('Technology vs Humanity');
    expect($frame->background_overview)->toContain('In 2087, mega-corporations rule');
    expect($frame->inciting_incident)->toContain('A massive data breach exposes');
});

test('campaign frame validation prevents invalid enhanced field data', function () {
    $user = User::factory()->create();

    // Test name too long
    $response = actingAs($user)->post('/campaign-frames', [
        'name' => str_repeat('A', 101), // Exceeds 100 character limit
        'description' => 'Valid description',
        'complexity_rating' => 2,
    ]);

    $response->assertSessionHasErrors(['name']);

    // Test description too long
    $response = actingAs($user)->post('/campaign-frames', [
        'name' => 'Valid Name',
        'description' => str_repeat('A', 501), // Exceeds 500 character limit
        'complexity_rating' => 2,
    ]);

    $response->assertSessionHasErrors(['description']);

    // Test background overview too long
    $response = actingAs($user)->post('/campaign-frames', [
        'name' => 'Valid Name',
        'description' => 'Valid description',
        'complexity_rating' => 2,
        'background_overview' => str_repeat('A', 2001), // Exceeds 2000 character limit
    ]);

    $response->assertSessionHasErrors(['background_overview']);

    // Test inciting incident too long
    $response = actingAs($user)->post('/campaign-frames', [
        'name' => 'Valid Name',
        'description' => 'Valid description',
        'complexity_rating' => 2,
        'inciting_incident' => str_repeat('A', 1001), // Exceeds 1000 character limit
    ]);

    $response->assertSessionHasErrors(['inciting_incident']);

    // Test invalid complexity rating
    $response = actingAs($user)->post('/campaign-frames', [
        'name' => 'Valid Name',
        'description' => 'Valid description',
        'complexity_rating' => 5, // Should be 1-4
    ]);

    $response->assertSessionHasErrors(['complexity_rating']);
});

test('can only edit own campaign frames', function () {
    $current_user = User::factory()->create();
    $other_user = User::factory()->create();

    $frame = CampaignFrame::create([
        'name' => 'Other User Frame',
        'description' => 'Created by another user',
        'complexity_rating' => 2,
        'is_public' => true,
        'creator_id' => $other_user->id,
        'pitch' => [],
        'touchstones' => [],
        'tone' => [],
        'themes' => [],
        'player_principles' => [],
        'gm_principles' => [],
        'community_guidance' => [],
        'ancestry_guidance' => [],
        'class_guidance' => [],
        'background_overview' => 'Background by other user',
        'setting_guidance' => [],
        'setting_distinctions' => [],
        'inciting_incident' => 'Incident by other user',
        'special_mechanics' => [],
        'campaign_mechanics' => [],
        'session_zero_questions' => [],
    ]);

    // Try to edit another user's frame
    $response = actingAs($current_user)->get("/campaign-frames/{$frame->id}/edit");
    $response->assertStatus(403);

    // Try to update another user's frame
    $response = actingAs($current_user)->put("/campaign-frames/{$frame->id}", [
        'name' => 'Malicious Update',
        'description' => 'Trying to edit someone else\'s frame',
        'complexity_rating' => 1,
    ]);
    $response->assertStatus(403);

    // Try to delete another user's frame
    $response = actingAs($current_user)->delete("/campaign-frames/{$frame->id}");
    $response->assertStatus(403);

    // Verify frame was not modified
    $frame->refresh();
    expect($frame->name)->toBe('Other User Frame');
    expect($frame->description)->toBe('Created by another user');
});

test('can view public campaign frames but not private ones', function () {
    $current_user = User::factory()->create();
    $other_user = User::factory()->create();

    $public_frame = CampaignFrame::create([
        'name' => 'Public Frame',
        'description' => 'Everyone can see this',
        'complexity_rating' => 2,
        'is_public' => true,
        'creator_id' => $other_user->id,
        'pitch' => ['Public pitch'],
        'touchstones' => ['Public movie'],
        'tone' => ['Public tone'],
        'themes' => ['Public theme'],
        'player_principles' => [],
        'gm_principles' => [],
        'community_guidance' => [],
        'ancestry_guidance' => [],
        'class_guidance' => [],
        'background_overview' => 'Public background',
        'setting_guidance' => [],
        'setting_distinctions' => [],
        'inciting_incident' => 'Public incident',
        'special_mechanics' => [],
        'campaign_mechanics' => [],
        'session_zero_questions' => [],
    ]);

    $private_frame = CampaignFrame::create([
        'name' => 'Private Frame',
        'description' => 'Only creator can see this',
        'complexity_rating' => 1,
        'is_public' => false,
        'creator_id' => $other_user->id,
        'pitch' => ['Private pitch'],
        'touchstones' => ['Private movie'],
        'tone' => ['Private tone'],
        'themes' => ['Private theme'],
        'player_principles' => [],
        'gm_principles' => [],
        'community_guidance' => [],
        'ancestry_guidance' => [],
        'class_guidance' => [],
        'background_overview' => 'Private background',
        'setting_guidance' => [],
        'setting_distinctions' => [],
        'inciting_incident' => 'Private incident',
        'special_mechanics' => [],
        'campaign_mechanics' => [],
        'session_zero_questions' => [],
    ]);

    // Should be able to view public frame
    $response = actingAs($current_user)->get("/campaign-frames/{$public_frame->id}");
    $response->assertStatus(200);
    $response->assertSee('Public Frame');
    $response->assertSee('Everyone can see this');

    // Should not be able to view private frame
    $response = actingAs($current_user)->get("/campaign-frames/{$private_frame->id}");
    $response->assertStatus(403);
});
