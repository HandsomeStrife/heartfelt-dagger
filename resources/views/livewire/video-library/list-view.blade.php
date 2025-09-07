{{-- Dark List View --}}
<div class="space-y-4">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-medium text-white">
            {{ $recordings->count() }} {{ Str::plural('Recording', $recordings->count()) }}
        </h3>
        <div class="text-xs text-slate-400">
            Sorted by most recent
        </div>
    </div>

    {{-- Recordings List --}}
    <div class="space-y-2">
        @foreach($recordings as $recording)
            <div class="bg-slate-800/50 border border-slate-600/50 rounded-lg p-4 hover:border-amber-500/30 transition-all duration-200 cursor-pointer backdrop-blur-xl"
                @click="slideoverOpen = true; $wire.selectRecording({{ $recording->id }})">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3 flex-1">
                        {{-- Video Thumbnail --}}
                        <div class="w-16 h-12 bg-slate-700 rounded-lg flex items-center justify-center relative overflow-hidden flex-shrink-0"
                             x-data="thumbnailComponent({{ $recording->id }}, {{ $recording->thumbnail_url ? 'true' : 'false' }}, {{ in_array($recording->status, ['ready', 'uploaded']) ? 'true' : 'false' }})"
                             x-init="init()">
                            
                            {{-- Loading State --}}
                            <div x-show="generating" class="absolute inset-0 flex items-center justify-center bg-slate-700 z-10">
                                <x-icons.loading class="w-4 h-4 text-amber-400" />
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
                                <x-icons.video class="w-5 h-5 text-slate-400" />
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
                            <div class="absolute bottom-1 right-1 bg-black/80 text-white text-xs px-1.5 py-0.5 rounded">
                                {{ $this->formatDuration($recording->ended_at_ms - $recording->started_at_ms) }}
                            </div>
                        </div>

                        {{-- Recording Info --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-2 mb-2">
                                <h4 class="text-sm font-medium text-white truncate">
                                    {{ $recording->filename }}
                                </h4>
                                
                                {{-- Status Badge --}}
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    @if(in_array($recording->status, ['ready', 'uploaded']))
                                        bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                                    @elseif($recording->status === 'processing')
                                        bg-blue-500/20 text-blue-400 border border-blue-500/30
                                    @else
                                        bg-red-500/20 text-red-400 border border-red-500/30
                                    @endif">
                                    @if(in_array($recording->status, ['ready', 'uploaded']))
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>Ready
                                    @elseif($recording->status === 'processing')
                                        <x-icons.loading class="w-3 h-3 mr-1" />Processing
                                    @else
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>Failed
                                    @endif
                                </span>
                            </div>

                            <div class="flex items-center space-x-4 text-xs text-slate-300">
                                <div class="flex items-center" 
                                     x-data="{ 
                                         startTime: {{ $recording->started_at_ms }},
                                         get localDate() { 
                                             return new Date(this.startTime).toLocaleDateString(); 
                                         },
                                         get localTime() { 
                                             return new Date(this.startTime).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}); 
                                         }
                                     }">
                                    <svg class="w-3 h-3 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span x-text="localDate + ' at ' + localTime"></span>
                                </div>
                                
                                <div class="flex items-center">
                                    <svg class="w-3 h-3 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    {{ $recording->room['name'] ?? 'Unknown Room' }}
                                </div>
                                
                                <div class="flex items-center">
                                    <x-icons.users class="w-3 h-3 mr-1 text-slate-400" />
                                    {{ $recording->user['username'] ?? 'Unknown User' }}
                                </div>
                                
                                <div class="flex items-center">
                                    <svg class="w-3 h-3 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                                    </svg>
                                    {{ $this->formatBytes($recording->size_bytes) }}
                                </div>
                                
                                <div class="flex items-center">
                                    @if($recording->provider === 'wasabi')
                                        <x-icons.brands.wasabi class="w-3 h-3 mr-1" />
                                        <span>Wasabi Cloud</span>
                                    @elseif($recording->provider === 'google_drive')
                                        <x-icons.brands.google-drive class="w-3 h-3 mr-1" />
                                        <span>Google Drive</span>
                                    @else
                                        <x-icons.server class="w-3 h-3 mr-1" />
                                        <span>Local Storage</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center space-x-2 flex-shrink-0">
                        @if(in_array($recording->status, ['ready', 'uploaded']))
                            <button wire:click.stop="downloadRecording({{ $recording->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="downloadRecording"
                                    class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 text-xs font-medium disabled:opacity-50">
                                <span wire:loading.remove wire:target="downloadRecording" class="flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>Download
                                </span>
                                <span wire:loading wire:target="downloadRecording" class="flex items-center">
                                    <x-icons.loading class="w-3 h-3 mr-1" />Downloading...
                                </span>
                            </button>
                        @endif
                        
                        <div class="text-slate-400">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>