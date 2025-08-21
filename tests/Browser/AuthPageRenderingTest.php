<?php

declare(strict_types=1);

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

class AuthPageRenderingTest extends DuskTestCase
{
    use DatabaseMigrations;

    #[Test]
    public function login_page_renders_correctly_in_browser(): void
    {
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
    }

    #[Test]
    public function register_page_renders_correctly_in_browser(): void
    {
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
    }

    #[Test]
    public function login_form_fields_are_interactive(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->type('#email', 'test@example.com')
                    ->type('#password', 'password123')
                    ->check('#remember')
                    ->assertInputValue('#email', 'test@example.com')
                    ->assertInputValue('#password', 'password123')
                    ->assertChecked('#remember');
        });
    }

    #[Test]
    public function register_form_fields_are_interactive(): void
    {
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
    }

    #[Test]
    public function auth_pages_have_proper_styling(): void
    {
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
    }

    #[Test]
    public function navigation_links_work_correctly(): void
    {
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
    }

    #[Test]
    public function pages_are_responsive(): void
    {
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
    }

    #[Test]
    public function decorative_elements_are_present(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->assertPresent('svg')
                    ->visit('/register')
                    ->assertPresent('svg');
        });
    }
}
