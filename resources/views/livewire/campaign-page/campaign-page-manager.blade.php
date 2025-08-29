<div class="max-w-7xl mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-outfit font-bold text-white">Campaign Pages</h1>
            <p class="text-slate-300 mt-1">Organize and manage your campaign lore, NPCs, and world-building content</p>
        </div>
        
        @if($this->canManagePages())
            <button 
                @click="$dispatch('slideover-open'); $wire.createPage()"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Create Page
            </button>
        @endif
    </div>

    <!-- Search and Filters -->
    <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-6 mb-6">
        <div class="flex flex-col lg:flex-row gap-4">
            <!-- Search Input -->
            <div class="flex-1">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search_query"
                        placeholder="Search pages..."
                        class="w-full pl-10 pr-4 py-2.5 bg-slate-800 border border-slate-600 text-white placeholder-slate-400 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors"
                    >
                </div>
            </div>

            <!-- View Mode Toggle -->
            <div class="flex bg-slate-800 rounded-lg p-1">
                <button 
                    wire:click="setViewMode('hierarchy')"
                    class="px-3 py-2 text-sm font-medium rounded-md transition-colors {{ $view_mode === 'hierarchy' ? 'bg-slate-700 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}"
                >
                    <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    Hierarchy
                </button>
                <button 
                    wire:click="setViewMode('list')"
                    class="px-3 py-2 text-sm font-medium rounded-md transition-colors {{ $view_mode === 'list' ? 'bg-slate-700 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}"
                >
                    <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    List
                </button>
            </div>

            <!-- Search Button -->
            @if(!empty($search_query))
                <button 
                    wire:click="search"
                    class="px-4 py-2.5 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors font-medium"
                >
                    Search
                </button>
            @endif
        </div>

        <!-- Category Filters -->
        @if($available_categories->isNotEmpty())
            <div class="mt-4 pt-4 border-t border-slate-600">
                <label class="block text-sm font-medium text-slate-300 mb-3">Filter by Categories</label>
                <div class="flex flex-wrap gap-2">
                    @foreach($available_categories as $category)
                        <button 
                            wire:click="toggleCategory('{{ $category }}')"
                            class="px-3 py-1.5 text-sm rounded-full border transition-colors {{ in_array($category, $selected_categories) ? 'bg-amber-500/20 border-amber-400 text-amber-300' : 'bg-slate-700 border-slate-600 text-slate-300 hover:bg-slate-600' }}"
                        >
                            {{ $category }}
                            @if(in_array($category, $selected_categories))
                                <svg class="w-3 h-3 ml-1 inline" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Clear Filters -->
        @if(!empty($search_query) || !empty($selected_categories) || !empty($selected_access_level))
            <div class="mt-4 pt-4 border-t border-slate-600">
                <button 
                    wire:click="clearSearch"
                    class="text-sm text-slate-400 hover:text-white underline"
                >
                    Clear all filters
                </button>
            </div>
        @endif
    </div>

    <!-- Pages Content -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Main Content Area -->
        <div class="lg:col-span-3">
            @if($pages->isEmpty())
                <!-- Empty State -->
                <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-12 text-center">
                    <svg class="mx-auto w-16 h-16 text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-white mb-2">No pages found</h3>
                    <p class="text-slate-300 mb-6">
                        @if($view_mode === 'search')
                            Try adjusting your search criteria or clearing filters.
                        @else
                            Start building your campaign by creating your first page.
                        @endif
                    </p>
                    @if($this->canManagePages() && $view_mode !== 'search')
                        <button 
                            @click="$dispatch('slideover-open'); $wire.createPage()"
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-medium rounded-lg transition-all duration-200"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Create Your First Page
                        </button>
                    @endif
                </div>
            @else
                <!-- Pages List -->
                <div class="space-y-3">
                    @if($view_mode === 'hierarchy')
                        @include('livewire.campaign-page.partials.hierarchy-view', ['pages' => $pages])
                    @else
                        @include('livewire.campaign-page.partials.list-view', ['pages' => $pages])
                    @endif
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <div class="sticky top-6 space-y-6">
                <!-- Quick Stats -->
                <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-6">
                    <h3 class="font-medium text-white mb-4">Campaign Stats</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Total Pages</span>
                            <span class="font-medium text-white">{{ $this->getTotalPagesCount() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Categories</span>
                            <span class="font-medium text-white">{{ $available_categories->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Members</span>
                            <span class="font-medium text-white">{{ count($campaign_members) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Popular Categories -->
                @if($available_categories->isNotEmpty())
                    <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-6">
                        <h3 class="font-medium text-white mb-4">Categories</h3>
                        <div class="space-y-2">
                            @foreach($available_categories->take(8) as $category)
                                <button 
                                    wire:click="toggleCategory('{{ $category }}')"
                                    class="block w-full text-left px-3 py-2 text-sm rounded-lg transition-colors {{ in_array($category, $selected_categories) ? 'bg-amber-500/20 text-amber-300' : 'text-slate-300 hover:bg-slate-700' }}"
                                >
                                    {{ $category }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Form Slideover -->
    <x-slideover 
        :show="$show_form"
        max-width="2xl"
        on-close="$wire.closeForm()"
    >
        <x-slot name="title">
            {{ $editing_page ? 'Edit Campaign Page' : 'Create Campaign Page' }}
        </x-slot>
        
        <x-slot name="subtitle">
            {{ $editing_page ? 'Update page details and content' : 'Add new content to your campaign' }}
        </x-slot>
        
        <x-slot name="content">
            @if($editing_page)
                <livewire:campaign-page.campaign-page-form 
                    :campaign="$campaign" 
                    :page="$editing_page"
                    key="form-edit-{{ $editing_page->id }}"
                />
            @else
                <livewire:campaign-page.campaign-page-form 
                    :campaign="$campaign"
                    key="form-create-new"
                />
            @endif
        </x-slot>
    </x-slideover>

    <!-- Flash Messages -->
    @if(session()->has('success'))
        <div 
            x-data="{ show: true }" 
            x-show="show" 
            x-transition
            x-init="setTimeout(() => show = false, 5000)"
            class="fixed bottom-4 right-4 bg-emerald-500 text-white px-6 py-3 rounded-lg shadow-lg z-50"
        >
            {{ session('success') }}
        </div>
    @endif

    <!-- Error Messages -->
    @if($errors->any())
        <div 
            x-data="{ show: true }" 
            x-show="show" 
            x-transition
            class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50"
        >
            <button @click="show = false" class="float-right ml-4">&times;</button>
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif
</div>