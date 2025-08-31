<div class="min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Character Header -->
        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl mb-8 overflow-hidden">
            <div class="relative">
                @if($character->profile_image_path)
                    <div class="h-32 bg-cover bg-center relative" style="background-image: url('{{ $character->getProfileImage() }}');">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 to-transparent"></div>
                    </div>
                @else
                    <div class="h-32 bg-gradient-to-r from-slate-800 to-slate-700"></div>
                @endif
                
                <div class="px-6 pb-6 @if($character->profile_image_path) -mt-16 relative z-10 @else pt-6 @endif">
                    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                        <div class="flex-1">
                            <h1 class="font-outfit text-3xl lg:text-4xl text-white font-bold mb-2">
                                {{ $character->name ?: 'Unnamed Character' }}
                                @if($pronouns)
                                    <span class="text-slate-300 text-lg font-normal">({{ $pronouns }})</span>
                                @endif
                            </h1>
                            
                            <div class="flex flex-wrap gap-2 mb-4">
                                <!-- Level Badge -->
                                <div class="bg-amber-500/20 border border-amber-500/30 rounded-lg px-3 py-1">
                                    <span class="text-amber-300 font-semibold">Level 1</span>
                                </div>
                                
                                <!-- Class Badge -->
                                @if($class_data)
                                    <div class="bg-blue-500/20 border border-blue-500/30 rounded-lg px-3 py-1">
                                        <span class="text-blue-300 font-semibold">{{ $class_data['name'] ?? ucfirst($character->selected_class) }}</span>
                                        @if($character->selected_subclass && $subclass_data)
                                            <span class="text-blue-200 text-sm"> - {{ $subclass_data['name'] ?? ucwords(str_replace('-', ' ', $character->selected_subclass)) }}</span>
                                        @endif
                                    </div>
                                @endif
                                
                                <!-- Heritage Badge -->
                                <div class="bg-green-500/20 border border-green-500/30 rounded-lg px-3 py-1">
                                    <span class="text-green-300 font-semibold">
                                        {{ $ancestry_data['name'] ?? ucfirst($character->selected_ancestry ?? 'Unknown') }}
                                        {{ $community_data ? ' ' . $community_data['name'] : '' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Edit Button -->
                        @if($can_edit)
                            <div class="flex-shrink-0" 
                                 x-data="{ canEdit: @js($can_edit), characterKey: @js($character_key) }"
                                 x-init="(() => {
                                    // For anonymous users, verify localStorage access
                                    if (!@json(auth()->check())) {
                                        const storedKeys = JSON.parse(localStorage.getItem('daggerheart_characters') || '[]');
                                        canEdit = storedKeys.includes(characterKey);
                                    }
                                 })()"
                                 x-show="canEdit">
                                <a :href="`/character-builder/${characterKey}`"
                                   class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-black font-semibold rounded-lg transition-all duration-300 shadow-lg hover:shadow-amber-500/25">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit Character
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Character Sheet Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
            
            <!-- Left Column: Traits & Combat Stats -->
            <div class="xl:col-span-1 space-y-6">
                <!-- Character Traits -->
                @if(!empty($character->assigned_traits))
                    <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                        <h3 class="text-xl font-outfit font-bold text-white mb-6 text-center">Character Traits</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-2 gap-4">
                            @foreach(['agility', 'strength', 'finesse', 'instinct', 'presence', 'knowledge'] as $trait)
                                @php
                                    $value = $character->assigned_traits[$trait] ?? 0;
                                    $trait_name = ucfirst($trait);
                                @endphp
                                <div class="relative">
                                    <!-- Trait Box with Background -->
                                    <div class="relative bg-cover bg-center rounded-lg p-4 h-24 flex flex-col items-center justify-center" 
                                         style="background-image: url('/img/stat-box-bg.webp'); background-size: cover;">
                                        <!-- Overlay for readability -->
                                        <div class="absolute inset-0 bg-slate-900/60 rounded-lg"></div>
                                        
                                        <div class="relative z-10 text-center">
                                            <div class="text-xs uppercase tracking-wider text-slate-300 font-semibold mb-1">{{ $trait_name }}</div>
                                            <div class="text-2xl font-bold text-white">{{ $value > 0 ? '+' : '' }}{{ $value }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Combat Stats -->
                @if(!empty($computed_stats))
                    <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                        <h3 class="text-xl font-outfit font-bold text-white mb-6 text-center">Combat Stats</h3>
                        
                        <div class="space-y-4">
                            <!-- Evasion -->
                            <div class="flex items-center justify-between bg-slate-800/50 rounded-lg p-3">
                                <span class="text-slate-300 font-medium">Evasion</span>
                                <span class="text-2xl font-bold text-white">{{ $computed_stats['final_evasion'] ?? 10 }}</span>
                            </div>
                            
                            <!-- Hit Points -->
                            <div class="flex items-center justify-between bg-slate-800/50 rounded-lg p-3">
                                <span class="text-slate-300 font-medium">Hit Points</span>
                                <span class="text-2xl font-bold text-red-400">{{ $computed_stats['final_hit_points'] ?? 5 }}</span>
                            </div>
                            
                            <!-- Hope -->
                            <div class="flex items-center justify-between bg-slate-800/50 rounded-lg p-3">
                                <span class="text-slate-300 font-medium">Hope</span>
                                <div class="flex items-center space-x-1">
                                    @for($i = 1; $i <= 6; $i++)
                                        @if($i <= 2)
                                            <x-icons.bolt class="w-4 h-4 text-amber-400" />
                                        @else
                                            <x-icons.bolt class="w-4 h-4 text-slate-600" />
                                        @endif
                                    @endfor
                                </div>
                            </div>
                            
                            <!-- Stress -->
                            <div class="bg-slate-800/50 rounded-lg p-3">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-slate-300 font-medium">Stress</span>
                                    <span class="text-sm text-slate-400">{{ $computed_stats['final_stress'] ?? 6 }} slots</span>
                                </div>
                                <div class="grid grid-cols-6 gap-1">
                                    @for($i = 1; $i <= 6; $i++)
                                        <div class="w-6 h-6 border-2 border-slate-600 rounded-sm bg-slate-700"></div>
                                    @endfor
                                </div>
                            </div>
                            
                            <!-- Damage Thresholds -->
                            @if(isset($computed_stats['major_threshold']) && isset($computed_stats['severe_threshold']))
                                <div class="bg-slate-800/50 rounded-lg p-3">
                                    <div class="text-slate-300 font-medium mb-2">Damage Thresholds</div>
                                    <div class="grid grid-cols-2 gap-2 text-sm">
                                        <div class="text-center">
                                            <div class="text-orange-400 font-bold">{{ $computed_stats['major_threshold'] }}</div>
                                            <div class="text-slate-400">Major</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-red-400 font-bold">{{ $computed_stats['severe_threshold'] }}</div>
                                            <div class="text-slate-400">Severe</div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Middle Column: Equipment & Features -->
            <div class="xl:col-span-2 space-y-6">
                
                <!-- Active Weapons -->
                @if(!empty($organized_equipment['weapons']))
                    <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                        <h3 class="text-xl font-outfit font-bold text-white mb-6">Active Weapons</h3>
                        
                        <div class="space-y-4">
                            @php $weapon_count = 0; @endphp
                            @foreach($organized_equipment['weapons'] as $weapon)
                                @if($weapon_count < 2)
                                    <div class="bg-slate-800/50 rounded-lg p-4">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                            <div class="flex-1">
                                                <div class="font-semibold text-white mb-1">{{ $weapon['data']['name'] ?? ucwords(str_replace('-', ' ', $weapon['key'])) }}</div>
                                                <div class="text-sm text-slate-300">
                                                    {{ $weapon_count === 0 ? 'Primary' : 'Secondary' }} Weapon
                                                    @if(isset($weapon['data']['trait']))
                                                        • {{ ucfirst($weapon['data']['trait']) }}
                                                    @endif
                                                    @if(isset($weapon['data']['range']))
                                                        • {{ ucwords(str_replace('-', ' ', $weapon['data']['range'])) }}
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                @if(isset($weapon['data']['damage']))
                                                    <div class="text-lg font-bold text-amber-400">{{ $weapon['data']['damage']['dice'] ?? 'd6' }}{{ isset($weapon['data']['damage']['modifier']) ? ($weapon['data']['damage']['modifier'] > 0 ? '+' . $weapon['data']['damage']['modifier'] : $weapon['data']['damage']['modifier']) : '' }}</div>
                                                    <div class="text-xs text-slate-400">{{ ucfirst($weapon['data']['damage']['type'] ?? 'physical') }}</div>
                                                @endif
                                            </div>
                                        </div>
                                        @if(isset($weapon['data']['features']) && !empty($weapon['data']['features']))
                                            <div class="mt-3 pt-3 border-t border-slate-700">
                                                <div class="text-sm text-slate-300">{{ is_array($weapon['data']['features']) ? implode(', ', $weapon['data']['features']) : $weapon['data']['features'] }}</div>
                                            </div>
                                        @endif
                                    </div>
                                    @php $weapon_count++; @endphp
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Active Armor -->
                @if(!empty($organized_equipment['armor']))
                    <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                        <h3 class="text-xl font-outfit font-bold text-white mb-6">Active Armor</h3>
                        
                        @foreach($organized_equipment['armor'] as $armor)
                            <div class="bg-slate-800/50 rounded-lg p-4">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <div class="flex-1">
                                        <div class="font-semibold text-white mb-1">{{ $armor['data']['name'] ?? ucwords(str_replace('-', ' ', $armor['key'])) }}</div>
                                        <div class="text-sm text-slate-300">Armor</div>
                                    </div>
                                    <div class="text-right">
                                        @if(isset($armor['data']['baseThresholds']))
                                            <div class="text-sm text-amber-400 font-semibold">
                                                {{ $armor['data']['baseThresholds']['major'] ?? 0 }} / {{ $armor['data']['baseThresholds']['severe'] ?? 0 }}
                                            </div>
                                            <div class="text-xs text-slate-400">Major / Severe</div>
                                        @endif
                                        @if(isset($armor['data']['baseScore']))
                                            <div class="text-lg font-bold text-blue-400 mt-1">+{{ $armor['data']['baseScore'] }}</div>
                                            <div class="text-xs text-slate-400">Armor Score</div>
                                        @endif
                                    </div>
                                </div>
                                @if(isset($armor['data']['features']) && !empty($armor['data']['features']))
                                    <div class="mt-3 pt-3 border-t border-slate-700">
                                        <div class="text-sm text-slate-300">{{ is_array($armor['data']['features']) ? implode(', ', $armor['data']['features']) : $armor['data']['features'] }}</div>
                                    </div>
                                @endif
                            </div>
                            @break {{-- Only show one armor piece in the active section --}}
                        @endforeach
                    </div>
                @endif

                <!-- Inventory -->
                @if(!empty($organized_equipment['items']) || !empty($organized_equipment['consumables']))
                    <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                        <h3 class="text-xl font-outfit font-bold text-white mb-6">Inventory</h3>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($organized_equipment['items'] as $item)
                                <div class="bg-slate-800/50 rounded-lg p-3">
                                    <div class="font-medium text-white">{{ $item['data']['name'] ?? ucwords(str_replace('-', ' ', $item['key'])) }}</div>
                                    <div class="text-sm text-slate-400">Item</div>
                                    @if(isset($item['data']['description']))
                                        <div class="text-xs text-slate-300 mt-1">{{ $item['data']['description'] }}</div>
                                    @endif
                                </div>
                            @endforeach
                            
                            @foreach($organized_equipment['consumables'] as $consumable)
                                <div class="bg-slate-800/50 rounded-lg p-3">
                                    <div class="font-medium text-white">{{ $consumable['data']['name'] ?? ucwords(str_replace('-', ' ', $consumable['key'])) }}</div>
                                    <div class="text-sm text-slate-400">Consumable</div>
                                    @if(isset($consumable['data']['description']))
                                        <div class="text-xs text-slate-300 mt-1">{{ $consumable['data']['description'] }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                <!-- Class Features -->
                @if($class_data)
                    <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                        <h3 class="text-xl font-outfit font-bold text-white mb-6">Class Features</h3>
                        
                        <div class="space-y-4">
                            <!-- Hope Feature -->
                            @if(isset($class_data['hopeFeature']))
                                <div class="bg-slate-800/50 rounded-lg p-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <x-icons.bolt class="w-5 h-5 text-amber-400" />
                                        <h4 class="font-bold text-amber-300">{{ $class_data['hopeFeature']['name'] ?? 'Hope Feature' }}</h4>
                                        <span class="text-xs bg-amber-500/20 text-amber-300 px-2 py-1 rounded">3 Hope</span>
                                    </div>
                                    <p class="text-slate-300 text-sm">{{ $class_data['hopeFeature']['description'] ?? 'Hope feature description' }}</p>
                                </div>
                            @endif
                            
                            <!-- Class Features -->
                            @if(isset($class_data['classFeatures']) && is_array($class_data['classFeatures']))
                                @foreach($class_data['classFeatures'] as $feature)
                                    <div class="bg-slate-800/50 rounded-lg p-4">
                                        <h4 class="font-bold text-white mb-2">{{ $feature['name'] ?? 'Class Feature' }}</h4>
                                        <p class="text-slate-300 text-sm">{{ $feature['description'] ?? 'Feature description' }}</p>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Column: Domain Cards, Experiences & Background -->
            <div class="xl:col-span-1 space-y-6">
                
                <!-- Domain Cards -->
                @if(!empty($domain_card_details))
                    <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                        <h3 class="text-xl font-outfit font-bold text-white mb-6 text-center">Domain Cards</h3>
                        
                        <div class="space-y-4">
                            @foreach($domain_card_details as $card)
                                @php
                                    $domain = $card['domain'] ?? 'unknown';
                                    $domain_colors = [
                                        'arcana' => 'from-purple-600 to-violet-600',
                                        'blade' => 'from-red-600 to-rose-600',
                                        'bone' => 'from-gray-600 to-slate-600',
                                        'codex' => 'from-blue-600 to-indigo-600',
                                        'grace' => 'from-pink-600 to-rose-600',
                                        'midnight' => 'from-gray-900 to-black',
                                        'sage' => 'from-green-600 to-emerald-600',
                                        'splendor' => 'from-yellow-500 to-amber-500',
                                        'valor' => 'from-orange-600 to-red-600',
                                        'dread' => 'from-purple-900 to-indigo-900',
                                    ];
                                    $gradient = $domain_colors[$domain] ?? 'from-slate-600 to-slate-700';
                                @endphp
                                
                                <div class="bg-slate-800/50 border border-slate-600/50 rounded-lg overflow-hidden">
                                    <!-- Card Header -->
                                    <div class="bg-gradient-to-r {{ $gradient }} p-3 relative">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                @if(view()->exists("components.icons.{$domain}"))
                                                    <x-dynamic-component :component="'icons.' . $domain" class="w-5 h-5 text-white" />
                                                @endif
                                                <span class="text-white font-semibold text-sm">Level {{ $card['ability_level'] ?? 1 }}</span>
                                            </div>
                                            @if(isset($card['ability_data']['recallCost']))
                                                <div class="flex items-center gap-1 bg-black/30 rounded-full px-2 py-1">
                                                    <x-icons.bolt class="w-3 h-3 text-amber-300" />
                                                    <span class="text-white text-xs font-bold">{{ $card['ability_data']['recallCost'] }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Card Content -->
                                    <div class="p-3">
                                        <h4 class="font-bold text-white mb-1">
                                            {{ $card['ability_data']['name'] ?? ucwords(str_replace('-', ' ', $card['ability_key'])) }}
                                        </h4>
                                        <div class="text-xs text-slate-400 mb-2">
                                            {{ ucfirst($domain) }} • {{ $card['ability_data']['type'] ?? 'Ability' }}
                                        </div>
                                        @if(isset($card['ability_data']['descriptions']) && is_array($card['ability_data']['descriptions']))
                                            <div class="text-sm text-slate-300 space-y-1">
                                                @foreach($card['ability_data']['descriptions'] as $description)
                                                    <p>{{ $description }}</p>
                                                @endforeach
                                            </div>
                                        @elseif(isset($card['ability_data']['description']))
                                            <p class="text-sm text-slate-300">{{ $card['ability_data']['description'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Experiences -->
                @if(!empty($character->experiences))
                    <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                        <h3 class="text-xl font-outfit font-bold text-white mb-6">Experiences</h3>
                        <div class="space-y-3">
                            @foreach($character->experiences as $experience)
                                <div class="bg-slate-800/50 rounded-lg p-4">
                                    <div class="font-medium text-white mb-1">{{ $experience['name'] }}</div>
                                    @if(!empty($experience['description']))
                                        <div class="text-slate-300 text-sm mb-2">{{ $experience['description'] }}</div>
                                    @endif
                                    <div class="flex items-center gap-1">
                                        <span class="text-amber-400 font-semibold">+{{ $experience['modifier'] ?? 2 }}</span>
                                        <span class="text-slate-400 text-sm">to related rolls</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Background & Connections -->
                @if(!empty($character->background_answers) || !empty($character->connection_answers))
                    <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                        <h3 class="text-xl font-outfit font-bold text-white mb-6">Character Details</h3>
                        
                        <div class="space-y-4">
                            @if(!empty($character->background_answers))
                                <div>
                                    <h4 class="font-semibold text-slate-300 mb-2">Background</h4>
                                    <div class="space-y-2">
                                        @foreach($character->background_answers as $index => $answer)
                                            @if(!empty($answer))
                                                <div class="bg-slate-800/50 rounded p-3">
                                                    <p class="text-slate-300 text-sm">{{ $answer }}</p>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            
                            @if(!empty($character->connection_answers))
                                <div>
                                    <h4 class="font-semibold text-slate-300 mb-2">Connections</h4>
                                    <div class="space-y-2">
                                        @foreach($character->connection_answers as $index => $answer)
                                            @if(!empty($answer))
                                                <div class="bg-slate-800/50 rounded p-3">
                                                    <p class="text-slate-300 text-sm">{{ $answer }}</p>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>


