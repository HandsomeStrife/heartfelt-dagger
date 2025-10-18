<!-- Domain Card Selection Step -->
<div class="space-y-6 sm:space-y-8" 
     x-data="{
         expandedLevel: {{ $character->starting_level > 1 ? 1 : 'null' }},
         toggleLevel(level) {
             this.expandedLevel = this.expandedLevel === level ? null : level;
         }
     }">
    <!-- Step Header -->
    <div class="mb-6 sm:mb-8">
        <div class="flex flex-col gap-3 sm:gap-4 mb-4">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-white mb-2 font-outfit">Select Domain Cards</h2>
                @if($character->starting_level === 1)
                    <p class="text-slate-300 font-roboto text-sm sm:text-base">Choose 2 starting domain cards from your class domains to represent your character's initial magical abilities.</p>
                @else
                    <p class="text-slate-300 font-roboto text-sm sm:text-base">Select domain cards for each level up to level {{ $character->starting_level }}. You'll choose 2 cards at level 1, then 1 card per level thereafter.</p>
                @endif
            </div>
            
            @if($character->starting_level > 1)
                <!-- Level-based Selection Info -->
                <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4">
                    <h4 class="text-blue-300 font-semibold mb-2 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        Card Selection Rules
                    </h4>
                    <ul class="space-y-1.5 text-xs text-slate-300">
                        <li class="flex items-start gap-2">
                            <span class="text-blue-400 mt-0.5">•</span>
                            <span><span class="font-semibold text-white">Level 1:</span> Select 2 domain cards from your class domains</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-blue-400 mt-0.5">•</span>
                            <span><span class="font-semibold text-white">Levels 2+:</span> Select 1 card at or below your current level from your class domains</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-blue-400 mt-0.5">•</span>
                            <span><span class="font-semibold text-white">Advancement Bonus:</span> If you select "Additional Domain Card" advancements, those cards appear in separate groups below</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-amber-400 mt-0.5">⚠</span>
                            <span><span class="text-amber-300 font-semibold">Each card can only be selected once</span> - Once you choose a card at any level, it's locked and cannot be selected again</span>
                        </li>
                    </ul>
                </div>
            @endif
        </div>
    </div>

    <!-- Overall Progress Indicator -->
    @if($character->starting_level > 1)
        <div class="bg-slate-800/50 border border-slate-700 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-white font-semibold text-sm mb-1">Card Selection Progress</h4>
                    <p class="text-slate-400 text-xs">Select cards for each level to complete this step</p>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-white">
                        <span x-text="(selected_domain_cards.length + Object.keys(creation_domain_cards || {}).length)"></span>
                        <span class="text-slate-500">/</span>
                        <span x-text="starting_level + 1"></span>
                    </div>
                    <p class="text-xs text-slate-400">cards selected</p>
                </div>
            </div>
        </div>
    @endif
    <!-- Domain Card Guide & Selection Strategy -->
    <div class="bg-purple-500/10 border border-purple-500/20 rounded-xl p-4 sm:p-6">
        <h4 class="text-purple-300 font-semibold font-outfit mb-3 text-sm sm:text-base">Understanding Domain Cards & Selection Strategy</h4>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 text-sm text-slate-300">
            <div>
                <h5 class="text-white font-medium mb-3">What Are Domain Cards?</h5>
                <ul class="space-y-2 text-xs leading-relaxed">
                    <li>• <span class="text-purple-300 font-semibold">Magical Abilities:</span> Domain cards represent your character's magical powers and specialized abilities</li>
                    <li>• <span class="text-blue-300 font-semibold">Class-Based:</span> Your available domains are determined by your chosen class</li>
                    <li>• <span class="text-amber-300 font-semibold">Hope-Powered:</span> Most domain cards require Hope to activate their abilities</li>
                    <li>• <span class="text-green-300 font-semibold">Progressive:</span> You start with 2 cards and gain more as you advance</li>
                </ul>
            </div>
            <div>
                <h5 class="text-white font-medium mb-3">Reading the Cards</h5>
                <ul class="space-y-2 text-xs leading-relaxed">
                    <li>• <span class="inline-block w-4 h-4 bg-amber-500 rounded-lg text-center text-black text-xs font-bold mr-2">1</span>Level requirement (shown in banner)</li>
                    <li>
                        <div class="flex items-center gap-1">
                            • 
                            <div class="bg-zinc-950 rounded-full text-white text-xs font-bold px-2 flex gap-1 items-center">0 <x-icons.bolt class="w-3 h-3" /></div>    
                            Recall cost (top-right circle)
                        </div> 
                    </li>
                    <li>• <span class="px-1 py-0.5 bg-orange-500/20 text-orange-300 rounded text-xs font-bold mr-2">ABILITY</span>Type (under title)</li>
                    <li>• <span class="text-emerald-300 font-semibold">Domain badge:</span> Shown at the bottom of each card</li>
                </ul>
            </div>
            <div>
                <h5 class="text-white font-medium mb-3">Selection Tips</h5>
                <ul class="space-y-2 text-xs leading-relaxed">
                    <li>• <span class="text-blue-300 font-semibold">Balance:</span> Consider both offensive and defensive abilities</li>
                    <li>• <span class="text-green-300 font-semibold">Hope costs:</span> Lower costs mean more frequent use</li>
                    <li>• <span class="text-purple-300 font-semibold">Synergy:</span> Look for abilities that work well together</li>
                    <li>• <span class="text-amber-300 font-semibold">Click to deselect:</span> Click selected cards to remove them</li>
                </ul>
            </div>
        </div>
        
        <template x-if="Object.keys(filteredDomainCards).length > 0">
            <div class="mt-6 pt-4 border-t border-purple-500/20">
                <div class="flex items-center gap-4 text-sm">
                    <span class="text-slate-300 font-semibold">Your class domains:</span>
                    <template x-for="domainKey in classDomains" :key="domainKey">
                        <span class="inline-flex items-center px-3 py-1 text-white rounded-lg font-bold"
                              :style="`background-color: ${getDomainColor(domainKey)}`"
                              x-text="domainKey.charAt(0).toUpperCase() + domainKey.slice(1)">
                        </span>
                    </template>
                    <span class="text-white font-bold ml-auto bg-slate-700 px-2 py-1 rounded-md">
                        <span pest="domain-card-selected-count" x-text="selected_domain_cards.length"></span>/<span x-text="maxDomainCards"></span> selected
                    </span>
                </div>
            </div>
        </template>
    </div>

    <!-- Level-Organized Domain Card Selection -->
    @if(!empty($game_data['domains']) && !empty($game_data['abilities']))
        @if($character->starting_level === 1)
            <!-- Level 1 Only (Original Flat View) -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-white font-outfit">Level 1 Domain Cards</h3>
                    <span class="text-white font-bold bg-slate-700 px-3 py-1 rounded-md">
                        <span pest="domain-card-selected-count" x-text="selected_domain_cards.length"></span>/2 selected
                    </span>
                </div>
            </div>
        @else
            <!-- Multi-Level Accordion View -->
            <div class="space-y-3">
                @for($level = 1; $level <= $character->starting_level; $level++)
                    @php
                        $cardsNeeded = $level === 1 ? 2 : 1;
                        // Get tier for color coding
                        $tier = $level <= 1 ? 1 : ($level <= 4 ? 2 : ($level <= 7 ? 3 : 4));
                        $tierColors = [
                            1 => ['bg' => 'bg-blue-500/10', 'border' => 'border-blue-500/30', 'text' => 'text-blue-300'],
                            2 => ['bg' => 'bg-emerald-500/10', 'border' => 'border-emerald-500/30', 'text' => 'text-emerald-300'],
                            3 => ['bg' => 'bg-purple-500/10', 'border' => 'border-purple-500/30', 'text' => 'text-purple-300'],
                            4 => ['bg' => 'bg-amber-500/10', 'border' => 'border-amber-500/30', 'text' => 'text-amber-300'],
                        ];
                        $tierColor = $tierColors[$tier];
                    @endphp
                    
                    <div class="border {{ $tierColor['border'] }} {{ $tierColor['bg'] }} rounded-lg overflow-hidden">
                        <!-- Level Header (Clickable) -->
                        <button 
                            @click="toggleLevel({{ $level }})"
                            class="w-full px-4 py-3 flex items-center justify-between hover:bg-slate-800/30 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-2">
                                    <span class="{{ $tierColor['text'] }} font-bold text-lg">Level {{ $level }}</span>
                                    @if($tier > 1)
                                        <span class="text-xs px-2 py-0.5 rounded {{ $tierColor['bg'] }} {{ $tierColor['border'] }} border {{ $tierColor['text'] }} font-semibold">
                                            Tier {{ $tier }}
                                        </span>
                                    @endif
                                </div>
                                <span class="text-sm text-slate-400">
                                    (Select {{ $cardsNeeded }} card{{ $cardsNeeded > 1 ? 's' : '' }})
                                </span>
                            </div>
                            <div class="flex items-center gap-3">
                                <!-- Selection Status -->
                                <div class="text-sm">
                                    @if($level === 1)
                                        <span x-show="selected_domain_cards.length >= 2" class="text-emerald-400 font-semibold">✓ Complete</span>
                                        <span x-show="selected_domain_cards.length < 2" class="text-slate-400">
                                            <span x-text="selected_domain_cards.length"></span>/2 selected
                                        </span>
                                    @else
                                        <span x-show="creation_domain_cards && creation_domain_cards[{{ $level }}]" class="text-emerald-400 font-semibold">✓ Complete</span>
                                        <span x-show="!creation_domain_cards || !creation_domain_cards[{{ $level }}]" class="text-slate-400">
                                            0/1 selected
                                        </span>
                                    @endif
                                </div>
                                <!-- Chevron Icon -->
                                <svg 
                                    class="w-5 h-5 text-slate-400 transition-transform"
                                    :class="{'rotate-180': expandedLevel === {{ $level }}}"
                                    fill="none" 
                                    stroke="currentColor" 
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </button>
                        
                        <!-- Level Content (Collapsible) -->
                        <div 
                            x-show="expandedLevel === {{ $level }}"
                            x-collapse
                            class="border-t {{ $tierColor['border'] }}">
                            <div class="p-4 space-y-4">
                                @php
                                    // Get maximum level for card availability
                                    $maxCardLevel = $level;
                                    
                                    // Get already selected cards across all levels
                                    $alreadySelectedKeys = collect($character->selected_domain_cards)
                                        ->pluck('ability_key')
                                        ->merge(
                                            collect($character->creation_domain_cards ?? [])
                                                ->values() // Get all values (card objects) from the level-indexed array
                                                ->pluck('ability_key')
                                        )
                                        ->unique()
                                        ->filter() // Remove any null/empty values
                                        ->toArray();
                                    
                                    // Get class domains
                                    $classDomains = !empty($character->selected_class) && isset($game_data['classes'][$character->selected_class]['domains'])
                                        ? $game_data['classes'][$character->selected_class]['domains']
                                        : [];
                                    
                                    // Filter available cards for this level
                                    $availableCards = collect($game_data['abilities'] ?? [])
                                        ->filter(function($ability, $key) use ($classDomains, $maxCardLevel, $alreadySelectedKeys) {
                                            $abilityLevel = $ability['level'] ?? 1;
                                            return in_array($ability['domain'] ?? '', $classDomains) &&
                                                   $abilityLevel <= $maxCardLevel &&
                                                   !in_array($key, $alreadySelectedKeys);
                                        })
                                        ->groupBy('domain');
                                    
                                    // Check if this level has a selection
                                    $levelSelection = $level === 1 
                                        ? collect($character->selected_domain_cards)
                                        : collect($character->creation_domain_cards ?? [])->get($level);
                                    
                                    $cardsNeededForLevel = $level === 1 ? 2 : 1;
                                    $currentSelectionCount = $level === 1 
                                        ? count($character->selected_domain_cards)
                                        : ($levelSelection ? 1 : 0);
                                @endphp
                                
                                @if($availableCards->isEmpty() && $currentSelectionCount < $cardsNeededForLevel)
                                    <div class="text-center py-8 text-slate-400">
                                        <p class="text-sm">No cards available at this level.</p>
                                        <p class="text-xs mt-1">All cards may have been selected at previous levels.</p>
                                    </div>
                                @else
                                    <!-- Show selected card(s) for this level -->
                                    @if($currentSelectionCount > 0)
                                        <div class="bg-slate-800/50 border border-slate-700 rounded-lg p-3 mb-4">
                                            <h5 class="text-white font-semibold text-sm mb-2">
                                                Selected for Level {{ $level }}:
                                            </h5>
                                            <div class="flex flex-wrap gap-2">
                                                @if($level === 1)
                                                    @foreach($character->selected_domain_cards as $card)
                                                        <span class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-500/20 border border-emerald-500/30 rounded-lg text-emerald-300 text-sm">
                                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                            </svg>
                                                            {{ $card['name'] ?? ucwords(str_replace('-', ' ', $card['ability_key'])) }}
                                                        </span>
                                                    @endforeach
                                                @elseif($levelSelection)
                                                    <span class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-500/20 border border-emerald-500/30 rounded-lg text-emerald-300 text-sm">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        </svg>
                                                        {{ $levelSelection['name'] ?? ucwords(str_replace('-', ' ', $levelSelection['ability_key'])) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <!-- Domain card selection grid -->
                                    @foreach($availableCards as $domainKey => $domainCards)
                                        <div class="space-y-3">
                                            <h5 class="text-white font-semibold text-sm">
                                                {{ ucfirst($domainKey) }} Domain Cards
                                                <span class="text-slate-400 font-normal text-xs ml-2">
                                                    ({{ $domainCards->count() }} available)
                                                </span>
                                            </h5>
                                            
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                @foreach($domainCards as $abilityKey => $abilityData)
                                                    @php
                                                        // Map domain colors
                                                        $domainColors = [
                                                            'valor' => '#e2680e',
                                                            'splendor' => '#b8a342', 
                                                            'sage' => '#244e30',
                                                            'midnight' => '#1e201f',
                                                            'grace' => '#8d3965',
                                                            'codex' => '#24395d',
                                                            'bone' => '#a4a9a8',
                                                            'blade' => '#af231c',
                                                            'arcana' => '#4e345b',
                                                            'dread' => '#1e201f',
                                                        ];
                                                        $domainColor = $domainColors[$domainKey] ?? '#24395d';
                                                    @endphp
                                                    
                                                    <!-- Domain Card -->
                                                    <div 
                                                        x-data="{ ability: @js($abilityData), level: {{ $level }} }"
                                                        @click="selectDomainCardForLevel({{ $level }}, '{{ $domainKey }}', '{{ $abilityKey }}', ability)"
                                                        :class="{
                                                            'relative group cursor-pointer transition-all duration-200 transform hover:scale-[1.02] hover:-translate-y-1 active:scale-[0.98] bg-slate-900 border-2 rounded-xl overflow-hidden shadow-lg flex flex-col touch-manipulation min-h-[380px]': true,
                                                            'border-emerald-500 ring-4 ring-emerald-500/50 shadow-xl shadow-emerald-500/25': isCardSelectedAtAnyLevel('{{ $abilityKey }}'),
                                                            'border-slate-700 hover:border-slate-600 hover:shadow-xl hover:shadow-blue-300/20': !isCardSelectedAtAnyLevel('{{ $abilityKey }}')
                                                        }"
                                                        wire:key="level-{{ $level }}-{{ $domainKey }}-{{ $abilityKey }}">
                                                        
                                                        <!-- Card content structure (simplified from existing) -->
                                                        <div class="relative min-h-[100px] flex flex-col items-center justify-end bg-slate-900 w-full overflow-hidden rounded-t-xl p-4">
                                                            <div class="w-full">
                                                                <h5 class="text-white font-black font-outfit text-lg leading-tight uppercase">
                                                                    {{ $abilityData['name'] ?? ucwords(str_replace('-', ' ', $abilityKey)) }}
                                                                </h5>
                                                                <div class="text-xs font-bold uppercase tracking-wide mt-1" style="color: {{ $domainColor }}">
                                                                    {{ $abilityData['type'] ?? 'ability' }}
                                                                    <span class="ml-2 px-1.5 py-0.5 text-[10px] rounded bg-slate-600/60 text-slate-200">Lvl {{ $abilityData['level'] ?? 1 }}</span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Card Content -->
                                                        <div class="flex flex-col relative px-4 py-4 text-sm text-white flex-1">
                                                            <div class="flex-1 text-white text-sm leading-relaxed">
                                                                @if(isset($abilityData['descriptions']) && is_array($abilityData['descriptions']))
                                                                    @foreach($abilityData['descriptions'] as $description)
                                                                        <p class="mb-2">{{ $description }}</p>
                                                                    @endforeach
                                                                @elseif(isset($abilityData['description']))
                                                                    <p>{{ $abilityData['description'] }}</p>
                                                                @endif
                                                            </div>
                                                            
                                                                    <!-- Domain Label -->
                                                                    <div class="mt-auto pt-3 text-center">
                                                                        <span class="inline-flex items-center px-2 py-1 text-[10px] font-bold uppercase tracking-widest rounded-md" style="background-color: {{ $domainColor }}; color: white;">
                                                                            {{ ucfirst($domainKey) }} Domain
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                
                                                                <!-- Selection indicator -->
                                                                <template x-if="isCardSelectedAtAnyLevel('{{ $abilityKey }}')">
                                                                    <div class="absolute top-4 right-4 z-50">
                                                                        <div class="bg-emerald-500 rounded-full p-1.5 shadow-lg ring-2 ring-white">
                                                                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                                            </svg>
                                                                        </div>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endfor
                        
                        <!-- Advancement-Granted Domain Cards -->
                        @if($character->starting_level > 1)
                            @for($level = 2; $level <= $character->starting_level; $level++)
                                @php
                                    // Check if this level has any "domain_card" advancements
                                    $levelAdvancements = $character->creation_advancements[$level] ?? [];
                                    $domainCardAdvancements = collect($levelAdvancements)->filter(fn($adv) => 
                                        isset($adv['type']) && $adv['type'] === 'domain_card'
                                    );
                                    
                                    if ($domainCardAdvancements->isEmpty()) {
                                        continue;
                                    }
                                    
                                    // Get tier for color coding
                                    $tier = $level <= 1 ? 1 : ($level <= 4 ? 2 : ($level <= 7 ? 3 : 4));
                                    $tierColors = [
                                        1 => ['bg' => 'bg-blue-500/10', 'border' => 'border-blue-500/30', 'text' => 'text-blue-300'],
                                        2 => ['bg' => 'bg-emerald-500/10', 'border' => 'border-emerald-500/30', 'text' => 'text-emerald-300'],
                                        3 => ['bg' => 'bg-purple-500/10', 'border' => 'border-purple-500/30', 'text' => 'text-purple-300'],
                                        4 => ['bg' => 'bg-amber-500/10', 'border' => 'border-amber-500/30', 'text' => 'text-amber-300'],
                                    ];
                                    $tierColor = $tierColors[$tier];
                                @endphp
                                
                                @foreach($domainCardAdvancements as $advIndex => $advancement)
                                    @php
                                        // Create unique key for this advancement card
                                        $advKey = "adv_{$level}_{$advIndex}";
                                        
                                        // Get already selected cards across all levels + advancements
                                        $alreadySelectedKeys = collect($character->selected_domain_cards)
                                            ->pluck('ability_key')
                                            ->merge(
                                                collect($character->creation_domain_cards ?? [])
                                                    ->values()
                                                    ->pluck('ability_key')
                                            )
                                            ->merge(
                                                collect($character->creation_advancement_cards ?? [])
                                                    ->values()
                                                    ->pluck('ability_key')
                                            )
                                            ->unique()
                                            ->filter()
                                            ->toArray();
                                        
                                        // Get class domains
                                        $classDomains = !empty($character->selected_class) && isset($game_data['classes'][$character->selected_class]['domains'])
                                            ? $game_data['classes'][$character->selected_class]['domains']
                                            : [];
                                        
                                        // Filter available cards (max level = current level)
                                        $availableCards = collect($game_data['abilities'] ?? [])
                                            ->filter(function($ability, $key) use ($classDomains, $level, $alreadySelectedKeys) {
                                                $abilityLevel = $ability['level'] ?? 1;
                                                return in_array($ability['domain'] ?? '', $classDomains) &&
                                                       $abilityLevel <= $level &&
                                                       !in_array($key, $alreadySelectedKeys);
                                            })
                                            ->groupBy('domain');
                                        
                                        // Check if this advancement has a selection
                                        $advSelection = collect($character->creation_advancement_cards ?? [])->get($advKey);
                                    @endphp
                                    
                                    <div class="border {{ $tierColor['border'] }} {{ $tierColor['bg'] }} rounded-lg overflow-hidden mt-3">
                                        <!-- Advancement Header -->
                                        <button 
                                            @click="toggleLevel('{{ $advKey }}')"
                                            class="w-full px-4 py-3 flex items-center justify-between hover:bg-slate-800/30 transition-colors">
                                            <div class="flex items-center gap-3">
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-5 h-5 {{ $tierColor['text'] }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                    <span class="{{ $tierColor['text'] }} font-bold text-sm">Tier {{ $tier }} Advancement</span>
                                                    <span class="text-xs px-2 py-0.5 rounded {{ $tierColor['bg'] }} {{ $tierColor['border'] }} border {{ $tierColor['text'] }} font-semibold">
                                                        Level {{ $level }}
                                                    </span>
                                                </div>
                                                <span class="text-sm text-slate-400">
                                                    (Select 1 card)
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <!-- Selection Status -->
                                                <div class="text-sm">
                                                    <span x-show="creation_advancement_cards && creation_advancement_cards['{{ $advKey }}']" class="text-emerald-400 font-semibold">✓ Complete</span>
                                                    <span x-show="!creation_advancement_cards || !creation_advancement_cards['{{ $advKey }}']" class="text-slate-400">
                                                        0/1 selected
                                                    </span>
                                                </div>
                                                <!-- Chevron Icon -->
                                                <svg 
                                                    class="w-5 h-5 text-slate-400 transition-transform"
                                                    :class="{'rotate-180': expandedLevel === '{{ $advKey }}'}"
                                                    fill="none" 
                                                    stroke="currentColor" 
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </div>
                                        </button>
                                        
                                        <!-- Advancement Content (Collapsible) -->
                                        <div 
                                            x-show="expandedLevel === '{{ $advKey }}'"
                                            x-collapse
                                            class="border-t {{ $tierColor['border'] }}">
                                            <div class="p-4 space-y-4">
                                                @if($advSelection)
                                                    <div class="bg-slate-800/50 border border-slate-700 rounded-lg p-3 mb-4">
                                                        <h5 class="text-white font-semibold text-sm mb-2">
                                                            Selected for Tier {{ $tier }} Advancement:
                                                        </h5>
                                                        <div class="flex flex-wrap gap-2">
                                                            <span class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-500/20 border border-emerald-500/30 rounded-lg text-emerald-300 text-sm">
                                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                                </svg>
                                                                {{ $advSelection['name'] ?? ucwords(str_replace('-', ' ', $advSelection['ability_key'])) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endif
                                                
                                                @if($availableCards->isEmpty())
                                                    <div class="text-center py-8 text-slate-400">
                                                        <p class="text-sm">No cards available for this advancement.</p>
                                                        <p class="text-xs mt-1">All cards may have been selected at other levels.</p>
                                                    </div>
                                                @else
                                                    <!-- Domain card selection grid -->
                                                    @foreach($availableCards as $domainKey => $domainCards)
                                                        <div class="space-y-3">
                                                            <h5 class="text-white font-semibold text-sm">
                                                                {{ ucfirst($domainKey) }} Domain Cards
                                                                <span class="text-slate-400 font-normal text-xs ml-2">
                                                                    ({{ $domainCards->count() }} available)
                                                                </span>
                                                            </h5>
                                                            
                                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                                @foreach($domainCards as $abilityKey => $abilityData)
                                                                    @php
                                                                        $domainColors = [
                                                                            'valor' => '#e2680e',
                                                                            'splendor' => '#b8a342', 
                                                                            'sage' => '#244e30',
                                                                            'midnight' => '#1e201f',
                                                                            'grace' => '#8d3965',
                                                                            'codex' => '#24395d',
                                                                            'bone' => '#a4a9a8',
                                                                            'blade' => '#af231c',
                                                                            'arcana' => '#4e345b',
                                                                            'dread' => '#1e201f',
                                                                        ];
                                                                        $domainColor = $domainColors[$domainKey] ?? '#24395d';
                                                                    @endphp
                                                                    
                                                                    <!-- Simplified Domain Card -->
                                                                    <div 
                                                                        x-data="{ ability: @js($abilityData), advKey: '{{ $advKey }}' }"
                                                                        @click="selectAdvancementDomainCard('{{ $advKey }}', '{{ $domainKey }}', '{{ $abilityKey }}', ability)"
                                                                        :class="{
                                                                            'relative group cursor-pointer transition-all duration-200 transform hover:scale-[1.02] hover:-translate-y-1 active:scale-[0.98] bg-slate-900 border-2 rounded-xl overflow-hidden shadow-lg flex flex-col touch-manipulation min-h-[340px]': true,
                                                                            'border-emerald-500 ring-4 ring-emerald-500/50 shadow-xl shadow-emerald-500/25': creation_advancement_cards && creation_advancement_cards['{{ $advKey }}'] && creation_advancement_cards['{{ $advKey }}'].ability_key === '{{ $abilityKey }}',
                                                                            'border-slate-700 hover:border-slate-600 hover:shadow-xl hover:shadow-blue-300/20': !creation_advancement_cards || !creation_advancement_cards['{{ $advKey }}'] || creation_advancement_cards['{{ $advKey }}'].ability_key !== '{{ $abilityKey }}'
                                                                        }"
                                                                        wire:key="adv-{{ $advKey }}-{{ $domainKey }}-{{ $abilityKey }}">
                                                                        
                                                                        <!-- Card Header -->
                                                                        <div class="relative min-h-[90px] flex flex-col items-center justify-end bg-slate-900 w-full overflow-hidden rounded-t-xl p-4">
                                                                            <div class="w-full">
                                                                                <h5 class="text-white font-black font-outfit text-base leading-tight uppercase">
                                                                                    {{ $abilityData['name'] ?? ucwords(str_replace('-', ' ', $abilityKey)) }}
                                                                                </h5>
                                                                                <div class="text-xs font-bold uppercase tracking-wide mt-1" style="color: {{ $domainColor }}">
                                                                                    {{ $abilityData['type'] ?? 'ability' }}
                                                                                    <span class="ml-2 px-1.5 py-0.5 text-[10px] rounded bg-slate-600/60 text-slate-200">Lvl {{ $abilityData['level'] ?? 1 }}</span>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <!-- Card Content -->
                                                                        <div class="flex flex-col relative px-4 py-3 text-sm text-white flex-1">
                                                                            <div class="flex-1 text-white text-xs leading-relaxed">
                                                                                @if(isset($abilityData['descriptions']) && is_array($abilityData['descriptions']))
                                                                                    @foreach($abilityData['descriptions'] as $description)
                                                                                        <p class="mb-2">{{ $description }}</p>
                                                                                    @endforeach
                                                                                @elseif(isset($abilityData['description']))
                                                                                    <p>{{ $abilityData['description'] }}</p>
                                                                                @endif
                                                                            </div>
                                                                            
                                                                            <!-- Domain Label -->
                                                                            <div class="mt-auto pt-3 text-center">
                                                                                <span class="inline-flex items-center px-2 py-1 text-[10px] font-bold uppercase tracking-widest rounded-md" style="background-color: {{ $domainColor }}; color: white;">
                                                                                    {{ ucfirst($domainKey) }} Domain
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        <!-- Selection indicator -->
                                                                        <template x-if="creation_advancement_cards && creation_advancement_cards['{{ $advKey }}'] && creation_advancement_cards['{{ $advKey }}'].ability_key === '{{ $abilityKey }}'">
                                                                            <div class="absolute top-4 right-4 z-50">
                                                                                <div class="bg-emerald-500 rounded-full p-1.5 shadow-lg ring-2 ring-white">
                                                                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                                                    </svg>
                                                                                </div>
                                                                            </div>
                                                                        </template>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endfor
                        @endif
                    </div>
                @endif
        
        <!-- Old flat view for level 1 only -->
        @if($character->starting_level === 1)
        @foreach($game_data['domains'] as $domainKey => $domainInfo)
            @php
                // Get level 1 abilities for this domain (character creation only)
                $domainAbilities = collect($game_data['abilities'])->filter(fn($ability) => 
                    $ability['domain'] === $domainKey && ($ability['level'] ?? 1) === 1
                );
            @endphp
            @if($domainAbilities->isNotEmpty())
                <div class="space-y-4" 
                     x-show="classDomains.includes('{{ $domainKey }}')"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95">
                    <div class="flex items-center gap-4">
                        <h4 class="text-xl font-bold text-white font-outfit">{{ ucfirst($domainKey) }} Domain</h4>
                        <span class="text-slate-400 text-sm" x-text="countSelectedInDomain('{{ $domainKey }}') + ' selected from this domain'"></span>
                    </div>

                    <!-- Available Abilities -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                        @foreach($domainAbilities as $abilityKey => $abilityData)
                        @php
                            $isSelected = collect($character->selected_domain_cards)->contains(fn($card) => 
                                $card['ability_key'] === $abilityKey && $card['domain'] === $domainKey
                            );
                            $canSelect = count($character->selected_domain_cards) < $character->getMaxDomainCards() || $isSelected;
                        @endphp
                        
                        @php
                            // Map domain to CSS color variables
                            $domainColors = [
                                'valor' => '#e2680e',
                                'splendor' => '#b8a342', 
                                'sage' => '#244e30',
                                'midnight' => '#1e201f',
                                'grace' => '#8d3965',
                                'codex' => '#24395d',
                                'bone' => '#a4a9a8',
                                'blade' => '#af231c',
                                'arcana' => '#4e345b',
                                'dread' => '#1e201f',
                            ];
                            $domainColor = $domainColors[$domainKey] ?? '#24395d';
                        @endphp
                        
                        <div 
                            x-data="{ ability: @js($abilityData) }"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            pest="domain-card-{{ $domainKey }}-{{ $abilityKey }}"
                            @click="toggleDomainCard('{{ $domainKey }}', '{{ $abilityKey }}', ability)"
                            :class="{
                                'relative group cursor-pointer transition-all duration-200 transform hover:scale-[1.02] hover:-translate-y-1 active:scale-[0.98]': true,
                                'bg-slate-900 border-2 rounded-xl overflow-hidden shadow-lg flex flex-col touch-manipulation min-h-[400px]': true,
                                'border-blue-500 ring-4 ring-blue-400/50 shadow-xl shadow-blue-500/25 scale-[1.02] -translate-y-1': isDomainCardSelected('{{ $domainKey }}', '{{ $abilityKey }}'),
                                'border-slate-700 hover:border-slate-600 hover:shadow-xl hover:shadow-blue-300/20': !isDomainCardSelected('{{ $domainKey }}', '{{ $abilityKey }}') && canSelectMoreDomainCards(),
                                'border-slate-800 opacity-60 cursor-not-allowed': !isDomainCardSelected('{{ $domainKey }}', '{{ $abilityKey }}') && !canSelectMoreDomainCards()
                            }"
                        >
                            <!-- Banner Structure -->
                            <div class="relative min-h-[120px] flex flex-col items-center justify-end bg-slate-900 w-full overflow-hidden rounded-t-xl">
                                <!-- Banner Background -->
                                <div class="absolute -top-1 left-[13.5px] z-40">
                                    <img class="h-[120px] w-[75px]" src="/img/empty-banner.webp">
                                    <div class="absolute inset-0 flex flex-col items-center justify-center pb-3 gap-1 pt-0.5">
                                        <!-- Level Badge -->
                                        @if(isset($abilityData['level']))
                                            <div class="text-2xl leading-[22px] font-bold border-2 border-dashed border-transparent pt-1 px-1 rounded-md">
                                                <div class="text-white font-black">{{ $abilityData['level'] }}</div>
                                            </div>
                                        @endif
                                        <!-- Domain Icon -->
                                        <div class="w-9 h-auto aspect-contain">
                                            <x-dynamic-component component="icons.{{ $domainKey }}" class="fill-white size-8" />
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Banner Colored Layers -->
                                <div class="absolute left-[16px] -top-1 h-[120px] w-[71px] z-30" 
                                     style="background: linear-gradient(to top, {{ $domainColor }} 75%, color-mix(in srgb, {{ $domainColor }}, white 30%) 100%); clip-path: polygon(0 0, 11% 1%, 11% 51%, 17% 55%, 18% 0, 82% 0, 83% 56%, 88% 52%, 88% 0, 100% 1%, 100% 58%, 83% 69%, 82% 90%, 72% 90%, 63% 88%, 57% 85%, 49% 82%, 43% 85%, 34% 88%, 25% 90%, 18% 90%, 17% 68%, 0 59%);"></div>
                                
                                <!-- Banner Sparkle Overlay -->
                                <div class="absolute left-[16px] -top-1 h-[120px] w-[71px] z-35 pointer-events-none" 
                                     style="background-image: url('data:image/svg+xml,%3Csvg%20width%3D\'20\'%20height%3D\'20\'%20viewBox%3D\'0%200%2020%2020\'%20fill%3D\'none\'%20xmlns%3D\'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg\'%3E%3Ccircle%20cx%3D\'7.32289569642708\'%20cy%3D\'10.393732452875549\'%20r%3D\'0.1835629390800243\'%20fill%3D\'gold\'%20fill-opacity%3D\'0.4418777326489399\'%20%2F%3E%3Ccircle%20cx%3D\'18.294652949617227\'%20cy%3D\'6.495357930520824\'%20r%3D\'0.15887393824069335\'%20fill%3D\'white\'%20fill-opacity%3D\'0.5632516711972856\'%20%2F%3E%3C%2Fsvg%3E'); background-repeat: repeat; background-size: 40px 40px; mix-blend-mode: screen; opacity: 0.25; clip-path: polygon(0 0, 100% 0, 85% 85%, 15% 85%);"></div>
                                
                                <!-- Banner Background Color -->
                                <div class="absolute left-[16px] -top-1 h-[120px] w-[71px] z-20" style="background: {{ $domainColor }}; clip-path: polygon(0 0, 11% 1%, 11% 51%, 17% 55%, 18% 0, 82% 0, 83% 56%, 88% 52%, 88% 0, 100% 1%, 100% 58%, 83% 69%, 82% 90%, 72% 90%, 63% 88%, 57% 85%, 49% 82%, 43% 85%, 34% 88%, 25% 90%, 18% 90%, 17% 68%, 0 59%);"></div>
                                
                                <!-- Recall Cost Badge (top right) -->
                                @if(isset($abilityData['recallCost']))
                                    <div class="absolute top-4 right-4 aspect-square rounded-full w-9.5 h-9.5 p-0 border-2 border-yellow-400 bg-gray-900 z-40">
                                        <div class="flex gap-0.5 items-center justify-center absolute inset-0 font-bold border-2 border-gray-500 rounded-full">
                                            <div class="pl-1 text-white">{{ $abilityData['recallCost'] }}</div>
                                            <x-icons.bolt />
                                        </div>
                                    </div>
                                @endif

                                <!-- Card Title -->
                                <div class="w-full pl-[100px] pr-3">
                                    <h5 class="text-white font-black font-outfit text-xl leading-tight uppercase">
                                        {{ $abilityData['name'] ?? ucwords(str_replace('-', ' ', $abilityKey)) }}
                                    </h5>
                                    <div class="text-xs font-bold uppercase tracking-wide mt-1" style="color: {{ $domainColor }}">
                                        {{ $abilityData['type'] ?? 'ability' }}
                                        <span class="ml-2 px-1.5 py-0.5 text-[10px] rounded bg-slate-600/60 text-slate-200">Lvl {{ $abilityData['level'] ?? 1 }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Content -->
                            <div class="flex flex-col relative px-4 py-4 z-40 text-sm text-white flex-1">
                                <!-- Full Ability Text -->
                                <div class="flex-1 text-white text-sm leading-relaxed">
                                    @if(isset($abilityData['descriptions']) && is_array($abilityData['descriptions']))
                                        @foreach($abilityData['descriptions'] as $description)
                                            <p class="mb-3">{{ $description }}</p>
                                        @endforeach
                                    @elseif(isset($abilityData['description']))
                                        <p>{{ $abilityData['description'] }}</p>
                                    @endif
                                </div>
                                
                                <!-- Domain Label - Pinned to bottom -->
                                <div class="mt-auto pt-4 text-center">
                                    <span class="inline-flex items-center px-2 py-1 text-[10px] font-bold uppercase tracking-widest rounded-md" style="background-color: {{ $domainColor }}; color: white;">
                                        {{ ucfirst($domainKey) }} Domain
                                    </span>
                                </div>
                            </div>

                            <template x-if="!isDomainCardSelected('{{ $domainKey }}', '{{ $abilityKey }}') && !canSelectMoreDomainCards()">
                                <div class="absolute inset-0 bg-slate-900/90 rounded-xl flex items-center justify-center z-50">
                                    <span class="text-white text-sm font-bold bg-slate-800 px-4 py-2 rounded-lg shadow-lg border border-slate-600">Maximum cards selected</span>
                                </div>
                            </template>

                            <!-- Selection indicator - moved to bottom right -->
                            <template x-if="isDomainCardSelected('{{ $domainKey }}', '{{ $abilityKey }}')">
                                <div class="absolute bottom-4 right-4 z-50">
                                    <div class="bg-green-500 rounded-full p-1.5 shadow-sm">
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414 1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                            </template>
                        </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
        @endif
        <!-- End level 1 flat view -->
    @else
        <div class="text-center py-12 bg-slate-800/30 rounded-xl border border-slate-700/50">
            <div class="text-slate-400">
                <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <p class="text-lg font-medium">No domains available</p>
                <p class="text-sm mt-1">Please complete the previous steps first.</p>
            </div>
        </div>
    @endif
</div>

<!-- Domain card selection now handled by main character-builder.js component -->
