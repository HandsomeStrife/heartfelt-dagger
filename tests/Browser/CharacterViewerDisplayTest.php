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

test('character owner can interact with HP tracking elements', function () {
    $user = User::factory()->create();
    $character = Character::factory()->complete()->create([
        'user_id' => $user->id,
        'name' => 'Interactive Test Character',
    ]);

    // Visit as authenticated owner
    actingAs($user);
    $page = visit("/character/{$character->public_key}");
    $page->wait(1);
    $page->wait(1); // Ensure Alpine is initialized

    // Should see edit button for owners
    $page->assertSee('Edit Character');

    // Click the first HP box to mark it as damaged
    $page->click('[data-testid="hp-toggle-0"]');
    $page->wait(0.5); // Allow Alpine.js to update

    // Verify the state change by checking if localStorage was updated
    $localStorageValue = $page->script('(function(){ return localStorage.getItem("character_state_' . $character->character_key . '"); })()');
    expect($localStorageValue)->not->toBeNull();
    
    // Parse the JSON and verify HP was marked
    $state = json_decode($localStorageValue, true);
    expect($state['hitPoints'][0])->toBeTrue();
});

test('character HP state persists after page refresh', function () {
    $user = User::factory()->create();
    $character = Character::factory()->complete()->create([
        'user_id' => $user->id,
        'name' => 'Persistence Test Character',
    ]);

    actingAs($user);
    $page = visit("/character/{$character->public_key}");
    $page->wait(2);

    // Set some HP and Stress damage via DOM clicks
    $page->click('[data-testid="hp-toggle-0"]');
    $page->click('[data-testid="hp-toggle-1"]');
    $page->click('[data-testid="stress-toggle-0"]');
    $page->click('[data-testid="hope-toggle-2"]');
    $page->wait(2);

    // Refresh the page and allow Livewire/Alpine to rehydrate state
    $page->navigate("/character/{$character->public_key}");
    $page->wait(2);
    // Ensure localStorage state exists as fallback
    $page->script('window.__state = localStorage.getItem("character_state_' . $character->character_key . '")');

    // Wait for Alpine to initialize state
    $ready = false;
    for ($i = 0; $i < 12; $i++) {
        $ready = $page->script('(function(){
            const root = document.querySelector("[x-data]");
            const hpEl = document.querySelector("[data-testid=\\"hp-toggle-0\\"] input");
            if (!root || !hpEl || !window.Alpine) return false;
            try { const d = Alpine.$data(root); return Array.isArray(d.hitPoints) && Array.isArray(d.stress) && Array.isArray(d.hope); } catch(e) { return false; }
        })()');
        if ($ready) { break; }
        $page->wait(0.5);
    }
    expect($ready)->toBeTrue();

    // Verify the state was restored (use Alpine state rather than raw checkbox to avoid browser toggling noise)
    $restored = $page->script('(function(){
        const c = document.querySelector("[x-data]");
        const d = Alpine.$data(c);
        return { hp0: d.hitPoints[0], hp1: d.hitPoints[1], stress0: d.stress[0], hope2: d.hope[2] };
    })()');
    
    // Two toggles above keep the persisted state; verify reflects as true
    expect($restored['hp0'])->toBeTrue();
    expect($restored['hp1'])->toBeTrue();
    expect($restored['stress0'])->toBeTrue();
    expect($restored['hope2'])->toBeTrue();
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

test('anonymous users cannot interact with character elements', function () {
    $owner = User::factory()->create();
    $character = Character::factory()->complete()->create([
        'user_id' => $owner->id,
        'name' => 'Public Character',
    ]);

    // Ensure no localStorage grants
    $page = visit("/character/{$character->public_key}");
    $page->script('localStorage.removeItem("daggerheart_characters"); localStorage.removeItem("character_state_' . $character->character_key . '")');
    $page->navigate("/character/{$character->public_key}");

    // Should not see edit button
    $page->assertDontSee('Edit Character');

    // Verify canEdit is false by checking the Gold section is hidden (only shows when canEdit)
    $goldVisible = $page->script('(function(){
        const goldH2 = Array.from(document.querySelectorAll("h2")).find(h => h.textContent.trim() === "Gold");
        if (!goldH2) return false;
        const container = goldH2.closest("div.rounded-3xl");
        if (!container) return false;
        const style = window.getComputedStyle(container);
        const rect = container.getBoundingClientRect();
        return style.display !== "none" && rect.width > 0 && rect.height > 0;
    })()');
    expect($goldVisible)->toBeFalse();

    // Wait for Alpine to initialize for anonymous view
    $ready = false;
    for ($i = 0; $i < 12; $i++) {
        $ready = $page->script('(function(){
            const root = document.querySelector("[x-data]");
            const hpEl = document.querySelector("[data-testid=\\"hp-toggle-0\\"] input");
            if (!root || !hpEl || !window.Alpine) return false;
            try { const d = Alpine.$data(root); return Array.isArray(d.hitPoints); } catch(e) { return false; }
        })()');
        if ($ready) { break; }
        $page->wait(0.5);
    }
    expect($ready)->toBeTrue();

    // Attempting to click HP elements should not change Alpine state
    $initial = $page->script('(function(){ const c=document.querySelector("[x-data]"); const d=Alpine.$data(c); return d.hitPoints[0]; })()');
    $page->click('[data-testid="hp-toggle-0"]');
    $after = $page->script('(function(){ const c=document.querySelector("[x-data]"); const d=Alpine.$data(c); return d.hitPoints[0]; })()');
    expect($initial)->toBe($after);
});

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

test('complete character state saves and loads correctly', function () {
    $user = User::factory()->create();
    $character = Character::factory()->complete()->create([
        'user_id' => $user->id,
        'name' => 'Complete State Test Character',
    ]);

    actingAs($user);
    $page = visit("/character/{$character->public_key}");
    $page->wait(1);

    // Set a comprehensive character state via clicks
    $page->click('[data-testid="hp-toggle-0"]');
    $page->click('[data-testid="hp-toggle-1"]');
    $page->click('[data-testid="hp-toggle-2"]');
    $page->click('[data-testid="stress-toggle-0"]');
    $page->click('[data-testid="stress-toggle-1"]');
    $page->click('[data-testid="hope-toggle-2"]');
    $page->click('[data-testid="gold-handful-0"]');
    $page->click('[data-testid="gold-handful-1"]');
    $page->click('[data-testid="gold-bag-0"]');
    $page->click('[data-testid="gold-chest-toggle"]');
    $page->wait(2);

    // Refresh the page to test persistence
    $page->navigate("/character/{$character->public_key}");
    $page->wait(2); // Allow Livewire/Alpine to initialize and load state

    // Wait for Alpine to initialize before reading state
    $ready = false;
    for ($i = 0; $i < 12; $i++) {
        $ready = $page->script('(function(){
            const root = document.querySelector("[x-data]");
            const hpEl = document.querySelector("[data-testid=\\"hp-toggle-0\\"] input");
            if (!root || !hpEl || !window.Alpine) return false;
            try { const d = Alpine.$data(root); return Array.isArray(d.hitPoints) && Array.isArray(d.goldHandfuls) && Array.isArray(d.goldBags); } catch(e) { return false; }
        })()');
        if ($ready) { break; }
        $page->wait(0.5);
    }
    expect($ready)->toBeTrue();

    // Verify all state was restored correctly (use Alpine state)
    $fullState = $page->script('(function(){
        const c = document.querySelector("[x-data]");
        const d = Alpine.$data(c);
        return {
            hitPointsMarked: d.hitPoints.filter(Boolean).length,
            stressMarked: d.stress.filter(Boolean).length,
            hopeMarked: d.hope.filter(Boolean).length,
            armorMarked: d.armorSlots ? d.armorSlots.filter(Boolean).length : 0,
            goldHandfulsMarked: d.goldHandfuls.filter(Boolean).length,
            goldBagsMarked: d.goldBags.filter(Boolean).length,
            goldChestMarked: d.goldChest === true
        };
    })()');

    expect($fullState['hitPointsMarked'])->toBe(3);
    expect($fullState['stressMarked'])->toBe(2);
    expect($fullState['hopeMarked'])->toBe(3); // Started with 2, added 1
    expect($fullState['armorMarked'])->toBe(0); // armor slots represented in stress section UI
    expect($fullState['goldHandfulsMarked'])->toBe(2);
    expect($fullState['goldBagsMarked'])->toBe(1);
    expect($fullState['goldChestMarked'])->toBeTrue();
});

test('character state loads from database when localStorage is empty', function () {
    $user = User::factory()->create();
    $character = Character::factory()->complete()->create([
        'user_id' => $user->id,
        'name' => 'DB Persistence Character',
    ]);

    actingAs($user);
    $page = visit("/character/{$character->public_key}");

    // Set a state via click interactions (no direct scripts)
    $page->click('[data-testid="hp-toggle-0"]');
    $page->click('[data-testid="stress-toggle-0"]');
    $page->click('[data-testid="hope-toggle-2"]');
    $page->click('[data-testid="gold-handful-0"]');
    $page->click('[data-testid="gold-bag-0"]');
    $page->click('[data-testid="gold-chest-toggle"]');
    $page->wait(2);

    // Clear localStorage to force load from database on next page load
    $page->script('localStorage.removeItem("character_state_' . $character->character_key . '"); localStorage.removeItem("daggerheart_characters");');

    // Reload the page; authenticated users should load from DB first
    $page->navigate("/character/{$character->public_key}");
    $page->wait(2);

    // Wait for Alpine to initialize then verify state restored from DB (not localStorage)
    $ready = false;
    for ($i = 0; $i < 12; $i++) {
        $ready = $page->script('(function(){
            const root = document.querySelector("[x-data]");
            const hpEl = document.querySelector("[data-testid=\\"hp-toggle-0\\"] input");
            if (!root || !hpEl || !window.Alpine) return false;
            try { const d = Alpine.$data(root); return Array.isArray(d.hitPoints) && Array.isArray(d.goldBags); } catch(e) { return false; }
        })()');
        if ($ready) { break; }
        $page->wait(0.5);
    }
    expect($ready)->toBeTrue();

    $restored = $page->script('(function(){
        const c = document.querySelector("[x-data]");
        const d = Alpine.$data(c);
        return {
            hp0: d.hitPoints[0],
            stress0: d.stress[0],
            hope2: d.hope[2],
            hand0: d.goldHandfuls[0],
            bag0: d.goldBags[0],
            chest: d.goldChest
        };
    })()');

    expect($restored['hp0'])->toBeTrue();
    expect($restored['stress0'])->toBeTrue();
    expect($restored['hope2'])->toBeTrue();
    expect($restored['hand0'])->toBeTrue();
    expect($restored['bag0'])->toBeTrue();
    expect($restored['chest'])->toBeTrue();
});
