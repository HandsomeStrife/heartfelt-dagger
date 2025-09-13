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
                                @include('reference.partials.navigation-menu', ['current_page' => 'weapons'])
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
                                        <h1 class="font-outfit text-3xl font-bold text-white">Weapons</h1>
                                        <p class="text-slate-400 text-sm mt-1">Combat Tools & Weapon Properties</p>
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
                                <!-- Weapon Overview -->
                                <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-6 mb-8">
                                    <p class="text-red-100 leading-relaxed text-lg">
                                        All weapons have a <strong class="text-red-300">tier</strong>, <strong class="text-red-300">trait</strong>, <strong class="text-red-300">range</strong>, <strong class="text-red-300">damage die</strong>, <strong class="text-red-300">damage type</strong>, and <strong class="text-red-300">burden</strong>. Some weapons also have a <strong class="text-red-300">feature</strong>.
                                    </p>
                                </div>

                                <!-- Weapon Properties -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Weapon Properties</h2>
                                    
                                    <div class="grid md:grid-cols-2 gap-6">
                                        <!-- Category -->
                                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-blue-300 mb-3">Category</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed mb-3">
                                                        A weapon's <strong class="text-blue-300">category</strong> specifies whether it is a Primary or Secondary weapon.
                                                    </p>
                                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                        <p class="text-blue-200 text-xs">
                                                            Your character can only equip up to <strong>one weapon of each category</strong> at a time.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Trait -->
                                        <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-green-300 mb-3">Trait</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed">
                                                        A weapon's <strong class="text-green-300">trait</strong> specifies which trait to use when making an attack roll with it.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Range -->
                                        <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-purple-300 mb-3">Range</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed">
                                                        A weapon's range specifies the <strong class="text-purple-300">maximum distance</strong> between the attacker and their target when attacking with it.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Damage -->
                                        <div class="bg-orange-500/10 border border-orange-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-orange-300 mb-3">Damage</h3>
                                                    <p class="text-slate-300 text-sm leading-relaxed mb-3">
                                                        A weapon's <strong class="text-orange-300">damage</strong> indicates the size of the damage dice you roll on a successful attack with it.
                                                    </p>
                                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                        <p class="text-orange-200 text-xs">
                                                            You roll a number of dice equal to your <strong>Proficiency</strong>. Flat modifiers are added to the total but not affected by Proficiency.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Advanced Properties -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Advanced Properties</h2>
                                    
                                    <div class="space-y-6">
                                        <!-- Damage Type -->
                                        <div class="bg-indigo-500/10 border border-indigo-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-indigo-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-indigo-300 mb-3">Damage Type</h3>
                                                    <p class="text-slate-300 leading-relaxed mb-4">
                                                        A weapon's <strong class="text-indigo-300">damage type</strong> indicates whether it deals physical or magic damage.
                                                    </p>
                                                    <div class="grid md:grid-cols-2 gap-4">
                                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                            <h4 class="font-outfit text-sm font-bold text-red-400 mb-2">Physical Damage</h4>
                                                            <p class="text-slate-300 text-xs">Standard weapons available to all characters</p>
                                                        </div>
                                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                            <h4 class="font-outfit text-sm font-bold text-blue-400 mb-2">Magic Damage</h4>
                                                            <p class="text-slate-300 text-xs">Requires a Spellcast trait to wield</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Burden -->
                                        <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-yellow-600 mb-3">Burden</h3>
                                                    <p class="text-slate-300 leading-relaxed mb-4">
                                                        A weapon's <strong class="text-yellow-600">burden</strong> indicates how many <strong>hands</strong> it occupies when equipped.
                                                    </p>
                                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                        <p class="text-yellow-200 text-sm">
                                                            <strong class="text-yellow-600">Maximum Burden:</strong> Your character's maximum burden is <strong>2 hands</strong>.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Feature -->
                                        <div class="bg-cyan-500/10 border border-cyan-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-cyan-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-cyan-300 mb-3">Feature</h3>
                                                    <p class="text-slate-300 leading-relaxed">
                                                        A weapon's <strong class="text-cyan-300">feature</strong> is a special rule that stays in effect while the weapon is equipped.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Throwing Weapons -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Throwing Weapons</h2>
                                    
                                    <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-8">
                                        <div class="flex items-start gap-6">
                                            <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="font-outfit text-2xl font-bold text-green-300 mb-4">Throwing Rules</h3>
                                                <p class="text-slate-300 leading-relaxed mb-6">
                                                    You can throw an equipped weapon at a target within <strong class="text-green-300">Very Close range</strong>, making the attack roll with <strong class="text-green-300">Finesse</strong>. On a success, deal damage as usual for that weapon.
                                                </p>
                                                
                                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-6">
                                                    <h4 class="font-outfit text-lg font-bold text-orange-400 mb-4">Important Consequences</h4>
                                                    
                                                    <div class="space-y-4">
                                                        <div class="flex items-start gap-3">
                                                            <svg class="w-5 h-5 text-red-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                                            </svg>
                                                            <div>
                                                                <p class="text-slate-300 text-sm font-medium">Once thrown, the weapon is <strong class="text-red-300">no longer considered equipped</strong></p>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="flex items-start gap-3">
                                                            <svg class="w-5 h-5 text-yellow-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                            <div>
                                                                <p class="text-slate-300 text-sm font-medium">Until you retrieve and re-equip it, you can't attack with it or benefit from its features</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Weapon Tables Reference -->
                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-8">
                                    <h3 class="font-outfit text-2xl font-bold text-amber-300 mb-6">Weapon Tables</h3>
                                    
                                    <div class="grid md:grid-cols-2 gap-6">
                                        <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-6 text-center">
                                            <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                                </svg>
                                            </div>
                                            <h4 class="font-outfit text-lg font-bold text-red-300 mb-3">Primary Weapons</h4>
                                            <p class="text-slate-300 text-sm mb-4">Main combat weapons with higher damage potential</p>
                                            <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                <p class="text-red-200 text-xs">See Primary Weapon Tables for complete stats</p>
                                            </div>
                                        </div>
                                        
                                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-6 text-center">
                                            <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m13 0h-6m-2-5h6m2 5H9l3-3-3-3m2-5h6m2 5H9" />
                                                </svg>
                                            </div>
                                            <h4 class="font-outfit text-lg font-bold text-blue-300 mb-3">Secondary Weapons</h4>
                                            <p class="text-slate-300 text-sm mb-4">Backup weapons and specialized tools</p>
                                            <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                                <p class="text-blue-200 text-xs">See Secondary Weapon Tables for complete stats</p>
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
