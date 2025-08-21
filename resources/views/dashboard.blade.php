<x-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950">
        <div class="px-4 sm:px-6 lg:px-8 pt-12 pb-16">
            <div class="max-w-5xl mx-auto">
                <!-- Welcome Header -->
                <div class="text-center mb-12">
                    <h1 class="font-federant text-4xl text-white tracking-wide mb-2">
                        {{ auth()->user()->username }}
                    </h1>
                    <p class="font-roboto text-slate-300 text-lg">
                        Ready for your next adventure?
                    </p>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
                    <!-- Character Creator -->
                    <div class="group relative">
                        <div class="absolute inset-0 bg-gradient-to-r from-amber-500 to-orange-500 rounded-2xl blur-lg opacity-20 group-hover:opacity-25 transition-opacity duration-300"></div>
                        <div class="relative bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 hover:border-amber-500/30 transition-all duration-300">
                            <div class="flex items-center mb-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-amber-500/20 to-orange-500/20 rounded-xl flex items-center justify-center border border-amber-500/30 mr-4">
                                    <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-white">Character Creator</h3>
                                    <p class="text-slate-400 text-sm">Forge your next hero</p>
                                </div>
                            </div>
                            <a href="{{ route('character-creator') }}" class="inline-flex items-center justify-center w-full bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-black font-semibold py-3 px-4 rounded-xl transition-all duration-300 shadow-lg hover:shadow-amber-500/25">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Create Character
                            </a>
                        </div>
                    </div>

                    <!-- Campaigns -->
                    <div class="group relative">
                        <div class="absolute inset-0 bg-gradient-to-r from-violet-500 to-purple-500 rounded-2xl blur-lg opacity-20 group-hover:opacity-25 transition-opacity duration-300"></div>
                        <div class="relative bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 hover:border-violet-500/30 transition-all duration-300">
                            <div class="flex items-center mb-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-violet-500/20 to-purple-500/20 rounded-xl flex items-center justify-center border border-violet-500/30 mr-4">
                                    <svg class="w-6 h-6 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-white">Campaigns</h3>
                                    <p class="text-slate-400 text-sm">Join epic adventures</p>
                                </div>
                            </div>
                            <a href="{{ route('campaigns') }}" class="inline-flex items-center justify-center w-full bg-gradient-to-r from-violet-500 to-purple-500 hover:from-violet-400 hover:to-purple-400 text-white font-semibold py-3 px-4 rounded-xl transition-all duration-300 shadow-lg hover:shadow-violet-500/25">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                                Browse Campaigns
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-3 gap-4 mb-10">
                    <div class="bg-slate-900/50 backdrop-blur-xl border border-slate-700/50 rounded-xl p-4 text-center">
                        <p class="text-2xl font-bold text-white mb-1">0</p>
                        <p class="text-slate-400 text-sm">Characters</p>
                    </div>
                    <div class="bg-slate-900/50 backdrop-blur-xl border border-slate-700/50 rounded-xl p-4 text-center">
                        <p class="text-2xl font-bold text-white mb-1">0</p>
                        <p class="text-slate-400 text-sm">Campaigns</p>
                    </div>
                    <div class="bg-slate-900/50 backdrop-blur-xl border border-slate-700/50 rounded-xl p-4 text-center">
                        <p class="text-2xl font-bold text-white mb-1">0</p>
                        <p class="text-slate-400 text-sm">Hours</p>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-slate-900/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-white">Recent Activity</h2>
                        <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></div>
                    </div>
                    
                    <!-- Empty State -->
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-slate-800 rounded-xl flex items-center justify-center border border-slate-600 mb-4 mx-auto">
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-white font-semibold mb-2">Your adventure log is empty</h3>
                        <p class="text-slate-400 text-sm mb-4">Start creating characters and joining campaigns to see your activity here.</p>
                        <div class="flex justify-center space-x-3">
                            <a href="{{ route('character-creator') }}" class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-400 text-black text-sm font-semibold rounded-lg transition-colors">
                                Create Character
                            </a>
                            <a href="{{ route('campaigns') }}" class="inline-flex items-center px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold rounded-lg transition-colors border border-slate-600">
                                Browse Campaigns
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>