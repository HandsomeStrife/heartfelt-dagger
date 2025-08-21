<?php

declare(strict_types=1);

namespace Tests\Browser\Macros;

use Laravel\Dusk\Browser;

class CharacterBuilderMacros
{
    public static function register(): void
    {
        Browser::macro('completeClassSelection', function () {
            return $this->waitFor('[dusk="class-card-warrior"]', 10)
                ->click('[dusk="class-card-warrior"]')
                ->waitForText('Scroll down to choose your subclass', 10)
                ->scroll(0, 500)
                ->waitFor('[dusk="subclass-card-call of the brave"]', 5)
                ->click('[dusk="subclass-card-call of the brave"]')
                ->waitForText('Class Selection Complete!', 5);
        });

        Browser::macro('completeHeritageSelection', function () {
            return $this->click('[dusk="next-step-button"]')
                ->waitFor('[dusk="ancestry-card-human"]', 10)
                ->click('[dusk="ancestry-card-human"]')
                ->waitFor('[dusk="community-card-wanderborne"]', 5)
                ->click('[dusk="community-card-wanderborne"]')
                ->waitFor('[dusk="completion-checkmark"]', 5);
        });

        Browser::macro('assignTraits', function () {
            return $this->click('[dusk="trait-value-agility-2"]')
                ->pause(500)
                ->click('[dusk="trait-value-strength-1"]')
                ->pause(500)
                ->click('[dusk="trait-value-finesse-1"]')
                ->pause(500)
                ->click('[dusk="trait-value-instinct-0"]')
                ->pause(500)
                ->click('[dusk="trait-value-presence-0"]')
                ->pause(500)
                ->click('[dusk="trait-value-knowledge--1"]')
                ->waitForText('Trait assignment complete!', 5);
        });

        Browser::macro('completeTraitAssignment', function () {
            return $this->click('[dusk="next-step-button"]')
                ->waitFor('[dusk="trait-card-agility"]', 10)
                ->assignTraits();
        });

        Browser::macro('completeCharacterInfo', function () {
            return $this->click('[dusk="next-step-button"]')
                ->waitFor('[dusk="character-name-input"]', 10)
                ->type('[dusk="character-name-input"]', 'Test Hero')
                ->waitForText('Character name set!', 5);
        });

        Browser::macro('selectEquipment', function () {
            return $this->waitFor('[dusk="weapon-card-shortsword"]', 10)
                ->click('[dusk="weapon-card-shortsword"]')
                ->pause(500)
                ->scrollIntoView('[dusk="armor-card-advanced leather armor"]')
                ->click('[dusk="armor-card-advanced leather armor"]')
                ->pause(500)
                ->scrollIntoView('[dusk="item-card-alistairs torch"]')
                ->click('[dusk="item-card-alistairs torch"]')
                ->waitForText('Equipment selection complete!', 5);
        });

        Browser::macro('completeEquipmentSelection', function () {
            return $this->click('[dusk="next-step-button"]')
                ->waitFor('[dusk="weapon-card-shortsword"]', 10)
                ->selectEquipment();
        });

        Browser::macro('completeBackgroundQuestions', function () {
            return $this->waitFor('[dusk="background-answer-0"]', 10)
                ->type('[dusk="background-answer-0"]', 'My mentor taught me confidence through rigorous training and belief in my abilities.')
                ->pause(500)
                ->type('[dusk="background-answer-1"]', 'I once loved a fellow bard who betrayed my trust by stealing my original compositions.')
                ->pause(500)
                ->type('[dusk="background-answer-2"]', 'I idolize the legendary bard Lyralei for her ability to inspire hope in the darkest times.')
                ->waitForText('Background Questions Complete!', 5);
        });

        Browser::macro('completeBackgroundCreation', function () {
            return $this->click('[dusk="next-step-button"]')
                ->waitFor('[dusk="background-answer-0"]', 10)
                ->completeBackgroundQuestions();
        });

        Browser::macro('createExperiences', function () {
            return $this->waitFor('[dusk="new-experience-name"]', 10)
                ->type('[dusk="new-experience-name"]', 'Combat Training')
                ->type('[dusk="new-experience-description"]', 'Extensive military training and battlefield experience')
                ->click('[dusk="add-experience-button"]')
                ->pause(1000)
                ->type('[dusk="new-experience-name"]', 'Wilderness Survival')
                ->type('[dusk="new-experience-description"]', 'Years of living and thriving in the wild')
                ->click('[dusk="add-experience-button"]')
                ->waitForText('Experiences Complete!', 5);
        });

        Browser::macro('completeExperienceCreation', function () {
            return $this->click('[dusk="next-step-button"]')
                ->waitFor('[dusk="new-experience-name"]', 10)
                ->createExperiences();
        });

        Browser::macro('selectDomainCards', function () {
            // Select first available domain card
            return $this->waitFor('[dusk^="domain-card-"]', 10)
                ->click($this->elements('[dusk^="domain-card-"]')[0])
                ->pause(1000)
                ->click($this->elements('[dusk^="domain-card-"]')[1])
                ->waitForText('Domain card selection complete!', 5);
        });

        Browser::macro('completeDomainCardSelection', function () {
            return $this->click('[dusk="next-step-button"]')
                ->waitFor('[dusk^="domain-card-"]', 10)
                ->selectDomainCards();
        });

        Browser::macro('completeConnections', function () {
            return $this->waitFor('[dusk="connection-answer-0"]', 10)
                ->type('[dusk="connection-answer-0"]', 'You saved my life in our first battle together, and I trust you completely.')
                ->pause(500)
                ->type('[dusk="connection-answer-1"]', 'Your constant humming during quiet moments both soothes and occasionally irritates me.')
                ->pause(500)
                ->type('[dusk="connection-answer-2"]', 'I reach for your hand when danger approaches because your presence gives me courage.')
                ->waitForText('Character Creation Complete!', 5);
        });

        Browser::macro('completeFullCharacterCreation', function () {
            return $this->completeClassSelection()
                ->completeHeritageSelection()
                ->completeTraitAssignment()
                ->completeCharacterInfo()
                ->completeEquipmentSelection()
                ->completeBackgroundCreation()
                ->completeExperienceCreation()
                ->completeDomainCardSelection()
                ->click('[dusk="next-step-button"]')
                ->completeConnections();
        });

        Browser::macro('assertStepComplete', function (int $step) {
            return $this->waitFor("[dusk=\"tab-{$step}\"] [dusk=\"completion-checkmark\"]", 5)
                ->assertPresent("[dusk=\"tab-{$step}\"] [dusk=\"completion-checkmark\"]");
        });

        Browser::macro('goToStep', function (int $step) {
            return $this->click("[dusk=\"tab-{$step}\"]")
                ->waitFor('.step-content', 3);
        });

        Browser::macro('assertCurrentStep', function (int $step) {
            return $this->assertPresent("[dusk=\"tab-{$step}\"].bg-gradient-to-r");
        });

        Browser::macro('waitForCharacterBuilderToLoad', function () {
            return $this->waitFor('[dusk="progress-bar"]', 10)
                ->waitFor('[dusk="tab-1"]', 5)
                ->waitFor('[dusk="character-summary"]', 5);
        });

        Browser::macro('assertProgressPercentage', function (int $expectedPercentage) {
            return $this->waitForText("{$expectedPercentage}% Complete", 5)
                ->assertSee("{$expectedPercentage}% Complete");
        });

        Browser::macro('resetCharacter', function () {
            return $this->scrollIntoView('[dusk="character-summary"]')
                ->click('button:contains("Reset Character")')
                ->acceptDialog()
                ->waitFor('[dusk="progress-bar"]', 3);
        });
    }
}
