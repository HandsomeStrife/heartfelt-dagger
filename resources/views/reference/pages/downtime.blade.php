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
                                @include('reference.partials.navigation-menu', ['current_page' => 'downtime'])
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-8">
                            <!-- Page Header -->
                            <div class="mb-8 flex items-start justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-blue-500 rounded-xl flex items-center justify-center shadow-lg">
                                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h1 class="font-outfit text-3xl font-bold text-white">Downtime</h1>
                                        <p class="text-slate-400 text-sm mt-1">Rest & Recovery</p>
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
                                <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-6 mb-8">
                                    <p class="text-green-100 leading-relaxed text-lg">
                                        Between conflicts, the party can take a <strong class="text-green-300">rest</strong> to recover expended resources and deepen their bonds. During a rest, each PC can make up to <strong class="text-green-300">two downtime moves</strong>.
                                    </p>
                                </div>

                                <!-- Rest Types & Rules -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Rest Types & Rules</h2>
                                    
                                    <div class="space-y-6">
                                        <!-- Rest Choice -->
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-4">Choosing Your Rest</h3>
                                            <p class="text-slate-300 leading-relaxed mb-4">
                                                When the party rests, they must choose between a short rest and a long rest.
                                            </p>
                                            <div class="bg-orange-500/10 border border-orange-500/30 rounded-lg p-4">
                                                <p class="text-orange-200 text-sm font-medium">
                                                    <strong>Important:</strong> If a party takes three short rests in a row, their next rest must be a long rest.
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Interruption Rules -->
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-4">Interruption Rules</h3>
                                            <div class="grid md:grid-cols-2 gap-4">
                                                <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4">
                                                    <h4 class="font-outfit text-sm font-bold text-red-300 mb-2">Short Rest Interrupted</h4>
                                                    <p class="text-slate-300 text-sm">Characters don't gain its benefits.</p>
                                                </div>
                                                <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-4">
                                                    <h4 class="font-outfit text-sm font-bold text-yellow-600 mb-2">Long Rest Interrupted</h4>
                                                    <p class="text-slate-300 text-sm">Characters only gain the benefits of a short rest.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Short Rest -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Short Rest</h2>
                                    
                                    <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6 mb-6">
                                        <div class="flex items-start gap-4">
                                            <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="font-outfit text-xl font-bold text-blue-300 mb-3">Duration & Setup</h3>
                                                <p class="text-slate-300 leading-relaxed mb-4">
                                                    A <strong class="text-blue-300">short rest</strong> lasts enough time for the party to catch its breath, about <strong>an hour in-world</strong>.
                                                </p>
                                                <p class="text-slate-300 leading-relaxed">
                                                    Each player can move domain cards between their loadout and vault for free, then choose <strong class="text-blue-300">twice</strong> from the following list of downtime moves (players can choose the same move twice):
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                                        <!-- Tend to Wounds -->
                                        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-6">
                                            <div class="flex items-center gap-3 mb-3">
                                                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                                </svg>
                                                <h4 class="font-outfit text-lg font-bold text-red-300">Tend to Wounds</h4>
                                            </div>
                                            <p class="text-slate-300 text-sm">Clear <strong class="text-red-300">1d4+Tier</strong> Hit Points for yourself or an ally.</p>
                                        </div>

                                        <!-- Clear Stress -->
                                        <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl p-6">
                                            <div class="flex items-center gap-3 mb-3">
                                                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                                </svg>
                                                <h4 class="font-outfit text-lg font-bold text-purple-300">Clear Stress</h4>
                                            </div>
                                            <p class="text-slate-300 text-sm">Clear <strong class="text-purple-300">1d4+Tier</strong> Stress.</p>
                                        </div>

                                        <!-- Repair Armor -->
                                        <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-6">
                                            <div class="flex items-center gap-3 mb-3">
                                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                </svg>
                                                <h4 class="font-outfit text-lg font-bold text-yellow-600">Repair Armor</h4>
                                            </div>
                                            <p class="text-slate-300 text-sm">Clear <strong class="text-yellow-600">1d4+Tier</strong> Armor Slots from your or an ally's armor.</p>
                                        </div>

                                        <!-- Prepare -->
                                        <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-6">
                                            <div class="flex items-center gap-3 mb-3">
                                                <svg class="w-6 h-6 text-green-400" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                </svg>
                                                <h4 class="font-outfit text-lg font-bold text-green-300">Prepare</h4>
                                            </div>
                                            <p class="text-slate-300 text-sm mb-2">Describe how you prepare yourself for the path ahead, then gain <strong class="text-green-300">a Hope</strong>.</p>
                                            <p class="text-green-200 text-xs italic">If you choose to Prepare with one or more members of your party, you each gain <strong>2 Hope</strong>.</p>
                                        </div>
                                    </div>

                                    <!-- Short Rest End Effects -->
                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                        <h4 class="font-outfit text-lg font-bold text-blue-300 mb-3">At the End of a Short Rest:</h4>
                                        <ul class="space-y-2 text-slate-300 text-sm">
                                            <li class="flex items-start gap-2">
                                                <span class="text-blue-400 mt-1">•</span>
                                                <span>Any features or effects with a limited number of uses per rest refresh</span>
                                            </li>
                                            <li class="flex items-start gap-2">
                                                <span class="text-blue-400 mt-1">•</span>
                                                <span>Any features or effects that last until your next rest expire</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Long Rest -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Long Rest</h2>
                                    
                                    <div class="bg-indigo-500/10 border border-indigo-500/30 rounded-xl p-6 mb-6">
                                        <div class="flex items-start gap-4">
                                            <div class="w-12 h-12 bg-indigo-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="font-outfit text-xl font-bold text-indigo-300 mb-3">Duration & Setup</h3>
                                                <p class="text-slate-300 leading-relaxed mb-4">
                                                    A <strong class="text-indigo-300">long rest</strong> is when the characters make camp and relax or sleep for <strong>several in-game hours</strong>.
                                                </p>
                                                <p class="text-slate-300 leading-relaxed">
                                                    Each player can move domain cards between their loadout and vault for free, then choose <strong class="text-indigo-300">twice</strong> from the following list of downtime moves (players can choose the same move twice):
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                                        <!-- Tend to All Wounds -->
                                        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-4">
                                            <div class="flex items-center gap-2 mb-2">
                                                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                                </svg>
                                                <h4 class="font-outfit text-sm font-bold text-red-300">Tend to All Wounds</h4>
                                            </div>
                                            <p class="text-slate-300 text-xs">Clear <strong class="text-red-300">all</strong> Hit Points for yourself or an ally.</p>
                                        </div>

                                        <!-- Clear All Stress -->
                                        <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl p-4">
                                            <div class="flex items-center gap-2 mb-2">
                                                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                                </svg>
                                                <h4 class="font-outfit text-sm font-bold text-purple-300">Clear All Stress</h4>
                                            </div>
                                            <p class="text-slate-300 text-xs">Clear <strong class="text-purple-300">all</strong> Stress.</p>
                                        </div>

                                        <!-- Repair All Armor -->
                                        <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-4">
                                            <div class="flex items-center gap-2 mb-2">
                                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                </svg>
                                                <h4 class="font-outfit text-sm font-bold text-yellow-600">Repair All Armor</h4>
                                            </div>
                                            <p class="text-slate-300 text-xs">Clear <strong class="text-yellow-600">all</strong> Armor Slots from your or an ally's armor.</p>
                                        </div>

                                        <!-- Prepare -->
                                        <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-4">
                                            <div class="flex items-center gap-2 mb-2">
                                                <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                </svg>
                                                <h4 class="font-outfit text-sm font-bold text-green-300">Prepare</h4>
                                            </div>
                                            <p class="text-slate-300 text-xs mb-1">Describe how you prepare for the next day's adventure, then gain <strong class="text-green-300">a Hope</strong>.</p>
                                            <p class="text-green-200 text-xs italic">Group preparation grants <strong>2 Hope</strong> each.</p>
                                        </div>

                                        <!-- Work on a Project -->
                                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-4 md:col-span-2 lg:col-span-1">
                                            <div class="flex items-center gap-2 mb-2">
                                                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z" />
                                                </svg>
                                                <h4 class="font-outfit text-sm font-bold text-blue-300">Work on a Project</h4>
                                            </div>
                                            <p class="text-slate-300 text-xs mb-2">With GM approval, pursue a long-term project (deciphering texts, crafting weapons, etc.).</p>
                                            <p class="text-blue-200 text-xs italic">First time: assign countdown. Each use: advance automatically or via action roll (GM's choice).</p>
                                        </div>
                                    </div>

                                    <!-- Long Rest End Effects -->
                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                        <h4 class="font-outfit text-lg font-bold text-indigo-300 mb-3">At the End of a Long Rest:</h4>
                                        <ul class="space-y-2 text-slate-300 text-sm">
                                            <li class="flex items-start gap-2">
                                                <span class="text-indigo-400 mt-1">•</span>
                                                <span>Any features or effects with a limited number of uses per rest or per long rest refresh</span>
                                            </li>
                                            <li class="flex items-start gap-2">
                                                <span class="text-indigo-400 mt-1">•</span>
                                                <span>Any features or effects that last until your next rest or until your next long rest expire</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Downtime Consequences -->
                                <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-8">
                                    <div class="flex items-start gap-4">
                                        <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="font-outfit text-2xl font-bold text-red-300 mb-4">Downtime Consequences</h3>
                                            <div class="grid md:grid-cols-2 gap-6">
                                                <div>
                                                    <h4 class="font-outfit text-lg font-bold text-red-400 mb-3">Short Rest</h4>
                                                    <p class="text-slate-300 text-sm">The GM gains <strong class="text-red-400">1d4 Fear</strong>.</p>
                                                </div>
                                                <div>
                                                    <h4 class="font-outfit text-lg font-bold text-red-400 mb-3">Long Rest</h4>
                                                    <p class="text-slate-300 text-sm mb-2">The GM gains Fear equal to <strong class="text-red-400">1d4 + the number of PCs</strong>.</p>
                                                    <p class="text-slate-300 text-sm">They can also advance a <strong class="text-red-400">long-term countdown</strong> of their choice.</p>
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
    </div>
</x-layouts.app>
