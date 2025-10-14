<div {{ $attributes->merge(['class' => 'space-y-4']) }}>
    @if($variant === 'compact')
        {{-- Compact Variant - Single Progress Bar --}}
        <div class="bg-slate-900/50 border border-slate-700 rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-semibold text-slate-300">
                    Level {{ $currentLevel }} of {{ $startingLevel }}
                </span>
                <span class="text-xs text-slate-400">
                    {{ $completedLevels }} / {{ $totalLevels }} complete
                </span>
            </div>
            <div class="w-full bg-slate-800 rounded-full h-2.5">
                <div 
                    class="bg-gradient-to-r from-amber-500 to-orange-500 h-2.5 rounded-full transition-all duration-500"
                    style="width: {{ $progressPercentage }}%"
                ></div>
            </div>
        </div>
    @else
        {{-- Default Variant - Full Step-by-Step Progress --}}
        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700 rounded-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-outfit font-bold text-amber-400">
                        Character Advancement
                    </h3>
                    <p class="text-sm text-slate-400 mt-1">
                        Progressing from Level 1 to {{ $startingLevel }}
                    </p>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-bold text-amber-400">
                        {{ round($progressPercentage) }}%
                    </div>
                    <div class="text-xs text-slate-400">Complete</div>
                </div>
            </div>

            {{-- Progress Bar --}}
            <div class="mb-6">
                <div class="w-full bg-slate-800 rounded-full h-3 relative overflow-hidden">
                    <div 
                        class="bg-gradient-to-r from-amber-500 to-orange-500 h-3 rounded-full transition-all duration-500 relative"
                        style="width: {{ $progressPercentage }}%"
                    >
                        <div class="absolute inset-0 bg-white/20 animate-pulse"></div>
                    </div>
                </div>
                <div class="flex justify-between mt-2 text-xs text-slate-400">
                    <span>Level 1</span>
                    <span>Level {{ $startingLevel }}</span>
                </div>
            </div>

            {{-- Level-by-Level Progress --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                @foreach($levelsToComplete as $level)
                    <div class="relative">
                        <div @class([
                            'relative p-3 rounded-lg border-2 transition-all duration-200',
                            'bg-gradient-to-br from-emerald-500/20 to-emerald-600/10 border-emerald-400' => $isLevelCompleted($level),
                            'bg-gradient-to-br from-amber-500/20 to-orange-500/10 border-amber-400 ring-2 ring-amber-400/50 animate-pulse' => $isLevelCurrent($level),
                            'bg-slate-800/50 border-slate-700' => $isLevelPending($level),
                        ])>
                            {{-- Checkmark for Completed --}}
                            @if($isLevelCompleted($level))
                                <div class="absolute -top-2 -right-2 bg-emerald-500 rounded-full p-1">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            @endif

                            {{-- Level Number --}}
                            <div class="text-center">
                                <div @class([
                                    'text-2xl font-bold',
                                    'text-emerald-300' => $isLevelCompleted($level),
                                    'text-amber-400' => $isLevelCurrent($level),
                                    'text-slate-500' => $isLevelPending($level),
                                ])>
                                    {{ $level }}
                                </div>
                                <div class="text-[10px] uppercase tracking-wider mt-1" :class="{
                                    'text-emerald-400': {{ $isLevelCompleted($level) ? 'true' : 'false' }},
                                    'text-amber-400': {{ $isLevelCurrent($level) ? 'true' : 'false' }},
                                    'text-slate-500': {{ $isLevelPending($level) ? 'true' : 'false' }}
                                }">
                                    @if($isLevelCompleted($level))
                                        Complete
                                    @elseif($isLevelCurrent($level))
                                        Current
                                    @else
                                        Tier {{ $getTier($level) }}
                                    @endif
                                </div>
                            </div>

                            {{-- Tier Achievement Badge --}}
                            @if($isTierLevel($level))
                                <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 translate-y-1/2">
                                    <div class="bg-indigo-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full border border-indigo-300">
                                        TIER {{ $getTier($level) }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Summary Stats --}}
            <div class="mt-6 pt-6 border-t border-slate-700 grid grid-cols-3 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-emerald-400">{{ $completedLevels }}</div>
                    <div class="text-xs text-slate-400 uppercase tracking-wide">Completed</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-amber-400">{{ $currentLevel > $startingLevel ? 0 : 1 }}</div>
                    <div class="text-xs text-slate-400 uppercase tracking-wide">In Progress</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-slate-500">{{ max(0, $startingLevel - $currentLevel) }}</div>
                    <div class="text-xs text-slate-400 uppercase tracking-wide">Remaining</div>
                </div>
            </div>
        </div>
    @endif
</div>

