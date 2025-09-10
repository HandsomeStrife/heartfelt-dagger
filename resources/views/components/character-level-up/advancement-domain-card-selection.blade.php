@props(['index', 'option', 'selected', 'character'])

{{-- Domain Card Selection for Advancement --}}
@if(str_contains(strtolower($option['description'] ?? ''), 'domain card'))
<div class="mt-6 border-t border-slate-600 pt-6" 
     x-show="{{ $selected }} === {{ $index }}"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100">
    <h5 class="text-slate-200 font-medium mb-3">Select Domain Card</h5>
    <p class="text-slate-400 text-sm mb-4">Choose a domain card from your available domains.</p>
    
    @php
        $maxLevel = match($character->current_tier ?? 2) {
            2 => 4,
            3 => 7,
            4 => 10,
            default => 4,
        };
        $availableCards = $character ? (new \App\Livewire\CharacterLevelUp())->getAvailableDomainCards($maxLevel) : [];
        $cardsByDomain = collect($availableCards)->groupBy('domain');
    @endphp
    
    <div class="space-y-4 max-h-96 overflow-y-auto">
        @foreach($cardsByDomain as $domain => $cards)
        <div>
            <h6 class="text-amber-400 font-medium mb-2 capitalize">{{ $domain }} Domain</h6>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($cards as $card)
                <div @click="$wire.selectDomainCard({{ $index }}, '{{ $card['key'] }}')"
                     class="relative group cursor-pointer transition-all duration-200 transform hover:scale-[1.02] hover:-translate-y-1 active:scale-[0.98] bg-slate-900 border-2 border-slate-700 hover:border-slate-600 hover:shadow-xl hover:shadow-blue-300/20 rounded-xl overflow-hidden shadow-lg flex flex-col min-h-[400px] max-w-[360px] mx-auto"
                     :class="advancementChoices[{{ $index }}]?.domain_card === '{{ $card['key'] }}' ? 'border-amber-500 bg-amber-500/10' : ''">
                    
                    <x-character-level-up.domain-card :card="$card" />
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
