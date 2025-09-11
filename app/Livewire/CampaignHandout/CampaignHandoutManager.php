<?php

namespace App\Livewire\CampaignHandout;

use App\Livewire\Forms\CampaignHandout\CampaignHandoutFormData;
use Domain\Campaign\Models\Campaign;
use Domain\CampaignHandout\Actions\CreateCampaignHandoutAction;
use Domain\CampaignHandout\Actions\DeleteCampaignHandoutAction;
use Domain\CampaignHandout\Actions\ToggleHandoutSidebarVisibilityAction;
use Domain\CampaignHandout\Actions\UpdateCampaignHandoutAction;
use Domain\CampaignHandout\Data\CreateCampaignHandoutData;
use Domain\CampaignHandout\Data\UpdateCampaignHandoutData;
use Domain\CampaignHandout\Enums\HandoutAccessLevel;
use Domain\CampaignHandout\Repositories\CampaignHandoutRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Usernotnull\Toast\Concerns\WireToast;

class CampaignHandoutManager extends Component
{
    use WithFileUploads, WireToast;

    public Campaign $campaign;

    public CampaignHandoutFormData $form;

    public string $view_mode = 'grid'; // grid, list

    public string $search_query = '';

    public string $filter_access_level = '';

    public string $filter_file_type = '';

    public bool $show_form = false;

    public bool $show_preview_modal = false;

    public ?int $editing_handout_id = null;

    public ?int $preview_handout_id = null;

    // Data collections
    public Collection $handouts;

    public Collection $campaign_members;

    private CampaignHandoutRepository $handout_repository;

    public function mount(Campaign $campaign): void
    {
        $this->campaign = $campaign;
        $this->handout_repository = new CampaignHandoutRepository;
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->handouts = $this->handout_repository->getForCampaign($this->campaign, Auth::user());
        $this->campaign_members = $this->campaign->members()->with('user')->get();
    }

    public function showCreateForm(): void
    {
        $this->form->reset();
        $this->editing_handout_id = null;
        $this->show_form = true;
    }

    public function showEditForm(int $handoutId): void
    {
        $handout = $this->handout_repository->findById($handoutId);
        
        if (!$handout) {
            $this->toast()->error('Handout not found');
            return;
        }

        $this->form->setHandout($handout);
        $this->editing_handout_id = $handoutId;
        $this->show_form = true;
    }

    public function cancelForm(): void
    {
        $this->form->reset();
        $this->editing_handout_id = null;
        $this->show_form = false;
    }

    public function save(): void
    {
        $user = Auth::user();
        if (!$user) {
            $this->toast()->error('You must be logged in to upload handouts');
            return;
        }

        if ($this->editing_handout_id) {
            $this->updateHandout();
        } else {
            $this->createHandout();
        }
    }

    private function createHandout(): void
    {
        $this->form->validate();

        try {
            $createAction = new CreateCampaignHandoutAction;
            
            $data = CreateCampaignHandoutData::from([
                'campaign_id' => $this->campaign->id,
                'creator_id' => Auth::id(),
                'title' => $this->form->title,
                'description' => $this->form->description,
                'file' => $this->form->file,
                'access_level' => $this->form->getAccessLevelEnum(),
                'is_visible_in_sidebar' => $this->form->is_visible_in_sidebar,
                'authorized_user_ids' => $this->form->authorized_user_ids,
            ]);

            $createAction->execute($data);

            $this->toast()->success('Handout uploaded successfully!');
            $this->cancelForm();
            $this->loadData();
        } catch (\Exception $e) {
            $this->toast()->error('Failed to upload handout: ' . $e->getMessage());
        }
    }

    private function updateHandout(): void
    {
        $this->form->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'access_level' => 'required|in:gm_only,all_players,specific_players',
            'is_visible_in_sidebar' => 'boolean',
            'is_published' => 'boolean',
            'authorized_user_ids' => 'array',
        ]);

        try {
            $updateAction = new UpdateCampaignHandoutAction;
            
            $data = UpdateCampaignHandoutData::from([
                'id' => $this->editing_handout_id,
                'title' => $this->form->title,
                'description' => $this->form->description,
                'access_level' => $this->form->getAccessLevelEnum(),
                'is_visible_in_sidebar' => $this->form->is_visible_in_sidebar,
                'is_published' => $this->form->is_published,
                'authorized_user_ids' => $this->form->authorized_user_ids,
            ]);

            $updateAction->execute($data);

            $this->toast()->success('Handout updated successfully!');
            $this->cancelForm();
            $this->loadData();
        } catch (\Exception $e) {
            $this->toast()->error('Failed to update handout: ' . $e->getMessage());
        }
    }

    public function deleteHandout(int $handoutId): void
    {
        $user = Auth::user();
        if (!$user) {
            $this->toast()->error('You must be logged in to delete handouts');
            return;
        }

        try {
            $deleteAction = new DeleteCampaignHandoutAction;
            $deleteAction->execute($handoutId, $user);

            $this->toast()->success('Handout deleted successfully!');
            $this->loadData();
        } catch (\Exception $e) {
            $this->toast()->error('Failed to delete handout: ' . $e->getMessage());
        }
    }

    public function toggleSidebarVisibility(int $handoutId): void
    {
        $user = Auth::user();
        if (!$user) {
            $this->toast()->error('You must be logged in to modify handouts');
            return;
        }

        try {
            $toggleAction = new ToggleHandoutSidebarVisibilityAction;
            $toggleAction->execute($handoutId, $user);

            $this->toast()->success('Sidebar visibility updated!');
            $this->loadData();
        } catch (\Exception $e) {
            $this->toast()->error('Failed to update visibility: ' . $e->getMessage());
        }
    }

    public function showPreview(int $handoutId): void
    {
        $handout = $this->handout_repository->findById($handoutId);
        
        if (!$handout || !$handout->isPreviewable()) {
            $this->toast()->error('This file cannot be previewed');
            return;
        }

        $this->preview_handout_id = $handoutId;
        $this->show_preview_modal = true;
    }

    public function closePreview(): void
    {
        $this->preview_handout_id = null;
        $this->show_preview_modal = false;
    }

    public function getFilteredHandouts(): Collection
    {
        $filtered = $this->handouts;

        // Apply search filter
        if (!empty($this->search_query)) {
            $filtered = $filtered->filter(function ($handout) {
                return str_contains(strtolower($handout->title), strtolower($this->search_query)) ||
                       str_contains(strtolower($handout->description ?? ''), strtolower($this->search_query)) ||
                       str_contains(strtolower($handout->original_file_name), strtolower($this->search_query));
            });
        }

        // Apply access level filter
        if (!empty($this->filter_access_level)) {
            $filtered = $filtered->filter(function ($handout) {
                return $handout->access_level->value === $this->filter_access_level;
            });
        }

        // Apply file type filter
        if (!empty($this->filter_file_type)) {
            $filtered = $filtered->filter(function ($handout) {
                return $handout->file_type->value === $this->filter_file_type;
            });
        }

        return $filtered;
    }

    public function getAccessLevelOptions(): array
    {
        return [
            ['value' => '', 'label' => 'All Access Levels'],
            ['value' => HandoutAccessLevel::GM_ONLY->value, 'label' => HandoutAccessLevel::GM_ONLY->label()],
            ['value' => HandoutAccessLevel::ALL_PLAYERS->value, 'label' => HandoutAccessLevel::ALL_PLAYERS->label()],
            ['value' => HandoutAccessLevel::SPECIFIC_PLAYERS->value, 'label' => HandoutAccessLevel::SPECIFIC_PLAYERS->label()],
        ];
    }

    public function render()
    {
        return view('livewire.campaign-handout.campaign-handout-manager', [
            'filtered_handouts' => $this->getFilteredHandouts(),
            'access_level_options' => $this->getAccessLevelOptions(),
        ]);
    }
}
