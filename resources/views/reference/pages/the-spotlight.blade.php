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
                                @include('reference.partials.navigation-menu', ['current_page' => 'the-spotlight'])
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-8">
                            <!-- Page Header -->
                            <div class="mb-8 flex items-start justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 bg-gradient-to-br from-yellow-400 to-amber-500 rounded-xl flex items-center justify-center shadow-lg">
                                        <svg class="w-10 h-10 text-black" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h1 class="font-outfit text-3xl font-bold text-white">The Spotlight</h1>
                                        <p class="text-slate-400 text-sm mt-1">Focus and Attention Management</p>
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
                                <div class="bg-gradient-to-r from-yellow-500/10 to-amber-500/10 border border-yellow-500/30 rounded-xl p-8 mb-8">
                                    <div class="flex items-start gap-4">
                                        <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-amber-500 rounded-full flex items-center justify-center flex-shrink-0">
                                            <svg class="w-6 h-6 text-black" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h2 class="font-outfit text-2xl font-bold text-amber-300 mb-4">What is the Spotlight?</h2>
                                            <p class="text-slate-200 leading-relaxed text-lg">
                                                The <strong class="text-amber-300">spotlight</strong> is a symbol that represents the table's attention—and therefore the immediate focus of both the narrative and the game mechanics. Any time a character or player becomes the focus of a scene, they "are in the spotlight" or "have the spotlight."
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- How the Spotlight Moves -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">How the Spotlight Moves</h2>
                                    
                                    <div class="grid md:grid-cols-2 gap-6">
                                        <!-- Organic Movement -->
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <div class="flex items-center gap-3 mb-4">
                                                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                                </svg>
                                                <h3 class="font-outfit text-lg font-bold text-green-300">Organic Movement</h3>
                                            </div>
                                            <p class="text-slate-300 text-sm leading-relaxed">
                                                The spotlight moves around the table organically as scenes unfold, flowing naturally from one character to another based on the fiction and narrative momentum.
                                            </p>
                                        </div>

                                        <!-- Mechanical Triggers -->
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <div class="flex items-center gap-3 mb-4">
                                                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                </svg>
                                                <h3 class="font-outfit text-lg font-bold text-red-300">Mechanical Triggers</h3>
                                            </div>
                                            <p class="text-slate-300 text-sm leading-relaxed">
                                                Sometimes a mechanical trigger determines where the spotlight goes next. For example, when a player fails an action roll, the mechanics prompt the GM to seize the spotlight and make a GM move.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Key Concepts -->
                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-8">
                                    <h3 class="font-outfit text-2xl font-bold text-amber-300 mb-6">Key Spotlight Concepts</h3>
                                    
                                    <div class="space-y-6">
                                        <div class="flex items-start gap-4">
                                            <div class="w-8 h-8 bg-amber-500 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                                <span class="text-black text-sm font-bold">1</span>
                                            </div>
                                            <div>
                                                <h4 class="font-outfit text-lg font-bold text-amber-400 mb-2">Represents Attention</h4>
                                                <p class="text-slate-300 text-sm leading-relaxed">
                                                    The spotlight symbolizes where the table's collective attention is focused at any given moment.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex items-start gap-4">
                                            <div class="w-8 h-8 bg-amber-500 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                                <span class="text-black text-sm font-bold">2</span>
                                            </div>
                                            <div>
                                                <h4 class="font-outfit text-lg font-bold text-amber-400 mb-2">Drives Mechanics</h4>
                                                <p class="text-slate-300 text-sm leading-relaxed">
                                                    Many game mechanics are triggered by or interact with who currently has the spotlight.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex items-start gap-4">
                                            <div class="w-8 h-8 bg-amber-500 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                                <span class="text-black text-sm font-bold">3</span>
                                            </div>
                                            <div>
                                                <h4 class="font-outfit text-lg font-bold text-amber-400 mb-2">Fluid Movement</h4>
                                                <p class="text-slate-300 text-sm leading-relaxed">
                                                    The spotlight moves fluidly between characters, ensuring everyone gets their moment to shine.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex items-start gap-4">
                                            <div class="w-8 h-8 bg-amber-500 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                                <span class="text-black text-sm font-bold">4</span>
                                            </div>
                                            <div>
                                                <h4 class="font-outfit text-lg font-bold text-amber-400 mb-2">Narrative Focus</h4>
                                                <p class="text-slate-300 text-sm leading-relaxed">
                                                    When you have the spotlight, you're the primary focus of the current scene and narrative moment.
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
    </div>
</x-layouts.app>
