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
                                @include('reference.partials.navigation-menu', ['current_page' => 'core-gameplay-loop'])
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-8">
                            <!-- Page Header -->
                            <div class="mb-8 flex items-start justify-between">
                                <h1 class="font-outfit text-3xl font-bold text-white">
                                    Core Gameplay Loop
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
                                    The core gameplay loop is the procedure that drives every scene, both in and out of combat:
                                </p>

                                <!-- The Four Steps -->
                                <div class="space-y-8">
                                    <!-- Step 1 -->
                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                        <div class="flex items-start gap-4">
                                            <div class="bg-amber-500 text-black rounded-full w-10 h-10 flex items-center justify-center text-lg font-bold flex-shrink-0">
                                                1
                                            </div>
                                            <div>
                                                <h2 class="font-outfit text-xl font-bold text-amber-400 mb-3">Set the Scene</h2>
                                                <p class="text-slate-300 leading-relaxed">
                                                    The GM describes a scenario, establishing the PCs' surroundings and any dangers, NPCs, or other important details the characters would notice.
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
                                                <h2 class="font-outfit text-xl font-bold text-amber-400 mb-3">Ask and Answer Questions</h2>
                                                <p class="text-slate-300 leading-relaxed mb-4">
                                                    The players ask clarifying questions to explore the scene more deeply and gather information that could inform their characters' actions. The GM responds to these questions by giving the players information their characters could easily obtain, or by asking questions of their own to the players.
                                                </p>
                                                <p class="text-slate-300 leading-relaxed">
                                                    The players also respond to any questions the GM poses to them. In this way, the table builds out the fiction collaboratively.
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
                                                <h2 class="font-outfit text-xl font-bold text-amber-400 mb-3">Build on the Fiction</h2>
                                                <p class="text-slate-300 leading-relaxed mb-4">
                                                    As the scene develops, the players find opportunities to take action—problems to solve, obstacles to overcome, mysteries to investigate, and so on. The players describe how their characters proceed; if their proposed actions carry no chance of failure (or if failure would be boring), they automatically succeed.
                                                </p>
                                                <p class="text-slate-300 leading-relaxed">
                                                    But if the outcome of their action is unknown, the GM calls for an action roll. Either way, the table works the outcome into the story and moves the fiction forward, narrating how the PC's actions have changed things.
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
                                                <h2 class="font-outfit text-xl font-bold text-amber-400 mb-3">Go Back to Step 1</h2>
                                                <p class="text-slate-300 leading-relaxed">
                                                    The process repeats from the beginning, with the GM relaying any updated details or material changes to the players. This process continues until the end of the scene is triggered by a mechanic or arrives organically.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Visual Flow Diagram -->
                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-8 mt-12">
                                    <h3 class="font-outfit text-2xl font-bold text-amber-300 mb-6 text-center">The Gameplay Loop</h3>
                                    <div class="flex items-center justify-center">
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 items-center">
                                            <div class="text-center">
                                                <div class="bg-amber-500 text-black rounded-full w-12 h-12 flex items-center justify-center text-lg font-bold mx-auto mb-2">1</div>
                                                <p class="text-slate-300 text-sm font-medium">Set Scene</p>
                                            </div>
                                            <div class="hidden md:flex justify-center">
                                                <svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </div>
                                            <div class="text-center">
                                                <div class="bg-amber-500 text-black rounded-full w-12 h-12 flex items-center justify-center text-lg font-bold mx-auto mb-2">2</div>
                                                <p class="text-slate-300 text-sm font-medium">Ask Questions</p>
                                            </div>
                                            <div class="hidden md:flex justify-center">
                                                <svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </div>
                                            <div class="text-center">
                                                <div class="bg-amber-500 text-black rounded-full w-12 h-12 flex items-center justify-center text-lg font-bold mx-auto mb-2">3</div>
                                                <p class="text-slate-300 text-sm font-medium">Build Fiction</p>
                                            </div>
                                            <div class="hidden md:flex justify-center">
                                                <svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </div>
                                            <div class="text-center">
                                                <div class="bg-amber-500 text-black rounded-full w-12 h-12 flex items-center justify-center text-lg font-bold mx-auto mb-2">4</div>
                                                <p class="text-slate-300 text-sm font-medium">Repeat</p>
                                            </div>
                                            <div class="hidden md:flex justify-center">
                                                <svg class="w-8 h-8 text-amber-400 transform rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
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
