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
                                @include('reference.partials.navigation-menu', ['current_page' => 'loot'])
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-8">
                            <!-- Page Header -->
                            <div class="mb-8 flex items-start justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-500 rounded-xl flex items-center justify-center shadow-lg">
                                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h1 class="font-outfit text-3xl font-bold text-white">Loot</h1>
                                        <p class="text-slate-400 text-sm mt-1">Items & Magical Equipment</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-2 ml-4 flex-shrink-0">
                                    <span class="px-3 py-1 bg-orange-500/20 text-orange-300 text-sm rounded-full">
                                        Equipment
                                    </span>
                                </div>
                            </div>

                            <!-- Page Content -->
                            <div class="prose prose-invert max-w-none" data-search-body>
                                <!-- Loot Overview -->
                                <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-6 mb-8">
                                    <p class="text-green-100 leading-relaxed text-lg">
                                        <strong class="text-green-300">Loot</strong> comprises any consumables or reusable items the party acquires. <strong class="text-green-300">Items</strong> can be used until sold, discarded, or lost.
                                    </p>
                                </div>

                                <!-- Random Generation -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Random Item Generation</h2>
                                    
                                    <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl p-6 mb-6">
                                        <h3 class="font-outfit text-lg font-bold text-purple-300 mb-4">Generation Process</h3>
                                        <p class="text-slate-300 leading-relaxed mb-4">
                                            To generate a random item, choose a rarity, roll the designated dice, and match the total to the item in the table:
                                        </p>
                                    </div>

                                    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
                                        <!-- Common -->
                                        <div class="bg-gray-500/10 border border-gray-500/30 rounded-xl p-6 text-center">
                                            <div class="w-12 h-12 bg-gray-500 rounded-full flex items-center justify-center mx-auto mb-3">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                            </div>
                                            <h4 class="font-outfit text-lg font-bold text-gray-300 mb-2">Common</h4>
                                            <div class="space-y-1">
                                                <p class="text-slate-300 text-sm">1d12</p>
                                                <p class="text-slate-400 text-xs">or</p>
                                                <p class="text-slate-300 text-sm">2d12</p>
                                            </div>
                                        </div>

                                        <!-- Uncommon -->
                                        <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-6 text-center">
                                            <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-3">
                                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                </svg>
                                            </div>
                                            <h4 class="font-outfit text-lg font-bold text-green-300 mb-2">Uncommon</h4>
                                            <div class="space-y-1">
                                                <p class="text-slate-300 text-sm">2d12</p>
                                                <p class="text-slate-400 text-xs">or</p>
                                                <p class="text-slate-300 text-sm">3d12</p>
                                            </div>
                                        </div>

                                        <!-- Rare -->
                                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6 text-center">
                                            <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-3">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                                </svg>
                                            </div>
                                            <h4 class="font-outfit text-lg font-bold text-blue-300 mb-2">Rare</h4>
                                            <div class="space-y-1">
                                                <p class="text-slate-300 text-sm">3d12</p>
                                                <p class="text-slate-400 text-xs">or</p>
                                                <p class="text-slate-300 text-sm">4d12</p>
                                            </div>
                                        </div>

                                        <!-- Legendary -->
                                        <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl p-6 text-center">
                                            <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center mx-auto mb-3">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                                </svg>
                                            </div>
                                            <h4 class="font-outfit text-lg font-bold text-purple-300 mb-2">Legendary</h4>
                                            <div class="space-y-1">
                                                <p class="text-slate-300 text-sm">4d12</p>
                                                <p class="text-slate-400 text-xs">or</p>
                                                <p class="text-slate-300 text-sm">5d12</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Item Categories -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Item Categories</h2>
                                    
                                    <div class="grid md:grid-cols-2 gap-6">
                                        <!-- Utility Items -->
                                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-blue-300 mb-4">Utility Items</h3>
                                            <div class="space-y-3">
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <h4 class="text-blue-200 text-sm font-bold mb-1">Premium Bedroll</h4>
                                                    <p class="text-slate-300 text-xs">During downtime, you automatically clear a Stress.</p>
                                                </div>
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <h4 class="text-blue-200 text-sm font-bold mb-1">Piper Whistle</h4>
                                                    <p class="text-slate-300 text-xs">Piercing tone can be heard within a 1-mile radius.</p>
                                                </div>
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <h4 class="text-blue-200 text-sm font-bold mb-1">Speaking Orbs</h4>
                                                    <p class="text-slate-300 text-xs">Pair of orbs allows communication across any distance.</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Combat Enhancement -->
                                        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-red-300 mb-4">Combat Enhancement</h3>
                                            <div class="space-y-3">
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <h4 class="text-red-200 text-sm font-bold mb-1">Charging Quiver</h4>
                                                    <p class="text-slate-300 text-xs">Gain damage bonus equal to your current tier on arrow attacks.</p>
                                                </div>
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <h4 class="text-red-200 text-sm font-bold mb-1">Bloodstone</h4>
                                                    <p class="text-slate-300 text-xs"><strong>Brutal:</strong> Roll additional damage die on maximum roll.</p>
                                                </div>
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <h4 class="text-red-200 text-sm font-bold mb-1">Piercing Arrows</h4>
                                                    <p class="text-slate-300 text-xs">Three times per rest, add Proficiency to damage roll.</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Magical Tools -->
                                        <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-purple-300 mb-4">Magical Tools</h3>
                                            <div class="space-y-3">
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <h4 class="text-purple-200 text-sm font-bold mb-1">Arcane Cloak</h4>
                                                    <p class="text-slate-300 text-xs">Spellcast users can adjust color, texture, and size at will.</p>
                                                </div>
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <h4 class="text-purple-200 text-sm font-bold mb-1">Glamour Stone</h4>
                                                    <p class="text-slate-300 text-xs">Memorize appearance, spend Hope to recreate as illusion.</p>
                                                </div>
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <h4 class="text-purple-200 text-sm font-bold mb-1">Arcane Prism</h4>
                                                    <p class="text-slate-300 text-xs">Allies within Close range gain +1 to Spellcast Rolls.</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Legendary Items -->
                                        <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-4">Legendary Items</h3>
                                            <div class="space-y-3">
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <h4 class="text-amber-200 text-sm font-bold mb-1">Portal Seed</h4>
                                                    <p class="text-slate-300 text-xs">Plant to grow portal, travel between planted locations.</p>
                                                </div>
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <h4 class="text-amber-200 text-sm font-bold mb-1">Infinite Bag</h4>
                                                    <p class="text-slate-300 text-xs">Pocket dimension storage that never runs out of space.</p>
                                                </div>
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <h4 class="text-amber-200 text-sm font-bold mb-1">Clay Companion</h4>
                                                    <p class="text-slate-300 text-xs">Sculpt into any animal companion, retains memory across forms.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Special Item Types -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Special Item Types</h2>
                                    
                                    <div class="space-y-6">
                                        <!-- Relics -->
                                        <div class="bg-indigo-500/10 border border-indigo-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-indigo-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-indigo-300 mb-3">Relics</h3>
                                                    <p class="text-slate-300 leading-relaxed mb-4">
                                                        Powerful artifacts that provide permanent bonuses to character traits. <strong class="text-indigo-300">You can only carry one relic.</strong>
                                                    </p>
                                                    <div class="grid md:grid-cols-2 gap-3">
                                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                            <p class="text-indigo-200 text-sm"><strong>Stride Relic:</strong> +1 Agility</p>
                                                        </div>
                                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                            <p class="text-indigo-200 text-sm"><strong>Bolster Relic:</strong> +1 Strength</p>
                                                        </div>
                                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                            <p class="text-indigo-200 text-sm"><strong>Control Relic:</strong> +1 Finesse</p>
                                                        </div>
                                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                            <p class="text-indigo-200 text-sm"><strong>Attune Relic:</strong> +1 Instinct</p>
                                                        </div>
                                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                            <p class="text-indigo-200 text-sm"><strong>Charm Relic:</strong> +1 Presence</p>
                                                        </div>
                                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                            <p class="text-indigo-200 text-sm"><strong>Enlighten Relic:</strong> +1 Knowledge</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Weapon Gems -->
                                        <div class="bg-cyan-500/10 border border-cyan-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-cyan-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-cyan-300 mb-3">Weapon Gems</h3>
                                                    <p class="text-slate-300 leading-relaxed mb-4">
                                                        Attachable gems that allow weapons to use different traits for attack rolls.
                                                    </p>
                                                    <div class="grid md:grid-cols-2 gap-3">
                                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                            <p class="text-cyan-200 text-sm"><strong>Gem of Alacrity:</strong> Use Agility</p>
                                                        </div>
                                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                            <p class="text-cyan-200 text-sm"><strong>Gem of Might:</strong> Use Strength</p>
                                                        </div>
                                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                            <p class="text-cyan-200 text-sm"><strong>Gem of Precision:</strong> Use Finesse</p>
                                                        </div>
                                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                            <p class="text-cyan-200 text-sm"><strong>Gem of Insight:</strong> Use Instinct</p>
                                                        </div>
                                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                            <p class="text-cyan-200 text-sm"><strong>Gem of Audacity:</strong> Use Presence</p>
                                                        </div>
                                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                            <p class="text-cyan-200 text-sm"><strong>Gem of Sagacity:</strong> Use Knowledge</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Crafting Recipes -->
                                        <div class="bg-orange-500/10 border border-orange-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-orange-300 mb-3">Crafting Recipes</h3>
                                                    <p class="text-slate-300 leading-relaxed mb-4">
                                                        Recipes allow characters to craft consumables during downtime using specific materials.
                                                    </p>
                                                    <div class="space-y-3">
                                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                            <p class="text-orange-200 text-sm"><strong>Minor Health Potion Recipe:</strong> Use vial of blood</p>
                                                        </div>
                                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                            <p class="text-orange-200 text-sm"><strong>Minor Stamina Potion Recipe:</strong> Use creature bone</p>
                                                        </div>
                                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                            <p class="text-orange-200 text-sm"><strong>Mythic Dust Recipe:</strong> Use fine gold dust</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Loot Tables Reference -->
                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-8">
                                    <h3 class="font-outfit text-2xl font-bold text-amber-300 mb-6">Complete Loot Tables</h3>
                                    
                                    <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-6 text-center">
                                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <h4 class="font-outfit text-lg font-bold text-green-300 mb-3">60+ Unique Items</h4>
                                        <p class="text-slate-300 text-sm mb-4">From common utility items to legendary artifacts, each with unique properties and abilities</p>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                            <p class="text-green-200 text-xs">See complete loot tables for full item descriptions, rarities, and mechanical effects</p>
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
