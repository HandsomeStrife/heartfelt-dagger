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
                                @include('reference.partials.navigation-menu', ['current_page' => 'making-moves-and-taking-action'])
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-8">
                            <!-- Page Header -->
                            <div class="mb-8 flex items-start justify-between">
                                <h1 class="font-outfit text-3xl font-bold text-white">
                                    Making Moves & Taking Action
                                </h1>
                                
                                <div class="flex items-center space-x-2 ml-4 flex-shrink-0">
                                    <span class="px-3 py-1 bg-blue-500/20 text-blue-300 text-sm rounded-full">
                                        Core Mechanics
                                    </span>
                                </div>
                            </div>

                            <!-- Page Content -->
                            <div class="prose prose-invert max-w-none" data-search-body>
                                <p class="text-slate-300 leading-relaxed mb-8 text-lg">
                                    Any time a character does something to advance the story, such as speaking with another character, interacting with the environment, making an attack, casting a spell, or using a class feature, they are making a move.
                                </p>

                                <!-- Action Rolls Overview -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Action Rolls</h2>
                                    <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6 mb-8">
                                        <p class="text-blue-100 leading-relaxed">
                                            Any move where success would be trivial or failure would be boring automatically succeeds, but any move that's difficult to accomplish or risky to attempt triggers an <strong class="text-blue-300">action roll</strong>.
                                        </p>
                                    </div>

                                    <!-- Duality Dice -->
                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6 mb-8">
                                        <h3 class="font-outfit text-xl font-bold text-amber-300 mb-4">Duality Dice</h3>
                                        <p class="text-slate-300 leading-relaxed mb-4">
                                            All action rolls require a pair of d12s called <strong class="text-amber-300">Duality Dice</strong>. These are two visually distinct twelve-sided dice, with one die representing Hope and the other representing Fear.
                                        </p>
                                        <p class="text-slate-300 leading-relaxed">
                                            To make an action roll, you roll the Duality Dice, sum the results, apply any relevant modifiers, and compare the total to a Difficulty number to determine the outcome.
                                        </p>
                                    </div>
                                </div>

                                <!-- Action Roll Outcomes -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Action Roll Outcomes</h2>
                                    
                                    <div class="space-y-4">
                                        <!-- Success with Hope -->
                                        <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-green-300 mb-2">Success with Hope</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed">
                                                        If your total meets or beats the Difficulty AND your Hope Die shows a higher result than your Fear Die, you succeed and gain a Hope.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Success with Fear -->
                                        <div class="bg-orange-500/10 border border-orange-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-orange-300 mb-2">Success with Fear</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed">
                                                        If your total meets or beats the Difficulty AND your Fear Die shows a higher result than your Hope Die, you succeed with a cost or complication, but the GM gains a Fear.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Failure with Hope -->
                                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-blue-300 mb-2">Failure with Hope</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed">
                                                        If your total is less than the Difficulty AND your Hope Die shows a higher result than your Fear Die, you fail with a minor consequence and gain a Hope, then the spotlight swings to the GM.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Failure with Fear -->
                                        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-red-300 mb-2">Failure with Fear</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed">
                                                        If your total is less than the Difficulty AND your Fear Die shows a higher result than your Hope Die, you fail with a major consequence and the GM gains a Fear, then the spotlight swings to the GM.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Critical Success -->
                                        <div class="bg-gradient-to-r from-yellow-500/10 to-amber-500/10 border border-yellow-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-amber-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-black" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Critical Success</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed mb-2">
                                                        If the Duality Dice show matching results, you automatically succeed with a bonus, gain a Hope, and clear a Stress. If this was an attack roll, you deal critical damage.
                                                    </p>
                                                    <p class="text-amber-200 text-xs italic">
                                                        Note: A Critical Success counts as a roll "with Hope."
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Failing Forward -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Failing Forward</h2>
                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                        <p class="text-slate-300 leading-relaxed">
                                            In Daggerheart, every time you roll the dice, the scene changes in some way. There is no such thing as a roll where "nothing happens," because the fiction constantly evolves based on the successes and failures of the characters.
                                        </p>
                                    </div>
                                </div>

                                <!-- Action Roll Procedure -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Action Roll Procedure</h2>
                                    
                                    <div class="space-y-6">
                                        <!-- Step 1 -->
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="bg-amber-500 text-black rounded-full w-10 h-10 flex items-center justify-center text-lg font-bold flex-shrink-0">
                                                    1
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Pick an Appropriate Trait</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed">
                                                        Some actions specify which trait applies; otherwise, the GM tells the acting player which character trait best applies to the action being attempted. If more than one trait could apply, the GM chooses or lets the acting player decide.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Step 2 -->
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="bg-amber-500 text-black rounded-full w-10 h-10 flex items-center justify-center text-lg font-bold flex-shrink-0">
                                                    2
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Determine the Difficulty</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed">
                                                        Some actions specify the Difficulty; otherwise, the GM determines it based on the scenario. The GM can choose whether to share the Difficulty with the table, but should communicate the potential consequences of failure.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Step 3 -->
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="bg-amber-500 text-black rounded-full w-10 h-10 flex items-center justify-center text-lg font-bold flex-shrink-0">
                                                    3
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Apply Extra Dice and Modifiers</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed mb-2">
                                                        The acting player decides whether to Utilize an Experience or activate other effects, then adds the appropriate tokens and dice (such as advantage or Rally dice) to their dice pool.
                                                    </p>
                                                    <p class="text-amber-200 text-xs italic">
                                                        Note: Unless specifically allowed, a player must declare the use of any Experiences, extra dice, or other modifiers before the roll.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Step 4 -->
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="bg-amber-500 text-black rounded-full w-10 h-10 flex items-center justify-center text-lg font-bold flex-shrink-0">
                                                    4
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Roll the Dice</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed">
                                                        The acting player rolls their entire dice pool and announces the results in the format of "[total result] with [Hope/Fear]"—or "Critical Success!" in the case of matching Duality Dice.
                                                    </p>
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
        </div>
    </div>
</x-layouts.app>
