<?php

uses(\Tests\DuskTestCase::class);
declare(strict_types=1);
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
uses(\Illuminate\Foundation\Testing\DatabaseMigrations::class);

test('login page renders correctly in browser', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/login')
                ->assertSee('HeartfeltDagger')
                ->assertSee('Enter the Realm')
                ->assertPresent('form')
                ->assertPresent('input[type="email"]')
                ->assertPresent('input[type="password"]')
                ->assertPresent('input[type="checkbox"]')
                ->assertPresent('button[type="submit"]')
                ->assertSee('Create your legend');
    });
});
test('register page renders correctly in browser', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/register')
                ->assertSee('HeartfeltDagger')
                ->assertSee('Join the Adventure')
                ->assertPresent('form')
                ->assertPresent('input[type="text"]')
                ->assertPresent('input[type="email"]')
                ->assertPresent('input[type="password"]')
                ->assertPresent('button[type="submit"]')
                ->assertSee('Enter the realm');
    });
});
test('login form fields are interactive', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/login')
                ->type('#email', 'test@example.com')
                ->type('#password', 'password123')
                ->check('#remember')
                ->assertInputValue('#email', 'test@example.com')
                ->assertInputValue('#password', 'password123')
                ->assertChecked('#remember');
    });
});
test('register form fields are interactive', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/register')
                ->type('#username', 'testuser')
                ->type('#email', 'test@example.com')
                ->type('#password', 'password123')
                ->type('#password_confirmation', 'password123')
                ->assertInputValue('#username', 'testuser')
                ->assertInputValue('#email', 'test@example.com')
                ->assertInputValue('#password', 'password123')
                ->assertInputValue('#password_confirmation', 'password123');
    });
});
test('auth pages have proper styling', function () {
    $this->browse(function (Browser $browser) {
        // Test login page styling
        $browser->visit('/login')
                ->assertSee('HeartfeltDagger')
                ->assertPresent('form')
                ->assertPresent('input')
                ->assertPresent('button');

        // Test register page styling
        $browser->visit('/register')
                ->assertSee('HeartfeltDagger')
                ->assertPresent('form')
                ->assertPresent('input')
                ->assertPresent('button');
    });
});
test('navigation links work correctly', function () {
    $this->browse(function (Browser $browser) {
        // Test login to register navigation
        $browser->visit('/login')
                ->clickLink('Create your legend')
                ->assertPathIs('/register')
                ->assertSee('Join the Adventure');

        // Test register to login navigation
        $browser->clickLink('Enter the realm')
                ->assertPathIs('/login')
                ->assertSee('Enter the Realm');
    });
});
test('pages are responsive', function () {
    $this->browse(function (Browser $browser) {
        // Test mobile viewport
        $browser->resize(375, 667)
                ->visit('/login')
                ->assertSee('HeartfeltDagger')
                ->assertPresent('form')
                ->visit('/register')
                ->assertSee('HeartfeltDagger')
                ->assertPresent('form');

        // Test tablet viewport
        $browser->resize(768, 1024)
                ->visit('/login')
                ->assertSee('HeartfeltDagger')
                ->assertPresent('form')
                ->visit('/register')
                ->assertSee('HeartfeltDagger')
                ->assertPresent('form');

        // Test desktop viewport
        $browser->resize(1280, 720)
                ->visit('/login')
                ->assertSee('HeartfeltDagger')
                ->assertPresent('form')
                ->visit('/register')
                ->assertSee('HeartfeltDagger')
                ->assertPresent('form');
    });
});
test('decorative elements are present', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/login')
                ->assertPresent('svg')
                ->visit('/register')
                ->assertPresent('svg');
    });
});
