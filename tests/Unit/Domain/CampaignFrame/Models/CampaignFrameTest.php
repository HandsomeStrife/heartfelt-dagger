<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\CampaignFrame\Models;

use Domain\Campaign\Models\Campaign;
use Domain\CampaignFrame\Models\CampaignFrame;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignFrameTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_the_correct_fillable_attributes(): void
    {
    // Arrange
    $data = [
        'name' => 'Test Frame',
        'description' => 'Test Description',
        'complexity_rating' => 2,
        'is_public' => true,
        'creator_id' => 1,
        'pitch' => ['pitch'],
        'tone_and_themes' => ['theme'],
        'background_overview' => 'background',
        'setting_guidance' => ['guidance'],
        'principles' => ['principle'],
        'setting_distinctions' => ['distinction'],
        'inciting_incident' => 'incident',
        'special_mechanics' => ['mechanic'],
        'session_zero_questions' => ['question'],
    ];

    // Act
    $frame = new CampaignFrame($data);

    // Assert
    $this->assertEquals('Test Frame', $frame->name);
    $this->assertEquals('Test Description', $frame->description);
    $this->assertEquals(2, $frame->complexity_rating);
    $this->assertTrue($frame->is_public);
    $this->assertEquals(1, $frame->creator_id);
    }

    #[Test]
    public function it_casts_attributes_correctly(): void
    {
    // Arrange & Act
    $frame = CampaignFrame::factory()->create([
        'is_public' => '1',
        'complexity_rating' => '3',
        'pitch' => ['item1', 'item2'],
    ]);

    // Assert
    $this->assertTrue($frame->is_public);
    $this->assertEquals(3, $frame->complexity_rating);
    $this->assertEquals(['item1', 'item2'], $frame->pitch);
    }
}
