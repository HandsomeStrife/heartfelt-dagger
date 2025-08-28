<?php

declare(strict_types=1);

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

class CampaignFramesPage extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return '/campaign-frames';
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->assertPathIs(url())
            ->assertSee('Campaign Frames')
            ->assertSee('Craft and discover inspiring campaign foundations');
    }

    /**
     * Get the element shortcuts for the page.
     */
    public function elements(): array
    {
        return [
            '@create-frame-button' => 'a[href*="/campaign-frames/create"]:first',
            '@browse-public-button' => 'a[href*="/campaign-frames/browse"]',
            '@create-first-frame-button' => 'a:contains("Create Your First Frame")',
            '@campaign-frames-link' => 'a[href*="/campaign-frames"]',
            '@edit-frame-button' => 'a:contains("Edit")',
            '@delete-frame-button' => 'button:contains("Delete")',
            '@clear-search-button' => 'a:contains("Clear")',
        ];
    }

    /**
     * Navigate to create frame page.
     */
    public function clickCreateFrame(Browser $browser): void
    {
        $browser->click('@create-frame-button');
    }

    /**
     * Navigate to browse public frames page.
     */
    public function clickBrowsePublic(Browser $browser): void
    {
        $browser->click('@browse-public-button');
    }

    /**
     * Assert frame appears in the list.
     */
    public function assertFrameExists(Browser $browser, string $frameName): void
    {
        $browser->assertSee($frameName);
    }

    /**
     * Assert frame does not appear in the list.
     */
    public function assertFrameNotExists(Browser $browser, string $frameName): void
    {
        $browser->assertDontSee($frameName);
    }

    /**
     * Click on a specific frame to view it.
     */
    public function clickFrame(Browser $browser, string $frameName): void
    {
        $browser->clickLink($frameName);
    }
});
