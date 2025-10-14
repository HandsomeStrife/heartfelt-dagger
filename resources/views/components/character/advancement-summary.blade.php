@props([
    'advancements' => [], // creation_advancements array (level => advancements)
    'tierExperiences' => [], // creation_tier_experiences array (level => experience)
    'domainCards' => [], // creation_domain_cards array (level => card_key)
    'startingLevel' => 1,
    'variant' => 'default', // 'default', 'compact', 'detailed'
])

<div {{ $attributes->merge(['class' => 'space-y-4']) }}>
    @if($startingLevel === 1)
        {{-- No advancements for level 1 characters --}}
        <div class="bg-slate-900/50 border border-slate-700 rounded-lg p-6 text-center">
            <p class="text-slate-400">
                <span class="text-lg">🎯</span> Starting at level 1 - no advancements yet!
            </p>
            <p class="text-sm text-slate-500 mt-2">
                Level up your character to gain powerful advancements and abilities.
            </p>
        </div>
    @else
        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700 rounded-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-outfit font-bold text-amber-400">
                    Advancement Summary
                </h3>
                <span class="text-sm text-slate-400">
                    Levels 1 → {{ $startingLevel }}
                </span>
            </div>

            <div class="space-y-6">
                @for($level = 2; $level <= $startingLevel; $level++)
                    <div class="border-l-4 border-amber-400/30 pl-4 space-y-3">
                        {{-- Level Header --}}
                        <div class="flex items-center gap-3">
                            <div class="bg-amber-500/10 border border-amber-400/30 rounded-lg px-3 py-1">
                                <span class="text-amber-400 font-bold">Level {{ $level }}</span>
                            </div>
                            <span class="text-xs text-slate-400">Tier {{ $getTier($level) }}</span>
                        </div>

                        {{-- Tier Achievement Experience (levels 2, 5, 8) --}}
                        @if(in_array($level, [2, 5, 8]) && isset($tierExperiences[$level]))
                            <div class="bg-indigo-500/10 border border-indigo-400/30 rounded-lg p-3">
                                <div class="flex items-center gap-2 mb-1">
                                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                    </svg>
                                    <span class="text-sm font-semibold text-indigo-300">Tier Achievement</span>
                                </div>
                                <p class="text-sm text-slate-300 ml-6">
                                    <span class="font-medium text-indigo-400">{{ $tierExperiences[$level]['name'] ?? 'Experience' }}</span>
                                    @if(!empty($tierExperiences[$level]['description']))
                                        <span class="text-slate-400"> - {{ $tierExperiences[$level]['description'] }}</span>
                                    @endif
                                </p>
                                <div class="text-xs text-slate-500 ml-6 mt-1">
                                    +2 bonus when relevant, proficiency increased, traits unmarked
                                </div>
                            </div>
                        @endif

                        {{-- Domain Card Selection --}}
                        @if(isset($domainCards[$level]))
                            <div class="bg-purple-500/10 border border-purple-400/30 rounded-lg p-3">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                                    </svg>
                                    <span class="text-sm font-semibold text-purple-300">Domain Card</span>
                                    <span class="text-xs text-slate-400 capitalize">{{ str_replace('-', ' ', $domainCards[$level]) }}</span>
                                </div>
                            </div>
                        @endif

                        {{-- Advancements (2 per level for levels 2+) --}}
                        @if(isset($advancements[$level]) && is_array($advancements[$level]))
                            <div class="space-y-2">
                                @foreach($advancements[$level] as $index => $advancement)
                                    @php
                                        $type = $advancement['type'] ?? 'unknown';
                                    @endphp
                                    <div class="bg-slate-800/50 border border-slate-600/30 rounded-lg p-2 pl-3">
                                        <div class="flex items-center gap-2">
                                            <span class="text-lg">{{ $getTypeIcon($type) }}</span>
                                            <span class="text-sm font-medium text-slate-200">{{ $getTypeLabel($type) }}</span>
                                            
                                            @if($type === 'trait_bonus' && isset($advancement['traits']))
                                                <span class="text-xs text-slate-400">
                                                    ({{ implode(', ', array_map('ucfirst', $advancement['traits'])) }})
                                                </span>
                                            @endif
                                            
                                            @if($type === 'multiclass' && isset($advancement['selection']['class']))
                                                <span class="text-xs text-slate-400">
                                                    ({{ ucfirst($advancement['selection']['class']) }})
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endfor
            </div>

            {{-- Summary Totals --}}
            @php
                $totals = [
                    'tier_achievements' => count(array_filter([2, 5, 8], fn($l) => $l <= $startingLevel && isset($tierExperiences[$l]))),
                    'advancements' => 0,
                    'domain_cards' => count($domainCards ?? []),
                ];
                foreach ($advancements as $levelAdvancements) {
                    $totals['advancements'] += count($levelAdvancements);
                }
            @endphp
            <div class="mt-6 pt-6 border-t border-slate-700">
                <div class="flex flex-wrap gap-4 justify-center">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-amber-400">{{ $totals['advancements'] }}</div>
                        <div class="text-xs text-slate-400 uppercase tracking-wide">Advancements</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-indigo-400">{{ $totals['tier_achievements'] }}</div>
                        <div class="text-xs text-slate-400 uppercase tracking-wide">Tier Achievements</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-400">{{ $totals['domain_cards'] }}</div>
                        <div class="text-xs text-slate-400 uppercase tracking-wide">Domain Cards</div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

