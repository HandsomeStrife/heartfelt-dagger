<?php

uses(\Tests\DuskTestCase::class);
declare(strict_types=1);
use Domain\Character\Models\Character;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
uses(\Illuminate\Foundation\Testing\DatabaseMigrations::class);

it('shows correct navigation for guest users', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
                ->assertSee('Create')
                ->assertSee('Characters')
                ->assertSee('Login')
                ->assertSee('Register');
    });
});
it('shows dropdown navigation for authenticated users', function () {
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
});
it('associates characters on login flow', function () {
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
        expect($character1->fresh()->user_id)->toEqual($user->id);
        expect($character2->fresh()->user_id)->toEqual($user->id);

        // Verify localStorage was cleared
        $localStorage = $browser->script("return localStorage.getItem('daggerheart_characters')")[0];
        expect($localStorage)->toBeNull();
    });
});
it('associates characters on registration flow', function () {
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
        expect($user)->not->toBeNull();

        // Verify characters are associated with the new user
        expect($character1->fresh()->user_id)->toEqual($user->id);
        expect($character2->fresh()->user_id)->toEqual($user->id);

        // Verify localStorage was cleared
        $localStorage = $browser->script("return localStorage.getItem('daggerheart_characters')")[0];
        expect($localStorage)->toBeNull();
    });
});
it('creates character as anonymous then associates on login', function () {
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
        expect($character)->not->toBeNull();
        expect($character->user_id)->toBeNull();

        // Now login
        $browser->visit('/login')
                ->type('email', 'test@example.com')
                ->type('password', 'password')
                ->press('Enter Realm')
                ->waitForLocation('/dashboard')
                ->assertPathIs('/dashboard');

        // Verify character is now associated with the user
        expect($character->fresh()->user_id)->toEqual($user->id);
    });
});
