<!-- Connection Creation Step -->
<div class="space-y-8">
    <!-- Step Header -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-white mb-2 font-outfit">Create Connections</h2>
        <p class="text-slate-300 font-roboto">Answer the connection questions to define relationships with other party members. These connections 
            create bonds that can provide mechanical benefits and rich roleplay opportunities.</p>
    </div>

    <!-- Step Completion Indicator -->
    <template x-if="isConnectionComplete">
        <div class="my-6 p-4 bg-gradient-to-r from-emerald-500/10 to-green-500/10 border border-emerald-500/20 rounded-xl">
            <div class="flex items-center">
                <div class="bg-emerald-500 rounded-full p-2 mr-3">
                    <svg class="w-5 h-5 text-black" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <p class="text-emerald-400 font-semibold">Connection Creation Complete!</p>
                    <p class="text-slate-300 text-sm">You've answered all <span x-text="totalConnections"></span> connection questions for your <span x-text="selectedClassData?.name || ''"></span>.</p>
                </div>
            </div>
        </div>
    </template>

    <!-- Connection Questions -->
    <template x-if="connectionQuestions.length > 0">
        <div class="space-y-6">
            <template x-for="(question, index) in connectionQuestions" :key="index">
                <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl p-6">
                    <div class="mb-4">
                        <div class="flex items-start justify-between mb-3">
                            <h4 class="text-white font-semibold font-outfit" x-text="`Connection ${index + 1}`"></h4>
                            <template x-if="connection_answers && connection_answers[index] && connection_answers[index].trim()">
                                <div class="bg-pink-500 rounded-full p-1">
                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </template>
                        </div>
                        <p class="text-slate-300 text-sm leading-relaxed" x-text="question"></p>
                    </div>

                    <div>
                        <label :for="`connection-${index}`" class="block text-sm font-medium text-slate-300 mb-2">Your Connection</label>
                        <textarea 
                            :dusk="`connection-answer-${index}`"
                            :id="`connection-${index}`"
                            x-model="connection_answers[index]"
                            @input="markAsUnsaved()"
                            placeholder="Describe your connection with another party member..."
                            rows="4"
                            maxlength="400"
                            class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-200 resize-y"
                        ></textarea>
                        
                        <!-- Character Count -->
                        <div class="flex justify-between items-center mt-2 text-xs">
                            <span class="text-slate-500" x-text="`${(connection_answers && connection_answers[index] || '').length}/400 characters`"></span>
                            <template x-if="connection_answers && connection_answers[index] && connection_answers[index].trim()">
                                <span class="text-pink-400 flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    Complete
                                </span>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </template>
    <template x-if="connectionQuestions.length === 0">
        <div class="text-center py-12 bg-slate-800/30 rounded-xl border border-slate-700/50">
            <div class="text-slate-400">
                <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <p class="text-lg font-medium">No connection questions available</p>
                <p class="text-sm mt-1">Please complete the previous steps first.</p>
            </div>
        </div>
    </template>

    <!-- Connection Tips -->
    <div class="bg-pink-500/10 border border-pink-500/20 rounded-xl p-6">
        <h4 class="text-pink-300 font-semibold font-outfit mb-4">Connection Guidelines</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h5 class="text-white font-medium mb-3">Writing Connections</h5>
                <ul class="space-y-2 text-sm text-slate-300">
                    <li class="flex items-start">
                        <span class="text-pink-400 mr-2">•</span>
                        <span>Be specific about the other party member</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-pink-400 mr-2">•</span>
                        <span>Describe the nature of your relationship</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-pink-400 mr-2">•</span>
                        <span>Include emotional stakes or history</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-pink-400 mr-2">•</span>
                        <span>Leave room for development and growth</span>
                    </li>
                </ul>
            </div>
            <div>
                <h5 class="text-white font-medium mb-3 text-sm sm:text-base">Connection Examples</h5>
                <div class="space-y-3 text-xs sm:text-sm text-slate-300">
                    <div class="bg-slate-800/30 rounded-lg p-3">
                        <p class="italic">"You saved my life once, and I've never forgotten that debt of honor."</p>
                    </div>
                    <div class="bg-slate-800/30 rounded-lg p-3">
                        <p class="italic">"We grew up together, but you left our village under mysterious circumstances."</p>
                    </div>
                    <div class="bg-slate-800/30 rounded-lg p-3">
                        <p class="italic">"Your optimism reminds me of someone I lost, and it both comforts and pains me."</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Completion Status -->
    <template x-if="isConnectionComplete">
        <div class="bg-gradient-to-r from-pink-500/10 to-purple-500/10 border border-pink-500/20 rounded-xl p-4 sm:p-6">
            <div class="flex items-start">
                <div class="bg-gradient-to-r from-pink-500 to-purple-500 rounded-full p-2 mr-3 sm:mr-4 flex-shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-pink-400 font-semibold font-outfit mb-1 text-sm sm:text-base">Character Creation Complete!</p>
                    <p class="text-slate-300 text-xs sm:text-sm mb-4">
                        🎉 Congratulations! You've successfully created your {{ ucfirst($character->selected_class) }} character. 
                        All connections have been established and your character is ready for adventure!
                    </p>
                    
                    <!-- Character Summary -->
                    <div class="bg-slate-800/30 rounded-lg p-3 sm:p-4">
                        <h5 class="text-white font-medium mb-3 text-sm sm:text-base">Character Summary</h5>
                        <div class="grid grid-cols-1 xl:grid-cols-3 gap-3 sm:gap-4 text-xs sm:text-sm">
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Name:</span>
                                    <span class="text-white">{{ $character->name ?: 'Unnamed' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Class:</span>
                                    <span class="text-white">{{ ucfirst($character->selected_class ?? 'Unknown') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Heritage:</span>
                                    <span class="text-white">{{ ucfirst($character->selected_ancestry ?? 'Unknown') }}</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Traits:</span>
                                    <span class="text-white">{{ count($character->assigned_traits) }}/6</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Equipment:</span>
                                    <span class="text-white">{{ count($character->selected_equipment) }} items</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Experiences:</span>
                                    <span class="text-white">{{ count($character->experiences) }}</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Domain Cards:</span>
                                    <span class="text-white">{{ count($character->selected_domain_cards) }}/2</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Background:</span>
                                    <span class="text-white">{{ count(array_filter($character->background_answers, fn($a) => !empty(trim($a)))) }} answers</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Connections:</span>
                                    <span class="text-white" x-text="`${answeredConnections}/${totalConnections}`"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 mt-4">
                        <button 
                            @click="saveCharacter()"
                            class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 text-white font-semibold rounded-lg transition-all duration-200 text-sm sm:text-base"
                        >
                            <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Save Character
                        </button>
                        
                        <button 
                            onclick="window.print()"
                            class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-lg transition-all duration-200 text-sm sm:text-base"
                        >
                            <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span class="hidden sm:inline">Print Sheet</span>
                            <span class="sm:hidden">Print</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
