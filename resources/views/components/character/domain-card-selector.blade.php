{{-- Domain card selector component - now uses component class for logic --}}

<div 
    x-data="{
        selectedCard: @js($selectedCard),
        level: @js($level),
        expandedDomain: null,
        
        selectCard(cardKey) {
            this.selectedCard = cardKey;
            this.$dispatch('domain-card-selected', {
                level: this.level,
                cardKey: cardKey
            });
        },
        
        isSelected(cardKey) {
            return this.selectedCard === cardKey;
        },
        
        toggleDomain(domain) {
            this.expandedDomain = this.expandedDomain === domain ? null : domain;
        },
        
        isDomainExpanded(domain) {
            return this.expandedDomain === domain || this.expandedDomain === 'all';
        },
        
        expandAll() {
            this.expandedDomain = 'all';
        },
        
        collapseAll() {
            this.expandedDomain = null;
        }
    }"
    @domain-card-reset.window="selectedCard = null"
    class="space-y-6"
>
    <!-- Header with Selection Status -->
    <div class="flex items-center justify-between p-4 rounded-lg bg-slate-800/50 border border-slate-700">
        <div>
            <h4 class="text-lg font-outfit font-bold text-white">
                Select a Domain Card
            </h4>
            <p class="text-sm text-slate-400 mt-1">
                For Level <span x-text="level"></span> • Choose from your accessible domains
            </p>
        </div>
        
        <div class="text-right">
            <div x-show="selectedCard" class="text-green-400 flex items-center space-x-2">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm font-bold">Card Selected</span>
            </div>
            <div x-show="!selectedCard" class="text-sm text-slate-400">
                No card selected
            </div>
        </div>
    </div>

    <!-- View Controls -->
    @if($groupByDomain && count($groupedCards) > 1)
        <div class="flex items-center justify-between">
            <div class="text-sm text-slate-400">
                <span x-text="Object.keys(@js($groupedCards)).length"></span> Domain<span x-show="Object.keys(@js($groupedCards)).length > 1">s</span> Available
            </div>
            <div class="flex items-center space-x-2">
                <button 
                    type="button"
                    @click="expandAll()"
                    class="px-3 py-1.5 text-xs font-semibold bg-slate-700 text-slate-300 rounded hover:bg-slate-600 transition-colors"
                >
                    Expand All
                </button>
                <button 
                    type="button"
                    @click="collapseAll()"
                    class="px-3 py-1.5 text-xs font-semibold bg-slate-700 text-slate-300 rounded hover:bg-slate-600 transition-colors"
                >
                    Collapse All
                </button>
            </div>
        </div>
    @endif

    <!-- Domain Groups -->
    @if(count($groupedCards) > 0)
        <div class="space-y-4">
            @foreach($groupedCards as $domain => $domainCards)
                <div class="space-y-3">
                    <!-- Domain Header (if grouped) -->
                    @if($domain !== 'ungrouped')
                        <button
                            type="button"
                            @click="toggleDomain('{{ $domain }}')"
                            class="w-full flex items-center justify-between p-4 rounded-lg border-2 transition-all duration-200"
                            style="background: linear-gradient(135deg, {{ $getDomainColor($domain) }}20, {{ $getDomainColor($domain) }}05); border-color: {{ $getDomainColor($domain) }}60;"
                            aria-label="Toggle {{ $getDomainDisplayName($domain) }} domain cards"
                            aria-expanded="true"
                        >
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                                     style="background: {{ $getDomainColor($domain) }};"
                                     aria-hidden="true">
                                    <x-dynamic-component component="icons.{{ $domain }}" class="fill-white size-6" />
                                </div>
                                <div class="text-left">
                                    <h5 class="text-white font-outfit font-bold text-lg">{{ $getDomainDisplayName($domain) }} Domain</h5>
                                    <p class="text-sm text-slate-400">{{ count($domainCards) }} card{{ count($domainCards) !== 1 ? 's' : '' }} available</p>
                                </div>
                            </div>
                            <svg 
                                class="w-6 h-6 text-white transition-transform duration-200"
                                x-bind:class="isDomainExpanded('{{ $domain }}') ? 'rotate-180' : ''"
                                fill="none" 
                                stroke="currentColor" 
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    @endif

                    <!-- Domain Cards Grid -->
                    <div 
                        x-show="isDomainExpanded('{{ $domain }}')" 
                        x-collapse
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
                    >
                        @foreach($domainCards as $card)
                            <div
                                @click="selectCard('{{ $card['key'] }}')"
                                x-bind:class="{
                                    'ring-4 ring-amber-400 scale-105': isSelected('{{ $card['key'] }}'),
                                    'hover:scale-102 cursor-pointer': !isSelected('{{ $card['key'] }}')
                                }"
                                class="relative bg-slate-900 rounded-xl border-2 border-slate-700 transition-all duration-200 overflow-hidden"
                                role="button"
                                tabindex="0"
                                x-bind:aria-pressed="isSelected('{{ $card['key'] }}')"
                            >
                                <!-- Selection Indicator -->
                                <div 
                                    x-show="isSelected('{{ $card['key'] }}')"
                                    class="absolute top-2 right-2 z-50 w-8 h-8 rounded-full bg-amber-400 flex items-center justify-center shadow-lg"
                                >
                                    <svg class="w-5 h-5 text-slate-900" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>

                                <!-- Domain Card Component -->
                                <x-character-level-up.domain-card :card="$card" />
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- No Cards Available -->
        <div class="text-center py-12 px-4">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-800 mb-4">
                <svg class="w-8 h-8 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                </svg>
            </div>
            <h4 class="text-lg font-outfit font-bold text-white mb-2">No Domain Cards Available</h4>
            <p class="text-slate-400 max-w-md mx-auto">
                Unable to load domain cards for your character's accessible domains at this level. 
                This may be due to level restrictions or missing game data.
            </p>
        </div>
    @endif

    <!-- SRD Help Text -->
    <div class="p-4 rounded-lg bg-blue-500/10 border border-blue-500/30">
        <div class="flex items-start space-x-3">
            <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-sm text-blue-300 space-y-1">
                <p><strong>Per DaggerHeart SRD Step Four:</strong></p>
                <p class="text-blue-200/80">"Acquire a new domain card at your level or lower from one of your class's domains"</p>
                <ul class="list-disc list-inside space-y-1 text-blue-200/80 mt-2">
                    <li>You must select <strong>exactly 1 domain card</strong> for every level</li>
                    <li>Cards must be from your <strong>class domains</strong> (or multiclass domains if applicable)</li>
                    <li>Card level must be <strong>at or below your character level</strong></li>
                    <li>Multiclass domain cards are restricted to <strong>half your character level</strong></li>
                </ul>
            </div>
        </div>
    </div>
</div>

