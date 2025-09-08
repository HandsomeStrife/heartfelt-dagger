<div x-data="{
    ...characterViewerState({
        canEdit: @js($can_edit),
        isAuthenticated: @js(auth()->check()),
        characterKey: @js($character->character_key),
        final_hit_points: @js($computed_stats['final_hit_points'] ?? 6),
        stress_len: @js($computed_stats['stress'] ?? 6),
        armor_score: @js($computed_stats['armor_score'] ?? 0),
        hope: @js($computed_stats['hope'] ?? 2),
        initialStatus: @js($character_status ? $character_status->toAlpineState() : null)
    }),
    activeTab: 'health',
    dropdownOpen: false
}" class="h-full flex flex-col">
    
    <!-- Header -->
    <div class="border-b border-slate-700/50 relative">
        <!-- Hope in Very Top Right (Interactive) -->
        <div class="absolute top-3 right-3">
            <div class="flex gap-1 justify-end items-center">
                <span class="text-xs mr-2 text-zinc-400">Hope</span>
                <template x-for="(filled, index) in hope" :key="index">
                    <label>
                        <input type="checkbox" class="sr-only peer" :checked="filled" @change="toggleHope(index)">
                        <span class="block w-3 h-3 rotate-45 rounded-sm ring-1 ring-indigo-400/50 bg-slate-900 peer-checked:bg-indigo-500/85 transition-colors cursor-pointer hover:ring-indigo-400"></span>
                    </label>
                </template>
            </div>
        </div>
        
        @if ($character->class)
            <div class="absolute -left-2 top-2">
                <x-class-banner :className="$character->class" size="xs" />
            </div>
        @endif
        
        <div class="pr-24 py-6 {{ $character->class ? 'pl-16' : '' }}">
            <h2 class="font-outfit text-xl text-white">{{ $character->name }}</h2>
            <p class="text-slate-300 text-sm capitalize">{{ $character->class }} • {{ $character->ancestry }}</p>
        </div>
    </div>

    <!-- Dropdown Navigation -->
    <div class="p-3 border-b border-slate-700/50">
        <div class="relative">
            <button @click="dropdownOpen = !dropdownOpen" 
                    class="w-full flex items-center justify-between px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-sm font-medium rounded-lg transition-colors">
                <span x-text="activeTab === 'health' ? 'Overview' : 
                            activeTab === 'equipment' ? 'Equipment' : 
                            activeTab === 'abilities' ? 'Abilities' : 
                            activeTab === 'notes' ? 'Notes' : 'Select Tab'"></span>
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
                <button @click="activeTab = 'health'; dropdownOpen = false" 
                        :class="activeTab === 'health' ? 'bg-amber-500/20 text-amber-400' : 'text-slate-300 hover:bg-slate-700'"
                        class="w-full text-left px-4 py-2 text-sm transition-colors flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    Overview
                </button>
                <button @click="activeTab = 'equipment'; dropdownOpen = false" 
                        :class="activeTab === 'equipment' ? 'bg-amber-500/20 text-amber-400' : 'text-slate-300 hover:bg-slate-700'"
                        class="w-full text-left px-4 py-2 text-sm transition-colors flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Equipment
                </button>
                <button @click="activeTab = 'abilities'; dropdownOpen = false" 
                        :class="activeTab === 'abilities' ? 'bg-amber-500/20 text-amber-400' : 'text-slate-300 hover:bg-slate-700'"
                        class="w-full text-left px-4 py-2 text-sm transition-colors flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Abilities
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
        <!-- Health Tab -->
        <div x-show="activeTab === 'health'" x-cloak class="p-4 space-y-4">
            <!-- 8-Item Grid: Evasion|Agility,Strength,Finesse / Armor|Instinct,Presence,Knowledge -->
            <div class="grid grid-cols-4 gap-1 justify-items-center">
                <!-- Row 1: Evasion + First 3 Traits -->
                <div class="cursor-pointer hover:scale-105 transition-transform duration-200">
                    <x-icons.evasion-frame pest="evasion-stat" :number="$computed_stats['evasion'] ?? '?'" class="w-full max-w-[70px]" />
                </div>
                @foreach (['agility', 'strength', 'finesse'] as $trait)
                    <div class="cursor-pointer hover:scale-105 transition-transform duration-200" 
                         data-trait="{{ $trait }}" 
                         data-trait-value="{{ $trait_values[$trait] ?? '+0' }}"
                         onclick="rollTraitCheck('{{ $trait }}', {{ str_replace('+', '', $trait_values[$trait] ?? '0') }})"
                         title="Click to roll {{ $trait_info[$trait] }} check">
                        <x-icons.stat-frame pest="trait-{{ $trait }}" :number="$trait_values[$trait] ?? '+0'" :label="$trait_info[$trait]" class="w-full max-w-[70px]" />
                    </div>
                @endforeach
                
                <!-- Row 2: Armor + Last 3 Traits -->
                <div class="cursor-pointer hover:scale-105 transition-transform duration-200">
                    <x-icons.armor-frame pest="armor-stat" :number="$computed_stats['armor_score'] ?? '?'" class="w-full max-w-[70px]" />
                </div>
                @foreach (['instinct', 'presence', 'knowledge'] as $trait)
                    <div class="cursor-pointer hover:scale-105 transition-transform duration-200" 
                         data-trait="{{ $trait }}" 
                         data-trait-value="{{ $trait_values[$trait] ?? '+0' }}"
                         onclick="rollTraitCheck('{{ $trait }}', {{ str_replace('+', '', $trait_values[$trait] ?? '0') }})"
                         title="Click to roll {{ $trait_info[$trait] }} check">
                        <x-icons.stat-frame pest="trait-{{ $trait }}" :number="$trait_values[$trait] ?? '+0'" :label="$trait_info[$trait]" class="w-full max-w-[70px]" />
                    </div>
                @endforeach
            </div>

            <!-- Damage & Health (with hidden title and full-width thresholds) -->
            <div class="damage-health-sidebar">
                <x-character-viewer.damage-health :computed-stats="$computed_stats" />
            </div>
            
            <style>
                .damage-health-sidebar h2 {
                    display: none;
                }
                .damage-health-sidebar > div > div{
                    gap: 0 !important;
                }
                .damage-health-sidebar [pest="damage-thresholds"] {
                    width: 100% !important;
                }
            </style>

            <!-- Active Weapons (Compact) -->
            @if (!empty($organized_equipment['weapons']))
                @php $primary = collect($organized_equipment['weapons'])->first(fn($w) => ($w['data']['type'] ?? 'Primary') === 'Primary'); @endphp
                @if ($primary)
                    <div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-4 shadow-lg">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-bold">{{ $primary['data']['name'] ?? 'Weapon' }}</h3>
                            <span class="text-xs text-slate-400">{{ ucfirst($primary['data']['range'] ?? 'Melee') }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <!-- Attack Stat -->
                            <div class="flex-1 bg-slate-700/40 rounded p-2 cursor-pointer hover:bg-slate-600/40 transition-colors"
                                 onclick="rollWeaponAttack('{{ $primary['key'] ?? 'primary' }}')"
                                 title="Click to roll attack">
                                <div class="text-[10px] uppercase text-slate-400">{{ ucfirst($primary['data']['trait'] ?? 'Strength') }}</div>
                                <div class="text-white font-bold text-sm">
                                    {{ $character->assigned_traits[$primary['data']['trait'] ?? 'strength'] ?? 0 > 0 ? '+' : '' }}{{ $character->assigned_traits[$primary['data']['trait'] ?? 'strength'] ?? 0 }}
                                </div>
                                <div class="text-slate-500 text-[8px]">Attack</div>
                            </div>
                            
                            <!-- Damage Stat -->
                            <div class="flex-1 bg-slate-700/40 rounded p-2 cursor-pointer hover:bg-slate-600/40 transition-colors"
                                 onclick="rollWeaponDamage('{{ $primary['key'] ?? 'primary' }}')"
                                 title="Click to roll damage">
                                <div class="text-[10px] uppercase text-slate-400">Damage</div>
                                <div class="text-white font-bold text-sm">
                                    {{ $primary['data']['damage']['dice'] ?? 'd6' }}{{ isset($primary['data']['damage']['modifier']) && $primary['data']['damage']['modifier'] > 0 ? '+' . $primary['data']['damage']['modifier'] : '' }}
                                </div>
                                <div class="text-slate-500 text-[8px]">{{ $primary['data']['damage']['type'] ?? 'Physical' }}</div>
                            </div>
                            
                            <!-- Feature (if present) -->
                            @php $feature = $primary['data']['feature'] ?? null; @endphp
                            @if (is_string($feature) && $feature !== '')
                                <div class="flex-2 text-xs text-slate-300 p-2 bg-slate-900/40 rounded border-l-2 border-amber-500/30">
                                    {{ $feature }}
                                </div>
                            @elseif (is_array($feature))
                                @php
                                    $parts = [];
                                    if (function_exists('array_is_list') && array_is_list($feature)) {
                                        foreach ($feature as $entry) {
                                            if (is_string($entry)) { $parts[] = $entry; }
                                            elseif (is_array($entry)) { $parts[] = $entry['description'] ?? ($entry['name'] ?? ''); }
                                        }
                                    } else {
                                        $parts[] = $feature['description'] ?? ($feature['name'] ?? '');
                                    }
                                    $parts = array_filter($parts, fn ($p) => $p !== '');
                                @endphp
                                @if (!empty($parts))
                                    <div class="flex-2 text-xs text-slate-300 p-2 bg-slate-900/40 rounded border-l-2 border-amber-500/30">
                                        {{ implode('; ', $parts) }}
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                @endif
            @endif
        </div>

        <!-- Equipment Tab -->
        <div x-show="activeTab === 'equipment'" x-cloak class="p-4 space-y-4">
            <!-- Gold -->
            <x-character-viewer.gold />

            <!-- Experience -->
            <x-character-viewer.experience :character="$character" />
            
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
        <div x-show="activeTab === 'abilities'" x-cloak class="p-4 space-y-4">
            <!-- Hope Feature (if available) -->
            @if ($class_data && isset($class_data['hopeFeature']))
                <div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-4 shadow-lg">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold">{{ $class_data['hopeFeature']['name'] ?? $class_data['name'] . ' Hope Feature' }}</h3>
                        <div class="flex items-center gap-2 text-xs">
                            <span class="text-slate-400">Cost</span>
                            <div class="flex gap-1">
                                @for ($i = 0; $i < 3; $i++)
                                    <span class="block w-3 h-3 rotate-45 rounded-sm ring-1 ring-indigo-400/50 bg-slate-900"></span>
                                @endfor
                            </div>
                        </div>
                    </div>
                    <p class="mt-2 text-xs leading-relaxed text-slate-300">{{ $class_data['hopeFeature']['description'] ?? 'Hope feature description' }}</p>
                </div>
            @endif

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
        <div x-show="activeTab === 'notes'" x-cloak class="p-4">
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
