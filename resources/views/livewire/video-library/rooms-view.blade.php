{{-- Dark Rooms View --}}
<div class="space-y-4">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-medium text-white">
            {{ $groupedRecordings->count() }} {{ Str::plural('Room', $groupedRecordings->count()) }}
            <span class="text-slate-400 text-xs font-normal">
                with recordings
            </span>
        </h3>
        <div class="text-xs text-slate-400">
            Grouped by room
        </div>
    </div>

    {{-- Rooms List --}}
    <div class="space-y-4">
        @forelse($groupedRecordings as $roomId => $roomData)
            <div class="bg-slate-800/50 border border-slate-600/50 rounded-xl overflow-hidden backdrop-blur-xl">
                {{-- Room Header --}}
                <div class="p-4 border-b border-slate-600/30">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            {{-- Room Icon --}}
                            <div class="w-10 h-10 bg-gradient-to-br from-violet-500/20 to-purple-500/20 border border-violet-500/30 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>

                            {{-- Room Info --}}
                            <div>
                                <h4 class="text-lg font-outfit font-bold text-white mb-0.5">
                                    {{ $roomData['room']['name'] ?? 'Unknown Room' }}
                                </h4>
                                @if(isset($roomData['room']['description']) && $roomData['room']['description'])
                                    <p class="text-slate-300 text-xs mb-1">
                                        {{ $roomData['room']['description'] }}
                                    </p>
                                @endif
                                <div class="flex items-center space-x-3 text-xs text-slate-400">
                                    <div class="flex items-center">
                                        <x-icons.users class="w-3 h-3 mr-1" />
                                        Created by {{ $roomData['room']['creator']['username'] ?? 'Unknown' }}
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {{ isset($roomData['room']['created_at']) ? \Carbon\Carbon::parse($roomData['room']['created_at'])->format('M j, Y') : 'Unknown' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Room Stats --}}
                        <div class="text-right">
                            <div class="grid grid-cols-2 gap-4 text-center">
                                <div>
                                    <p class="text-lg font-bold text-white">{{ $roomData['total_count'] }}</p>
                                    <p class="text-slate-400 text-xs">{{ Str::plural('Recording', $roomData['total_count']) }}</p>
                                </div>
                                <div>
                                    <p class="text-lg font-bold text-white">{{ $this->formatBytes($roomData['total_size_bytes']) }}</p>
                                    <p class="text-slate-400 text-xs">Total Size</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Recordings List --}}
                <div class="divide-y divide-slate-600/30">
                    @foreach($roomData['recordings']->take(5) as $recording)
                        <div class="p-3 hover:bg-slate-700/30 transition-colors duration-200 cursor-pointer"
                             @click="slideoverOpen = true; $wire.selectRecording({{ $recording->id }})">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3 flex-1">
                                    {{-- Video Thumbnail --}}
                                    <div class="w-12 h-9 bg-slate-700 rounded-lg flex items-center justify-center relative overflow-hidden flex-shrink-0"
                                         x-data="thumbnailComponent({{ $recording->id }}, {{ $recording->thumbnail_url ? 'true' : 'false' }}, {{ in_array($recording->status, ['ready', 'uploaded']) ? 'true' : 'false' }})"
                                         x-init="init()">
                                        
                                        {{-- Loading State --}}
                                        <div x-show="generating" class="absolute inset-0 flex items-center justify-center bg-slate-700 z-10">
                                            <x-icons.loading class="w-3 h-3 text-amber-400" />
                                        </div>
                                        
                                        {{-- Thumbnail Image --}}
                                        @if($recording->thumbnail_url)
                                            <img src="{{ $recording->thumbnail_url }}" 
                                                 alt="Recording thumbnail" 
                                                 class="w-full h-full object-cover"
                                                 x-show="!generating && hasThumbnail">
                                        @endif
                                        
                                        {{-- Default Video Icon --}}
                                        <div x-show="!generating && !hasThumbnail" class="flex flex-col items-center">
                                            <x-icons.video class="w-4 h-4 text-slate-400" />
                                            @if(in_array($recording->status, ['ready', 'uploaded']))
                                                <button @click.stop="generateThumbnail()" 
                                                        class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 hover:opacity-100 transition-opacity">
                                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                        
                                        {{-- Duration Badge --}}
                                        <div class="absolute bottom-0 right-0 bg-black/80 text-white text-xs px-1 rounded-tl">
                                            {{ $this->formatDuration($recording->ended_at_ms - $recording->started_at_ms) }}
                                        </div>
                                    </div>

                                    {{-- Recording Info --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center space-x-2 mb-1">
                                            <h5 class="text-sm font-medium text-white truncate">
                                                {{ $recording->filename }}
                                            </h5>
                                            
                                            {{-- Status Badge --}}
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium
                                                @if(in_array($recording->status, ['ready', 'uploaded']))
                                                    bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                                                @elseif($recording->status === 'processing')
                                                    bg-blue-500/20 text-blue-400 border border-blue-500/30
                                                @else
                                                    bg-red-500/20 text-red-400 border border-red-500/30
                                                @endif">
                                                @if(in_array($recording->status, ['ready', 'uploaded']))
                                                    <svg class="w-2 h-2 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>Ready
                                                @elseif($recording->status === 'processing')
                                                    <x-icons.loading class="w-2 h-2 mr-1" />Processing
                                                @else
                                                    <svg class="w-2 h-2 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                    </svg>Failed
                                                @endif
                                            </span>
                                        </div>
                                        
                                        <div class="flex items-center space-x-3 text-xs text-slate-400">
                                            <div class="flex items-center" 
                                                 x-data="{ 
                                                     startTime: {{ $recording->started_at_ms }},
                                                     get localDateTime() { 
                                                         return new Date(this.startTime).toLocaleDateString() + ' at ' + new Date(this.startTime).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}); 
                                                     }
                                                 }">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <span x-text="localDateTime"></span>
                                            </div>
                                            
                                            <div class="flex items-center">
                                                <x-icons.users class="w-3 h-3 mr-1" />
                                                {{ $recording->user['username'] ?? 'Unknown User' }}
                                            </div>
                                            
                                            <div class="flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                                                </svg>
                                                {{ $this->formatBytes($recording->size_bytes) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Provider & Actions --}}
                                <div class="flex items-center space-x-2 flex-shrink-0">
                                    {{-- Provider Icon --}}
                                    <div class="flex items-center text-xs text-slate-400">
                                        @if($recording->provider === 'wasabi')
                                            <svg class="w-3 h-3 text-orange-400 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                            </svg>
                                            <span>Wasabi</span>
                                        @elseif($recording->provider === 'google_drive')
                                            <svg class="w-3 h-3 text-blue-400 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12.01 2C6.5 2 2.02 6.48 2.02 12s4.48 10 9.99 10c5.51 0 10.02-4.48 10.02-10S17.52 2 12.01 2zM4.21 12C4.21 7.58 7.79 4 12.21 4s8 3.58 8 8-3.58 8-8 8-8-3.58-8-8z"/>
                                            </svg>
                                            <span>Google Drive</span>
                                        @else
                                            <svg class="w-3 h-3 text-slate-400 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                                            </svg>
                                            <span>{{ ucfirst($recording->provider) }}</span>
                                        @endif
                                    </div>

                                    {{-- Action Buttons --}}
                                    @if(in_array($recording->status, ['ready', 'uploaded']))
                                        <button @click.stop 
                                                wire:click="downloadRecording({{ $recording->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="downloadRecording"
                                                class="p-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 disabled:opacity-50"
                                                title="Download Recording">
                                            <span wire:loading.remove wire:target="downloadRecording">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </span>
                                            <span wire:loading wire:target="downloadRecording">
                                                <x-icons.loading class="w-3 h-3" />
                                            </span>
                                        </button>
                                    @endif

                                    {{-- Expand Icon --}}
                                    <div class="text-slate-400">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- Show More Recordings --}}
                    @if($roomData['recordings']->count() > 5)
                        <div class="p-3 text-center border-t border-slate-600/30 bg-slate-700/20">
                            <button class="text-amber-400 hover:text-amber-300 text-xs font-medium transition-colors duration-200">
                                Show {{ $roomData['recordings']->count() - 5 }} more {{ Str::plural('recording', $roomData['recordings']->count() - 5) }}
                                <svg class="w-3 h-3 ml-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            {{-- Empty State --}}
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-slate-800/50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-white mb-2">No rooms with recordings</h3>
                <p class="text-slate-400 mb-4 text-sm">Start recording in your rooms to see them organized here</p>
                <a href="{{ route('rooms.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-lg hover:from-amber-600 hover:to-orange-600 transition-all duration-200 font-medium text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>Browse Rooms
                </a>
            </div>
        @endforelse
    </div>
</div>