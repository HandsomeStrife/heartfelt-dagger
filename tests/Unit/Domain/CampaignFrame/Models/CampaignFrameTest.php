<?php

declare(strict_types=1);

use Domain\CampaignFrame\Models\CampaignFrame;

test('it has the correct fillable attributes', function () {
    // Arrange
    $data = [
        'name' => 'Test Frame',
        'description' => 'Test Description',
        'complexity_rating' => 2,
        'is_public' => true,
        'creator_id' => 1,
        'pitch' => ['pitch'],
        'tone' => ['theme'],
        'themes' => ['theme'],
        'background_overview' => 'background',
        'setting_guidance' => ['guidance'],
        'player_principles' => ['principle'],
        'setting_distinctions' => ['distinction'],
        'inciting_incident' => 'incident',
        'special_mechanics' => ['mechanic'],
        'session_zero_questions' => ['question'],
    ];

    // Act
    $frame = new CampaignFrame($data);

    // Assert
    expect($frame->name)->toBe('Test Frame');
    expect($frame->description)->toBe('Test Description');
    expect($frame->complexity_rating)->toBe(2);
    expect($frame->is_public)->toBe(true);
    expect($frame->creator_id)->toBe(1);
});

test('it casts attributes correctly', function () {
    // Arrange & Act
    $frame = CampaignFrame::factory()->create([
        'is_public' => '1',
        'complexity_rating' => '3',
        'pitch' => ['item1', 'item2'],
    ]);

    // Assert
    expect($frame->is_public)->toBe(true);
    expect($frame->complexity_rating)->toBe(3);
    expect($frame->pitch)->toBe(['item1', 'item2']);
});
