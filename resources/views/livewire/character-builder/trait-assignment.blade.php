<!-- Trait Assignment Step -->
<div class="space-y-6">
    <!-- Step Header -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-white mb-2 font-outfit">Assign Traits</h2>
        <p class="text-slate-300 font-roboto">Distribute trait values to determine your character's strengths and weaknesses.</p>
    </div>

    <!-- Step Completion Indicator -->
    <div x-show="remainingValues.length === 0" class="my-6 p-4 bg-gradient-to-r from-emerald-500/10 to-green-500/10 border border-emerald-500/20 rounded-xl">
        <div class="flex items-center">
            <div class="bg-emerald-500 rounded-full p-2 mr-3">
                <svg class="w-5 h-5 text-black" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            </div>
            <div>
                <p class="text-emerald-400 font-semibold">Trait Assignment Complete!</p>
                <p class="text-slate-300 text-sm">All trait values have been successfully assigned to your character.</p>
            </div>
        </div>
    </div>
    
    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-12">
        <!-- Left Column: Instructions and Controls -->
        <div class="space-y-8">
            <!-- Instructions -->
            <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl p-6">
                <h3 class="text-lg font-bold text-white mb-4 font-outfit">Character Traits</h3>
                <div class="space-y-3 text-slate-300">
                    <p class="text-sm">Traits determine your character's core abilities and how they interact with the world.</p>
                    <div class="space-y-2 text-sm">
                        <p><strong class="text-blue-400">Agility:</strong> Speed, reflexes, and stealth</p>
                        <p><strong class="text-red-400">Strength:</strong> Physical power and endurance</p>
                        <p><strong class="text-green-400">Finesse:</strong> Precision and fine motor control</p>
                        <p><strong class="text-purple-400">Instinct:</strong> Intuition and awareness</p>
                        <p><strong class="text-pink-400">Presence:</strong> Charisma and leadership</p>
                        <p><strong class="text-amber-400">Knowledge:</strong> Learning and reasoning</p>
                    </div>
                    <p class="text-xs text-slate-400 mt-4">Use the values on the right to assign <strong class="text-amber-400">-1, 0, 0, +1, +1, +2</strong> to these traits. Higher values make you better at related actions.</p>
                </div>
            </div>

            <!-- Class Suggestions -->
            <template x-if="selected_class && selectedClassData?.suggestedTraits">
                <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl p-6">
                    <h3 class="text-lg font-bold text-white mb-4 font-outfit" x-text="selectedClassData.name + ' Suggestions'"></h3>
                    
                    <div class="grid grid-cols-2 gap-3 mb-6">
                        <template x-for="[trait, value] in Object.entries(selectedClassData.suggestedTraits)" :key="trait">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-300 text-sm" x-text="trait.charAt(0).toUpperCase() + trait.slice(1) + ':'"></span>
                                <span class="text-white font-medium" x-text="value > 0 ? '+' + value : value"></span>
                            </div>
                        </template>
                    </div>
                    
                    <button 
                        @click="applySuggestedTraits()"
                        class="w-full px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-xl transition-all duration-200 flex items-center justify-center gap-2"
                        pest="apply-suggested-traits"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                        <span x-text="'Use ' + selectedClassData.name + ' Suggestions'"></span>
                    </button>
                </div>
            </template>
        </div>

        <!-- Right Column: Trait Boxes (3x2 Grid) -->
        <div class="space-y-6">
            <!-- Heading with Progress -->
            <div class="text-center">
                <div class="flex items-center justify-center gap-4 mb-4">
                    <h3 class="text-lg font-bold text-white font-outfit">Character Traits</h3>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-slate-300">
                            <span x-text="6 - remainingValues.length"></span>/6 assigned
                        </span>
                        <div x-show="remainingValues.length === 0" class="flex items-center text-emerald-400">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span class="font-medium">Complete!</span>
                        </div>
                    </div>
                </div>
                
                <!-- Available Values -->
                <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-lg p-4 mb-6">
                    <p class="text-slate-300 text-sm mb-3">Drag values onto trait boxes below:</p>
                    <div class="flex flex-wrap gap-2 justify-center">
                        <template x-for="(value, index) in remainingValues" :key="'remaining-' + index">
                            <div 
                                :draggable="true"
                                @dragstart="draggedValue = value"
                                @dragend="draggedValue = null"
                                :class="{
                                    'px-3 py-1 rounded-lg font-bold text-sm cursor-grab active:cursor-grabbing transition-all duration-200 shadow-lg': true,
                                    'bg-red-500 text-white': value < 0,
                                    'bg-slate-500 text-white': value === 0,
                                    'bg-emerald-500 text-white': value > 0
                                }"
                                x-text="value > 0 ? '+' + value : value"
                            ></div>
                        </template>
                        
                        <div x-show="remainingValues.length === 0" class="text-slate-400 text-center py-1 px-3 text-sm">
                            All values assigned
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Trait Boxes Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4">
                <template x-for="[traitKey, traitInfo] in Object.entries(traitsData)" :key="traitKey">
                    <div 
                        @dragover.prevent
                        @dragenter.prevent
                        @drop="dropValue(traitKey, draggedValue)"
                        :class="{
                            'transition-all duration-200': true,
                            'ring-2 ring-amber-400/50': draggedValue !== null && canDropValue(traitKey, draggedValue)
                        }"
                        class="group"
                    >
                        <!-- Stat Box Container -->
                        <div class="flex flex-col items-center w-20 sm:w-24 mx-auto">
                            <!-- Trait Name Header -->
                            <div class="bg-zinc-950 w-full rounded-t-lg px-2 py-1 text-center">
                                <span class="font-semibold text-white text-xs tracking-wide" x-text="traitInfo.name"></span>
                            </div>
                            
                            <!-- Stat Box with Background -->
                            <div class="relative w-full h-auto overflow-hidden rounded-b-lg">
                                <img src="/img/stat-box-bg.webp" alt="Stat Box Background" class="w-full h-auto">
                                
                                <!-- Content Overlay -->
                                <div class="absolute inset-0 flex justify-center">
                                    <div class="text-center mt-4">
                                        <template x-if="assigned_traits[traitKey] !== undefined">
                                            <div 
                                                @click="removeValue(traitKey)"
                                                class="text-4xl font-bold cursor-pointer hover:scale-110 transition-transform duration-200 text-zinc-900 drop-shadow-lg"
                                                title="Click to remove"
                                                x-text="assigned_traits[traitKey] > 0 ? '+' + assigned_traits[traitKey] : assigned_traits[traitKey]"
                                            ></div>
                                        </template>
                                        <template x-if="assigned_traits[traitKey] === undefined">
                                            <div class="bg-white/80 backdrop-blur-sm border-2 border-dashed border-slate-600 rounded px-2 py-1 text-slate-700 text-xs font-medium shadow-sm">
                                                Drop here
                                            </div>
                                        </template>
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
    </div>
</div>