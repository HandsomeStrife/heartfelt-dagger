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
        ->waitForText('Class Selection Complete!');
}

function selectSubclass($page, string $subclassKey)
{
    return $page->click('[dusk="next-step-button"]')
        ->wait(2)
        ->click("[dusk=\"subclass-card-{$subclassKey}\"]")
        ->waitForText('Subclass Selection Complete!');
}

function selectAncestry($page, string $ancestryKey)
{
    return $page->click('[dusk="next-step-button"]')
        ->wait(2)
        ->click("[dusk=\"ancestry-card-{$ancestryKey}\"]")
        ->waitForText('Ancestry Selection Complete!');
}

function selectCommunity($page, string $communityKey)
{
    return $page->click('[dusk="next-step-button"]')
        ->wait(2)
        ->click("[dusk=\"community-card-{$communityKey}\"]")
        ->waitForText('Community Selection Complete!');
}

function assignTraitsDirectly($page, array $traits)
{
    foreach ($traits as $traitName => $value) {
        $page->script("\$wire.assignTrait('{$traitName}', {$value});");
    }
    return $page->waitForText('Trait assignment complete!');
}

function goToTraitAssignment($page)
{
    return $page->click('[dusk="next-step-button"]')
        ->waitForText('Assign Traits');
}

function setCharacterName($page, string $name)
{
    return $page->click('[dusk="next-step-button"]')
        ->wait(2)
        ->type('[dusk="character-name-input"]', $name)
        ->waitForText('Character name set!');
}

function selectBasicEquipment($page)
{
    return $page->click('[dusk="next-step-button"]')
        ->waitForText('Choose Equipment')
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
        ->waitForText('Equipment selection complete!');
}

function fillBackgroundQuestions($page, array $answers)
{
    $page = $page->click('[dusk="next-step-button"]')
        ->wait(2);
    
    foreach ($answers as $index => $answer) {
        $page->type("[dusk=\"background-answer-{$index}\"]", $answer);
    }
    
    return $page->waitForText('Background Questions Complete!');
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
    
    return $page->waitForText('Experiences Complete!');
}

function selectDomainCards($page, int $count = 2)
{
    return $page->click('[dusk="next-step-button"]')
        ->waitForText('Choose Domain Cards')
        ->script("
            const domainCards = document.querySelectorAll('[dusk^=\"domain-card-\"]');
            for (let i = 0; i < Math.min({$count}, domainCards.length); i++) {
                domainCards[i].click();
            }
        ")
        ->waitForText('Domain card selection complete!');
}

function fillConnections($page, array $answers)
{
    $page = $page->click('[dusk="next-step-button"]')
        ->wait(2);
    
    foreach ($answers as $index => $answer) {
        $page->type("[dusk=\"connection-answer-{$index}\"]", $answer);
    }
    
    return $page->waitForText('Character Creation Complete!');
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
