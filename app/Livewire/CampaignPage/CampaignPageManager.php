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

    protected $listeners = [
        'page-saved' => 'refreshPages',
        'form-cancelled' => 'closeForm',
        'page-deleted' => 'refreshPages',
    ];

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

    public function render()
    {
        return view('livewire.campaign-page.campaign-page-manager');
    }
}
