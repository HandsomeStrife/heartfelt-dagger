<x-layout>
    <div class="min-h-screen">
        <!-- Header Section with Breadcrumb -->
        <div class="mb-4">
            <x-sub-navigation>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        @if($campaign)
                            <a href="{{ route('campaigns.show', $campaign->campaign_code) }}" class="text-slate-400 hover:text-slate-300 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </a>
                        @endif
                        <div class="flex items-center gap-4">
                            <h1 class="font-outfit text-lg font-semibold text-white">
                                {{ $room->name }} - Recordings
                            </h1>
                            @if($room->status->value === 'archived')
                                <span class="px-2 py-1 bg-slate-500/20 text-slate-400 rounded-lg text-xs font-medium">
                                    Archived
                                </span>
                            @endif
                        </div>
                        
                        <!-- Room Info in Banner -->
                        <div class="hidden md:flex items-center gap-6 text-sm text-slate-400">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span>{{ $room->creator?->username ?? 'Unknown' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>{{ \Carbon\Carbon::parse($room->created_at)->diffForHumans() }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                <span>{{ $recordings->count() }} recordings</span>
                            </div>
                        </div>
                    </div>
                    
                    @if($room->transcripts()->count() > 0)
                        <a href="{{ route('rooms.transcripts', $room) }}" 
                           class="inline-flex items-center bg-blue-500 hover:bg-blue-400 text-white font-semibold py-2 px-4 rounded-xl transition-colors text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.013 8.013 0 01-2.319-.317l-4.681 1.17a1 1 0 01-1.235-1.235l1.17-4.681A8.013 8.013 0 015 12c0-4.418 3.582-8 8-8s8 3.582 8 8z" />
                            </svg>
                            View Transcripts
                        </a>
                    @endif
                </div>
            </x-sub-navigation>
        </div>

        <div class="container mx-auto px-3 sm:px-6 pb-8">
            <!-- Room Description -->
            @if($room->description)
                <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 mb-6">
                    <p class="text-slate-300 text-base">{{ $room->description }}</p>
                </div>
            @endif

            <!-- Recordings Content -->
            <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="font-outfit text-xl font-semibold text-white mb-2">Session Recordings</h2>
                        <p class="text-slate-400 text-sm">Video recordings from this room's sessions</p>
                    </div>
                </div>

                @if($recordings && $recordings->count() > 0)
                    <div class="space-y-4">
                        @foreach($recordings as $recording)
                            <div class="bg-slate-800/50 border border-slate-600/50 rounded-xl p-6 hover:bg-slate-800/70 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-3">
                                            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="font-outfit font-semibold text-white text-lg">{{ $recording->filename }}</h3>
                                                <p class="text-slate-400 text-sm">
                                                    Recorded by {{ $recording->user?->username ?? 'Unknown' }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                            <div>
                                                <span class="text-slate-500 text-xs uppercase tracking-wide">Duration</span>
                                                <p class="text-white font-medium">{{ $recording->getFormattedDuration() }}</p>
                                            </div>
                                            <div>
                                                <span class="text-slate-500 text-xs uppercase tracking-wide">Size</span>
                                                <p class="text-white font-medium">{{ $recording->getFormattedSize() }}</p>
                                            </div>
                                            <div>
                                                <span class="text-slate-500 text-xs uppercase tracking-wide">Started</span>
                                                <p class="text-white font-medium">{{ $recording->getStartedAt()->format('M j, Y H:i') }}</p>
                                            </div>
                                            <div>
                                                <span class="text-slate-500 text-xs uppercase tracking-wide">Status</span>
                                                <span class="inline-flex items-center px-2 py-1 bg-emerald-500/20 text-emerald-400 rounded text-xs font-medium">
                                                    Ready
                                                </span>
                                            </div>
                                        </div>

                                        @if($recording->thumbnail_url)
                                            <div class="mb-4">
                                                <img src="{{ $recording->thumbnail_url }}" 
                                                     alt="Recording thumbnail" 
                                                     class="w-full max-w-sm h-auto rounded-lg border border-slate-600/50">
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex flex-col gap-2 ml-4">
                                        @if($recording->stream_url)
                                            <a href="{{ $recording->stream_url }}" 
                                               target="_blank"
                                               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-500 to-indigo-500 hover:from-purple-400 hover:to-indigo-400 text-white font-semibold rounded-xl transition-all duration-300 text-sm">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M16 14h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Watch
                                            </a>
                                        @endif
                                        
                                        <a href="{{ route('api.rooms.recordings.download', [$room, $recording]) }}" 
                                           class="inline-flex items-center px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white font-medium rounded-xl transition-colors text-sm">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="font-outfit text-lg font-semibold text-white mb-2">No Recordings Available</h3>
                        <p class="text-slate-400 text-sm">
                            This room doesn't have any recorded sessions yet.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layout>
