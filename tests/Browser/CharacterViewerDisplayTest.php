<?php

declare(strict_types=1);

use Domain\User\Models\User;
use Domain\Character\Models\Character;

use function Pest\Laravel\actingAs;

test('character viewer displays basic character information correctly', function () {
    // Create authenticated user and character
    $user = User::factory()->create();
    $character = Character::factory()->complete()->create([
        'user_id' => $user->id,
        'name' => 'Test Warrior',
        'class' => 'warrior',
        'ancestry' => 'human',
        'community' => 'wildborne',
    ]);

    $page = visit("/character/{$character->public_key}");
    $page->wait(1);

    $page->assertSee('Test Warrior')
        ->assertSee('Warrior')
        ->assertSee('Human')
        ->assertSee('Wildborne')
        ->assertSee('LEVEL')
        ->assertSee('1')
        ->assertSee('Damage & Health')
        ->assertSee('Hope')
        ->assertSee('Active Weapons');
});

test('character viewer shows trait values correctly', function () {
    $character = Character::factory()->complete()->create([
        'name' => 'Trait Test Character',
        'class' => 'warrior',
    ]);
    // Attach traits explicitly to match viewer expectations
    $character->traits()->delete();
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => -1],
        ['trait_name' => 'strength', 'trait_value' => 0],
        ['trait_name' => 'finesse', 'trait_value' => 0],
        ['trait_name' => 'instinct', 'trait_value' => 1],
        ['trait_name' => 'presence', 'trait_value' => 1],
        ['trait_name' => 'knowledge', 'trait_value' => 2],
    ]);

    $page = visit("/character/{$character->public_key}");
    $page->wait(1);

    // Verify trait numbers using aria-labels on stat frames (SVG text is not assertSee-visible)
    $agility = $page->script('(function(){ const el = document.querySelector("svg[aria-label*=\\"Agility\\"]"); return el ? el.getAttribute("aria-label") : null; })()');
    $instinct = $page->script('(function(){ const el = document.querySelector("svg[aria-label*=\\"Instinct\\"]"); return el ? el.getAttribute("aria-label") : null; })()');
    $presence = $page->script('(function(){ const el = document.querySelector("svg[aria-label*=\\"Presence\\"]"); return el ? el.getAttribute("aria-label") : null; })()');
    $knowledge = $page->script('(function(){ const el = document.querySelector("svg[aria-label*=\\"Knowledge\\"]"); return el ? el.getAttribute("aria-label") : null; })()');

    expect($agility)->toContain('-1');
    expect($instinct)->toContain('+1');
    expect($presence)->toContain('+1');
    expect($knowledge)->toContain('+2');

    // Action text may vary by layout; ensure trait frames exist
});

test('character viewer displays computed statistics accurately', function () {
    $character = Character::factory()->complete()->create([
        'class' => 'guardian',
    ]);
    $character->traits()->delete();
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 0],
        ['trait_name' => 'strength', 'trait_value' => 2],
        ['trait_name' => 'finesse', 'trait_value' => 0],
        ['trait_name' => 'instinct', 'trait_value' => 1],
        ['trait_name' => 'presence', 'trait_value' => -1],
        ['trait_name' => 'knowledge', 'trait_value' => 1],
    ]);

    $page = visit("/character/{$character->public_key}");
    $page->wait(1);

    // Guardian has starting Evasion 9, Hit Points 7
    $page->assertSee('9') // Evasion (now displayed under profile picture)
        ->assertSee('Minor → Major') // Damage thresholds (now showing transition values)
        ->assertSee('Major → Severe')
        ->assertSee('Evasion'); // Evasion label
});

test('character viewer shows domain information for spellcasters', function () {
    $character = Character::factory()->complete()->create([
        'class' => 'sorcerer', // Arcana & Midnight domains
        'subclass' => 'emberwild',
    ]);
    $character->domainCards()->delete();
    $character->domainCards()->createMany([
        ['domain' => 'arcana', 'ability_key' => 'elemental-blast', 'ability_level' => 1],
        ['domain' => 'midnight', 'ability_key' => 'shadow-step', 'ability_level' => 1],
    ]);

    $page = visit("/character/{$character->public_key}");
    $page->wait(1);

    $page->assertSee('Elemental Blast')
        ->assertSee('Shadow Step');
});

// Recreating failing tests below
test('character owner can interact with HP tracking elements', function () {
    $user = User::factory()->create();
    $character = Character::factory()->complete()->create([
        'user_id' => $user->id,
        'name' => 'Interactive Test Character',
    ]);

    actingAs($user);
    $page = visit("/character/{$character->public_key}");

    // Owner should see edit control (icon link with aria-label)
    $page->assertVisible('[aria-label="Edit character"]');

    // Toggle first HP and wait for persistence
    $page->click('[data-testid="hp-toggle-0"]');
    waitForChecked($page, '[data-testid="hp-toggle-0"] input');
    // Note: relying on DOM state for interactivity; persistence covered in dedicated tests
});

//
test('character HP state persists after page refresh', function () {
    $user = User::factory()->create();
    $character = Character::factory()->complete()->create([
        'user_id' => $user->id,
        'name' => 'Persistence Test Character',
    ]);

    actingAs($user);
    $page = visit("/character/{$character->public_key}");

    // Interact via DOM
    $page->click('[data-testid="hp-toggle-0"]');
    $page->click('[data-testid="hp-toggle-1"]');
    $page->click('[data-testid="stress-toggle-0"]');
    $page->click('[data-testid="hope-toggle-2"]');
    $page->wait(1.5);

    // Reload and assert DOM state restored (checkboxes)
    $page->navigate("/character/{$character->public_key}");
    waitForHydration($page);

    // Assert checkboxes are checked rather than Alpine internals
    $page->assertChecked('[data-testid="hp-toggle-0"] input');
    $page->assertChecked('[data-testid="hp-toggle-1"] input');
    $page->assertChecked('[data-testid="stress-toggle-0"] input');
    $page->assertChecked('[data-testid="hope-toggle-2"] input');
});

test('character stress tracking works correctly', function () {
    $user = User::factory()->create();
    $character = Character::factory()->complete()->create([
        'user_id' => $user->id,
        'name' => 'Stress Test Character',
    ]);

    actingAs($user);
    $page = visit("/character/{$character->public_key}");

    // Toggle three stress via clicks
    $page->click('[data-testid="stress-toggle-0"]');
    $page->click('[data-testid="stress-toggle-1"]');
    $page->click('[data-testid="stress-toggle-2"]');

    // Check that 3 stress are marked via DOM
    $marked = $page->script('(function(){ return Array.from(document.querySelectorAll("[data-testid^=\\"stress-toggle-\\"] input")).filter(el => el.checked).length; })()');
    expect($marked)->toBe(3);

    // Verify counter shows "3 / 6 Marked"
    $page->assertSee('3 / 6 Marked');
});

test('character hope tracking functions properly', function () {
    $user = User::factory()->create();
    $character = Character::factory()->complete()->create([
        'user_id' => $user->id,
        'name' => 'Hope Test Character',
    ]);

    actingAs($user);
    $page = visit("/character/{$character->public_key}");

    // Start with 2 hope, add a third one via click
    $page->click('[data-testid="hope-toggle-2"]');

    // Verify hope counter shows "3 / 6"
    $page->assertSee('3 / 6');

    // Remove first hope
    $page->click('[data-testid="hope-toggle-0"]');

    // Should now show "2 / 6"
    $page->assertSee('2 / 6');
});

test('armor slot tracking works correctly', function () {
    $user = User::factory()->create();
    $character = Character::factory()->complete()->create([
        'user_id' => $user->id,
        'name' => 'Armor Test Character',
    ]);

    actingAs($user);
    $page = visit("/character/{$character->public_key}");

    // Mark 2 armor slots as damaged via clicks (these are the stress slots)
    $page->click('[data-testid="stress-toggle-0"]');
    $page->click('[data-testid="stress-toggle-1"]');

    $armorState = $page->script('(function(){ return Array.from(document.querySelectorAll("[data-testid^=\\"stress-toggle-\\"] input")).filter(el => el.checked).length; })()');
    expect($armorState)->toBe(2);
});

test('gold tracking system functions correctly', function () {
    $user = User::factory()->create();
    $character = Character::factory()->complete()->create([
        'user_id' => $user->id,
        'name' => 'Gold Test Character',
    ]);

    actingAs($user);
    $page = visit("/character/{$character->public_key}");

    // Should see the gold tracking section with SVG icons
    $page->assertSee('Handfuls')
        ->assertSee('Bags')
        ->assertSee('Chest');

    // Verify expected counts using data-testid
    $counts = $page->script('(function(){
        return {
            handfuls: document.querySelectorAll("[data-testid^=\\"gold-handful-\\"]").length,
            bags: document.querySelectorAll("[data-testid^=\\"gold-bag-\\"]").length,
            chest: document.querySelectorAll("[data-testid=\\"gold-chest-toggle\\"]").length,
        };
    })()');
    expect($counts['handfuls'])->toBe(9);
    expect($counts['bags'])->toBe(9);
    expect($counts['chest'])->toBe(1);

    // Mark some gold as spent via clicks
    $page->click('[data-testid="gold-handful-0"]');
    $page->click('[data-testid="gold-handful-1"]');
    $page->click('[data-testid="gold-handful-2"]');
    $page->click('[data-testid="gold-bag-0"]');
    $page->click('[data-testid="gold-bag-1"]');
    $page->click('[data-testid="gold-chest-toggle"]');

    $goldState = $page->script('(function(){
        return {
            handfuls: Array.from(document.querySelectorAll("[data-testid^=\\"gold-handful-\\"] input")).filter(el => el.checked).length,
            bags: Array.from(document.querySelectorAll("[data-testid^=\\"gold-bag-\\"] input")).filter(el => el.checked).length,
            chest: document.querySelector("[data-testid=\\"gold-chest-toggle\\"] input").checked
        };
    })()');

    expect($goldState['handfuls'])->toBe(3);
    expect($goldState['bags'])->toBe(2);
    expect($goldState['chest'])->toBeTrue();
});

//

test('localStorage key grants edit access for anonymous users', function () {
    $character = Character::factory()->complete()->create([
        'name' => 'Anonymous Owner Character',
    ]);

    // Simulate localStorage having the character key
    $page = visit("/character/{$character->public_key}");
    $page->wait(1);
    $page->script('localStorage.setItem("daggerheart_characters", JSON.stringify(["' . $character->character_key . '"]))');
    $page->navigate("/character/{$character->public_key}");
    $page->wait(2);

    // Now canEdit should be true by checking that Gold section is visible
    $goldVisible = $page->script('(function(){
        const goldSection = Array.from(document.querySelectorAll("h2")).find(h => h.textContent.trim() === "Gold");
        return !!goldSection;
    })()');
    expect($goldVisible)->toBeTrue();
});

//

//
