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
                                @include('reference.partials.navigation-menu', ['current_page' => 'consumables'])
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
                                        <svg class="w-10 h-10 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h1 class="font-outfit text-3xl font-bold text-white">Consumables</h1>
                                        <p class="text-slate-400 text-sm mt-1">Potions & Single-Use Items</p>
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
                                <!-- Consumables Overview -->
                                <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-6 mb-8">
                                    <p class="text-yellow-100 leading-relaxed text-lg">
                                        Consumables are loot that can <strong class="text-yellow-300">only be used once</strong>. You can hold up to <strong class="text-yellow-300">five of each consumable</strong> at a time. Using a consumable doesn't require a roll unless required by the GM or the demands of the fiction.
                                    </p>
                                </div>

                                <!-- Usage Rules -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Usage Rules</h2>
                                    
                                    <div class="grid md:grid-cols-2 gap-6">
                                        <!-- Single Use -->
                                        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-red-300 mb-3">Single Use Only</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed">
                                                        Each consumable can only be used <strong class="text-red-300">once</strong> before it's consumed and removed from your inventory.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Inventory Limit -->
                                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-blue-300 mb-3">Inventory Limit</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed">
                                                        You can hold up to <strong class="text-blue-300">five of each consumable</strong> type at a time.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- No Roll Required -->
                                    <div class="mt-6 bg-green-500/10 border border-green-500/30 rounded-xl p-6">
                                        <div class="flex items-start gap-4">
                                            <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="font-outfit text-lg font-bold text-green-300 mb-3">No Roll Required</h3>
                                                <p class="text-slate-300 text-sm leading-relaxed">
                                                    Using a consumable doesn't require a roll unless required by the GM or the demands of the fiction.
                                                </p>
                                            </div>
                                        </div>
                    </div>
                                </div>

                                <!-- Random Generation -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Random Generation</h2>
                                    
                                    <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl p-6 mb-6">
                                        <h3 class="font-outfit text-lg font-bold text-purple-300 mb-4">Generation Process</h3>
                                        <p class="text-slate-300 leading-relaxed mb-4">
                                            To generate a random consumable, choose a rarity, roll the designated dice, and match the total to the item in the table:
                                        </p>
                                    </div>

                                    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
                                        <!-- Common -->
                                        <div class="bg-gray-500/10 border border-gray-500/30 rounded-xl p-6 text-center">
                                            <div class="w-12 h-12 bg-gray-500 rounded-full flex items-center justify-center mx-auto mb-3">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z" />
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

                                <!-- Consumable Categories -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Consumable Categories</h2>
                                    
                                    <div class="grid md:grid-cols-2 gap-6">
                                        <!-- Trait Potions -->
                                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-blue-300 mb-4">Trait Enhancement Potions</h3>
                                            <div class="space-y-3">
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <h4 class="text-blue-200 text-sm font-bold mb-1">Stride Potion</h4>
                                                    <p class="text-slate-300 text-xs">+1 bonus to your next Agility Roll</p>
                                                </div>
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <h4 class="text-blue-200 text-sm font-bold mb-1">Bolster Potion</h4>
                                                    <p class="text-slate-300 text-xs">+1 bonus to your next Strength Roll</p>
                                                </div>
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <h4 class="text-blue-200 text-sm font-bold mb-1">Control Potion</h4>
                                                    <p class="text-slate-300 text-xs">+1 bonus to your next Finesse Roll</p>
                                                </div>
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <h4 class="text-blue-200 text-sm font-bold mb-1">Major Versions</h4>
                                                    <p class="text-slate-300 text-xs">+1 bonus to trait until your next rest</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Healing Items -->
                                        <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-green-300 mb-4">Healing & Recovery</h3>
                                            <div class="space-y-3">
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <h4 class="text-green-200 text-sm font-bold mb-1">Minor Health Potion</h4>
                                                    <p class="text-slate-300 text-xs">Clear 1d4 HP</p>
                                                </div>
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <h4 class="text-green-200 text-sm font-bold mb-1">Health Potion</h4>
                                                    <p class="text-slate-300 text-xs">Clear 1d4+1 HP</p>
                                                </div>
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <h4 class="text-green-200 text-sm font-bold mb-1">Minor Stamina Potion</h4>
                                                    <p class="text-slate-300 text-xs">Clear 1d4 Stress</p>
                                                </div>
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <h4 class="text-green-200 text-sm font-bold mb-1">Varik Leaves</h4>
                                                    <p class="text-slate-300 text-xs">Immediately gain 2 Hope</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Combat Enhancers -->
                                        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-red-300 mb-4">Combat Enhancers</h3>
                                            <div class="space-y-3">
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <h4 class="text-red-200 text-sm font-bold mb-1">Grindeltooth Venom</h4>
                                                    <p class="text-slate-300 text-xs">Add d6 to next weapon damage roll</p>
                                                </div>
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <h4 class="text-red-200 text-sm font-bold mb-1">Redthorn Saliva</h4>
                                                    <p class="text-slate-300 text-xs">Add d12 to next weapon damage roll</p>
                                                </div>
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <h4 class="text-red-200 text-sm font-bold mb-1">Hornet's Secret Potion</h4>
                                                    <p class="text-slate-300 text-xs">Next successful attack critically succeeds</p>
                                                </div>
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <h4 class="text-red-200 text-sm font-bold mb-1">Unstable Arcane Shard</h4>
                                                    <p class="text-slate-300 text-xs">Throw for 1d20 magic damage to group</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Utility Consumables -->
                                        <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-purple-300 mb-4">Utility & Special</h3>
                                            <div class="space-y-3">
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <h4 class="text-purple-200 text-sm font-bold mb-1">Vial of Moondrip</h4>
                                                    <p class="text-slate-300 text-xs">See in total darkness until next rest</p>
                                                </div>
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <h4 class="text-purple-200 text-sm font-bold mb-1">Jumping Root</h4>
                                                    <p class="text-slate-300 text-xs">Leap up to Far range without rolling</p>
                                                </div>
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <h4 class="text-purple-200 text-sm font-bold mb-1">Morphing Clay</h4>
                                                    <p class="text-slate-300 text-xs">Spend 4 Hope to alter face until next rest</p>
                                                </div>
                                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                    <h4 class="text-purple-200 text-sm font-bold mb-1">Blood of the Yorgi</h4>
                                                    <p class="text-slate-300 text-xs">Teleport to point within Very Far range</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Special Consumables -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Legendary Consumables</h2>
                                    
                                    <div class="space-y-6">
                                        <!-- Channelstone -->
                                        <div class="bg-indigo-500/10 border border-indigo-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-indigo-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-indigo-300 mb-3">Channelstone</h3>
                                                    <p class="text-slate-300 leading-relaxed">
                                                        You can use this stone to take a spell or grimoire from your vault, use it once, and return it to your vault.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Hopehold Flare -->
                                        <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-amber-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-black" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-amber-600 mb-3">Hopehold Flare</h3>
                                                    <p class="text-slate-300 leading-relaxed">
                                                        When you use this flare, allies within Close range roll a d6 when they spend a Hope. On a result of 6, they gain the effect of that Hope without spending it. The flare lasts until the end of the scene.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Consumables Tables Reference -->
                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-8">
                                    <h3 class="font-outfit text-2xl font-bold text-amber-300 mb-6">Complete Consumables Tables</h3>
                                    
                                    <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-6 text-center">
                                        <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                            <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <h4 class="font-outfit text-lg font-bold text-yellow-600 mb-3">60+ Consumable Items</h4>
                                        <p class="text-slate-300 text-sm mb-4">From basic trait potions to legendary magical consumables with unique effects</p>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                            <p class="text-yellow-200 text-xs">See complete consumables tables for full descriptions, rarities, and mechanical effects</p>
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
