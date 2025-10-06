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
                                {{ $room->name }} - Transcripts
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.013 8.013 0 01-2.319-.317l-4.681 1.17a1 1 0 01-1.235-1.235l1.17-4.681A8.013 8.013 0 015 12c0-4.418 3.582-8 8-8s8 3.582 8 8z" />
                                </svg>
                                <span>{{ $transcripts->count() }} messages</span>
                            </div>
                        </div>
                    </div>
                    
                    @if($room->recordings()->ready()->count() > 0)
                        <a href="{{ route('rooms.recordings', $room) }}" 
                           class="inline-flex items-center bg-purple-500 hover:bg-purple-400 text-white font-semibold py-2 px-4 rounded-xl transition-colors text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            View Recordings
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

            <!-- Transcripts Content -->
            <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl overflow-hidden">
                <div class="p-6 border-b border-slate-700/50">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="font-outfit text-xl font-semibold text-white mb-2">Session Transcripts</h2>
                            <p class="text-slate-400 text-sm">Speech-to-text transcription from this room's sessions</p>
                        </div>
                        @if($allTranscripts->count() > 0)
                            <div class="text-sm text-slate-400">
                                @if($searchText || $selectedSpeaker)
                                    Showing {{ $transcripts->count() }} of {{ $allTranscripts->count() }} messages
                                @else
                                    Total: {{ $allTranscripts->count() }} messages
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Search and Filter Controls -->
                    @if($allTranscripts->count() > 0)
                        <form method="GET" class="space-y-4" id="transcript-search-form">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Text Search -->
                                <div class="md:col-span-2">
                                    <label for="search" class="block text-sm font-medium text-slate-300 mb-2">
                                        Search Messages
                                    </label>
                                    <div class="relative">
                                        <input type="text" 
                                               id="search" 
                                               name="search" 
                                               value="{{ $searchText }}"
                                               placeholder="Search transcript content..."
                                               class="w-full bg-slate-800/50 border border-slate-600/50 rounded-xl px-4 py-2 text-white placeholder-slate-400 focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20 transition-colors">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- Speaker Filter -->
                                <div>
                                    <label for="speaker" class="block text-sm font-medium text-slate-300 mb-2">
                                        Filter by Speaker
                                    </label>
                                    <select id="speaker" 
                                            name="speaker" 
                                            class="w-full bg-slate-800/50 border border-slate-600/50 rounded-xl px-4 py-2 text-white focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20 transition-colors">
                                        <option value="">All Speakers</option>
                                        @foreach($speakers as $speaker)
                                            <option value="{{ $speaker }}" {{ $selectedSpeaker === $speaker ? 'selected' : '' }}>
                                                {{ $speaker }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <button type="submit" 
                                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-amber-900 font-semibold rounded-xl transition-all duration-300 text-sm">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    Search
                                </button>
                                
                                @if($searchText || $selectedSpeaker)
                                    <a href="{{ route('rooms.transcripts', $room) }}" 
                                       class="inline-flex items-center px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white font-medium rounded-xl transition-colors text-sm">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Clear Filters
                                    </a>
                                @endif
                            </div>
                        </form>
                    @endif
                </div>

                @if($transcripts && $transcripts->count() > 0)
                    <!-- Chat Log Style Transcript Display -->
                    <div class="max-h-[600px] overflow-y-auto">
                        <div class="p-4 space-y-4">
                            @php
                                $currentDate = null;
                                $lastSpeaker = null;
                            @endphp
                            
                            @foreach($transcripts as $transcript)
                                @php
                                    $transcriptDate = $transcript->getStartedAt()->format('Y-m-d');
                                    $speaker = $transcript->character_name ?: ($transcript->user?->username ?? 'Unknown');
                                    $isNewSpeaker = $speaker !== $lastSpeaker;
                                @endphp
                                
                                <!-- Date separator -->
                                @if($currentDate !== $transcriptDate)
                                    @php $currentDate = $transcriptDate; @endphp
                                    <div class="flex items-center justify-center py-2">
                                        <div class="bg-slate-700/50 px-3 py-1 rounded-full">
                                            <span class="text-slate-300 text-xs font-medium">
                                                {{ $transcript->getStartedAt()->format('F j, Y') }}
                                            </span>
                                        </div>
                                    </div>
                                @endif

                                <!-- Transcript message -->
                                <div class="flex gap-3 {{ $isNewSpeaker ? 'mt-4' : 'mt-1' }} transition-colors duration-300 rounded-lg p-2 -m-2" 
                                     data-transcript-id="{{ $transcript->id }}"
                                     data-timestamp="{{ $transcript->started_at_ms }}">
                                    @if($isNewSpeaker)
                                        @php
                                            $speakerColor = $speakerColors[$speaker] ?? ['primary' => '#6366f1', 'secondary' => '#8b5cf6', 'domains' => []];
                                        @endphp
                                        <!-- Avatar/Initial for new speaker -->
                                        <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center" 
                                             style="background: linear-gradient(135deg, {{ $speakerColor['primary'] }}, {{ $speakerColor['secondary'] }});"
                                             title="@if(!empty($speakerColor['domains'])){{ implode(' + ', $speakerColor['domains']) }}@else{{ $speaker }}@endif">
                                            <span class="text-white text-xs font-bold">
                                                {{ strtoupper(substr($speaker, 0, 1)) }}
                                            </span>
                                        </div>
                                    @else
                                        <!-- Spacer for continued messages -->
                                        <div class="flex-shrink-0 w-8"></div>
                                    @endif
                                    
                                    <div class="flex-1 min-w-0">
                                        @if($isNewSpeaker)
                                            <!-- Speaker name and timestamp -->
                                            <div class="flex items-baseline gap-2 mb-1">
                                                <span class="font-semibold" style="color: {{ $speakerColor['primary'] }};">{{ $speaker }}</span>
                                                @if($transcript->character_class)
                                                    <span class="text-xs text-slate-400 bg-slate-700/50 px-2 py-0.5 rounded" 
                                                          title="@if(!empty($speakerColor['domains']))Domains: {{ implode(' + ', $speakerColor['domains']) }}@endif">
                                                        {{ ucfirst($transcript->character_class) }}
                                                        @if(!empty($speakerColor['domains']))
                                                            <span class="ml-1 opacity-75">({{ implode('+', array_map(fn($d) => substr($d, 0, 3), $speakerColor['domains'])) }})</span>
                                                        @endif
                                                    </span>
                                                @endif
                                                <span class="text-xs text-slate-500">
                                                    {{ $transcript->getFormattedTimestamp() }}
                                                </span>
                                                @if($transcript->confidence && $transcript->confidence < 0.8)
                                                    <span class="text-xs text-amber-400" title="Low confidence transcription">
                                                        <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.728-.833-2.498 0L4.316 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                                        </svg>
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                        
                                        <!-- Message content -->
                                        <div class="text-slate-200 leading-relaxed">
                                            @if($searchText)
                                                {!! preg_replace('/(' . preg_quote($searchText, '/') . ')/i', '<mark class="bg-amber-400/30 text-amber-200 px-1 rounded">$1</mark>', e($transcript->text)) !!}
                                            @else
                                                {{ $transcript->text }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                @php $lastSpeaker = $speaker; @endphp
                            @endforeach
                        </div>
                    </div>

                    <!-- Timeline Bar -->
                    @if($allTranscripts->count() > 0 && !empty($timelineData) && $allTranscripts->first() && $allTranscripts->last())
                        <div class="p-4 border-t border-slate-700/50 bg-slate-800/30">
                            <div class="mb-3">
                                <div class="flex items-center justify-between text-sm mb-2">
                                    <span class="text-slate-300 font-medium">Activity Timeline</span>
                                    <span class="text-slate-400" id="timeline-timestamp">
                                        {{ $allTranscripts->first()->getStartedAt()->format('H:i:s') }} - {{ $allTranscripts->last()->getEndedAt()->format('H:i:s') }}
                                    </span>
                                </div>
                                
                                <!-- Timeline Bar -->
                                <div class="relative bg-slate-700/50 rounded-lg h-8 overflow-hidden cursor-pointer" id="timeline-bar">
                                    @foreach($timelineData as $segment)
                                        @php
                                            $intensity = $segment['count'] > 0 ? min(1, $segment['count'] / 5) : 0;
                                            $opacity = $intensity > 0 ? max(0.2, $intensity) : 0.05;
                                            
                                            // Determine segment color based on speakers
                                            $segmentColor = ['from' => '#f59e0b', 'to' => '#fbbf24']; // Default amber
                                            if (!empty($segment['speakers'])) {
                                                $speakerNames = array_keys($segment['speakers']);
                                                if (count($speakerNames) === 1) {
                                                    // Single speaker - use their domain colors
                                                    $speakerColor = $segment['speakers'][$speakerNames[0]];
                                                    $segmentColor = ['from' => $speakerColor['primary'], 'to' => $speakerColor['secondary']];
                                                } elseif (count($speakerNames) > 1) {
                                                    // Multiple speakers - create a mixed gradient
                                                    $colors = array_values(array_map(fn($s) => $s['primary'], $segment['speakers']));
                                                    $segmentColor = ['from' => $colors[0], 'to' => $colors[count($colors) - 1]];
                                                }
                                            }
                                            
                                            $tooltipText = $segment['timestamp'] . ' - ' . $segment['count'] . ' messages';
                                            if (!empty($segment['speakers'])) {
                                                $tooltipText .= ' (' . implode(', ', array_keys($segment['speakers'])) . ')';
                                            }
                                        @endphp
                                        <div class="absolute top-0 h-full transition-all duration-200 hover:scale-110"
                                             style="left: {{ $segment['segment'] }}%; width: 1%; opacity: {{ $opacity }}; background: linear-gradient(to top, {{ $segmentColor['from'] }}, {{ $segmentColor['to'] }});"
                                             data-segment="{{ $segment['segment'] }}"
                                             data-timestamp="{{ $segment['timestamp'] }}"
                                             data-count="{{ $segment['count'] }}"
                                             data-start-ms="{{ $segment['start_ms'] }}"
                                             data-speakers="{{ json_encode(array_keys($segment['speakers'])) }}"
                                             title="{{ $tooltipText }}">
                                        </div>
                                    @endforeach
                                    
                                    <!-- Timeline cursor -->
                                    <div id="timeline-cursor" 
                                         class="absolute top-0 w-0.5 h-full bg-white shadow-lg transition-all duration-200 opacity-0">
                                    </div>
                                </div>
                                
                                <!-- Timeline labels -->
                                <div class="flex justify-between text-xs text-slate-500 mt-1">
                                    <span>{{ $allTranscripts->first()->getStartedAt()->format('H:i') }}</span>
                                    <span>{{ $allTranscripts->last()->getEndedAt()->format('H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Transcript Controls -->
                    <div class="p-4 border-t border-slate-700/50 bg-slate-800/30">
                        <div class="flex items-center justify-between text-sm">
                            <div class="text-slate-400">
                                @if($allTranscripts->count() > 0 && $allTranscripts->first() && $allTranscripts->last())
                                    Session from {{ $allTranscripts->first()->getStartedAt()->format('M j, Y H:i') }} 
                                    to {{ $allTranscripts->last()->getEndedAt()->format('H:i') }}
                                @endif
                            </div>
                            <div class="flex items-center gap-4">
                                <button onclick="scrollToTop()" 
                                        class="text-slate-400 hover:text-slate-300 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                                    </svg>
                                    Top
                                </button>
                                <button onclick="scrollToBottom()" 
                                        class="text-slate-400 hover:text-slate-300 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                                    </svg>
                                    Bottom
                                </button>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.013 8.013 0 01-2.319-.317l-4.681 1.17a1 1 0 01-1.235-1.235l1.17-4.681A8.013 8.013 0 015 12c0-4.418 3.582-8 8-8s8 3.582 8 8z" />
                            </svg>
                        </div>
                        <h3 class="font-outfit text-lg font-semibold text-white mb-2">No Transcripts Available</h3>
                        <p class="text-slate-400 text-sm">
                            This room doesn't have any speech-to-text transcriptions yet.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Enhanced Transcript Controls Script -->
    <script>
        // Global variables
        let transcriptContainer;
        let timelineBar;
        let timelineCursor;
        let allTranscripts = @json($allTranscripts->values());
        let timelineData = @json($timelineData);

        function scrollToTop() {
            if (transcriptContainer) {
                transcriptContainer.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }

        function scrollToBottom() {
            if (transcriptContainer) {
                transcriptContainer.scrollTo({ top: transcriptContainer.scrollHeight, behavior: 'smooth' });
            }
        }

        function scrollToTimestamp(targetMs) {
            if (!transcriptContainer || !allTranscripts.length) return;

            // Find the transcript closest to the target timestamp
            let closestTranscript = null;
            let closestDiff = Infinity;

            allTranscripts.forEach((transcript, index) => {
                const diff = Math.abs(transcript.started_at_ms - targetMs);
                if (diff < closestDiff) {
                    closestDiff = diff;
                    closestTranscript = { transcript, index };
                }
            });

            if (closestTranscript) {
                // Find the corresponding DOM element
                const transcriptElements = transcriptContainer.querySelectorAll('[data-transcript-id]');
                const targetElement = Array.from(transcriptElements).find(el => 
                    el.dataset.transcriptId == closestTranscript.transcript.id
                );

                if (targetElement) {
                    // Scroll to the element
                    const containerRect = transcriptContainer.getBoundingClientRect();
                    const elementRect = targetElement.getBoundingClientRect();
                    const scrollTop = transcriptContainer.scrollTop + elementRect.top - containerRect.top - 50;
                    
                    transcriptContainer.scrollTo({ top: scrollTop, behavior: 'smooth' });
                    
                    // Highlight the element briefly
                    targetElement.classList.add('bg-amber-500/20', 'border-amber-400/50');
                    setTimeout(() => {
                        targetElement.classList.remove('bg-amber-500/20', 'border-amber-400/50');
                    }, 2000);
                }
            }
        }

        function updateTimelineCursor(percentage) {
            if (timelineCursor) {
                timelineCursor.style.left = percentage + '%';
                timelineCursor.style.opacity = '1';
            }
        }

        function hideTimelineCursor() {
            if (timelineCursor) {
                timelineCursor.style.opacity = '0';
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            transcriptContainer = document.querySelector('.max-h-\\[600px\\]');
            timelineBar = document.getElementById('timeline-bar');
            timelineCursor = document.getElementById('timeline-cursor');

            // Auto-scroll to bottom on page load (only if no search filters)
            const hasFilters = {{ ($searchText || $selectedSpeaker) ? 'true' : 'false' }};
            if (transcriptContainer && !hasFilters) {
                transcriptContainer.scrollTop = transcriptContainer.scrollHeight;
            }

            // Timeline interactions
            if (timelineBar && timelineData.length > 0) {
                // Click to jump to time
                timelineBar.addEventListener('click', function(e) {
                    const rect = timelineBar.getBoundingClientRect();
                    const percentage = ((e.clientX - rect.left) / rect.width) * 100;
                    const segmentIndex = Math.floor(percentage);
                    
                    if (timelineData[segmentIndex]) {
                        const targetMs = timelineData[segmentIndex].start_ms;
                        scrollToTimestamp(targetMs);
                        updateTimelineCursor(percentage);
                    }
                });

                // Hover effects
                timelineBar.addEventListener('mousemove', function(e) {
                    const rect = timelineBar.getBoundingClientRect();
                    const percentage = ((e.clientX - rect.left) / rect.width) * 100;
                    const segmentIndex = Math.floor(percentage);
                    
                    if (timelineData[segmentIndex]) {
                        updateTimelineCursor(percentage);
                        
                        // Update timestamp display with speaker info
                        const timestampDisplay = document.getElementById('timeline-timestamp');
                        if (timestampDisplay) {
                            const segment = timelineData[segmentIndex];
                            let displayText = `${segment.timestamp} - ${segment.count} messages`;
                            
                            // Add speaker names if available
                            if (segment.speakers && Object.keys(segment.speakers).length > 0) {
                                const speakerNames = Object.keys(segment.speakers);
                                displayText += ` (${speakerNames.join(', ')})`;
                            }
                            
                            timestampDisplay.textContent = displayText;
                        }
                    }
                });

                timelineBar.addEventListener('mouseleave', function() {
                    hideTimelineCursor();
                    
                    // Reset timestamp display
                    const timestampDisplay = document.getElementById('timeline-timestamp');
                    if (timestampDisplay && allTranscripts.length > 0) {
                        const startTime = new Date(allTranscripts[0].started_at_ms).toLocaleTimeString('en-US', {hour12: false});
                        const endTime = new Date(allTranscripts[allTranscripts.length - 1].ended_at_ms).toLocaleTimeString('en-US', {hour12: false});
                        timestampDisplay.textContent = `${startTime} - ${endTime}`;
                    }
                });
            }

            // Real-time search (debounced)
            const searchInput = document.getElementById('search');
            const speakerSelect = document.getElementById('speaker');
            let searchTimeout;

            function performSearch() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    document.getElementById('transcript-search-form').submit();
                }, 500);
            }

            if (searchInput) {
                searchInput.addEventListener('input', performSearch);
            }

            if (speakerSelect) {
                speakerSelect.addEventListener('change', function() {
                    document.getElementById('transcript-search-form').submit();
                });
            }

            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Ctrl/Cmd + F to focus search
                if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                    e.preventDefault();
                    if (searchInput) {
                        searchInput.focus();
                        searchInput.select();
                    }
                }
                
                // Escape to clear search
                if (e.key === 'Escape' && (searchInput === document.activeElement)) {
                    searchInput.value = '';
                    performSearch();
                }
            });
        });
    </script>
</x-layout>
