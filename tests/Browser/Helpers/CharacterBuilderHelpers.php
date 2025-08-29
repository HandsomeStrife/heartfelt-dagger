<?php

declare(strict_types=1);

namespace Tests\Browser\Helpers;

/**
 * Helper functions for character builder browser tests using Pest4.
 * These functions accept a page instance and perform common character builder actions.
 */

function completeClassSelection($page)
{
    return $page->waitFor('[dusk="class-card-warrior"]')
        ->click('[dusk="class-card-warrior"]')
        ->waitForText('Class Selection Complete!');
}

function completeSubclassSelection($page)
{
    return $page->click('[dusk="next-step-button"]')
        ->waitFor('[dusk="subclass-card-call of the brave"]')
        ->click('[dusk="subclass-card-call of the brave"]')
        ->waitForText('Subclass Selection Complete!');
}

function completeAncestrySelection($page)
{
    return $page->click('[dusk="next-step-button"]')
        ->waitFor('[dusk="ancestry-card-human"]')
        ->click('[dusk="ancestry-card-human"]')
        ->waitForText('Ancestry Selection Complete!');
}

function completeCommunitySelection($page)
{
    return $page->click('[dusk="next-step-button"]')
        ->waitFor('[dusk="community-card-wanderborne"]')
        ->click('[dusk="community-card-wanderborne"]')
        ->waitForText('Community Selection Complete!');
}

function assignTraits($page)
{
    return $page->click('[dusk="trait-value-agility-2"]')
        ->wait(0.5)
        ->click('[dusk="trait-value-strength-1"]')
        ->wait(0.5)
        ->click('[dusk="trait-value-finesse-1"]')
        ->wait(0.5)
        ->click('[dusk="trait-value-instinct-0"]')
        ->wait(0.5)
        ->click('[dusk="trait-value-presence-0"]')
        ->wait(0.5)
        ->click('[dusk="trait-value-knowledge--1"]')
        ->waitForText('Trait assignment complete!');
}

function completeTraitAssignment($page)
{
    return $page->click('[dusk="next-step-button"]')
        ->waitFor('[dusk="trait-card-agility"]')
        ->then(fn($page) => assignTraits($page));
}

function completeCharacterInfo($page)
{
    return $page->click('[dusk="next-step-button"]')
        ->waitFor('[dusk="character-name-input"]')
        ->type('[dusk="character-name-input"]', 'Test Hero')
        ->waitForText('Character name set!');
}

function selectEquipment($page)
{
    return $page->waitFor('[dusk="weapon-card-shortsword"]')
        ->click('[dusk="weapon-card-shortsword"]')
        ->wait(0.5)
        ->scrollIntoView('[dusk="armor-card-advanced leather armor"]')
        ->click('[dusk="armor-card-advanced leather armor"]')
        ->wait(0.5)
        ->scrollIntoView('[dusk="item-card-alistairs torch"]')
        ->click('[dusk="item-card-alistairs torch"]')
        ->waitForText('Equipment selection complete!');
}

function completeEquipmentSelection($page)
{
    return $page->click('[dusk="next-step-button"]')
        ->waitFor('[dusk="weapon-card-shortsword"]')
        ->then(fn($page) => selectEquipment($page));
}

function completeBackgroundQuestions($page)
{
    return $page->waitFor('[dusk="background-answer-0"]')
        ->type('[dusk="background-answer-0"]', 'My mentor taught me confidence through rigorous training and belief in my abilities.')
        ->wait(0.5)
        ->type('[dusk="background-answer-1"]', 'I once loved a fellow bard who betrayed my trust by stealing my original compositions.')
        ->wait(0.5)
        ->type('[dusk="background-answer-2"]', 'I idolize the legendary bard Lyralei for her ability to inspire hope in the darkest times.')
        ->waitForText('Background Questions Complete!');
}

function completeBackgroundCreation($page)
{
    return $page->click('[dusk="next-step-button"]')
        ->waitFor('[dusk="background-answer-0"]')
        ->then(fn($page) => completeBackgroundQuestions($page));
}

function createExperiences($page)
{
    return $page->waitFor('[dusk="new-experience-name"]')
        ->type('[dusk="new-experience-name"]', 'Combat Training')
        ->type('[dusk="new-experience-description"]', 'Extensive military training and battlefield experience')
        ->click('[dusk="add-experience-button"]')
        ->wait(1)
        ->type('[dusk="new-experience-name"]', 'Wilderness Survival')
        ->type('[dusk="new-experience-description"]', 'Years of living and thriving in the wild')
        ->click('[dusk="add-experience-button"]')
        ->waitForText('Experiences Complete!');
}

function completeExperienceCreation($page)
{
    return $page->click('[dusk="next-step-button"]')
        ->waitFor('[dusk="new-experience-name"]')
        ->then(fn($page) => createExperiences($page));
}

function selectDomainCards($page)
{
    return $page->waitFor('[dusk^="domain-card-"]')
        ->script('return document.querySelectorAll(\'[dusk^="domain-card-"]\')[0].click()')
        ->wait(1)
        ->script('return document.querySelectorAll(\'[dusk^="domain-card-"]\')[1].click()')
        ->waitForText('Domain card selection complete!');
}

function completeDomainCardSelection($page)
{
    return $page->click('[dusk="next-step-button"]')
        ->waitFor('[dusk^="domain-card-"]')
        ->then(fn($page) => selectDomainCards($page));
}

function completeConnections($page)
{
    return $page->waitFor('[dusk="connection-answer-0"]')
        ->type('[dusk="connection-answer-0"]', 'You saved my life in our first battle together, and I trust you completely.')
        ->wait(0.5)
        ->type('[dusk="connection-answer-1"]', 'Your constant humming during quiet moments both soothes and occasionally irritates me.')
        ->wait(0.5)
        ->type('[dusk="connection-answer-2"]', 'I reach for your hand when danger approaches because your presence gives me courage.')
        ->waitForText('Character Creation Complete!');
}

function completeFullCharacterCreation($page)
{
    return completeClassSelection($page)
        ->then(fn($page) => completeSubclassSelection($page))
        ->then(fn($page) => completeAncestrySelection($page))
        ->then(fn($page) => completeCommunitySelection($page))
        ->then(fn($page) => completeTraitAssignment($page))
        ->then(fn($page) => completeCharacterInfo($page))
        ->then(fn($page) => completeEquipmentSelection($page))
        ->then(fn($page) => completeBackgroundCreation($page))
        ->then(fn($page) => completeExperienceCreation($page))
        ->then(fn($page) => completeDomainCardSelection($page))
        ->click('[dusk="next-step-button"]')
        ->then(fn($page) => completeConnections($page));
}

function assertStepComplete($page, int $step)
{
    return $page->waitFor("[dusk=\"sidebar-tab-{$step}\"] [dusk=\"sidebar-completion-checkmark\"]")
        ->assertPresent("[dusk=\"sidebar-tab-{$step}\"] [dusk=\"sidebar-completion-checkmark\"]");
}

function goToStep($page, int $step)
{
    return $page->click("[dusk=\"sidebar-tab-{$step}\"]")
        ->waitFor('.step-content');
}

function assertCurrentStep($page, int $step)
{
    return $page->assertPresent("[dusk=\"sidebar-tab-{$step}\"].bg-gradient-to-r");
}

function waitForCharacterBuilderToLoad($page)
{
    return $page->waitFor('[dusk="progress-bar"]')
        ->waitFor('[dusk="sidebar-tab-1"]')
        ->waitFor('[dusk="character-summary"]');
}

function assertProgressPercentage($page, int $expectedPercentage)
{
    return $page->waitForText("{$expectedPercentage}% Complete")
        ->assertSee("{$expectedPercentage}% Complete");
}

function resetCharacter($page)
{
    return $page->scrollIntoView('[dusk="character-summary"]')
        ->click('button:contains("Reset Character")')
        ->acceptDialog()
        ->waitFor('[dusk="progress-bar"]');
}
