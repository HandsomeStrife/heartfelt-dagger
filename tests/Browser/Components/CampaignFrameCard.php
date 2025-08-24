<?php

declare(strict_types=1);

namespace Tests\Browser\Components;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Component as BaseComponent;

class CampaignFrameCard extends BaseComponent
{
    public function __construct(private string $frameName)
    {
    }

    /**
     * Get the root selector for the component.
     */
    public function selector(): string
    {
        return sprintf('[data-frame-name="%s"]', $this->frameName);
    }

    /**
     * Assert that the browser page contains the component.
     */
    public function assert(Browser $browser): void
    {
        $browser->assertPresent($this->selector());
    }

    /**
     * Get the element shortcuts for the component.
     */
    public function elements(): array
    {
        return [
            '@name' => '.frame-name',
            '@description' => '.frame-description', 
            '@creator' => '.frame-creator',
            '@complexity' => '.complexity-indicator',
            '@public-badge' => '.public-badge',
            '@edit-button' => '.edit-button',
            '@view-button' => '.view-button',
            '@explore-button' => '.explore-button',
        ];
    }

    /**
     * Click the edit button.
     */
    public function edit(Browser $browser): void
    {
        $browser->click('@edit-button');
    }

    /**
     * Click the view button.
     */
    public function view(Browser $browser): void
    {
        $browser->click('@view-button');
    }

    /**
     * Click the explore button (for public frames).
     */
    public function explore(Browser $browser): void
    {
        $browser->click('@explore-button');
    }

    /**
     * Assert frame has specific complexity.
     */
    public function assertComplexity(Browser $browser, string $complexity): void
    {
        $browser->within('@complexity', function ($browser) use ($complexity) {
            $browser->assertSee($complexity);
        });
    }

    /**
     * Assert frame is marked as public.
     */
    public function assertIsPublic(Browser $browser): void
    {
        $browser->assertPresent('@public-badge');
    }

    /**
     * Assert frame is not marked as public.
     */
    public function assertIsPrivate(Browser $browser): void
    {
        $browser->assertMissing('@public-badge');
    }

    /**
     * Assert frame shows creator name.
     */
    public function assertCreator(Browser $browser, string $creatorName): void
    {
        $browser->within('@creator', function ($browser) use ($creatorName) {
            $browser->assertSee("by {$creatorName}");
        });
    }
}
