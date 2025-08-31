<!-- Connection Creation Step -->
<div class="space-y-8">
    <!-- Step Header -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-white mb-2 font-outfit">Create Connections</h2>
        <p class="text-slate-300 font-roboto">Answer the connection questions to define relationships with other party members. These connections 
            create bonds that can provide mechanical benefits and rich roleplay opportunities.</p>
    </div>

    <!-- Step Completion Indicator -->
    @if($connection_progress['answered_connections'] >= $connection_progress['total_connections'] && $connection_progress['total_connections'] > 0)
        <div class="my-6 p-4 bg-gradient-to-r from-emerald-500/10 to-green-500/10 border border-emerald-500/20 rounded-xl">
            <div class="flex items-center">
                <div class="bg-emerald-500 rounded-full p-2 mr-3">
                    <svg class="w-5 h-5 text-black" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <p class="text-emerald-400 font-semibold">Connection Creation Complete!</p>
                    <p class="text-slate-300 text-sm">You've answered all {{ $connection_progress['total_connections'] }} connection questions for your {{ ucfirst($character->selected_class) }}.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Connection Questions -->
    @if(!empty($filtered_data['connection_questions']))
        <div class="space-y-6">
            @foreach($filtered_data['connection_questions'] as $index => $question)
                <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl p-6">
                    <div class="mb-4">
                        <div class="flex items-start justify-between mb-3">
                            <h4 class="text-white font-semibold font-outfit">Connection {{ $index + 1 }}</h4>
                            @if(!empty(trim($character->connection_answers[$index] ?? '')))
                                <div class="bg-pink-500 rounded-full p-1">
                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <p class="text-slate-300 text-sm leading-relaxed">{{ $question }}</p>
                    </div>

                    <div>
                        <label for="connection-{{ $index }}" class="block text-sm font-medium text-slate-300 mb-2">Your Connection</label>
                        <textarea 
                            dusk="connection-answer-{{ $index }}"
                            id="connection-{{ $index }}"
                            wire:model.live.debounce.500ms="character.connection_answers.{{ $index }}"
                            wire:change="updateConnectionAnswer({{ $index }}, $event.target.value)"
                            placeholder="Describe your connection with another party member..."
                            rows="4"
                            maxlength="400"
                            class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-200 resize-y"
                        ></textarea>
                        
                        <!-- Character Count -->
                        <div class="flex justify-between items-center mt-2 text-xs">
                            <span class="text-slate-500">{{ strlen($character->connection_answers[$index] ?? '') }}/400 characters</span>
                            @if(!empty(trim($character->connection_answers[$index] ?? '')))
                                <span class="text-pink-400 flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    Complete
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12 bg-slate-800/30 rounded-xl border border-slate-700/50">
            <div class="text-slate-400">
                <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <p class="text-lg font-medium">No connection questions available</p>
                <p class="text-sm mt-1">Please complete the previous steps first.</p>
            </div>
        </div>
    @endif

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
                <h5 class="text-white font-medium mb-3">Connection Examples</h5>
                <div class="space-y-3 text-sm text-slate-300">
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
    @if($connection_progress['answered_connections'] >= $connection_progress['total_connections'] && $connection_progress['total_connections'] > 0)
        <div class="bg-gradient-to-r from-pink-500/10 to-purple-500/10 border border-pink-500/20 rounded-xl p-6">
            <div class="flex items-start">
                <div class="bg-gradient-to-r from-pink-500 to-purple-500 rounded-full p-2 mr-4 flex-shrink-0">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-pink-400 font-semibold font-outfit mb-1">Character Creation Complete!</p>
                    <p class="text-slate-300 text-sm mb-4">
                        🎉 Congratulations! You've successfully created your {{ ucfirst($character->selected_class) }} character. 
                        All connections have been established and your character is ready for adventure!
                    </p>
                    
                    <!-- Character Summary -->
                    <div class="bg-slate-800/30 rounded-lg p-4">
                        <h5 class="text-white font-medium mb-3">Character Summary</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
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
                                    <span class="text-white">{{ $connection_progress['answered_connections'] }}/{{ $connection_progress['total_connections'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-3 mt-4">
                        <button 
                            wire:click="saveCharacter"
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 text-white font-semibold rounded-lg transition-all duration-200"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Save Character
                        </button>
                        
                        <button 
                            onclick="window.print()"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-lg transition-all duration-200"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Print Sheet
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
