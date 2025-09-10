<?php

declare(strict_types=1);

namespace App\Livewire\CampaignFrame;

use App\Livewire\Forms\CampaignFrame\CreateCampaignFrameForm;
use App\Livewire\Forms\CampaignFrame\EditCampaignFrameForm;
use Domain\CampaignFrame\Data\CampaignFrameData;
use Domain\CampaignFrame\Enums\ComplexityRating;
use Livewire\Component;

class CampaignFrameManager extends Component
{
    public string $mode = 'create'; // 'create' or 'edit'

    public ?CampaignFrameData $frame = null;

    public CreateCampaignFrameForm $create_form;

    public EditCampaignFrameForm $edit_form;

    public function mount(string $mode = 'create', ?CampaignFrameData $frame = null): void
    {
        $this->mode = $mode;
        $this->frame = $frame;

        if ($this->mode === 'edit' && $this->frame) {
            $this->edit_form->setFrame($this->frame);
        }
    }

    public function save(): void
    {
        if ($this->mode === 'create') {
            $frame = $this->create_form->save();
            $this->redirect(route('campaign-frames.show', $frame), navigate: true);
        } else {
            $frame = $this->edit_form->save();
            $this->redirect(route('campaign-frames.show', $frame), navigate: true);
        }
    }

    public function getComplexityOptions(): array
    {
        return ComplexityRating::options();
    }

    public function render()
    {
        return view('livewire.campaign-frame.campaign-frame-manager');
    }
}
