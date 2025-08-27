<?php

declare(strict_types=1);
test('user can see simiah evasion bonus in character stats', function () {
    $page = visit('/character-builder');
    
    $page
            ->assertPresent('@class-selection')
            ->click('@class-warrior')
            ->assertPresent('@heritage-selection')
            ->click('@ancestry-simiah')
            ->click('@community-wildborne')
            ->assertPresent('@character-info')
            ->waitUntilMissing('.loading')
            ->assertSeeIn('@character-stats', 'Evasion')
            ->assertSeeIn('@evasion-value', '10') // Base 9 + Simiah 1
            ->assertSeeIn('@evasion-bonus-indicator', '+1'); // Should show bonus indicator
});

test('user can see giant hit point bonus highlighted', function () {
    $page = visit('/character-builder');
    
    $page
            ->assertPresent('@class-selection')
            ->click('@class-warrior')
            ->assertPresent('@heritage-selection')
            ->click('@ancestry-giant')
            ->click('@community-ridgeborne')
            ->assertPresent('@character-info')
            ->waitUntilMissing('.loading')
            ->assertSeeIn('@character-stats', 'Hit Points')
            ->assertSeeIn('@hit-points-value', '8') // Base 7 + Giant 1
            ->assertSeeIn('@hit-points-bonus-indicator', '+1'); // Should show bonus indicator
});

test('user can see human stress bonus in stats', function () {
    $page = visit('/character-builder');
    
    $page
            ->assertPresent('@class-selection')
            ->click('@class-bard')
            ->assertPresent('@heritage-selection')
            ->click('@ancestry-human')
            ->click('@community-highborne')
            ->assertPresent('@character-info')
            ->waitUntilMissing('.loading')
            ->assertSeeIn('@character-stats', 'Stress')
            ->assertSeeIn('@stress-value', '7') // Base 6 + Human 1
            ->assertSeeIn('@stress-bonus-indicator', '+1'); // Should show bonus indicator
});

test('user can see galapa damage threshold bonus information', function () {
    $page = visit('/character-builder');
    
    $page
            ->assertPresent('@class-selection')
            ->click('@class-guardian')
            ->assertPresent('@heritage-selection')
            ->click('@ancestry-galapa')
            ->click('@community-seaborne')
            ->assertPresent('@character-info')
            ->waitUntilMissing('.loading')
            ->assertSeeIn('@character-stats', 'Damage Thresholds')
            ->assertSeeIn('@damage-threshold-bonus-text', 'Proficiency bonus applied'); // Should indicate Galapa bonus
});

test('ancestry bonus tooltips display correctly', function () {
    $page = visit('/character-builder');
    
    $page
            ->assertPresent('@class-selection')
            ->click('@class-warrior')
            ->assertPresent('@heritage-selection')
            ->click('@ancestry-simiah')
            ->click('@community-wildborne')
            ->assertPresent('@character-info')
            ->waitUntilMissing('.loading')
            ->mouseover('@evasion-bonus-indicator')
            ->assertSee('Simiah ancestry provides +1 Evasion at character creation')
            ->assertSee('Simiah ancestry provides +1 Evasion at character creation');
});

test('ancestry features are highlighted in heritage selection', function () {
    $page = visit('/character-builder');
    
    $page
            ->assertPresent('@class-selection')
            ->click('@class-warrior')
            ->assertPresent('@heritage-selection')
            ->click('@ancestry-simiah')
            ->assertSee('Ancestry Features')
            ->assertSeeIn('@ancestry-features', 'Gain a permanent +1 bonus to your Evasion')
            ->assertPresent('@ancestry-feature-bonus-highlight'); // Should have special highlighting
});

test('non bonus ancestry shows no bonus indicators', function () {
    $page = visit('/character-builder');
    
    $page
            ->assertPresent('@class-selection')
            ->click('@class-wizard')
            ->assertPresent('@heritage-selection')
            ->click('@ancestry-elf') // Elf has no stat bonuses
            ->click('@community-loreborne')
            ->assertPresent('@character-info')
            ->waitUntilMissing('.loading')
            ->assertSeeIn('@evasion-value', '9') // Just base value
            ->assertDontSee('@evasion-bonus-indicator') // No bonus indicator
            ->assertDontSee('@hit-points-bonus-indicator') // No bonus indicator
            ->assertDontSee('@stress-bonus-indicator'); // No bonus indicator
});

test('user can see ancestry bonus breakdown in character info', function () {
    $page = visit('/character-builder');
    
    $page
            ->assertPresent('@class-selection')
            ->click('@class-warrior')
            ->assertPresent('@heritage-selection')
            ->click('@ancestry-giant')
            ->click('@community-ridgeborne')
            ->assertPresent('@character-info')
            ->waitUntilMissing('.loading')
            ->assertSeeIn('@stat-breakdown', 'Base Hit Points: 7')
            ->assertSeeIn('@stat-breakdown', 'Giant Ancestry Bonus: +1')
            ->assertSeeIn('@stat-breakdown', 'Total Hit Points: 8');
});

test('multiple bonuses from different sources are visible', function () {
    $page = visit('/character-builder');
    
    $page
            ->assertPresent('@class-selection')
            ->click('@class-warrior')
            ->assertPresent('@heritage-selection')
            ->click('@ancestry-simiah')
            ->click('@community-wildborne')
            ->assertPresent('@trait-assignment')
            ->click('@assign-agility-2') // Assign +2 to agility for additional evasion
            ->click('@assign-strength-1')
            ->click('@assign-finesse-1')
            ->click('@assign-instinct-0')
            ->click('@assign-presence-0')
            ->click('@assign-knowledge--1')
            ->assertPresent('@character-info')
            ->waitUntilMissing('.loading')
            ->assertSeeIn('@evasion-value', '12') // Base 9 + Simiah 1 + Agility 2
            ->assertSeeIn('@stat-breakdown', 'Base Evasion: 9')
            ->assertSeeIn('@stat-breakdown', 'Simiah Ancestry Bonus: +1')
            ->assertSeeIn('@stat-breakdown', 'Agility Trait Modifier: +2')
            ->assertSeeIn('@stat-breakdown', 'Total Evasion: 12');
});

test('experience creation works with new property names', function () {
    $page = visit('/character-builder');
    
    $page
            ->assertPresent('@class-selection')
            ->click('@class-rogue')
            ->assertPresent('@heritage-selection')
            ->click('@ancestry-human')
            ->click('@community-slyborne')
            ->assertPresent('@experience-creation')
            ->type('@new-experience-name', 'Lockpicking')
            ->type('@new-experience-description', 'Expert at opening locks')
            ->click('@add-experience-button')
            ->assertSee('Lockpicking')
            ->assertSeeIn('@experience-card-0', 'Lockpicking')
            ->assertSeeIn('@experience-card-0', 'Expert at opening locks')
            ->assertSeeIn('@experience-card-0', '+2') // Modifier should be visible
            ->assertInputValue('@new-experience-name', ''); // Form should be cleared
});

test('ancestry bonus persists across character save and load', function () {
    $page = visit('/character-builder');
    
    $page
            ->assertPresent('@class-selection')
            ->click('@class-guardian')
            ->assertPresent('@heritage-selection')
            ->click('@ancestry-giant')
            ->click('@community-orderborne')
            ->assertPresent('@character-info')
            ->waitUntilMissing('.loading')
            ->assertSeeIn('@hit-points-value', '8') // Giant bonus applied
            ->click('@save-character-button')
            ->assertSee('Character saved successfully')
            ->refresh()
            ->assertPresent('@character-info')
            ->waitUntilMissing('.loading')
            ->assertSeeIn('@hit-points-value', '8') // Should still show Giant bonus
            ->assertSeeIn('@hit-points-bonus-indicator', '+1');
});

test('galapa damage threshold bonus scales with proficiency', function () {
    // This test would need a character with higher level to show proficiency scaling
    // For now, test that the UI shows the concept correctly
    $page = visit('/character-builder');
    
    $page
            ->assertPresent('@class-selection')
            ->click('@class-seraph')
            ->assertPresent('@heritage-selection')
            ->click('@ancestry-galapa')
            ->click('@community-seaborne')
            ->assertPresent('@character-info')
            ->waitUntilMissing('.loading')
            ->assertSeeIn('@character-stats', 'Damage Thresholds')
            ->mouseover('@damage-threshold-bonus-indicator')
            ->assertSee('Galapa ancestry provides damage threshold bonus equal to Proficiency')
            ->assertSee('Galapa ancestry provides damage threshold bonus equal to Proficiency');
});

test('character builder shows clear visual hierarchy for bonuses', function () {
    $page = visit('/character-builder');
    
    $page
            ->assertPresent('@class-selection')
            ->click('@class-warrior')
            ->assertPresent('@heritage-selection')
            ->click('@ancestry-simiah')
            ->click('@community-wildborne')
            ->assertPresent('@character-info')
            ->waitUntilMissing('.loading')
            // Check that bonus indicators have proper styling
            ->assertHasClass('@evasion-bonus-indicator', 'text-amber-400') // Amber text for bonuses
            ->assertHasClass('@evasion-bonus-indicator', 'font-bold') // Bold for emphasis
            ->assertPresent('@evasion-bonus-sparkle'); // Sparkle effect for bonuses
