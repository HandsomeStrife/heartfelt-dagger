@php
    $renderPage = function($page, $depth = 0) use (&$renderPage) {
        $indentClass = $depth > 0 ? 'ml-' . ($depth * 6) : '';
        $borderClass = $depth > 0 ? 'border-l-2 border-slate-200 pl-6' : '';
@endphp
        <div class="group {{ $borderClass }}">
            <div class="bg-white rounded-lg border border-slate-200 hover:border-slate-300 transition-all duration-200 hover:shadow-sm">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <!-- Page Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 mb-2">
                                <!-- Depth Indicator -->
                                @if($depth > 0)
                                    <div class="flex items-center text-slate-400">
                                        @for($i = 1; $i < $depth; $i++)
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                            </svg>
                                        @endfor
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </div>
                                @endif

                                <!-- Page Title -->
                                <h3 class="text-lg font-medium text-slate-900 truncate">
                                    {{ $page->title }}
                                </h3>

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
                            @if(!empty($page->category_tags) && is_array($page->category_tags))
                                <div class="flex flex-wrap gap-1 mb-3">
                                    <span class="text-xs text-slate-600">
                                        {{ implode(' • ', $page->category_tags) }}
                                    </span>
                                </div>
                            @endif

                            <!-- Content Preview -->
                            @if($page->content)
                                <p class="text-slate-600 text-sm line-clamp-2">
                                    {{ Str::limit(strip_tags($page->content), 120) }}
                                </p>
                            @endif

                            <!-- Meta Info -->
                            <div class="flex items-center gap-4 mt-3 text-xs text-slate-500">
                                <span>
                                    Created {{ \Carbon\Carbon::parse($page->created_at)->diffForHumans() }}
                                </span>
                                @if($page->updated_at !== $page->created_at)
                                    <span>
                                        Updated {{ \Carbon\Carbon::parse($page->updated_at)->diffForHumans() }}
                                    </span>
                                @endif
                                @if($page->creator)
                                    <span>
                                        by {{ $page->creator->username }}
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

            <!-- Children -->
            @if($page->children && $page->children->isNotEmpty())
                <div class="mt-3 space-y-3">
                    @foreach($page->children as $child)
                        {!! $renderPage($child, $depth + 1) !!}
                    @endforeach
                </div>
            @endif
        </div>
@php
    };
@endphp

@foreach($pages as $page)
    {!! $renderPage($page) !!}
@endforeach
