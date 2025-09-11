<div x-data="{ activeTab: 'players', dropdownOpen: false }" class="h-full flex flex-col">
    <!-- Header -->
    <div class="p-4 border-b border-slate-700/50">
        <h2 class="font-outfit text-xl text-white mb-2">GM Dashboard</h2>
        <p class="text-slate-300 text-sm">{{ $campaign?->name ?? 'Campaign Room' }}</p>
    </div>

    <!-- Dropdown Navigation -->
    <div class="p-3 border-b border-slate-700/50">
        <div class="relative">
            <button @click="dropdownOpen = !dropdownOpen" 
                    class="w-full flex items-center justify-between px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-sm font-medium rounded-lg transition-colors">
                <span x-text="activeTab === 'players' ? 'Players' : 
                            activeTab === 'pages' ? 'Pages' : 
                            activeTab === 'handouts' ? 'Handouts' : 
                            activeTab === 'notes' ? 'Notes' : 
                            activeTab === 'gamestate' ? 'Game State' : 'Select Tab'"></span>
                <svg class="w-4 h-4 transition-transform" :class="dropdownOpen ? 'rotate-180' : ''" 
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            
            <div x-show="dropdownOpen" 
                 x-cloak
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 @click.away="dropdownOpen = false"
                 class="absolute z-10 mt-1 w-full bg-slate-800 border border-slate-600 rounded-lg shadow-lg">
                <button @click="activeTab = 'players'; dropdownOpen = false" 
                        :class="activeTab === 'players' ? 'bg-amber-500/20 text-amber-400' : 'text-slate-300 hover:bg-slate-700'"
                        class="w-full text-left px-4 py-2 text-sm transition-colors flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                    </svg>
                    Players
                </button>
                
                @if($campaign && $campaign_pages->count() > 0)
                <button @click="activeTab = 'pages'; dropdownOpen = false" 
                        :class="activeTab === 'pages' ? 'bg-amber-500/20 text-amber-400' : 'text-slate-300 hover:bg-slate-700'"
                        class="w-full text-left px-4 py-2 text-sm transition-colors flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Pages
                </button>
                @endif
                
                @if($campaign && $campaign_handouts->count() > 0)
                <button @click="activeTab = 'handouts'; dropdownOpen = false" 
                        :class="activeTab === 'handouts' ? 'bg-amber-500/20 text-amber-400' : 'text-slate-300 hover:bg-slate-700'"
                        class="w-full text-left px-4 py-2 text-sm transition-colors flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Handouts
                </button>
                @endif
                
                <button @click="activeTab = 'gamestate'; dropdownOpen = false" 
                        :class="activeTab === 'gamestate' ? 'bg-amber-500/20 text-amber-400' : 'text-slate-300 hover:bg-slate-700'"
                        class="w-full text-left px-4 py-2 text-sm transition-colors flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Game State
                </button>
                
                <button @click="activeTab = 'notes'; dropdownOpen = false" 
                        :class="activeTab === 'notes' ? 'bg-amber-500/20 text-amber-400' : 'text-slate-300 hover:bg-slate-700'"
                        class="w-full text-left px-4 py-2 text-sm transition-colors flex items-center rounded-b-lg">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Notes
                </button>
            </div>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="flex-1 overflow-y-auto">
        <!-- Players Tab -->
        <div x-show="activeTab === 'players'" x-cloak class="p-4 space-y-4">
            <h3 class="font-outfit text-lg text-white mb-3">Player Characters</h3>
            
            @forelse($participants as $participant)
                @if($participant->user_id !== $room->creator_id)
                    <x-room-sidebar.player-summary :participant="$participant" />
                @endif
            @empty
                <div class="text-center py-8">
                    <svg class="w-12 h-12 mx-auto text-slate-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.196-2.121M9 20H4v-2a3 3 0 015.196-2.121m0 0a5.002 5.002 0 019.608 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <p class="text-slate-400 text-sm">No players have joined yet</p>
                </div>
            @endforelse
        </div>

        <!-- Campaign Pages Tab -->
        @if($campaign && $campaign_pages->count() > 0)
        <div x-show="activeTab === 'pages'" x-cloak class="p-4 space-y-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-outfit text-lg text-white">Campaign Pages</h3>
                <a href="{{ route('campaigns.pages', $campaign) }}" 
                   target="_blank"
                   class="text-amber-400 hover:text-amber-300 text-sm flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    Manage
                </a>
            </div>
            
            @foreach($campaign_pages as $page)
                <div class="bg-slate-800/50 rounded-lg p-3 hover:bg-slate-800/70 transition-colors cursor-pointer"
                     onclick="window.open('/campaigns/{{ $campaign->campaign_code }}/pages/{{ $page->id }}', '_blank')">
                    <h4 class="text-white font-medium text-sm mb-1">{{ $page->title }}</h4>
                    @if($page->category_tags && count($page->category_tags) > 0)
                        <div class="flex flex-wrap gap-1 mb-2">
                            @foreach($page->category_tags as $tag)
                                <span class="px-2 py-1 bg-amber-500/20 text-amber-300 text-xs rounded">{{ $tag }}</span>
                            @endforeach
                        </div>
                    @endif
                    <p class="text-slate-400 text-xs">
                        {{ $page->access_level->value }} • 
                        Updated {{ \Carbon\Carbon::parse($page->updated_at)->diffForHumans() }}
                    </p>
                </div>
            @endforeach
        </div>
        @endif

        <!-- Handouts Tab -->
        @if($campaign && $campaign_handouts->count() > 0)
        <div x-show="activeTab === 'handouts'" x-cloak class="p-4 space-y-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-outfit text-lg text-white">Campaign Handouts</h3>
                <a href="{{ route('campaigns.handouts', $campaign) }}" 
                   target="_blank"
                   class="text-amber-400 hover:text-amber-300 text-sm flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    Manage
                </a>
            </div>
            
            @foreach($campaign_handouts as $handout)
                <div class="bg-slate-800/50 rounded-lg p-3 hover:bg-slate-800/70 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-2 mb-1">
                                <h4 class="text-white font-medium text-sm truncate">{{ $handout->title }}</h4>
                                <span class="px-2 py-1 bg-blue-500/20 text-blue-300 text-xs rounded">
                                    {{ strtoupper($handout->file_type->value) }}
                                </span>
                            </div>
                            @if($handout->description)
                                <p class="text-slate-400 text-xs line-clamp-1">{{ $handout->description }}</p>
                            @endif
                            <p class="text-slate-500 text-xs mt-1">
                                {{ $handout->formatted_file_size }} • 
                                {{ \Carbon\Carbon::parse($handout->created_at)->diffForHumans() }}
                            </p>
                        </div>
                        
                        <div class="flex items-center space-x-1 ml-2">
                            @if($handout->isPreviewable())
                                <button onclick="showHandoutPreview({{ $handout->id }})"
                                        class="p-1 text-slate-400 hover:text-blue-400 transition-colors"
                                        title="Preview">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            @endif
                            
                            <button onclick="window.open('{{ $handout->file_url }}', '_blank')"
                                    class="p-1 text-slate-400 hover:text-green-400 transition-colors"
                                    title="Download">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @endif

        <!-- Game State Tab -->
        <div x-show="activeTab === 'gamestate'" x-cloak class="p-4 space-y-6">
            <h3 class="font-outfit text-lg text-white mb-4">Game State Management</h3>
            
            <!-- Fear Tracker -->
            <div class="bg-slate-800/50 rounded-lg p-4">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <h4 class="text-white font-medium">Fear Level</h4>
                    </div>
                </div>
                
                <div class="flex items-center justify-between p-3 bg-slate-700/50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="text-red-300 font-medium text-sm">Fear</div>
                        <div class="text-white font-bold text-lg" data-fear-display="level">{{ $current_fear_level ?? 0 }}</div>
                    </div>
                    
                    <div class="flex items-center space-x-1">
                        <button wire:click="decreaseFear" 
                                class="px-2 py-1 bg-red-600/20 hover:bg-red-600/30 border border-red-500/50 text-red-300 rounded transition-colors text-xs">
                            -1
                        </button>
                        
                        <button wire:click="increaseFear"
                                class="px-2 py-1 bg-green-600/20 hover:bg-green-600/30 border border-green-500/50 text-green-300 rounded transition-colors text-xs">
                            +1
                        </button>
                        
                        <div class="flex items-center space-x-1 ml-2">
                            <input type="number" 
                                   wire:model.live="fear_level_input" 
                                   min="0" 
                                   max="255"
                                   class="w-12 bg-slate-600/50 border border-slate-500/50 rounded px-2 py-1 text-white text-center text-xs focus:outline-none focus:ring-1 focus:ring-red-500/50"
                                   placeholder="0">
                            <button wire:click="setFearLevel" 
                                    class="px-2 py-1 bg-amber-600/20 hover:bg-amber-600/30 border border-amber-500/50 text-amber-300 rounded transition-colors text-xs">
                                Set
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Countdown Trackers -->
            <div class="bg-slate-800/50 rounded-lg p-4">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h4 class="text-white font-medium">Countdown Trackers</h4>
                    </div>
                    <button wire:click="$set('show_add_countdown', true)" 
                            class="px-3 py-2 bg-blue-600/20 hover:bg-blue-600/30 border border-blue-500/50 text-blue-300 rounded-lg transition-colors text-sm flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add
                    </button>
                </div>

                <!-- Add Countdown Form -->
                @if($show_add_countdown ?? false)
                <div class="mb-4 p-3 bg-slate-700/50 rounded-lg border border-slate-600/50">
                    <div class="space-y-3">
                        <input type="text" 
                               wire:model.live="new_countdown_name" 
                               placeholder="Timer name..."
                               class="w-full bg-slate-600/50 border border-slate-500/50 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50">
                        
                        <div class="flex items-center space-x-2">
                            <input type="number" 
                                   wire:model.live="new_countdown_value" 
                                   min="0"
                                   placeholder="Value"
                                   class="flex-1 bg-slate-600/50 border border-slate-500/50 rounded-lg px-3 py-2 text-white text-sm text-center focus:outline-none focus:ring-2 focus:ring-blue-500/50">
                            
                            <button wire:click="createCountdownTracker" 
                                    class="px-3 py-2 bg-green-600/20 hover:bg-green-600/30 border border-green-500/50 text-green-300 rounded-lg transition-colors text-sm">
                                Create
                            </button>
                            
                            <button wire:click="$set('show_add_countdown', false)" 
                                    class="px-3 py-2 bg-slate-600/20 hover:bg-slate-600/30 border border-slate-500/50 text-slate-300 rounded-lg transition-colors text-sm">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Existing Countdown Trackers -->
                <div class="space-y-2">
                    @if(empty($countdown_trackers ?? []))
                        <div class="text-center py-4 text-slate-400 text-sm">
                            No countdown trackers active
                        </div>
                    @else
                        @foreach($countdown_trackers ?? [] as $trackerId => $tracker)
                            <div class="flex items-center justify-between p-3 bg-slate-700/50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="text-blue-300 font-medium text-sm">{{ $tracker['name'] }}</div>
                                    <div class="text-white font-bold text-lg">{{ $tracker['value'] }}</div>
                                </div>
                                
                                <div class="flex items-center space-x-1">
                                    <button wire:click="decreaseCountdown('{{ $trackerId }}')" 
                                            class="px-2 py-1 bg-red-600/20 hover:bg-red-600/30 border border-red-500/50 text-red-300 rounded transition-colors text-xs">
                                        -1
                                    </button>
                                    
                                    <button wire:click="increaseCountdown('{{ $trackerId }}')" 
                                            class="px-2 py-1 bg-green-600/20 hover:bg-green-600/30 border border-green-500/50 text-green-300 rounded transition-colors text-xs">
                                        +1
                                    </button>
                                    
                                    <button wire:click="deleteCountdownTracker('{{ $trackerId }}')" 
                                            onclick="return confirm('Delete this countdown tracker?')"
                                            class="px-2 py-1 bg-slate-600/20 hover:bg-slate-600/30 border border-slate-500/50 text-slate-300 rounded transition-colors text-xs">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- Quick Reference -->
            <div class="bg-slate-800/50 rounded-lg p-4">
                <x-reference.sidebar-quick-ref 
                    title="GM Quick Reference"
                    :items="[
                        ['title' => 'Core GM Mechanics', 'url' => route('reference.page', 'core-gm-mechanics')],
                        ['title' => 'Adversaries', 'url' => route('reference.page', 'adversaries')],
                        ['title' => 'GM Guidance', 'url' => route('reference.page', 'gm-guidance')],
                        ['title' => 'Action Roll Results', 'url' => route('reference.page', 'making-moves-and-taking-action')],
                        ['title' => 'Conditions', 'url' => route('reference.page', 'conditions')],
                        ['title' => 'Combat Rules', 'url' => route('reference.page', 'combat')],
                    ]" />
            </div>
        </div>

        <!-- Notes Tab -->
        <div x-show="activeTab === 'notes'" x-cloak class="p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-outfit text-lg text-white">Session Notes</h3>
                <button wire:click="saveSessionNotes" 
                        x-data="{ saving: false }"
                        @click="saving = true"
                        @notes-saved.window="saving = false; setTimeout(() => { $el.classList.add('text-green-400'); setTimeout(() => $el.classList.remove('text-green-400'), 2000) }, 100)"
                        class="text-amber-400 hover:text-amber-300 text-sm flex items-center transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    <span x-show="!saving">Save</span>
                    <span x-show="saving">Saving...</span>
                </button>
            </div>
            
            <textarea wire:model.live.debounce.500ms="session_notes"
                      placeholder="Add your session notes here..."
                      class="w-full h-64 bg-slate-800/50 border border-slate-600/50 rounded-lg p-3 text-white placeholder-slate-400 resize-none focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50"></textarea>
            
            <div class="mt-3 text-xs text-slate-500">
                Notes are automatically saved to the database and synced across your devices
            </div>
        </div>
    </div>
</div>
