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
                                @include('reference.partials.navigation-menu', ['current_page' => 'additional-gm-guidance'])
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
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h1 class="font-outfit text-3xl font-bold text-white">Additional GM Guidance</h1>
                                        <p class="text-slate-400 text-sm mt-1">Session Preparation & Player Engagement</p>
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
                                <!-- Introduction -->
                                <div class="bg-indigo-500/10 border border-indigo-500/30 rounded-xl p-6 mb-8">
                                    <p class="text-indigo-100 leading-relaxed text-lg">
                                        This section provides additional guidance for <strong class="text-indigo-300">preparing and running a session</strong> of Daggerheart.
                                    </p>
                                </div>

                                <!-- Story Beats -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Story Beats</h2>
                                    
                                    <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-8">
                                        <div class="flex items-start gap-6">
                                            <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="font-outfit text-2xl font-bold text-blue-300 mb-4">Understanding Story Beats</h3>
                                                <p class="text-slate-300 leading-relaxed mb-6">
                                                    In storytelling, a <strong class="text-blue-300">beat</strong> is a moment that changes the trajectory of the narrative—a shift in the world, a significant action or reaction, an emotional revelation, or an important decision.
                                                </p>
                                                
                                                <div class="space-y-4">
                                                    <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-4">
                                                        <h4 class="font-outfit text-sm font-bold text-blue-400 mb-2">Collaborative Storytelling</h4>
                                                        <p class="text-slate-300 text-sm">Take turns with the players, narrating a beat and then letting them react and carry the scene forward with their own beats.</p>
                                                    </div>
                                                    
                                                    <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-4">
                                                        <h4 class="font-outfit text-sm font-bold text-blue-400 mb-2">Session Preparation</h4>
                                                        <p class="text-slate-300 text-sm">When preparing for a session, plan in terms of the moments that give shape to each scene or sequence, rather than pre-scripting specific details or exchanges.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Combat Encounters -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Preparing Combat Encounters</h2>
                                    
                                    <div class="space-y-6">
                                        <!-- Core Philosophy -->
                                        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-red-300 mb-4">Story-Driven Combat</h3>
                                            <p class="text-slate-300 leading-relaxed mb-4">
                                                Build the hurdles the PCs face around the question of <strong class="text-red-300">"What helps tell the story?"</strong> Enemies, environments, and hazards are the tools for heightening tension and creating drama.
                                            </p>
                                            <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                <p class="text-red-200 text-sm">
                                                    Ensure that combat is being used to give players more information about the unfolding story, revealing the world, the plot, or the characters.
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Battles and Narrative -->
                                        <div class="bg-orange-500/10 border border-orange-500/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-orange-300 mb-4">Battles and Narrative</h3>
                                            <p class="text-slate-300 leading-relaxed mb-4">
                                                Dynamic battles create suspense by forcing players to choose between their various objectives, engaging their character's motivations and weaknesses, and creating the crucible that the players use to forge their characters into legendary heroes.
                                            </p>
                                            
                                            <h4 class="font-outfit text-sm font-bold text-orange-400 mb-3">When preparing combat encounters:</h4>
                                            <div class="grid md:grid-cols-2 gap-3">
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <p class="text-orange-200 text-sm">• Consider the narrative function of the battle</p>
                                                </div>
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <p class="text-orange-200 text-sm">• Base adversaries' moves on their motives</p>
                                                </div>
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <p class="text-orange-200 text-sm">• Use dynamic environments to bring the battleground to life</p>
                                                </div>
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <p class="text-orange-200 text-sm">• Add enemies that can interact with the PCs' features and special abilities</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Session Rewards -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Session Rewards</h2>
                                    
                                    <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-6">
                                        <h3 class="font-outfit text-lg font-bold text-green-300 mb-4">Reward Players at the End of a Session</h3>
                                        
                                        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                                            <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4 text-center">
                                                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-2">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </div>
                                                <h4 class="text-green-300 font-bold text-sm mb-1">Useful Information</h4>
                                                <p class="text-slate-300 text-xs">Clues, secrets, or knowledge</p>
                                            </div>
                                            
                                            <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4 text-center">
                                                <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center mx-auto mb-2">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                    </svg>
                                                </div>
                                                <h4 class="text-green-300 font-bold text-sm mb-1">Story Hooks</h4>
                                                <p class="text-slate-300 text-xs">Future adventure opportunities</p>
                                            </div>
                                            
                                            <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4 text-center">
                                                <div class="w-10 h-10 bg-emerald-500 rounded-full flex items-center justify-center mx-auto mb-2">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                    </svg>
                                                </div>
                                                <h4 class="text-green-300 font-bold text-sm mb-1">Loot</h4>
                                                <p class="text-slate-300 text-xs">Items and magical equipment</p>
                                            </div>
                                            
                                            <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4 text-center">
                                                <div class="w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center mx-auto mb-2">
                                                    <svg class="w-5 h-5 text-black" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7L15 1H5C3.9 1 3 1.9 3 3V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V9M12 7C14.8 7 17 9.2 17 12S14.8 17 12 17 7 14.8 7 12 9.2 7 12 7Z"/>
                                                    </svg>
                                                </div>
                                                <h4 class="text-green-300 font-bold text-sm mb-1">Gold</h4>
                                                <p class="text-slate-300 text-xs">Wealth and currency</p>
                                            </div>
                                            
                                            <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4 text-center md:col-span-2 lg:col-span-1">
                                                <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-2">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                    </svg>
                                                </div>
                                                <h4 class="text-green-300 font-bold text-sm mb-1">Equipment Access</h4>
                                                <p class="text-slate-300 text-xs">New gear or enhancements</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Crafting Scenes -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Crafting Scenes</h2>
                                    
                                    <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl p-6">
                                        <h3 class="font-outfit text-lg font-bold text-purple-300 mb-4">Scene Setting</h3>
                                        <p class="text-slate-300 leading-relaxed">
                                            Whenever you start a session, arrive at a new place, or change the situation, tell the players what they need to know by <strong class="text-purple-300">thinking with all of your senses</strong> and sharing something unique or unexpected about the fiction.
                                        </p>
                                    </div>
                                </div>

                                <!-- Engaging Players -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Engaging Your Players</h2>
                                    
                                    <div class="bg-cyan-500/10 border border-cyan-500/30 rounded-xl p-6 mb-6">
                                        <h3 class="font-outfit text-lg font-bold text-cyan-300 mb-4">Keep Your Players Engaged By:</h3>
                                    </div>

                                    <div class="grid md:grid-cols-2 gap-4">
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                            <h4 class="text-cyan-300 font-bold text-sm mb-2">Rotating the Focus</h4>
                                            <p class="text-slate-300 text-xs">Ensure all PCs get spotlight time</p>
                                        </div>
                                        
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                            <h4 class="text-cyan-300 font-bold text-sm mb-2">Tying Together Story Elements</h4>
                                            <p class="text-slate-300 text-xs">Connect plot threads and character arcs</p>
                                        </div>
                                        
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                            <h4 class="text-cyan-300 font-bold text-sm mb-2">Engaging Quiet Players</h4>
                                            <p class="text-slate-300 text-xs">Draw in less vocal participants</p>
                                        </div>
                                        
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                            <h4 class="text-cyan-300 font-bold text-sm mb-2">Using Visual Aids</h4>
                                            <p class="text-slate-300 text-xs">Enhance immersion with props and images</p>
                                        </div>
                                        
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                            <h4 class="text-cyan-300 font-bold text-sm mb-2">Encouraging Unguided Play</h4>
                                            <p class="text-slate-300 text-xs">Let players drive their own stories</p>
                                        </div>
                                        
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                            <h4 class="text-cyan-300 font-bold text-sm mb-2">Confronting with Conflicts</h4>
                                            <p class="text-slate-300 text-xs">Present internal and external challenges</p>
                                        </div>
                                        
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                            <h4 class="text-cyan-300 font-bold text-sm mb-2">Raise the Stakes</h4>
                                            <p class="text-slate-300 text-xs">Spend Fear to increase tension</p>
                                        </div>
                                        
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                            <h4 class="text-cyan-300 font-bold text-sm mb-2">Layering Goals</h4>
                                            <p class="text-slate-300 text-xs">Add objectives beyond simple attrition</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Random Objectives -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Random Combat Objectives</h2>
                                    
                                    <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-6">
                                        <h3 class="font-outfit text-lg font-bold text-yellow-600 mb-4">Table of Random Objectives</h3>
                                        <p class="text-slate-300 leading-relaxed mb-4">
                                            Use this table to add variety and complexity to combat encounters beyond simple defeat-the-enemy scenarios.
                                        </p>
                                        
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                            <p class="text-yellow-200 text-sm">
                                                <strong class="text-yellow-600">Usage:</strong> Roll 1d12 to generate a random objective that adds tactical depth and narrative interest to combat encounters.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- GM Guidance Summary -->
                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-8">
                                    <h3 class="font-outfit text-2xl font-bold text-amber-300 mb-6">Additional Guidance Summary</h3>
                                    
                                    <div class="grid md:grid-cols-2 gap-6">
                                        <div>
                                            <h4 class="font-outfit text-lg font-bold text-amber-400 mb-4">Preparation</h4>
                                            <ul class="space-y-2 text-slate-300 text-sm">
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>Plan in story beats, not scripts</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>Design combat to serve the narrative</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>Think with all senses when setting scenes</span>
                                                </li>
                                            </ul>
                                        </div>
                                        
                                        <div>
                                            <h4 class="font-outfit text-lg font-bold text-amber-400 mb-4">Execution</h4>
                                            <ul class="space-y-2 text-slate-300 text-sm">
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>Rotate focus between all players</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>Reward sessions with meaningful gains</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>Layer objectives beyond simple combat</span>
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
