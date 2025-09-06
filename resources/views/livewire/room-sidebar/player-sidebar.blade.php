<div x-data="characterViewerState({
    canEdit: @js($can_edit),
    isAuthenticated: @js(auth()->check()),
    characterKey: @js($character->character_key),
    final_hit_points: @js($computed_stats['final_hit_points'] ?? 6),
    stress_len: @js($computed_stats['stress'] ?? 6),
    armor_score: @js($computed_stats['armor_score'] ?? 0),
    hope: @js($computed_stats['hope'] ?? 2),
    initialStatus: @js($character_status ? $character_status->toAlpineState() : null)
})" x-data="{ activeTab: 'health' }" class="h-full flex flex-col">
    
    <!-- Header -->
    <div class="p-4 border-b border-slate-700/50">
        <h2 class="font-outfit text-xl text-white mb-2">{{ $character->name }}</h2>
        <p class="text-slate-300 text-sm capitalize">{{ $character->class }} • {{ $character->ancestry }}</p>
    </div>

    <!-- Tab Navigation -->
    <div class="flex border-b border-slate-700/50 text-xs">
        <button @click="activeTab = 'health'" 
                :class="activeTab === 'health' ? 'bg-slate-800 text-white border-amber-500' : 'text-slate-400 hover:text-slate-300'"
                class="flex-1 px-3 py-2 font-medium border-b-2 border-transparent transition-colors">
            Health
        </button>
        
        <button @click="activeTab = 'equipment'" 
                :class="activeTab === 'equipment' ? 'bg-slate-800 text-white border-amber-500' : 'text-slate-400 hover:text-slate-300'"
                class="flex-1 px-3 py-2 font-medium border-b-2 border-transparent transition-colors">
            Equipment
        </button>
        
        <button @click="activeTab = 'abilities'" 
                :class="activeTab === 'abilities' ? 'bg-slate-800 text-white border-amber-500' : 'text-slate-400 hover:text-slate-300'"
                class="flex-1 px-3 py-2 font-medium border-b-2 border-transparent transition-colors">
            Abilities
        </button>
        
        <button @click="activeTab = 'notes'" 
                :class="activeTab === 'notes' ? 'bg-slate-800 text-white border-amber-500' : 'text-slate-400 hover:text-slate-300'"
                class="flex-1 px-3 py-2 font-medium border-b-2 border-transparent transition-colors">
            Notes
        </button>
    </div>

    <!-- Tab Content -->
    <div class="flex-1 overflow-y-auto">
        <!-- Health Tab -->
        <div x-show="activeTab === 'health'" class="p-4 space-y-4">
            <!-- Character Stats Summary -->
            <div class="bg-slate-800/30 rounded-lg p-3 border border-slate-700/50">
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="text-center">
                        <div class="text-slate-400 text-xs">Evasion</div>
                        <div class="text-white font-bold text-lg">{{ $computed_stats['evasion'] ?? 'N/A' }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-slate-400 text-xs">Armor Score</div>
                        <div class="text-white font-bold text-lg">{{ $computed_stats['armor_score'] ?? 0 }}</div>
                    </div>
                </div>
            </div>

            <!-- Damage & Health Component -->
            <x-character-viewer.damage-health :computed-stats="$computed_stats" />

            <!-- Hope & Gold in smaller format -->
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-slate-800/30 rounded-lg p-3 border border-slate-700/50">
                    <x-character-viewer.hope :class-data="$class_data" />
                </div>
                <div class="bg-slate-800/30 rounded-lg p-3 border border-slate-700/50">
                    <x-character-viewer.gold />
                </div>
            </div>

            <!-- Experience -->
            <x-character-viewer.experience :character="$character" />
        </div>

        <!-- Equipment Tab -->
        <div x-show="activeTab === 'equipment'" class="p-4 space-y-4">
            <!-- Active Weapons -->
            <x-character-viewer.active-weapons :organized-equipment="$organized_equipment" :character="$character" />
            
            <!-- Active Armor -->
            <x-character-viewer.active-armor :organized-equipment="$organized_equipment" />
            
            <!-- Equipment List (Compact) -->
            <div class="bg-slate-800/30 rounded-lg p-3 border border-slate-700/50">
                <h4 class="text-white font-outfit font-semibold mb-3">All Equipment</h4>
                <div class="space-y-2 max-h-48 overflow-y-auto">
                    @foreach($organized_equipment as $type => $items)
                        @if(count($items) > 0)
                            <div class="text-xs">
                                <div class="text-slate-400 font-medium capitalize mb-1">{{ $type }}</div>
                                @foreach($items as $item)
                                    <div class="text-slate-300 ml-2 flex items-center justify-between">
                                        <span>{{ $item['data']['name'] ?? 'Unknown' }}</span>
                                        @if($item['is_equipped'])
                                            <span class="text-green-400 text-xs">Equipped</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Abilities Tab -->
        <div x-show="activeTab === 'abilities'" class="p-4 space-y-4">
            <!-- Domain Cards -->
            <div class="space-y-3">
                <h4 class="text-white font-outfit font-semibold">Domain Cards</h4>
                @forelse($domain_card_details as $card)
                    <div class="bg-slate-800/30 rounded-lg p-3 border border-slate-700/50">
                        <div class="flex items-center justify-between mb-2">
                            <h5 class="text-white font-medium text-sm">{{ $card['ability_data']['name'] ?? 'Unknown Ability' }}</h5>
                            <span class="text-xs text-slate-400 capitalize">{{ $card['domain'] }}</span>
                        </div>
                        @if($card['ability_data'])
                            <div class="text-slate-300 text-xs space-y-1">
                                @foreach($card['ability_data']['descriptions'] ?? [] as $description)
                                    <p>{{ $description }}</p>
                                @endforeach
                            </div>
                            @if(isset($card['ability_data']['recallCost']) && $card['ability_data']['recallCost'] > 0)
                                <div class="mt-2 text-xs text-amber-400">
                                    Recall Cost: {{ $card['ability_data']['recallCost'] }} Hope
                                </div>
                            @endif
                        @endif
                    </div>
                @empty
                    <p class="text-slate-400 text-sm">No domain cards selected</p>
                @endforelse
            </div>

            <!-- Class Features (Compact) -->
            @if($class_data)
                <div class="bg-slate-800/30 rounded-lg p-3 border border-slate-700/50">
                    <h4 class="text-white font-outfit font-semibold mb-2">Class Features</h4>
                    <div class="text-slate-300 text-xs space-y-1">
                        @foreach($class_data['classFeatures'] ?? [] as $feature)
                            <div>
                                <span class="text-white font-medium">{{ $feature['name'] ?? 'Unknown' }}:</span>
                                {{ $feature['description'] ?? 'No description' }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Notes Tab -->
        <div x-show="activeTab === 'notes'" class="p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-outfit text-lg text-white">Character Notes</h3>
                <button wire:click="saveCharacterNotes" 
                        x-data="{ saving: false }"
                        @click="saving = true"
                        @character-notes-saved.window="saving = false; setTimeout(() => { $el.classList.add('text-green-400'); setTimeout(() => $el.classList.remove('text-green-400'), 2000) }, 100)"
                        class="text-amber-400 hover:text-amber-300 text-sm flex items-center transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    <span x-show="!saving">Save</span>
                    <span x-show="saving">Saving...</span>
                </button>
            </div>
            
            <textarea wire:model.live.debounce.500ms="character_notes"
                      placeholder="Add your character notes here..."
                      class="w-full h-64 bg-slate-800/50 border border-slate-600/50 rounded-lg p-3 text-white placeholder-slate-400 resize-none focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50"></textarea>
            
            <div class="mt-3 text-xs text-slate-500">
                Notes are automatically saved to the database and synced across your devices
            </div>
        </div>
    </div>
</div>
