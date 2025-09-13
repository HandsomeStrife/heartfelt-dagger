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
                                @include('reference.partials.navigation-menu', ['current_page' => 'conditions'])
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-8">
                            <!-- Page Header -->
                            <div class="mb-8 flex items-start justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-red-500 rounded-xl flex items-center justify-center shadow-lg">
                                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h1 class="font-outfit text-3xl font-bold text-white">Conditions</h1>
                                        <p class="text-slate-400 text-sm mt-1">Status Effects & Modifiers</p>
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
                                <div class="bg-orange-500/10 border border-orange-500/30 rounded-xl p-6 mb-8">
                                    <p class="text-orange-100 leading-relaxed text-lg">
                                        <strong class="text-orange-300">Conditions</strong> are effects that grant specific benefits or drawbacks to the target they are attached to.
                                    </p>
                                </div>

                                <!-- General Rules -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">General Rules</h2>
                                    
                                    <div class="space-y-4">
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                            <p class="text-slate-300 text-sm leading-relaxed">
                                                Some features can apply special or unique conditions, which work as described in the feature text.
                                            </p>
                                        </div>
                                        
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                            <p class="text-slate-300 text-sm leading-relaxed">
                                                Unless otherwise noted, the same condition can't be applied more than once to the same target.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Standard Conditions -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Standard Conditions</h2>
                                    <p class="text-slate-300 leading-relaxed mb-8">
                                        Daggerheart has three standard conditions:
                                    </p>
                                    
                                    <div class="space-y-6">
                                        <!-- Hidden -->
                                        <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-purple-300 mb-3">Hidden</h3>
                                                    <p class="text-slate-300 leading-relaxed mb-4">
                                                        While you're out of sight from all enemies and they don't otherwise know your location, you gain the Hidden condition.
                                                    </p>
                                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4 mb-4">
                                                        <h4 class="font-outfit text-sm font-bold text-purple-400 mb-2">Effect:</h4>
                                                        <p class="text-slate-300 text-sm">Any rolls against a Hidden creature have disadvantage.</p>
                                                    </div>
                                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                        <h4 class="font-outfit text-sm font-bold text-purple-400 mb-2">Ends When:</h4>
                                                        <ul class="text-slate-300 text-sm space-y-1">
                                                            <li>• An adversary moves to where they would see you</li>
                                                            <li>• You move into their line of sight</li>
                                                            <li>• You make an attack</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Restrained -->
                                        <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-yellow-600 mb-3">Restrained</h3>
                                                    <p class="text-slate-300 leading-relaxed mb-4">
                                                        Restrained characters can't move, but you can still take actions from their current position.
                                                    </p>
                                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                        <h4 class="font-outfit text-sm font-bold text-yellow-600 mb-2">Effect:</h4>
                                                        <p class="text-slate-300 text-sm">Cannot move, but can still act from current position.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Vulnerable -->
                                        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-red-300 mb-3">Vulnerable</h3>
                                                    <p class="text-slate-300 leading-relaxed mb-4">
                                                        When a creature is Vulnerable, all rolls targeting them have advantage.
                                                    </p>
                                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                        <h4 class="font-outfit text-sm font-bold text-red-400 mb-2">Effect:</h4>
                                                        <p class="text-slate-300 text-sm">All rolls targeting this creature have advantage.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Temporary Tags & Special Conditions -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Temporary Tags & Special Conditions</h2>
                                    
                                    <div class="space-y-6">
                                        <!-- Temporary Conditions -->
                                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-blue-300 mb-3">Temporary Conditions</h3>
                                                    <p class="text-slate-300 leading-relaxed mb-4">
                                                        The <strong class="text-blue-300">temporary</strong> tag denotes a condition or effect that the affected creature can clear by making a move against it.
                                                    </p>
                                                    
                                                    <div class="grid md:grid-cols-2 gap-4">
                                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                            <h4 class="font-outfit text-sm font-bold text-blue-400 mb-2">For PCs:</h4>
                                                            <p class="text-slate-300 text-sm">Requires a successful action roll using an appropriate trait to clear the condition.</p>
                                                        </div>
                                                        
                                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                            <h4 class="font-outfit text-sm font-bold text-blue-400 mb-2">For Adversaries:</h4>
                                                            <p class="text-slate-300 text-sm">GM puts spotlight on adversary and describes how they clear it; no roll required but uses their spotlight.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Special Conditions -->
                                        <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-green-300 mb-3">Special Conditions</h3>
                                                    <p class="text-slate-300 leading-relaxed mb-4">
                                                        <strong class="text-green-300">Special conditions</strong> are only cleared when specific requirements are met, such as completing a certain action or using a particular item.
                                                    </p>
                                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                        <p class="text-slate-300 text-sm">
                                                            The requirements for clearing these conditions are stated in the text of the effect that applies the condition.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Condition Summary -->
                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-8">
                                    <h3 class="font-outfit text-2xl font-bold text-amber-300 mb-6">Condition Quick Reference</h3>
                                    
                                    <div class="grid md:grid-cols-3 gap-4">
                                        <div class="bg-purple-500/10 border border-purple-500/30 rounded-lg p-4 text-center">
                                            <div class="text-purple-400 font-bold text-lg mb-2">Hidden</div>
                                            <div class="text-slate-300 text-sm">Attacks against you have disadvantage</div>
                                        </div>
                                        
                                        <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-4 text-center">
                                            <div class="text-yellow-600 font-bold text-lg mb-2">Restrained</div>
                                            <div class="text-slate-300 text-sm">Cannot move, can still act</div>
                                        </div>
                                        
                                        <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 text-center">
                                            <div class="text-red-400 font-bold text-lg mb-2">Vulnerable</div>
                                            <div class="text-slate-300 text-sm">All attacks against you have advantage</div>
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
