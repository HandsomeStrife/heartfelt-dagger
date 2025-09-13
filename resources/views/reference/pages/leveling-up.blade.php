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
                                @include('reference.partials.navigation-menu', ['current_page' => 'leveling-up'])
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-8">
                            <!-- Page Header -->
                            <div class="mb-8 flex items-start justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 bg-gradient-to-br from-yellow-500 to-orange-500 rounded-xl flex items-center justify-center shadow-lg">
                                        <svg class="w-10 h-10 text-black" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h1 class="font-outfit text-3xl font-bold text-white">Leveling Up</h1>
                                        <p class="text-slate-400 text-sm mt-1">Character Advancement & Progression</p>
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
                                <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-6 mb-8">
                                    <p class="text-yellow-100 leading-relaxed text-lg">
                                        Your party levels up whenever the GM decides you've reached a narrative milestone (usually about every 3 sessions). <strong class="text-yellow-300">All party members level up at the same time.</strong>
                                    </p>
                                </div>

                                <!-- Tier System -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Tier System</h2>
                                    <p class="text-slate-300 leading-relaxed mb-6">
                                        Daggerheart has <strong class="text-amber-300">10 PC levels</strong> divided into <strong class="text-amber-300">4 tiers</strong>:
                                    </p>
                                    
                                    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                                        <!-- Tier 1 -->
                                        <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-6 text-center">
                                            <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-3">
                                                <span class="text-white font-bold text-lg">1</span>
                                            </div>
                                            <h3 class="font-outfit text-lg font-bold text-green-300 mb-2">Tier 1</h3>
                                            <p class="text-slate-300 text-sm">Level 1 only</p>
                                        </div>

                                        <!-- Tier 2 -->
                                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6 text-center">
                                            <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-3">
                                                <span class="text-white font-bold text-lg">2</span>
                                            </div>
                                            <h3 class="font-outfit text-lg font-bold text-blue-300 mb-2">Tier 2</h3>
                                            <p class="text-slate-300 text-sm">Levels 2–4</p>
                                        </div>

                                        <!-- Tier 3 -->
                                        <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl p-6 text-center">
                                            <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center mx-auto mb-3">
                                                <span class="text-white font-bold text-lg">3</span>
                                            </div>
                                            <h3 class="font-outfit text-lg font-bold text-purple-300 mb-2">Tier 3</h3>
                                            <p class="text-slate-300 text-sm">Levels 5–7</p>
                                        </div>

                                        <!-- Tier 4 -->
                                        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-6 text-center">
                                            <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-3">
                                                <span class="text-white font-bold text-lg">4</span>
                                            </div>
                                            <h3 class="font-outfit text-lg font-bold text-red-300 mb-2">Tier 4</h3>
                                            <p class="text-slate-300 text-sm">Levels 8–10</p>
                                        </div>
                                    </div>

                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                        <p class="text-slate-300 text-sm">
                                            Your tier affects your <strong class="text-amber-300">damage thresholds</strong>, <strong class="text-amber-300">tier achievements</strong>, and access to <strong class="text-amber-300">advancements</strong>.
                                        </p>
                                    </div>
                                </div>

                                <!-- Leveling Process -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Leveling Process</h2>
                                    
                                    <div class="space-y-8">
                                        <!-- Step One: Tier Achievements -->
                                        <div class="bg-gradient-to-r from-green-500/10 to-blue-500/10 border border-green-500/30 rounded-xl p-8">
                                            <div class="flex items-start gap-6">
                                                <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <span class="text-white font-bold text-xl">1</span>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-2xl font-bold text-green-300 mb-4">Tier Achievements</h3>
                                                    <p class="text-slate-300 leading-relaxed mb-6">
                                                        Take any applicable tier <strong class="text-green-300">achievements</strong>:
                                                    </p>
                                                    
                                                    <div class="space-y-4">
                                                        <!-- Level 2 -->
                                                        <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-6">
                                                            <div class="flex items-center gap-3 mb-3">
                                                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                                                    <span class="text-white font-bold text-sm">2</span>
                                                                </div>
                                                                <h4 class="font-outfit text-lg font-bold text-blue-300">Level 2</h4>
                                                            </div>
                                                            <ul class="space-y-2 text-slate-300 text-sm">
                                                                <li class="flex items-start gap-2">
                                                                    <span class="text-blue-400 mt-1">•</span>
                                                                    <span>Gain a new Experience at +2</span>
                                                                </li>
                                                                <li class="flex items-start gap-2">
                                                                    <span class="text-blue-400 mt-1">•</span>
                                                                    <span>Permanently increase your Proficiency by 1</span>
                                                                </li>
                                                            </ul>
                                                        </div>

                                                        <!-- Level 5 -->
                                                        <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-6">
                                                            <div class="flex items-center gap-3 mb-3">
                                                                <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                                                                    <span class="text-white font-bold text-sm">5</span>
                                                                </div>
                                                                <h4 class="font-outfit text-lg font-bold text-purple-300">Level 5</h4>
                                                            </div>
                                                            <ul class="space-y-2 text-slate-300 text-sm">
                                                                <li class="flex items-start gap-2">
                                                                    <span class="text-purple-400 mt-1">•</span>
                                                                    <span>Gain a new Experience at +2</span>
                                                                </li>
                                                                <li class="flex items-start gap-2">
                                                                    <span class="text-purple-400 mt-1">•</span>
                                                                    <span>Permanently increase your Proficiency by 1</span>
                                                                </li>
                                                                <li class="flex items-start gap-2">
                                                                    <span class="text-purple-400 mt-1">•</span>
                                                                    <span><strong class="text-purple-300">Clear any marked traits</strong></span>
                                                                </li>
                                                            </ul>
                                                        </div>

                                                        <!-- Level 8 -->
                                                        <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-6">
                                                            <div class="flex items-center gap-3 mb-3">
                                                                <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                                                                    <span class="text-white font-bold text-sm">8</span>
                                                                </div>
                                                                <h4 class="font-outfit text-lg font-bold text-red-300">Level 8</h4>
                                                            </div>
                                                            <ul class="space-y-2 text-slate-300 text-sm">
                                                                <li class="flex items-start gap-2">
                                                                    <span class="text-red-400 mt-1">•</span>
                                                                    <span>Gain a new Experience at +2</span>
                                                                </li>
                                                                <li class="flex items-start gap-2">
                                                                    <span class="text-red-400 mt-1">•</span>
                                                                    <span>Permanently increase your Proficiency by 1</span>
                                                                </li>
                                                                <li class="flex items-start gap-2">
                                                                    <span class="text-red-400 mt-1">•</span>
                                                                    <span><strong class="text-red-300">Clear any marked traits</strong></span>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Step Two: Advancements -->
                                        <div class="bg-gradient-to-r from-purple-500/10 to-pink-500/10 border border-purple-500/30 rounded-xl p-8">
                                            <div class="flex items-start gap-6">
                                                <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <span class="text-white font-bold text-xl">2</span>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-2xl font-bold text-purple-300 mb-4">Advancements</h3>
                                                    <p class="text-slate-300 leading-relaxed mb-6">
                                                        Choose any <strong class="text-purple-300">two advancements</strong> with at least one unmarked slot from your tier or below. Options with multiple slots can be chosen more than once. When you choose an advancement, mark one of its slots.
                                                    </p>
                                                    
                                                    <div class="grid md:grid-cols-2 gap-4">
                                                        <!-- Standard Advancements -->
                                                        <div class="space-y-3">
                                                            <div class="bg-slate-800/50 border border-slate-600/30 rounded-lg p-4">
                                                                <h5 class="font-outfit text-sm font-bold text-amber-400 mb-2">Increase Two Character Traits</h5>
                                                                <p class="text-slate-300 text-xs">Choose two unmarked traits and gain +1 to each. Can't increase again until next tier.</p>
                                                            </div>
                                                            
                                                            <div class="bg-slate-800/50 border border-slate-600/30 rounded-lg p-4">
                                                                <h5 class="font-outfit text-sm font-bold text-red-400 mb-2">Add Hit Point Slots</h5>
                                                                <p class="text-slate-300 text-xs">Permanently add 1 or more Hit Point slots to your character sheet.</p>
                                                            </div>
                                                            
                                                            <div class="bg-slate-800/50 border border-slate-600/30 rounded-lg p-4">
                                                                <h5 class="font-outfit text-sm font-bold text-purple-400 mb-2">Add Stress Slots</h5>
                                                                <p class="text-slate-300 text-xs">Permanently add 1 or more Stress slots to your character sheet.</p>
                                                            </div>
                                                            
                                                            <div class="bg-slate-800/50 border border-slate-600/30 rounded-lg p-4">
                                                                <h5 class="font-outfit text-sm font-bold text-green-400 mb-2">Increase Experience</h5>
                                                                <p class="text-slate-300 text-xs">Choose two Experiences and gain +1 bonus to both.</p>
                                                            </div>
                                                        </div>

                                                        <div class="space-y-3">
                                                            <div class="bg-slate-800/50 border border-slate-600/30 rounded-lg p-4">
                                                                <h5 class="font-outfit text-sm font-bold text-blue-400 mb-2">Additional Domain Card</h5>
                                                                <p class="text-slate-300 text-xs">Choose an additional domain card at or below your level from your class domains.</p>
                                                            </div>
                                                            
                                                            <div class="bg-slate-800/50 border border-slate-600/30 rounded-lg p-4">
                                                                <h5 class="font-outfit text-sm font-bold text-cyan-400 mb-2">Increase Evasion</h5>
                                                                <p class="text-slate-300 text-xs">Gain a permanent +1 bonus to your Evasion.</p>
                                                            </div>
                                                            
                                                            <div class="bg-slate-800/50 border border-slate-600/30 rounded-lg p-4">
                                                                <h5 class="font-outfit text-sm font-bold text-yellow-400 mb-2">Upgraded Subclass Card</h5>
                                                                <p class="text-slate-300 text-xs">Take the next card for your subclass (specialization or mastery). Crosses out multiclass option.</p>
                                                            </div>
                                                            
                                                            <div class="bg-slate-800/50 border border-slate-600/30 rounded-lg p-4 border-2 border-orange-500/50">
                                                                <h5 class="font-outfit text-sm font-bold text-orange-400 mb-2">Increase Proficiency ⚫</h5>
                                                                <p class="text-slate-300 text-xs">Requires 2 advancements. Fill in Proficiency circle, increase weapon damage dice by 1.</p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Multiclass Option -->
                                                    <div class="mt-6 bg-gradient-to-r from-indigo-500/10 to-purple-500/10 border-2 border-indigo-500/50 rounded-xl p-6">
                                                        <h5 class="font-outfit text-lg font-bold text-indigo-300 mb-3">Multiclass ⚫</h5>
                                                        <p class="text-slate-300 text-sm mb-4">
                                                            <strong>Requires 2 advancements.</strong> Choose an additional class, select one of its domains, and gain its class feature. Add the multiclass module and take a foundation card from one of its subclasses.
                                                        </p>
                                                        <p class="text-indigo-200 text-xs italic">
                                                            Crosses out "upgraded subclass" advancement in this tier and all other "multiclass" options.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Step Three: Damage Thresholds -->
                                        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <span class="text-white font-bold text-lg">3</span>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-red-300 mb-3">Damage Thresholds</h3>
                                                    <p class="text-slate-300 leading-relaxed">
                                                        Increase <strong class="text-red-300">all damage thresholds by 1</strong>.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Step Four: Domain Cards -->
                                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <span class="text-white font-bold text-lg">4</span>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-blue-300 mb-3">Domain Cards</h3>
                                                    <p class="text-slate-300 leading-relaxed mb-4">
                                                        Acquire a <strong class="text-blue-300">new domain card</strong> at your level or lower from one of your class's domains and add it to your loadout or vault.
                                                    </p>
                                                    <div class="space-y-3">
                                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                            <p class="text-slate-300 text-sm">
                                                                If your loadout is already full, you can't add the new card to it until you move another into your vault.
                                                            </p>
                                                        </div>
                                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                            <p class="text-slate-300 text-sm">
                                                                You can also <strong class="text-blue-300">exchange</strong> one domain card you've previously acquired for a different domain card of the same level or lower.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Level Up Summary -->
                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-8">
                                    <h3 class="font-outfit text-2xl font-bold text-amber-300 mb-6">Level Up Checklist</h3>
                                    
                                    <div class="grid md:grid-cols-2 gap-6">
                                        <div>
                                            <h4 class="font-outfit text-lg font-bold text-amber-400 mb-4">Every Level:</h4>
                                            <ul class="space-y-2 text-slate-300 text-sm">
                                                <li class="flex items-center gap-2">
                                                    <span class="text-amber-400">□</span>
                                                    <span>Take tier achievements (if applicable)</span>
                                                </li>
                                                <li class="flex items-center gap-2">
                                                    <span class="text-amber-400">□</span>
                                                    <span>Choose two advancements</span>
                                                </li>
                                                <li class="flex items-center gap-2">
                                                    <span class="text-amber-400">□</span>
                                                    <span>Increase all damage thresholds by 1</span>
                                                </li>
                                                <li class="flex items-center gap-2">
                                                    <span class="text-amber-400">□</span>
                                                    <span>Acquire new domain card</span>
                                                </li>
                                            </ul>
                                        </div>
                                        
                                        <div>
                                            <h4 class="font-outfit text-lg font-bold text-amber-400 mb-4">Key Milestones:</h4>
                                            <ul class="space-y-2 text-slate-300 text-sm">
                                                <li class="flex items-center gap-2">
                                                    <span class="text-blue-400">2</span>
                                                    <span>New Experience, +1 Proficiency</span>
                                                </li>
                                                <li class="flex items-center gap-2">
                                                    <span class="text-purple-400">5</span>
                                                    <span>New Experience, +1 Proficiency, clear traits</span>
                                                </li>
                                                <li class="flex items-center gap-2">
                                                    <span class="text-red-400">8</span>
                                                    <span>New Experience, +1 Proficiency, clear traits</span>
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
