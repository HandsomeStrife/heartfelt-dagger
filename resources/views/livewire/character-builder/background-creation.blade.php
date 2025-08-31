<!-- Background Creation Step -->
<div class="space-y-6">
    <!-- Step Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-white mb-2 font-outfit">Create Background</h2>
        <p class="text-slate-300 text-sm">Define your character's history and personality through class-specific questions.</p>
    </div>

    @php
        $totalQuestions = count($filtered_data['background_questions'] ?? []);
        $answeredQuestions = count(array_filter($character->background_answers ?? [], fn($answer) => !empty(trim($answer))));
    @endphp

    <!-- Mark Done Section (Moved to top) -->
    <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl p-4">
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
                    @if($answeredQuestions >= 1)
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span>You've answered {{ $answeredQuestions }} question{{ $answeredQuestions !== 1 ? 's' : '' }}. Ready to continue?</span>
                        </div>
                    @else
                        <span>Fill in at least one question, then mark this section as done when ready.</span>
                    @endif
                </div>
                
                <button 
                    wire:click="markBackgroundComplete"
                    @if($answeredQuestions >= 1) 
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition-colors duration-200 flex items-center gap-2"
                        dusk="mark-background-complete"
                    @else
                        class="px-4 py-2 bg-slate-600 text-slate-400 font-medium rounded-lg cursor-not-allowed flex items-center gap-2"
                        disabled
                    @endif
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
    <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl p-4">
        <h3 class="text-lg font-bold text-white font-outfit mb-3">Writing Tips</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-slate-300 text-sm">
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
        @if($character->selected_class)
            <div class="mt-3 pt-3 border-t border-blue-500/20">
                <p class="text-blue-300 text-sm"><strong>{{ ucfirst($character->selected_class) }} Focus:</strong> Questions below are tailored to your class's themes and experiences.</p>
            </div>
        @endif
    </div>

    <!-- Progress Indicator -->
    @if($totalQuestions > 0)
        <div class="flex items-center justify-between bg-slate-800/30 rounded-lg p-3">
            <div class="flex items-center gap-3">
                <div class="text-sm text-slate-300">
                    <span class="font-medium text-white">{{ $answeredQuestions }}</span> of {{ $totalQuestions }} answered
                </div>
                @if($answeredQuestions >= 1)
                    <div class="flex items-center text-emerald-400 text-sm">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span class="font-medium">Complete</span>
                    </div>
                @endif
            </div>
            <div class="w-24 bg-slate-700 rounded-full h-2">
                <div class="bg-emerald-500 h-2 rounded-full transition-all duration-300" style="width: {{ $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 100) : 0 }}%"></div>
            </div>
        </div>
    @endif

    <!-- Main Content in Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Left Column: Background Questions -->
        <div class="space-y-4">
            <h3 class="text-lg font-bold text-white font-outfit">Background Questions</h3>
            
            @if(!empty($filtered_data['background_questions']))
                <div class="space-y-4">
                    @foreach($filtered_data['background_questions'] as $index => $question)
                        <div class="bg-slate-800/30 backdrop-blur border border-slate-700/30 rounded-lg p-4">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-amber-400 font-semibold text-sm">Q{{ $index + 1 }}</span>
                                        @if(!empty(trim($character->background_answers[$index] ?? '')))
                                            <div class="bg-emerald-500 rounded-full p-0.5">
                                                <svg class="w-3 h-3 text-black" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <p class="text-slate-300 text-sm mb-3 leading-relaxed">{{ $question }}</p>
                                </div>
                            </div>

                            <textarea
                                dusk="background-answer-{{ $index }}"
                                wire:model.live.debounce.500ms="character.background_answers.{{ $index }}"
                                class="w-full px-3 py-2 bg-slate-900/50 border border-slate-600/50 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-amber-500/50 focus:border-amber-500/50 resize-none text-sm"
                                rows="3"
                                placeholder="Describe your character's experience..."
                                maxlength="500"
                            ></textarea>
                            
                            @if(!empty(trim($character->background_answers[$index] ?? '')))
                                <div class="mt-2 text-right text-xs text-slate-500">
                                    {{ strlen($character->background_answers[$index] ?? '') }}/500 characters
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <div class="text-slate-400 mb-4">
                        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Select a Class First</h3>
                    <p class="text-slate-400 text-sm">Background questions are tailored to your chosen class.</p>
                </div>
            @endif
        </div>

        <!-- Right Column: Additional Details -->
        <div class="space-y-4">
            <h3 class="text-lg font-bold text-white font-outfit">Character Details</h3>
            
            <!-- Physical Description -->
            <div class="bg-slate-800/20 backdrop-blur border border-slate-700/20 rounded-lg p-4">
                <label class="block text-sm font-semibold text-white mb-2">Physical Description</label>
                <textarea
                    dusk="physical-description"
                    wire:model.live.debounce.500ms="character.physical_description"
                    class="w-full px-3 py-2 bg-slate-900/40 border border-slate-600/40 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-amber-500/50 focus:border-amber-500/50 resize-none text-sm"
                    rows="3"
                    placeholder="Describe appearance and features..."
                    maxlength="300"
                ></textarea>
            </div>

            <!-- Personality Traits -->
            <div class="bg-slate-800/20 backdrop-blur border border-slate-700/20 rounded-lg p-4">
                <label class="block text-sm font-semibold text-white mb-2">Personality & Mannerisms</label>
                <textarea
                    dusk="personality-traits"
                    wire:model.live.debounce.500ms="character.personality_traits"
                    class="w-full px-3 py-2 bg-slate-900/40 border border-slate-600/40 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-amber-500/50 focus:border-amber-500/50 resize-none text-sm"
                    rows="3"
                    placeholder="Describe personality quirks and habits..."
                    maxlength="300"
                ></textarea>
            </div>

            <!-- Personal History -->
            <div class="bg-slate-800/20 backdrop-blur border border-slate-700/20 rounded-lg p-4">
                <label class="block text-sm font-semibold text-white mb-2">Personal History</label>
                <textarea
                    dusk="personal-history"
                    wire:model.live.debounce.500ms="character.personal_history"
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
                    wire:model.live.debounce.500ms="character.motivations"
                    class="w-full px-3 py-2 bg-slate-900/40 border border-slate-600/40 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-amber-500/50 focus:border-amber-500/50 resize-none text-sm"
                    rows="3"
                    placeholder="What drives your character forward..."
                    maxlength="300"
                ></textarea>
            </div>
        </div>
    </div>
</div>