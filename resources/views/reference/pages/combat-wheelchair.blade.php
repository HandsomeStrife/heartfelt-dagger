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
                                @include('reference.partials.navigation-menu', ['current_page' => 'combat-wheelchair'])
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-8">
                            <!-- Page Header -->
                            <div class="mb-8 flex items-start justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center shadow-lg">
                                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h1 class="font-outfit text-3xl font-bold text-white">Combat Wheelchair</h1>
                                        <p class="text-slate-400 text-sm mt-1">Accessibility & Inclusive Design</p>
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
                                <!-- Author Credit -->
                                <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl p-6 mb-8">
                                    <div class="flex items-center gap-3 mb-3">
                                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span class="text-purple-300 font-semibold">By Mark Thompson</span>
                                    </div>
                                    <p class="text-purple-100 leading-relaxed">
                                        The combat wheelchair is a ruleset designed to help you play a wheelchair user in Daggerheart. This section provides mechanics and narrative guidance for you to work from, but feel free to adapt the flavor text to best suit your character. <strong class="text-purple-300">Have fun with your character's wheelchair design, and make it as unique or tailored to them as you please.</strong>
                                    </p>
                                </div>

                                <!-- Action and Movement -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Action and Movement</h2>
                                    
                                    <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-6">
                                        <h3 class="font-outfit text-lg font-bold text-green-300 mb-4">Movement Descriptions</h3>
                                        <p class="text-slate-300 leading-relaxed mb-4">
                                            When describing how your character moves, you can use descriptions such as the following:
                                        </p>
                                        
                                        <div class="space-y-3">
                                            <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                <p class="text-green-200 text-sm italic">
                                                    "I roll over to the door to see if it's open."
                                                </p>
                                            </div>
                                            <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                <p class="text-green-200 text-sm italic">
                                                    "I wheel myself over to the group to ask what's going on."
                                                </p>
                                            </div>
                                            <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                <p class="text-green-200 text-sm italic">
                                                    "I pull my brakes and skid to a halt, turning in my seat to level my bow at the intruder."
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Consequences -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Consequences</h2>
                                    
                                    <div class="bg-orange-500/10 border border-orange-500/30 rounded-xl p-6 mb-6">
                                        <h3 class="font-outfit text-lg font-bold text-orange-300 mb-4">Complication Examples</h3>
                                        <p class="text-slate-300 leading-relaxed mb-4">
                                            Here are some ways you might describe complications you encounter when your character uses their wheelchair:
                                        </p>
                                        
                                        <div class="space-y-3">
                                            <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                <p class="text-orange-200 text-sm italic">
                                                    "I pull my brakes, but I don't think to account for the loose gravel on the ground."
                                                </p>
                                            </div>
                                            <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                <p class="text-orange-200 text-sm italic">
                                                    "I hit a patch of ice awkwardly and am sent skidding out past my target."
                                                </p>
                                            </div>
                                            <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                <p class="text-orange-200 text-sm italic">
                                                    "I go to push off in pursuit, but one of my front caster wheels snags on a crack in the pavement, stalling me for a moment."
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-6">
                                        <div class="flex items-start gap-4">
                                            <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="font-outfit text-xl font-bold text-red-300 mb-3">Important GM Guideline</h3>
                                                <p class="text-slate-300 leading-relaxed">
                                                    GMs should avoid <strong class="text-red-300">breaking a character's wheelchair</strong> or otherwise removing it from play as a consequence, unless <strong class="text-red-300">everyone at the table</strong>, especially the wheelchair user's player, gives their approval.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Game Mechanics -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Game Mechanics</h2>
                                    
                                    <div class="grid md:grid-cols-2 gap-6">
                                        <!-- Evasion -->
                                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-blue-300 mb-3">Evasion</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed mb-3">
                                                        Your character is assumed to be <strong class="text-blue-300">skilled in moving their wheelchair</strong> and navigating numerous situations in it.
                                                    </p>
                                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                        <p class="text-blue-200 text-xs">
                                                            Only the <strong>Heavy Frame model</strong> gives a penalty to Evasion.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Burden -->
                                        <div class="bg-cyan-500/10 border border-cyan-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-cyan-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-cyan-300 mb-3">Burden</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed mb-3">
                                                        All wheelchairs can be maneuvered using <strong class="text-cyan-300">one or two hands</strong> outside of combat.
                                                    </p>
                                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                        <p class="text-cyan-200 text-xs">
                                                            When used as a weapon, burden depends on the model chosen.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Accessibility Options -->
                                    <div class="mt-6 bg-indigo-500/10 border border-indigo-500/30 rounded-xl p-6">
                                        <h3 class="font-outfit text-lg font-bold text-indigo-300 mb-4">Accessibility Adaptations</h3>
                                        <p class="text-slate-300 leading-relaxed mb-4">
                                            If you're playing a character who has limited to no mobility in their arms, their wheelchair can be <strong class="text-indigo-300">attuned to them by magical means</strong>. For example, your character might use a psychic link to guide the chair around like a pseudo-electric wheelchair.
                                        </p>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                            <p class="text-indigo-200 text-sm">
                                                <strong class="text-indigo-300">Design Philosophy:</strong> All the rules presented here can be tailored and adapted to any character's needs.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Wheelchair Models -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Choosing Your Model</h2>
                                    
                                    <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl p-6 mb-8">
                                        <p class="text-purple-100 leading-relaxed text-lg">
                                            All combat wheelchairs are equipped as <strong class="text-purple-300">Primary Weapons</strong>. There are <strong class="text-purple-300">three models</strong> of wheelchair available: light, heavy, and arcane. You're encouraged to consider the type of character you're playing and the class they belong to, then choose the model that best matches that character concept.
                                        </p>
                                    </div>

                                    <!-- Light Frame Models -->
                                    <div class="mb-8">
                                        <h3 class="font-outfit text-xl font-bold text-green-400 mb-4">Light Frame Models</h3>
                                        <p class="text-slate-300 leading-relaxed mb-6">
                                            Though tough, these wheelchairs have light frames that allow the chair to move with your character in more acrobatic ways. These models are best suited to adventurers who rely on <strong class="text-green-300">speed and flexibility</strong>.
                                        </p>
                                        
                                        <div class="overflow-x-auto">
                                            <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-6">
                                                <div class="space-y-4">
                                                    <!-- Light-Frame Wheelchair -->
                                                    <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4">
                                                        <div class="flex flex-wrap items-center gap-4 mb-3">
                                                            <h4 class="font-outfit text-lg font-bold text-green-300">Light-Frame Wheelchair</h4>
                                                            <div class="flex gap-2">
                                                                <span class="px-2 py-1 bg-slate-700 text-slate-300 text-xs rounded">Tier 1</span>
                                                                <span class="px-2 py-1 bg-blue-600 text-white text-xs rounded">Agility</span>
                                                                <span class="px-2 py-1 bg-red-600 text-white text-xs rounded">d8 phy</span>
                                                                <span class="px-2 py-1 bg-yellow-600 text-black text-xs rounded">One-Handed</span>
                                                            </div>
                                                        </div>
                                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                            <p class="text-green-200 text-sm">
                                                                <strong class="text-green-300">Quick:</strong> When you make an attack, you can mark a Stress to target another creature within range.
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <!-- Improved Light-Frame -->
                                                    <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4">
                                                        <div class="flex flex-wrap items-center gap-4 mb-3">
                                                            <h4 class="font-outfit text-lg font-bold text-green-300">Improved Light-Frame Wheelchair</h4>
                                                            <div class="flex gap-2">
                                                                <span class="px-2 py-1 bg-slate-700 text-slate-300 text-xs rounded">Tier 2</span>
                                                                <span class="px-2 py-1 bg-blue-600 text-white text-xs rounded">Agility</span>
                                                                <span class="px-2 py-1 bg-red-600 text-white text-xs rounded">d8+3 phy</span>
                                                                <span class="px-2 py-1 bg-yellow-600 text-black text-xs rounded">One-Handed</span>
                                                            </div>
                                                        </div>
                                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                            <p class="text-green-200 text-sm">
                                                                <strong class="text-green-300">Quick:</strong> When you make an attack, you can mark a Stress to target another creature within range.
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <!-- Advanced Light-Frame -->
                                                    <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4">
                                                        <div class="flex flex-wrap items-center gap-4 mb-3">
                                                            <h4 class="font-outfit text-lg font-bold text-green-300">Advanced Light-Frame Wheelchair</h4>
                                                            <div class="flex gap-2">
                                                                <span class="px-2 py-1 bg-slate-700 text-slate-300 text-xs rounded">Tier 3</span>
                                                                <span class="px-2 py-1 bg-blue-600 text-white text-xs rounded">Agility</span>
                                                                <span class="px-2 py-1 bg-red-600 text-white text-xs rounded">d8+6 phy</span>
                                                                <span class="px-2 py-1 bg-yellow-600 text-black text-xs rounded">One-Handed</span>
                                                            </div>
                                                        </div>
                                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                            <p class="text-green-200 text-sm">
                                                                <strong class="text-green-300">Quick:</strong> When you make an attack, you can mark a Stress to target another creature within range.
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <!-- Legendary Light-Frame -->
                                                    <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4">
                                                        <div class="flex flex-wrap items-center gap-4 mb-3">
                                                            <h4 class="font-outfit text-lg font-bold text-green-300">Legendary Light-Frame Wheelchair</h4>
                                                            <div class="flex gap-2">
                                                                <span class="px-2 py-1 bg-slate-700 text-slate-300 text-xs rounded">Tier 4</span>
                                                                <span class="px-2 py-1 bg-blue-600 text-white text-xs rounded">Agility</span>
                                                                <span class="px-2 py-1 bg-red-600 text-white text-xs rounded">d8+9 phy</span>
                                                                <span class="px-2 py-1 bg-yellow-600 text-black text-xs rounded">One-Handed</span>
                                                            </div>
                                                        </div>
                                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                            <p class="text-green-200 text-sm">
                                                                <strong class="text-green-300">Quick:</strong> When you make an attack, you can mark a Stress to target another creature within range.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Heavy Frame Models -->
                                    <div class="mb-8">
                                        <h3 class="font-outfit text-xl font-bold text-red-400 mb-4">Heavy Frame Models</h3>
                                        <p class="text-slate-300 leading-relaxed mb-6">
                                            These wheelchairs have bulky and heavier frames, allowing the chair to lend its weight to your character's attacks. <strong class="text-red-300">It also makes them a bigger target.</strong>
                                        </p>
                                        
                                        <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-6">
                                            <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 text-center">
                                                <p class="text-red-300 text-sm">
                                                    <strong>Note:</strong> Heavy Frame models provide enhanced damage but may affect mobility. See full Heavy Frame table for complete specifications.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Arcane Models -->
                                    <div>
                                        <h3 class="font-outfit text-xl font-bold text-purple-400 mb-4">Arcane Frame Models</h3>
                                        <p class="text-slate-300 leading-relaxed mb-6">
                                            Magically enhanced wheelchairs that provide unique spellcasting capabilities and magical features for characters with Spellcast traits.
                                        </p>
                                        
                                        <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-6">
                                            <div class="bg-purple-500/10 border border-purple-500/30 rounded-lg p-4 text-center">
                                                <p class="text-purple-300 text-sm">
                                                    <strong>Note:</strong> Arcane Frame models integrate magical abilities with mobility. See full Arcane Frame table for magical features and spellcasting bonuses.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Design Philosophy -->
                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-8">
                                    <h3 class="font-outfit text-2xl font-bold text-amber-300 mb-6">Design Philosophy</h3>
                                    
                                    <div class="grid md:grid-cols-2 gap-6">
                                        <div>
                                            <h4 class="font-outfit text-lg font-bold text-amber-400 mb-4">Customization</h4>
                                            <ul class="space-y-2 text-slate-300 text-sm">
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>Make the wheelchair as unique as your character</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>Adapt flavor text to suit your character concept</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>Consider magical or technological enhancements</span>
                                                </li>
                                            </ul>
                                        </div>
                                        
                                        <div>
                                            <h4 class="font-outfit text-lg font-bold text-amber-400 mb-4">Inclusivity</h4>
                                            <ul class="space-y-2 text-slate-300 text-sm">
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>Rules can be adapted to any character's needs</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>Magical attunement for different mobility levels</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>Respectful representation and gameplay</span>
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
