<div class="space-y-3">
    @if($domain_info)
        <div class="bg-slate-700/30 rounded-lg p-3">
            <h5 class="text-white font-medium text-sm mb-2">{{ $domain_info['name'] ?? ucfirst($domain_key) }} Domain</h5>
            @if(isset($domain_info['description']))
                <div class="text-slate-300 text-xs mb-2">
                    {{ Str::limit($domain_info['description'], 100) }}
                </div>
            @endif
        </div>
    @endif

    <div class="space-y-2">
        <h6 class="text-slate-400 text-xs uppercase tracking-wider">Abilities ({{ count($abilities) }})</h6>
        
        @foreach(array_slice($abilities, 0, 3) as $abilityKey => $abilityData)
            <div class="bg-slate-700/30 rounded-lg p-3">
                <div class="flex items-center justify-between mb-2">
                    <h5 class="text-white font-medium text-sm">{{ $abilityData['name'] ?? $abilityKey }}</h5>
                    @if(isset($abilityData['level']))
                        <span class="px-2 py-1 bg-amber-500/20 text-amber-300 text-xs rounded">
                            Level {{ $abilityData['level'] }}
                        </span>
                    @endif
                </div>
                
                @if(isset($abilityData['type']))
                    <div class="text-slate-400 text-xs mb-1">{{ $abilityData['type'] }}</div>
                @endif
                
                @if(isset($abilityData['recallCost']) && $abilityData['recallCost'] > 0)
                    <div class="text-amber-400 text-xs mb-2">
                        Recall Cost: {{ $abilityData['recallCost'] }} Hope
                    </div>
                @endif
                
                @if(isset($abilityData['descriptions']) && is_array($abilityData['descriptions']))
                    <div class="text-slate-300 text-xs">
                        {{ Str::limit($abilityData['descriptions'][0] ?? '', 80) }}
                    </div>
                @elseif(isset($abilityData['description']))
                    <div class="text-slate-300 text-xs">
                        {{ Str::limit($abilityData['description'], 80) }}
                    </div>
                @endif
            </div>
        @endforeach
        
        @if(count($abilities) > 3)
            <div class="text-xs text-slate-400 text-center">
                and {{ count($abilities) - 3 }} more abilities...
            </div>
        @endif
    </div>
    
    <div class="pt-2 border-t border-slate-700/50">
        <a href="{{ route('reference.page', $domain_key . '-abilities') }}" 
           target="_blank"
           class="inline-flex items-center text-amber-400 hover:text-amber-300 transition-colors text-xs">
            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
            View All {{ ucfirst($domain_key) }} Abilities
        </a>
    </div>
</div>
