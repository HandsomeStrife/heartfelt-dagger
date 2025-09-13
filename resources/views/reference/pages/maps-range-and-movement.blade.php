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
                                @include('reference.partials.navigation-menu', ['current_page' => 'maps-range-and-movement'])
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
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h1 class="font-outfit text-3xl font-bold text-white">Maps, Range, and Movement</h1>
                                        <p class="text-slate-400 text-sm mt-1">Positioning & Distance</p>
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
                                    <p class="text-green-100 leading-relaxed">
                                        You can play Daggerheart using "theater of the mind" or maps and miniatures. The conversions below from abstract ranges to physical measurements assume <strong class="text-green-300">1 inch of map represents about 5 feet</strong> of fictional space.
                                    </p>
                                </div>

                                <!-- Range Categories -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Range Categories</h2>
                                    <p class="text-slate-300 leading-relaxed mb-8">
                                        Daggerheart uses the following <strong class="text-amber-300">ranges</strong> to translate fictional positioning into relative distance for the purposes of targeting, movement, and other game mechanics:
                                    </p>
                                    
                                    <div class="space-y-4">
                                        <!-- Melee -->
                                        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-red-300 mb-2">Melee</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed">
                                                        Close enough to touch, up to a few feet away.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Very Close -->
                                        <div class="bg-orange-500/10 border border-orange-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <span class="text-white font-bold text-sm">VC</span>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-orange-300 mb-2">Very Close</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed mb-2">
                                                        Close enough to see fine details, about <strong>5–10 feet</strong> away. While in danger, a character can move from Very Close range into Melee range as part of their action.
                                                    </p>
                                                    <p class="text-orange-200 text-xs italic">
                                                        On a map: anything within the shortest length of a game card (2-3 inches).
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Close -->
                                        <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <span class="text-black font-bold text-sm">C</span>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-yellow-300 mb-2">Close</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed mb-2">
                                                        Close enough to see prominent details, about <strong>10–30 feet</strong> away. While in danger, a character can move from Close range into Melee range as part of their action.
                                                    </p>
                                                    <p class="text-yellow-200 text-xs italic">
                                                        On a map: anything within the length of a pencil (5-6 inches).
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Far -->
                                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <span class="text-white font-bold text-sm">F</span>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-blue-300 mb-2">Far</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed mb-2">
                                                        Close enough to see very little detail, about <strong>30–100 feet</strong> away. While in danger, a character must make an <strong class="text-blue-300">Agility Roll</strong> to safely move from Far range into Melee range.
                                                    </p>
                                                    <p class="text-blue-200 text-xs italic">
                                                        On a map: anything within the length of the long edge of a piece of copy paper (11–12 inches).
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Very Far -->
                                        <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <span class="text-white font-bold text-sm">VF</span>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-purple-300 mb-2">Very Far</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed mb-2">
                                                        Too far to make out any details, about <strong>100–300 feet</strong> away. While in danger, a character must make an <strong class="text-purple-300">Agility Roll</strong> to safely move from Very Far range into Melee range.
                                                    </p>
                                                    <p class="text-purple-200 text-xs italic">
                                                        On a map: anything beyond Far range, but still within the bounds of the conflict or scene.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Out of Range -->
                                        <div class="bg-slate-600/10 border border-slate-600/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-slate-600 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-slate-300 mb-2">Out of Range</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed">
                                                        Anything beyond a character's Very Far range is Out of Range and usually can't be targeted.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Range Rules -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Range Rules</h2>
                                    
                                    <div class="space-y-4">
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                            <p class="text-slate-300 text-sm leading-relaxed">
                                                Range is measured from the source of an effect, such as the attacker or spellcaster, to the target or object of an effect.
                                            </p>
                                        </div>
                                        
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                            <p class="text-slate-300 text-sm leading-relaxed">
                                                A weapon, spell, ability, item, or other effect's stated range is a maximum range; unless otherwise noted, it can be used at closer distances.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Optional Grid Rules -->
                                <div class="mb-12">
                                    <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-8">
                                        <div class="flex items-start gap-4 mb-6">
                                            <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center flex-shrink-0">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="font-outfit text-2xl font-bold text-indigo-300 mb-4">Optional Rule: Defined Ranges</h3>
                                                <p class="text-slate-300 leading-relaxed mb-6">
                                                    If your table would rather operate with more precise range rules, you can use a 1-inch grid battle map during combat. If you do, use the following guidelines for play:
                                                </p>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                            <div class="bg-slate-700/30 border border-slate-600/30 rounded-lg p-4 text-center">
                                                <div class="text-red-400 font-bold text-lg mb-1">Melee</div>
                                                <div class="text-slate-300 text-sm">1 square</div>
                                            </div>
                                            <div class="bg-slate-700/30 border border-slate-600/30 rounded-lg p-4 text-center">
                                                <div class="text-orange-400 font-bold text-lg mb-1">Very Close</div>
                                                <div class="text-slate-300 text-sm">3 squares</div>
                                            </div>
                                            <div class="bg-slate-700/30 border border-slate-600/30 rounded-lg p-4 text-center">
                                                <div class="text-yellow-400 font-bold text-lg mb-1">Close</div>
                                                <div class="text-slate-300 text-sm">6 squares</div>
                                            </div>
                                            <div class="bg-slate-700/30 border border-slate-600/30 rounded-lg p-4 text-center">
                                                <div class="text-blue-400 font-bold text-lg mb-1">Far</div>
                                                <div class="text-slate-300 text-sm">12 squares</div>
                                            </div>
                                            <div class="bg-slate-700/30 border border-slate-600/30 rounded-lg p-4 text-center">
                                                <div class="text-purple-400 font-bold text-lg mb-1">Very Far</div>
                                                <div class="text-slate-300 text-sm">13+ squares</div>
                                            </div>
                                            <div class="bg-slate-700/30 border border-slate-600/30 rounded-lg p-4 text-center">
                                                <div class="text-slate-400 font-bold text-lg mb-1">Out of Range</div>
                                                <div class="text-slate-300 text-sm">Off the battlemap</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Movement & Combat Rules -->
                                <div class="grid md:grid-cols-2 gap-6">
                                    <!-- Movement Under Pressure -->
                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                        <h3 class="font-outfit text-lg font-bold text-amber-300 mb-4">Movement Under Pressure</h3>
                                        <ul class="space-y-3 text-sm">
                                            <li class="flex items-start gap-2">
                                                <span class="text-amber-400 mt-1">•</span>
                                                <span class="text-slate-300">Move to Close range as part of an action roll</span>
                                            </li>
                                            <li class="flex items-start gap-2">
                                                <span class="text-amber-400 mt-1">•</span>
                                                <span class="text-slate-300">Agility Roll required for longer movement</span>
                                            </li>
                                            <li class="flex items-start gap-2">
                                                <span class="text-amber-400 mt-1">•</span>
                                                <span class="text-slate-300">Adversaries move Close range free with actions</span>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- Area Effects & Line of Sight -->
                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                        <h3 class="font-outfit text-lg font-bold text-amber-300 mb-4">Area Effects & Line of Sight</h3>
                                        <ul class="space-y-3 text-sm">
                                            <li class="flex items-start gap-2">
                                                <span class="text-amber-400 mt-1">•</span>
                                                <span class="text-slate-300">Group effects: targets within Very Close of origin</span>
                                            </li>
                                            <li class="flex items-start gap-2">
                                                <span class="text-amber-400 mt-1">•</span>
                                                <span class="text-slate-300">Line of sight required for ranged attacks</span>
                                            </li>
                                            <li class="flex items-start gap-2">
                                                <span class="text-amber-400 mt-1">•</span>
                                                <span class="text-slate-300">Cover grants disadvantage to attacks</span>
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
</x-layouts.app>
