{{-- Recording Detail Modal --}}
<div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50">
    <div class="bg-slate-900 border border-slate-700 rounded-xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
        {{-- Modal Header --}}
        <div class="p-6 border-b border-slate-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-outfit font-bold text-white mb-1">
                        {{ $this->selectedRecording->room['name'] ?? 'Unknown Room' }}
                    </h3>
                    <p class="text-slate-300 text-sm">
                        Recording Details
                    </p>
                </div>
                <button wire:click="selectRecording(null)" 
                        class="text-slate-400 hover:text-white transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        {{-- Modal Content --}}
        <div class="overflow-y-auto max-h-[calc(90vh-120px)]">
            <div class="p-6 space-y-6">
                {{-- Video Player Section --}}
                @if(in_array($this->selectedRecording->status, ['ready', 'uploaded']))
                    <div class="aspect-video bg-black rounded-xl overflow-hidden relative">
                        @if($this->selectedRecording->stream_url)
                            <video id="recordingPlayer" 
                                   controls 
                                   class="w-full h-full"
                                   poster="{{ $this->selectedRecording->thumbnail_url }}"
                                   preload="metadata">
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
                                <span class="text-white font-medium">
                                    {{ \Carbon\Carbon::createFromTimestamp($this->selectedRecording->started_at_ms / 1000)->format('M j, Y') }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between p-3 bg-slate-800/50 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas fa-clock text-slate-400 mr-3"></i>
                                    <span class="text-slate-300">Time</span>
                                </div>
                                <span class="text-white font-medium">
                                    {{ \Carbon\Carbon::createFromTimestamp($this->selectedRecording->started_at_ms / 1000)->format('g:i A') }} - 
                                    {{ \Carbon\Carbon::createFromTimestamp($this->selectedRecording->ended_at_ms / 1000)->format('g:i A') }}
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
                                        @if($this->selectedRecording->provider === 'wasabi') bg-orange-500/20
                                        @elseif($this->selectedRecording->provider === 'google_drive') bg-blue-500/20  
                                        @else bg-slate-500/20 @endif">
                                        @if($this->selectedRecording->provider === 'wasabi')
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
                                    {{ ucfirst(str_replace('_', ' ', $this->selectedRecording->provider)) }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between p-3 bg-slate-800/50 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas 
                                        @if(in_array($this->selectedRecording->status, ['ready', 'uploaded'])) fa-check-circle text-emerald-400
                                        @elseif($this->selectedRecording->status === 'processing') fa-spinner fa-spin text-blue-400
                                        @else fa-exclamation-circle text-red-400
                                        @endif mr-3"></i>
                                    <span class="text-slate-300">Status</span>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full border font-medium
                                    @if(in_array($this->selectedRecording->status, ['ready', 'uploaded']))
                                        bg-emerald-500/20 text-emerald-400 border-emerald-500/30
                                    @elseif($this->selectedRecording->status === 'processing')
                                        bg-blue-500/20 text-blue-400 border-blue-500/30
                                    @else
                                        bg-red-500/20 text-red-400 border-red-500/30
                                    @endif">
                                    {{ ucfirst($this->selectedRecording->status) }}
                                </span>
                            </div>

                            @if($this->selectedRecording->format)
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

                            @if($this->selectedRecording->quality)
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

                {{-- Room Information --}}
                @if($this->selectedRecording->room)
                    <div class="border-t border-slate-600 pt-6">
                        <h4 class="text-lg font-medium text-white mb-4">Room Information</h4>
                        
                        <div class="bg-slate-800/30 rounded-lg p-4">
                            <div class="flex items-start space-x-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-violet-500/20 to-purple-500/20 border border-violet-500/30 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-door-open text-violet-400"></i>
                                </div>
                                <div class="flex-1">
                                    <h5 class="text-white font-medium mb-1">
                                        {{ $this->selectedRecording->room['name'] }}
                                    </h5>
                                    @if(isset($this->selectedRecording->room['description']) && $this->selectedRecording->room['description'])
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

        {{-- Modal Footer --}}
        <div class="p-6 border-t border-slate-700">
            <div class="flex items-center justify-between">
                            <div class="text-sm text-slate-400">
                @if($this->selectedRecording->created_at)
                    Recorded {{ \Carbon\Carbon::parse($this->selectedRecording->created_at)->diffForHumans() }}
                @endif
            </div>
                
                @if(in_array($this->selectedRecording->status, ['ready', 'uploaded']))
                    <div class="flex items-center space-x-3">
                        <button wire:click="downloadRecording({{ $this->selectedRecording->id }})" 
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg transition-colors duration-200 font-medium">
                            <i class="fas fa-download mr-2"></i>Download
                        </button>
                        
                        @if($this->selectedRecording->stream_url)
                            <button onclick="document.getElementById('recordingPlayer').requestFullscreen()" 
                                    class="px-4 py-2 bg-slate-600 hover:bg-slate-500 text-white rounded-lg transition-colors duration-200 font-medium">
                                <i class="fas fa-expand mr-2"></i>Fullscreen
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
