<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

test('witch class appears in class selection with proper playtest markings', function () {
    $this->browse(function ($browser) {
        $browser->visit('/')
            ->assertSee('DaggerHeart Character Builder')
            ->within('[data-step="class-selection"]', function ($browser) {
                // Verify Witch class appears in the grid
                $browser->assertSee('Witch')
                    ->assertSee('Void - Playtest v1.5')
                    ->assertPresent('[data-class="witch"]')
                    ->within('[data-class="witch"]', function ($browser) {
                        $browser->assertSee('Witch')
                            ->assertSee('Dread & Sage')
                            ->assertSee('Void - Playtest v1.5');
});

test('witch class selection loads correct data and subclasses', function () {
    $this->browse(function ($browser) {
        $browser->visit('/')
            ->click('[data-class="witch"]')
            ->waitForText('Select a Subclass')
            ->assertSee('Witch')
            ->assertSee('Starting Evasion: 10')
            ->assertSee('Starting Hit Points: 6')
            ->assertSee('Domains: Dread, Sage')
            ->assertSee('Void - Playtest v1.5')
            ->within('[data-step="subclass-selection"]', function ($browser) {
                // Verify both subclasses are available
                $browser->assertSee('Hedge')
                    ->assertSee('Moon')
                    ->assertPresent('[data-subclass="hedge"]')
                    ->assertPresent('[data-subclass="moon"]');
});

test('witch hedge subclass selection displays complete information', function () {
    $this->browse(function ($browser) {
        $browser->visit('/')
            ->click('[data-class="witch"]')
            ->waitForText('Select a Subclass')
            ->click('[data-subclass="hedge"]')
            ->waitForText('Hedge')
            ->assertSee('Play the Hedge if you want to craft protective charms')
            ->assertSee('Spellcast Trait: Knowledge')
            ->assertSee('Void - Playtest v1.5')
            // Foundation features
            ->assertSee('Herbal Remedies')
            ->assertSee('increase the number cleared by one')
            ->assertSee('Tethered Talisman')
            ->assertSee('imbue a small item with your protective essence')
            // Specialization features
            ->assertSee('Walk Between Worlds')
            ->assertSee('step beyond the veil of death')
            ->assertSee('Enhanced Hex')
            ->assertSee('damage bonus equal to your Proficiency')
            // Mastery features
            ->assertSee('Circle of Power')
            ->assertSee('mark a circle on the ground');
});

test('witch moon subclass selection displays complete information', function () {
    $this->browse(function ($browser) {
        $browser->visit('/')
            ->click('[data-class="witch"]')
            ->waitForText('Select a Subclass')
            ->click('[data-subclass="moon"]')
            ->waitForText('Moon')
            ->assertSee('Play the Moon if you want to use illusion and lunar magic')
            ->assertSee('Spellcast Trait: Instinct')
            ->assertSee('Void - Playtest v1.5')
            // Foundation features
            ->assertSee('Night\'s Glamour')
            ->assertSee('Mark a Stress to Glamour yourself')
            ->assertSee('Disguise yourself to look like any creature')
            // Specialization features
            ->assertSee('Moonbeam')
            ->assertSee('conjure a column of moonlight')
            ->assertSee('Ire of Pale Light')
            ->assertSee('When a Hexed creature within Far range fails')
            // Mastery features
            ->assertSee('Lunar Phases')
            ->assertSee('phases of the moon');
});

test('witch class domain card selection works with dread and sage domains', function () {
    $character = Character::factory()->create([
        'selected_class' => 'witch',
        'selected_subclass' => 'hedge',
    ]);

    $this->browse(function ($browser) use ($character) {
        $browser->visitRoute('character.builder', ['character' => $character])
            ->clickAndWaitForStep('domain-card-selection')
            ->within('[data-step="domain-card-selection"]', function ($browser) {
                // Should show domain cards from both Dread and Sage domains
                $browser->assertSee('Choose 2 starting domain cards')
                    ->assertSee('Dread')
                    ->assertSee('Sage')
                    // Should show Dread domain cards
                    ->assertSee('Blighting Strike')
                    ->assertSee('Voice of Dread')
                    ->assertSee('Umbral Veil')
                    // Should show Sage domain cards
                    ->assertSee('Healing Hands')
                    ->assertSee('Nature\'s Call');
});

test('witch class hope feature and class features display correctly', function () {
    $this->browse(function ($browser) {
        $browser->visit('/')
            ->click('[data-class="witch"]')
            ->click('[data-subclass="hedge"]')
            ->waitForText('Character Summary')
            ->within('[data-section="hope-feature"]', function ($browser) {
                $browser->assertSee('Witch\'s Charm')
                    ->assertSee('spend 3 Hope to change it into a success with Fear');
            })
            ->within('[data-section="class-features"]', function ($browser) {
                $browser->assertSee('Hex')
                    ->assertSee('mark a Stress to Hex them')
                    ->assertSee('bonus equal to your tier')
                    ->assertSee('Commune')
                    ->assertSee('commune with an ancestor, deity, nature spirit');
});

test('witch class stats display correctly in character preview', function () {
    $character = Character::factory()->create([
        'selected_class' => 'witch',
        'selected_subclass' => 'moon',
        'agility' => 0,
        'strength' => -1,
        'finesse' => 0,
        'instinct' => 2,
        'presence' => 1,
        'knowledge' => 1,
    ]);

    $this->browse(function ($browser) use ($character) {
        $browser->visitRoute('character.builder', ['character' => $character])
            ->within('[data-section="character-stats"]', function ($browser) {
                $browser->assertSee('Evasion: 10')
                    ->assertSee('Hit Points: 6')
                    ->assertSee('Stress: 0')
                    ->assertSee('Hope: 2');
            })
            ->within('[data-section="character-traits"]', function ($browser) {
                $browser->assertSee('Instinct: +2') // Spellcast trait for Moon
                    ->assertSee('Presence: +1')
                    ->assertSee('Knowledge: +1');
});

test('witch playtest warning displays correctly throughout character builder', function () {
    $this->browse(function ($browser) {
        $browser->visit('/')
            ->click('[data-class="witch"]')
            ->within('[data-playtest-warning]', function ($browser) {
                $browser->assertSee('This class is part of DaggerHeart\'s Void - Playtest v1.5 content')
                    ->assertSee('may be subject to changes based on community feedback');
            })
            ->click('[data-subclass="hedge"]')
            ->within('[data-playtest-warning]', function ($browser) {
                $browser->assertSee('This subclass is part of DaggerHeart\'s Void - Playtest v1.5 content')
                    ->assertSee('may be subject to changes based on community feedback');
});

test('witch class inventory suggestions load correctly', function () {
    $this->browse(function ($browser) {
        $browser->visit('/')
            ->click('[data-class="witch"]')
            ->click('[data-subclass="hedge"]')
            ->clickAndWaitForStep('equipment-selection')
            ->within('[data-section="starting-inventory"]', function ($browser) {
                $browser->assertSee('torch')
                    ->assertSee('50 feet of rope')
                    ->assertSee('basic supplies')
                    ->assertSee('handful of gold')
                    ->assertSee('Minor Health Potion')
                    ->assertSee('Minor Stamina Potion')
                    ->assertSee('small harmless pet')
                    ->assertSee('talking skull')
                    ->assertSee('handwritten journal')
                    ->assertSee('runestones');
            })
            ->within('[data-section="suggested-equipment"]', function ($browser) {
                $browser->assertSee('Dualstaff')
                    ->assertSee('Instinct')
                    ->assertSee('Far')
                    ->assertSee('d6+3 mag')
                    ->assertSee('Gambeson Armor')
                    ->assertSee('Flexible: +1 to Evasion');
});

test('witch background and connection questions load correctly', function () {
    $this->browse(function ($browser) {
        $browser->visit('/')
            ->click('[data-class="witch"]')
            ->click('[data-subclass="moon"]')
            ->clickAndWaitForStep('background-selection')
            ->within('[data-section="background-questions"]', function ($browser) {
                $browser->assertSee('How did you first discover your affinity for magical craft?')
                    ->assertSee('You once used your power to help someone in a dire situation')
                    ->assertSee('Your magic once opened a door best left closed');
            })
            ->clickAndWaitForStep('connections')
            ->within('[data-section="connection-questions"]', function ($browser) {
                $browser->assertSee('What about my magical practice makes you most ill at ease?')
                    ->assertSee('I once appeared to you in a dream and shared a vision')
                    ->assertSee('Why do you come to me for advice?');
});

test('completed witch character shows all correct information', function () {
    $character = Character::factory()->create([
        'selected_class' => 'witch',
        'selected_subclass' => 'hedge',
        'agility' => 0,
        'strength' => -1,
        'finesse' => 0,
        'instinct' => 2,
        'presence' => 1,
        'knowledge' => 1,
    ]);

    // Add some domain cards from available domains
    $character->domainCards()->create([
        'domain' => 'dread',
        'ability_key' => 'blighting-strike',
        'ability_level' => 1,
    ]);

    $character->domainCards()->create([
        'domain' => 'sage',
        'ability_key' => 'healing-hands',
        'ability_level' => 1,
    ]);

    $this->browse(function ($browser) use ($character) {
        $browser->visitRoute('character.summary', ['character' => $character])
            ->assertSee('Witch (Hedge)')
            ->assertSee('Void - Playtest v1.5')
            ->assertSee('Evasion: 10')
            ->assertSee('Hit Points: 6')
            ->assertSee('Dread & Sage')
            ->assertSee('Knowledge') // Spellcast trait
            ->assertSee('Witch\'s Charm') // Hope feature
            ->assertSee('Hex') // Class features
            ->assertSee('Commune')
            ->assertSee('Herbal Remedies') // Subclass features
            ->assertSee('Tethered Talisman')
            ->assertSee('Blighting Strike') // Domain cards
            ->assertSee('Healing Hands');
});