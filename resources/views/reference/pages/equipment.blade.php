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
                                @include('reference.partials.navigation-menu', ['current_page' => 'equipment'])
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-8">
                            <!-- Page Header -->
                            <div class="mb-8 flex items-start justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 bg-gradient-to-br from-amber-500 to-orange-500 rounded-xl flex items-center justify-center shadow-lg">
                                        <svg class="w-10 h-10 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h1 class="font-outfit text-3xl font-bold text-white">Equipment</h1>
                                        <p class="text-slate-400 text-sm mt-1">Weapons, Armor & Gear</p>
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
                                <!-- Equipment Overview -->
                                <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-6 mb-8">
                                    <p class="text-amber-100 leading-relaxed text-lg">
                                        Your <strong class="text-amber-300">equipped</strong> weapons and armor are the ones listed in the "Active Weapons" and "Active Armor" sections of your character sheet. Your character can only attack with weapons, benefit from armor, and gain features from items they have equipped.
                                    </p>
                                </div>

                                <!-- Tier Restrictions -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Tier Restrictions</h2>
                                    
                                    <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-6">
                                        <div class="flex items-start gap-4">
                                            <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="font-outfit text-xl font-bold text-red-300 mb-3">Equipment Tier Limit</h3>
                                                <p class="text-slate-300 leading-relaxed">
                                                    You can't equip weapons or armor with a <strong class="text-red-300">higher tier than you</strong>. This ensures equipment progression matches character advancement.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Weapon Management -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Weapon Management</h2>
                                    
                                    <div class="space-y-6">
                                        <!-- Inventory Capacity -->
                                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-blue-300 mb-3">Inventory Capacity</h3>
                                                    <p class="text-slate-300 leading-relaxed">
                                                        PCs can carry up to <strong class="text-blue-300">two additional weapons</strong> in the "Inventory Weapon" area of the character sheet.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Weapon Swapping -->
                                        <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-green-300 mb-3">Weapon Swapping</h3>
                                                    <p class="text-slate-300 leading-relaxed mb-4">
                                                        You can swap an Inventory Weapon with an Active Weapon under different circumstances:
                                                    </p>
                                                    <div class="grid md:grid-cols-2 gap-4">
                                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                            <h4 class="font-outfit text-sm font-bold text-green-400 mb-2">Free Swap</h4>
                                                            <p class="text-slate-300 text-sm">During a rest or moment of calm</p>
                                                        </div>
                                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                            <h4 class="font-outfit text-sm font-bold text-orange-400 mb-2">Stress Cost</h4>
                                                            <p class="text-slate-300 text-sm">Mark a Stress to swap in combat or danger</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Armor Management -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Armor Management</h2>
                                    
                                    <div class="space-y-6">
                                        <!-- Single Armor Rule -->
                                        <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-purple-300 mb-3">Single Active Armor</h3>
                                                    <p class="text-slate-300 leading-relaxed">
                                                        Your character can only have <strong class="text-purple-300">one Active Armor</strong> at a time. You can't carry armor in your inventory.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Armor Equipping Rules -->
                                        <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-yellow-600 mb-3">Equipping Restrictions</h3>
                                                    <p class="text-slate-300 leading-relaxed mb-4">
                                                        They can't equip armor <strong class="text-yellow-600">while in danger or under pressure</strong>; otherwise, they can equip or unequip armor without cost.
                                                    </p>
                                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-4">
                                                        <p class="text-slate-300 text-sm">
                                                            <strong class="text-yellow-600">Important:</strong> When your character equips or unequips armor, recalculate your damage thresholds.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Armor Slot Tracking -->
                                        <div class="bg-orange-500/10 border border-orange-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-orange-300 mb-3">Armor Slot Tracking</h3>
                                                    <p class="text-slate-300 leading-relaxed">
                                                        Each armor has its own <strong class="text-orange-300">Armor Slots</strong>; if your character unequips their armor, track how many of its Armor Slots are marked for when they re-equip it later.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Equipment Categories -->
                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-8">
                                    <h3 class="font-outfit text-2xl font-bold text-amber-300 mb-6">Equipment Categories</h3>
                                    
                                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        <!-- Weapons -->
                                        <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 text-center">
                                            <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-3">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                                </svg>
                                            </div>
                                            <div class="text-red-400 font-bold text-lg mb-2">Weapons</div>
                                            <div class="text-slate-300 text-sm">Primary & Secondary</div>
                                        </div>
                                        
                                        <!-- Armor -->
                                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4 text-center">
                                            <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-3">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                </svg>
                                            </div>
                                            <div class="text-blue-400 font-bold text-lg mb-2">Armor</div>
                                            <div class="text-slate-300 text-sm">Protection & Thresholds</div>
                                        </div>
                                        
                                        <!-- Items -->
                                        <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4 text-center">
                                            <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-3">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                            </div>
                                            <div class="text-green-400 font-bold text-lg mb-2">Loot</div>
                                            <div class="text-slate-300 text-sm">Items & Consumables</div>
                                        </div>
                                        
                                        <!-- Combat Wheelchair -->
                                        <div class="bg-purple-500/10 border border-purple-500/30 rounded-lg p-4 text-center">
                                            <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center mx-auto mb-3">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                </svg>
                                            </div>
                                            <div class="text-purple-400 font-bold text-lg mb-2">Combat Wheelchair</div>
                                            <div class="text-slate-300 text-sm">Accessibility Options</div>
                                        </div>
                                        
                                        <!-- Consumables -->
                                        <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-4 text-center">
                                            <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center mx-auto mb-3">
                                                <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z" />
                                                </svg>
                                            </div>
                                            <div class="text-yellow-600 font-bold text-lg mb-2">Consumables</div>
                                            <div class="text-slate-300 text-sm">Potions & One-Use Items</div>
                                        </div>
                                        
                                        <!-- Gold -->
                                        <div class="bg-amber-500/10 border border-amber-500/30 rounded-lg p-4 text-center">
                                            <div class="w-12 h-12 bg-amber-500 rounded-full flex items-center justify-center mx-auto mb-3">
                                                <svg class="w-6 h-6 text-black" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7L15 1H5C3.9 1 3 1.9 3 3V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V9M12 7C14.8 7 17 9.2 17 12S14.8 17 12 17 7 14.8 7 12 9.2 7 12 7Z"/>
                                                </svg>
                                            </div>
                                            <div class="text-amber-600 font-bold text-lg mb-2">Gold</div>
                                            <div class="text-slate-300 text-sm">Currency & Wealth</div>
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
