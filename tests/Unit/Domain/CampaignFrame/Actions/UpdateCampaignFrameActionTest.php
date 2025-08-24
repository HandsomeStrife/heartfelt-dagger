<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\CampaignFrame\Actions;

use Domain\CampaignFrame\Actions\UpdateCampaignFrameAction;
use Domain\CampaignFrame\Data\UpdateCampaignFrameData;
use Domain\CampaignFrame\Enums\ComplexityRating;
use Domain\CampaignFrame\Models\CampaignFrame;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateCampaignFrameActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_updates_a_campaign_frame_successfully(): void
    {
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
        'tone_and_themes' => ['Updated theme'],
        'background_overview' => 'Updated background',
        'setting_guidance' => ['Updated guidance'],
        'principles' => ['Updated principle'],
        'setting_distinctions' => ['Updated distinction'],
        'inciting_incident' => 'Updated incident',
        'special_mechanics' => [['name' => 'Updated Mechanic', 'description' => 'Updated description']],
        'session_zero_questions' => ['Updated question?'],
    ]);

    $action = new UpdateCampaignFrameAction();

    // Act
    $updated_frame = $action->execute($frame, $update_data);

    // Assert
    $this->assertEquals('Updated Name', $updated_frame->name);
    $this->assertEquals('Updated Description', $updated_frame->description);
    $this->assertEquals(ComplexityRating::COMPLEX->value, $updated_frame->complexity_rating);
    $this->assertTrue($updated_frame->is_public);
    $this->assertEquals(['Updated pitch'], $updated_frame->pitch);
    $this->assertEquals('Updated background', $updated_frame->background_overview);
    $this->assertEquals('Updated incident', $updated_frame->inciting_incident);
    }

    #[Test]
    public function it_maintains_the_creator_when_updating(): void
    {
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
    $this->assertEquals($user->id, $updated_frame->creator_id);
    }
}
