<?php

declare(strict_types=1);

use Domain\CampaignFrame\Actions\CreateCampaignFrameAction;
use Domain\CampaignFrame\Data\CreateCampaignFrameData;
use Domain\CampaignFrame\Enums\ComplexityRating;
use Domain\CampaignFrame\Models\CampaignFrame;
use Domain\User\Models\User;

test('it creates a campaign frame successfully', function () {
    // Arrange
    $user = User::factory()->create();
    $data = CreateCampaignFrameData::from([
        'name' => 'Test Campaign Frame',
        'description' => 'A test campaign frame description',
        'complexity_rating' => ComplexityRating::MODERATE,
        'is_public' => true,
        'pitch' => ['First pitch point', 'Second pitch point'],
        'tone' => ['Dark', 'Mystery'],
        'themes' => ['Adventure'],
        'background_overview' => 'This is a detailed background overview.',
        'setting_guidance' => ['Use elves sparingly', 'Focus on human conflicts'],
        'player_principles' => ['Story over rules', 'Player agency matters'],
        'setting_distinctions' => ['Magic is rare', 'Gods are distant'],
        'inciting_incident' => 'A dragon awakens in the nearby mountains.',
        'special_mechanics' => [['name' => 'Fear Dice', 'description' => 'Roll extra dice when afraid']],
        'session_zero_questions' => ['What are your character goals?', 'What are your limits?'],
    ]);

    $action = new CreateCampaignFrameAction();

    // Act
    $frame = $action->execute($data, $user);

    // Assert
    expect($frame)->toBeInstanceOf(CampaignFrame::class);
    expect($frame->name)->toBe('Test Campaign Frame');
    expect($frame->description)->toBe('A test campaign frame description');
    expect($frame->complexity_rating)->toBe(ComplexityRating::MODERATE->value);
    expect($frame->is_public)->toBe(true);
    expect($frame->creator_id)->toBe($user->id);
    expect($frame->pitch)->toBe(['First pitch point', 'Second pitch point']);
    expect($frame->tone)->toBe(['Dark', 'Mystery']);
    expect($frame->themes)->toBe(['Adventure']);
    expect($frame->background_overview)->toBe('This is a detailed background overview.');
    expect($frame->inciting_incident)->toBe('A dragon awakens in the nearby mountains.');

    expect(CampaignFrame::count())->toBe(1);
});

test('it creates a private campaign frame by default', function () {
    // Arrange
    $user = User::factory()->create();
    $data = CreateCampaignFrameData::from([
        'name' => 'Private Frame',
        'description' => 'A private campaign frame',
        'complexity_rating' => ComplexityRating::SIMPLE,
        'is_public' => false,
    ]);

    $action = new CreateCampaignFrameAction();

    // Act
    $frame = $action->execute($data, $user);

    // Assert
    expect($frame->is_public)->toBe(false);
});
