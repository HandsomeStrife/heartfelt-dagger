<div x-data="{ 
    showResults: @entangle('show_results'),
    searchQuery: @entangle('search_query'),
    selectedPage: @entangle('selected_page_key'),
    isSearching: false,
}" 
class="relative">
    <!-- Search Input -->
    <div class="relative">
        <input type="text" 
               wire:model.live.debounce.300ms="search_query"
               placeholder="{{ $is_sidebar ? 'Search reference pages...' : 'Search all reference content...' }}"
               dusk="searchInput"
               @input="isSearching = $event.target.value.length >= 2"
               wire:loading.attr="disabled"
               class="w-full {{ $is_sidebar ? 'px-3 py-2 text-sm' : 'px-4 py-2 text-sm' }} bg-slate-800/50 border border-slate-600/50 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-colors">
        
        <!-- Search Icon / Loading Spinner -->
        <div class="absolute {{ $is_sidebar ? 'right-2 top-2' : 'right-3 top-2' }}">
            <!-- Loading Spinner -->
            <div wire:loading wire:target="search_query" class="animate-spin">
                <svg class="{{ $is_sidebar ? 'w-4 h-4' : 'w-5 h-5' }} text-amber-400" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            
            <!-- Search Icon -->
            <div wire:loading.remove wire:target="search_query">
                <svg class="{{ $is_sidebar ? 'w-4 h-4' : 'w-5 h-5' }} text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>
        
        <!-- Clear Button -->
        @if($search_query)
            <button wire:click="clearSearch" 
                    class="absolute {{ $is_sidebar ? 'right-8 top-2' : 'right-10 top-2' }} text-slate-400 hover:text-white transition-colors">
                <svg class="{{ $is_sidebar ? 'w-4 h-4' : 'w-5 h-5' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        @endif
    </div>


    <!-- Search Results Dropdown -->
    @if($show_results && count($search_results) > 0)
        <div x-show="showResults" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             @click.away="showResults = false"
             class="absolute z-50 w-full mt-2 bg-slate-800/95 backdrop-blur-xl border border-slate-600/50 rounded-lg shadow-xl max-h-96 overflow-y-auto">
            
            <div class="{{ $is_sidebar ? 'p-2' : 'p-3' }}">
                <div class="text-xs text-slate-400 mb-2 px-2">{{ count($search_results) }} result{{ count($search_results) === 1 ? '' : 's' }} found</div>
                
                <div class="space-y-1">
                    @foreach($search_results as $result)
                        <button wire:click="selectPage('{{ $result['key'] }}', '{{ $result['section_anchor'] ?? '' }}')"
                                @click="showResults = false"
                                dusk="searchResult"
                                class="w-full text-left p-2 rounded-lg hover:bg-slate-700/50 transition-colors group">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-2 mb-1">
                                        <h4 class="{{ $is_sidebar ? 'text-sm' : 'text-base' }} font-medium text-white group-hover:text-amber-400 transition-colors truncate">
                                            {{ $result['title'] }}
                                        </h4>
                                        
                                        <!-- Result Type Badge -->
                                        @php
                                            $hasHighlight = ($result['has_highlight'] ?? false) || str_contains($result['snippet'] ?? '', '<mark>');
                                            $badgeColor = $result['type'] === 'page' ? 'blue' : ($result['type'] === 'ability' ? 'purple' : 'green');
                                            if ($hasHighlight) $badgeColor = 'amber';
                                        @endphp
                                        <span class="px-2 py-1 bg-{{ $badgeColor }}-500/20 text-{{ $badgeColor }}-300 text-xs rounded flex-shrink-0 {{ $hasHighlight ? 'ring-1 ring-amber-400/50' : '' }}">
                                            {{ $hasHighlight ? '★ ' : '' }}{{ ucfirst($result['type']) }}
                                        </span>
                                    </div>
                                    
                                    @if(isset($result['parent_page']))
                                        <div class="text-xs text-slate-400 mb-1">
                                            in {{ $result['parent_page'] }}
                                        </div>
                                    @endif
                                    
                                    @if(isset($result['snippet']))
                                        <div class="{{ $is_sidebar ? 'text-xs' : 'text-sm' }} text-slate-300 line-clamp-2">
                                            {!! $result['snippet'] !!}
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Relevance Score (for debugging, hidden in production) -->
                                @if(config('app.debug'))
                                    <div class="text-xs text-slate-500 ml-2">
                                        {{ number_format($result['score'], 1) }}
                                    </div>
                                @endif
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Selected Page Content (for sidebar display) -->
    @if($is_sidebar && $selected_page_key && !empty($selected_page_content))
        <div class="mt-4 bg-slate-800/30 rounded-lg border border-slate-700/50">
            <!-- Content Header -->
            <div class="p-3 border-b border-slate-700/50">
                <div class="flex items-center justify-between">
                    <h4 class="text-white font-medium text-sm">{{ $selected_page_content['title'] ?? 'Reference Page' }}</h4>
                    <button wire:click="clearSearch" 
                            class="text-slate-400 hover:text-white transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Content Display -->
            <div class="p-3 max-h-96 overflow-y-auto">
                @if($selected_page_content['content_type'] === 'json')
                    @include('reference.partials.sidebar-json-content', [
                        'data' => $selected_page_content['json_data'] ?? [],
                        'source' => $selected_page_content['data_source'] ?? ''
                    ])
                @elseif($selected_page_content['content_type'] === 'domain-abilities')
                    @include('reference.partials.sidebar-domain-abilities', [
                        'abilities' => $selected_page_content['abilities'] ?? [],
                        'domain_info' => $selected_page_content['domain_info'] ?? [],
                        'domain_key' => $selected_page_content['domain_key'] ?? ''
                    ])
                @else
                    <div class="text-slate-300 text-sm">
                        <p class="mb-3">This reference page contains detailed information about {{ strtolower($selected_page_content['title']) }}.</p>
                        <a href="{{ route('reference.page', $selected_page_key) }}" 
                           target="_blank"
                           class="inline-flex items-center text-amber-400 hover:text-amber-300 transition-colors">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                            View Full Page
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Custom Styles for Search Results -->
    <style>
        mark {
            background-color: rgba(245, 158, 11, 0.3);
            color: #fbbf24;
            padding: 1px 2px;
            border-radius: 2px;
        }
        
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</div>
