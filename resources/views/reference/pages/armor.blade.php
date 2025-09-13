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
                                @include('reference.partials.navigation-menu', ['current_page' => 'armor'])
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-8">
                            <!-- Page Header -->
                            <div class="mb-8 flex items-start justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center shadow-lg">
                                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h1 class="font-outfit text-3xl font-bold text-white">Armor</h1>
                                        <p class="text-slate-400 text-sm mt-1">Protection & Damage Thresholds</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-2 ml-4 flex-shrink-0">
                                    <span class="px-3 py-1 bg-orange-500/20 text-orange-300 text-sm rounded-full">
                                        Equipment
                                    </span>
                                </div>
                            </div>

                            <!-- Page Content -->
                            <div class="prose prose-invert max-w-none" data-search-body>
                                <!-- Armor Overview -->
                                <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6 mb-8">
                                    <p class="text-blue-100 leading-relaxed text-lg">
                                        Every armor has a <strong class="text-blue-300">name</strong>, <strong class="text-blue-300">base damage thresholds</strong>, and a <strong class="text-blue-300">base Armor Score</strong>. Some armor also has a <strong class="text-blue-300">feature</strong>.
                                    </p>
                                </div>

                                <!-- Armor Properties -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Armor Properties</h2>
                                    
                                    <div class="space-y-6">
                                        <!-- Base Armor Score -->
                                        <div class="bg-cyan-500/10 border border-cyan-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-cyan-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-cyan-300 mb-3">Base Armor Score</h3>
                                                    <p class="text-slate-300 leading-relaxed mb-4">
                                                        An armor's <strong class="text-cyan-300">base armor score</strong> indicates how many Armor Slots it provides its wearer before additional bonuses are added to calculate their total Armor Score.
                                                    </p>
                                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                        <p class="text-cyan-200 text-sm">
                                                            <strong class="text-cyan-300">Maximum Armor Score:</strong> A PC's Armor Score can't exceed <strong>12</strong>.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Base Thresholds -->
                                        <div class="bg-orange-500/10 border border-orange-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-orange-300 mb-3">Base Thresholds</h3>
                                                    <p class="text-slate-300 leading-relaxed">
                                                        An armor's <strong class="text-orange-300">base thresholds</strong> determine its wearer's major and severe damage thresholds before adding bonuses to calculate their final damage thresholds.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Feature -->
                                        <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-purple-300 mb-3">Feature</h3>
                                                    <p class="text-slate-300 leading-relaxed">
                                                        An armor's <strong class="text-purple-300">feature</strong> is a special rule that stays in effect while the armor is equipped.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Unarmored Rules -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Unarmored Characters</h2>
                                    
                                    <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-8">
                                        <div class="flex items-start gap-6">
                                            <div class="w-16 h-16 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="font-outfit text-2xl font-bold text-red-300 mb-4">Unarmored Statistics</h3>
                                                <p class="text-slate-300 leading-relaxed mb-6">
                                                    While <strong class="text-red-300">unarmored</strong>, your character has the following base statistics:
                                                </p>
                                                
                                                <div class="grid md:grid-cols-3 gap-4">
                                                    <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-4 text-center">
                                                        <div class="text-red-400 font-bold text-lg mb-2">Armor Score</div>
                                                        <div class="text-slate-300 text-2xl font-bold">0</div>
                                                    </div>
                                                    
                                                    <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-4 text-center">
                                                        <div class="text-yellow-400 font-bold text-lg mb-2">Major Threshold</div>
                                                        <div class="text-slate-300 text-lg">= Character Level</div>
                                                    </div>
                                                    
                                                    <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-4 text-center">
                                                        <div class="text-orange-400 font-bold text-lg mb-2">Severe Threshold</div>
                                                        <div class="text-slate-300 text-lg">= 2 × Character Level</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Damage Reduction -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Reducing Incoming Damage</h2>
                                    
                                    <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-8">
                                        <div class="flex items-start gap-6">
                                            <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="font-outfit text-2xl font-bold text-green-300 mb-4">Armor Slot Usage</h3>
                                                <p class="text-slate-300 leading-relaxed mb-6">
                                                    When you take damage, you can <strong class="text-green-300">mark one Armor Slot</strong> to reduce the number of Hit Points you would mark by one.
                                                </p>
                                                
                                                <div class="space-y-4">
                                                    <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-6">
                                                        <h4 class="font-outfit text-lg font-bold text-red-400 mb-3">Zero Armor Score</h4>
                                                        <p class="text-slate-300 text-sm">
                                                            If your character has an <strong class="text-red-300">Armor Score of 0</strong>, you can't mark Armor Slots.
                                                        </p>
                                                    </div>
                                                    
                                                    <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-6">
                                                        <h4 class="font-outfit text-lg font-bold text-blue-400 mb-3">Temporary Armor Score</h4>
                                                        <p class="text-slate-300 text-sm">
                                                            If an effect temporarily increases your Armor Score, it increases your available Armor Slots by the same amount. When the effect ends, so does the availability of these Armor Slots.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Armor Examples -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Armor Examples</h2>
                                    
                                    <div class="grid md:grid-cols-2 gap-6">
                                        <!-- Light Armor -->
                                        <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-6">
                                            <div class="flex items-center gap-3 mb-4">
                                                <div class="w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                    </svg>
                                                </div>
                                                <h3 class="font-outfit text-lg font-bold text-yellow-600">Light Armor</h3>
                                            </div>
                                            <div class="space-y-3">
                                                <div class="flex justify-between items-center">
                                                    <span class="text-slate-300 text-sm">Armor Score:</span>
                                                    <span class="text-yellow-300 font-bold">2-4</span>
                                                </div>
                                                <div class="flex justify-between items-center">
                                                    <span class="text-slate-300 text-sm">Protection:</span>
                                                    <span class="text-yellow-300 font-bold">Moderate</span>
                                                </div>
                                                <div class="flex justify-between items-center">
                                                    <span class="text-slate-300 text-sm">Mobility:</span>
                                                    <span class="text-green-300 font-bold">High</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Heavy Armor -->
                                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6">
                                            <div class="flex items-center gap-3 mb-4">
                                                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                    </svg>
                                                </div>
                                                <h3 class="font-outfit text-lg font-bold text-blue-300">Heavy Armor</h3>
                                            </div>
                                            <div class="space-y-3">
                                                <div class="flex justify-between items-center">
                                                    <span class="text-slate-300 text-sm">Armor Score:</span>
                                                    <span class="text-blue-300 font-bold">6-12</span>
                                                </div>
                                                <div class="flex justify-between items-center">
                                                    <span class="text-slate-300 text-sm">Protection:</span>
                                                    <span class="text-blue-300 font-bold">Maximum</span>
                                                </div>
                                                <div class="flex justify-between items-center">
                                                    <span class="text-slate-300 text-sm">Mobility:</span>
                                                    <span class="text-yellow-300 font-bold">Reduced</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Armor Tables Reference -->
                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-8">
                                    <h3 class="font-outfit text-2xl font-bold text-amber-300 mb-6">Armor Tables</h3>
                                    
                                    <div class="bg-indigo-500/10 border border-indigo-500/30 rounded-lg p-6 text-center">
                                        <div class="w-12 h-12 bg-indigo-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <h4 class="font-outfit text-lg font-bold text-indigo-300 mb-3">Complete Armor Statistics</h4>
                                        <p class="text-slate-300 text-sm mb-4">Detailed armor statistics including base scores, thresholds, and special features</p>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                            <p class="text-indigo-200 text-xs">See Armor Tables for complete armor specifications across all tiers</p>
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
