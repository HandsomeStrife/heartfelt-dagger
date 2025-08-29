<x-layouts.app>
    <div class="max-w-4xl mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-outfit font-bold text-white">{{ $page->title }}</h1>
                <p class="text-slate-300 mt-1">
                    <a href="{{ route('campaigns.pages', ['campaign' => $campaign->campaign_code]) }}" class="text-amber-400 hover:text-amber-300">
                        {{ $campaign->name }}
                    </a>
                    <span class="mx-2">•</span>
                    Campaign Page
                </p>
            </div>
            
            <div class="flex items-center gap-3">
                <!-- Access Level Badge -->
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    {{ $page->access_level->value === 'gm_only' ? 'bg-red-500/20 text-red-300 border border-red-500/30' : 
                       ($page->access_level->value === 'all_players' ? 'bg-blue-500/20 text-blue-300 border border-blue-500/30' : 'bg-yellow-500/20 text-yellow-300 border border-yellow-500/30') }}">
                    {{ $page->access_level->label() }}
                </span>
                
                @if(!$page->is_published)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-slate-700 text-slate-300 border border-slate-600">
                        Draft
                    </span>
                @endif
            </div>
        </div>

        <!-- Categories -->
        @if(!empty($page->category_tags))
            <div class="flex flex-wrap gap-2 mb-6">
                @foreach($page->category_tags as $tag)
                    <span class="inline-flex items-center px-3 py-1 rounded-md text-sm font-medium bg-slate-800 text-slate-300 border border-slate-600">
                        {{ $tag }}
                    </span>
                @endforeach
            </div>
        @endif

        <!-- Content -->
        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl">
            <div class="p-8">
                @if($page->content)
                    <div class="prose prose-invert max-w-none tiptap">
                        {!! $page->content !!}
                    </div>
                @else
                    <p class="text-slate-400 italic">This page has no content yet.</p>
                @endif
            </div>
        </div>

        <!-- Meta Information -->
        <div class="mt-6 flex items-center justify-between text-sm text-slate-500">
            <div class="flex items-center gap-4">
                <span>Created {{ \Carbon\Carbon::parse($page->created_at)->diffForHumans() }}</span>
                @if($page->updated_at !== $page->created_at)
                    <span>Updated {{ \Carbon\Carbon::parse($page->updated_at)->diffForHumans() }}</span>
                @endif
                @if($page->creator)
                    <span>by {{ $page->creator->username }}</span>
                @endif
            </div>
            
            <a href="{{ route('pages', ['campaign' => $campaign->campaign_code]) }}" 
               class="text-amber-400 hover:text-amber-300 font-medium">
                ← Back to Pages
            </a>
        </div>
    </div>
</x-layout.default>
