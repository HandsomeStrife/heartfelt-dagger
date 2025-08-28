@foreach($pages as $page)
    <div class="group bg-white rounded-lg border border-slate-200 hover:border-slate-300 transition-all duration-200 hover:shadow-sm">
        <div class="p-6">
            <div class="flex items-start justify-between">
                <!-- Page Info -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 mb-2">
                        <!-- Page Title -->
                        <h3 class="text-lg font-medium text-slate-900 truncate">
                            {{ $page->title }}
                        </h3>

                        <!-- Breadcrumb for non-root pages -->
                        @if($page->breadcrumbs && count($page->breadcrumbs) > 0)
                            <div class="flex items-center text-sm text-slate-500">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                                </svg>
                                @foreach($page->breadcrumbs as $index => $breadcrumb)
                                    @if($index > 0)
                                        <svg class="w-3 h-3 mx-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    @endif
                                    <span class="truncate max-w-24">{{ $breadcrumb->title }}</span>
                                @endforeach
                            </div>
                        @endif

                        <!-- Access Level Badge -->
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            {{ $page->access_level->value === 'gm_only' ? 'bg-red-100 text-red-800' : 
                               ($page->access_level->value === 'all_players' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ $page->access_level->label() }}
                        </span>

                        <!-- Published Status -->
                        @if(!$page->is_published)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Draft
                            </span>
                        @endif
                    </div>

                    <!-- Categories -->
                    @if(!empty($page->category_tags))
                        <div class="flex flex-wrap gap-1 mb-3">
                            @foreach($page->category_tags as $tag)
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-700">
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    <!-- Content Preview -->
                    @if($page->content)
                        <p class="text-slate-600 text-sm line-clamp-3 mb-3">
                            {{ Str::limit(strip_tags($page->content), 200) }}
                        </p>
                    @endif

                    <!-- Meta Info -->
                    <div class="flex items-center gap-4 text-xs text-slate-500">
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                            </svg>
                            Created {{ \Carbon\Carbon::parse($page->created_at)->diffForHumans() }}
                        </span>
                        
                        @if($page->updated_at !== $page->created_at)
                            <span class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                                </svg>
                                Updated {{ \Carbon\Carbon::parse($page->updated_at)->diffForHumans() }}
                            </span>
                        @endif
                        
                        @if($page->creator)
                            <span class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                </svg>
                                {{ $page->creator->username }}
                            </span>
                        @endif

                        <!-- Children Count -->
                        @if($page->children && $page->children->count() > 0)
                            <span class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                                </svg>
                                {{ $page->children->count() }} sub-page{{ $page->children->count() !== 1 ? 's' : '' }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-2 ml-4 opacity-0 group-hover:opacity-100 transition-opacity">
                    @if($this->canManagePages())
                        <!-- Add Child Page -->
                        <button 
                            wire:click="createPage({{ $page->id }})"
                            class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors"
                            title="Add child page"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </button>

                        <!-- Edit -->
                        <button 
                            @click="$dispatch('slideover-open'); $wire.editPage({{ $page->id }})"
                            class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors"
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
                            class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                            title="Delete page"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    @endif

                    <!-- View/Expand -->
                    <button 
                        class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors"
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
