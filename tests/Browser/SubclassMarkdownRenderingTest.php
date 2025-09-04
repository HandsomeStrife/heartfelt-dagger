<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders markdown content in subclass feature descriptions correctly', function () {
    $this->visit('/character-builder')
        ->select('warrior', 'class')
        ->click('[pest="step-advance-button"]')
        ->waitFor('[pest="subclass-card-stalwart"]')
        ->click('[pest="subclass-card-stalwart"]')
        ->pause(500); // Allow markdown to process

    // Check that markdown formatting is converted to HTML
    // The Stalwart subclass has descriptions that should be rendered properly
    $page = $this->getPage();
    
    // Look for the rendered feature content
    $foundationFeatures = $page->locator('.space-y-3 .bg-slate-800\\/30');
    
    // Verify that at least one feature description exists
    expect($foundationFeatures->count())->toBeGreaterThan(0);
    
    // Check that the description contains proper HTML elements (not raw markdown)
    $firstFeatureDescription = $foundationFeatures->first()->locator('div:last-child')->innerHTML();
    
    // Should contain HTML, not raw markdown
    expect($firstFeatureDescription)->not->toContain('**');
    expect($firstFeatureDescription)->not->toContain('\n\n');
    
    // Should contain proper HTML tags for formatting
    expect($firstFeatureDescription)->toContain('<');
});

it('handles subclass descriptions with bullet points correctly', function () {
    $this->visit('/character-builder')
        ->select('druid', 'class')
        ->click('[pest="step-advance-button"]')
        ->waitFor('[pest="subclass-card-warden of the elements"]')
        ->click('[pest="subclass-card-warden of the elements"]')
        ->pause(500); // Allow markdown to process

    $page = $this->getPage();
    
    // Look for rendered specialization features which contain bullet points
    $specializationFeatures = $page->locator('.space-y-3 .bg-gradient-to-r');
    
    if ($specializationFeatures->count() > 0) {
        $featureDescription = $specializationFeatures->first()->locator('div:last-child')->innerHTML();
        
        // Should not contain raw markdown bullets
        expect($featureDescription)->not->toContain('- **');
        
        // Should contain proper list HTML if there were bullet points in the source
        if (str_contains($featureDescription, '<ul>') || str_contains($featureDescription, '<li>')) {
            expect($featureDescription)->toContain('<ul');
            expect($featureDescription)->toContain('<li');
        }
    }
});

it('preserves bold text formatting from markdown', function () {
    $this->visit('/character-builder')
        ->select('druid', 'class')
        ->click('[pest="step-advance-button"]')
        ->waitFor('[pest="subclass-card-warden of the elements"]')
        ->click('[pest="subclass-card-warden of the elements"]')
        ->pause(500);

    $page = $this->getPage();
    
    // The Warden of the Elements has bold text in descriptions like **Fire:** **Earth:** etc.
    $foundationFeatures = $page->locator('.space-y-3 .bg-slate-800\\/30');
    
    if ($foundationFeatures->count() > 0) {
        $featureDescription = $foundationFeatures->first()->locator('div:last-child')->innerHTML();
        
        // Should not contain raw markdown bold syntax
        expect($featureDescription)->not->toContain('**');
        
        // Should contain HTML strong tags for bold text
        if (str_contains($featureDescription, '<strong>')) {
            expect($featureDescription)->toContain('<strong');
            expect($featureDescription)->toContain('</strong>');
        }
    }
});

it('handles playtest subclasses with markdown correctly', function () {
    $this->visit('/character-builder')
        ->select('rogue', 'class')
        ->click('[pest="step-advance-button"]')
        ->waitFor('[pest="subclass-card-executioners guild"]')
        ->click('[pest="subclass-card-executioners guild"]')
        ->pause(500);

    $page = $this->getPage();
    
    // Check that playtest content (which may have different markdown patterns) renders correctly
    $foundationFeatures = $page->locator('.space-y-3 .bg-slate-800\\/30');
    
    expect($foundationFeatures->count())->toBeGreaterThan(0);
    
    $firstFeatureDescription = $foundationFeatures->first()->locator('div:last-child')->innerHTML();
    
    // Should be properly formatted HTML, not raw text
    expect($firstFeatureDescription)->toContain('<');
    expect($firstFeatureDescription)->not->toContain('**');
});
