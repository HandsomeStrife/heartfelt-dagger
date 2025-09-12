<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Http\Controllers\ReferenceSearchController;
use Illuminate\Http\Request;
use Livewire\Component;

class ReferenceSearch extends Component
{
    public string $search_query = '';
    public array $search_results = [];
    public bool $show_results = false;
    public string $selected_page_key = '';
    public array $selected_page_content = [];
    public bool $is_sidebar = false;

    public function mount(bool $isSidebar = false): void
    {
        $this->is_sidebar = $isSidebar;
    }

    public function updatedSearchQuery(): void
    {
        if (strlen($this->search_query) < 2) {
            $this->search_results = [];
            $this->show_results = false;
            return;
        }

        // Perform search directly using the controller
        try {
            $searchController = new \App\Http\Controllers\JsonReferenceSearchController();
            $request = new \Illuminate\Http\Request(['q' => $this->search_query]);
            $response = $searchController->search($request);
            
            $this->search_results = $response->getData(true);
            $this->show_results = count($this->search_results) > 0;
        } catch (\Exception $e) {
            $this->search_results = [];
            $this->show_results = false;
            // Log the error for debugging
            \Illuminate\Support\Facades\Log::error('Reference search error: ' . $e->getMessage());
        }
    }

    public function selectPage(string $pageKey, string $anchor = ''): void
    {
        $this->selected_page_key = $pageKey;
        $this->show_results = false;
        
        // Load the page content
        $this->loadPageContent($pageKey);
        
        // Navigate to the page
        if ($this->is_sidebar) {
            // For sidebar, emit event
            $this->dispatch('page-selected', $pageKey, $anchor);
        } else {
            // For main page, redirect to the reference page with anchor
            $url = route('reference.page', $pageKey);
            if (!empty($anchor)) {
                $url .= '#' . $anchor;
            }
            $this->redirect($url, navigate: true);
        }
    }

    public function clearSearch(): void
    {
        $this->search_query = '';
        $this->search_results = [];
        $this->show_results = false;
        $this->selected_page_key = '';
        $this->selected_page_content = [];
    }

    private function loadPageContent(string $pageKey): void
    {
        try {
            // Use the existing ReferenceController logic to load content
            $referenceController = new \App\Http\Controllers\ReferenceController();
            $response = $referenceController->show($pageKey);
            
            // Extract the relevant data from the view response
            $viewData = $response->getData();
            
            $this->selected_page_content = [
                'title' => $viewData['title'] ?? '',
                'content_type' => $viewData['content_type'] ?? 'blade',
                'key' => $pageKey
            ];

            // Store additional data based on content type
            if (isset($viewData['json_data'])) {
                $this->selected_page_content['json_data'] = $viewData['json_data'];
                $this->selected_page_content['data_source'] = $viewData['data_source'];
            }
            
            if (isset($viewData['abilities'])) {
                $this->selected_page_content['abilities'] = $viewData['abilities'];
                $this->selected_page_content['domain_info'] = $viewData['domain_info'];
                $this->selected_page_content['domain_key'] = $viewData['domain_key'];
            }
            
            if (isset($viewData['content_view'])) {
                $this->selected_page_content['content_view'] = $viewData['content_view'];
            }
            
        } catch (\Exception $e) {
            $this->selected_page_content = [
                'title' => 'Error',
                'content_type' => 'error',
                'error' => 'Could not load page content'
            ];
        }
    }

    public function render()
    {
        return view('livewire.reference-search');
    }
}
