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
                                @include('reference.partials.navigation-menu', ['current_page' => 'flow-of-the-game'])
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-8">
                            <!-- Page Header -->
                            <div class="mb-8 flex items-start justify-between">
                                <h1 class="font-outfit text-3xl font-bold text-white">
                                    Flow of the Game
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
                                    Daggerheart is a conversation. The GM describes fictional scenarios involving the PCs, and the players take turns describing how their characters react. The goal of every person at the table is to build upon everyone else's ideas and collaboratively tell a satisfying story. The system facilitates this collaborative process by providing structure to the conversation and mechanics for resolving moments of tension where fate or fortune determine the outcome of events.
                                </p>

                                <!-- Player Principles -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Player Principles & Best Practices</h2>
                                    <p class="text-slate-300 leading-relaxed mb-8">
                                        To get the most out of Daggerheart, we recommend players keep the following principles and practices in mind throughout each session:
                                    </p>

                                    <div class="grid md:grid-cols-2 gap-8">
                                        <!-- Principles -->
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-xl font-bold text-amber-300 mb-4 flex items-center">
                                                <svg class="w-6 h-6 mr-2 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                                </svg>
                                                Principles
                                            </h3>
                                            <ul class="space-y-3">
                                                <li class="flex items-start">
                                                    <span class="text-amber-400 mr-2 mt-1">•</span>
                                                    <span class="text-slate-300 text-sm">Be a fan of your character and their journey.</span>
                                                </li>
                                                <li class="flex items-start">
                                                    <span class="text-amber-400 mr-2 mt-1">•</span>
                                                    <span class="text-slate-300 text-sm">Spotlight your friends.</span>
                                                </li>
                                                <li class="flex items-start">
                                                    <span class="text-amber-400 mr-2 mt-1">•</span>
                                                    <span class="text-slate-300 text-sm">Address the characters and address the players.</span>
                                                </li>
                                                <li class="flex items-start">
                                                    <span class="text-amber-400 mr-2 mt-1">•</span>
                                                    <span class="text-slate-300 text-sm">Build the world together.</span>
                                                </li>
                                                <li class="flex items-start">
                                                    <span class="text-amber-400 mr-2 mt-1">•</span>
                                                    <span class="text-slate-300 text-sm">Play to find out what happens.</span>
                                                </li>
                                                <li class="flex items-start">
                                                    <span class="text-amber-400 mr-2 mt-1">•</span>
                                                    <span class="text-slate-300 text-sm">Hold on gently.</span>
                                                </li>
                                            </ul>
                                        </div>

                                        <!-- Best Practices -->
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-xl font-bold text-amber-300 mb-4 flex items-center">
                                                <svg class="w-6 h-6 mr-2 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                </svg>
                                                Best Practices
                                            </h3>
                                            <ul class="space-y-3">
                                                <li class="flex items-start">
                                                    <span class="text-amber-400 mr-2 mt-1">•</span>
                                                    <span class="text-slate-300 text-sm">Embrace danger.</span>
                                                </li>
                                                <li class="flex items-start">
                                                    <span class="text-amber-400 mr-2 mt-1">•</span>
                                                    <span class="text-slate-300 text-sm">Use your resources.</span>
                                                </li>
                                                <li class="flex items-start">
                                                    <span class="text-amber-400 mr-2 mt-1">•</span>
                                                    <span class="text-slate-300 text-sm">Tell the story.</span>
                                                </li>
                                                <li class="flex items-start">
                                                    <span class="text-amber-400 mr-2 mt-1">•</span>
                                                    <span class="text-slate-300 text-sm">Discover your character.</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reference Note -->
                                <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6">
                                    <p class="text-blue-100 leading-relaxed text-sm">
                                        <strong>For more information:</strong> See the Daggerheart Core Rulebook, pages 9 and 108.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
