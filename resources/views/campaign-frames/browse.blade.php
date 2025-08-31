<x-layout>
    <div class="min-h-screen">
        <div class="px-4 sm:px-6 lg:px-8 pt-12 pb-16">
            <div class="max-w-6xl mx-auto">
                <!-- Header -->
                <div class="text-center mb-12">
                    <h1 class="font-outfit text-4xl text-white tracking-wide mb-2">
                        Browse Public Campaign Frames
                    </h1>
                    <p class="text-slate-300 text-lg">
                        Discover inspiring foundations created by the community
                    </p>
                </div>

                <!-- Navigation -->
                <div class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4">
                    <div>
                        <a href="{{ route('campaign-frames.index') }}" class="inline-flex items-center text-slate-400 hover:text-white transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back to My Frames
                        </a>
                    </div>
                    
                    <!-- Search -->
                    <div class="relative">
                        <form method="GET" action="{{ route('campaign-frames.browse') }}" class="flex items-center space-x-2">
                            <div class="relative">
                                <input 
                                    type="text" 
                                    name="search" 
                                    value="{{ $search ?? '' }}" 
                                    placeholder="Search frames..."
                                    class="w-full sm:w-64 bg-slate-800/50 border border-slate-700/50 rounded-xl px-4 py-2 pl-10 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500/50"
                                >
                                <svg class="absolute left-3 top-2.5 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <button type="submit" class="bg-violet-600 hover:bg-violet-700 text-white px-4 py-2 rounded-xl transition-colors">
                                Search
                            </button>
                            @if($search)
                                <a href="{{ route('campaign-frames.browse') }}" class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-xl transition-colors">
                                    Clear
                                </a>
                            @endif
                        </form>
                    </div>
                </div>

                <!-- Results -->
                @if($frames->count() > 0)
                    <div class="mb-6">
                        <p class="text-slate-400">
                            @if($search)
                                Found {{ $frames->count() }} frame{{ $frames->count() !== 1 ? 's' : '' }} for "{{ $search }}"
                            @else
                                Showing {{ $frames->count() }} public frame{{ $frames->count() !== 1 ? 's' : '' }}
                            @endif
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($frames as $frame)
                            <div class="group relative">
                                <div class="absolute inset-0 bg-gradient-to-r from-violet-500/20 to-purple-500/20 rounded-2xl blur-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 hover:border-violet-500/30 rounded-2xl p-6 transition-all duration-300 h-full flex flex-col">
                                    <div class="flex-1">
                                        <div class="flex justify-between items-start mb-4">
                                            <div class="flex-1">
                                                <h3 class="font-outfit text-lg font-bold text-white mb-2 line-clamp-2">{{ $frame->name }}</h3>
                                                <p class="text-slate-400 text-sm line-clamp-3 mb-3">{{ $frame->description }}</p>
                                            </div>
                                        </div>
                                        
                                        <!-- Creator and Metadata -->
                                        <div class="text-slate-500 text-xs mb-4 space-y-1">
                                            <div>by {{ $frame->creator?->username ?? 'Unknown' }}</div>
                                            <div class="flex items-center">
                                                <div class="w-2 h-2 rounded-full mr-2 {{ $frame->complexity_rating->value === 1 ? 'bg-green-400' : ($frame->complexity_rating->value === 2 ? 'bg-yellow-400' : ($frame->complexity_rating->value === 3 ? 'bg-orange-400' : 'bg-red-400')) }}"></div>
                                                {{ $frame->complexity_rating->label() }}
                                            </div>
                                        </div>

                                        <!-- Tags -->
                                        @if(!empty($frame->tone_and_themes))
                                            <div class="flex flex-wrap gap-1 mb-4">
                                                @foreach(array_slice($frame->tone_and_themes, 0, 3) as $theme)
                                                    @if(!empty($theme))
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-violet-500/20 text-violet-300 border border-violet-500/30">
                                                            {{ $theme }}
                                                        </span>
                                                    @endif
                                                @endforeach
                                                @if(count($frame->tone_and_themes) > 3)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-slate-500/20 text-slate-400 border border-slate-500/30">
                                                        +{{ count($frame->tone_and_themes) - 3 }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Actions -->
                                    <div class="flex items-center justify-between pt-4 border-t border-slate-700/50">
                                        <div class="flex items-center space-x-2 text-xs text-slate-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <span>View Details</span>
                                        </div>
                                        <a href="{{ route('campaign-frames.show', $frame->id) }}" class="inline-flex items-center justify-center bg-gradient-to-r from-violet-500 to-purple-500 hover:from-violet-400 hover:to-purple-400 text-white text-sm font-semibold py-2 px-4 rounded-xl transition-all duration-300">
                                            Explore
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="text-center">
                        <div class="mb-6">
                            <svg class="w-24 h-24 mx-auto text-slate-400/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <h2 class="font-outfit text-2xl text-white mb-4">
                            @if($search)
                                No Results Found
                            @else
                                No Public Frames Yet
                            @endif
                        </h2>
                        <p class="text-slate-400 text-lg mb-6">
                            @if($search)
                                Try adjusting your search terms or browse all available frames.
                            @else
                                Be the first to create and share an inspiring campaign frame with the community!
                            @endif
                        </p>
                        
                        <div class="flex justify-center space-x-4">
                            @if($search)
                                <a href="{{ route('campaign-frames.browse') }}" class="inline-flex items-center justify-center bg-gradient-to-r from-violet-500 to-purple-500 hover:from-violet-400 hover:to-purple-400 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 shadow-lg">
                                    Browse All
                                </a>
                            @endif
                            <a href="{{ route('campaign-frames.create') }}" class="inline-flex items-center justify-center bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-black font-semibold py-3 px-6 rounded-xl transition-all duration-300 shadow-lg">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Create Frame
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layout>
