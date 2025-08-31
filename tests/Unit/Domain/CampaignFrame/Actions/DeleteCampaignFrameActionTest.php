<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\CampaignFrame\Actions\DeleteCampaignFrameAction;
use Domain\CampaignFrame\Models\CampaignFrame;
use Domain\User\Models\User;

test('it deletes a campaign frame successfully when not used by campaigns', function () {
    // Arrange
    $frame = CampaignFrame::factory()->create();
    $action = new DeleteCampaignFrameAction();

    // Act
    $action->execute($frame);

    // Assert
    expect(CampaignFrame::count())->toBe(0);
});

test('it throws an exception when trying to delete a frame used by campaigns', function () {
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
    expect(fn() => $action->execute($frame))
        ->toThrow(\Exception::class, 'Cannot delete campaign frame that is being used by active campaigns.');
});
