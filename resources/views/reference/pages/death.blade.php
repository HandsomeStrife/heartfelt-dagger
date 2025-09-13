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
                                @include('reference.partials.navigation-menu', ['current_page' => 'death'])
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-8">
                            <!-- Page Header -->
                            <div class="mb-8 flex items-start justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 bg-gradient-to-br from-gray-600 to-black rounded-xl flex items-center justify-center shadow-lg">
                                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h1 class="font-outfit text-3xl font-bold text-white">Death</h1>
                                        <p class="text-slate-400 text-sm mt-1">Death Moves & Consequences</p>
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
                                <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-6 mb-8">
                                    <p class="text-red-100 leading-relaxed text-lg mb-4">
                                        When a PC marks their last Hit Point, they must make a <strong class="text-red-300">death move</strong> by choosing one of the options below.
                                    </p>
                                    <p class="text-red-200 text-sm">
                                        If your character dies, work with the GM before the next session to create a new character at the current level of the rest of the party.
                                    </p>
                                </div>

                                <!-- Death Move Options -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Death Move Options</h2>
                                    
                                    <div class="space-y-8">
                                        <!-- Blaze of Glory -->
                                        <div class="bg-gradient-to-r from-orange-500/10 to-red-500/10 border border-orange-500/30 rounded-xl p-8">
                                            <div class="flex items-start gap-6">
                                                <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-2xl font-bold text-orange-300 mb-4">Blaze of Glory</h3>
                                                    <p class="text-slate-300 leading-relaxed mb-6 text-lg">
                                                        Your character embraces death and goes out in a blaze of glory.
                                                    </p>
                                                    
                                                    <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-6">
                                                        <h4 class="font-outfit text-lg font-bold text-orange-400 mb-3">How It Works:</h4>
                                                        <div class="space-y-3">
                                                            <div class="flex items-start gap-3">
                                                                <span class="text-orange-400 font-bold text-sm mt-1">1.</span>
                                                                <p class="text-slate-300 text-sm">Take one final action</p>
                                                            </div>
                                                            <div class="flex items-start gap-3">
                                                                <span class="text-orange-400 font-bold text-sm mt-1">2.</span>
                                                                <p class="text-slate-300 text-sm">It automatically <strong class="text-orange-300">critically succeeds</strong> (with GM approval)</p>
                                                            </div>
                                                            <div class="flex items-start gap-3">
                                                                <span class="text-orange-400 font-bold text-sm mt-1">3.</span>
                                                                <p class="text-slate-300 text-sm">Then you cross through the veil of death</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Avoid Death -->
                                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-8">
                                            <div class="flex items-start gap-6">
                                                <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-2xl font-bold text-blue-300 mb-4">Avoid Death</h3>
                                                    <p class="text-slate-300 leading-relaxed mb-6 text-lg">
                                                        Your character avoids death and faces the consequences.
                                                    </p>
                                                    
                                                    <div class="space-y-6">
                                                        <!-- Immediate Effects -->
                                                        <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-6">
                                                            <h4 class="font-outfit text-lg font-bold text-blue-400 mb-3">Immediate Effects:</h4>
                                                            <div class="space-y-3">
                                                                <div class="flex items-start gap-3">
                                                                    <span class="text-blue-400 font-bold text-sm mt-1">•</span>
                                                                    <p class="text-slate-300 text-sm">They temporarily drop unconscious</p>
                                                                </div>
                                                                <div class="flex items-start gap-3">
                                                                    <span class="text-blue-400 font-bold text-sm mt-1">•</span>
                                                                    <p class="text-slate-300 text-sm">Work with the GM to describe how the situation worsens</p>
                                                                </div>
                                                                <div class="flex items-start gap-3">
                                                                    <span class="text-blue-400 font-bold text-sm mt-1">•</span>
                                                                    <p class="text-slate-300 text-sm">While unconscious: can't move or act, can't be targeted by attacks</p>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Recovery -->
                                                        <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-6">
                                                            <h4 class="font-outfit text-lg font-bold text-green-400 mb-3">Recovery:</h4>
                                                            <p class="text-slate-300 text-sm mb-3">They return to consciousness when:</p>
                                                            <div class="space-y-2">
                                                                <div class="flex items-start gap-3">
                                                                    <span class="text-green-400 font-bold text-sm mt-1">•</span>
                                                                    <p class="text-slate-300 text-sm">An ally clears 1 or more of their marked Hit Points, OR</p>
                                                                </div>
                                                                <div class="flex items-start gap-3">
                                                                    <span class="text-green-400 font-bold text-sm mt-1">•</span>
                                                                    <p class="text-slate-300 text-sm">The party finishes a long rest</p>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Scar System -->
                                                        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-6">
                                                            <h4 class="font-outfit text-lg font-bold text-red-400 mb-3">Scar System:</h4>
                                                            <div class="space-y-3">
                                                                <p class="text-slate-300 text-sm">After your character falls unconscious, roll your Hope Die:</p>
                                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                                    <p class="text-slate-300 text-sm mb-2">
                                                                        <strong class="text-red-300">If Hope Die ≤ Character Level:</strong> They gain a scar
                                                                    </p>
                                                                    <ul class="space-y-1 text-slate-400 text-xs ml-4">
                                                                        <li>• Permanently cross out a Hope slot</li>
                                                                        <li>• Work with GM to determine lasting narrative impact</li>
                                                                        <li>• Determine how (if possible) it can be restored</li>
                                                                    </ul>
                                                                </div>
                                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                                    <p class="text-red-200 text-sm font-medium">
                                                                        <strong>Critical:</strong> If you ever cross out your last Hope slot, your character's journey ends.
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Risk It All -->
                                        <div class="bg-gradient-to-r from-purple-500/10 to-pink-500/10 border border-purple-500/30 rounded-xl p-8">
                                            <div class="flex items-start gap-6">
                                                <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-2xl font-bold text-purple-300 mb-4">Risk It All</h3>
                                                    <p class="text-slate-300 leading-relaxed mb-6 text-lg">
                                                        Roll your Duality Dice and let fate decide your character's destiny.
                                                    </p>
                                                    
                                                    <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-6">
                                                        <h4 class="font-outfit text-lg font-bold text-purple-400 mb-4">Possible Outcomes:</h4>
                                                        
                                                        <div class="space-y-4">
                                                            <!-- Hope Die Higher -->
                                                            <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4">
                                                                <div class="flex items-center gap-3 mb-2">
                                                                    <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 24 24">
                                                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                                    </svg>
                                                                    <h5 class="font-outfit text-sm font-bold text-green-300">Hope Die Higher</h5>
                                                                </div>
                                                                <p class="text-slate-300 text-sm">Your character stays on their feet and clears a number of Hit Points or Stress equal to the Hope Die value (you can divide between HP and Stress however you prefer).</p>
                                                            </div>

                                                            <!-- Fear Die Higher -->
                                                            <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4">
                                                                <div class="flex items-center gap-3 mb-2">
                                                                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                                                    </svg>
                                                                    <h5 class="font-outfit text-sm font-bold text-red-300">Fear Die Higher</h5>
                                                                </div>
                                                                <p class="text-slate-300 text-sm">Your character crosses through the veil of death.</p>
                                                            </div>

                                                            <!-- Matching Results -->
                                                            <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-4">
                                                                <div class="flex items-center gap-3 mb-2">
                                                                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                                                    </svg>
                                                                    <h5 class="font-outfit text-sm font-bold text-yellow-600">Matching Results</h5>
                                                                </div>
                                                                <p class="text-slate-300 text-sm">Your character stays up and clears nothing.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Death Move Summary -->
                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-8">
                                    <h3 class="font-outfit text-2xl font-bold text-amber-300 mb-6">Death Move Quick Reference</h3>
                                    
                                    <div class="grid md:grid-cols-3 gap-6">
                                        <div class="bg-orange-500/10 border border-orange-500/30 rounded-lg p-4 text-center">
                                            <div class="text-orange-400 font-bold text-lg mb-2">Blaze of Glory</div>
                                            <div class="text-slate-300 text-sm mb-2">One final critical action</div>
                                            <div class="text-orange-200 text-xs italic">Then death</div>
                                        </div>
                                        
                                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4 text-center">
                                            <div class="text-blue-400 font-bold text-lg mb-2">Avoid Death</div>
                                            <div class="text-slate-300 text-sm mb-2">Unconscious + consequences</div>
                                            <div class="text-blue-200 text-xs italic">Possible scar</div>
                                        </div>
                                        
                                        <div class="bg-purple-500/10 border border-purple-500/30 rounded-lg p-4 text-center">
                                            <div class="text-purple-400 font-bold text-lg mb-2">Risk It All</div>
                                            <div class="text-slate-300 text-sm mb-2">Roll Duality Dice</div>
                                            <div class="text-purple-200 text-xs italic">Hope = recover, Fear = death</div>
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
