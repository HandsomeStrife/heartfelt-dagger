<?php

namespace App\Livewire\Forms\CampaignHandout;

use Domain\CampaignHandout\Data\CampaignHandoutData;
use Domain\CampaignHandout\Enums\HandoutAccessLevel;
use Livewire\Attributes\Validate;
use Livewire\Form;

class CampaignHandoutFormData extends Form
{
    public ?int $id = null;

    #[Validate('required|string|max:200')]
    public string $title = '';

    #[Validate('nullable|string|max:1000')]
    public ?string $description = null;

    #[Validate('required|in:gm_only,all_players,specific_players')]
    public string $access_level = 'gm_only';

    #[Validate('boolean')]
    public bool $is_visible_in_sidebar = false;

    #[Validate('boolean')]
    public bool $is_published = true;

    #[Validate('array')]
    public array $authorized_user_ids = [];

    #[Validate('required|file|max:10240')] // 10MB max
    public $file = null;

    public function setHandout(?CampaignHandoutData $handout): void
    {
        if ($handout) {
            $this->id = $handout->id;
            $this->title = $handout->title;
            $this->description = $handout->description;
            $this->access_level = $handout->access_level->value;
            $this->is_visible_in_sidebar = $handout->is_visible_in_sidebar;
            $this->is_published = $handout->is_published;
            
            // Load authorized user IDs if specific access
            if ($handout->access_level === HandoutAccessLevel::SPECIFIC_PLAYERS && $handout->authorized_users) {
                $this->authorized_user_ids = $handout->authorized_users->pluck('id')->toArray();
            }
        }
    }

    public function reset(...$properties): void
    {
        $this->id = null;
        $this->title = '';
        $this->description = null;
        $this->access_level = 'gm_only';
        $this->is_visible_in_sidebar = false;
        $this->is_published = true;
        $this->authorized_user_ids = [];
        $this->file = null;

        if (empty($properties)) {
            $this->resetErrorBag();
        }
    }

    public function getAccessLevelEnum(): HandoutAccessLevel
    {
        return HandoutAccessLevel::from($this->access_level);
    }

    public function requiresSpecificAccess(): bool
    {
        return $this->access_level === HandoutAccessLevel::SPECIFIC_PLAYERS->value;
    }
}
