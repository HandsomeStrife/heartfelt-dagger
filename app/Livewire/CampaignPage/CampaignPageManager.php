<?php

namespace App\Livewire\CampaignPage;

use Domain\Campaign\Models\Campaign;
use Domain\CampaignPage\Actions\DeleteCampaignPageAction;
use Domain\CampaignPage\Actions\ReorderCampaignPagesAction;
use Domain\CampaignPage\Actions\SearchCampaignPagesAction;
use Domain\CampaignPage\Data\SearchCampaignPagesData;
use Domain\CampaignPage\Models\CampaignPage;
use Domain\CampaignPage\Repositories\CampaignPageRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CampaignPageManager extends Component
{
    public Campaign $campaign;
    public string $view_mode = 'hierarchy'; // hierarchy, list, search
    public string $search_query = '';
    public array $selected_categories = [];
    public string $selected_access_level = '';
    public bool $show_form = false;
    public ?CampaignPage $editing_page = null;

    // Data collections
    public Collection $pages;
    public Collection $available_categories;
    public array $campaign_members = [];

    public function mount(Campaign $campaign)
    {
        $this->campaign = $campaign;
        $this->loadData();
    }

    public function loadData()
    {
        $repository = new CampaignPageRepository();
        $user = Auth::user();

        // Load pages based on current view mode
        if ($this->view_mode === 'search' && !empty($this->search_query)) {
            $this->pages = $this->performSearch();
        } elseif ($this->view_mode === 'hierarchy') {
            $this->pages = $repository->getPageHierarchy($this->campaign, $user);
        } else {
            $this->pages = $repository->getAccessiblePagesForCampaign($this->campaign, $user);
        }

        // Load supporting data
        $this->available_categories = $repository->getCategoryTagsForCampaign($this->campaign);
        $this->campaign_members = $this->campaign->members()
            ->with('user')
            ->get()
            ->map(fn($member) => [
                'id' => $member->user_id,
                'username' => $member->user->username
            ])
            ->toArray();
    }

    public function performSearch(): Collection
    {
        $searchData = SearchCampaignPagesData::from([
            'query' => $this->search_query,
            'category_tags' => $this->selected_categories,
            'access_level' => $this->selected_access_level ?: null,
            'sort_by' => 'relevance',
        ]);

        $searchAction = new SearchCampaignPagesAction();
        return $searchAction->execute($this->campaign, $searchData, Auth::user());
    }

    public function setViewMode(string $mode)
    {
        $this->view_mode = $mode;
        $this->loadData();
    }

    public function search()
    {
        $this->view_mode = 'search';
        $this->loadData();
    }

    protected $listeners = [
        'page-saved' => 'handlePageSaved',
        'form-cancelled' => 'closeForm',
        'page-deleted' => 'refreshPages',
    ];

    /**
     * Called automatically when search_query is updated via wire:model.live
     */
    public function updatedSearchQuery()
    {
        // Skip if we're currently editing or showing a form
        if ($this->show_form || $this->editing_page) {
            return;
        }

        if (!empty($this->search_query)) {
            $this->view_mode = 'search';
            $this->loadData();
        } elseif ($this->view_mode === 'search') {
            $this->view_mode = 'hierarchy';
            $this->loadData();
        }
    }

    public function clearSearch()
    {
        $this->search_query = '';
        $this->selected_categories = [];
        $this->selected_access_level = '';
        $this->view_mode = 'hierarchy';
        $this->loadData();
    }

    public function createPage(?int $parent_id = null)
    {
        $this->editing_page = null;
        $this->show_form = true;
        
        // Set default parent if provided
        if ($parent_id) {
            $this->dispatch('set-parent', parent_id: $parent_id);
        }
    }

    public function editPage(int $page_id)
    {
        $this->editing_page = CampaignPage::findOrFail($page_id);
        $this->show_form = true;
    }

    public function deletePage(int $page_id)
    {
        $page = CampaignPage::findOrFail($page_id);
        
        // Check permissions
        if (!$page->canBeEditedBy(Auth::user())) {
            $this->addError('permission', 'You do not have permission to delete this page.');
            return;
        }

        try {
            $action = new DeleteCampaignPageAction();
            $action->execute($page);
            
            $this->dispatch('page-deleted');
            session()->flash('success', 'Page deleted successfully!');
        } catch (\Exception $e) {
            $this->addError('delete', 'Failed to delete page: ' . $e->getMessage());
        }
    }

    public function reorderPages(array $page_order, ?int $parent_id = null)
    {
        try {
            $action = new ReorderCampaignPagesAction();
            $action->execute($this->campaign, $page_order, $parent_id);
            
            $this->loadData();
            session()->flash('success', 'Pages reordered successfully!');
        } catch (\Exception $e) {
            $this->addError('reorder', 'Failed to reorder pages: ' . $e->getMessage());
        }
    }

    public function refreshPages()
    {
        $this->loadData();
        $this->closeForm();
    }

    public function handlePageSaved()
    {
        $this->refreshPages();
        $this->closeForm();
    }

    public function closeForm()
    {
        $this->show_form = false;
        $this->editing_page = null;
    }

    public function toggleCategory(string $category)
    {
        if (in_array($category, $this->selected_categories)) {
            $this->selected_categories = array_values(
                array_filter($this->selected_categories, fn($cat) => $cat !== $category)
            );
        } else {
            $this->selected_categories[] = $category;
        }
        
        if ($this->view_mode === 'search') {
            $this->loadData();
        }
    }

    public function canManagePages(): bool
    {
        return $this->campaign->isCreator(Auth::user());
    }

    /**
     * Get total count of all pages including children (for hierarchy view)
     */
    public function getTotalPagesCount(): int
    {
        if ($this->view_mode === 'hierarchy') {
            return $this->countPagesRecursively($this->pages);
        }
        
        return $this->pages->count();
    }

    /**
     * Recursively count pages including their children
     */
    private function countPagesRecursively(Collection $pages): int
    {
        $count = $pages->count();
        
        foreach ($pages as $page) {
            if ($page->children && $page->children->isNotEmpty()) {
                $count += $this->countPagesRecursively($page->children);
            }
        }
        
        return $count;
    }

    public function viewPage(int $page_id)
    {
        // Navigate to the page view
        return redirect()->route('campaigns.page.show', [
            'campaign' => $this->campaign->campaign_code,
            'page' => $page_id
        ]);
    }

    public function render()
    {
        return view('livewire.campaign-page.campaign-page-manager');
    }
}
