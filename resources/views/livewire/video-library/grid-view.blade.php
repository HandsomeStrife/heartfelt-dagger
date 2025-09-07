{{-- Dark Grid View --}}
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

    {{-- Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @foreach($recordings as $recording)
            <div class="bg-slate-800/50 border border-slate-600/50 rounded-xl overflow-hidden hover:border-amber-500/30 transition-all duration-200 cursor-pointer group backdrop-blur-xl"
                 @click="slideoverOpen = true; $wire.selectRecording({{ $recording->id }})">
                
                {{-- Video Thumbnail --}}
                <div class="relative aspect-video bg-slate-700 flex items-center justify-center"
                     x-data="thumbnailComponent({{ $recording->id }}, {{ $recording->thumbnail_url ? 'true' : 'false' }}, {{ in_array($recording->status, ['ready', 'uploaded']) ? 'true' : 'false' }})"
                     x-init="init()">
                    
                    {{-- Loading State --}}
                    <div x-show="generating" class="absolute inset-0 flex items-center justify-center bg-slate-700 z-10">
                        <x-icons.loading class="w-6 h-6 text-amber-400" />
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
                        <x-icons.video class="w-8 h-8 text-slate-400" />
                        @if(in_array($recording->status, ['ready', 'uploaded']))
                            <button @click.stop="generateThumbnail()" 
                                    class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 hover:opacity-100 transition-opacity">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </button>
                        @endif
                    </div>
                    
                    {{-- Overlay --}}
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors duration-200"></div>
                    
                    {{-- Play Button Overlay --}}
                    @if(in_array($recording->status, ['ready', 'uploaded']))
                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                            <div class="w-12 h-12 bg-white/90 backdrop-blur-sm text-slate-900 rounded-full flex items-center justify-center shadow-lg">
                                <x-icons.play class="w-5 h-5 ml-0.5" />
                            </div>
                        </div>
                    @endif
                    
                    {{-- Duration Badge --}}
                    <div class="absolute bottom-2 right-2 bg-black/80 text-white text-xs px-2 py-1 rounded">
                        {{ $this->formatDuration($recording->ended_at_ms - $recording->started_at_ms) }}
                    </div>
                    
                    {{-- Status Badge --}}
                    <div class="absolute top-2 left-2">
                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded backdrop-blur-sm
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

                    {{-- Provider Badge --}}
                    <div class="absolute top-2 right-2">
                        <div class="w-6 h-6 rounded flex items-center justify-center backdrop-blur-sm
                            @if($recording->provider === 'wasabi') bg-orange-500/20 border border-orange-500/30
                            @elseif($recording->provider === 'google_drive') bg-blue-500/20 border border-blue-500/30
                            @else bg-slate-500/20 border border-slate-500/30 @endif">
                            @if($recording->provider === 'wasabi')
                                <svg class="w-3 h-3 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                </svg>
                            @elseif($recording->provider === 'google_drive')
                                <svg class="w-3 h-3 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.01 2C6.5 2 2.02 6.48 2.02 12s4.48 10 9.99 10c5.51 0 10.02-4.48 10.02-10S17.52 2 12.01 2zM4.21 12C4.21 7.58 7.79 4 12.21 4s8 3.58 8 8-3.58 8-8 8-8-3.58-8-8z"/>
                                </svg>
                            @else
                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                                </svg>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Card Content --}}
                <div class="p-3">
                    {{-- Recording Title --}}
                    <h4 class="text-sm font-medium text-white mb-1 truncate group-hover:text-amber-400 transition-colors duration-200">
                        {{ $recording->filename }}
                    </h4>

                    {{-- Room Name --}}
                    <p class="text-xs text-slate-300 mb-2 truncate">
                        {{ $recording->room['name'] ?? 'Unknown Room' }}
                    </p>

                    {{-- Recording Info --}}
                    <div class="space-y-1 text-xs text-slate-400">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center" 
                                 x-data="{ 
                                     startTime: {{ $recording->started_at_ms }},
                                     get localDate() { 
                                         return new Date(this.startTime).toLocaleDateString(); 
                                     }
                                 }">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span x-text="localDate"></span>
                            </div>
                            <div class="flex items-center" 
                                 x-data="{ 
                                     startTime: {{ $recording->started_at_ms }},
                                     get localTime() { 
                                         return new Date(this.startTime).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}); 
                                     }
                                 }">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span x-text="localTime"></span>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <x-icons.users class="w-3 h-3 mr-1" />
                                <span class="truncate">{{ $recording->user['username'] ?? 'Unknown' }}</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                                </svg>
                                {{ $this->formatBytes($recording->size_bytes) }}
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    @if(in_array($recording->status, ['ready', 'uploaded']))
                        <div class="mt-3">
                            <button @click.stop 
                                    wire:click="downloadRecording({{ $recording->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="downloadRecording"
                                    class="w-full px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 text-xs font-medium disabled:opacity-50">
                                <span wire:loading.remove wire:target="downloadRecording">
                                    <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>Download
                                </span>
                                <span wire:loading wire:target="downloadRecording">
                                    <x-icons.loading class="w-3 h-3 mr-1 inline" />Downloading...
                                </span>
                            </button>
                        </div>
                    @elseif($recording->status === 'processing')
                        <div class="mt-3 flex items-center justify-center text-blue-400 text-xs">
                            <x-icons.loading class="w-3 h-3 mr-1" />
                            Processing...
                        </div>
                    @else
                        <div class="mt-3 flex items-center justify-center text-red-400 text-xs">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            Processing Failed
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>