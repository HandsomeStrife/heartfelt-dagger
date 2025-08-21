<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PageRenderingTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function login_page_renders_successfully(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200)
                ->assertSee('HeartfeltDagger')
                ->assertSee('Enter the Realm')
                ->assertSee('Email')
                ->assertSee('Password')
                ->assertSee('Enter the Realm')
                ->assertSee('Create your legend');
    }

    #[Test]
    public function register_page_renders_successfully(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200)
                ->assertSee('HeartfeltDagger')
                ->assertSee('Join the Adventure')
                ->assertSee('Username')
                ->assertSee('Email')
                ->assertSee('Password')
                ->assertSee('Confirm Password')
                ->assertSee('Begin Adventure')
                ->assertSee('Enter the realm');
    }

    #[Test]
    public function login_page_contains_proper_form_elements(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200)
                ->assertSee('wire:submit="login"', false)
                ->assertSee('wire:model="form.email"', false)
                ->assertSee('wire:model="form.password"', false)
                ->assertSee('wire:model="form.remember"', false)
                ->assertSee('type="email"', false)
                ->assertSee('type="password"', false)
                ->assertSee('type="checkbox"', false);
    }

    #[Test]
    public function register_page_contains_proper_form_elements(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200)
                ->assertSee('wire:submit="register"', false)
                ->assertSee('wire:model="form.username"', false)
                ->assertSee('wire:model="form.email"', false)
                ->assertSee('wire:model="form.password"', false)
                ->assertSee('wire:model="form.password_confirmation"', false)
                ->assertSee('type="text"', false)
                ->assertSee('type="email"', false)
                ->assertSee('type="password"', false);
    }

    #[Test]
    public function login_page_has_proper_navigation_links(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200)
                ->assertSee('href="/register"', false)
                ->assertSee('Create your legend');
    }

    #[Test]
    public function register_page_has_proper_navigation_links(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200)
                ->assertSee('href="/login"', false)
                ->assertSee('Enter the realm');
    }

    #[Test]
    public function auth_pages_have_proper_css_classes(): void
    {
        $loginResponse = $this->get('/login');
        $registerResponse = $this->get('/register');

        // Check for gradient backgrounds
        $loginResponse->assertSee('bg-gradient-to-br from-slate-900 via-purple-900 to-slate-800', false);
        $registerResponse->assertSee('bg-gradient-to-br from-slate-900 via-purple-900 to-slate-800', false);

        // Check for form styling
        $loginResponse->assertSee('bg-gradient-to-br from-slate-800 to-slate-900', false);
        $registerResponse->assertSee('bg-gradient-to-br from-slate-800 to-slate-900', false);

        // Check for button styling
        $loginResponse->assertSee('bg-gradient-to-r from-amber-500 to-yellow-500', false);
        $registerResponse->assertSee('bg-gradient-to-r from-amber-500 to-yellow-500', false);
    }

    #[Test]
    public function auth_pages_include_decorative_elements(): void
    {
        $loginResponse = $this->get('/login');
        $registerResponse = $this->get('/register');

        // Check for SVG stars
        $starPath = 'M12 2L13.5 8.5L20 10L13.5 11.5L12 18L10.5 11.5L4 10L10.5 8.5L12 2Z';
        
        $loginResponse->assertSee($starPath, false);
        $registerResponse->assertSee($starPath, false);
    }

    #[Test]
    public function auth_pages_use_correct_fonts(): void
    {
        $loginResponse = $this->get('/login');
        $registerResponse = $this->get('/register');

        // Check for fantasy font usage
        $loginResponse->assertSee('font-federant', false);
        $registerResponse->assertSee('font-federant', false);

        // Check for roboto font usage
        $loginResponse->assertSee('font-roboto', false);
        $registerResponse->assertSee('font-roboto', false);
    }

    #[Test]
    public function auth_pages_have_loading_states(): void
    {
        $loginResponse = $this->get('/login');
        $registerResponse = $this->get('/register');

        // Check for wire:loading directives
        $loginResponse->assertSee('wire:loading.remove', false)
                    ->assertSee('wire:loading', false)
                    ->assertSee('wire:loading.attr="disabled"', false);

        $registerResponse->assertSee('wire:loading.remove', false)
                        ->assertSee('wire:loading', false)
                        ->assertSee('wire:loading.attr="disabled"', false);

        // Check for loading spinner
        $loginResponse->assertSee('animate-spin', false);
        $registerResponse->assertSee('animate-spin', false);
    }
}
