<div class="relative" x-data="referenceSearch()">
    <!-- Search Input -->
    <div class="relative">
        <input 
            x-ref="searchInput"
            x-model="query"
            @input="search()"
            @focus="showResults = true"
            @keydown.escape="showResults = false"
            @keydown.arrow-down.prevent="navigateResults(1)"
            @keydown.arrow-up.prevent="navigateResults(-1)"
            @keydown.enter.prevent="selectResult()"
            type="search" 
            placeholder="Search reference..." 
            class="w-full px-4 py-2 pl-10 pr-4 text-sm text-white placeholder-slate-400 bg-slate-800/50 border border-slate-600/50 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400/50 focus:border-amber-400/50 backdrop-blur-sm"
        />
        
        <!-- Search Icon -->
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>

        <!-- Loading Indicator -->
        <div x-show="loading" class="absolute inset-y-0 right-0 flex items-center pr-3">
            <svg class="w-4 h-4 text-amber-400 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </div>

    <!-- Search Results -->
    <div 
        x-show="showResults && (results.length > 0 || (query.length > 0 && !loading))"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.away="showResults = false"
        class="absolute z-50 w-full mt-2 bg-slate-900/95 backdrop-blur-xl border border-slate-700/50 rounded-xl shadow-2xl max-h-96 overflow-y-auto"
    >
        <!-- Results -->
        <template x-if="results.length > 0">
            <div class="p-2">
                <template x-for="(result, index) in results.slice(0, 10)" :key="result.id">
                    <a 
                        :href="result.url"
                        @mouseenter="selectedIndex = index"
                        :class="selectedIndex === index ? 'bg-amber-500/20 border-amber-400/50' : 'border-transparent hover:bg-slate-800/50'"
                        class="block p-3 rounded-lg border transition-all duration-150 mb-1 last:mb-0"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-medium text-white text-sm truncate" x-text="result.title"></h3>
                                <p class="text-xs text-slate-400 mt-1 line-clamp-2" x-text="getSnippet(result)"></p>
                            </div>
                            <div class="ml-2 flex-shrink-0">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-slate-700/50 text-slate-300">
                                    <span x-text="getCategoryFromPage(result.page)"></span>
                                </span>
                            </div>
                        </div>
                    </a>
                </template>
            </div>
        </template>

        <!-- No Results -->
        <template x-if="query.length > 0 && results.length === 0 && !loading">
            <div class="p-4 text-center">
                <div class="text-slate-400 text-sm">
                    <svg class="w-8 h-8 mx-auto mb-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0120 12a8 8 0 10-8 8 4 4 0 01-4-4z" />
                    </svg>
                    <p>No results found for "<span x-text="query"></span>"</p>
                    <p class="text-xs mt-1">Try different keywords or check spelling</p>
                </div>
            </div>
        </template>

        <!-- Search Tips -->
        <template x-if="query.length === 0">
            <div class="p-4 text-center">
                <div class="text-slate-400 text-sm">
                    <svg class="w-8 h-8 mx-auto mb-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <p>Search the DaggerHeart reference</p>
                    <p class="text-xs mt-1">Try "combat", "domains", "character creation"</p>
                </div>
            </div>
        </template>
    </div>

    <script>
    function referenceSearch() {
        return {
            query: '',
            results: [],
            showResults: false,
            loading: false,
            selectedIndex: -1,
            miniSearch: null,

            async init() {
                await this.loadSearchIndex();
            },

            async loadSearchIndex() {
                try {
                    this.loading = true;
                    const response = await fetch('/docs-index.json', { 
                        cache: 'no-store',
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    
                    const indexData = await response.json();
                    
                    // Import MiniSearch dynamically
                    const { default: MiniSearch } = await import('https://cdn.jsdelivr.net/npm/minisearch@6/dist/es/index.js');
                    this.miniSearch = MiniSearch.loadJSON(indexData);
                    
                    console.log('Search index loaded successfully');
                } catch (error) {
                    console.error('Failed to load search index:', error);
                    // Fallback: show message that search is unavailable
                } finally {
                    this.loading = false;
                }
            },

            search() {
                if (!this.miniSearch) {
                    this.results = [];
                    return;
                }

                const trimmedQuery = this.query.trim();
                
                if (trimmedQuery.length < 2) {
                    this.results = [];
                    this.selectedIndex = -1;
                    return;
                }

                try {
                    this.loading = true;
                    const searchResults = this.miniSearch.search(trimmedQuery, { 
                        combineWith: 'AND',
                        prefix: true,
                        fuzzy: 0.2
                    });
                    
                    this.results = searchResults.slice(0, 20);
                    this.selectedIndex = this.results.length > 0 ? 0 : -1;
                } catch (error) {
                    console.error('Search error:', error);
                    this.results = [];
                    this.selectedIndex = -1;
                } finally {
                    this.loading = false;
                }
            },

            navigateResults(direction) {
                if (this.results.length === 0) return;
                
                this.selectedIndex += direction;
                
                if (this.selectedIndex < 0) {
                    this.selectedIndex = this.results.length - 1;
                } else if (this.selectedIndex >= this.results.length) {
                    this.selectedIndex = 0;
                }
            },

            selectResult() {
                if (this.selectedIndex >= 0 && this.selectedIndex < this.results.length) {
                    const result = this.results[this.selectedIndex];
                    window.location.href = result.url;
                }
            },

            getSnippet(result) {
                if (!result.match || !result.match.content) {
                    return result.content ? result.content.slice(0, 120) + '...' : '';
                }
                
                // Get the matched content snippet
                const snippet = result.match.content.slice(0, 120);
                return snippet + (snippet.length < result.content.length ? '...' : '');
            },

            getCategoryFromPage(page) {
                if (['what-is-this', 'the-basics'].includes(page)) return 'Introduction';
                if (page === 'character-creation') return 'Character Creation';
                if (['domains', 'classes', 'ancestries', 'communities'].includes(page)) return 'Core Materials';
                if (page.endsWith('-abilities')) return 'Domain Abilities';
                if (['flow-of-the-game', 'core-gameplay-loop', 'the-spotlight', 'turn-order-and-action-economy', 'making-moves-and-taking-action', 'combat', 'stress', 'attacking', 'maps-range-and-movement', 'conditions', 'downtime', 'death', 'additional-rules', 'leveling-up', 'multiclassing'].includes(page)) return 'Core Mechanics';
                if (['equipment', 'weapons', 'combat-wheelchair', 'armor', 'loot', 'consumables', 'gold'].includes(page)) return 'Equipment';
                if (['gm-guidance', 'core-gm-mechanics', 'adversaries', 'environments', 'additional-gm-guidance', 'campaign-frames'].includes(page)) return 'GM Resources';
                return 'Reference';
            }
        }
    }
    </script>

    <style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    </style>
</div>