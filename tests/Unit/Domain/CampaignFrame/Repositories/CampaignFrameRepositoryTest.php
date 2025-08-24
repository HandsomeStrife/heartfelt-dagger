<?php

declare(strict_types=1);

use Domain\CampaignFrame\Models\CampaignFrame;
use Domain\CampaignFrame\Repositories\CampaignFrameRepository;
use Domain\User\Models\User;

test('it gets public frames', function () {
    // Arrange
    CampaignFrame::factory()->create(['is_public' => true, 'name' => 'A Frame']);
    CampaignFrame::factory()->create(['is_public' => false]);
    CampaignFrame::factory()->create(['is_public' => true, 'name' => 'B Frame']);

    $repository = new CampaignFrameRepository();

    // Act
    $frames = $repository->getPublicFrames();

    // Assert
    expect($frames)->toHaveCount(2);
    expect($frames->first()->name)->toBe('A Frame'); // Should be ordered by name
    expect($frames->last()->name)->toBe('B Frame');
});

test('gets frames by user', function () {
    // Arrange
    $user = User::factory()->create();
    $other_user = User::factory()->create();
    
    CampaignFrame::factory()->create(['creator_id' => $user->id, 'updated_at' => now()->subHour()]);
    CampaignFrame::factory()->create(['creator_id' => $other_user->id]);
    CampaignFrame::factory()->create(['creator_id' => $user->id, 'updated_at' => now()]);

    $repository = new CampaignFrameRepository();

    // Act
    $frames = $repository->getFramesByUser($user);

    // Assert
    expect($frames)->toHaveCount(2);
    // Should be ordered by updated_at desc (most recent first)
    expect($frames->first()->updated_at)->toBeGreaterThan($frames->last()->updated_at);
});

test('finds frame by id', function () {
    // Arrange
    $frame = CampaignFrame::factory()->create();
    $repository = new CampaignFrameRepository();

    // Act
    $found_frame = $repository->findById($frame->id);

    // Assert
    expect($found_frame)->not->toBeNull();
    expect($found_frame->id)->toBe($frame->id);
    expect($found_frame->name)->toBe($frame->name);
});

test('returns null when frame not found by id', function () {
    // Arrange
    $repository = new CampaignFrameRepository();

    // Act
    $found_frame = $repository->findById(999);

    // Assert
    expect($found_frame)->toBeNull();
});

test('finds frame by id for user with access', function () {
    // Arrange
    $user = User::factory()->create();
    $other_user = User::factory()->create();
    
    $public_frame = CampaignFrame::factory()->create(['is_public' => true]);
    $private_frame = CampaignFrame::factory()->create([
        'creator_id' => $user->id,
        'is_public' => false,
    ]);

    $repository = new CampaignFrameRepository();

    // Act & Assert
    // User can access public frames
    $found_frame = $repository->findByIdForUser($public_frame->id, $other_user);
    expect($found_frame)->not->toBeNull();
    expect($found_frame->id)->toBe($public_frame->id);

    // User can access their private frames
    $found_frame = $repository->findByIdForUser($private_frame->id, $user);
    expect($found_frame)->not->toBeNull();
    expect($found_frame->id)->toBe($private_frame->id);

    // User cannot access others' private frames
    $found_frame = $repository->findByIdForUser($private_frame->id, $other_user);
    expect($found_frame)->toBeNull();
});

test('searches public frames', function () {
    // Arrange
    CampaignFrame::factory()->create([
        'is_public' => true,
        'name' => 'Fantasy Adventure',
        'description' => 'A magical quest',
    ]);
    
    CampaignFrame::factory()->create([
        'is_public' => true,
        'name' => 'Sci-Fi Campaign',
        'description' => 'Space exploration',
    ]);
    
    CampaignFrame::factory()->create([
        'is_public' => false,
        'name' => 'Fantasy Private',
        'description' => 'Secret adventure',
    ]);

    $repository = new CampaignFrameRepository();

    // Act
    $fantasy_frames = $repository->searchPublicFrames('Fantasy');
    $space_frames = $repository->searchPublicFrames('Space');

    // Assert
    expect($fantasy_frames)->toHaveCount(1);
    expect($fantasy_frames->first()->name)->toBe('Fantasy Adventure');

    expect($space_frames)->toHaveCount(1);
    expect($space_frames->first()->name)->toBe('Sci-Fi Campaign');
});

test('gets frames available for campaign', function () {
    // Arrange
    $user = User::factory()->create();
    
    CampaignFrame::factory()->create(['is_public' => true, 'name' => 'A Public']);
    CampaignFrame::factory()->create([
        'creator_id' => $user->id,
        'is_public' => false,
        'name' => 'B Private',
    ]);
    CampaignFrame::factory()->create(['is_public' => true, 'name' => 'C Public']);

    $repository = new CampaignFrameRepository();

    // Act
    $frames = $repository->getFramesAvailableForCampaign($user);

    // Assert
    expect($frames)->toHaveCount(3);
    expect($frames->pluck('name')->sort()->values()->toArray())->toBe([
        'A Public',
        'B Private',
        'C Public',
    ]);
});

test('gets only public frames when no user provided', function () {
    // Arrange
    $user = User::factory()->create();
    
    CampaignFrame::factory()->create(['is_public' => true]);
    CampaignFrame::factory()->create([
        'creator_id' => $user->id,
        'is_public' => false,
    ]);

    $repository = new CampaignFrameRepository();

    // Act
    $frames = $repository->getFramesAvailableForCampaign();

    // Assert
    expect($frames)->toHaveCount(1);
    expect($frames->first()->is_public)->toBe(true);
});
