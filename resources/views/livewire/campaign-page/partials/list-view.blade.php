@foreach($pages as $page)
    <div class="group bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-lg hover:border-slate-600 transition-all duration-200 hover:bg-slate-800/90">
        <div class="px-4 py-3">
            <div class="flex items-center justify-between">
                <!-- Page Info - Single Line -->
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <!-- Page Title -->
                    <h3 class="text-sm font-medium text-white truncate flex-shrink">
                        {{ $page->title }}
                    </h3>

                    <!-- Breadcrumb for non-root pages (compact) -->
                    @if($page->breadcrumbs && count($page->breadcrumbs) > 0)
                        <div class="flex items-center text-xs text-slate-400 flex-shrink-0">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                            </svg>
                            {{ end($page->breadcrumbs)->title }}
                        </div>
                    @endif

                    <!-- Access Level Badge -->
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium flex-shrink-0
                        {{ $page->access_level->value === 'gm_only' ? 'bg-red-500/20 text-red-300 border border-red-500/30' : 
                           ($page->access_level->value === 'all_players' ? 'bg-blue-500/20 text-blue-300 border border-blue-500/30' : 'bg-yellow-500/20 text-yellow-300 border border-yellow-500/30') }}">
                        {{ $page->access_level->label() }}
                    </span>

                    <!-- Published Status -->
                    @if(!$page->is_published)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-700 text-slate-300 border border-slate-600 flex-shrink-0">
                            Draft
                        </span>
                    @endif

                    <!-- Categories (compact) -->
                    @if(!empty($page->category_tags))
                        <span class="text-xs text-slate-400 truncate">
                            {{ implode(' • ', array_slice($page->category_tags, 0, 2)) }}{{ count($page->category_tags) > 2 ? '...' : '' }}
                        </span>
                    @endif

                    <!-- Meta Info (compact) -->
                    <div class="flex items-center gap-3 text-xs text-slate-500 flex-shrink-0">
                        <span>{{ \Carbon\Carbon::parse($page->updated_at !== $page->created_at ? $page->updated_at : $page->created_at)->diffForHumans() }}</span>
                        @if($page->creator)
                            <span>{{ $page->creator->username }}</span>
                        @endif
                        <!-- Children Count -->
                        @if($page->children && $page->children->count() > 0)
                            <span>{{ $page->children->count() }} sub-page{{ $page->children->count() !== 1 ? 's' : '' }}</span>
                        @endif
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-1 ml-4 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0">
                    @if($this->canManagePages())
                        <!-- Add Child Page -->
                        <button 
                            @click="$dispatch('slideover-open'); $wire.createPage({{ $page->id }})"
                            class="p-1.5 text-slate-400 hover:text-amber-400 hover:bg-slate-700 rounded transition-colors"
                            title="Add child page"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </button>

                        <!-- Edit -->
                        <button 
                            @click="$dispatch('slideover-open'); $wire.editPage({{ $page->id }})"
                            class="p-1.5 text-slate-400 hover:text-blue-400 hover:bg-slate-700 rounded transition-colors"
                            title="Edit page"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>

                        <!-- Delete -->
                        <button 
                            wire:click="deletePage({{ $page->id }})"
                            wire:confirm="Are you sure you want to delete this page? Child pages will be moved to the parent level."
                            class="p-1.5 text-slate-400 hover:text-red-400 hover:bg-slate-700 rounded transition-colors"
                            title="Delete page"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    @endif

                    <!-- View/Expand -->
                    <button 
                        wire:click="viewPage({{ $page->id }})"
                        class="p-1.5 text-slate-400 hover:text-emerald-400 hover:bg-slate-700 rounded transition-colors"
                        title="View page"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endforeach
