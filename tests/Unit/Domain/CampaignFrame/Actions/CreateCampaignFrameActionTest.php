<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\CampaignFrame\Actions;

use Domain\CampaignFrame\Actions\CreateCampaignFrameAction;
use Domain\CampaignFrame\Data\CreateCampaignFrameData;
use Domain\CampaignFrame\Enums\ComplexityRating;
use Domain\CampaignFrame\Models\CampaignFrame;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateCampaignFrameActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_a_campaign_frame_successfully(): void
    {
    // Arrange
    $user = User::factory()->create();
    $data = CreateCampaignFrameData::from([
        'name' => 'Test Campaign Frame',
        'description' => 'A test campaign frame description',
        'complexity_rating' => ComplexityRating::MODERATE,
        'is_public' => true,
        'pitch' => ['First pitch point', 'Second pitch point'],
        'tone_and_themes' => ['Dark', 'Mystery', 'Adventure'],
        'background_overview' => 'This is a detailed background overview.',
        'setting_guidance' => ['Use elves sparingly', 'Focus on human conflicts'],
        'principles' => ['Story over rules', 'Player agency matters'],
        'setting_distinctions' => ['Magic is rare', 'Gods are distant'],
        'inciting_incident' => 'A dragon awakens in the nearby mountains.',
        'special_mechanics' => [['name' => 'Fear Dice', 'description' => 'Roll extra dice when afraid']],
        'session_zero_questions' => ['What are your character goals?', 'What are your limits?'],
    ]);

    $action = new CreateCampaignFrameAction();

    // Act
    $frame = $action->execute($data, $user);

    // Assert
    $this->assertInstanceOf(CampaignFrame::class, $frame);
    $this->assertEquals('Test Campaign Frame', $frame->name);
    $this->assertEquals('A test campaign frame description', $frame->description);
    $this->assertEquals(ComplexityRating::MODERATE->value, $frame->complexity_rating);
    $this->assertTrue($frame->is_public);
    $this->assertEquals($user->id, $frame->creator_id);
    $this->assertEquals(['First pitch point', 'Second pitch point'], $frame->pitch);
    $this->assertEquals(['Dark', 'Mystery', 'Adventure'], $frame->tone_and_themes);
    $this->assertEquals('This is a detailed background overview.', $frame->background_overview);
    $this->assertEquals('A dragon awakens in the nearby mountains.', $frame->inciting_incident);

    $this->assertEquals(1, CampaignFrame::count());
    }

    #[Test]
    public function it_creates_a_private_campaign_frame_by_default(): void
    {
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
    $this->assertFalse($frame->is_public);
    }
}
