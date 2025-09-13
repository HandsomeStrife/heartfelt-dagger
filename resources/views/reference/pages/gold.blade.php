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
                                @include('reference.partials.navigation-menu', ['current_page' => 'gold'])
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-8">
                            <!-- Page Header -->
                            <div class="mb-8 flex items-start justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 bg-gradient-to-br from-amber-500 to-yellow-500 rounded-xl flex items-center justify-center shadow-lg">
                                        <svg class="w-10 h-10 text-black" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7L15 1H5C3.9 1 3 1.9 3 3V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V9M12 7C14.8 7 17 9.2 17 12S14.8 17 12 17 7 14.8 7 12 9.2 7 12 7Z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h1 class="font-outfit text-3xl font-bold text-white">Gold</h1>
                                        <p class="text-slate-400 text-sm mt-1">Currency & Wealth Management</p>
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
                                <!-- Gold Overview -->
                                <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-6 mb-8">
                                    <p class="text-amber-100 leading-relaxed text-lg">
                                        Gold is an <strong class="text-amber-300">abstract measurement</strong> of how much wealth a character has, and is measured in <strong class="text-amber-300">handfuls</strong>, <strong class="text-amber-300">bags</strong>, and <strong class="text-amber-300">chests</strong>.
                                    </p>
                                </div>

                                <!-- Currency System -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Currency Denominations</h2>
                                    
                                    <div class="grid md:grid-cols-3 gap-6 mb-8">
                                        <!-- Handfuls -->
                                        <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-6 text-center">
                                            <div class="w-16 h-16 bg-yellow-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                                <svg class="w-8 h-8 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11" />
                                                </svg>
                                            </div>
                                            <h3 class="font-outfit text-xl font-bold text-yellow-600 mb-2">Handfuls</h3>
                                            <p class="text-slate-300 text-sm">Basic currency unit</p>
                                            <div class="mt-3 bg-slate-800/30 border border-slate-600/30 rounded-lg p-2">
                                                <p class="text-yellow-200 text-xs">10 slots available</p>
                                            </div>
                                        </div>

                                        <!-- Bags -->
                                        <div class="bg-orange-500/10 border border-orange-500/30 rounded-xl p-6 text-center">
                                            <div class="w-16 h-16 bg-orange-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                            </div>
                                            <h3 class="font-outfit text-xl font-bold text-orange-300 mb-2">Bags</h3>
                                            <p class="text-slate-300 text-sm">= 10 Handfuls</p>
                                            <div class="mt-3 bg-slate-800/30 border border-slate-600/30 rounded-lg p-2">
                                                <p class="text-orange-200 text-xs">10 slots available</p>
                                            </div>
                                        </div>

                                        <!-- Chests -->
                                        <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-6 text-center">
                                            <div class="w-16 h-16 bg-amber-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                                <svg class="w-8 h-8 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                            </div>
                                            <h3 class="font-outfit text-xl font-bold text-amber-600 mb-2">Chests</h3>
                                            <p class="text-slate-300 text-sm">= 10 Bags = 100 Handfuls</p>
                                            <div class="mt-3 bg-slate-800/30 border border-slate-600/30 rounded-lg p-2">
                                                <p class="text-amber-200 text-xs">1 slot maximum</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Conversion Chart -->
                                    <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-6">
                                        <h3 class="font-outfit text-lg font-bold text-amber-300 mb-4">Conversion Chart</h3>
                                        <div class="flex items-center justify-center">
                                            <div class="flex items-center space-x-4 text-center">
                                                <div class="bg-yellow-500/20 border border-yellow-500/30 rounded-lg p-3">
                                                    <div class="text-yellow-300 font-bold text-lg">10</div>
                                                    <div class="text-slate-300 text-xs">Handfuls</div>
                                                </div>
                                                <div class="text-slate-400">=</div>
                                                <div class="bg-orange-500/20 border border-orange-500/30 rounded-lg p-3">
                                                    <div class="text-orange-300 font-bold text-lg">1</div>
                                                    <div class="text-slate-300 text-xs">Bag</div>
                                                </div>
                                                <div class="text-slate-400">=</div>
                                                <div class="bg-amber-500/20 border border-amber-500/30 rounded-lg p-3">
                                                    <div class="text-amber-300 font-bold text-lg">1/10</div>
                                                    <div class="text-slate-300 text-xs">Chest</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Automatic Conversion -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Automatic Conversion</h2>
                                    
                                    <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-8">
                                        <div class="flex items-start gap-6">
                                            <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="font-outfit text-2xl font-bold text-green-300 mb-4">Conversion Rules</h3>
                                                <p class="text-slate-300 leading-relaxed mb-6">
                                                    When you have marked all of the slots in a category and you gain another gold reward in that category, mark a slot in the following category and <strong class="text-green-300">clear all the slots</strong> in the current one.
                                                </p>
                                                
                                                <div class="space-y-4">
                                                    <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-6">
                                                        <h4 class="font-outfit text-lg font-bold text-yellow-400 mb-3">Example: Handfuls to Bags</h4>
                                                        <p class="text-slate-300 text-sm">
                                                            If you have <strong class="text-yellow-300">9 handfuls</strong> and gain another, you instead mark <strong class="text-orange-300">1 bag</strong> and erase all handfuls.
                                                        </p>
                                                    </div>
                                                    
                                                    <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-6">
                                                        <h4 class="font-outfit text-lg font-bold text-orange-400 mb-3">Example: Bags to Chests</h4>
                                                        <p class="text-slate-300 text-sm">
                                                            If you have <strong class="text-orange-300">9 bags</strong> and gain another, you mark <strong class="text-amber-300">1 chest</strong> and erase all bags.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Wealth Limit -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Wealth Limit</h2>
                                    
                                    <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-8">
                                        <div class="flex items-start gap-6">
                                            <div class="w-16 h-16 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="font-outfit text-2xl font-bold text-red-300 mb-4">Maximum Wealth</h3>
                                                <p class="text-slate-300 leading-relaxed mb-6">
                                                    You can't have more than <strong class="text-red-300">1 chest</strong>, so if all your Gold slots are marked, you'll need to spend some of your gold or store it somewhere else before you can acquire more.
                                                </p>
                                                
                                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-6">
                                                    <h4 class="font-outfit text-lg font-bold text-red-400 mb-3">When At Maximum</h4>
                                                    <ul class="space-y-2 text-slate-300 text-sm">
                                                        <li class="flex items-start gap-2">
                                                            <span class="text-red-400 mt-1">•</span>
                                                            <span>Spend gold on equipment or services</span>
                                                        </li>
                                                        <li class="flex items-start gap-2">
                                                            <span class="text-red-400 mt-1">•</span>
                                                            <span>Store wealth in a safe location</span>
                                                        </li>
                                                        <li class="flex items-start gap-2">
                                                            <span class="text-red-400 mt-1">•</span>
                                                            <span>Invest in property or businesses</span>
                                                        </li>
                                                        <li class="flex items-start gap-2">
                                                            <span class="text-red-400 mt-1">•</span>
                                                            <span>Share with party members</span>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Optional Rule -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Optional Rule: Gold Coins</h2>
                                    
                                    <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-8">
                                        <div class="flex items-start gap-6">
                                            <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7L15 1H5C3.9 1 3 1.9 3 3V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V9M12 7C14.8 7 17 9.2 17 12S14.8 17 12 17 7 14.8 7 12 9.2 7 12 7Z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="font-outfit text-2xl font-bold text-blue-300 mb-4">More Granular Tracking</h3>
                                                <p class="text-slate-300 leading-relaxed mb-6">
                                                    If your group wants to track gold with more granularity, you can add <strong class="text-blue-300">coins</strong> as your lowest denomination. Following the established pattern, <strong class="text-blue-300">10 coins equal 1 handful</strong>.
                                                </p>
                                                
                                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-6">
                                                    <h4 class="font-outfit text-lg font-bold text-blue-400 mb-4">Extended Conversion Chart</h4>
                                                    <div class="flex items-center justify-center">
                                                        <div class="flex items-center space-x-3 text-center text-sm">
                                                            <div class="bg-cyan-500/20 border border-cyan-500/30 rounded-lg p-2">
                                                                <div class="text-cyan-300 font-bold">10</div>
                                                                <div class="text-slate-300 text-xs">Coins</div>
                                                            </div>
                                                            <div class="text-slate-400">=</div>
                                                            <div class="bg-yellow-500/20 border border-yellow-500/30 rounded-lg p-2">
                                                                <div class="text-yellow-300 font-bold">1</div>
                                                                <div class="text-slate-300 text-xs">Handful</div>
                                                            </div>
                                                            <div class="text-slate-400">=</div>
                                                            <div class="bg-orange-500/20 border border-orange-500/30 rounded-lg p-2">
                                                                <div class="text-orange-300 font-bold">1/10</div>
                                                                <div class="text-slate-300 text-xs">Bag</div>
                                                            </div>
                                                            <div class="text-slate-400">=</div>
                                                            <div class="bg-amber-500/20 border border-amber-500/30 rounded-lg p-2">
                                                                <div class="text-amber-300 font-bold">1/100</div>
                                                                <div class="text-slate-300 text-xs">Chest</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Wealth Management Tips -->
                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-8">
                                    <h3 class="font-outfit text-2xl font-bold text-amber-300 mb-6">Wealth Management Tips</h3>
                                    
                                    <div class="grid md:grid-cols-2 gap-6">
                                        <div>
                                            <h4 class="font-outfit text-lg font-bold text-amber-400 mb-4">Tracking</h4>
                                            <ul class="space-y-2 text-slate-300 text-sm">
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>Mark slots as you gain gold</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>Clear lower denominations when converting up</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>Remember the 1 chest maximum</span>
                                                </li>
                                            </ul>
                                        </div>
                                        
                                        <div>
                                            <h4 class="font-outfit text-lg font-bold text-amber-400 mb-4">Spending</h4>
                                            <ul class="space-y-2 text-slate-300 text-sm">
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>Equipment upgrades and repairs</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>Services and information</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>Consumables and supplies</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>Property and investments</span>
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
