<?php

declare(strict_types=1);

namespace Tests\Browser;

use Domain\Character\Models\Character;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

class CharacterBuilderTest extends DuskTestCase
{
    use DatabaseMigrations;

    #[Test]
    public function user_can_access_character_builder(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/character-builder')
                ->assertSee('Character Builder')
                ->assertSee('Create your Daggerheart character')
                ->assertSee('Choose a Class')
                ->assertPresent('[dusk="tab-1"]')
                ->assertPresent('[dusk="progress-bar"]');
        });
    }

    #[Test]
    public function user_can_complete_class_selection(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/character-builder')
                ->assertSee('Choose Your Class')
                ->waitFor('[dusk="class-card-warrior"]', 10)
                ->click('[dusk="class-card-warrior"]')
                ->waitForText('Scroll down to choose your subclass', 10)
                ->scroll(0, 500)
                ->waitFor('[dusk="subclass-card-call of the brave"]', 5)
                ->click('[dusk="subclass-card-call of the brave"]')
                ->waitForText('Class Selection Complete!', 5)
                ->assertSee('Class Selection Complete!');
        });
    }

    #[Test]
    public function user_can_complete_heritage_selection(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/character-builder')
                ->completeClassSelection()
                ->click('[dusk="next-step-button"]')
                ->assertSee('Choose Heritage')
                ->waitFor('[dusk="ancestry-card-human"]', 10)
                ->click('[dusk="ancestry-card-human"]')
                ->waitFor('[dusk="community-card-wanderborne"]', 5)
                ->click('[dusk="community-card-wanderborne"]')
                ->waitFor('[dusk="completion-checkmark"]', 5)
                ->assertPresent('[dusk="completion-checkmark"]');
        });
    }

    #[Test]
    public function user_can_assign_traits_correctly(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/character-builder')
                ->completeClassSelection()
                ->completeHeritageSelection()
                ->click('[dusk="next-step-button"]')
                ->assertSee('Assign Traits')
                ->assignTraits()
                ->assertSee('Trait assignment complete!');
        });
    }

    #[Test]
    public function user_can_set_character_information(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/character-builder')
                ->completeClassSelection()
                ->completeHeritageSelection()
                ->completeTraitAssignment()
                ->click('[dusk="next-step-button"]')
                ->assertSee('Character Info')
                ->type('[dusk="character-name-input"]', 'Test Hero')
                ->waitForText('Character name set!', 5)
                ->assertSee('Character name set!');
        });
    }

    #[Test]
    public function user_can_select_equipment(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/character-builder')
                ->completeClassSelection()
                ->completeHeritageSelection()
                ->completeTraitAssignment()
                ->completeCharacterInfo()
                ->click('[dusk="next-step-button"]')
                ->assertSee('Starting Equipment')
                ->selectEquipment()
                ->assertSee('Equipment selection complete!');
        });
    }

    #[Test]
    public function user_can_complete_background_creation(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/character-builder')
                ->completeClassSelection()
                ->completeHeritageSelection()
                ->completeTraitAssignment()
                ->completeCharacterInfo()
                ->completeEquipmentSelection()
                ->click('[dusk="next-step-button"]')
                ->assertSee('Create Your Background')
                ->completeBackgroundQuestions()
                ->assertSee('Background Questions Complete!');
        });
    }

    #[Test]
    public function user_can_create_experiences(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/character-builder')
                ->completeClassSelection()
                ->completeHeritageSelection()
                ->completeTraitAssignment()
                ->completeCharacterInfo()
                ->completeEquipmentSelection()
                ->completeBackgroundCreation()
                ->click('[dusk="next-step-button"]')
                ->assertSee('Create Your Experiences')
                ->createExperiences()
                ->assertSee('Experiences Complete!');
        });
    }

    #[Test]
    public function user_can_select_domain_cards(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/character-builder')
                ->completeClassSelection()
                ->completeHeritageSelection()
                ->completeTraitAssignment()
                ->completeCharacterInfo()
                ->completeEquipmentSelection()
                ->completeBackgroundCreation()
                ->completeExperienceCreation()
                ->click('[dusk="next-step-button"]')
                ->assertSee('Select Domain Cards')
                ->selectDomainCards()
                ->assertSee('Domain card selection complete!');
        });
    }

    #[Test]
    public function user_can_complete_full_character_creation(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/character-builder')
                ->completeClassSelection()
                ->completeHeritageSelection()
                ->completeTraitAssignment()
                ->completeCharacterInfo()
                ->completeEquipmentSelection()
                ->completeBackgroundCreation()
                ->completeExperienceCreation()
                ->completeDomainCardSelection()
                ->click('[dusk="next-step-button"]')
                ->assertSee('Create Connections')
                ->completeConnections()
                ->assertSee('Character Creation Complete!')
                ->assertSee('Congratulations!');
        });
    }

    #[Test]
    public function user_can_save_completed_character(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/character-builder')
                ->completeFullCharacterCreation()
                ->click('[dusk="save-character-button"]')
                ->waitForText('Character saved successfully!', 10)
                ->assertSee('Character saved successfully!');
        });

        // Verify character was saved to database
        $this->assertDatabaseHas('characters', [
            'user_id' => $user->id,
            'name' => 'Test Hero',
            'class' => 'warrior',
        ]);
    }

    #[Test]
    public function user_can_edit_existing_character(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->complete()->forUser($user)->create([
            'name' => 'Existing Hero',
            'class' => 'warrior',
        ]);

        $this->browse(function (Browser $browser) use ($user, $character) {
            $browser->loginAs($user)
                ->visit("/character-builder/{$character->character_key}")
                ->assertSee('Existing Hero')
                ->assertSee('warrior')
                ->assertPresent('[dusk="completion-checkmark"]');
        });
    }

    #[Test]
    public function character_data_persists_in_browser_storage(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/character-builder')
                ->completeClassSelection()
                ->refresh()
                ->assertPresent('[dusk="completion-checkmark"]')
                ->assertSelected('[dusk="class-card-warrior"]');
        });
    }

    #[Test]
    public function user_cannot_proceed_without_completing_steps(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/character-builder')
                ->assertAttribute('[dusk="next-step-button"]', 'disabled', 'true')
                ->click('[dusk="class-card-warrior"]')
                ->assertAttribute('[dusk="next-step-button"]', 'disabled', 'true')
                ->scroll(0, 500)
                ->waitFor('[dusk="subclass-card-call of the brave"]', 5)
                ->click('[dusk="subclass-card-call of the brave"]')
                ->assertAttributeDoesntContain('[dusk="next-step-button"]', 'disabled', 'true');
        });
    }

    #[Test]
    public function progress_bar_updates_correctly(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/character-builder')
                ->assertSeeIn('[dusk="progress-percentage"]', '0%')
                ->completeClassSelection()
                ->assertNotSeeIn('[dusk="progress-percentage"]', '0%')
                ->completeHeritageSelection()
                ->completeTraitAssignment()
                ->completeCharacterInfo()
                ->completeEquipmentSelection()
                ->completeBackgroundCreation()
                ->completeExperienceCreation()
                ->completeDomainCardSelection()
                ->completeConnections()
                ->assertSeeIn('[dusk="progress-percentage"]', '100%');
        });
    }
}
