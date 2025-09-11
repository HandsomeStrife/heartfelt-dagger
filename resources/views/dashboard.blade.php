<x-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950">
        <!-- Compact Navigation -->
        <x-sub-navigation>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div>
                        <h1 class="font-outfit text-lg font-bold text-white tracking-wide">
                            Welcome, {{ auth()->user()->username }}
                        </h1>
                        <p class="text-slate-400 text-xs">
                            Ready for your next adventure?
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('characters') }}" 
                       class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white text-sm font-medium rounded-md transition-all duration-200">
                        <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Characters
                    </a>
                    <a href="{{ route('campaigns.index') }}" 
                       class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-violet-500 to-purple-500 hover:from-violet-600 hover:to-purple-600 text-white text-sm font-medium rounded-md transition-all duration-200">
                        <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Campaigns
                    </a>
                    <a href="{{ route('rooms.index') }}" 
                       class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white text-sm font-medium rounded-md transition-all duration-200">
                        <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        Rooms
                    </a>
                </div>
            </div>
        </x-sub-navigation>

        <div class="px-4 sm:px-6 lg:px-8 pt-8 pb-12">
            <div class="max-w-5xl mx-auto space-y-6">
                <!-- Development Notice -->
                <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-3">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-amber-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <div>
                            <h3 class="text-amber-400 font-outfit font-medium text-sm">Under Development</h3>
                            <p class="text-amber-300/80 text-xs">All features are currently in active development. Expect changes and improvements!</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Overview -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Recent Characters -->
                    <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-gradient-to-br from-amber-500/20 to-orange-500/20 rounded-lg flex items-center justify-center border border-amber-500/30">
                                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <h2 class="font-outfit text-lg font-bold text-white">Characters</h2>
                            </div>
                        </div>
                        
                        <div class="space-y-3">
                            @forelse($recent_characters as $character)
                                <div class="bg-slate-800/50 rounded-lg p-3 hover:bg-slate-800/70 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="font-medium text-white text-sm">{{ $character->name ?: 'Unnamed Character' }}</h3>
                                            <div class="flex items-center gap-2 text-xs text-slate-400">
                                                @if($character->class)
                                                    <span>{{ ucfirst($character->class) }}</span>
                                                @endif
                                                @if($character->ancestry)
                                                    <span>{{ ucfirst($character->ancestry) }}</span>
                                                @endif
                                                @if($character->level)
                                                    <span>• Level {{ $character->level }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <a href="{{ route('character-builder.edit', $character->character_key) }}" 
                                           class="text-amber-400 hover:text-amber-300 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <svg class="w-8 h-8 text-slate-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <p class="text-slate-400 text-sm">No characters yet</p>
                                    <a href="{{ route('character-builder') }}" class="text-amber-400 hover:text-amber-300 text-xs">Create your first character</a>
                                </div>
                            @endforelse
                        </div>
                        
                        @if($recent_characters->count() >= 3)
                            <div class="mt-4 pt-3 border-t border-slate-700/50">
                                <a href="{{ route('characters') }}" class="block text-center text-amber-400 hover:text-amber-300 text-sm font-medium transition-colors">
                                    See all characters
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Recent Campaigns -->
                    <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-gradient-to-br from-violet-500/20 to-purple-500/20 rounded-lg flex items-center justify-center border border-violet-500/30">
                                    <svg class="w-4 h-4 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h2 class="font-outfit text-lg font-bold text-white">Campaigns</h2>
                            </div>
                        </div>
                        
                        <div class="space-y-3">
                            @forelse($recent_campaigns as $campaign)
                                <div class="bg-slate-800/50 rounded-lg p-3 hover:bg-slate-800/70 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="font-medium text-white text-sm">{{ $campaign->name }}</h3>
                                            <div class="flex items-center gap-2 text-xs text-slate-400">
                                                @if($campaign->creator && $campaign->creator->username === auth()->user()->username)
                                                    <span>Created by you</span>
                                                @else
                                                    <span>Joined campaign</span>
                                                @endif
                                                @if($campaign->member_count ?? false)
                                                    <span>• {{ $campaign->member_count }} members</span>
                                                @endif
                                            </div>
                                        </div>
                                        <a href="{{ route('campaigns.show', $campaign->campaign_code) }}" 
                                           class="text-violet-400 hover:text-violet-300 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <svg class="w-8 h-8 text-slate-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="text-slate-400 text-sm">No campaigns yet</p>
                                    <a href="{{ route('campaigns.create') }}" class="text-violet-400 hover:text-violet-300 text-xs">Create your first campaign</a>
                                </div>
                            @endforelse
                        </div>
                        
                        @if($recent_campaigns->count() >= 3)
                            <div class="mt-4 pt-3 border-t border-slate-700/50">
                                <a href="{{ route('campaigns.index') }}" class="block text-center text-violet-400 hover:text-violet-300 text-sm font-medium transition-colors">
                                    See all campaigns
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Recent Rooms -->
                    <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-gradient-to-br from-emerald-500/20 to-teal-500/20 rounded-lg flex items-center justify-center border border-emerald-500/30">
                                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <h2 class="font-outfit text-lg font-bold text-white">Rooms</h2>
                            </div>
                        </div>
                        
                        <div class="space-y-3">
                            @forelse($recent_rooms as $room)
                                <div class="bg-slate-800/50 rounded-lg p-3 hover:bg-slate-800/70 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="font-medium text-white text-sm">{{ $room->name }}</h3>
                                            <div class="flex items-center gap-2 text-xs text-slate-400">
                                                @if($room->creator && $room->creator->username === auth()->user()->username)
                                                    <span>Created by you</span>
                                                @else
                                                    <span>Joined room</span>
                                                @endif
                                                @if($room->active_participant_count ?? false)
                                                    <span>• {{ $room->active_participant_count }} active</span>
                                                @endif
                                            </div>
                                        </div>
                                        <a href="{{ route('rooms.show', $room->invite_code) }}" 
                                           class="text-emerald-400 hover:text-emerald-300 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <svg class="w-8 h-8 text-slate-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    <p class="text-slate-400 text-sm">No rooms yet</p>
                                    <a href="{{ route('rooms.create') }}" class="text-emerald-400 hover:text-emerald-300 text-xs">Create your first room</a>
                                </div>
                            @endforelse
                        </div>
                        
                        @if($recent_rooms->count() >= 3)
                            <div class="mt-4 pt-3 border-t border-slate-700/50">
                                <a href="{{ route('rooms.index') }}" class="block text-center text-emerald-400 hover:text-emerald-300 text-sm font-medium transition-colors">
                                    See all rooms
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Access Cards -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <!-- Campaign Frames -->
                    <a href="{{ route('campaign-frames.index') }}" class="group relative bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 hover:border-indigo-500/30 rounded-xl p-4 transition-all duration-300">
                        <div class="text-center">
                            <div class="w-8 h-8 bg-gradient-to-br from-indigo-500/20 to-blue-500/20 rounded-lg flex items-center justify-center border border-indigo-500/30 mx-auto mb-2">
                                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <h3 class="font-outfit text-sm font-bold text-white mb-1">Frames</h3>
                            <p class="text-slate-400 text-xs">Campaign templates</p>
                        </div>
                    </a>

                    <!-- Storage Accounts -->
                    <a href="{{ route('storage-accounts') }}" class="group relative bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 hover:border-slate-500/30 rounded-xl p-4 transition-all duration-300">
                        <div class="text-center">
                            <div class="w-8 h-8 bg-gradient-to-br from-slate-500/20 to-gray-500/20 rounded-lg flex items-center justify-center border border-slate-500/30 mx-auto mb-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                                </svg>
                            </div>
                            <h3 class="font-outfit text-sm font-bold text-white mb-1">Storage</h3>
                            <p class="text-slate-400 text-xs">Cloud accounts</p>
                        </div>
                    </a>

                    <!-- Video Library -->
                    <a href="{{ route('video-library') }}" class="group relative bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 hover:border-purple-500/30 rounded-xl p-4 transition-all duration-300">
                        <div class="text-center">
                            <div class="w-8 h-8 bg-gradient-to-br from-purple-500/20 to-indigo-500/20 rounded-lg flex items-center justify-center border border-purple-500/30 mx-auto mb-2">
                                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="font-outfit text-sm font-bold text-white mb-1">Video Library</h3>
                            <p class="text-slate-400 text-xs">Recorded sessions</p>
                        </div>
                    </a>

                    <!-- Range Check Tool -->
                    <a href="{{ route('range-check') }}" class="group relative bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 hover:border-cyan-500/30 rounded-xl p-4 transition-all duration-300">
                        <div class="text-center">
                            <div class="w-8 h-8 bg-gradient-to-br from-cyan-500/20 to-blue-500/20 rounded-lg flex items-center justify-center border border-cyan-500/30 mx-auto mb-2">
                                <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <h3 class="font-outfit text-sm font-bold text-white mb-1">Range Check</h3>
                            <p class="text-slate-400 text-xs">Distance viewer</p>
                        </div>
                    </a>

                    <!-- Actual Plays -->
                    <a href="{{ route('actual-plays') }}" class="group relative bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 hover:border-pink-500/30 rounded-xl p-4 transition-all duration-300">
                        <div class="text-center">
                            <div class="w-8 h-8 bg-gradient-to-br from-pink-500/20 to-rose-500/20 rounded-lg flex items-center justify-center border border-pink-500/30 mx-auto mb-2">
                                <svg class="w-4 h-4 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h8m-9-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="font-outfit text-sm font-bold text-white mb-1">Actual Plays</h3>
                            <p class="text-slate-400 text-xs">Watch streams</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layout>