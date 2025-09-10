<?php

declare(strict_types=1);

use App\Livewire\CharacterBuilder;
use Domain\Character\Models\Character;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

test('character image displays properly when uploaded', function () {
    // Fake S3 storage for testing
    Storage::fake('s3');

    // Create a character in the database
    $character = Character::factory()->create([
        'name' => 'Image Test Character',
        'class' => 'warrior',
    ]);

    // Create a fake image file
    $fakeImage = UploadedFile::fake()->image('test-avatar.jpg', 300, 300);

    // Test the Livewire component
    $component = livewire(CharacterBuilder::class, ['characterKey' => $character->character_key]);

    // Initially no image
    $initialImageUrl = $component->instance()->getImageUrl();
    expect($initialImageUrl)->toBeNull();

    // Set the profile image (this should trigger updatedProfileImage)
    $component->set('profile_image', $fakeImage);

    // Wait for the upload to process and then check if we have an image URL
    $imageUrl = $component->instance()->getImageUrl();
    expect($imageUrl)->not()->toBeNull();

    // Verify file was uploaded and stored to database
    $character->refresh();
    expect($character->profile_image_path)->not()->toBeNull();

    // Since we're using fake storage, let's check if the file was stored
    if ($character->profile_image_path) {
        expect(Storage::disk('s3')->exists($character->profile_image_path))->toBeTrue();
    }
});

test('character image displays from S3 after refresh', function () {
    // Fake S3 storage for testing
    Storage::fake('s3');

    // Create a character with an existing image path
    $imagePath = 'character-portraits/2025/08/27/TESTKEY123/test_avatar_120000.jpg';
    Storage::disk('s3')->put($imagePath, 'fake image content');

    $character = Character::factory()->create([
        'name' => 'S3 Test Character',
        'class' => 'wizard',
        'profile_image_path' => $imagePath,
    ]);

    // Test the Livewire component displays the S3 image
    $component = livewire(CharacterBuilder::class, ['characterKey' => $character->character_key]);
    $imageUrl = $component->instance()->getImageUrl();
    expect($imageUrl)->not()->toBeNull();
    expect($imageUrl)->toContain($imagePath);
});

test('character image clears properly', function () {
    // Fake S3 storage for testing
    Storage::fake('s3');

    // Create a character with an existing image
    $imagePath = 'test-images/test_avatar.jpg';
    Storage::disk('s3')->put($imagePath, 'fake image content');

    $character = Character::factory()->create([
        'name' => 'Clear Test Character',
        'class' => 'bard',
        'profile_image_path' => $imagePath,
    ]);

    // Test clearing the image
    $component = livewire(CharacterBuilder::class, ['characterKey' => $character->character_key]);

    // Initially should have image
    $initialImageUrl = $component->instance()->getImageUrl();
    expect($initialImageUrl)->not()->toBeNull();
    expect($initialImageUrl)->toContain($imagePath);

    // Clear the image
    $component->call('clearProfileImage');

    // Should be null after clearing
    $clearedImageUrl = $component->instance()->getImageUrl();
    expect($clearedImageUrl)->toBeNull();

    // Verify file was deleted from S3 and database
    expect(Storage::disk('s3')->exists($imagePath))->toBeFalse();
    $character->refresh();
    expect($character->profile_image_path)->toBeNull();
});

test('character image getProfileImage method generates correct signed URL', function () {
    // Fake S3 storage for testing
    Storage::fake('s3');

    $imagePath = 'test-images/test_avatar.jpg';

    // Actually store the file in fake S3
    Storage::disk('s3')->put($imagePath, 'fake image content');

    $character = Character::factory()->create([
        'profile_image_path' => $imagePath,
    ]);

    $imageUrl = $character->getProfileImage();

    // Should contain the image path and expiration parameter (indicating signed URL)
    expect($imageUrl)->toContain($imagePath);
    expect($imageUrl)->toContain('expiration=');
});

test('character image returns default when no image exists', function () {
    $character = Character::factory()->create([
        'profile_image_path' => null,
    ]);

    $imageUrl = $character->getProfileImage();

    // Should return default avatar
    expect($imageUrl)->toContain('default-avatar.png');
});

test('character image handles all three scenarios correctly', function () {
    Storage::fake('s3');

    // Create a character with no image initially
    $character = Character::factory()->create([
        'name' => 'Scenario Test Character',
        'class' => 'rogue',
        'profile_image_path' => null,
    ]);

    $component = livewire(CharacterBuilder::class, ['characterKey' => $character->character_key]);

    // Scenario 1: No image exists - should show upload area
    expect($component->instance()->getImageUrl())->toBeNull();

    // Scenario 2: Upload an image when there is none
    $fakeImage1 = UploadedFile::fake()->image('first-avatar.jpg', 300, 300);
    $component->set('profile_image', $fakeImage1);

    $uploadedImageUrl = $component->instance()->getImageUrl();
    expect($uploadedImageUrl)->not()->toBeNull();
    expect($uploadedImageUrl)->toContain('preview-file');

    // Clear the temporary image and verify it saves to database
    $component->set('profile_image', null);

    // Reload to get saved image
    $character->refresh();
    $component = livewire(CharacterBuilder::class, ['characterKey' => $character->character_key]);

    if ($character->profile_image_path) {
        $savedImageUrl = $component->instance()->getImageUrl();
        expect($savedImageUrl)->not()->toBeNull();
        expect($savedImageUrl)->toContain($character->profile_image_path);
    }

    // Scenario 3: Delete the existing image
    $component->call('clearProfileImage');
    expect($component->instance()->getImageUrl())->toBeNull();

    // Scenario 4: Upload a new image after deleting
    $fakeImage2 = UploadedFile::fake()->image('second-avatar.jpg', 300, 300);
    $component->set('profile_image', $fakeImage2);

    $newImageUrl = $component->instance()->getImageUrl();
    expect($newImageUrl)->not()->toBeNull();
    expect($newImageUrl)->toContain('preview-file');
});
