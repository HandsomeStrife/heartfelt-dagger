<?php

namespace App\Livewire\CampaignPage;

use App\Livewire\Forms\CampaignPage\CampaignPageFormData;
use Domain\Campaign\Models\Campaign;
use Domain\CampaignPage\Models\CampaignPage;
use Domain\CampaignPage\Repositories\CampaignPageRepository;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CampaignPageForm extends Component
{
    public CampaignPageFormData $form;

    public Campaign $campaign;

    public ?CampaignPage $page = null;

    public bool $showForm = false;

    // Data for form options
    public array $parentPageOptions = [];

    public array $campaignMembers = [];

    public array $categoryTags = [];

    public function mount(Campaign $campaign, ?CampaignPage $page = null)
    {
        $this->campaign = $campaign;
        $this->page = $page;

        $this->form->setCampaign($campaign);
        $this->form->setPage($page);

        $this->loadFormOptions();
    }

    public function getModeProperty(): string
    {
        return $this->page !== null && $this->page->exists ? 'edit' : 'create';
    }

    public function loadFormOptions()
    {
        $repository = new CampaignPageRepository;
        $user = Auth::user();

        // Get available parent pages (excluding current page to prevent circular references)
        $allPages = $repository->getAccessiblePagesForCampaign($this->campaign, $user);
        $this->parentPageOptions = $allPages
            ->filter(fn ($pageData) => ! $this->page || $pageData->id !== $this->page->id)
            ->map(fn ($pageData) => [
                'value' => $pageData->id,
                'label' => str_repeat('— ', $pageData->depth_level ?? 0).$pageData->title,
            ])
            ->prepend(['value' => null, 'label' => 'No Parent (Root Level)'])
            ->toArray();

        // Get campaign members for specific access
        $this->campaignMembers = $this->campaign->members()
            ->with('user')
            ->get()
            ->map(fn ($member) => [
                'value' => $member->user_id,
                'label' => $member->user->username,
            ])
            ->toArray();

        // Get existing category tags
        $this->categoryTags = $repository->getCategoryTagsForCampaign($this->campaign)->toArray();
    }

    public function save()
    {
        try {
            $user = Auth::user();
            $savedPage = $this->form->save($user);

            $this->dispatch('page-saved', [
                'page_id' => $savedPage->id,
                'title' => $savedPage->title,
                'mode' => $this->mode,
            ]);

            session()->flash('success', $this->mode === 'create'
                ? 'Campaign page created successfully!'
                : 'Campaign page updated successfully!');

            if ($this->mode === 'create') {
                $this->form->reset();
                $this->showForm = false;
            }

        } catch (\Exception $e) {
            $this->addError('form', 'Failed to save page: '.$e->getMessage());
        }
    }

    public function cancel()
    {
        $this->form->reset();
        $this->showForm = false;
        $this->dispatch('form-cancelled');
    }

    public function toggleForm()
    {
        $this->showForm = ! $this->showForm;
        if (! $this->showForm) {
            $this->form->reset();
        }
    }

    public function addCategoryTag(string $tag)
    {
        $tag = trim($tag);
        if (! empty($tag) && ! in_array($tag, $this->form->category_tags)) {
            $this->form->category_tags[] = $tag;
        }
    }

    public function removeCategoryTag(int $index)
    {
        unset($this->form->category_tags[$index]);
        $this->form->category_tags = array_values($this->form->category_tags);
    }

    public function canEdit(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if ($this->page && $this->page->exists) {
            return $this->page->canBeEditedBy($user);
        }

        // For new pages, check if user can edit campaign
        return $this->campaign->isCreator($user);
    }

    public function render()
    {
        if (! $this->canEdit()) {
            return '<div><!-- No permission to edit --></div>';
        }

        return view('livewire.campaign-page.campaign-page-form');
    }
}
