<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\CampaignFrame\Actions;

use Domain\Campaign\Models\Campaign;
use Domain\CampaignFrame\Actions\DeleteCampaignFrameAction;
use Domain\CampaignFrame\Models\CampaignFrame;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteCampaignFrameActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_deletes_a_campaign_frame_successfully_when_not_used_by_campaigns(): void
    {
    // Arrange
    $frame = CampaignFrame::factory()->create();
    $action = new DeleteCampaignFrameAction();

    // Act
    $action->execute($frame);

    // Assert
    $this->assertEquals(0, CampaignFrame::count());
    }

    #[Test]
    public function it_throws_an_exception_when_trying_to_delete_a_frame_used_by_campaigns(): void
    {
    // Arrange
    $user = User::factory()->create();
    $frame = CampaignFrame::factory()->create();
    
    // Create a campaign that uses this frame
    Campaign::factory()->create([
        'creator_id' => $user->id,
        'campaign_frame_id' => $frame->id,
    ]);

    $action = new DeleteCampaignFrameAction();

    // Act & Assert
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Cannot delete campaign frame that is being used by active campaigns.');
    
    $action->execute($frame);
    }
}
