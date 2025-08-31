<?php

declare(strict_types=1);

namespace App\Livewire\Forms\CampaignFrame;

use Domain\CampaignFrame\Actions\CreateCampaignFrameAction;
use Domain\CampaignFrame\Data\CreateCampaignFrameData;
use Domain\CampaignFrame\Enums\ComplexityRating;
use Domain\CampaignFrame\Models\CampaignFrame;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Form;

class CreateCampaignFrameForm extends Form
{
    #[Validate('required|string|max:100')]
    public string $name = '';

    #[Validate('required|string|max:500')]
    public string $description = '';

    #[Validate('required|integer|min:1|max:4')]
    public int $complexity_rating = 1;

    public bool $is_public = false;

    public array $pitch = [];
    public array $touchstones = [];
    public array $tone = [];
    public array $themes = [];
    public array $player_principles = [];
    public array $gm_principles = [];
    public array $community_guidance = [];
    public array $ancestry_guidance = [];
    public array $class_guidance = [];

    #[Validate('string|max:2000')]
    public string $background_overview = '';

    public array $setting_guidance = [];
    public array $setting_distinctions = [];

    #[Validate('string|max:1000')]
    public string $inciting_incident = '';

    public array $special_mechanics = [];
    public array $campaign_mechanics = [];
    public array $session_zero_questions = [];

    public function save(): CampaignFrame
    {
        $this->validate();

        $data = CreateCampaignFrameData::from([
            'name' => $this->name,
            'description' => $this->description,
            'complexity_rating' => ComplexityRating::from($this->complexity_rating),
            'is_public' => $this->is_public,
            'pitch' => $this->pitch,
            'touchstones' => $this->touchstones,
            'tone' => $this->tone,
            'themes' => $this->themes,
            'player_principles' => $this->player_principles,
            'gm_principles' => $this->gm_principles,
            'community_guidance' => $this->community_guidance,
            'ancestry_guidance' => $this->ancestry_guidance,
            'class_guidance' => $this->class_guidance,
            'background_overview' => $this->background_overview,
            'setting_guidance' => $this->setting_guidance,
            'setting_distinctions' => $this->setting_distinctions,
            'inciting_incident' => $this->inciting_incident,
            'special_mechanics' => $this->special_mechanics,
            'campaign_mechanics' => $this->campaign_mechanics,
            'session_zero_questions' => $this->session_zero_questions,
        ]);

        $action = new CreateCampaignFrameAction();
        return $action->execute($data, Auth::user());
    }

    public function addPitchItem(): void
    {
        $this->pitch[] = '';
    }

    public function removePitchItem(int $index): void
    {
        unset($this->pitch[$index]);
        $this->pitch = array_values($this->pitch);
    }

    public function addTouchstoneItem(): void
    {
        $this->touchstones[] = '';
    }

    public function removeTouchstoneItem(int $index): void
    {
        unset($this->touchstones[$index]);
        $this->touchstones = array_values($this->touchstones);
    }

    public function addToneItem(): void
    {
        $this->tone[] = '';
    }

    public function removeToneItem(int $index): void
    {
        unset($this->tone[$index]);
        $this->tone = array_values($this->tone);
    }

    public function addThemeItem(): void
    {
        $this->themes[] = '';
    }

    public function removeThemeItem(int $index): void
    {
        unset($this->themes[$index]);
        $this->themes = array_values($this->themes);
    }

    public function addPlayerPrincipleItem(): void
    {
        $this->player_principles[] = '';
    }

    public function removePlayerPrincipleItem(int $index): void
    {
        unset($this->player_principles[$index]);
        $this->player_principles = array_values($this->player_principles);
    }

    public function addGmPrincipleItem(): void
    {
        $this->gm_principles[] = '';
    }

    public function removeGmPrincipleItem(int $index): void
    {
        unset($this->gm_principles[$index]);
        $this->gm_principles = array_values($this->gm_principles);
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

    public function addCampaignMechanicItem(): void
    {
        $this->campaign_mechanics[] = '';
    }

    public function removeCampaignMechanicItem(int $index): void
    {
        unset($this->campaign_mechanics[$index]);
        $this->campaign_mechanics = array_values($this->campaign_mechanics);
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
