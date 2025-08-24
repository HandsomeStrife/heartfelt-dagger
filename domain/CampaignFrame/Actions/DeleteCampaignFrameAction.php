<?php

declare(strict_types=1);

namespace Domain\CampaignFrame\Actions;

use Domain\CampaignFrame\Models\CampaignFrame;
use Exception;

class DeleteCampaignFrameAction
{
    public function execute(CampaignFrame $frame): void
    {
        // Check if the frame is being used by any campaigns
        if ($frame->campaigns()->count() > 0) {
            throw new Exception('Cannot delete campaign frame that is being used by active campaigns.');
        }

        $frame->delete();
    }
}
