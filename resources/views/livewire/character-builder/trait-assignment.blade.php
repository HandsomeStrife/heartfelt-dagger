<!-- Trait Assignment Step -->
<div class="space-y-4 sm:space-y-6">
    <!-- Step Header -->
    <div class="mb-4 sm:mb-6">
        <h2 class="text-xl sm:text-2xl font-bold text-white mb-2 font-outfit">Assign Traits</h2>
        <p class="text-slate-300 font-roboto text-sm sm:text-base">Distribute trait values to determine your character's strengths and weaknesses.</p>
    </div>

    <!-- Step Completion Indicator -->
    <div x-show="remainingValues.length === 0" class="p-3 sm:p-4 bg-gradient-to-r from-emerald-500/10 to-green-500/10 border border-emerald-500/20 rounded-xl">
        <div class="flex items-center">
            <div class="bg-emerald-500 rounded-full p-2 mr-3 flex-shrink-0">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-black" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            </div>
            <div>
                <p class="text-emerald-400 font-semibold text-sm sm:text-base">Trait Assignment Complete!</p>
                <p class="text-slate-300 text-xs sm:text-sm">All trait values have been successfully assigned to your character.</p>
            </div>
        </div>
    </div>

    <!-- Top Section: Progress and Suggestions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-8">
        <!-- Left Column: Progress and Available Values -->
        <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl p-3 sm:p-4">
            <h3 class="text-base sm:text-lg font-bold text-white font-outfit mb-3">Progress</h3>
            
            <div class="flex items-center gap-2 mb-4">
                <span class="text-slate-300 text-sm">
                    <span x-text="6 - remainingValues.length"></span>/6 assigned
                </span>
                <div x-show="remainingValues.length === 0" class="flex items-center text-emerald-400">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    <span class="font-medium">Complete!</span>
                </div>
            </div>
            
            <!-- Available Values -->
            <div class="mb-4">
                <span class="text-slate-300 text-sm block mb-2">Available values:</span>
                <div class="flex flex-wrap gap-2">
                    <template x-for="(value, index) in remainingValues" :key="'remaining-' + index">
                        <div 
                            :draggable="true"
                            @dragstart="draggedValue = value"
                            @dragend="draggedValue = null"
                            @click="selectedValue = value"
                            :class="{
                                'px-3 py-2 rounded-lg font-bold text-sm cursor-pointer transition-all duration-200 shadow-lg touch-manipulation border-2': true,
                                'bg-red-500 text-white border-red-600': value < 0,
                                'bg-slate-500 text-white border-slate-600': value === 0,
                                'bg-emerald-500 text-white border-emerald-600': value > 0,
                                'ring-2 ring-amber-400': selectedValue === value
                            }"
                            x-text="value > 0 ? '+' + value : value"
                            pest="available-value"
                            :pest-value="value"
                        ></div>
                    </template>
                    
                    <div x-show="remainingValues.length === 0" class="text-slate-400 text-center py-2 px-3 text-sm">
                        All assigned ✓
                    </div>
                </div>
            </div>
            
            <!-- Instructions -->
            <div class="pt-3 border-t border-slate-700/50">
                <p class="text-xs text-slate-400">
                    <span class="hidden sm:inline">Drag values to traits below, or </span>
                    <strong class="text-amber-300">1) Select a value</strong>, then <strong class="text-emerald-300">2) Tap a trait</strong> to assign it.
                    <span class="hidden sm:inline"><br>Must use exactly: <span class="text-amber-400 font-semibold">-1, 0, 0, +1, +1, +2</span></span>
                </p>
            </div>
        </div>

        <!-- Right Column: Class Suggestions -->
        <template x-if="selected_class && selectedClassData?.suggestedTraits">
            <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl p-3 sm:p-4">
                <h3 class="text-base sm:text-lg font-bold text-white mb-3 font-outfit" x-text="selectedClassData.name + ' Suggestions'"></h3>
                
                <div class="grid grid-cols-2 gap-2 mb-4">
                    <template x-for="[trait, value] in Object.entries(selectedClassData.suggestedTraits)" :key="trait">
                        <div class="flex justify-between items-center bg-slate-700/50 rounded-lg px-2 py-1">
                            <span class="text-slate-300 text-xs sm:text-sm" x-text="trait.charAt(0).toUpperCase() + trait.slice(1) + ':'"></span>
                            <span class="text-white font-medium text-xs sm:text-sm" x-text="value > 0 ? '+' + value : value"></span>
                        </div>
                    </template>
                </div>
                
                <button 
                    @click="applySuggestedTraits()"
                    class="w-full px-3 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-xl transition-all duration-200 flex items-center justify-center gap-2 text-sm"
                    pest="apply-suggested-traits"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    <span x-text="'Use ' + selectedClassData.name + ' Suggestions'"></span>
                </button>
            </div>
        </template>
        
        <!-- Placeholder when no class selected -->
        <template x-if="!selected_class || !selectedClassData?.suggestedTraits">
            <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl p-3 sm:p-4 flex items-center justify-center">
                <p class="text-slate-400 text-sm text-center">Select a class to see trait suggestions</p>
            </div>
        </template>
    </div>
    <!-- Trait Boxes Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
        <template x-for="[traitKey, traitInfo] in Object.entries(traitsData)" :key="traitKey">
            <div 
                @dragover.prevent
                @dragenter.prevent
                @drop="dropValue(traitKey, draggedValue)"
                @click="assignSelectedValue(traitKey)"
                :class="{
                    'transition-all duration-200 cursor-pointer hover:scale-105 active:scale-95': true,
                    'ring-2 ring-amber-400/50': draggedValue !== null && canDropValue(traitKey, draggedValue)
                }"
                class="group flex flex-col items-center"
                pest="trait-box"
                :pest-trait="traitKey"
            >
                <!-- Stat Frame Component -->
                <div class="w-20 h-auto sm:w-24 relative">
                    <!-- Stat Frame SVG -->
                    <svg
                        class="inline-block align-middle w-full h-auto"
                        role="img"
                        :aria-label="traitInfo.name + ' trait value ' + (assigned_traits[traitKey] !== undefined ? (assigned_traits[traitKey] >= 0 ? '+' + assigned_traits[traitKey] : assigned_traits[traitKey]) : 'unassigned')"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 360 380"
                        fill="none"
                    >
                        <defs>
                            <linearGradient :id="'goldBase-' + traitKey" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#FFD65A"/>
                                <stop offset="35%" stop-color="#FFC23A"/>
                                <stop offset="65%" stop-color="#E39B1D"/>
                                <stop offset="100%" stop-color="#B87512"/>
                            </linearGradient>
                            <linearGradient :id="'goldHighlight-' + traitKey" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#FFF6C8"/>
                                <stop offset="40%" stop-color="#FFE682"/>
                                <stop offset="100%" stop-color="#D58F1A"/>
                            </linearGradient>
                        </defs>

                        <!-- Shield -->
                        <polygon :id="'shield-' + traitKey"
                                points="40,24 320,24 320,222 292,248 292,300 180,352 68,300 68,248 40,222"
                                fill="white"/>
                        <use :href="'#shield-' + traitKey" fill="none" :stroke="'url(#goldBase-' + traitKey + ')'" stroke-width="28" stroke-linejoin="round"/>
                        <use :href="'#shield-' + traitKey" fill="white" :stroke="'url(#goldHighlight-' + traitKey + ')'" stroke-width="14" stroke-linejoin="round"/>

                        <!-- Number Display -->
                        <text
                            x="165"
                            y="200"
                            text-anchor="middle"
                            dominant-baseline="middle"
                            font-weight="900"
                            font-family="inherit"
                            font-size="140"
                            :fill="(assigned_traits && assigned_traits[traitKey] !== undefined) ? '#000' : '#666'"
                            x-text="(assigned_traits && assigned_traits[traitKey] !== undefined) ? 
                                ('+' + assigned_traits[traitKey]) : 
                                '?'">
                        </text>

                        <!-- Label on top -->
                        <g aria-hidden="true">
                            <rect x="15" y="0" width="330" height="60" rx="10" fill="#000000" />
                            <text x="180" y="34"
                                text-anchor="middle"
                                dominant-baseline="middle"
                                font-size="42"
                                font-weight="900"
                                font-family="inherit"
                                fill="#ffffff"
                                style="letter-spacing:0.6px; text-transform:uppercase;"
                                x-text="traitInfo.name">
                            </text>
                        </g>
                    </svg>
                    
                    <!-- Instruction Overlay for Unassigned -->
                    <div 
                        x-show="!assigned_traits || assigned_traits[traitKey] === undefined"
                        class="absolute inset-0 flex items-center justify-center">
                        <div class="bg-slate-900/90 backdrop-blur-sm border border-dashed border-slate-500 rounded-lg px-2 py-1 text-slate-300 text-xs font-medium shadow-lg text-center">
                            <!-- Show different messages based on selection state -->
                            <div x-show="selectedValue === null">
                                <span class="hidden sm:inline">Select value above</span>
                                <span class="sm:hidden">Select value</span>
                            </div>
                            <div x-show="selectedValue !== null">
                                <span class="hidden sm:inline">Tap to assign </span>
                                <span class="sm:hidden">Tap for </span>
                                <span class="font-bold text-white" x-text="'+' + selectedValue"></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Trait Description -->
                <div class="mt-2 text-center">
                    <p class="text-slate-400 text-xs leading-tight" x-text="traitInfo.description"></p>
                </div>
            </div>
        </template>
    </div>

    <!-- Tier-Based Trait Advancements (For Level 2+) -->
    <template x-if="starting_level > 1">
        <div class="mt-8 pt-8 border-t border-slate-700">
            <div class="mb-6">
                <h3 class="text-xl font-bold text-white mb-2 font-outfit">Trait Advancements</h3>
                <p class="text-slate-300 text-sm mb-4">
                    When you select "Trait Bonus" advancements in the advancement panel, you can increase your character's traits here.
                </p>
                
                <!-- Tier Marking Explanation -->
                <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4">
                    <h4 class="text-blue-300 font-semibold mb-2 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        Tier Marking Rules
                    </h4>
                    <ul class="space-y-1.5 text-xs text-slate-300">
                        <li class="flex items-start gap-2">
                            <span class="text-blue-400 mt-0.5">•</span>
                            <span><span class="font-semibold text-white">Each advancement:</span> Select 2 traits to increase by +1</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-blue-400 mt-0.5">•</span>
                            <span><span class="font-semibold text-white">Tier boundaries:</span> Tier 2 (Levels 2-4), Tier 3 (Levels 5-7), Tier 4 (Levels 8-10)</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-amber-400 mt-0.5">⚠</span>
                            <span><span class="text-amber-300 font-semibold">Marking restriction:</span> A trait can only be marked once per tier - Once you increase a trait at any level within a tier, you cannot select it again until the next tier</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-400 mt-0.5">✓</span>
                            <span><span class="font-semibold text-white">Marks clear:</span> All trait marks clear at the start of each new tier (Levels 2, 5, and 8)</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Tier 2 (Levels 2-4) -->
            <template x-if="starting_level >= 2">
                <div class="mb-6">
                    <div class="bg-emerald-900/20 border border-emerald-500/30 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-emerald-300 font-bold font-outfit text-lg">Tier 2 Trait Advancements (Levels 2-4)</h4>
                            <span class="text-xs text-slate-400">Marks cleared at Level 5</span>
                        </div>
                        
                        <div class="space-y-4">
                            <template x-if="getTraitBonusAdvancementsForTier(2).length === 0">
                                <p class="text-slate-400 text-sm italic">No trait bonus advancements selected for Tier 2. Select them in the advancement panel above.</p>
                            </template>
                            
                            <!-- Loop through each trait bonus advancement -->
                            <template x-for="(bonus, bonusIdx) in getTraitBonusAdvancementsForTier(2)" :key="'tier2-bonus-' + bonus.level + '-' + bonus.advIndex">
                                <div class="bg-slate-800/50 border border-slate-600/50 rounded-xl p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                            <span class="text-emerald-300 font-bold font-outfit" x-text="'Level ' + bonus.level + ' - Trait Bonus'"></span>
                                        </div>
                                        <span class="text-xs text-slate-400">
                                            <span x-text="(bonus.advancement.traits || []).filter(t => t).length"></span> / 2 selected
                                        </span>
                                    </div>

                                    <p class="text-slate-300 text-sm mb-3">Select 2 traits to receive +1 bonus:</p>

                                    <!-- Trait Selection Grid -->
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                        <template x-for="[traitKey, traitInfo] in Object.entries(traitsData)" :key="'trait-' + bonus.level + '-' + bonus.advIndex + '-' + traitKey">
                                            <button
                                                type="button"
                                                @click="toggleTraitBonus(bonus.level, bonus.advIndex, traitKey)"
                                                :disabled="!canSelectTraitForBonus(traitKey, bonus.level, bonus.advIndex, 0) && !canSelectTraitForBonus(traitKey, bonus.level, bonus.advIndex, 1) && !isTraitSelectedForBonus(bonus.level, bonus.advIndex, traitKey)"
                                                :class="{
                                                    'px-3 py-2 rounded-lg font-medium text-sm transition-all duration-200 text-left flex items-center justify-between': true,
                                                    'bg-emerald-500/20 border-2 border-emerald-400 text-white': isTraitSelectedForBonus(bonus.level, bonus.advIndex, traitKey),
                                                    'bg-slate-700/50 border border-slate-600 text-slate-300 hover:bg-slate-600/50 hover:border-slate-500 cursor-pointer': !isTraitSelectedForBonus(bonus.level, bonus.advIndex, traitKey) && (canSelectTraitForBonus(traitKey, bonus.level, bonus.advIndex, 0) || canSelectTraitForBonus(traitKey, bonus.level, bonus.advIndex, 1)),
                                                    'bg-slate-800/50 border border-slate-700 text-slate-500 cursor-not-allowed opacity-50': !canSelectTraitForBonus(traitKey, bonus.level, bonus.advIndex, 0) && !canSelectTraitForBonus(traitKey, bonus.level, bonus.advIndex, 1) && !isTraitSelectedForBonus(bonus.level, bonus.advIndex, traitKey)
                                                }"
                                            >
                                                <span class="capitalize" x-text="traitKey"></span>
                                                <template x-if="isTraitSelectedForBonus(bonus.level, bonus.advIndex, traitKey)">
                                                    <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                </template>
                                                <template x-if="!isTraitSelectedForBonus(bonus.level, bonus.advIndex, traitKey) && (!canSelectTraitForBonus(traitKey, bonus.level, bonus.advIndex, 0) && !canSelectTraitForBonus(traitKey, bonus.level, bonus.advIndex, 1))">
                                                    <span class="text-xs text-slate-600">Marked</span>
                                                </template>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Tier 3 (Levels 5-7) -->
            <template x-if="starting_level >= 5">
                <div class="mb-6">
                    <div class="bg-purple-900/20 border border-purple-500/30 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-purple-300 font-bold font-outfit text-lg">Tier 3 Trait Advancements (Levels 5-7)</h4>
                            <span class="text-xs text-slate-400">Marks cleared at Level 8</span>
                        </div>
                        
                        <div class="space-y-4">
                            <template x-if="getTraitBonusAdvancementsForTier(3).length === 0">
                                <p class="text-slate-400 text-sm italic">No trait bonus advancements selected for Tier 3. Select them in the advancement panel above.</p>
                            </template>
                            
                            <!-- Loop through each trait bonus advancement -->
                            <template x-for="(bonus, bonusIdx) in getTraitBonusAdvancementsForTier(3)" :key="'tier3-bonus-' + bonus.level + '-' + bonus.advIndex">
                                <div class="bg-slate-800/50 border border-slate-600/50 rounded-xl p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                            <span class="text-purple-300 font-bold font-outfit" x-text="'Level ' + bonus.level + ' - Trait Bonus'"></span>
                                        </div>
                                        <span class="text-xs text-slate-400">
                                            <span x-text="(bonus.advancement.traits || []).filter(t => t).length"></span> / 2 selected
                                        </span>
                                    </div>

                                    <p class="text-slate-300 text-sm mb-3">Select 2 traits to receive +1 bonus:</p>

                                    <!-- Trait Selection Grid -->
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                        <template x-for="[traitKey, traitInfo] in Object.entries(traitsData)" :key="'trait-' + bonus.level + '-' + bonus.advIndex + '-' + traitKey">
                                            <button
                                                type="button"
                                                @click="toggleTraitBonus(bonus.level, bonus.advIndex, traitKey)"
                                                :disabled="!canSelectTraitForBonus(traitKey, bonus.level, bonus.advIndex, 0) && !canSelectTraitForBonus(traitKey, bonus.level, bonus.advIndex, 1) && !isTraitSelectedForBonus(bonus.level, bonus.advIndex, traitKey)"
                                                :class="{
                                                    'px-3 py-2 rounded-lg font-medium text-sm transition-all duration-200 text-left flex items-center justify-between': true,
                                                    'bg-purple-500/20 border-2 border-purple-400 text-white': isTraitSelectedForBonus(bonus.level, bonus.advIndex, traitKey),
                                                    'bg-slate-700/50 border border-slate-600 text-slate-300 hover:bg-slate-600/50 hover:border-slate-500 cursor-pointer': !isTraitSelectedForBonus(bonus.level, bonus.advIndex, traitKey) && (canSelectTraitForBonus(traitKey, bonus.level, bonus.advIndex, 0) || canSelectTraitForBonus(traitKey, bonus.level, bonus.advIndex, 1)),
                                                    'bg-slate-800/50 border border-slate-700 text-slate-500 cursor-not-allowed opacity-50': !canSelectTraitForBonus(traitKey, bonus.level, bonus.advIndex, 0) && !canSelectTraitForBonus(traitKey, bonus.level, bonus.advIndex, 1) && !isTraitSelectedForBonus(bonus.level, bonus.advIndex, traitKey)
                                                }"
                                            >
                                                <span class="capitalize" x-text="traitKey"></span>
                                                <template x-if="isTraitSelectedForBonus(bonus.level, bonus.advIndex, traitKey)">
                                                    <svg class="w-4 h-4 text-purple-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                </template>
                                                <template x-if="!isTraitSelectedForBonus(bonus.level, bonus.advIndex, traitKey) && (!canSelectTraitForBonus(traitKey, bonus.level, bonus.advIndex, 0) && !canSelectTraitForBonus(traitKey, bonus.level, bonus.advIndex, 1))">
                                                    <span class="text-xs text-slate-600">Marked</span>
                                                </template>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Tier 4 (Levels 8-10) -->
            <template x-if="starting_level >= 8">
                <div class="mb-6">
                    <div class="bg-amber-900/20 border border-amber-500/30 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-amber-300 font-bold font-outfit text-lg">Tier 4 Trait Advancements (Levels 8-10)</h4>
                            <span class="text-xs text-slate-400">Final tier</span>
                        </div>
                        
                        <div class="space-y-4">
                            <template x-if="getTraitBonusAdvancementsForTier(4).length === 0">
                                <p class="text-slate-400 text-sm italic">No trait bonus advancements selected for Tier 4. Select them in the advancement panel above.</p>
                            </template>
                            
                            <!-- Loop through each trait bonus advancement -->
                            <template x-for="(bonus, bonusIdx) in getTraitBonusAdvancementsForTier(4)" :key="'tier4-bonus-' + bonus.level + '-' + bonus.advIndex">
                                <div class="bg-slate-800/50 border border-slate-600/50 rounded-xl p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                            <span class="text-amber-300 font-bold font-outfit" x-text="'Level ' + bonus.level + ' - Trait Bonus'"></span>
                                        </div>
                                        <span class="text-xs text-slate-400">
                                            <span x-text="(bonus.advancement.traits || []).filter(t => t).length"></span> / 2 selected
                                        </span>
                                    </div>

                                    <p class="text-slate-300 text-sm mb-3">Select 2 traits to receive +1 bonus:</p>

                                    <!-- Trait Selection Grid -->
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                        <template x-for="[traitKey, traitInfo] in Object.entries(traitsData)" :key="'trait-' + bonus.level + '-' + bonus.advIndex + '-' + traitKey">
                                            <button
                                                type="button"
                                                @click="toggleTraitBonus(bonus.level, bonus.advIndex, traitKey)"
                                                :disabled="!canSelectTraitForBonus(traitKey, bonus.level, bonus.advIndex, 0) && !canSelectTraitForBonus(traitKey, bonus.level, bonus.advIndex, 1) && !isTraitSelectedForBonus(bonus.level, bonus.advIndex, traitKey)"
                                                :class="{
                                                    'px-3 py-2 rounded-lg font-medium text-sm transition-all duration-200 text-left flex items-center justify-between': true,
                                                    'bg-amber-500/20 border-2 border-amber-400 text-white': isTraitSelectedForBonus(bonus.level, bonus.advIndex, traitKey),
                                                    'bg-slate-700/50 border border-slate-600 text-slate-300 hover:bg-slate-600/50 hover:border-slate-500 cursor-pointer': !isTraitSelectedForBonus(bonus.level, bonus.advIndex, traitKey) && (canSelectTraitForBonus(traitKey, bonus.level, bonus.advIndex, 0) || canSelectTraitForBonus(traitKey, bonus.level, bonus.advIndex, 1)),
                                                    'bg-slate-800/50 border border-slate-700 text-slate-500 cursor-not-allowed opacity-50': !canSelectTraitForBonus(traitKey, bonus.level, bonus.advIndex, 0) && !canSelectTraitForBonus(traitKey, bonus.level, bonus.advIndex, 1) && !isTraitSelectedForBonus(bonus.level, bonus.advIndex, traitKey)
                                                }"
                                            >
                                                <span class="capitalize" x-text="traitKey"></span>
                                                <template x-if="isTraitSelectedForBonus(bonus.level, bonus.advIndex, traitKey)">
                                                    <svg class="w-4 h-4 text-amber-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                </template>
                                                <template x-if="!isTraitSelectedForBonus(bonus.level, bonus.advIndex, traitKey) && (!canSelectTraitForBonus(traitKey, bonus.level, bonus.advIndex, 0) && !canSelectTraitForBonus(traitKey, bonus.level, bonus.advIndex, 1))">
                                                    <span class="text-xs text-slate-600">Marked</span>
                                                </template>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </template>
</div>