<div class="h-full flex flex-col">
    <!-- Search Bar -->
    <div class="p-4 border-b border-slate-700/50">
        <livewire:reference-search-new :is-sidebar="true" />
    </div>

    <!-- Page Title (when content is shown) -->
    @if($content_html)
    <div class="px-4 pt-4 pb-2 border-b border-slate-700/50">
        <button wire:click="$set('content_html', null)" 
                class="inline-flex items-center text-sm text-slate-400 hover:text-white transition-colors mb-2">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Navigation
        </button>
        <h2 class="font-outfit text-lg font-bold text-white">{{ $page_title }}</h2>
    </div>
    @else
    <div class="px-4 pt-4 pb-2 border-b border-slate-700/50">
        <h2 class="font-outfit text-lg font-bold text-white">Reference</h2>
        <p class="text-xs text-slate-400 mt-1">Browse all reference pages</p>
    </div>
    @endif

    <!-- Content Area (scrollable) -->
    <div class="flex-1 overflow-y-auto">
        @if($content_html)
            <!-- Show Page Content -->
            <div class="prose prose-invert prose-sm max-w-none p-4">
                {!! $content_html !!}
            </div>
        @else
            <!-- Show Navigation Menu -->
            <div class="p-4 space-y-6">
                @foreach($this->pages as $section => $sectionPages)
                    <div>
                        <h4 class="text-xs uppercase tracking-wider text-slate-400 mb-2 px-2">{{ $section }}</h4>
                        <div class="space-y-1">
                            @foreach($sectionPages as $pageKey => $title)
                                <button 
                                    wire:click="loadPage('{{ $pageKey }}')"
                                    class="w-full cursor-pointer text-left p-2 rounded text-sm transition-colors {{ $current_page === $pageKey ? 'bg-amber-500/20 text-amber-400' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }} {{ str_contains($pageKey, '-abilities') ? 'pl-4' : '' }}">
                                    {{ $title }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
