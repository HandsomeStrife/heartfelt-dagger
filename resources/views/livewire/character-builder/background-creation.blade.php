<!-- Background Creation Step -->
<div class="space-y-4 sm:space-y-6">
    <!-- Step Header -->
    <div class="mb-4 sm:mb-6">
        <h2 class="text-xl sm:text-2xl font-bold text-white mb-2 font-outfit">Create Background</h2>
        <p class="text-slate-300 text-xs sm:text-sm">Define your character's history and personality through class-specific questions.</p>
    </div>



    <!-- Mark Done Section (Moved to top) -->
    <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl p-3 sm:p-4">
        @if(in_array(\Domain\Character\Enums\CharacterBuilderStep::BACKGROUND->getStepNumber(), $completed_steps))
            <!-- Already Marked Complete -->
            <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="bg-emerald-500 rounded-full p-2">
                            <svg class="w-4 h-4 text-black" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-emerald-400 font-semibold">Background Section Complete!</p>
                            <p class="text-slate-300 text-sm">You can continue to the next step or keep adding details here.</p>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Mark Done Controls -->
            <div class="flex items-center justify-between">
                <div class="text-slate-300 text-sm">
                    <template x-if="canMarkBackgroundComplete">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span>You've answered <span x-text="answeredQuestions"></span> question<span x-text="answeredQuestions !== 1 ? 's' : ''"></span>. Ready to continue?</span>
                        </div>
                    </template>
                    <template x-if="!canMarkBackgroundComplete">
                        <span>Fill in at least one question, then mark this section as done when ready.</span>
                    </template>
                </div>
                
                <button 
                    @click="markBackgroundComplete()"
                    :class="canMarkBackgroundComplete ? 
                        'px-3 sm:px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition-colors duration-200 flex items-center gap-2 text-sm sm:text-base' :
                        'px-3 sm:px-4 py-2 bg-slate-600 text-slate-400 font-medium rounded-lg cursor-not-allowed flex items-center gap-2 text-sm sm:text-base'"
                    :disabled="!canMarkBackgroundComplete"
                    dusk="mark-background-complete"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Mark Done
                </button>
            </div>
        @endif
    </div>

    <!-- Writing Tips (Moved to top, replacing Background Guidelines) -->
    <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl p-3 sm:p-4">
        <h3 class="text-base sm:text-lg font-bold text-white font-outfit mb-3">Writing Tips</h3>
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-3 sm:gap-4 text-slate-300 text-xs sm:text-sm">
            <div>
                <h5 class="text-white font-medium mb-2">Writing Guidelines</h5>
                <ul class="space-y-1 text-xs">
                    <li>• Be specific and concrete in your answers</li>
                    <li>• Consider how your background ties to your class</li>
                    <li>• Think about relationships with other characters</li>
                    <li>• Leave room for growth and development</li>
                </ul>
            </div>
            <div>
                <h5 class="text-white font-medium mb-2">Story Elements</h5>
                <ul class="space-y-1 text-xs">
                    <li>• Include flaws and vulnerabilities</li>
                    <li>• Reference your ancestry and community</li>
                    <li>• Mention specific people, places, or events</li>
                    <li>• Connect to the wider world and campaign</li>
                </ul>
            </div>
        </div>
        <template x-if="selected_class">
            <div class="mt-3 pt-3 border-t border-blue-500/20">
                <p class="text-blue-300 text-sm"><strong x-text="selectedClassData?.name || ''"></strong> Focus: Questions below are tailored to your class's themes and experiences.</p>
            </div>
        </template>
    </div>

    <!-- Progress Indicator -->
    <template x-if="totalQuestions > 0">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between bg-slate-800/30 rounded-lg p-3 gap-2 sm:gap-0">
            <div class="flex items-center gap-3">
                <div class="text-sm text-slate-300">
                    <span class="font-medium text-white" x-text="answeredQuestions"></span> of <span x-text="totalQuestions"></span> answered
                </div>
                <template x-if="answeredQuestions >= 1">
                    <div class="flex items-center text-emerald-400 text-sm">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span class="font-medium">Complete</span>
                    </div>
                </template>
            </div>
            <div class="w-24 bg-slate-700 rounded-full h-2">
                <div class="bg-emerald-500 h-2 rounded-full transition-all duration-300" :style="`width: ${progressPercentage}%`"></div>
            </div>
        </div>
    </template>

    <!-- Main Content in Columns -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 sm:gap-6">
        
        <!-- Left Column: Background Questions -->
        <div class="space-y-3 sm:space-y-4">
            <h3 class="text-base sm:text-lg font-bold text-white font-outfit">Background Questions</h3>
            
            <template x-if="backgroundQuestions.length > 0">
                <div class="space-y-3 sm:space-y-4">
                    <template x-for="(question, index) in backgroundQuestions" :key="index">
                        <div class="bg-slate-800/30 backdrop-blur border border-slate-700/30 rounded-lg p-3 sm:p-4">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-amber-400 font-semibold text-sm" x-text="`Q${index + 1}`"></span>
                                        <template x-if="background_answers && background_answers[index] && background_answers[index].trim()">
                                            <div class="bg-emerald-500 rounded-full p-0.5">
                                                <svg class="w-3 h-3 text-black" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </template>
                                    </div>
                                    <p class="text-slate-300 text-sm mb-3 leading-relaxed" x-text="question"></p>
                                </div>
                            </div>

                            <textarea
                                :dusk="`background-answer-${index}`"
                                x-model="background_answers[index]"
                                @input="markAsUnsaved()"
                                class="w-full px-3 py-2 bg-slate-900/50 border border-slate-600/50 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-amber-500/50 focus:border-amber-500/50 resize-none text-xs sm:text-sm"
                                rows="4"
                                placeholder="Describe your character's experience..."
                                maxlength="500"
                            ></textarea>
                            
                            <template x-if="background_answers && background_answers[index] && background_answers[index].trim()">
                                <div class="mt-2 text-right text-xs text-slate-500">
                                    <span x-text="(background_answers[index] || '').length"></span>/500 characters
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </template>
            <template x-if="backgroundQuestions.length === 0">
                <div class="text-center py-8">
                    <div class="text-slate-400 mb-4">
                        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Select a Class First</h3>
                    <p class="text-slate-400 text-sm">Background questions are tailored to your chosen class.</p>
                </div>
            </template>
        </div>

        <!-- Right Column: Additional Details -->
        <div class="space-y-3 sm:space-y-4">
            <h3 class="text-base sm:text-lg font-bold text-white font-outfit">Character Details</h3>
            
            <!-- Physical Description -->
            <div class="bg-slate-800/20 backdrop-blur border border-slate-700/20 rounded-lg p-3 sm:p-4">
                <label class="block text-xs sm:text-sm font-semibold text-white mb-2">Physical Description</label>
                <textarea
                    dusk="physical-description"
                    x-model="physical_description"
                    @input="markAsUnsaved()"
                    class="w-full px-3 py-2 bg-slate-900/40 border border-slate-600/40 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-amber-500/50 focus:border-amber-500/50 resize-none text-xs sm:text-sm"
                    rows="4"
                    placeholder="Describe appearance and features..."
                    maxlength="300"
                ></textarea>
            </div>

            <!-- Personality Traits -->
            <div class="bg-slate-800/20 backdrop-blur border border-slate-700/20 rounded-lg p-3 sm:p-4">
                <label class="block text-xs sm:text-sm font-semibold text-white mb-2">Personality & Mannerisms</label>
                <textarea
                    dusk="personality-traits"
                    x-model="personality_traits"
                    @input="markAsUnsaved()"
                    class="w-full px-3 py-2 bg-slate-900/40 border border-slate-600/40 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-amber-500/50 focus:border-amber-500/50 resize-none text-xs sm:text-sm"
                    rows="4"
                    placeholder="Describe personality quirks and habits..."
                    maxlength="300"
                ></textarea>
            </div>

            <!-- Personal History -->
            <div class="bg-slate-800/20 backdrop-blur border border-slate-700/20 rounded-lg p-4">
                <label class="block text-sm font-semibold text-white mb-2">Personal History</label>
                <textarea
                    dusk="personal-history"
                    x-model="personal_history"
                    @input="markAsUnsaved()"
                    class="w-full px-3 py-2 bg-slate-900/40 border border-slate-600/40 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-amber-500/50 focus:border-amber-500/50 resize-none text-sm"
                    rows="3"
                    placeholder="Key events that shaped your character..."
                    maxlength="300"
                ></textarea>
            </div>

            <!-- Motivations -->
            <div class="bg-slate-800/20 backdrop-blur border border-slate-700/20 rounded-lg p-4">
                <label class="block text-sm font-semibold text-white mb-2">Motivations & Goals</label>
                <textarea
                    dusk="motivations-goals"
                    x-model="motivations"
                    @input="markAsUnsaved()"
                    class="w-full px-3 py-2 bg-slate-900/40 border border-slate-600/40 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-amber-500/50 focus:border-amber-500/50 resize-none text-sm"
                    rows="3"
                    placeholder="What drives your character forward..."
                    maxlength="300"
                ></textarea>
            </div>
        </div>
    </div>
</div>