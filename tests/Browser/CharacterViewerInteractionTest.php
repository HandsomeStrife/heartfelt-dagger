<?php

declare(strict_types=1);

use Domain\User\Models\User;
use Domain\Character\Models\Character;

use function Pest\Laravel\actingAs;

test('character viewer edit button navigates to character builder', function () {
    $user = User::factory()->create();
    $character = Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Edit Navigation Test',
    ]);

    actingAs($user);
    $page = visit("/character-viewer/{$character->public_key}");

    $page->assertSee('Edit Character');
    
    // Click edit button and verify navigation
    $page->click('Edit Character');
    $page->assertPathIs("/character-builder/{$character->character_key}");
});

test('character viewer shows correct class-specific information', function () {
    $character = Character::factory()->create([
        'selected_class' => 'guardian',
        'selected_subclass' => 'stalwart',
        'name' => 'Class Specific Test',
    ]);

    $page = visit("/character-viewer/{$character->public_key}");

    // Guardian should show Blade & Valor domains
    $page->assertSee('Blade & Valor')
        ->assertSee('Guardian')
        ->assertSee('Stalwart');
});

test('character viewer displays equipment correctly', function () {
    $character = Character::factory()->create([
        'selected_equipment' => [
            [
                'key' => 'leather-armor',
                'type' => 'armor',
                'data' => [
                    'name' => 'Leather Armor',
                    'baseScore' => 3,
                    'baseThresholds' => ['minor' => 1, 'major' => 2, 'severe' => 3]
                ]
            ],
            [
                'key' => 'longsword',
                'type' => 'weapon',
                'data' => [
                    'name' => 'Longsword',
                    'type' => 'Primary',
                    'trait' => 'Strength',
                    'range' => 'Melee',
                    'damage' => ['dice' => 'd8', 'modifier' => 2, 'type' => 'physical']
                ]
            ]
        ],
        'name' => 'Equipment Test Character',
    ]);

    $page = visit("/character-viewer/{$character->public_key}");

    $page->assertSee('Leather Armor')
        ->assertSee('Longsword')
        ->assertSee('Active Armor')
        ->assertSee('Active Weapons')
        ->assertSee('3') // Armor score
        ->assertSee('Strength') // Weapon trait
        ->assertSee('Melee'); // Weapon range
});

test('character viewer displays experiences with modifiers', function () {
    $character = Character::factory()->create([
        'experiences' => [
            ['name' => 'Wilderness Survival', 'modifier' => 2],
            ['name' => 'Court Etiquette', 'modifier' => 2],
        ],
        'name' => 'Experience Test Character',
    ]);

    $page = visit("/character-viewer/{$character->public_key}");

    $page->assertSee('Experience')
        ->assertSee('Wilderness Survival')
        ->assertSee('Court Etiquette')
        ->assertSee('+2') // Experience modifier
        ->assertSee('+2');
});

test('character viewer shows journal content when available', function () {
    $character = Character::factory()->create([
        'personality_traits' => 'Brave and loyal',
        'personal_history' => 'Born in the mountains',
        'motivations' => 'Protect the innocent',
        'name' => 'Journal Test Character',
    ]);

    $page = visit("/character-viewer/{$character->public_key}");

    $page->assertSee('Journal')
        ->assertSee('Personality: Brave and loyal')
        ->assertSee('History: Born in the mountains')
        ->assertSee('Motivations: Protect the innocent');
});

test('character viewer handles missing optional data gracefully', function () {
    $character = Character::factory()->create([
        'name' => 'Minimal Character',
        'selected_class' => 'warrior',
        'selected_ancestry' => 'human',
        'selected_community' => 'wildborne',
        // Minimal data - no equipment, experiences, etc.
    ]);

    $page = visit("/character-viewer/{$character->public_key}");

    $page->assertSee('Minimal Character')
        ->assertSee('Warrior')
        ->assertSee('Human')
        ->assertSee('Wildborne')
        ->assertSee('No armor equipped')
        ->assertSee('Set active in the equipment section');
});

test('character viewer displays profile image when available', function () {
    $character = Character::factory()->create([
        'profile_image_path' => 'characters/test-character.jpg',
        'name' => 'Image Test Character',
    ]);

    $page = visit("/character-viewer/{$character->public_key}");

    // Check that an img element is present (profile image)
    $imageCount = $page->script('return document.querySelectorAll("img").length');
    expect($imageCount)->toBeGreaterThan(0);
});

test('character viewer shows default gradient when no profile image', function () {
    $character = Character::factory()->create([
        'profile_image_path' => null,
        'name' => 'No Image Character',
    ]);

    $page = visit("/character-viewer/{$character->public_key}");

    // Should show gradient background div instead of img
    $gradientElement = $page->script('return document.querySelector(".bg-gradient-to-br.from-slate-700") !== null');
    expect($gradientElement)->toBeTrue();
});

test('character viewer counters update correctly with interaction', function () {
    $user = User::factory()->create();
    $character = Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Counter Test Character',
    ]);

    actingAs($user);
    $page = visit("/character-viewer/{$character->public_key}");

    // Initially should show 0 / 6 for HP
    $page->assertSee('0 / 6 Marked');

    // Mark some HP and verify counter updates
    $page->script('
        const component = document.querySelector("[x-data]");
        const data = Alpine.$data(component);
        data.toggleHitPoint(0);
        data.toggleHitPoint(1);
    ');

    $page->assertSee('2 / 6 Marked');

    // Test Hope counter (starts with 2/6)
    $page->assertSee('2 / 6'); // Hope counter

    // Add hope and verify
    $page->script('
        const component = document.querySelector("[x-data]");
        const data = Alpine.$data(component);
        data.toggleHope(2);
    ');

    $page->assertSee('3 / 6'); // Updated hope counter
});

test('character viewer responsive layout works on mobile', function () {
    $character = Character::factory()->create([
        'name' => 'Mobile Test Character',
    ]);

    $page = visit("/character-viewer/{$character->public_key}")
        ->on()->mobile();

    // Should still display all key elements on mobile
    $page->assertSee('Mobile Test Character')
        ->assertSee('Damage & Health')
        ->assertSee('Hope')
        ->assertSee('LEVEL')
        ->assertSee('1');

    // Verify the grid layout adapts (check for mobile-specific classes)
    $gridElement = $page->script('return document.querySelector(".grid.grid-cols-2") !== null');
    expect($gridElement)->toBeTrue(); // Traits should be 2 columns on mobile
});
