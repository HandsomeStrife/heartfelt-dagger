{{-- Grid View for Video Library --}}
<div class="space-y-4">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-medium text-white">
            {{ $recordings->count() }} {{ Str::plural('Recording', $recordings->count()) }}
        </h3>
        <div class="text-sm text-slate-400">
            Sorted by most recent
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @foreach($recordings as $recording)
            <div class="bg-slate-800/50 border border-slate-600/50 rounded-xl overflow-hidden hover:border-amber-500/30 transition-all duration-200 cursor-pointer group"
                 wire:click="selectRecording({{ $recording->id }})">
                
                {{-- Video Thumbnail --}}
                <div class="relative aspect-video bg-slate-700 flex items-center justify-center">
                    @if($recording->thumbnail_url)
                        <img src="{{ $recording->thumbnail_url }}" 
                             alt="Recording thumbnail" 
                             class="w-full h-full object-cover">
                    @else
                        <i class="fas fa-video text-slate-400 text-4xl"></i>
                    @endif
                    
                    {{-- Overlay --}}
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors duration-200"></div>
                    
                    {{-- Play Button Overlay --}}
                    @if(in_array($recording->status, ['ready', 'uploaded']))
                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                            <button onclick="event.stopPropagation()" 
                                    wire:click="playRecording({{ $recording->id }})"
                                    class="w-12 h-12 bg-emerald-500 hover:bg-emerald-400 text-white rounded-full flex items-center justify-center transition-colors duration-200">
                                <i class="fas fa-play ml-1"></i>
                            </button>
                        </div>
                    @endif
                    
                    {{-- Duration Badge --}}
                    <div class="absolute bottom-2 right-2 bg-black/80 text-white text-xs px-2 py-1 rounded">
                        {{ $this->formatDuration($recording->ended_at_ms - $recording->started_at_ms) }}
                    </div>
                    
                    {{-- Status Badge --}}
                    <div class="absolute top-2 left-2">
                        <span class="px-2 py-1 text-xs rounded-full border
                            @if(in_array($recording->status, ['ready', 'uploaded']))
                                bg-emerald-500/20 text-emerald-400 border-emerald-500/30 backdrop-blur-sm
                            @elseif($recording->status === 'processing')
                                bg-blue-500/20 text-blue-400 border-blue-500/30 backdrop-blur-sm
                            @else
                                bg-red-500/20 text-red-400 border-red-500/30 backdrop-blur-sm
                            @endif">
                            <i class="fas 
                                @if(in_array($recording->status, ['ready', 'uploaded'])) fa-check-circle
                                @elseif($recording->status === 'processing') fa-spinner fa-spin
                                @else fa-exclamation-circle
                                @endif mr-1"></i>
                            {{ ucfirst($recording->status) }}
                        </span>
                    </div>

                    {{-- Provider Badge --}}
                    <div class="absolute top-2 right-2">
                        <div class="w-6 h-6 rounded-md flex items-center justify-center backdrop-blur-sm
                            @if($recording->provider === 'wasabi') bg-orange-500/20 border border-orange-500/30
                            @elseif($recording->provider === 'google_drive') bg-blue-500/20 border border-blue-500/30
                            @else bg-slate-500/20 border border-slate-500/30 @endif">
                            @if($recording->provider === 'wasabi')
                                <i class="fas fa-cloud text-orange-400 text-xs"></i>
                            @elseif($recording->provider === 'google_drive')
                                <i class="fab fa-google-drive text-blue-400 text-xs"></i>
                            @else
                                <i class="fas fa-server text-slate-400 text-xs"></i>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Card Content --}}
                <div class="p-4">
                    {{-- Room Name --}}
                    <h4 class="font-medium text-white mb-2 truncate group-hover:text-amber-400 transition-colors duration-200">
                        {{ $recording->room->name ?? 'Unknown Room' }}
                    </h4>

                    {{-- Recording Info --}}
                    <div class="space-y-2 text-sm text-slate-300">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-calendar mr-1 text-slate-400"></i>
                                {{ \Carbon\Carbon::createFromTimestamp($recording->started_at_ms / 1000)->format('M j, Y') }}
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-clock mr-1 text-slate-400"></i>
                                {{ \Carbon\Carbon::createFromTimestamp($recording->started_at_ms / 1000)->format('g:i A') }}
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-user mr-1 text-slate-400"></i>
                                {{ $recording->user->username ?? 'Unknown' }}
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-hdd mr-1 text-slate-400"></i>
                                {{ $this->formatBytes($recording->size_bytes) }}
                            </div>
                        </div>
                    </div>

                    {{-- Room Description --}}
                    @if($recording->room && $recording->room->description)
                        <p class="text-xs text-slate-400 mt-2 line-clamp-2">
                            {{ $recording->room->description }}
                        </p>
                    @endif

                    {{-- Action Buttons --}}
                    @if(in_array($recording->status, ['ready', 'uploaded']))
                        <div class="flex items-center space-x-2 mt-4">
                            <button onclick="event.stopPropagation()" 
                                    wire:click="playRecording({{ $recording->id }})"
                                    class="flex-1 px-3 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg transition-colors duration-200 text-sm font-medium">
                                <i class="fas fa-play mr-2"></i>Play
                            </button>
                            
                            <button onclick="event.stopPropagation()" 
                                    wire:click="downloadRecording({{ $recording->id }})"
                                    class="px-3 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg transition-colors duration-200 text-sm"
                                    title="Download">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    @elseif($recording->status === 'processing')
                        <div class="mt-4 flex items-center justify-center text-blue-400 text-sm">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Processing...
                        </div>
                    @else
                        <div class="mt-4 flex items-center justify-center text-red-400 text-sm">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Processing Failed
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
