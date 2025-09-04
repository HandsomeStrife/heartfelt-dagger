<!-- Domain Card Selection Step -->
<div class="space-y-6 sm:space-y-8">
    <!-- Step Header -->
    <div class="mb-6 sm:mb-8">
        <div class="flex flex-col gap-3 sm:gap-4 mb-4">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-white mb-2 font-outfit">Select Domain Cards</h2>
                <p class="text-slate-300 font-roboto text-sm sm:text-base">Choose {{ $character->getMaxDomainCards() }} starting domain card{{ $character->getMaxDomainCards() !== 1 ? 's' : '' }} from your class domains to represent your character's initial magical abilities.
                @if($character->getMaxDomainCards() > 2)
                    <span class="text-purple-300"> (includes {{ $character->getMaxDomainCards() - 2 }} bonus card{{ $character->getMaxDomainCards() - 2 !== 1 ? 's' : '' }} from {{ ucfirst($character->selected_subclass) }})</span>
                @endif
                </p>
            </div>
            
            <!-- Level Filter Toggle -->
            <div class="flex flex-col">
                <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3">
                    <span class="text-sm text-slate-400">Showing level 1 cards only</span>
                    <div class="text-xs text-slate-500">Higher level cards will be available as you advance</div>
                </div>
                <p class="text-xs text-slate-500 mt-1">Only level 1 cards available for character creation</p>
            </div>
        </div>
    </div>

    <!-- Step Completion Indicator (JS-first) -->
    <template x-if="selected_domain_cards.length >= maxDomainCards">
        <div class="my-6 p-4 bg-gradient-to-r from-emerald-500/10 to-green-500/10 border border-emerald-500/20 rounded-xl">
            <div class="flex items-center">
                <div class="bg-emerald-500 rounded-full p-2 mr-3">
                    <svg class="w-5 h-5 text-black" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <p class="text-emerald-400 font-semibold">Domain Card Selection Complete!</p>
                    <p class="text-slate-300 text-sm">You have selected <span x-text="selected_domain_cards.length"></span> of <span x-text="maxDomainCards"></span> domain card<span x-text="maxDomainCards !== 1 ? 's' : ''"></span> for your character.</p>
                </div>
            </div>
        </div>
    </template>
    <template x-if="selected_domain_cards.length >= 2 && selected_domain_cards.length < maxDomainCards">
        <div class="my-6 p-4 bg-gradient-to-r from-purple-500/10 to-blue-500/10 border border-purple-500/20 rounded-xl">
            <div class="flex items-center">
                <div class="bg-purple-500 rounded-full p-2 mr-3">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <p class="text-purple-400 font-semibold">Minimum Cards Selected</p>
                    <p class="text-slate-300 text-sm">You have selected <span x-text="selected_domain_cards.length"></span> of <span x-text="maxDomainCards"></span> domain cards. You can select <span x-text="maxDomainCards - selected_domain_cards.length"></span> more bonus card<span x-text="(maxDomainCards - selected_domain_cards.length) !== 1 ? 's' : ''"></span>.</p>
                </div>
            </div>
        </div>
    </template>
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

    <!-- Persistent Selected Count Badge (JS-first, always rendered) -->
    <div class="mt-2 flex justify-end">
        <span class="text-white font-bold bg-slate-700 px-2 py-1 rounded-md">
            <span pest="domain-card-selected-count" x-text="selected_domain_cards.length"></span>/<span x-text="maxDomainCards"></span> selected
        </span>
    </div>

    <!-- Domain Cards (Pre-rendered, visibility controlled by AlpineJS) -->
    @if(!empty($game_data['domains']) && !empty($game_data['abilities']))
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
                    <div class="flex flex-wrap justify-center sm:justify-start gap-4 sm:gap-8">
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
                                'bg-slate-900 border-2 rounded-xl overflow-hidden shadow-lg min-h-[350px] sm:min-h-[400px] w-[260px] sm:w-[280px] flex flex-col touch-manipulation': true,
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
                                <div class="w-full pl-[90px] sm:pl-[100px] pr-2">
                                    <h5 class="text-white font-black font-outfit text-lg sm:text-xl leading-tight uppercase">
                                        {{ $abilityData['name'] ?? ucwords(str_replace('-', ' ', $abilityKey)) }}
                                    </h5>
                                    <div class="text-xs font-bold uppercase tracking-wide mt-1" style="color: {{ $domainColor }}">
                                        {{ $abilityData['type'] ?? 'ability' }}
                                        <span class="ml-2 px-1.5 py-0.5 text-[10px] rounded bg-slate-600/60 text-slate-200">Lvl {{ $abilityData['level'] ?? 1 }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Content -->
                            <div class="flex flex-col relative px-3 sm:px-4 py-3 sm:py-4 z-40 text-sm text-white flex-1">
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
