<?php

declare(strict_types=1);

namespace Domain\CampaignFrame\Actions;

use Domain\CampaignFrame\Data\UpdateCampaignFrameData;
use Domain\CampaignFrame\Models\CampaignFrame;

class UpdateCampaignFrameAction
{
    public function execute(CampaignFrame $frame, UpdateCampaignFrameData $data): CampaignFrame
    {
        $frame->update([
            'name' => $data->name,
            'description' => $data->description,
            'complexity_rating' => $data->complexity_rating->value,
            'is_public' => $data->is_public,
            'pitch' => $data->pitch,
            'tone_and_themes' => $data->tone_and_themes,
            'background_overview' => $data->background_overview,
            'setting_guidance' => $data->setting_guidance,
            'principles' => $data->principles,
            'setting_distinctions' => $data->setting_distinctions,
            'inciting_incident' => $data->inciting_incident,
            'special_mechanics' => $data->special_mechanics,
            'session_zero_questions' => $data->session_zero_questions,
        ]);

        return $frame->refresh();
    }
}
