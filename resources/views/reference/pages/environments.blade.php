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
                                @include('reference.partials.navigation-menu', ['current_page' => 'environments'])
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-8">
                            <!-- Page Header -->
                            <div class="mb-8 flex items-start justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-teal-500 rounded-xl flex items-center justify-center shadow-lg">
                                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h1 class="font-outfit text-3xl font-bold text-white">Environments</h1>
                                        <p class="text-slate-400 text-sm mt-1">Dynamic Locations & Scene Elements</p>
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
                                <!-- Environment Overview -->
                                <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-6 mb-8">
                                    <p class="text-green-100 leading-relaxed text-lg">
                                        <strong class="text-green-300">Environments</strong> represent everything in a scene beyond the PCs and adversaries, such as the <strong class="text-green-300">physical space</strong>, <strong class="text-green-300">background NPCs</strong>, and <strong class="text-green-300">natural forces</strong>.
                                    </p>
                                </div>

                                <!-- Environment Stat Block -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Environment Stat Block</h2>
                                    
                                    <div class="bg-teal-500/10 border border-teal-500/30 rounded-xl p-6 mb-6">
                                        <p class="text-teal-100 leading-relaxed">
                                            Each environment's <strong class="text-teal-300">stat block</strong> presents their necessary mechanical statistics:
                                        </p>
                                    </div>

                                    <div class="space-y-6">
                                        <!-- Name & Tier -->
                                        <div class="grid md:grid-cols-2 gap-6">
                                            <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6">
                                                <div class="flex items-start gap-4">
                                                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <h3 class="font-outfit text-lg font-bold text-blue-300 mb-2">Name</h3>
                                                        <p class="text-slate-300 text-sm">Unique identifier for the environment</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl p-6">
                                                <div class="flex items-start gap-4">
                                                    <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <h3 class="font-outfit text-lg font-bold text-purple-300 mb-2">Tier</h3>
                                                        <p class="text-slate-300 text-sm">PC tier the environment is designed to challenge</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Description & Impulses -->
                                        <div class="grid md:grid-cols-2 gap-6">
                                            <div class="bg-orange-500/10 border border-orange-500/30 rounded-xl p-6">
                                                <div class="flex items-start gap-4">
                                                    <div class="w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <h3 class="font-outfit text-lg font-bold text-orange-300 mb-2">Description</h3>
                                                        <p class="text-slate-300 text-sm">An evocative one-line summary of the environment</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-6">
                                                <div class="flex items-start gap-4">
                                                    <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <h3 class="font-outfit text-lg font-bold text-red-300 mb-2">Impulses</h3>
                                                        <p class="text-slate-300 text-sm">The manner with which the environment pushes and pulls people within it</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Difficulty & Features -->
                                        <div class="bg-indigo-500/10 border border-indigo-500/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-indigo-300 mb-4">Additional Components</h3>
                                            <div class="grid md:grid-cols-3 gap-4">
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <h4 class="text-indigo-200 text-sm font-bold mb-1">Difficulty</h4>
                                                    <p class="text-slate-300 text-xs">Standard Difficulty for rolls against the environment</p>
                                                </div>
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <h4 class="text-indigo-200 text-sm font-bold mb-1">Potential Adversaries</h4>
                                                    <p class="text-slate-300 text-xs">Creatures that might be found in this environment</p>
                                                </div>
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <h4 class="text-indigo-200 text-sm font-bold mb-1">Features</h4>
                                                    <p class="text-slate-300 text-xs">Special environmental elements and mechanics</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Environment Types -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Environment Types</h2>
                                    
                                    <div class="bg-cyan-500/10 border border-cyan-500/30 rounded-xl p-6 mb-6">
                                        <p class="text-cyan-100 leading-relaxed">
                                            The type of scene it most easily supports:
                                        </p>
                                    </div>

                                    <div class="grid md:grid-cols-2 gap-6">
                                        <!-- Explorations -->
                                        <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-emerald-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-emerald-300 mb-3">Explorations</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed">
                                                        Wondrous locations with mysteries and marvels to discover.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Socials -->
                                        <div class="bg-pink-500/10 border border-pink-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-pink-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-pink-300 mb-3">Socials</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed">
                                                        Locations that primarily present interpersonal challenges.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Traversals -->
                                        <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-yellow-600 mb-3">Traversals</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed">
                                                        Dangerous locations where movement through and around the space itself is a challenge.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Events -->
                                        <div class="bg-violet-500/10 border border-violet-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-violet-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-violet-300 mb-3">Events</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed">
                                                        Special activities or occurrences (rather than physical spaces).
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Adapting Environments -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Adapting Environments</h2>
                                    
                                    <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-8">
                                        <div class="flex items-start gap-6">
                                            <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="font-outfit text-2xl font-bold text-blue-300 mb-4">Flexibility & Customization</h3>
                                                <p class="text-slate-300 leading-relaxed mb-6">
                                                    Sometimes you want to use an environment but it's at the wrong tier for your party. Or you might want to replace a feature or two, then present it as an entirely different environment. Whether planning your session or even improvising an environment mid-session, you can adjust an existing environment's stat block to fit the needs of your scene or improvise elements as needed.
                                                </p>
                                                
                                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-6">
                                                    <h4 class="font-outfit text-lg font-bold text-blue-400 mb-3">Design Philosophy</h4>
                                                    <p class="text-slate-300 text-sm">
                                                        The environments framework is there to help organize ideas, <strong class="text-blue-300">not to stifle creativity</strong>.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tier Adjustment -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Tier Adjustment</h2>
                                    
                                    <div class="bg-orange-500/10 border border-orange-500/30 rounded-xl p-6">
                                        <h3 class="font-outfit text-lg font-bold text-orange-300 mb-4">Quick Tier Scaling</h3>
                                        <p class="text-slate-300 leading-relaxed mb-4">
                                            When you need to quickly adjust a stat block to a different tier, you can simply replace its existing statistics with those listed on the <strong class="text-orange-300">Environment Statistics by Tier table</strong>, using the column that corresponds to your party's tier.
                                        </p>
                                        
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                            <p class="text-orange-200 text-sm">
                                                <strong class="text-orange-300">Benchmark Statistics:</strong> See the complete Environment Statistics by Tier table for precise difficulty scaling across all four tiers.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Feature Questions -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Feature Questions</h2>
                                    
                                    <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl p-6">
                                        <h3 class="font-outfit text-lg font-bold text-purple-300 mb-4">Narrative Integration</h3>
                                        <p class="text-slate-300 leading-relaxed mb-4">
                                            Feature questions are <strong class="text-purple-300">prompts for plot hooks</strong>, narrative engines, and connections to other story elements.
                                        </p>
                                        
                                        <div class="grid md:grid-cols-2 gap-4">
                                            <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                <h4 class="text-purple-200 text-sm font-bold mb-2">Plot Hooks</h4>
                                                <p class="text-slate-300 text-xs">Questions that generate story opportunities</p>
                                            </div>
                                            <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                <h4 class="text-purple-200 text-sm font-bold mb-2">Narrative Engines</h4>
                                                <p class="text-slate-300 text-xs">Elements that drive ongoing story development</p>
                                            </div>
                                            <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                <h4 class="text-purple-200 text-sm font-bold mb-2">Story Connections</h4>
                                                <p class="text-slate-300 text-xs">Links to broader campaign elements</p>
                                            </div>
                                            <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                <h4 class="text-purple-200 text-sm font-bold mb-2">Character Ties</h4>
                                                <p class="text-slate-300 text-xs">Connections to PC backgrounds and motivations</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Environment Reference -->
                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-8">
                                    <h3 class="font-outfit text-2xl font-bold text-amber-300 mb-6">Complete Environment Collection</h3>
                                    
                                    <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-6 text-center">
                                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <h4 class="font-outfit text-lg font-bold text-green-300 mb-3">Environment Stat Blocks</h4>
                                        <p class="text-slate-300 text-sm mb-4">Complete collection of environments organized by tier and type, with full stat blocks, features, and adaptation guidance</p>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                            <p class="text-green-200 text-xs">See complete environment collections for all types: Explorations, Socials, Traversals, and Events across Tiers 1-4</p>
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
