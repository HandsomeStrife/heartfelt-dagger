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
                                @include('reference.partials.navigation-menu', ['current_page' => 'combat'])
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
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h1 class="font-outfit text-3xl font-bold text-white">Combat</h1>
                                        <p class="text-slate-400 text-sm mt-1">Physical Conflicts & Damage</p>
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
                                <p class="text-slate-300 leading-relaxed mb-8 text-lg">
                                    Though Daggerheart relies on the same flow of collaborative storytelling in and out of combat, physical conflicts rely more heavily on several key mechanics related to attacking, maneuvering, and taking damage.
                                </p>

                                <!-- Evasion -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Evasion</h2>
                                    <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6 mb-6">
                                        <p class="text-blue-100 leading-relaxed mb-4">
                                            <strong class="text-blue-300">Evasion</strong> represents a character's ability to avoid attacks and other unwanted effects. Any roll made against a PC has a Difficulty equal to the target's Evasion.
                                        </p>
                                        <p class="text-blue-100 leading-relaxed">
                                            A PC's Base Evasion is determined by their class, but can be modified by domain cards, equipment, conditions, and other effects.
                                        </p>
                                    </div>
                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                        <p class="text-slate-300 text-sm leading-relaxed">
                                            <strong>Note:</strong> Attacks rolled against adversaries use the target's Difficulty instead of Evasion.
                                        </p>
                                    </div>
                                </div>

                                <!-- Hit Points & Damage Thresholds -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Hit Points & Damage Thresholds</h2>
                                    
                                    <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-6 mb-6">
                                        <p class="text-red-100 leading-relaxed mb-4">
                                            <strong class="text-red-300">Hit Points (HP)</strong> represent a character's ability to withstand physical injury. When a character takes damage, they mark 1 to 3 HP, based on their <strong class="text-red-300">damage thresholds</strong>:
                                        </p>
                                    </div>

                                    <div class="grid md:grid-cols-3 gap-4 mb-8">
                                        <!-- Minor Damage -->
                                        <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-4">
                                            <div class="flex items-center gap-3 mb-3">
                                                <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center text-black font-bold">
                                                    1
                                                </div>
                                                <h3 class="font-outfit text-lg font-bold text-yellow-300">Minor</h3>
                                            </div>
                                            <p class="text-slate-300 text-sm leading-relaxed">
                                                If damage is below the Major threshold, mark <strong>1 HP</strong>.
                                            </p>
                                        </div>

                                        <!-- Major Damage -->
                                        <div class="bg-orange-500/10 border border-orange-500/30 rounded-xl p-4">
                                            <div class="flex items-center gap-3 mb-3">
                                                <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center text-black font-bold">
                                                    2
                                                </div>
                                                <h3 class="font-outfit text-lg font-bold text-orange-300">Major</h3>
                                            </div>
                                            <p class="text-slate-300 text-sm leading-relaxed">
                                                If damage is at or above Major but below Severe threshold, mark <strong>2 HP</strong>.
                                            </p>
                                        </div>

                                        <!-- Severe Damage -->
                                        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-4">
                                            <div class="flex items-center gap-3 mb-3">
                                                <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center text-white font-bold">
                                                    3
                                                </div>
                                                <h3 class="font-outfit text-lg font-bold text-red-300">Severe</h3>
                                            </div>
                                            <p class="text-slate-300 text-sm leading-relaxed">
                                                If damage is at or above the Severe threshold, mark <strong>3 HP</strong>.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="space-y-4">
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                            <p class="text-slate-300 text-sm leading-relaxed">
                                                If incoming damage is ever reduced to 0 or less, no HP is marked.
                                            </p>
                                        </div>

                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                            <p class="text-slate-300 text-sm leading-relaxed">
                                                A PC's damage thresholds are calculated by adding their level to the listed damage thresholds of their equipped armor. A PC's starting HP is based on their class, but they can gain additional Hit Points through advancements, features, and other effects.
                                            </p>
                                        </div>

                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                            <p class="text-slate-300 text-sm leading-relaxed">
                                                An adversary's Damage Thresholds and HP are listed in their stat blocks.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Falling & Death -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Falling & Recovery</h2>
                                    
                                    <div class="grid md:grid-cols-2 gap-6">
                                        <!-- Falling -->
                                        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-6">
                                            <div class="flex items-center gap-3 mb-4">
                                                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                                </svg>
                                                <h3 class="font-outfit text-lg font-bold text-red-300">When You Fall</h3>
                                            </div>
                                            <p class="text-slate-300 text-sm leading-relaxed">
                                                When a character marks their last Hit Point, they fall. If a PC falls, they make a death move.
                                            </p>
                                        </div>

                                        <!-- Recovery -->
                                        <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-6">
                                            <div class="flex items-center gap-3 mb-4">
                                                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                                </svg>
                                                <h3 class="font-outfit text-lg font-bold text-green-300">Recovery</h3>
                                            </div>
                                            <p class="text-slate-300 text-sm leading-relaxed">
                                                Characters can clear Hit Points by taking downtime moves or by activating relevant special abilities or effects.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Optional Rule -->
                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-8">
                                    <div class="flex items-start gap-4 mb-6">
                                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-500 rounded-xl flex items-center justify-center flex-shrink-0">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="font-outfit text-2xl font-bold text-purple-300 mb-4">Optional Rule: Massive Damage</h3>
                                            <p class="text-slate-300 leading-relaxed">
                                                If a character ever takes damage equal to twice their Severe threshold, they mark <strong class="text-purple-300">4 HP</strong> instead of 3.
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
</x-layouts.app>
