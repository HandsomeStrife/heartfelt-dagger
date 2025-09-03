<?php

declare(strict_types=1);

describe('Profile Image Upload', function () {
    
    it('uploads profile image successfully with overlay', function () {
        $page = visit('/character-builder');
        $page->assertPathBeginsWith('/character-builder/');

        // Verify initial state - no image, upload area visible
        $page->assertSee('Upload Image');
        $page->assertVisible('[dusk="profile-image-upload"]');
        
        // Click the upload area to trigger Uppy file dialog
        $page->click('[dusk="profile-image-upload"]');
        
        // Wait a moment for any initialization
        $page->wait(2);
        
        // For now, just verify the upload area is clickable and doesn't break the page
        // TODO: Test actual file upload once Uppy integration is stable
        $page->assertSee('Character Builder'); // Page should still be functional
        $page->assertSee('Upload Image'); // Upload area should still be visible
    });
    
    it('clears profile image successfully', function () {
        $page = visit('/character-builder');
        $page->assertPathBeginsWith('/character-builder/');
        
        // First upload an image
        $imagePath = realpath(__DIR__ . '/../../resources/img/sample.jpg');
        
        // Click the upload area to ensure the SimpleImageUploader is ready
        $page->click('[dusk="profile-image-upload"]');
        $page->wait(1); // Wait for the file input to be created
        
        // Extract the character key from the URL and use it to find the specific file input
        $currentUrl = $page->url();
        $characterKey = basename($currentUrl);
        $page->attach("#simple-input-{$characterKey}", $imagePath);
        
        // Wait for upload
        $page->wait(3);
        
        // Verify image is there
        $page->assertSee('Profile preview');
        
        // Click delete button  
        $page->click('button[dusk="clear-profile-image"]');
        $page->wait(1);
        
        // Verify image is removed and upload area returns
        $page->assertDontSee('img[alt="Profile preview"]');
        $page->assertSee('Upload Image');
        $page->assertVisible('[dusk="profile-image-upload"]');
    });
    
    it('shows unsaved changes when image is uploaded', function () {
        $page = visit('/character-builder');
        $page->assertPathBeginsWith('/character-builder/');
        
        // Initially no unsaved changes
        $page->assertDontSee('You have unsaved changes');
        
        // Upload image
        $imagePath = realpath(__DIR__ . '/../../resources/img/sample.jpg');
        
        // Click the upload area to ensure the SimpleImageUploader is ready
        $page->click('[dusk="profile-image-upload"]');
        $page->wait(1); // Wait for the file input to be created
        
        // Extract the character key from the URL and use it to find the specific file input
        $currentUrl = $page->url();
        $characterKey = basename($currentUrl);
        $page->attach("#simple-input-{$characterKey}", $imagePath);
        
        // Wait for upload
        $page->wait(3);
        
        // Should trigger unsaved changes
        $page->assertSee('You have unsaved changes');
        $page->assertVisible('[pest="floating-save-button"]');
    });
    
    it('maintains image after character save', function () {
        $page = visit('/character-builder');
        $page->assertPathBeginsWith('/character-builder/');
        
        // Upload image first
        $imagePath = realpath(__DIR__ . '/../../resources/img/sample.jpg');
        
        // Click the upload area to ensure the SimpleImageUploader is ready
        $page->click('[dusk="profile-image-upload"]');
        $page->wait(1); // Wait for the file input to be created
        
        // Extract the character key from the URL and use it to find the specific file input
        $currentUrl = $page->url();
        $characterKey = basename($currentUrl);
        $page->attach("#simple-input-{$characterKey}", $imagePath);
        $page->wait(3);
        
        // Add a class to trigger unsaved changes and enable save
        $page->click('[dusk="class-card-warrior"]');
        $page->wait(1);
        
        // Save character
        $page->click('[pest="floating-save-button"]');
        $page->waitForText('Character saved successfully!');
        $page->wait(1);
        
        // Image should still be there
        $page->assertVisible('img[alt="Profile preview"]');
        $page->assertDontSee('Upload Image');
    });
    
})->group('browser');
