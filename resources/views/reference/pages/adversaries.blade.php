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
                                @include('reference.partials.navigation-menu', ['current_page' => 'adversaries'])
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-8">
                            <!-- Page Header -->
                            <div class="mb-8 flex items-start justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-orange-500 rounded-xl flex items-center justify-center shadow-lg">
                                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h1 class="font-outfit text-3xl font-bold text-white">Adversaries</h1>
                                        <p class="text-slate-400 text-sm mt-1">Enemies & Stat Blocks</p>
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
                                <!-- Stat Block Overview -->
                                <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-6 mb-8">
                                    <p class="text-red-100 leading-relaxed text-lg">
                                        All the information required to run an adversary is contained in their <strong class="text-red-300">stat block</strong>. An adversary's stat block includes their name, tier, type, description, motives & tactics, and mechanical statistics.
                                    </p>
                                </div>

                                <!-- Stat Block Components -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Stat Block Components</h2>
                                    
                                    <div class="space-y-6">
                                        <!-- Name -->
                                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-blue-300 mb-3">Name</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed">
                                                        Each stat block has a unique <strong class="text-blue-300">name</strong>. Abilities that affect adversaries with a certain name include all adversaries who use that stat block, regardless of their in-story name.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Tier -->
                                        <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-green-300 mb-3">Tier</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed">
                                                        Each adversary is designed to oppose PCs of a certain <strong class="text-green-300">tier</strong>. If you confront the party with an adversary from another tier, adjust their stats.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Description -->
                                        <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-purple-300 mb-3">Description & Motives</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed mb-3">
                                                        A summary of the adversary's <strong class="text-purple-300">appearance and demeanor</strong>.
                                                    </p>
                                                    <p class="text-slate-300 text-sm leading-relaxed">
                                                        <strong class="text-purple-300">Motives & Tactics:</strong> Suggested impulses, actions and goals for the adversary.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Adversary Types -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Adversary Types</h2>
                                    
                                    <div class="bg-orange-500/10 border border-orange-500/30 rounded-xl p-6 mb-6">
                                        <p class="text-orange-100 leading-relaxed">
                                            The adversary's type appears alongside their tier. An adversary's <strong class="text-orange-300">type</strong> represents the role they play in a conflict.
                                        </p>
                                    </div>

                                    <div class="grid md:grid-cols-2 gap-4">
                                        <!-- Bruisers -->
                                        <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4">
                                            <h4 class="font-outfit text-sm font-bold text-red-300 mb-2">Bruisers</h4>
                                            <p class="text-slate-300 text-xs">Tough; deliver powerful attacks.</p>
                                        </div>

                                        <!-- Hordes -->
                                        <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-4">
                                            <h4 class="font-outfit text-sm font-bold text-yellow-600 mb-2">Hordes</h4>
                                            <p class="text-slate-300 text-xs">Groups of identical creatures acting together as a single unit.</p>
                                        </div>

                                        <!-- Leaders -->
                                        <div class="bg-purple-500/10 border border-purple-500/30 rounded-lg p-4">
                                            <h4 class="font-outfit text-sm font-bold text-purple-300 mb-2">Leaders</h4>
                                            <p class="text-slate-300 text-xs">Command and summon other adversaries.</p>
                                        </div>

                                        <!-- Minions -->
                                        <div class="bg-gray-500/10 border border-gray-500/30 rounded-lg p-4">
                                            <h4 class="font-outfit text-sm font-bold text-gray-300 mb-2">Minions</h4>
                                            <p class="text-slate-300 text-xs">Easily dispatched but dangerous in numbers.</p>
                                        </div>

                                        <!-- Ranged -->
                                        <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4">
                                            <h4 class="font-outfit text-sm font-bold text-green-300 mb-2">Ranged</h4>
                                            <p class="text-slate-300 text-xs">Fragile in close encounters but deal high damage at range.</p>
                                        </div>

                                        <!-- Skulks -->
                                        <div class="bg-indigo-500/10 border border-indigo-500/30 rounded-lg p-4">
                                            <h4 class="font-outfit text-sm font-bold text-indigo-300 mb-2">Skulks</h4>
                                            <p class="text-slate-300 text-xs">Maneuver and exploit opportunities to ambush opponents.</p>
                                        </div>

                                        <!-- Socials -->
                                        <div class="bg-pink-500/10 border border-pink-500/30 rounded-lg p-4">
                                            <h4 class="font-outfit text-sm font-bold text-pink-300 mb-2">Socials</h4>
                                            <p class="text-slate-300 text-xs">Present challenges around conversation instead of combat.</p>
                                        </div>

                                        <!-- Solos -->
                                        <div class="bg-amber-500/10 border border-amber-500/30 rounded-lg p-4">
                                            <h4 class="font-outfit text-sm font-bold text-amber-600 mb-2">Solos</h4>
                                            <p class="text-slate-300 text-xs">Present a formidable challenge to a whole party, with or without support.</p>
                                        </div>

                                        <!-- Standards -->
                                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4">
                                            <h4 class="font-outfit text-sm font-bold text-blue-300 mb-2">Standards</h4>
                                            <p class="text-slate-300 text-xs">Representative of their fictional group.</p>
                                        </div>

                                        <!-- Supports -->
                                        <div class="bg-teal-500/10 border border-teal-500/30 rounded-lg p-4">
                                            <h4 class="font-outfit text-sm font-bold text-teal-300 mb-2">Supports</h4>
                                            <p class="text-slate-300 text-xs">Enhance their allies and disrupt their opponents.</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Mechanical Statistics -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Mechanical Statistics</h2>
                                    
                                    <div class="space-y-6">
                                        <!-- Difficulty -->
                                        <div class="bg-cyan-500/10 border border-cyan-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-cyan-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-cyan-300 mb-3">Difficulty</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed">
                                                        The Difficulty of any roll made against the adversary, unless otherwise noted.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Health Systems -->
                                        <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-emerald-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-emerald-300 mb-3">Damage Thresholds, Hit Points, and Stress</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed mb-3">
                                                        These systems function the same way they do for PCs. The numbers listed after "Threshold" are the adversary's <strong class="text-emerald-300">Major and Severe Thresholds</strong>.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Attack Statistics -->
                                        <div class="bg-rose-500/10 border border-rose-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-rose-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-rose-300 mb-3">Attack Modifier & Standard Attack</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed mb-3">
                                                        <strong class="text-rose-300">Attack Modifier:</strong> When you attack with the adversary, apply this bonus or penalty to your attack roll.
                                                    </p>
                                                    <p class="text-slate-300 text-sm leading-relaxed">
                                                        <strong class="text-rose-300">Standard Attack:</strong> The adversary's basic attack with damage and any special properties.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Using Adversaries -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Using Adversaries Effectively</h2>
                                    
                                    <div class="grid md:grid-cols-2 gap-6">
                                        <!-- Tactical Considerations -->
                                        <div class="bg-indigo-500/10 border border-indigo-500/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-indigo-300 mb-4">Tactical Considerations</h3>
                                            <ul class="space-y-2 text-slate-300 text-sm">
                                                <li class="flex items-start gap-2">
                                                    <span class="text-indigo-400 mt-1">•</span>
                                                    <span>Use motives & tactics to guide behavior</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-indigo-400 mt-1">•</span>
                                                    <span>Match adversary tier to party level</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-indigo-400 mt-1">•</span>
                                                    <span>Combine different types for variety</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-indigo-400 mt-1">•</span>
                                                    <span>Consider environmental factors</span>
                                                </li>
                                            </ul>
                                        </div>

                                        <!-- Narrative Integration -->
                                        <div class="bg-violet-500/10 border border-violet-500/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-violet-300 mb-4">Narrative Integration</h3>
                                            <ul class="space-y-2 text-slate-300 text-sm">
                                                <li class="flex items-start gap-2">
                                                    <span class="text-violet-400 mt-1">•</span>
                                                    <span>Base actions on character motivations</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-violet-400 mt-1">•</span>
                                                    <span>Use description to enhance atmosphere</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-violet-400 mt-1">•</span>
                                                    <span>Make each adversary memorable</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-violet-400 mt-1">•</span>
                                                    <span>Connect to larger story themes</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- Adversary Reference -->
                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-8">
                                    <h3 class="font-outfit text-2xl font-bold text-amber-300 mb-6">Complete Adversary Collection</h3>
                                    
                                    <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-6 text-center">
                                        <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z" />
                                            </svg>
                                        </div>
                                        <h4 class="font-outfit text-lg font-bold text-red-300 mb-3">Adversary Stat Blocks</h4>
                                        <p class="text-slate-300 text-sm mb-4">Complete collection of adversaries organized by tier, with full stat blocks, abilities, and tactical guidance</p>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                            <p class="text-red-200 text-xs">See complete adversary collections for Tiers 1-4 with detailed stat blocks and encounter building guidance</p>
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
