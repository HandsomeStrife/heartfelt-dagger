<?php

namespace App\Livewire\Forms\CampaignPage;

use Domain\Campaign\Models\Campaign;
use Domain\CampaignPage\Actions\CreateCampaignPageAction;
use Domain\CampaignPage\Actions\UpdateCampaignPageAction;
use Domain\CampaignPage\Data\CreateCampaignPageData;
use Domain\CampaignPage\Data\UpdateCampaignPageData;
use Domain\CampaignPage\Enums\PageAccessLevel;
use Domain\CampaignPage\Models\CampaignPage;
use Domain\User\Models\User;
use Livewire\Attributes\Validate;
use Livewire\Form;

class CampaignPageFormData extends Form
{
    public ?CampaignPage $page = null;
    public Campaign $campaign;

    #[Validate('required|string|max:200')]
    public string $title = '';

    #[Validate('nullable|string')]
    public string $content = '';

    #[Validate('nullable|array')]
    public array $category_tags = [];

    #[Validate('required|in:gm_only,all_players,specific_players')]
    public string $access_level = 'gm_only';

    #[Validate('nullable|integer|exists:campaign_pages,id')]
    public ?int $parent_id = null;

    #[Validate('integer|min:0')]
    public int $display_order = 0;

    #[Validate('boolean')]
    public bool $is_published = true;

    #[Validate('nullable|array')]
    public array $authorized_user_ids = [];

    public function setCampaign(Campaign $campaign): self
    {
        $this->campaign = $campaign;
        return $this;
    }

    public function setPage(?CampaignPage $page): self
    {
        $this->page = $page;
        
        if ($page) {
            $this->fill([
                'title' => $page->title ?? '',
                'content' => $page->content ?? '',
                'category_tags' => $page->category_tags ?? [],
                'access_level' => $page->access_level?->value ?? 'gm_only',
                'parent_id' => $page->parent_id,
                'display_order' => $page->display_order ?? 0,
                'is_published' => $page->is_published ?? true,
                'authorized_user_ids' => $page->authorizedUsers->pluck('id')->toArray(),
            ]);
        } else {
            // Ensure form has proper default values for create mode
            $this->title = '';
            $this->content = '';
            $this->category_tags = [];
            $this->access_level = 'gm_only';
            $this->parent_id = null;
            $this->display_order = 0;
            $this->is_published = true;
            $this->authorized_user_ids = [];
        }

        return $this;
    }

    public function save(User $user): CampaignPage
    {
        $this->validate();

        if ($this->page && $this->page->exists) {
            return $this->updatePage();
        }

        return $this->createPage($user);
    }

    private function createPage(User $user): CampaignPage
    {
        $createData = CreateCampaignPageData::from([
            'campaign_id' => $this->campaign->id,
            'parent_id' => $this->parent_id,
            'title' => $this->title,
            'content' => $this->content,
            'category_tags' => $this->category_tags,
            'access_level' => PageAccessLevel::from($this->access_level),
            'display_order' => $this->display_order,
            'is_published' => $this->is_published,
            'authorized_user_ids' => $this->authorized_user_ids,
        ]);

        $action = new CreateCampaignPageAction();
        return $action->execute($createData, $user);
    }

    private function updatePage(): CampaignPage
    {
        $updateData = UpdateCampaignPageData::from([
            'parent_id' => $this->parent_id,
            'title' => $this->title,
            'content' => $this->content,
            'category_tags' => $this->category_tags,
            'access_level' => PageAccessLevel::from($this->access_level),
            'display_order' => $this->display_order,
            'is_published' => $this->is_published,
            'authorized_user_ids' => $this->authorized_user_ids,
        ]);

        $action = new UpdateCampaignPageAction();
        return $action->execute($this->page, $updateData);
    }

    public function reset(...$properties): void
    {
        if (empty($properties)) {
            // Reset all properties if none specified
            $this->page = null;
            $this->title = '';
            $this->content = '';
            $this->category_tags = [];
            $this->access_level = 'gm_only';
            $this->parent_id = null;
            $this->display_order = 0;
            $this->is_published = true;
            $this->authorized_user_ids = [];
            $this->resetErrorBag();
        } else {
            // Call parent's reset with specified properties
            parent::reset(...$properties);
        }
    }

    public function getAccessLevelOptions(): array
    {
        return PageAccessLevel::options();
    }
}
