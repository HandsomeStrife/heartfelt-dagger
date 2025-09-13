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
                                @include('reference.partials.navigation-menu', ['current_page' => 'stress'])
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-8">
                            <!-- Page Header -->
                            <div class="mb-8 flex items-start justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-indigo-500 rounded-xl flex items-center justify-center shadow-lg">
                                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h1 class="font-outfit text-3xl font-bold text-white">Stress</h1>
                                        <p class="text-slate-400 text-sm mt-1">Mental, Physical & Emotional Strain</p>
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
                                <!-- Main Definition -->
                                <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl p-6 mb-8">
                                    <p class="text-purple-100 leading-relaxed text-lg">
                                        <strong class="text-purple-300">Stress</strong> represents how much mental, physical, and emotional strain a character can endure. Some special abilities or effects require the character activating them to mark Stress, and the GM can require a PC to mark Stress as a GM move or to represent the cost, complication, or consequence of an action roll.
                                    </p>
                                </div>

                                <!-- Key Stress Mechanics -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Key Stress Mechanics</h2>
                                    
                                    <div class="space-y-6">
                                        <!-- Vulnerability -->
                                        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-red-300 mb-3">Maximum Stress = Vulnerable</h3>
                                                    <p class="text-slate-300 leading-relaxed">
                                                        When a character marks their last Stress, they become <strong class="text-red-300">Vulnerable</strong> until they clear at least 1 Stress.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Stress Overflow -->
                                        <div class="bg-orange-500/10 border border-orange-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-orange-300 mb-3">Stress Overflow</h3>
                                                    <p class="text-slate-300 leading-relaxed">
                                                        When a character must mark 1 or more Stress but can't, they mark <strong class="text-orange-300">1 HP</strong> instead.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Usage Restriction -->
                                        <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-yellow-600 mb-3">Usage Restriction</h3>
                                                    <p class="text-slate-300 leading-relaxed">
                                                        A character can't use a move that requires them to mark Stress if all of their Stress is marked.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Clearing Stress -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Clearing Stress</h2>
                                    
                                    <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-6">
                                        <div class="flex items-start gap-4">
                                            <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="font-outfit text-lg font-bold text-green-300 mb-3">Recovery Methods</h3>
                                                <p class="text-slate-300 leading-relaxed">
                                                    PCs can clear Stress by making <strong class="text-green-300">downtime moves</strong>. A PC's maximum Stress is determined by their class, but they can increase it through advancements, abilities, and other effects.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Stress in Practice -->
                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-8">
                                    <h3 class="font-outfit text-2xl font-bold text-amber-300 mb-6">Stress in Practice</h3>
                                    
                                    <div class="grid md:grid-cols-2 gap-6">
                                        <!-- When to Mark Stress -->
                                        <div>
                                            <h4 class="font-outfit text-lg font-bold text-purple-400 mb-4">When You Mark Stress:</h4>
                                            <ul class="space-y-3">
                                                <li class="flex items-start gap-3">
                                                    <span class="text-purple-400 mt-1">•</span>
                                                    <span class="text-slate-300 text-sm">Using certain special abilities</span>
                                                </li>
                                                <li class="flex items-start gap-3">
                                                    <span class="text-purple-400 mt-1">•</span>
                                                    <span class="text-slate-300 text-sm">GM moves and consequences</span>
                                                </li>
                                                <li class="flex items-start gap-3">
                                                    <span class="text-purple-400 mt-1">•</span>
                                                    <span class="text-slate-300 text-sm">Costs of action roll outcomes</span>
                                                </li>
                                                <li class="flex items-start gap-3">
                                                    <span class="text-purple-400 mt-1">•</span>
                                                    <span class="text-slate-300 text-sm">Environmental hazards</span>
                                                </li>
                                            </ul>
                                        </div>

                                        <!-- Strategic Considerations -->
                                        <div>
                                            <h4 class="font-outfit text-lg font-bold text-purple-400 mb-4">Strategic Considerations:</h4>
                                            <ul class="space-y-3">
                                                <li class="flex items-start gap-3">
                                                    <span class="text-purple-400 mt-1">•</span>
                                                    <span class="text-slate-300 text-sm">Monitor your Stress levels carefully</span>
                                                </li>
                                                <li class="flex items-start gap-3">
                                                    <span class="text-purple-400 mt-1">•</span>
                                                    <span class="text-slate-300 text-sm">Plan downtime to clear Stress</span>
                                                </li>
                                                <li class="flex items-start gap-3">
                                                    <span class="text-purple-400 mt-1">•</span>
                                                    <span class="text-slate-300 text-sm">Avoid becoming Vulnerable</span>
                                                </li>
                                                <li class="flex items-start gap-3">
                                                    <span class="text-purple-400 mt-1">•</span>
                                                    <span class="text-slate-300 text-sm">Balance resource usage with risk</span>
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
