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
                                @include('reference.partials.navigation-menu', ['current_page' => 'multiclassing'])
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
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h1 class="font-outfit text-3xl font-bold text-white">Multiclassing</h1>
                                        <p class="text-slate-400 text-sm mt-1">Combining Multiple Classes</p>
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
                                <!-- Availability -->
                                <div class="bg-indigo-500/10 border border-indigo-500/30 rounded-xl p-6 mb-8">
                                    <div class="flex items-start gap-4">
                                        <div class="w-12 h-12 bg-indigo-500 rounded-full flex items-center justify-center flex-shrink-0">
                                            <span class="text-white font-bold text-lg">5</span>
                                        </div>
                                        <div>
                                            <h3 class="font-outfit text-xl font-bold text-indigo-300 mb-3">Available Starting at Level 5</h3>
                                            <p class="text-indigo-100 leading-relaxed text-lg">
                                                Starting at <strong class="text-indigo-300">level 5</strong>, you can choose multiclassing as an option when leveling up.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- What You Gain -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">What You Gain When Multiclassing</h2>
                                    
                                    <div class="grid md:grid-cols-2 gap-6">
                                        <!-- Immediate Benefits -->
                                        <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-green-300 mb-4">Immediate Benefits</h3>
                                            <ul class="space-y-3">
                                                <li class="flex items-start gap-3">
                                                    <svg class="w-5 h-5 text-green-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <div>
                                                        <span class="text-slate-300 text-sm font-medium">Choose an additional class</span>
                                                    </div>
                                                </li>
                                                <li class="flex items-start gap-3">
                                                    <svg class="w-5 h-5 text-green-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <div>
                                                        <span class="text-slate-300 text-sm font-medium">Gain access to one of its domains</span>
                                                    </div>
                                                </li>
                                                <li class="flex items-start gap-3">
                                                    <svg class="w-5 h-5 text-green-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <div>
                                                        <span class="text-slate-300 text-sm font-medium">Acquire its class feature</span>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>

                                        <!-- Setup Requirements -->
                                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-blue-300 mb-4">Setup Requirements</h3>
                                            <ul class="space-y-3">
                                                <li class="flex items-start gap-3">
                                                    <svg class="w-5 h-5 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                    </svg>
                                                    <div>
                                                        <span class="text-slate-300 text-sm font-medium">Take the appropriate multiclass module</span>
                                                    </div>
                                                </li>
                                                <li class="flex items-start gap-3">
                                                    <svg class="w-5 h-5 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4" />
                                                    </svg>
                                                    <div>
                                                        <span class="text-slate-300 text-sm font-medium">Add it to the right side of your character sheet</span>
                                                    </div>
                                                </li>
                                                <li class="flex items-start gap-3">
                                                    <svg class="w-5 h-5 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z" />
                                                    </svg>
                                                    <div>
                                                        <span class="text-slate-300 text-sm font-medium">Choose a foundation card from one of its subclasses</span>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- Spellcast Trait -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Spellcast Trait Handling</h2>
                                    
                                    <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl p-6">
                                        <div class="flex items-start gap-4">
                                            <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="font-outfit text-xl font-bold text-purple-300 mb-3">Multiple Spellcast Traits</h3>
                                                <p class="text-slate-300 leading-relaxed">
                                                    If your foundation cards specify different <strong class="text-purple-300">Spellcast traits</strong>, you can choose which one to apply when making a Spellcast roll.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Domain Card Acquisition -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Domain Card Acquisition</h2>
                                    
                                    <div class="bg-orange-500/10 border border-orange-500/30 rounded-xl p-8">
                                        <div class="flex items-start gap-6">
                                            <div class="w-16 h-16 bg-orange-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="font-outfit text-2xl font-bold text-orange-300 mb-4">Multiclass Domain Cards</h3>
                                                <p class="text-slate-300 leading-relaxed mb-6">
                                                    Whenever you have the option to acquire a new domain card, you can choose from cards at or below <strong class="text-orange-300">half your current level</strong> (rounded up) from the domain you chose when you selected the multiclass advancement.
                                                </p>
                                                
                                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-6">
                                                    <h4 class="font-outfit text-lg font-bold text-orange-400 mb-4">Level Calculation Examples</h4>
                                                    
                                                    <div class="grid md:grid-cols-2 gap-4">
                                                        <div class="space-y-3">
                                                            <div class="bg-slate-700/30 border border-slate-600/30 rounded-lg p-3">
                                                                <div class="flex justify-between items-center">
                                                                    <span class="text-slate-300 text-sm">Character Level 5</span>
                                                                    <span class="text-orange-300 text-sm font-bold">→ Level 3 cards</span>
                                                                </div>
                                                                <p class="text-slate-400 text-xs mt-1">5 ÷ 2 = 2.5 → rounds up to 3</p>
                                                            </div>
                                                            
                                                            <div class="bg-slate-700/30 border border-slate-600/30 rounded-lg p-3">
                                                                <div class="flex justify-between items-center">
                                                                    <span class="text-slate-300 text-sm">Character Level 6</span>
                                                                    <span class="text-orange-300 text-sm font-bold">→ Level 3 cards</span>
                                                                </div>
                                                                <p class="text-slate-400 text-xs mt-1">6 ÷ 2 = 3 → stays 3</p>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="space-y-3">
                                                            <div class="bg-slate-700/30 border border-slate-600/30 rounded-lg p-3">
                                                                <div class="flex justify-between items-center">
                                                                    <span class="text-slate-300 text-sm">Character Level 7</span>
                                                                    <span class="text-orange-300 text-sm font-bold">→ Level 4 cards</span>
                                                                </div>
                                                                <p class="text-slate-400 text-xs mt-1">7 ÷ 2 = 3.5 → rounds up to 4</p>
                                                            </div>
                                                            
                                                            <div class="bg-slate-700/30 border border-slate-600/30 rounded-lg p-3">
                                                                <div class="flex justify-between items-center">
                                                                    <span class="text-slate-300 text-sm">Character Level 10</span>
                                                                    <span class="text-orange-300 text-sm font-bold">→ Level 5 cards</span>
                                                                </div>
                                                                <p class="text-slate-400 text-xs mt-1">10 ÷ 2 = 5 → stays 5</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Multiclassing Strategy -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Multiclassing Strategy</h2>
                                    
                                    <div class="grid md:grid-cols-2 gap-6">
                                        <!-- Benefits -->
                                        <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-green-300 mb-4">Benefits of Multiclassing</h3>
                                            <ul class="space-y-3 text-slate-300 text-sm">
                                                <li class="flex items-start gap-2">
                                                    <span class="text-green-400 mt-1">•</span>
                                                    <span>Access to a third domain for more versatility</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-green-400 mt-1">•</span>
                                                    <span>Additional class feature for unique abilities</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-green-400 mt-1">•</span>
                                                    <span>More subclass foundation card options</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-green-400 mt-1">•</span>
                                                    <span>Flexibility in Spellcast trait selection</span>
                                                </li>
                                            </ul>
                                        </div>

                                        <!-- Considerations -->
                                        <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-yellow-600 mb-4">Important Considerations</h3>
                                            <ul class="space-y-3 text-slate-300 text-sm">
                                                <li class="flex items-start gap-2">
                                                    <span class="text-yellow-600 mt-1">•</span>
                                                    <span>Requires 2 advancement slots (significant investment)</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-yellow-600 mt-1">•</span>
                                                    <span>Multiclass domain cards are limited to half your level</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-yellow-600 mt-1">•</span>
                                                    <span>Locks out upgraded subclass advancement in that tier</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-yellow-600 mt-1">•</span>
                                                    <span>Prevents future multiclass options</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick Reference -->
                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-8">
                                    <h3 class="font-outfit text-2xl font-bold text-amber-300 mb-6">Multiclassing Quick Reference</h3>
                                    
                                    <div class="grid md:grid-cols-3 gap-4">
                                        <div class="bg-indigo-500/10 border border-indigo-500/30 rounded-lg p-4 text-center">
                                            <div class="text-indigo-400 font-bold text-lg mb-2">Available At</div>
                                            <div class="text-slate-300 text-sm">Level 5+</div>
                                        </div>
                                        
                                        <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 text-center">
                                            <div class="text-red-400 font-bold text-lg mb-2">Cost</div>
                                            <div class="text-slate-300 text-sm">2 Advancement Slots</div>
                                        </div>
                                        
                                        <div class="bg-orange-500/10 border border-orange-500/30 rounded-lg p-4 text-center">
                                            <div class="text-orange-400 font-bold text-lg mb-2">Domain Cards</div>
                                            <div class="text-slate-300 text-sm">Half Level (rounded up)</div>
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
