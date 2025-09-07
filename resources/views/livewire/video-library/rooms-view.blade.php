{{-- Rooms View for Video Library --}}
<div class="space-y-4">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-medium text-white">
            {{ $groupedRecordings->count() }} {{ Str::plural('Room', $groupedRecordings->count()) }}
            <span class="text-slate-400 text-sm font-normal">
                with recordings
            </span>
        </h3>
        <div class="text-sm text-slate-400">
            Grouped by room
        </div>
    </div>

    <div class="space-y-4">
        @forelse($groupedRecordings as $roomId => $roomData)
            <div class="bg-slate-800/50 border border-slate-600/50 rounded-xl overflow-hidden">
                {{-- Room Header --}}
                <div class="p-6 border-b border-slate-600/50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            {{-- Room Icon --}}
                            <div class="w-12 h-12 bg-gradient-to-br from-violet-500/20 to-purple-500/20 border border-violet-500/30 rounded-lg flex items-center justify-center">
                                <i class="fas fa-door-open text-violet-400 text-lg"></i>
                            </div>

                            {{-- Room Info --}}
                            <div>
                                <h4 class="text-xl font-outfit font-bold text-white mb-1">
                                    {{ $roomData['room']->name }}
                                </h4>
                                @if($roomData['room']->description)
                                    <p class="text-slate-300 text-sm">
                                        {{ $roomData['room']->description }}
                                    </p>
                                @endif
                                <div class="flex items-center space-x-4 mt-2 text-sm text-slate-400">
                                    <div class="flex items-center">
                                        <i class="fas fa-user mr-1"></i>
                                        Created by {{ $roomData['room']->creator->username ?? 'Unknown' }}
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar mr-1"></i>
                                        {{ $roomData['room']->created_at->format('M j, Y') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Room Stats --}}
                        <div class="text-right">
                            <div class="grid grid-cols-2 gap-4 text-center">
                                <div>
                                    <p class="text-2xl font-bold text-white">{{ $roomData['total_count'] }}</p>
                                    <p class="text-slate-400 text-sm">{{ Str::plural('Recording', $roomData['total_count']) }}</p>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-white">{{ $this->formatBytes($roomData['total_size_bytes']) }}</p>
                                    <p class="text-slate-400 text-sm">Total Size</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Recordings List --}}
                <div class="divide-y divide-slate-600/30">
                    @foreach($roomData['recordings']->take(5) as $recording)
                        <div class="p-4 hover:bg-slate-700/30 transition-colors duration-200 cursor-pointer"
                             x-on:click="slideoverOpen = true"
                             wire:click="selectRecording({{ $recording->id }})">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4 flex-1">
                                    {{-- Video Thumbnail --}}
                                    <div class="w-12 h-9 bg-slate-700 rounded-lg flex items-center justify-center relative overflow-hidden">
                                        @if($recording->thumbnail_url)
                                            <img src="{{ $recording->thumbnail_url }}" 
                                                 alt="Recording thumbnail" 
                                                 class="w-full h-full object-cover">
                                        @else
                                            <i class="fas fa-video text-slate-400"></i>
                                        @endif
                                        
                                        {{-- Duration Badge --}}
                                        <div class="absolute bottom-0 right-0 bg-black/80 text-white text-xs px-1">
                                            {{ $this->formatDuration($recording->ended_at_ms - $recording->started_at_ms) }}
                                        </div>
                                    </div>

                                    {{-- Recording Info --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center space-x-3 mb-1">
                                            <div class="flex items-center text-sm text-slate-300">
                                                <i class="fas fa-calendar mr-1"></i>
                                                {{ \Carbon\Carbon::createFromTimestamp($recording->started_at_ms / 1000)->format('M j, Y g:i A') }}
                                            </div>
                                            
                                            <div class="flex items-center text-sm text-slate-300">
                                                <i class="fas fa-user mr-1"></i>
                                                {{ $recording->user->username ?? 'Unknown User' }}
                                            </div>
                                            
                                            <div class="flex items-center text-sm text-slate-300">
                                                <i class="fas fa-hdd mr-1"></i>
                                                {{ $this->formatBytes($recording->size_bytes) }}
                                            </div>

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
                                    </div>
                                </div>

                                {{-- Provider & Actions --}}
                                <div class="flex items-center space-x-3">
                                    {{-- Provider Icon --}}
                                    <div class="w-6 h-6 rounded flex items-center justify-center
                                        @if($recording->provider === 'wasabi') bg-orange-500/20
                                        @elseif($recording->provider === 'google_drive') bg-blue-500/20  
                                        @else bg-slate-500/20 @endif">
                                        @if($recording->provider === 'wasabi')
                                            <i class="fas fa-cloud text-orange-400 text-xs"></i>
                                        @elseif($recording->provider === 'google_drive')
                                            <i class="fab fa-google-drive text-blue-400 text-xs"></i>
                                        @else
                                            <i class="fas fa-server text-slate-400 text-xs"></i>
                                        @endif
                                    </div>

                                    {{-- Action Buttons --}}
                                    @if(in_array($recording->status, ['ready', 'uploaded']))
                                        <div class="flex items-center space-x-1">
                                            <button onclick="event.stopPropagation()" 
                                                    wire:click="playRecording({{ $recording->id }})"
                                                    class="p-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded transition-colors duration-200"
                                                    title="Play Recording">
                                                <i class="fas fa-play text-xs"></i>
                                            </button>
                                            
                                            <button onclick="event.stopPropagation()" 
                                                    wire:click="downloadRecording({{ $recording->id }})"
                                                    class="p-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded transition-colors duration-200"
                                                    title="Download Recording">
                                                <i class="fas fa-download text-xs"></i>
                                            </button>
                                        </div>
                                    @endif

                                    {{-- Expand Icon --}}
                                    <div class="text-slate-400">
                                        <i class="fas fa-chevron-right text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- Show More Recordings --}}
                    @if($roomData['recordings']->count() > 5)
                        <div class="p-4 text-center border-t border-slate-600/30">
                            <button class="text-amber-400 hover:text-amber-300 text-sm font-medium transition-colors duration-200">
                                Show {{ $roomData['recordings']->count() - 5 }} more {{ Str::plural('recording', $roomData['recordings']->count() - 5) }}
                                <i class="fas fa-chevron-down ml-1"></i>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            {{-- Empty State --}}
            <div class="text-center py-12 text-slate-400">
                <i class="fas fa-door-open text-6xl mb-4 opacity-50"></i>
                <h3 class="text-xl font-medium text-white mb-2">No rooms with recordings</h3>
                <p class="mb-4">Start recording in your rooms to see them organized here</p>
                <a href="{{ route('rooms.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-lg hover:from-amber-600 hover:to-orange-600 transition-all duration-200 font-medium">
                    <i class="fas fa-door-open mr-2"></i>Browse Rooms
                </a>
            </div>
        @endforelse
    </div>
</div>
