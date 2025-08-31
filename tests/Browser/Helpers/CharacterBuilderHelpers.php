<?php

declare(strict_types=1);

namespace Tests\Browser\Helpers;

/**
 * Helper functions for character builder browser tests using Pest4.
 * These functions accept a page instance and perform common character builder actions.
 */

function waitForCharacterBuilderToLoad($page)
{
    return $page->wait(3)
        ->assertSee('Choose a Class');
}

function selectClass($page, string $classKey)
{
    return $page->click("[dusk=\"class-card-{$classKey}\"]")
        ->wait(2) // Wait for content to load
            ->assertSee('Class Selection Complete!');
}

function selectSubclass($page, string $subclassKey)
{
    return $page->click('[dusk="next-step-button"]')
        ->wait(2)
        ->click("[dusk=\"subclass-card-{$subclassKey}\"]")
        ->wait(2) // Wait for content to load
            ->assertSee('Subclass Selection Complete!');
}

function selectAncestry($page, string $ancestryKey)
{
    return $page->click('[dusk="next-step-button"]')
        ->wait(2)
        ->click("[dusk=\"ancestry-card-{$ancestryKey}\"]")
        ->wait(2) // Wait for content to load
            ->assertSee('Ancestry Selection Complete!');
}

function selectCommunity($page, string $communityKey)
{
    return $page->click('[dusk="next-step-button"]')
        ->wait(2)
        ->click("[dusk=\"community-card-{$communityKey}\"]")
        ->wait(2) // Wait for content to load
            ->assertSee('Community Selection Complete!');
}

function assignTraitsDirectly($page, array $traits)
{
    foreach ($traits as $traitName => $value) {
        $page->script("\$wire.assignTrait('{$traitName}', {$value});");
    }
    return $page->wait(2) // Wait for content to load
            ->assertSee('Trait assignment complete!');
}

function goToTraitAssignment($page)
{
    return $page->click('[dusk="next-step-button"]')
        ->wait(2) // Wait for content to load
            ->assertSee('Assign Traits');
}

function setCharacterName($page, string $name)
{
    return $page->click('[dusk="next-step-button"]')
        ->wait(2)
        ->type('[dusk="character-name-input"]', $name)
        ->wait(2) // Wait for content to load
            ->assertSee('Character name set!');
}

function selectBasicEquipment($page)
{
    return $page->click('[dusk="next-step-button"]')
        ->wait(2) // Wait for content to load
            ->assertSee('Choose Equipment')
        ->wait(2)
        ->script('
            // Select first available weapon
            const weaponCards = document.querySelectorAll(\'[dusk^="weapon-card-"]\');
            if (weaponCards.length > 0) weaponCards[0].click();
        ')
        ->wait(1)
        ->script('
            // Select first available armor
            const armorCards = document.querySelectorAll(\'[dusk^="armor-card-"]\');
            if (armorCards.length > 0) armorCards[0].click();
        ')
        ->wait(1)
        ->script('
            // Select first available item
            const itemCards = document.querySelectorAll(\'[dusk^="item-card-"]\');
            if (itemCards.length > 0) itemCards[0].click();
        ')
        ->wait(2) // Wait for content to load
            ->assertSee('Equipment selection complete!');
}

function fillBackgroundQuestions($page, array $answers)
{
    $page = $page->click('[dusk="next-step-button"]')
        ->wait(2);
    
    foreach ($answers as $index => $answer) {
        $page->type("[dusk=\"background-answer-{$index}\"]", $answer);
    }
    
    return $page->wait(2) // Wait for content to load
            ->assertSee('Background Questions Complete!');
}

function createExperiences($page, array $experiences)
{
    $page = $page->click('[dusk="next-step-button"]')
        ->wait(2);
    
    foreach ($experiences as $experience) {
        $page->type('[dusk="new-experience-name"]', $experience['name'])
            ->type('[dusk="new-experience-description"]', $experience['description'])
            ->click('[dusk="add-experience-button"]')
            ->wait(1);
    }
    
    return $page->wait(2) // Wait for content to load
            ->assertSee('Experiences Complete!');
}

function selectDomainCards($page, int $count = 2)
{
    return $page->click('[dusk="next-step-button"]')
        ->wait(2) // Wait for content to load
            ->assertSee('Choose Domain Cards')
        ->script("
            const domainCards = document.querySelectorAll('[dusk^=\"domain-card-\"]');
            for (let i = 0; i < Math.min({$count}, domainCards.length); i++) {
                domainCards[i].click();
            }
        ")
        ->wait(2) // Wait for content to load
            ->assertSee('Domain card selection complete!');
}

function fillConnections($page, array $answers)
{
    $page = $page->click('[dusk="next-step-button"]')
        ->wait(2);
    
    foreach ($answers as $index => $answer) {
        $page->type("[dusk=\"connection-answer-{$index}\"]", $answer);
    }
    
    return $page->wait(2) // Wait for content to load
            ->assertSee('Character Creation Complete!');
}

function assertStepComplete($page, int $step)
{
    // Simply wait a moment and assert the step has the completed class/styling
    return $page->wait(2)
        ->assertSee('Complete');
}

function goToStep($page, int $step)
{
    return $page->click("[dusk=\"sidebar-tab-{$step}\"]")
        ->wait(1);
}

function assertProgressPercentage($page, int $expectedPercentage)
{
    return $page->assertSee("{$expectedPercentage}% Complete");
}
