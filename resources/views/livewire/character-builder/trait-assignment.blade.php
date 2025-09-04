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
</div>