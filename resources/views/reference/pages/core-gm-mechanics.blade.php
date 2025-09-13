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
                                @include('reference.partials.navigation-menu', ['current_page' => 'core-gm-mechanics'])
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-8">
                            <!-- Page Header -->
                            <div class="mb-8 flex items-start justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-pink-500 rounded-xl flex items-center justify-center shadow-lg">
                                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h1 class="font-outfit text-3xl font-bold text-white">Core GM Mechanics</h1>
                                        <p class="text-slate-400 text-sm mt-1">Dice Rolling & GM Moves</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-2 ml-4 flex-shrink-0">
                                    <span class="px-3 py-1 bg-purple-500/20 text-purple-300 text-sm rounded-full">
                                        Running an Adventure
                                    </span>
                                </div>
                            </div>

                            <!-- Page Content -->
                            <div class="prose prose-invert max-w-none" data-search-body>
                                <!-- GM Dice -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Rolling Dice</h2>
                                    
                                    <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-8">
                                        <div class="flex items-start gap-6">
                                            <div class="w-16 h-16 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12a8 8 0 11-16 0 8 8 0 0116 0zm-8-5v5l3 3" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="font-outfit text-2xl font-bold text-red-300 mb-4">The GM's Die</h3>
                                                <p class="text-slate-300 leading-relaxed">
                                                    The GM has <strong class="text-red-300">no Duality Dice</strong>; instead, they roll a single <strong class="text-red-300">d20 called the GM's Die</strong>.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Adversary Attacks -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Adversary Attack Rolls</h2>
                                    
                                    <div class="space-y-6">
                                        <!-- Basic Attack Rules -->
                                        <div class="bg-orange-500/10 border border-orange-500/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-orange-300 mb-4">Attack Resolution</h3>
                                            <p class="text-slate-300 leading-relaxed mb-4">
                                                When an adversary attacks a PC, roll your d20 and add the adversary's attack bonus to the result. If the total meets or beats the target's Evasion, the attack succeeds; otherwise, the attack fails. On a successful attack, roll the attack's damage dice to determine how much it deals.
                                            </p>
                                        </div>

                                        <!-- Critical Success -->
                                        <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-black" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-yellow-600 mb-3">Critical Success (Natural 20)</h3>
                                                    <p class="text-slate-300 leading-relaxed mb-4">
                                                        If you roll a <strong class="text-yellow-600">natural 20</strong> on an attack, your roll automatically succeeds and you deal extra damage.
                                                    </p>
                                                    <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-4">
                                                        <h4 class="font-outfit text-sm font-bold text-yellow-400 mb-2">Critical Damage Calculation:</h4>
                                                        <p class="text-slate-300 text-sm mb-3">
                                                            Roll damage normally, then add the <strong class="text-yellow-400">highest number on the damage dice</strong> to the total.
                                                        </p>
                                                        <div class="bg-slate-700/30 border border-slate-600/30 rounded-lg p-3">
                                                            <p class="text-yellow-200 text-xs">
                                                                <strong>Example:</strong> An attack that deals 3d6+2 deals 18+3d6+2 on a critical success; the critical success does not affect the flat damage modifier.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Reaction Rolls Note -->
                                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-blue-300 mb-3">Reaction Roll Note</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed">
                                                        A critical success on an adversary's <strong class="text-blue-300">reaction roll</strong> automatically succeeds, but confers no additional benefit.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Roll Guidance -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Guidance on Action Rolls</h2>
                                    
                                    <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-8">
                                        <div class="flex items-start gap-6">
                                            <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="font-outfit text-2xl font-bold text-green-300 mb-4">When Players Want to Act</h3>
                                                <p class="text-slate-300 leading-relaxed mb-6">
                                                    After a player describes a move they want to make during the game, you might decide an action roll is necessary to determine how the scene progresses. Use this guide to determine what to present the player:
                                                </p>
                                                
                                                <div class="space-y-4">
                                                    <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-4">
                                                        <h4 class="font-outfit text-sm font-bold text-green-400 mb-2">1. Determine Necessity</h4>
                                                        <p class="text-slate-300 text-xs">Consider the PC's Experiences or backstory, the pressure they're acting under, and the possible outcomes.</p>
                                                    </div>
                                                    
                                                    <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-4">
                                                        <h4 class="font-outfit text-sm font-bold text-green-400 mb-2">2. Establish Stakes</h4>
                                                        <p class="text-slate-300 text-xs">Establish the stakes of an action roll before the player makes it.</p>
                                                    </div>
                                                    
                                                    <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-4">
                                                        <h4 class="font-outfit text-sm font-bold text-green-400 mb-2">3. Communicate Consequences</h4>
                                                        <p class="text-slate-300 text-xs">Communicate any unavoidable consequences.</p>
                                                    </div>
                                                    
                                                    <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-4">
                                                        <h4 class="font-outfit text-sm font-bold text-green-400 mb-2">4. Offer Alternatives</h4>
                                                        <p class="text-slate-300 text-xs">If desired, you can offer the player the opportunity to forgo an action roll in exchange for agreeing to an interesting outcome, cost, or complication.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Making Moves -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Making Moves</h2>
                                    
                                    <div class="space-y-6">
                                        <!-- GM Moves Overview -->
                                        <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-purple-300 mb-4">GM Moves</h3>
                                            <p class="text-slate-300 leading-relaxed mb-4">
                                                As the GM, you have <strong class="text-purple-300">GM moves</strong> that change the story in response to the players' actions. GM moves aren't bound by specific spells or effects—when you make a GM move, you can describe the action in whatever way the fiction demands.
                                            </p>
                                            <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                <p class="text-purple-200 text-sm">
                                                    GM moves happen during <strong class="text-purple-300">GM turns</strong>. A GM turn begins when the spotlight passes to them and ends when the spotlight passes back to the players.
                                                </p>
                                            </div>
                                        </div>

                                        <!-- When to Make a Move -->
                                        <div class="bg-indigo-500/10 border border-indigo-500/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-indigo-300 mb-4">When to Make a Move</h3>
                                            <p class="text-slate-300 leading-relaxed mb-4">
                                                The GM can make a GM move whenever you want, but the frequency and severity depends on the type of story you're telling, the actions your players take, and the tone of the session you're running.
                                            </p>
                                            
                                            <h4 class="font-outfit text-sm font-bold text-indigo-400 mb-3">Make a GM move when the players:</h4>
                                            <div class="grid md:grid-cols-2 gap-3">
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <p class="text-indigo-200 text-sm">• Roll with Fear</p>
                                                </div>
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <p class="text-indigo-200 text-sm">• Fail an action roll</p>
                                                </div>
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <p class="text-indigo-200 text-sm">• Do something with unavoidable consequences</p>
                                                </div>
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <p class="text-indigo-200 text-sm">• Give you a "golden opportunity"</p>
                                                </div>
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3 md:col-span-2">
                                                    <p class="text-indigo-200 text-sm">• Look to you for what happens next</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Choosing GM Moves -->
                                        <div class="bg-teal-500/10 border border-teal-500/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-teal-300 mb-4">Choosing GM Moves</h3>
                                            <p class="text-slate-300 leading-relaxed mb-4">
                                                The result of a player's action roll determines your response:
                                            </p>
                                            
                                            <div class="space-y-3">
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                    <h4 class="font-outfit text-sm font-bold text-green-400 mb-2">Critical Success</h4>
                                                    <p class="text-slate-300 text-xs">Let the player describe their success, then give them an additional opportunity or advantage.</p>
                                                </div>
                                                
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                    <h4 class="font-outfit text-sm font-bold text-blue-400 mb-2">Success with Hope</h4>
                                                    <p class="text-slate-300 text-xs">Let the player describe their success, then show how the world reacts to it.</p>
                                                </div>
                                                
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                    <h4 class="font-outfit text-sm font-bold text-yellow-400 mb-2">Success with Fear</h4>
                                                    <p class="text-slate-300 text-xs">Work with the player to describe their success, then take a Fear and make a GM move to introduce a minor consequence, complication, or cost.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick Reference -->
                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-8">
                                    <h3 class="font-outfit text-2xl font-bold text-amber-300 mb-6">GM Mechanics Quick Reference</h3>
                                    
                                    <div class="grid md:grid-cols-2 gap-6">
                                        <div>
                                            <h4 class="font-outfit text-lg font-bold text-amber-400 mb-4">Dice & Attacks</h4>
                                            <ul class="space-y-2 text-slate-300 text-sm">
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>GM rolls single d20 (no Duality Dice)</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>Natural 20 = auto success + extra damage</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>Critical damage = normal + highest die</span>
                                                </li>
                                            </ul>
                                        </div>
                                        
                                        <div>
                                            <h4 class="font-outfit text-lg font-bold text-amber-400 mb-4">GM Moves</h4>
                                            <ul class="space-y-2 text-slate-300 text-sm">
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>Make moves during GM turns</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>Respond to Fear rolls and failures</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>Establish stakes before rolls</span>
                                                </li>
                                            </ul>
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
