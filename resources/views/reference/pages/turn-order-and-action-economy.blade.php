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
                                @include('reference.partials.navigation-menu', ['current_page' => 'turn-order-and-action-economy'])
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-8">
                            <!-- Page Header -->
                            <div class="mb-8 flex items-start justify-between">
                                <h1 class="font-outfit text-3xl font-bold text-white">
                                    Turn Order & Action Economy
                                </h1>
                                
                                <div class="flex items-center space-x-2 ml-4 flex-shrink-0">
                                    <span class="px-3 py-1 bg-blue-500/20 text-blue-300 text-sm rounded-full">
                                        Core Mechanics
                                    </span>
                                </div>
                            </div>

                            <!-- Page Content -->
                            <div class="prose prose-invert max-w-none" data-search-body>
                                <!-- Main Concept -->
                                <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6 mb-8">
                                    <p class="text-blue-100 leading-relaxed text-lg">
                                        Daggerheart's turns don't follow a traditional, rigid format; there is no explicit initiative mechanic and characters don't have a set number of actions they can take or things they can do before the spotlight passes to someone else.
                                    </p>
                                </div>

                                <!-- How Spotlight Moves -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">How the Spotlight Moves</h2>
                                    <p class="text-slate-300 leading-relaxed mb-6">
                                        A player with the spotlight describes what their character does and the spotlight simply swings to whoever:
                                    </p>

                                    <div class="space-y-4">
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="bg-amber-500 text-black rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold flex-shrink-0 mt-1">
                                                    1
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">The Fiction Would Naturally Turn It Toward</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed">
                                                        The narrative flow and story logic determines who should act next based on what just happened.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="bg-amber-500 text-black rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold flex-shrink-0 mt-1">
                                                    2
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Hasn't Had the Focus in a While</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed">
                                                        Players who haven't been in the spotlight recently get priority to ensure everyone participates.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="bg-amber-500 text-black rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold flex-shrink-0 mt-1">
                                                    3
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">A Triggered Mechanic Puts It On</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed">
                                                        Specific game mechanics (like failed rolls) can determine who gets the spotlight next.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Optional Rule -->
                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-8">
                                    <div class="flex items-start gap-4 mb-6">
                                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-500 rounded-xl flex items-center justify-center flex-shrink-0">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="font-outfit text-2xl font-bold text-purple-300 mb-4">Optional: Spotlight Tracker Tool</h3>
                                            <p class="text-slate-300 leading-relaxed mb-6">
                                                If your group prefers a more traditional action economy, you can use tokens to track how many times a player has had the spotlight:
                                            </p>
                                        </div>
                                    </div>

                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                        <h4 class="font-outfit text-lg font-bold text-purple-400 mb-4">How It Works:</h4>
                                        <div class="space-y-4">
                                            <div class="flex items-start gap-3">
                                                <span class="text-purple-400 font-bold text-sm mt-1">1.</span>
                                                <p class="text-slate-300 text-sm leading-relaxed">
                                                    At the start of a session or scene, each player adds a certain number of tokens (we recommend 3) to their character sheet.
                                                </p>
                                            </div>
                                            <div class="flex items-start gap-3">
                                                <span class="text-purple-400 font-bold text-sm mt-1">2.</span>
                                                <p class="text-slate-300 text-sm leading-relaxed">
                                                    Players remove a token each time they take an action.
                                                </p>
                                            </div>
                                            <div class="flex items-start gap-3">
                                                <span class="text-purple-400 font-bold text-sm mt-1">3.</span>
                                                <p class="text-slate-300 text-sm leading-relaxed">
                                                    If the spotlight would swing to someone without any tokens, it swings to someone else instead.
                                                </p>
                                            </div>
                                            <div class="flex items-start gap-3">
                                                <span class="text-purple-400 font-bold text-sm mt-1">4.</span>
                                                <p class="text-slate-300 text-sm leading-relaxed">
                                                    Once every player has used all their available tokens, players refill their character sheet with the same number of tokens as before, then continue playing.
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
</x-layouts.app>
