@props(['startingLevel' => 1, 'disabled' => false])

<div x-data="{
    selectedLevel: {{ $startingLevel }},
    showLevelInfo: false,
    levelInfo: {
        1: { tier: 1, advancements: 0, experiences: 2, domainCards: 2, description: 'Start your journey' },
        2: { tier: 2, advancements: 2, experiences: 3, domainCards: 3, description: 'First tier achievement' },
        3: { tier: 2, advancements: 4, experiences: 3, domainCards: 4, description: 'Tier 2' },
        4: { tier: 2, advancements: 6, experiences: 3, domainCards: 5, description: 'Tier 2' },
        5: { tier: 3, advancements: 8, experiences: 4, domainCards: 6, description: 'Second tier achievement' },
        6: { tier: 3, advancements: 10, experiences: 4, domainCards: 7, description: 'Tier 3' },
        7: { tier: 3, advancements: 12, experiences: 4, domainCards: 8, description: 'Tier 3' },
        8: { tier: 4, advancements: 14, experiences: 5, domainCards: 9, description: 'Third tier achievement' },
        9: { tier: 4, advancements: 16, experiences: 5, domainCards: 10, description: 'Tier 4' },
        10: { tier: 4, advancements: 18, experiences: 5, domainCards: 11, description: 'Maximum level' }
    },
    selectLevel(level) {
        if (!{{ $disabled ? 'true' : 'false' }}) {
            this.selectedLevel = level;
            @this.selectStartingLevel(level);
        }
    },
    getLevelInfo(level) {
        return this.levelInfo[level] || this.levelInfo[1];
    }
}" class="space-y-4">
    
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xl font-outfit font-bold text-amber-400">Starting Level</h3>
            <p class="text-sm text-slate-400 mt-1">Choose your character's starting level (1-10)</p>
        </div>
        <button 
            type="button"
            @click="showLevelInfo = !showLevelInfo"
            class="text-sm text-amber-400 hover:text-amber-300 transition-colors flex items-center space-x-1"
            x-tooltip="'Toggle level information'">
            <span x-text="showLevelInfo ? 'Hide Info' : 'Show Info'"></span>
            <svg class="w-4 h-4 transition-transform" :class="showLevelInfo ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
    </div>

    <!-- Level Selection Grid -->
    <div class="grid grid-cols-5 sm:grid-cols-10 gap-2">
        <template x-for="level in [1,2,3,4,5,6,7,8,9,10]" :key="level">
            <button 
                type="button"
                @click="selectLevel(level)"
                :disabled="{{ $disabled ? 'true' : 'false' }}"
                class="relative group aspect-square rounded-lg border-2 transition-all duration-200 flex flex-col items-center justify-center"
                :class="{
                    'border-amber-400 bg-amber-400/20 text-amber-400': selectedLevel === level,
                    'border-slate-600 bg-slate-800/50 text-slate-300 hover:border-amber-400/50 hover:bg-slate-800 hover:scale-105': selectedLevel !== level && !{{ $disabled ? 'true' : 'false' }},
                    'border-slate-700 bg-slate-900/30 text-slate-600 cursor-not-allowed': {{ $disabled ? 'true' : 'false' }}
                }">
                <!-- Level Number -->
                <span class="text-2xl font-bold font-outfit" x-text="level"></span>
                
                <!-- Tier Badge -->
                <span class="absolute -top-1 -right-1 w-5 h-5 rounded-full text-xs flex items-center justify-center font-bold"
                      :class="{
                          'bg-blue-500 text-white': getLevelInfo(level).tier === 1,
                          'bg-green-500 text-white': getLevelInfo(level).tier === 2,
                          'bg-purple-500 text-white': getLevelInfo(level).tier === 3,
                          'bg-orange-500 text-white': getLevelInfo(level).tier === 4
                      }"
                      x-text="'T' + getLevelInfo(level).tier">
                </span>
                
                <!-- Selection Indicator -->
                <div x-show="selectedLevel === level" 
                     class="absolute inset-0 rounded-lg border-2 border-amber-400 animate-pulse pointer-events-none">
                </div>
            </button>
        </template>
    </div>

    <!-- Level Information Panel (Expandable) -->
    <div x-show="showLevelInfo" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="bg-slate-900/80 backdrop-blur-xl border border-slate-700 rounded-lg p-4">
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Tier -->
            <div class="space-y-1">
                <div class="text-xs text-slate-400 uppercase tracking-wide">Tier</div>
                <div class="text-2xl font-bold font-outfit"
                     :class="{
                         'text-blue-400': getLevelInfo(selectedLevel).tier === 1,
                         'text-green-400': getLevelInfo(selectedLevel).tier === 2,
                         'text-purple-400': getLevelInfo(selectedLevel).tier === 3,
                         'text-orange-400': getLevelInfo(selectedLevel).tier === 4
                     }"
                     x-text="getLevelInfo(selectedLevel).tier">
                </div>
            </div>
            
            <!-- Total Advancements -->
            <div class="space-y-1">
                <div class="text-xs text-slate-400 uppercase tracking-wide">Total Advancements</div>
                <div class="text-2xl font-bold text-amber-400 font-outfit" x-text="getLevelInfo(selectedLevel).advancements"></div>
                <div class="text-xs text-slate-500" x-text="'(' + (getLevelInfo(selectedLevel).advancements / 2) + ' levels)'"></div>
            </div>
            
            <!-- Experiences -->
            <div class="space-y-1">
                <div class="text-xs text-slate-400 uppercase tracking-wide">Experiences</div>
                <div class="text-2xl font-bold text-emerald-400 font-outfit" x-text="getLevelInfo(selectedLevel).experiences"></div>
            </div>
            
            <!-- Domain Cards -->
            <div class="space-y-1">
                <div class="text-xs text-slate-400 uppercase tracking-wide">Domain Cards</div>
                <div class="text-2xl font-bold text-blue-400 font-outfit" x-text="getLevelInfo(selectedLevel).domainCards"></div>
            </div>
        </div>

        <!-- Additional Requirements Notice -->
        <div x-show="selectedLevel > 1" class="mt-4 pt-4 border-t border-slate-700">
            <div class="flex items-start space-x-2 text-sm text-amber-400">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <p class="font-medium">Additional Character Creation Steps Required</p>
                    <p class="text-slate-400 mt-1">
                        You'll need to complete <span class="text-amber-400 font-semibold" x-text="selectedLevel - 1"></span> level<span x-show="selectedLevel > 2">s</span> of advancement selections:
                    </p>
                    <ul class="mt-2 space-y-1 text-slate-400">
                        <li class="flex items-center space-x-2" x-show="[2, 5, 8].includes(selectedLevel)">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                            <span>Tier Achievement (new Experience, Proficiency increase)</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                            <span x-text="'Choose ' + getLevelInfo(selectedLevel).advancements + ' advancements total'"></span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                            <span x-text="'Select ' + (selectedLevel - 1) + ' domain card' + (selectedLevel > 2 ? 's' : '') + ' (one per level)'"></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Level 1 Notice -->
        <div x-show="selectedLevel === 1" class="mt-4 pt-4 border-t border-slate-700">
            <div class="flex items-start space-x-2 text-sm text-emerald-400">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <p class="font-medium">Standard Character Creation</p>
                    <p class="text-slate-400 mt-1">
                        Follow the standard character creation process. No advancement selections required.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tier Legend -->
    <div class="flex flex-wrap items-center gap-4 text-sm">
        <span class="text-slate-400">Tier Colors:</span>
        <div class="flex items-center space-x-2">
            <span class="w-4 h-4 rounded-full bg-blue-500"></span>
            <span class="text-slate-300">Tier 1</span>
        </div>
        <div class="flex items-center space-x-2">
            <span class="w-4 h-4 rounded-full bg-green-500"></span>
            <span class="text-slate-300">Tier 2</span>
        </div>
        <div class="flex items-center space-x-2">
            <span class="w-4 h-4 rounded-full bg-purple-500"></span>
            <span class="text-slate-300">Tier 3</span>
        </div>
        <div class="flex items-center space-x-2">
            <span class="w-4 h-4 rounded-full bg-orange-500"></span>
            <span class="text-slate-300">Tier 4</span>
        </div>
    </div>
</div>




