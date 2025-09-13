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
                                @include('reference.partials.navigation-menu', ['current_page' => 'communities'])
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-8">
                            <!-- Page Header -->
                            <div class="mb-8 flex items-start justify-between">
                                <h1 class="font-outfit text-3xl font-bold text-white">
                                    Communities
                                </h1>
                                
                                <div class="flex items-center space-x-2 ml-4 flex-shrink-0">
                                    <span class="px-3 py-1 bg-emerald-500/20 text-emerald-300 text-sm rounded-full">
                                        Core Materials
                                    </span>
                                </div>
                            </div>

                            <!-- Page Content -->
                            <div class="prose prose-invert max-w-none" data-search-body>
                                <p class="text-slate-300 leading-relaxed mb-8">
                                    Communities represent a key aspect of the <strong class="text-amber-300">culture, class, or environment of origin</strong> that has had the most influence over your character's upbringing.
                                </p>

                                <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl p-6 mb-8">
                                    <p class="text-emerald-100 leading-relaxed">
                                        Your character's community grants them a <strong>community feature</strong>. Each community card also lists six adjectives you can use as inspiration to create your character's personality, their relationship to their peers, their attitude toward their upbringing, or the demeanor with which they interact with the rest of the party.
                                    </p>
                                </div>

                                <!-- Available Communities -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">The 9 Communities</h2>
                                    
                                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6 hover:bg-slate-800/50 transition-colors">
                                            <div class="text-center">
                                                <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-700 rounded-xl flex items-center justify-center mx-auto mb-4">
                                                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 2L2 7v10c0 5.55 3.84 9.74 9 11 5.16-1.26 9-5.45 9-11V7l-10-5z"/>
                                                    </svg>
                                                </div>
                                                <h3 class="font-outfit text-xl font-bold text-amber-300 mb-2">Highborne</h3>
                                                <p class="text-slate-400 text-sm">Wealthy, privileged, court-trained</p>
                                            </div>
                                        </div>

                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6 hover:bg-slate-800/50 transition-colors">
                                            <div class="text-center">
                                                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl flex items-center justify-center mx-auto mb-4">
                                                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                    </svg>
                                                </div>
                                                <h3 class="font-outfit text-xl font-bold text-amber-300 mb-2">Loreborne</h3>
                                                <p class="text-slate-400 text-sm">Academic, scholarly, knowledge-seeking</p>
                                            </div>
                                        </div>

                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6 hover:bg-slate-800/50 transition-colors">
                                            <div class="text-center">
                                                <div class="w-16 h-16 bg-gradient-to-br from-yellow-500 to-yellow-700 rounded-xl flex items-center justify-center mx-auto mb-4">
                                                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                                    </svg>
                                                </div>
                                                <h3 class="font-outfit text-xl font-bold text-amber-300 mb-2">Orderborne</h3>
                                                <p class="text-slate-400 text-sm">Disciplined, faith-based, structured</p>
                                            </div>
                                        </div>

                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6 hover:bg-slate-800/50 transition-colors">
                                            <div class="text-center">
                                                <div class="w-16 h-16 bg-gradient-to-br from-gray-500 to-gray-700 rounded-xl flex items-center justify-center mx-auto mb-4">
                                                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M14 6l-3.75 5 2.85 3.8-1.6 1.2C9.81 13.75 7 10 7 10l-6 8h22L14 6z"/>
                                                    </svg>
                                                </div>
                                                <h3 class="font-outfit text-xl font-bold text-amber-300 mb-2">Ridgeborne</h3>
                                                <p class="text-slate-400 text-sm">Mountain-dwelling, sturdy, practical</p>
                                            </div>
                                        </div>

                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6 hover:bg-slate-800/50 transition-colors">
                                            <div class="text-center">
                                                <div class="w-16 h-16 bg-gradient-to-br from-cyan-500 to-cyan-700 rounded-xl flex items-center justify-center mx-auto mb-4">
                                                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M3.5 18.49l6-6.01 4 4L22 6.92l-1.41-1.41-7.09 7.97-4-4L2 16.99z"/>
                                                    </svg>
                                                </div>
                                                <h3 class="font-outfit text-xl font-bold text-amber-300 mb-2">Seaborne</h3>
                                                <p class="text-slate-400 text-sm">Ocean-dwelling, adaptable, trading</p>
                                            </div>
                                        </div>

                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6 hover:bg-slate-800/50 transition-colors">
                                            <div class="text-center">
                                                <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-xl flex items-center justify-center mx-auto mb-4">
                                                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                    </svg>
                                                </div>
                                                <h3 class="font-outfit text-xl font-bold text-amber-300 mb-2">Slyborne</h3>
                                                <p class="text-slate-400 text-sm">Urban, cunning, street-smart</p>
                                            </div>
                                        </div>

                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6 hover:bg-slate-800/50 transition-colors">
                                            <div class="text-center">
                                                <div class="w-16 h-16 bg-gradient-to-br from-slate-500 to-slate-700 rounded-xl flex items-center justify-center mx-auto mb-4">
                                                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                                    </svg>
                                                </div>
                                                <h3 class="font-outfit text-xl font-bold text-amber-300 mb-2">Underborne</h3>
                                                <p class="text-slate-400 text-sm">Underground-dwelling, secretive</p>
                                            </div>
                                        </div>

                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6 hover:bg-slate-800/50 transition-colors">
                                            <div class="text-center">
                                                <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-orange-700 rounded-xl flex items-center justify-center mx-auto mb-4">
                                                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                    </svg>
                                                </div>
                                                <h3 class="font-outfit text-xl font-bold text-amber-300 mb-2">Wanderborne</h3>
                                                <p class="text-slate-400 text-sm">Nomadic, traveling, adaptable</p>
                                            </div>
                                        </div>

                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6 hover:bg-slate-800/50 transition-colors">
                                            <div class="text-center">
                                                <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-700 rounded-xl flex items-center justify-center mx-auto mb-4">
                                                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                    </svg>
                                                </div>
                                                <h3 class="font-outfit text-xl font-bold text-amber-300 mb-2">Wildborne</h3>
                                                <p class="text-slate-400 text-sm">Wilderness-dwelling, survival-focused</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Community Features -->
                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-8">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-300 mb-6">Community Features</h2>
                                    <p class="text-slate-300 leading-relaxed mb-6">
                                        Each community provides a unique <strong class="text-amber-300">community feature</strong> that reflects the skills, knowledge, or connections your character gained from their upbringing.
                                    </p>
                                    
                                    <div class="grid md:grid-cols-2 gap-6">
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-amber-400 mb-3">Mechanical Benefits</h3>
                                            <p class="text-slate-300 text-sm leading-relaxed">
                                                Community features provide specific mechanical advantages that can be used during gameplay, such as special abilities, bonuses to certain actions, or access to unique resources.
                                            </p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-amber-400 mb-3">Personality Inspiration</h3>
                                            <p class="text-slate-300 text-sm leading-relaxed">
                                                Each community card lists six adjectives that can inspire your character's personality, relationships with peers, attitude toward their upbringing, or interaction style with the party.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl p-6 mt-6">
                                        <p class="text-emerald-100 leading-relaxed">
                                            <strong>Remember:</strong> Your community represents the most influential aspect of your character's cultural background. Work with your GM to determine how your character's community shaped their worldview, skills, and connections.
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
</x-layouts.app>
