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
                                @include('reference.partials.navigation-menu', ['current_page' => 'attacking'])
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-8">
                            <!-- Page Header -->
                            <div class="mb-8 flex items-start justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-pink-500 rounded-xl flex items-center justify-center shadow-lg">
                                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h1 class="font-outfit text-3xl font-bold text-white">Attacking</h1>
                                        <p class="text-slate-400 text-sm mt-1">Attack Rolls & Damage</p>
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
                                <!-- Attack Rolls -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Attack Rolls</h2>
                                    <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-6 mb-6">
                                        <p class="text-red-100 leading-relaxed mb-4">
                                            An <strong class="text-red-300">attack roll</strong> is an action roll intended to inflict harm. The trait that applies to an attack roll is specified by the weapon or spell being used.
                                        </p>
                                        <div class="grid md:grid-cols-2 gap-4">
                                            <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                <h4 class="font-outfit text-sm font-bold text-red-300 mb-2">Unarmed Attacks</h4>
                                                <p class="text-slate-300 text-sm">Use either Strength or Finesse (GM's choice)</p>
                                            </div>
                                            <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                <h4 class="font-outfit text-sm font-bold text-red-300 mb-2">Difficulty</h4>
                                                <p class="text-slate-300 text-sm">Equal to the target's Difficulty score</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Damage Rolls -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Damage Rolls</h2>
                                    
                                    <div class="bg-orange-500/10 border border-orange-500/30 rounded-xl p-6 mb-6">
                                        <p class="text-orange-100 leading-relaxed mb-4">
                                            On a successful attack, roll damage. Damage is calculated from the <strong class="text-orange-300">damage roll</strong> listed in the attack's description with the format "xdy+[modifier]" <em>(e.g., for a spell that inflicts "1d8+2" damage, you roll an eight-sided die and add 2 to the result)</em>.
                                        </p>
                                    </div>

                                    <div class="grid md:grid-cols-2 gap-6">
                                        <!-- Spellcast Damage -->
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <div class="flex items-center gap-3 mb-4">
                                                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z" />
                                                </svg>
                                                <h3 class="font-outfit text-lg font-bold text-purple-300">Spellcast Damage</h3>
                                            </div>
                                            <p class="text-slate-300 text-sm leading-relaxed mb-3">
                                                Any time an effect says to deal damage using your Spellcast trait, you roll a number of dice equal to your Spellcast trait.
                                            </p>
                                            <div class="bg-slate-700/30 border border-slate-600/30 rounded-lg p-3">
                                                <p class="text-slate-400 text-xs">
                                                    <strong>Note:</strong> If your Spellcast trait is +0 or lower, you don't roll anything.
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Weapon Proficiency -->
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <div class="flex items-center gap-3 mb-4">
                                                <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                                </svg>
                                                <h3 class="font-outfit text-lg font-bold text-amber-300">Weapon Proficiency</h3>
                                            </div>
                                            <p class="text-slate-300 text-sm leading-relaxed mb-3">
                                                For weapons, the number of damage dice you roll is equal to your <strong class="text-amber-300">Proficiency</strong>. Proficiency multiplies the number of dice you roll, but doesn't affect the modifier.
                                            </p>
                                            <div class="bg-slate-700/30 border border-slate-600/30 rounded-lg p-3">
                                                <p class="text-slate-400 text-xs">
                                                    <strong>Example:</strong> A PC with Proficiency 2 wielding a weapon with "d8+2" damage deals "2d8+2" on a successful attack.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Unarmed Damage -->
                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4 mt-6">
                                        <p class="text-slate-300 text-sm leading-relaxed">
                                            <strong class="text-amber-300">Unarmed attacks</strong> inflict [Proficiency]d4 damage.
                                        </p>
                                    </div>
                                </div>

                                <!-- Critical Damage -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Critical Damage</h2>
                                    <div class="bg-gradient-to-r from-yellow-500/10 to-amber-500/10 border border-yellow-500/30 rounded-xl p-6">
                                        <div class="flex items-start gap-4">
                                            <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-amber-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                <svg class="w-6 h-6 text-black" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="font-outfit text-lg font-bold text-amber-300 mb-3">Critical Success Damage</h3>
                                                <p class="text-slate-300 leading-relaxed mb-4">
                                                    When you get a critical success (matching values on your Duality Dice) on an attack roll, you deal extra damage. Make the damage roll as usual, but add the maximum possible result of the damage dice to the final total.
                                                </p>
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                    <p class="text-amber-200 text-sm">
                                                        <strong>Example:</strong> If an attack would normally deal 2d8+1 damage, a critical success would deal 2d8+1+16.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Damage Types -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Damage Types</h2>
                                    
                                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                                        <!-- Physical Damage -->
                                        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-6">
                                            <div class="flex items-center gap-3 mb-4">
                                                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                                </svg>
                                                <h3 class="font-outfit text-lg font-bold text-red-300">Physical Damage (phy)</h3>
                                            </div>
                                            <p class="text-slate-300 text-sm leading-relaxed">
                                                Mundane weapons and unarmed attacks deal physical damage unless stated otherwise.
                                            </p>
                                        </div>

                                        <!-- Magic Damage -->
                                        <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl p-6">
                                            <div class="flex items-center gap-3 mb-4">
                                                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z" />
                                                </svg>
                                                <h3 class="font-outfit text-lg font-bold text-purple-300">Magic Damage (mag)</h3>
                                            </div>
                                            <p class="text-slate-300 text-sm leading-relaxed">
                                                Spells deal magic damage unless stated otherwise.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Resistance & Immunity -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Resistance, Immunity & Direct Damage</h2>
                                    
                                    <div class="space-y-6">
                                        <!-- Resistance -->
                                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-blue-300 mb-2">Resistance</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed mb-3">
                                                        If a target has <strong class="text-blue-300">resistance</strong> to a damage type, they reduce incoming damage of that type by half before comparing it to their Hit Point Thresholds.
                                                    </p>
                                                    <p class="text-slate-400 text-xs">
                                                        If the target has additional ways of reducing incoming damage (like marking Armor Slots), they apply resistance first. Multiple resistances to the same damage type do not stack.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Immunity -->
                                        <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-green-300 mb-2">Immunity</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed">
                                                        If a target has <strong class="text-green-300">immunity</strong> to a damage type, they ignore incoming damage of that type completely.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Mixed Damage -->
                                        <div class="bg-orange-500/10 border border-orange-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-orange-300 mb-2">Mixed Damage Types</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed">
                                                        If an attack deals both physical and magic damage, a character can only benefit from resistance or immunity if they are resistant or immune to <strong class="text-orange-300">both</strong> damage types.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Direct Damage -->
                                        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-red-300 mb-2">Direct Damage</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed">
                                                        <strong class="text-red-300">Direct damage</strong> is damage that can't be reduced by marking Armor Slots.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Multi-Target & Multiple Sources -->
                                <div class="grid md:grid-cols-2 gap-6">
                                    <!-- Multi-Target -->
                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                        <h3 class="font-outfit text-lg font-bold text-amber-300 mb-4">Multi-Target Attacks</h3>
                                        <p class="text-slate-300 text-sm leading-relaxed">
                                            If a spell or ability allows you to target multiple adversaries, make one attack roll and one damage roll, then apply the same attack roll result individually.
                                        </p>
                                    </div>

                                    <!-- Multiple Sources -->
                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                        <h3 class="font-outfit text-lg font-bold text-amber-300 mb-4">Multiple Damage Sources</h3>
                                        <p class="text-slate-300 text-sm leading-relaxed">
                                            Damage dealt simultaneously from multiple sources is always totaled before it's compared to its target's damage thresholds.
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
</x-layouts.app>
