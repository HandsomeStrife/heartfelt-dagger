<?php

declare(strict_types=1);

use Domain\User\Models\User;
use Laravel\Dusk\Browser;

describe('Reference Navigation Browser Tests', function () {
    test('user can navigate through reference sections', function () {
        visit('/')
            ->clickLink('DaggerHeart Reference')
            ->assertPathIs('/reference')
            ->assertSee('DaggerHeart Reference')
            ->assertSee('Complete System Reference Document')
            ->click('[data-section="introduction"]')
            ->assertPathIs('/reference/introduction')
            ->assertSee('Introduction');
    });

    test('user can view individual reference pages', function () {
        visit('/reference')
            ->click('[data-section="introduction"]')
            ->assertPathIs('/reference/introduction')
            ->clickLink('The Basics')
            ->assertPathIs('/reference/introduction/the-basics')
            ->assertSee('THE BASICS');
    });

    test('breadcrumb navigation works correctly', function () {
        visit('/reference/introduction/the-basics')
            ->assertSee('Reference')
            ->assertSee('Introduction')
            ->assertSee('The Basics')
            ->clickLink('Reference')
            ->assertPathIs('/reference');
    });

    test('sidebar navigation works on reference pages', function () {
        visit('/reference/introduction/the-basics')
            ->assertSee('Introduction')
            ->clickLink('What Is This')
            ->assertPathIs('/reference/introduction/what-is-this');
    });

    test('mobile navigation includes reference link', function () {
        visit('/')
            ->resize(375, 667) // Mobile viewport
            ->click('[data-mobile-menu]')
            ->assertSee('DaggerHeart Reference')
            ->clickLink('DaggerHeart Reference')
            ->assertPathIs('/reference');
    });

    test('reference quick links work from home page', function () {
        visit('/reference')
            ->assertSee('New to DaggerHeart?')
            ->clickLink('The Basics')
            ->assertPathIs('/reference/introduction/the-basics')
            ->assertSee('THE BASICS');
    });

    test('search functionality works if implemented', function () {
        // This test can be expanded when search is implemented
        visit('/reference')
            ->assertSee('DaggerHeart Reference');
    });
})->group('browser');

