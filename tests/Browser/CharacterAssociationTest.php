<?php

declare(strict_types=1);

namespace Tests\Browser;

use Domain\Character\Models\Character;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

class CharacterAssociationTest extends DuskTestCase
{
    use DatabaseMigrations;

    #[Test]
    public function it_shows_correct_navigation_for_guest_users(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSee('Create')
                    ->assertSee('Characters')
                    ->assertSee('Login')
                    ->assertSee('Register');
        });
    }

    #[Test]
    public function it_shows_dropdown_navigation_for_authenticated_users(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/')
                    ->assertDontSee('Login')
                    ->assertDontSee('Register')
                    ->assertSee($user->username)
                    ->click('button') // Click the dropdown button
                    ->waitFor('.absolute') // Wait for dropdown to appear
                    ->assertSee('Create Character')
                    ->assertSee('My Characters')
                    ->assertSee('Dashboard')
                    ->assertSee('Campaigns')
                    ->assertSee('Logout');
        });
    }

    #[Test]
    public function it_associates_characters_on_login_flow(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Create anonymous characters
        $character1 = Character::factory()->create(['user_id' => null]);
        $character2 = Character::factory()->create(['user_id' => null]);

        $this->browse(function (Browser $browser) use ($user, $character1, $character2) {
            $browser->visit('/login')
                    ->script([
                        "localStorage.setItem('daggerheart_characters', JSON.stringify(['{$character1->character_key}', '{$character2->character_key}']))"
                    ])
                    ->type('email', 'test@example.com')
                    ->type('password', 'password')
                    ->press('Enter Realm')
                    ->waitForLocation('/dashboard')
                    ->assertPathIs('/dashboard');

            // Verify characters are associated with the user
            $this->assertEquals($user->id, $character1->fresh()->user_id);
            $this->assertEquals($user->id, $character2->fresh()->user_id);

            // Verify localStorage was cleared
            $localStorage = $browser->script("return localStorage.getItem('daggerheart_characters')")[0];
            $this->assertNull($localStorage);
        });
    }

    #[Test]
    public function it_associates_characters_on_registration_flow(): void
    {
        // Create anonymous characters
        $character1 = Character::factory()->create(['user_id' => null]);
        $character2 = Character::factory()->create(['user_id' => null]);

        $this->browse(function (Browser $browser) use ($character1, $character2) {
            $browser->visit('/register')
                    ->script([
                        "localStorage.setItem('daggerheart_characters', JSON.stringify(['{$character1->character_key}', '{$character2->character_key}']))"
                    ])
                    ->type('username', 'testuser')
                    ->type('email', 'newuser@example.com')
                    ->type('password', 'password')
                    ->type('password_confirmation', 'password')
                    ->press('Begin Adventure')
                    ->waitForLocation('/dashboard')
                    ->assertPathIs('/dashboard');

            // Get the newly created user
            $user = User::where('email', 'newuser@example.com')->first();
            $this->assertNotNull($user);

            // Verify characters are associated with the new user
            $this->assertEquals($user->id, $character1->fresh()->user_id);
            $this->assertEquals($user->id, $character2->fresh()->user_id);

            // Verify localStorage was cleared
            $localStorage = $browser->script("return localStorage.getItem('daggerheart_characters')")[0];
            $this->assertNull($localStorage);
        });
    }

    #[Test]
    public function it_creates_character_as_anonymous_then_associates_on_login(): void
    {
        // Create a user for later login
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            // Create a character as anonymous user
            $browser->visit('/character-builder')
                    ->waitFor('[data-step="1"]') // Wait for character builder to load
                    ->assertPathBeginsWith('/character-builder/');

            // Get the character key from the URL
            $currentUrl = $browser->driver->getCurrentURL();
            $characterKey = basename(parse_url($currentUrl, PHP_URL_PATH));

            // Verify character was created as anonymous
            $character = Character::where('character_key', $characterKey)->first();
            $this->assertNotNull($character);
            $this->assertNull($character->user_id);

            // Now login
            $browser->visit('/login')
                    ->type('email', 'test@example.com')
                    ->type('password', 'password')
                    ->press('Enter Realm')
                    ->waitForLocation('/dashboard')
                    ->assertPathIs('/dashboard');

            // Verify character is now associated with the user
            $this->assertEquals($user->id, $character->fresh()->user_id);
        });
    }
}
