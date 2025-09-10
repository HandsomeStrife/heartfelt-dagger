<?php

declare(strict_types=1);

namespace App\Livewire\CampaignFrame;

use Domain\Campaign\Models\Campaign;
use Domain\CampaignFrame\Models\CampaignFrameVisibility;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CampaignFrameVisibilityManager extends Component
{
    public Campaign $campaign;

    public array $visibilitySettings = [];

    public bool $showManager = false;

    public function mount(Campaign $campaign)
    {
        $this->campaign = $campaign;
        $this->loadVisibilitySettings();
    }

    public function loadVisibilitySettings()
    {
        if (! $this->campaign->campaign_frame_id) {
            return;
        }

        // Load existing settings or use defaults
        $existingSettings = $this->campaign->campaignFrameVisibilities()
            ->pluck('is_visible_to_players', 'section_name')
            ->toArray();

        $defaultSettings = CampaignFrameVisibility::getDefaultVisibilitySettings();

        // Merge existing with defaults
        $this->visibilitySettings = array_merge($defaultSettings, $existingSettings);
    }

    public function toggleSectionVisibility(string $section)
    {
        if (! $this->canManageVisibility()) {
            return;
        }

        $this->visibilitySettings[$section] = ! ($this->visibilitySettings[$section] ?? false);
        $this->saveVisibilitySettings();
    }

    public function saveVisibilitySettings()
    {
        if (! $this->canManageVisibility()) {
            return;
        }

        foreach ($this->visibilitySettings as $section => $isVisible) {
            CampaignFrameVisibility::updateOrCreate(
                [
                    'campaign_id' => $this->campaign->id,
                    'section_name' => $section,
                ],
                [
                    'is_visible_to_players' => $isVisible,
                ]
            );
        }

        session()->flash('success', 'Visibility settings saved successfully!');
    }

    public function toggleManager()
    {
        $this->showManager = ! $this->showManager;
    }

    public function canManageVisibility(): bool
    {
        return $this->campaign->isCreator(Auth::user());
    }

    public function hasCampaignFrame(): bool
    {
        return ! is_null($this->campaign->campaign_frame_id);
    }

    public function render()
    {
        return view('livewire.campaign-frame.campaign-frame-visibility-manager', [
            'availableSections' => CampaignFrameVisibility::getAvailableSections(),
        ]);
    }
}
