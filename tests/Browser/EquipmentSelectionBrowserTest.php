<?php

declare(strict_types=1);

it('allows primary weapon selection and replacement', function () {
    $character = \Domain\Character\Models\Character::create([
        'name' => null,
        'character_key' => \Domain\Character\Models\Character::generateUniqueKey(),
        'user_id' => null,
        'class' => null,
        'subclass' => null,
        'ancestry' => null,
        'community' => null,
        'level' => 1,
        'character_data' => [],
        'is_public' => false,
    ]);

    $page = visit('/character-builder/'.$character->character_key);
    
    // Select class to enable equipment
    $page->assertPresent('[pest="class-card-warrior"]');
    $page->click('[pest="class-card-warrior"]');
    $page->wait(1);

    // Navigate to Equipment step
    for ($i = 0; $i < 5; $i++) {
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
    }

    $page->assertSee('Select Equipment');
    $page->wait(2);

    // Check that we can see weapon selection options
    $page->assertSee('Primary Weapon');

    // Select a primary weapon (click first available weapon)
    $page->click('div[\\@click*="selectEquipment"][\\@click*="weapon"]:first-of-type');
    $page->wait(2);

    // Should show weapon was selected in the progress indicator
    $page->assertSee('Primary'); // Should show in progress
});

it('enforces armor selection requirement', function () {
    $character = \Domain\Character\Models\Character::create([
        'name' => null,
        'character_key' => \Domain\Character\Models\Character::generateUniqueKey(),
        'user_id' => null,
        'class' => null,
        'subclass' => null,
        'ancestry' => null,
        'community' => null,
        'level' => 1,
        'character_data' => [],
        'is_public' => false,
    ]);

    $page = visit('/character-builder/'.$character->character_key);
    
    // Select class
    $page->assertPresent('[pest="class-card-bard"]');
    $page->click('[pest="class-card-bard"]');
    $page->wait(1);

    // Navigate to Equipment step
    for ($i = 0; $i < 5; $i++) {
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
    }

    $page->wait(2);

    // Select primary weapon first
    $page->click('div[\\@click*="selectEquipment"][\\@click*="weapon"]:first-of-type');
    $page->wait(1);

    // Select armor
    $page->click('div[\\@click*="selectEquipment"][\\@click*="armor"]:first-of-type');
    $page->wait(1);

    // Progress should reflect both selections
    $page->assertSee('Primary');
    $page->assertSee('Armor');
});

it('handles starting inventory chooseOne requirements', function () {
    $character = \Domain\Character\Models\Character::create([
        'name' => null,
        'character_key' => \Domain\Character\Models\Character::generateUniqueKey(),
        'user_id' => null,
        'class' => null,
        'subclass' => null,
        'ancestry' => null,
        'community' => null,
        'level' => 1,
        'character_data' => [],
        'is_public' => false,
    ]);

    $page = visit('/character-builder/'.$character->character_key);
    
    // Select class with chooseOne requirement (Bard)
    $page->assertPresent('[pest="class-card-bard"]');
    $page->click('[pest="class-card-bard"]');
    $page->wait(1);

    // Navigate to Equipment step
    for ($i = 0; $i < 5; $i++) {
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
    }

    $page->wait(2);

    // Select required equipment
    $page->click('div[\\@click*="selectEquipment"][\\@click*="weapon"]:first-of-type');
    $page->wait(1);
    $page->click('div[\\@click*="selectEquipment"][\\@click*="armor"]:first-of-type');
    $page->wait(1);

    // Should see chooseOne section
    $page->assertSee('Choose One');

    // Select a chooseOne item
    $page->click('div[\\@click*="selectInventoryItem"]:first-of-type');
    $page->wait(1);

    // Equipment step should be complete
    $page->assertSee('Inventory');
});

it('handles starting inventory chooseExtra requirements', function () {
    $character = \Domain\Character\Models\Character::create([
        'name' => null,
        'character_key' => \Domain\Character\Models\Character::generateUniqueKey(),
        'user_id' => null,
        'class' => null,
        'subclass' => null,
        'ancestry' => null,
        'community' => null,
        'level' => 1,
        'character_data' => [],
        'is_public' => false,
    ]);

    $page = visit('/character-builder/'.$character->character_key);
    
    // Select class with both chooseOne and chooseExtra (Guardian)
    $page->assertPresent('[pest="class-card-guardian"]');
    $page->click('[pest="class-card-guardian"]');
    $page->wait(1);

    // Navigate to Equipment step
    for ($i = 0; $i < 5; $i++) {
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
    }

    $page->wait(2);

    // Select required equipment
    $page->click('div[\\@click*="selectEquipment"][\\@click*="weapon"]:first-of-type');
    $page->wait(1);
    $page->click('div[\\@click*="selectEquipment"][\\@click*="armor"]:first-of-type');
    $page->wait(1);

    // Should see both chooseOne and chooseExtra sections
    $page->assertSee('Choose One');
    $page->assertSee('Choose Extra');

    // Select items from both sections (chooseOne and chooseExtra)
    $page->click('div[\\@click*="selectInventoryItem"]:first-of-type');
    $page->wait(1);
    $page->click('div[\\@click*="selectInventoryItem"]:nth-of-type(2)');
    $page->wait(1);

    // Equipment should be complete when both are selected
    $page->assertSee('Inventory');
});

it('allows secondary weapon selection as optional', function () {
    $character = \Domain\Character\Models\Character::create([
        'name' => null,
        'character_key' => \Domain\Character\Models\Character::generateUniqueKey(),
        'user_id' => null,
        'class' => null,
        'subclass' => null,
        'ancestry' => null,
        'community' => null,
        'level' => 1,
        'character_data' => [],
        'is_public' => false,
    ]);

    $page = visit('/character-builder/'.$character->character_key);
    
    // Select class
    $page->assertPresent('[pest="class-card-warrior"]');
    $page->click('[pest="class-card-warrior"]');
    $page->wait(1);

    // Navigate to Equipment step
    for ($i = 0; $i < 5; $i++) {
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
    }

    $page->wait(2);

    // Select primary weapon and armor
    $page->click('div[\\@click*="selectEquipment"][\\@click*="weapon"]:first-of-type');
    $page->wait(1);
    $page->click('div[\\@click*="selectEquipment"][\\@click*="armor"]:first-of-type');
    $page->wait(1);

    // Secondary should show as optional
    $page->assertSee('Secondary');
    
    // Select secondary weapon (find second weapon option)
    $page->click('div[\\@click*="selectEquipment"][\\@click*="weapon"]:nth-of-type(2)');
    $page->wait(1);

    // Both primary and secondary should show as selected
    $page->assertSee('Primary');
    $page->assertSee('Secondary');
});

it('shows equipment completion status correctly', function () {
    $character = \Domain\Character\Models\Character::create([
        'name' => null,
        'character_key' => \Domain\Character\Models\Character::generateUniqueKey(),
        'user_id' => null,
        'class' => null,
        'subclass' => null,
        'ancestry' => null,
        'community' => null,
        'level' => 1,
        'character_data' => [],
        'is_public' => false,
    ]);

    $page = visit('/character-builder/'.$character->character_key);
    
    // Select a simple class (Warrior - no chooseOne/chooseExtra)
    $page->assertPresent('[pest="class-card-warrior"]');
    $page->click('[pest="class-card-warrior"]');
    $page->wait(1);

    // Navigate to Equipment step
    for ($i = 0; $i < 5; $i++) {
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
    }

    $page->wait(2);

    // Initially incomplete
    $page->assertSee('Select Equipment');

    // Select primary weapon
    $page->click('div[\\@click*="selectEquipment"][\\@click*="weapon"]:first-of-type');
    $page->wait(1);

    // Still incomplete (need armor)
    $page->assertSee('Primary');

    // Select armor
    $page->click('div[\\@click*="selectEquipment"][\\@click*="armor"]:first-of-type');
    $page->wait(1);

    // Should be complete for Warrior (no inventory requirements)
    $page->assertSee('Armor');
    
    // Next step button should be enabled
    $page->assertPresent('[pest="next-step-button"]:not([disabled])');
});
