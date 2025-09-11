<?php

declare(strict_types=1);

use App\Http\Controllers\ReferenceController;

describe('ReferenceController', function () {
    beforeEach(function () {
        // Ensure test routes are available
        $this->app['router']->get('/reference', [ReferenceController::class, 'index'])->name('reference.index');
        $this->app['router']->get('/reference/{page}', [ReferenceController::class, 'show'])->name('reference.page');
    });

    describe('index', function () {
        test('displays reference index page successfully', function () {
            $response = $this->get(route('reference.index'));

            $response->assertStatus(200)
                ->assertViewIs('reference.index')
                ->assertViewHas('pages')
                ->assertSee('DaggerHeart Reference')
                ->assertSee('Official System Reference Document');
        });

        test('includes all expected pages in sidebar', function () {
            $response = $this->get(route('reference.index'));

            $pages = $response->viewData('pages');

            expect($pages)->toHaveKey('what-is-this')
                ->and($pages)->toHaveKey('the-basics')
                ->and($pages)->toHaveKey('character-creation')
                ->and($pages)->toHaveKey('domains')
                ->and($pages)->toHaveKey('classes')
                ->and($pages)->toHaveKey('combat')
                ->and($pages)->toHaveKey('gm-guidance');
        });

        test('displays default page content', function () {
            $response = $this->get(route('reference.index'));

            // Index now shows the 'what-is-this' page by default
            $response->assertSee('What Is This')
                ->assertViewHas('current_page', 'what-is-this')
                ->assertViewHas('content_view');
        });

        test('redirects to first page by default', function () {
            $response = $this->get(route('reference.index'));

            // Should redirect to 'what-is-this' page by default
            $response->assertViewHas('current_page', 'what-is-this');
        });
    });

    describe('page view', function () {
        test('displays page content successfully for existing markdown file', function () {
            $response = $this->get(route('reference.page', 'the-basics'));

            $response->assertStatus(200)
                ->assertViewIs('reference.index')
                ->assertViewHas('current_page', 'the-basics')
                ->assertViewHas('title')
                ->assertViewHas('content_view')
                ->assertViewHas('pages');
        });

        test('returns 404 for invalid page', function () {
            $response = $this->get(route('reference.page', 'invalid-page'));
            
            $response->assertStatus(404);
        });

        test('returns 404 for non-existent markdown file', function () {
            $response = $this->get(route('reference.page', 'non-existent-page'));

            $response->assertStatus(404);
        });

        test('blade content view is passed to template', function () {
            $response = $this->get(route('reference.page', 'the-basics'));

            $contentView = $response->viewData('content_view');

            expect($contentView)->toBeString()
                ->and($contentView)->toContain('reference.pages'); // Should be a blade view path
        });

        test('displays correct page title in header', function () {
            $response = $this->get(route('reference.page', 'what-is-this'));

            $response->assertSee('What Is This');
        });

        test('highlights current page in sidebar', function () {
            $response = $this->get(route('reference.page', 'what-is-this'));

            // Current page should have special styling
            $response->assertSee('bg-amber-500/20 text-amber-400', false);
        });

        test('shows navigation buttons when appropriate', function () {
            $response = $this->get(route('reference.page', 'the-basics'));

            // Should have previous/next navigation in header
            $response->assertSee('Previous', false)
                ->assertSee('Next', false);
        });

        test('shows all pages in sidebar navigation', function () {
            $response = $this->get(route('reference.page', 'combat'));

            // Sidebar should contain all available pages
            $response->assertSee('What Is This')
                ->assertSee('Combat')
                ->assertSee('Classes')
                ->assertSee('Equipment');
        });

        test('handles special characters in page names', function () {
            $response = $this->get(route('reference.page', 'maps-range-and-movement'));

            // Should handle the conversion correctly
            expect($response->status())->toBeIn([200, 404]);
        });
    });

    describe('content structure', function () {
        test('includes json-based domains page', function () {
            $response = $this->get(route('reference.page', 'domains'));

            $response->assertStatus(200)
                ->assertViewHas('title', 'Domains')
                ->assertViewHas('current_page', 'domains')
                ->assertViewHas('content_type', 'json')
                ->assertViewHas('json_data');
        });

        test('includes json-based classes page', function () {
            $response = $this->get(route('reference.page', 'classes'));

            $response->assertStatus(200)
                ->assertViewHas('title', 'Classes')
                ->assertViewHas('current_page', 'classes')
                ->assertViewHas('content_type', 'json')
                ->assertViewHas('json_data');
        });
    });

    describe('route integration', function () {
        test('reference routes are properly registered', function () {
            expect(route('reference.index'))->toBe(url('/reference'))
                ->and(route('reference.page', 'the-basics'))->toBe(url('/reference/the-basics'));
        });
    });
});