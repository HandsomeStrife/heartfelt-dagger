{{-- Recording Detail Slideover --}}
<x-slideover :show="!!$selectedRecordingId" 
    x-model="slideoverOpen"
    x-modelable="show"
    maxWidth="4xl" :title="$this->selectedRecording->room['name'] ?? 'Loading...'" subtitle="Recording Details"
    onClose="$wire.selectRecording(null)">
    <div class="p-6 space-y-6">
        @if ($this->selectedRecording)
            {{-- Video Player Section --}}
            @if (in_array($this->selectedRecording->status, ['ready', 'uploaded']))
                <div class="aspect-video bg-black rounded-xl overflow-hidden relative">
                    @if ($this->selectedRecording->stream_url)
                        <video id="recordingPlayer" controls class="w-full h-full"
                            poster="{{ $this->selectedRecording->thumbnail_url }}" preload="metadata">
                            <source src="{{ $this->selectedRecording->stream_url }}" type="video/webm">
                            <source src="{{ $this->selectedRecording->stream_url }}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    @else
                        {{-- Placeholder for video not yet available --}}
                        <div class="flex items-center justify-center h-full">
                            <div class="text-center">
                                <i class="fas fa-video text-slate-400 text-6xl mb-4"></i>
                                <h4 class="text-white font-medium mb-2">Video Not Available</h4>
                                <p class="text-slate-400 text-sm">The video file is not ready for streaming yet.</p>
                            </div>
                        </div>
                    @endif

                    {{-- Duration Overlay --}}
                    <div class="absolute bottom-4 right-4 bg-black/80 text-white px-3 py-1 rounded">
                        {{ $this->formatDuration($this->selectedRecording->ended_at_ms - $this->selectedRecording->started_at_ms) }}
                    </div>
                </div>
            @endif


            {{-- Recording Information --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Basic Information --}}
                <div class="space-y-4">
                    <h4 class="text-lg font-medium text-white">Recording Information</h4>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-slate-800/50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-calendar text-slate-400 mr-3"></i>
                                <span class="text-slate-300">Date</span>
                            </div>
                            <span class="text-white font-medium" x-data="{
                                timestamp: {{ $this->selectedRecording->started_at_ms }}
                            }"
                                x-text="new Date(timestamp).toLocaleDateString()">
                            </span>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-slate-800/50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-clock text-slate-400 mr-3"></i>
                                <span class="text-slate-300">Time</span>
                            </div>
                            <span class="text-white font-medium" x-data="{
                                startTime: {{ $this->selectedRecording->started_at_ms }},
                                endTime: {{ $this->selectedRecording->ended_at_ms }}
                            }"
                                x-text="new Date(startTime).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) + ' - ' + new Date(endTime).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})">
                            </span>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-slate-800/50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-stopwatch text-slate-400 mr-3"></i>
                                <span class="text-slate-300">Duration</span>
                            </div>
                            <span class="text-white font-medium">
                                {{ $this->formatDuration($this->selectedRecording->ended_at_ms - $this->selectedRecording->started_at_ms) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-slate-800/50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-hdd text-slate-400 mr-3"></i>
                                <span class="text-slate-300">File Size</span>
                            </div>
                            <span class="text-white font-medium">
                                {{ $this->formatBytes($this->selectedRecording->size_bytes) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-slate-800/50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-user text-slate-400 mr-3"></i>
                                <span class="text-slate-300">Recorded By</span>
                            </div>
                            <span class="text-white font-medium">
                                {{ $this->selectedRecording->user['username'] ?? 'Unknown User' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Technical Information --}}
                <div class="space-y-4">
                    <h4 class="text-lg font-medium text-white">Technical Details</h4>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-slate-800/50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-6 h-6 rounded flex items-center justify-center mr-3
                                            @if ($this->selectedRecording->provider === 'wasabi') bg-orange-500/20
                                            @elseif($this->selectedRecording->provider === 'google_drive') bg-blue-500/20  
                                            @else bg-slate-500/20 @endif"
                                    title="@if ($this->selectedRecording->provider === 'wasabi') Wasabi Cloud Storage@elseif($this->selectedRecording->provider === 'google_drive')Google Drive@else{{ ucfirst(str_replace('_', ' ', $this->selectedRecording->provider)) }} @endif">
                                    @if ($this->selectedRecording->provider === 'wasabi')
                                        <i class="fas fa-cloud text-orange-400 text-sm"></i>
                                    @elseif($this->selectedRecording->provider === 'google_drive')
                                        <i class="fab fa-google-drive text-blue-400 text-sm"></i>
                                    @else
                                        <i class="fas fa-server text-slate-400 text-sm"></i>
                                    @endif
                                </div>
                                <span class="text-slate-300">Storage Provider</span>
                            </div>
                            <span class="text-white font-medium">
                                @if ($this->selectedRecording->provider === 'wasabi')
                                    Wasabi Cloud Storage
                                @elseif($this->selectedRecording->provider === 'google_drive')
                                    Google Drive
                                @else
                                    {{ ucfirst(str_replace('_', ' ', $this->selectedRecording->provider)) }}
                                @endif
                            </span>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-slate-800/50 rounded-lg">
                            <div class="flex items-center">
                                <i
                                    class="fas 
                                            @if (in_array($this->selectedRecording->status, ['ready', 'uploaded'])) fa-check-circle text-emerald-400
                                            @elseif($this->selectedRecording->status === 'processing') fa-spinner fa-spin text-blue-400
                                            @else fa-exclamation-circle text-red-400 @endif mr-3"></i>
                                <span class="text-slate-300">Status</span>
                            </div>
                            <span
                                class="px-2 py-1 text-xs rounded-full border font-medium
                                        @if (in_array($this->selectedRecording->status, ['ready', 'uploaded'])) bg-emerald-500/20 text-emerald-400 border-emerald-500/30
                                        @elseif($this->selectedRecording->status === 'processing')
                                            bg-blue-500/20 text-blue-400 border-blue-500/30
                                        @else
                                            bg-red-500/20 text-red-400 border-red-500/30 @endif">
                                @if ($this->selectedRecording->status === 'uploaded')
                                    Ready to View
                                @else
                                    {{ ucfirst($this->selectedRecording->status) }}
                                @endif
                            </span>
                        </div>

                        @if ($this->selectedRecording->format)
                            <div class="flex items-center justify-between p-3 bg-slate-800/50 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas fa-file-video text-slate-400 mr-3"></i>
                                    <span class="text-slate-300">Format</span>
                                </div>
                                <span class="text-white font-medium">
                                    {{ strtoupper($this->selectedRecording->format) }}
                                </span>
                            </div>
                        @endif

                        @if ($this->selectedRecording->quality)
                            <div class="flex items-center justify-between p-3 bg-slate-800/50 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas fa-eye text-slate-400 mr-3"></i>
                                    <span class="text-slate-300">Quality</span>
                                </div>
                                <span class="text-white font-medium">
                                    {{ $this->selectedRecording->quality }}
                                </span>
                            </div>
                        @endif

                        <div class="flex items-center justify-between p-3 bg-slate-800/50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-database text-slate-400 mr-3"></i>
                                <span class="text-slate-300">Recording ID</span>
                            </div>
                            <span class="text-slate-400 text-sm font-mono">
                                #{{ $this->selectedRecording->id }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="flex items-center justify-center h-full">
                <div class="text-center">
                    <x-icons.loading class="w-10 h-10 text-slate-400 mb-4" />
                    <h4 class="text-white font-medium mb-2">Loading...</h4>
                </div>
            </div>
        @endif

        {{-- Room Information --}}
        @if ($this->selectedRecording && $this->selectedRecording->room)
            <div class="border-t border-slate-600 pt-6">
                <h4 class="text-lg font-medium text-white mb-4">Room Information</h4>

                <div class="bg-slate-800/30 rounded-lg p-4">
                    <div class="flex items-start space-x-4">
                        <div
                            class="w-12 h-12 bg-gradient-to-br from-violet-500/20 to-purple-500/20 border border-violet-500/30 rounded-lg flex items-center justify-center">
                            <i class="fas fa-door-open text-violet-400"></i>
                        </div>
                        <div class="flex-1">
                            <h5 class="text-white font-medium mb-1">
                                {{ $this->selectedRecording->room['name'] }}
                            </h5>
                            @if (isset($this->selectedRecording->room['description']) && $this->selectedRecording->room['description'])
                                <p class="text-slate-300 text-sm mb-3">
                                    {{ $this->selectedRecording->room['description'] }}
                                </p>
                            @endif
                            <div class="flex items-center space-x-4 text-sm text-slate-400">
                                <div class="flex items-center">
                                    <i class="fas fa-user mr-1"></i>
                                    Created by {{ $this->selectedRecording->room['creator']['username'] ?? 'Unknown' }}
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-calendar mr-1"></i>
                                    {{ \Carbon\Carbon::parse($this->selectedRecording->room['created_at'])->format('M j, Y') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    </div>

    <x-slot name="footer">
        @if ($this->selectedRecording)
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-400">
                        @if ($this->selectedRecording->created_at)
                            Recorded {{ \Carbon\Carbon::parse($this->selectedRecording->created_at)->diffForHumans() }}
                        @endif
                    </div>

                    @if (in_array($this->selectedRecording->status, ['ready', 'uploaded']))
                        <div class="flex items-center space-x-3">
                            <button wire:click="downloadRecording({{ $this->selectedRecording->id }})"
                                wire:loading.attr="disabled" wire:target="downloadRecording"
                                class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 disabled:from-slate-600 disabled:to-slate-600 text-white rounded-lg transition-all duration-200 font-medium shadow-lg hover:shadow-emerald-500/25">
                                <i class="fas fa-download mr-2" wire:loading.remove wire:target="downloadRecording"></i>
                                <i class="fas fa-spinner fa-spin mr-2" wire:loading wire:target="downloadRecording"></i>
                                <span wire:loading.remove wire:target="downloadRecording">Download</span>
                                <span wire:loading wire:target="downloadRecording">Preparing...</span>
                            </button>

                            @if ($this->selectedRecording->stream_url)
                                <button onclick="document.getElementById('recordingPlayer').requestFullscreen()"
                                    class="px-4 py-2 bg-slate-600 hover:bg-slate-500 text-white rounded-lg transition-colors duration-200 font-medium">
                                    <i class="fas fa-expand mr-2"></i>Fullscreen
                                </button>
                            @endif

                            <button
                                onclick="navigator.clipboard.writeText('{{ $this->selectedRecording->stream_url ?? 'No stream URL available' }}')"
                                class="px-4 py-2 bg-slate-600 hover:bg-slate-500 text-white rounded-lg transition-colors duration-200 font-medium"
                                title="Copy video URL to clipboard">
                                <i class="fas fa-link mr-2"></i>Copy URL
                            </button>
                        </div>
                    @else
                        <div class="text-sm text-slate-400">
                            @if ($this->selectedRecording->status === 'processing')
                                <i class="fas fa-spinner fa-spin mr-2"></i>Recording is being processed and will be
                                available soon.
                            @elseif($this->selectedRecording->status === 'failed')
                                <i class="fas fa-exclamation-triangle mr-2 text-red-400"></i>Recording failed to process.
                                Please contact support if this persists.
                            @else
                                <i class="fas fa-clock mr-2"></i>Recording is not yet ready for download.
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </x-slot>
</x-slideover>
