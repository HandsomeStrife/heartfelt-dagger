@props(['character', 'availableCards'])

<!-- Domain Card Selection Section -->
<div class="bg-slate-800/30 border border-slate-600/50 rounded-lg p-6"
     x-data="{ 
        selectedDomainCard: @entangle('advancement_choices.tier_domain_card').live,
        
        selectCard(cardKey) {
            this.selectedDomainCard = cardKey;
        },
        
        removeCard() {
            this.selectedDomainCard = null;
        }
     }">
    <div class="flex items-center mb-4">
        <div class="flex items-center space-x-2">
            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
            <h4 class="font-semibold text-slate-100 text-lg">Select Your Domain Card</h4>
        </div>
        <div class="ml-auto">
            <template x-if="selectedDomainCard">
                <div class="bg-emerald-500/20 text-emerald-400 px-3 py-1 rounded-full text-sm font-medium">
                    ✓ Selected
                </div>
            </template>
            <template x-if="!selectedDomainCard">
                <div class="bg-amber-500/20 text-amber-400 px-3 py-1 rounded-full text-sm font-medium">
                    Required
                </div>
            </template>
        </div>
    </div>
    <p class="text-slate-400 text-sm mb-4">Choose a domain card at your level or lower from your class domains.</p>
    
    <template x-if="selectedDomainCard">
        <!-- Show Selected Domain Card -->
        <div class="mb-4">
            @foreach($availableCards as $card)
            <div x-show="selectedDomainCard === '{{ $card['key'] }}'" class="bg-slate-900/60 border border-slate-800 rounded-xl shadow-lg overflow-hidden max-w-[360px] flex flex-col">
                <x-character-level-up.domain-card :card="$card" />
            </div>
            @endforeach
            
            <div class="flex justify-end mt-4">
                <button type="button" 
                        @click="removeCard()"
                        class="px-4 py-2 bg-slate-600 hover:bg-slate-500 text-slate-200 rounded-lg transition-colors">
                    Change Selection
                </button>
            </div>
        </div>
    </template>
    
    <template x-if="!selectedDomainCard">
        <!-- Domain Card Selection Interface -->
        @php
            $cardsByDomain = collect($availableCards)->groupBy('domain');
        @endphp
        
        <div class="space-y-6">
            @foreach($cardsByDomain as $domain => $cards)
            <div>
                <h6 class="text-amber-400 font-medium mb-4 capitalize text-lg">{{ $domain }} Domain</h6>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($cards as $card)
                    <div @click="selectCard('{{ $card['key'] }}')"
                         class="relative group cursor-pointer transition-all duration-200 transform hover:scale-[1.02] hover:-translate-y-1 active:scale-[0.98] bg-slate-900 border-2 border-slate-700 hover:border-slate-600 hover:shadow-xl hover:shadow-blue-300/20 rounded-xl overflow-hidden shadow-lg flex flex-col min-h-[400px] max-w-[360px] mx-auto">
                        
                        <x-character-level-up.domain-card :card="$card" />
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </template>
</div>
