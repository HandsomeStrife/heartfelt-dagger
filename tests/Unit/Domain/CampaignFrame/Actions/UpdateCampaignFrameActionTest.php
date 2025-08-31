<?php

declare(strict_types=1);

use Domain\CampaignFrame\Actions\UpdateCampaignFrameAction;
use Domain\CampaignFrame\Data\UpdateCampaignFrameData;
use Domain\CampaignFrame\Enums\ComplexityRating;
use Domain\CampaignFrame\Models\CampaignFrame;
use Domain\User\Models\User;

test('it updates a campaign frame successfully', function () {
    // Arrange
    $user = User::factory()->create();
    $frame = CampaignFrame::factory()->create([
        'creator_id' => $user->id,
        'name' => 'Original Name',
        'description' => 'Original Description',
        'complexity_rating' => ComplexityRating::SIMPLE->value,
        'is_public' => false,
    ]);

    $update_data = UpdateCampaignFrameData::from([
        'name' => 'Updated Name',
        'description' => 'Updated Description',
        'complexity_rating' => ComplexityRating::COMPLEX,
        'is_public' => true,
        'pitch' => ['Updated pitch'],
        'tone' => ['Updated'],
        'themes' => ['Updated theme'],
        'background_overview' => 'Updated background',
        'setting_guidance' => ['Updated guidance'],
        'player_principles' => ['Updated principle'],
        'setting_distinctions' => ['Updated distinction'],
        'inciting_incident' => 'Updated incident',
        'special_mechanics' => [['name' => 'Updated Mechanic', 'description' => 'Updated description']],
        'session_zero_questions' => ['Updated question?'],
    ]);

    $action = new UpdateCampaignFrameAction();

    // Act
    $updated_frame = $action->execute($frame, $update_data);

    // Assert
    expect($updated_frame->name)->toBe('Updated Name');
    expect($updated_frame->description)->toBe('Updated Description');
    expect($updated_frame->complexity_rating)->toBe(ComplexityRating::COMPLEX->value);
    expect($updated_frame->is_public)->toBe(true);
    expect($updated_frame->pitch)->toBe(['Updated pitch']);
    expect($updated_frame->background_overview)->toBe('Updated background');
    expect($updated_frame->inciting_incident)->toBe('Updated incident');
});

test('it maintains the creator when updating', function () {
    // Arrange
    $user = User::factory()->create();
    $frame = CampaignFrame::factory()->create(['creator_id' => $user->id]);

    $update_data = UpdateCampaignFrameData::from([
        'name' => 'Updated Name',
        'description' => 'Updated Description',
        'complexity_rating' => ComplexityRating::MODERATE,
        'is_public' => false,
    ]);

    $action = new UpdateCampaignFrameAction();

    // Act
    $updated_frame = $action->execute($frame, $update_data);

    // Assert
    expect($updated_frame->creator_id)->toBe($user->id);
});
