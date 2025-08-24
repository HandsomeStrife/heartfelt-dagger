<?php

uses(\Tests\DuskTestCase::class);
declare(strict_types=1);
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
test('user can see simiah evasion bonus in character stats', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/character-builder')
            ->waitFor('@class-selection')
            ->click('@class-warrior')
            ->waitFor('@heritage-selection')
            ->click('@ancestry-simiah')
            ->click('@community-wildborne')
            ->waitFor('@character-info')
            ->waitUntilMissing('.loading')
            ->assertSeeIn('@character-stats', 'Evasion')
            ->assertSeeIn('@evasion-value', '10') // Base 9 + Simiah 1
            ->assertSeeIn('@evasion-bonus-indicator', '+1'); // Should show bonus indicator
    });
});
test('user can see giant hit point bonus highlighted', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/character-builder')
            ->waitFor('@class-selection')
            ->click('@class-warrior')
            ->waitFor('@heritage-selection')
            ->click('@ancestry-giant')
            ->click('@community-ridgeborne')
            ->waitFor('@character-info')
            ->waitUntilMissing('.loading')
            ->assertSeeIn('@character-stats', 'Hit Points')
            ->assertSeeIn('@hit-points-value', '8') // Base 7 + Giant 1
            ->assertSeeIn('@hit-points-bonus-indicator', '+1'); // Should show bonus indicator
    });
});
test('user can see human stress bonus in stats', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/character-builder')
            ->waitFor('@class-selection')
            ->click('@class-bard')
            ->waitFor('@heritage-selection')
            ->click('@ancestry-human')
            ->click('@community-highborne')
            ->waitFor('@character-info')
            ->waitUntilMissing('.loading')
            ->assertSeeIn('@character-stats', 'Stress')
            ->assertSeeIn('@stress-value', '7') // Base 6 + Human 1
            ->assertSeeIn('@stress-bonus-indicator', '+1'); // Should show bonus indicator
    });
});
test('user can see galapa damage threshold bonus information', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/character-builder')
            ->waitFor('@class-selection')
            ->click('@class-guardian')
            ->waitFor('@heritage-selection')
            ->click('@ancestry-galapa')
            ->click('@community-seaborne')
            ->waitFor('@character-info')
            ->waitUntilMissing('.loading')
            ->assertSeeIn('@character-stats', 'Damage Thresholds')
            ->assertSeeIn('@damage-threshold-bonus-text', 'Proficiency bonus applied'); // Should indicate Galapa bonus
    });
});
test('ancestry bonus tooltips display correctly', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/character-builder')
            ->waitFor('@class-selection')
            ->click('@class-warrior')
            ->waitFor('@heritage-selection')
            ->click('@ancestry-simiah')
            ->click('@community-wildborne')
            ->waitFor('@character-info')
            ->waitUntilMissing('.loading')
            ->mouseover('@evasion-bonus-indicator')
            ->waitForText('Simiah ancestry provides +1 Evasion at character creation')
            ->assertSee('Simiah ancestry provides +1 Evasion at character creation');
    });
});
test('ancestry features are highlighted in heritage selection', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/character-builder')
            ->waitFor('@class-selection')
            ->click('@class-warrior')
            ->waitFor('@heritage-selection')
            ->click('@ancestry-simiah')
            ->waitForText('Ancestry Features')
            ->assertSeeIn('@ancestry-features', 'Gain a permanent +1 bonus to your Evasion')
            ->assertPresent('@ancestry-feature-bonus-highlight'); // Should have special highlighting
    });
});
test('non bonus ancestry shows no bonus indicators', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/character-builder')
            ->waitFor('@class-selection')
            ->click('@class-wizard')
            ->waitFor('@heritage-selection')
            ->click('@ancestry-elf') // Elf has no stat bonuses
            ->click('@community-loreborne')
            ->waitFor('@character-info')
            ->waitUntilMissing('.loading')
            ->assertSeeIn('@evasion-value', '9') // Just base value
            ->assertDontSee('@evasion-bonus-indicator') // No bonus indicator
            ->assertDontSee('@hit-points-bonus-indicator') // No bonus indicator
            ->assertDontSee('@stress-bonus-indicator'); // No bonus indicator
    });
});
test('user can see ancestry bonus breakdown in character info', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/character-builder')
            ->waitFor('@class-selection')
            ->click('@class-warrior')
            ->waitFor('@heritage-selection')
            ->click('@ancestry-giant')
            ->click('@community-ridgeborne')
            ->waitFor('@character-info')
            ->waitUntilMissing('.loading')
            ->assertSeeIn('@stat-breakdown', 'Base Hit Points: 7')
            ->assertSeeIn('@stat-breakdown', 'Giant Ancestry Bonus: +1')
            ->assertSeeIn('@stat-breakdown', 'Total Hit Points: 8');
    });
});
test('multiple bonuses from different sources are visible', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/character-builder')
            ->waitFor('@class-selection')
            ->click('@class-warrior')
            ->waitFor('@heritage-selection')
            ->click('@ancestry-simiah')
            ->click('@community-wildborne')
            ->waitFor('@trait-assignment')
            ->click('@assign-agility-2') // Assign +2 to agility for additional evasion
            ->click('@assign-strength-1')
            ->click('@assign-finesse-1')
            ->click('@assign-instinct-0')
            ->click('@assign-presence-0')
            ->click('@assign-knowledge--1')
            ->waitFor('@character-info')
            ->waitUntilMissing('.loading')
            ->assertSeeIn('@evasion-value', '12') // Base 9 + Simiah 1 + Agility 2
            ->assertSeeIn('@stat-breakdown', 'Base Evasion: 9')
            ->assertSeeIn('@stat-breakdown', 'Simiah Ancestry Bonus: +1')
            ->assertSeeIn('@stat-breakdown', 'Agility Trait Modifier: +2')
            ->assertSeeIn('@stat-breakdown', 'Total Evasion: 12');
    });
});
test('experience creation works with new property names', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/character-builder')
            ->waitFor('@class-selection')
            ->click('@class-rogue')
            ->waitFor('@heritage-selection')
            ->click('@ancestry-human')
            ->click('@community-slyborne')
            ->waitFor('@experience-creation')
            ->type('@new-experience-name', 'Lockpicking')
            ->type('@new-experience-description', 'Expert at opening locks')
            ->click('@add-experience-button')
            ->waitForText('Lockpicking')
            ->assertSeeIn('@experience-card-0', 'Lockpicking')
            ->assertSeeIn('@experience-card-0', 'Expert at opening locks')
            ->assertSeeIn('@experience-card-0', '+2') // Modifier should be visible
            ->assertInputValue('@new-experience-name', ''); // Form should be cleared
    });
});
test('ancestry bonus persists across character save and load', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/character-builder')
            ->waitFor('@class-selection')
            ->click('@class-guardian')
            ->waitFor('@heritage-selection')
            ->click('@ancestry-giant')
            ->click('@community-orderborne')
            ->waitFor('@character-info')
            ->waitUntilMissing('.loading')
            ->assertSeeIn('@hit-points-value', '8') // Giant bonus applied
            ->click('@save-character-button')
            ->waitForText('Character saved successfully')
            ->refresh()
            ->waitFor('@character-info')
            ->waitUntilMissing('.loading')
            ->assertSeeIn('@hit-points-value', '8') // Should still show Giant bonus
            ->assertSeeIn('@hit-points-bonus-indicator', '+1');
    });
});
test('galapa damage threshold bonus scales with proficiency', function () {
    // This test would need a character with higher level to show proficiency scaling
    // For now, test that the UI shows the concept correctly
    $this->browse(function (Browser $browser) {
        $browser->visit('/character-builder')
            ->waitFor('@class-selection')
            ->click('@class-seraph')
            ->waitFor('@heritage-selection')
            ->click('@ancestry-galapa')
            ->click('@community-seaborne')
            ->waitFor('@character-info')
            ->waitUntilMissing('.loading')
            ->assertSeeIn('@character-stats', 'Damage Thresholds')
            ->mouseover('@damage-threshold-bonus-indicator')
            ->waitForText('Galapa ancestry provides damage threshold bonus equal to Proficiency')
            ->assertSee('Galapa ancestry provides damage threshold bonus equal to Proficiency');
    });
});
test('character builder shows clear visual hierarchy for bonuses', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/character-builder')
            ->waitFor('@class-selection')
            ->click('@class-warrior')
            ->waitFor('@heritage-selection')
            ->click('@ancestry-simiah')
            ->click('@community-wildborne')
            ->waitFor('@character-info')
            ->waitUntilMissing('.loading')
            // Check that bonus indicators have proper styling
            ->assertHasClass('@evasion-bonus-indicator', 'text-amber-400') // Amber text for bonuses
            ->assertHasClass('@evasion-bonus-indicator', 'font-bold') // Bold for emphasis
            ->assertPresent('@evasion-bonus-sparkle'); // Sparkle effect for bonuses
    });
});
