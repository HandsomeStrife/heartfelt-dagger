<div x-data="{ activeTab: 'players' }" class="h-full flex flex-col">
    <!-- Header -->
    <div class="p-4 border-b border-slate-700/50">
        <h2 class="font-outfit text-xl text-white mb-2">GM Dashboard</h2>
        <p class="text-slate-300 text-sm">{{ $campaign?->name ?? 'Campaign Room' }}</p>
    </div>

    <!-- Tab Navigation -->
    <div class="flex border-b border-slate-700/50">
        <button @click="activeTab = 'players'" 
                :class="activeTab === 'players' ? 'bg-slate-800 text-white border-amber-500' : 'text-slate-400 hover:text-slate-300'"
                class="flex-1 px-4 py-3 text-sm font-medium border-b-2 border-transparent transition-colors">
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
            </svg>
            Players
        </button>
        
        @if($campaign && $campaign_pages->count() > 0)
        <button @click="activeTab = 'pages'" 
                :class="activeTab === 'pages' ? 'bg-slate-800 text-white border-amber-500' : 'text-slate-400 hover:text-slate-300'"
                class="flex-1 px-4 py-3 text-sm font-medium border-b-2 border-transparent transition-colors">
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Pages
        </button>
        @endif
        
        <button @click="activeTab = 'notes'" 
                :class="activeTab === 'notes' ? 'bg-slate-800 text-white border-amber-500' : 'text-slate-400 hover:text-slate-300'"
                class="flex-1 px-4 py-3 text-sm font-medium border-b-2 border-transparent transition-colors">
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Notes
        </button>
    </div>

    <!-- Tab Content -->
    <div class="flex-1 overflow-y-auto">
        <!-- Players Tab -->
        <div x-show="activeTab === 'players'" class="p-4 space-y-4">
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
        <div x-show="activeTab === 'pages'" class="p-4 space-y-3" x-cloak>
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

        <!-- Notes Tab -->
        <div x-show="activeTab === 'notes'" class="p-4" x-cloak>
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
