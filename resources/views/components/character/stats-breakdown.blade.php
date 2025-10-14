@props([
    'stats' => [],
    'showDetails' => true,
    'variant' => 'default', // 'default', 'compact', 'card'
])

<div {{ $attributes->merge(['class' => 'space-y-4']) }}>
    @if($variant === 'card')
        {{-- Card Variant - Beautiful card-based display --}}
        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700 rounded-lg p-6">
            <h3 class="text-xl font-outfit font-bold text-amber-400 mb-4">Character Stats</h3>
            <x-character.stats-breakdown :stats="$stats" variant="default" :show-details="$showDetails" />
        </div>
    @elseif($variant === 'compact')
        {{-- Compact Variant - Single line with key stats --}}
        <div class="flex flex-wrap gap-4">
            @if(isset($stats['evasion']))
                <div class="flex items-center gap-2">
                    <span class="text-sm text-slate-400">Evasion:</span>
                    <span class="text-lg font-bold text-amber-400">{{ $stats['evasion'] }}</span>
                </div>
            @endif
            @if(isset($stats['hit_points']))
                <div class="flex items-center gap-2">
                    <span class="text-sm text-slate-400">HP:</span>
                    <span class="text-lg font-bold text-emerald-400">{{ $stats['hit_points'] }}</span>
                </div>
            @endif
            @if(isset($stats['armor_score']))
                <div class="flex items-center gap-2">
                    <span class="text-sm text-slate-400">Armor:</span>
                    <span class="text-lg font-bold text-blue-400">{{ $stats['armor_score'] }}</span>
                </div>
            @endif
            @if(isset($stats['damage_threshold']))
                <div class="flex items-center gap-2">
                    <span class="text-sm text-slate-400">Threshold:</span>
                    <span class="text-lg font-bold text-purple-400">{{ $stats['damage_threshold'] }}</span>
                </div>
            @endif
        </div>
    @else
        {{-- Default Variant - Full breakdown with expandable details --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4" 
             x-data="{ 
                 expanded: {},
                 toggle(stat) {
                     this.expanded[stat] = !this.expanded[stat];
                 }
             }">
            
            {{-- Evasion Stat --}}
            @if(isset($stats['evasion']))
                <div class="bg-slate-900/50 border border-slate-700 rounded-lg p-4 hover:border-amber-400/50 transition-all duration-200">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-slate-300 uppercase tracking-wide">Evasion</span>
                        <span class="text-2xl font-bold text-amber-400">{{ $stats['evasion'] }}</span>
                    </div>
                    
                    @if($showDetails && isset($stats['breakdown']['evasion']))
                        <button 
                            type="button"
                            @click="toggle('evasion')"
                            class="text-xs text-slate-400 hover:text-amber-400 transition-colors flex items-center gap-1"
                            aria-label="Toggle evasion breakdown details"
                        >
                            <span x-show="!expanded.evasion">Show Details</span>
                            <span x-show="expanded.evasion" x-cloak>Hide Details</span>
                            <svg class="w-3 h-3 transition-transform" :class="expanded.evasion ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        
                        <div x-show="expanded.evasion" x-cloak class="mt-3 space-y-1 text-sm">
                            @foreach($stats['breakdown']['evasion'] as $source => $value)
                                @if($value != 0)
                                    <div class="flex justify-between items-center">
                                        <span class="text-slate-400 capitalize">{{ str_replace('_', ' ', $source) }}:</span>
                                        <span class="font-mono text-slate-300">{{ $value > 0 ? '+' : '' }}{{ $value }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            {{-- Hit Points Stat --}}
            @if(isset($stats['hit_points']))
                <div class="bg-slate-900/50 border border-slate-700 rounded-lg p-4 hover:border-emerald-400/50 transition-all duration-200">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-slate-300 uppercase tracking-wide">Hit Points</span>
                        <span class="text-2xl font-bold text-emerald-400">{{ $stats['hit_points'] }}</span>
                    </div>
                    
                    @if($showDetails && isset($stats['breakdown']['hit_points']))
                        <button 
                            type="button"
                            @click="toggle('hit_points')"
                            class="text-xs text-slate-400 hover:text-emerald-400 transition-colors flex items-center gap-1"
                            aria-label="Toggle hit points breakdown details"
                        >
                            <span x-show="!expanded.hit_points">Show Details</span>
                            <span x-show="expanded.hit_points" x-cloak>Hide Details</span>
                            <svg class="w-3 h-3 transition-transform" :class="expanded.hit_points ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        
                        <div x-show="expanded.hit_points" x-cloak class="mt-3 space-y-1 text-sm">
                            @foreach($stats['breakdown']['hit_points'] as $source => $value)
                                @if($value != 0)
                                    <div class="flex justify-between items-center">
                                        <span class="text-slate-400 capitalize">{{ str_replace('_', ' ', $source) }}:</span>
                                        <span class="font-mono text-slate-300">{{ $value > 0 ? '+' : '' }}{{ $value }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            {{-- Armor Score Stat --}}
            @if(isset($stats['armor_score']))
                <div class="bg-slate-900/50 border border-slate-700 rounded-lg p-4 hover:border-blue-400/50 transition-all duration-200">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-slate-300 uppercase tracking-wide">Armor Score</span>
                        <span class="text-2xl font-bold text-blue-400">{{ $stats['armor_score'] }}</span>
                    </div>
                    
                    @if($showDetails && isset($stats['breakdown']['armor_score']))
                        <div class="mt-2 text-xs text-slate-400">
                            From equipped armor
                        </div>
                    @endif
                </div>
            @endif

            {{-- Damage Threshold Stat --}}
            @if(isset($stats['damage_threshold']))
                <div class="bg-slate-900/50 border border-slate-700 rounded-lg p-4 hover:border-purple-400/50 transition-all duration-200">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-slate-300 uppercase tracking-wide">Damage Threshold</span>
                        <span class="text-2xl font-bold text-purple-400">{{ $stats['damage_threshold'] }}</span>
                    </div>
                    
                    @if($showDetails && isset($stats['breakdown']['damage_threshold']))
                        <button 
                            type="button"
                            @click="toggle('damage_threshold')"
                            class="text-xs text-slate-400 hover:text-purple-400 transition-colors flex items-center gap-1"
                            aria-label="Toggle damage threshold breakdown details"
                        >
                            <span x-show="!expanded.damage_threshold">Show Details</span>
                            <span x-show="expanded.damage_threshold" x-cloak>Hide Details</span>
                            <svg class="w-3 h-3 transition-transform" :class="expanded.damage_threshold ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        
                        <div x-show="expanded.damage_threshold" x-cloak class="mt-3 space-y-1 text-sm">
                            @foreach($stats['breakdown']['damage_threshold'] as $source => $value)
                                @if($value != 0)
                                    <div class="flex justify-between items-center">
                                        <span class="text-slate-400 capitalize">{{ str_replace('_', ' ', $source) }}:</span>
                                        <span class="font-mono text-slate-300">{{ $value > 0 ? '+' : '' }}{{ $value }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            {{-- Stress Slots Stat --}}
            @if(isset($stats['stress_slots']))
                <div class="bg-slate-900/50 border border-slate-700 rounded-lg p-4 hover:border-red-400/50 transition-all duration-200">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-slate-300 uppercase tracking-wide">Stress Slots</span>
                        <span class="text-2xl font-bold text-red-400">{{ $stats['stress_slots'] }}</span>
                    </div>
                    
                    @if($showDetails && isset($stats['breakdown']['stress_slots']))
                        <button 
                            type="button"
                            @click="toggle('stress_slots')"
                            class="text-xs text-slate-400 hover:text-red-400 transition-colors flex items-center gap-1"
                            aria-label="Toggle stress slots breakdown details"
                        >
                            <span x-show="!expanded.stress_slots">Show Details</span>
                            <span x-show="expanded.stress_slots" x-cloak>Hide Details</span>
                            <svg class="w-3 h-3 transition-transform" :class="expanded.stress_slots ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        
                        <div x-show="expanded.stress_slots" x-cloak class="mt-3 space-y-1 text-sm">
                            @foreach($stats['breakdown']['stress_slots'] as $source => $value)
                                @if($value != 0)
                                    <div class="flex justify-between items-center">
                                        <span class="text-slate-400 capitalize">{{ str_replace('_', ' ', $source) }}:</span>
                                        <span class="font-mono text-slate-300">{{ $value > 0 ? '+' : '' }}{{ $value }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            {{-- Proficiency Stat --}}
            @if(isset($stats['proficiency']))
                <div class="bg-slate-900/50 border border-slate-700 rounded-lg p-4 hover:border-indigo-400/50 transition-all duration-200 md:col-span-2">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-slate-300 uppercase tracking-wide">Proficiency</span>
                        <span class="text-2xl font-bold text-indigo-400">{{ $stats['proficiency']['total'] ?? 1 }}</span>
                    </div>
                    
                    @if($showDetails && isset($stats['proficiency']))
                        <div class="mt-2 space-y-1 text-sm">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-400">Base Proficiency:</span>
                                <span class="font-mono text-slate-300">{{ $stats['proficiency']['base_proficiency'] ?? 1 }}</span>
                            </div>
                            @if(isset($stats['proficiency']['level_proficiency']) && $stats['proficiency']['level_proficiency'] > 0)
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-400">From Tier Achievements:</span>
                                    <span class="font-mono text-slate-300">+{{ $stats['proficiency']['level_proficiency'] }}</span>
                                </div>
                            @endif
                            @if(isset($stats['proficiency']['advancement_proficiency']) && $stats['proficiency']['advancement_proficiency'] > 0)
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-400">From Advancements:</span>
                                    <span class="font-mono text-slate-300">+{{ $stats['proficiency']['advancement_proficiency'] }}</span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @endif
</div>


