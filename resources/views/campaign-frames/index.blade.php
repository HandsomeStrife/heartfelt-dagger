<x-layout>
    <div class="min-h-screen">
        <div class="px-4 sm:px-6 lg:px-8 pt-12 pb-16">
            <div class="max-w-6xl mx-auto">
                <!-- Header -->
                <div class="text-center mb-12">
                    <h1 class="font-outfit text-4xl text-white tracking-wide mb-2">
                        Campaign Frames
                    </h1>
                    <p class="text-slate-300 text-lg">
                        Craft and discover inspiring campaign foundations
                    </p>
                </div>

                <!-- Action Bar -->
                <div class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4">
                    <div class="flex space-x-4">
                        <a href="{{ route('campaign-frames.create') }}" class="inline-flex items-center justify-center bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-black font-semibold py-3 px-6 rounded-xl transition-all duration-300 shadow-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Create Frame
                        </a>
                        <a href="{{ route('campaign-frames.browse') }}" class="inline-flex items-center justify-center bg-gradient-to-r from-violet-500 to-purple-500 hover:from-violet-400 hover:to-purple-400 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 shadow-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Browse Public
                        </a>
                    </div>
                </div>

                <!-- My Campaign Frames -->
                @if($user_frames->count() > 0)
                    <div class="mb-12">
                        <h2 class="font-outfit text-2xl text-white mb-6">My Campaign Frames</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($user_frames as $frame)
                                <div class="group relative">
                                    <div class="absolute inset-0 bg-gradient-to-r from-amber-500/20 to-orange-500/20 rounded-2xl blur-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <div class="relative bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 hover:border-amber-500/30 rounded-2xl p-6 transition-all duration-300">
                                        <div class="flex justify-between items-start mb-4">
                                            <div class="flex-1">
                                                <h3 class="font-outfit text-lg font-bold text-white mb-2 line-clamp-1">{{ $frame->name }}</h3>
                                                <p class="text-slate-400 text-sm line-clamp-2 mb-3">{{ $frame->description }}</p>
                                            </div>
                                            @if($frame->is_public)
                                                <div class="ml-2">
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        Public
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-4">
                                                <div class="flex items-center text-slate-400 text-xs">
                                                    <div class="w-2 h-2 rounded-full mr-2 {{ $frame->complexity_rating->value === 1 ? 'bg-green-400' : ($frame->complexity_rating->value === 2 ? 'bg-yellow-400' : ($frame->complexity_rating->value === 3 ? 'bg-orange-400' : 'bg-red-400')) }}"></div>
                                                    {{ $frame->complexity_rating->label() }}
                                                </div>
                                            </div>
                                            <div class="flex space-x-2">
                                                <a href="{{ route('campaign-frames.edit', $frame->id) }}" class="text-amber-400 hover:text-amber-300 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </a>
                                                <a href="{{ route('campaign-frames.show', $frame->id) }}" class="text-violet-400 hover:text-violet-300 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Popular Public Frames -->
                @if($public_frames->count() > 0)
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="font-outfit text-2xl text-white">Popular Public Frames</h2>
                            <a href="{{ route('campaign-frames.browse') }}" class="text-violet-400 hover:text-violet-300 transition-colors font-semibold">
                                View All →
                            </a>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($public_frames->take(6) as $frame)
                                <div class="group relative">
                                    <div class="absolute inset-0 bg-gradient-to-r from-violet-500/20 to-purple-500/20 rounded-2xl blur-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <div class="relative bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 hover:border-violet-500/30 rounded-2xl p-6 transition-all duration-300">
                                        <div class="mb-4">
                                            <h3 class="font-outfit text-lg font-bold text-white mb-2 line-clamp-1">{{ $frame->name }}</h3>
                                            <p class="text-slate-400 text-sm line-clamp-2 mb-3">{{ $frame->description }}</p>
                                            <div class="text-slate-500 text-xs">by {{ $frame->creator?->username ?? 'Unknown' }}</div>
                                        </div>
                                        
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center text-slate-400 text-xs">
                                                <div class="w-2 h-2 rounded-full mr-2 {{ $frame->complexity_rating->value === 1 ? 'bg-green-400' : ($frame->complexity_rating->value === 2 ? 'bg-yellow-400' : ($frame->complexity_rating->value === 3 ? 'bg-orange-400' : 'bg-red-400')) }}"></div>
                                                {{ $frame->complexity_rating->label() }}
                                            </div>
                                            <a href="{{ route('campaign-frames.show', $frame->id) }}" class="text-violet-400 hover:text-violet-300 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Empty State -->
                @if($user_frames->count() === 0 && $public_frames->count() === 0)
                    <div class="text-center">
                        <div class="mb-6">
                            <svg class="w-24 h-24 mx-auto text-slate-400/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h2 class="font-outfit text-2xl text-white mb-4">No Campaign Frames Yet</h2>
                        <p class="text-slate-400 text-lg mb-6">
                            Start by creating your first campaign frame to inspire amazing adventures!
                        </p>
                        <a href="{{ route('campaign-frames.create') }}" class="inline-flex items-center justify-center bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-black font-semibold py-3 px-6 rounded-xl transition-all duration-300 shadow-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Create Your First Frame
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layout>
