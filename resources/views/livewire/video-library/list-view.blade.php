{{-- List View for Video Library --}}
<div class="space-y-3">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium text-white">
            {{ $recordings->count() }} {{ Str::plural('Recording', $recordings->count()) }}
        </h3>
        <div class="text-sm text-slate-400">
            Sorted by most recent
        </div>
    </div>

    @foreach($recordings as $recording)
        <div class="bg-slate-800/50 border border-slate-600/50 rounded-lg p-4 hover:border-amber-500/30 transition-all duration-200 cursor-pointer"
             wire:click="selectRecording({{ $recording->id }})">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4 flex-1">
                    {{-- Video Thumbnail/Icon --}}
                    <div class="w-16 h-12 bg-slate-700 rounded-lg flex items-center justify-center relative overflow-hidden">
                        @if($recording->thumbnail_url)
                            <img src="{{ $recording->thumbnail_url }}" 
                                 alt="Recording thumbnail" 
                                 class="w-full h-full object-cover">
                        @else
                            <i class="fas fa-video text-slate-400 text-lg"></i>
                        @endif
                        
                        {{-- Duration Badge --}}
                        <div class="absolute bottom-1 right-1 bg-black/80 text-white text-xs px-1 rounded">
                            {{ $this->formatDuration($recording->ended_at_ms - $recording->started_at_ms) }}
                        </div>
                    </div>

                    {{-- Recording Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center mb-1">
                            <h4 class="font-medium text-white truncate mr-2">
                                {{ $recording->room['name'] ?? 'Unknown Room' }}
                            </h4>
                            
                            {{-- Status Badge --}}
                            <span class="px-2 py-1 text-xs rounded-full border
                                @if(in_array($recording->status, ['ready', 'uploaded']))
                                    bg-emerald-500/20 text-emerald-400 border-emerald-500/30
                                @elseif($recording->status === 'processing')
                                    bg-blue-500/20 text-blue-400 border-blue-500/30
                                @else
                                    bg-red-500/20 text-red-400 border-red-500/30
                                @endif">
                                <i class="fas 
                                    @if(in_array($recording->status, ['ready', 'uploaded'])) fa-check-circle
                                    @elseif($recording->status === 'processing') fa-spinner fa-spin
                                    @else fa-exclamation-circle
                                    @endif mr-1"></i>
                                {{ ucfirst($recording->status) }}
                            </span>
                        </div>

                        <div class="flex items-center text-sm text-slate-300 space-x-4">
                            <div class="flex items-center">
                                <i class="fas fa-calendar mr-1"></i>
                                {{ \Carbon\Carbon::createFromTimestamp($recording->started_at_ms / 1000)->format('M j, Y g:i A') }}
                            </div>
                            
                            <div class="flex items-center">
                                <i class="fas fa-user mr-1"></i>
                                {{ $recording->user['username'] ?? 'Unknown User' }}
                            </div>
                            
                            <div class="flex items-center">
                                <i class="fas fa-hdd mr-1"></i>
                                {{ $this->formatBytes($recording->size_bytes) }}
                            </div>
                        </div>

                        @if($recording->room && isset($recording->room['description']) && $recording->room['description'])
                            <p class="text-sm text-slate-400 mt-1 truncate">
                                {{ $recording->room['description'] }}
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Provider & Actions --}}
                <div class="flex items-center space-x-3">
                    {{-- Provider Icon --}}
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center
                            @if($recording->provider === 'wasabi') bg-orange-500/20
                            @elseif($recording->provider === 'google_drive') bg-blue-500/20  
                            @else bg-slate-500/20 @endif">
                            @if($recording->provider === 'wasabi')
                                <i class="fas fa-cloud text-orange-400"></i>
                            @elseif($recording->provider === 'google_drive')
                                <i class="fab fa-google-drive text-blue-400"></i>
                            @else
                                <i class="fas fa-server text-slate-400"></i>
                            @endif
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    @if(in_array($recording->status, ['ready', 'uploaded']))
                        <div class="flex items-center space-x-2">
                            <button onclick="event.stopPropagation()" 
                                    wire:click="playRecording({{ $recording->id }})"
                                    class="p-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg transition-colors duration-200"
                                    title="Play Recording">
                                <i class="fas fa-play text-sm"></i>
                            </button>
                            
                            <button onclick="event.stopPropagation()" 
                                    wire:click="downloadRecording({{ $recording->id }})"
                                    class="p-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg transition-colors duration-200"
                                    title="Download Recording">
                                <i class="fas fa-download text-sm"></i>
                            </button>
                        </div>
                    @elseif($recording->status === 'processing')
                        <div class="flex items-center text-blue-400">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            <span class="text-sm">Processing...</span>
                        </div>
                    @else
                        <div class="flex items-center text-red-400">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <span class="text-sm">Failed</span>
                        </div>
                    @endif

                    {{-- Expand Icon --}}
                    <div class="text-slate-400">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
