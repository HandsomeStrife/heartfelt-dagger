<x-layouts.app>
    <x-sub-navigation>
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center space-x-4">
                <a href="{{ route('reference.index') }}" 
                   class="text-slate-400 hover:text-white transition-colors text-sm">
                    ← Back
                </a>
            </div>
            
            <div class="flex-1 max-w-md mx-4">
                <livewire:reference-search-new :is_sidebar="false" />
            </div>
            
            <div class="w-16"></div> <!-- Spacer for centering -->
        </div>
    </x-sub-navigation>

    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950">
        <div class="container mx-auto px-4 py-8">
            <div class="w-full mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    <!-- Sidebar -->
                    <div class="lg:col-span-3">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-6">
                            <h3 class="font-outfit text-lg font-semibold text-white mb-4">Reference Pages</h3>
                            
                            <nav class="space-y-6">
                                @include('reference.partials.navigation-menu', ['current_page' => 'additional-rules'])
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-8">
                            <!-- Page Header -->
                            <div class="mb-8 flex items-start justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center shadow-lg">
                                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h1 class="font-outfit text-3xl font-bold text-white">Additional Rules</h1>
                                        <p class="text-slate-400 text-sm mt-1">General Game Rules & Clarifications</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-2 ml-4 flex-shrink-0">
                                    <span class="px-3 py-1 bg-blue-500/20 text-blue-300 text-sm rounded-full">
                                        Core Mechanics
                                    </span>
                                </div>
                            </div>

                            <!-- Page Content -->
                            <div class="prose prose-invert max-w-none" data-search-body>
                                <!-- Introduction -->
                                <div class="bg-indigo-500/10 border border-indigo-500/30 rounded-xl p-6 mb-8">
                                    <p class="text-indigo-100 leading-relaxed text-lg">
                                        The following rules apply to many aspects of the game and help clarify common situations that arise during play.
                                    </p>
                                </div>

                                <!-- Rules Grid -->
                                <div class="grid md:grid-cols-2 gap-6 mb-12">
                                    <!-- Rounding Up -->
                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                        <div class="flex items-center gap-3 mb-4">
                                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                                            </svg>
                                            <h3 class="font-outfit text-lg font-bold text-blue-300">Rounding Up</h3>
                                        </div>
                                        <p class="text-slate-300 text-sm leading-relaxed mb-3">
                                            This game doesn't use fractions; if you need to round to a whole number, <strong class="text-blue-300">round up</strong> unless otherwise specified.
                                        </p>
                                        <p class="text-blue-200 text-xs italic">
                                            When in doubt, resolve any ambiguity in favor of the PCs.
                                        </p>
                                    </div>

                                    <!-- Rerolling Dice -->
                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                        <div class="flex items-center gap-3 mb-4">
                                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            <h3 class="font-outfit text-lg font-bold text-green-300">Rerolling Dice</h3>
                                        </div>
                                        <p class="text-slate-300 text-sm leading-relaxed">
                                            When a feature allows you to reroll a die, you always take the <strong class="text-green-300">new result</strong> unless the feature specifically says otherwise.
                                        </p>
                                    </div>

                                    <!-- Incoming Damage -->
                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                        <div class="flex items-center gap-3 mb-4">
                                            <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                            </svg>
                                            <h3 class="font-outfit text-lg font-bold text-red-300">Incoming Damage</h3>
                                        </div>
                                        <p class="text-slate-300 text-sm leading-relaxed">
                                            <strong class="text-red-300">Incoming damage</strong> means the total damage from a single attack or source, <strong>before</strong> Armor Slots are marked.
                                        </p>
                                    </div>

                                    <!-- Simultaneous Effects -->
                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                        <div class="flex items-center gap-3 mb-4">
                                            <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                            </svg>
                                            <h3 class="font-outfit text-lg font-bold text-yellow-300">Simultaneous Effects</h3>
                                        </div>
                                        <p class="text-slate-300 text-sm leading-relaxed">
                                            If the resolution order of multiple effects is unclear, the person in control of the effects (player or GM) decides what order to resolve them in.
                                        </p>
                                    </div>
                                </div>

                                <!-- Advanced Rules -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Advanced Rules</h2>
                                    
                                    <div class="space-y-6">
                                        <!-- Stacking Effects -->
                                        <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-purple-300 mb-3">Stacking Effects</h3>
                                                    <p class="text-slate-300 leading-relaxed mb-4">
                                                        Unless stated otherwise, all effects beside <strong class="text-purple-300">conditions</strong> and <strong class="text-purple-300">advantage/disadvantage</strong> can stack.
                                                    </p>
                                                    <div class="grid md:grid-cols-2 gap-4">
                                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                            <h4 class="font-outfit text-sm font-bold text-green-400 mb-2">Can Stack:</h4>
                                                            <ul class="text-slate-300 text-xs space-y-1">
                                                                <li>• Damage bonuses</li>
                                                                <li>• Stat bonuses</li>
                                                                <li>• Most spell effects</li>
                                                            </ul>
                                                        </div>
                                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                            <h4 class="font-outfit text-sm font-bold text-red-400 mb-2">Don't Stack:</h4>
                                                            <ul class="text-slate-300 text-xs space-y-1">
                                                                <li>• Same condition twice</li>
                                                                <li>• Advantage/disadvantage</li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Ongoing Spell Effects -->
                                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-blue-300 mb-3">Ongoing Spell Effects</h3>
                                                    <p class="text-slate-300 leading-relaxed">
                                                        If an effect doesn't have a listed mechanical expiration, it only ends when decided by the controlling player, the GM, or the demands of the fiction.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Resource Management -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Resource Management</h2>
                                    
                                    <div class="space-y-6">
                                        <!-- Spending Resources -->
                                        <div class="bg-orange-500/10 border border-orange-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-orange-300 mb-3">Spending Resources</h3>
                                                    <p class="text-slate-300 leading-relaxed mb-4">
                                                        Unless an effect states otherwise, you can't spend Hope or mark Stress <strong class="text-orange-300">multiple times</strong> on the same feature to increase or repeat its effects on the same roll.
                                                    </p>
                                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                        <p class="text-slate-300 text-sm">
                                                            <strong class="text-orange-300">Example:</strong> You can't spend 2 Hope on the same ability to double its effect, unless the ability specifically allows it.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Using Features After a Roll -->
                                        <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-green-300 mb-3">Using Features After a Roll</h3>
                                                    <p class="text-slate-300 leading-relaxed mb-4">
                                                        If a feature allows you to affect a roll after the result has been totaled, you can use it after the GM declares whether the roll succeeds or fails, but <strong class="text-green-300">not after</strong> the consequences unfold or another roll is made.
                                                    </p>
                                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                        <h4 class="font-outfit text-sm font-bold text-green-400 mb-2">Timing Window:</h4>
                                                        <div class="space-y-2 text-slate-300 text-sm">
                                                            <div class="flex items-center gap-2">
                                                                <span class="text-green-400">✓</span>
                                                                <span>After roll is totaled</span>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <span class="text-green-400">✓</span>
                                                                <span>After success/failure is declared</span>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <span class="text-red-400">✗</span>
                                                                <span>After consequences unfold</span>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <span class="text-red-400">✗</span>
                                                                <span>After another roll is made</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick Reference -->
                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-8">
                                    <h3 class="font-outfit text-2xl font-bold text-amber-300 mb-6">Quick Reference</h3>
                                    
                                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4 text-center">
                                            <div class="text-blue-400 font-bold text-sm mb-1">Rounding</div>
                                            <div class="text-slate-300 text-xs">Always round up</div>
                                        </div>
                                        
                                        <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4 text-center">
                                            <div class="text-green-400 font-bold text-sm mb-1">Rerolls</div>
                                            <div class="text-slate-300 text-xs">Take new result</div>
                                        </div>
                                        
                                        <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 text-center">
                                            <div class="text-red-400 font-bold text-sm mb-1">Incoming Damage</div>
                                            <div class="text-slate-300 text-xs">Before armor reduction</div>
                                        </div>
                                        
                                        <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-4 text-center">
                                            <div class="text-yellow-600 font-bold text-sm mb-1">Simultaneous</div>
                                            <div class="text-slate-300 text-xs">Controller decides order</div>
                                        </div>
                                        
                                        <div class="bg-purple-500/10 border border-purple-500/30 rounded-lg p-4 text-center">
                                            <div class="text-purple-400 font-bold text-sm mb-1">Stacking</div>
                                            <div class="text-slate-300 text-xs">Most effects stack</div>
                                        </div>
                                        
                                        <div class="bg-orange-500/10 border border-orange-500/30 rounded-lg p-4 text-center">
                                            <div class="text-orange-400 font-bold text-sm mb-1">Resources</div>
                                            <div class="text-slate-300 text-xs">One use per roll</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
