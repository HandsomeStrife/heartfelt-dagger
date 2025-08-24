<?php

declare(strict_types=1);

namespace App\Livewire\Forms\CampaignFrame;

use Domain\CampaignFrame\Actions\UpdateCampaignFrameAction;
use Domain\CampaignFrame\Data\CampaignFrameData;
use Domain\CampaignFrame\Data\UpdateCampaignFrameData;
use Domain\CampaignFrame\Enums\ComplexityRating;
use Domain\CampaignFrame\Models\CampaignFrame;
use Livewire\Attributes\Validate;
use Livewire\Form;

class EditCampaignFrameForm extends Form
{
    public ?CampaignFrame $frame = null;

    #[Validate('required|string|max:100')]
    public string $name = '';

    #[Validate('required|string|max:500')]
    public string $description = '';

    #[Validate('required|integer|min:1|max:4')]
    public int $complexity_rating = 1;

    public bool $is_public = false;

    public array $pitch = [];
    public array $tone_and_themes = [];

    #[Validate('string|max:2000')]
    public string $background_overview = '';

    public array $setting_guidance = [];
    public array $principles = [];
    public array $setting_distinctions = [];

    #[Validate('string|max:1000')]
    public string $inciting_incident = '';

    public array $special_mechanics = [];
    public array $session_zero_questions = [];

    public function setFrame(CampaignFrameData $frame_data): void
    {
        $this->frame = CampaignFrame::find($frame_data->id);
        
        $this->name = $frame_data->name;
        $this->description = $frame_data->description;
        $this->complexity_rating = $frame_data->complexity_rating->value;
        $this->is_public = $frame_data->is_public;
        $this->pitch = $frame_data->pitch;
        $this->tone_and_themes = $frame_data->tone_and_themes;
        $this->background_overview = $frame_data->background_overview;
        $this->setting_guidance = $frame_data->setting_guidance;
        $this->principles = $frame_data->principles;
        $this->setting_distinctions = $frame_data->setting_distinctions;
        $this->inciting_incident = $frame_data->inciting_incident;
        $this->special_mechanics = $frame_data->special_mechanics;
        $this->session_zero_questions = $frame_data->session_zero_questions;
    }

    public function save(): CampaignFrame
    {
        if (!$this->frame) {
            throw new \Exception('No campaign frame set for editing.');
        }

        $this->validate();

        $data = UpdateCampaignFrameData::from([
            'name' => $this->name,
            'description' => $this->description,
            'complexity_rating' => ComplexityRating::from($this->complexity_rating),
            'is_public' => $this->is_public,
            'pitch' => $this->pitch,
            'tone_and_themes' => $this->tone_and_themes,
            'background_overview' => $this->background_overview,
            'setting_guidance' => $this->setting_guidance,
            'principles' => $this->principles,
            'setting_distinctions' => $this->setting_distinctions,
            'inciting_incident' => $this->inciting_incident,
            'special_mechanics' => $this->special_mechanics,
            'session_zero_questions' => $this->session_zero_questions,
        ]);

        $action = new UpdateCampaignFrameAction();
        return $action->execute($this->frame, $data);
    }

    // Array management methods (same as CreateCampaignFrameForm)
    public function addPitchItem(): void
    {
        $this->pitch[] = '';
    }

    public function removePitchItem(int $index): void
    {
        unset($this->pitch[$index]);
        $this->pitch = array_values($this->pitch);
    }

    public function addToneAndThemeItem(): void
    {
        $this->tone_and_themes[] = '';
    }

    public function removeToneAndThemeItem(int $index): void
    {
        unset($this->tone_and_themes[$index]);
        $this->tone_and_themes = array_values($this->tone_and_themes);
    }

    public function addSettingGuidanceItem(): void
    {
        $this->setting_guidance[] = '';
    }

    public function removeSettingGuidanceItem(int $index): void
    {
        unset($this->setting_guidance[$index]);
        $this->setting_guidance = array_values($this->setting_guidance);
    }

    public function addPrincipleItem(): void
    {
        $this->principles[] = '';
    }

    public function removePrincipleItem(int $index): void
    {
        unset($this->principles[$index]);
        $this->principles = array_values($this->principles);
    }

    public function addSettingDistinctionItem(): void
    {
        $this->setting_distinctions[] = '';
    }

    public function removeSettingDistinctionItem(int $index): void
    {
        unset($this->setting_distinctions[$index]);
        $this->setting_distinctions = array_values($this->setting_distinctions);
    }

    public function addSpecialMechanicItem(): void
    {
        $this->special_mechanics[] = ['name' => '', 'description' => ''];
    }

    public function removeSpecialMechanicItem(int $index): void
    {
        unset($this->special_mechanics[$index]);
        $this->special_mechanics = array_values($this->special_mechanics);
    }

    public function addSessionZeroQuestionItem(): void
    {
        $this->session_zero_questions[] = '';
    }

    public function removeSessionZeroQuestionItem(int $index): void
    {
        unset($this->session_zero_questions[$index]);
        $this->session_zero_questions = array_values($this->session_zero_questions);
    }
}
