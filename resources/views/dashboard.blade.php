<x-layout>
    <div class="min-h-screen">
        <div class="px-4 sm:px-6 lg:px-8 pt-12 pb-16">
            <div class="max-w-4xl mx-auto">
                <!-- Development Notice -->
                <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-4 mb-8">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-amber-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <div>
                            <h3 class="text-amber-400 font-outfit font-semibold">Under Development</h3>
                            <p class="text-amber-300/80 text-sm">All features are currently in active development. Expect changes and improvements!</p>
                        </div>
                    </div>
                </div>

                <!-- Welcome Header -->
                <div class="text-center mb-12">
                    <h1 class="font-outfit text-4xl text-white tracking-wide mb-2">
                        Welcome, {{ auth()->user()->username }}
                    </h1>
                    <p class="text-slate-300 text-lg">
                        Ready for your next adventure?
                    </p>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Character Creator -->
                    <div class="group relative">
                        <div class="absolute inset-0 bg-gradient-to-r from-amber-500 to-orange-500 rounded-2xl blur-lg opacity-20 group-hover:opacity-25 transition-opacity duration-300"></div>
                        <div class="relative bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 hover:border-amber-500/30 transition-all duration-300">
                            <div class="text-center">
                                <div class="w-12 h-12 bg-gradient-to-br from-amber-500/20 to-orange-500/20 rounded-xl flex items-center justify-center border border-amber-500/30 mx-auto mb-4">
                                    <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <h3 class="font-outfit text-lg font-bold text-white mb-2">Characters</h3>
                                <p class="text-slate-400 text-sm mb-4">Forge your heroes</p>
                                <a href="{{ route('character-builder') }}" class="inline-flex items-center justify-center w-full bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-black font-semibold py-2 px-4 rounded-xl transition-all duration-300 text-sm">
                                    Create
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Campaigns -->
                    <div class="group relative">
                        <div class="absolute inset-0 bg-gradient-to-r from-violet-500 to-purple-500 rounded-2xl blur-lg opacity-20 group-hover:opacity-25 transition-opacity duration-300"></div>
                        <div class="relative bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 hover:border-violet-500/30 transition-all duration-300">
                            <div class="text-center">
                                <div class="w-12 h-12 bg-gradient-to-br from-violet-500/20 to-purple-500/20 rounded-xl flex items-center justify-center border border-violet-500/30 mx-auto mb-4">
                                    <svg class="w-6 h-6 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h3 class="font-outfit text-lg font-bold text-white mb-2">Campaigns</h3>
                                <p class="text-slate-400 text-sm mb-4">Epic adventures</p>
                                <a href="{{ route('campaigns.index') }}" class="inline-flex items-center justify-center w-full bg-gradient-to-r from-violet-500 to-purple-500 hover:from-violet-400 hover:to-purple-400 text-white font-semibold py-2 px-4 rounded-xl transition-all duration-300 text-sm">
                                    Browse
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Rooms -->
                    <div class="group relative">
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-2xl blur-lg opacity-20 group-hover:opacity-25 transition-opacity duration-300"></div>
                        <div class="relative bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 hover:border-emerald-500/30 transition-all duration-300">
                            <div class="text-center">
                                <div class="w-12 h-12 bg-gradient-to-br from-emerald-500/20 to-teal-500/20 rounded-xl flex items-center justify-center border border-emerald-500/30 mx-auto mb-4">
                                    <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <h3 class="font-outfit text-lg font-bold text-white mb-2">Rooms</h3>
                                <p class="text-slate-400 text-sm mb-4">Live sessions</p>
                                <a href="{{ route('rooms.index') }}" class="inline-flex items-center justify-center w-full bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-white font-semibold py-2 px-4 rounded-xl transition-all duration-300 text-sm">
                                    Browse
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Campaign Frames -->
                    <div class="group relative">
                        <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-blue-500 rounded-2xl blur-lg opacity-20 group-hover:opacity-25 transition-opacity duration-300"></div>
                        <div class="relative bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 hover:border-indigo-500/30 transition-all duration-300">
                            <div class="text-center">
                                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500/20 to-blue-500/20 rounded-xl flex items-center justify-center border border-indigo-500/30 mx-auto mb-4">
                                    <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </div>
                                <h3 class="font-outfit text-lg font-bold text-white mb-2">Frames</h3>
                                <p class="text-slate-400 text-sm mb-4">Campaign templates</p>
                                <a href="{{ route('campaign-frames.index') }}" class="inline-flex items-center justify-center w-full bg-gradient-to-r from-indigo-500 to-blue-500 hover:from-indigo-400 hover:to-blue-400 text-white font-semibold py-2 px-4 rounded-xl transition-all duration-300 text-sm">
                                    Manage
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Storage Accounts -->
                    <div class="group relative">
                        <div class="absolute inset-0 bg-gradient-to-r from-slate-500 to-gray-500 rounded-2xl blur-lg opacity-20 group-hover:opacity-25 transition-opacity duration-300"></div>
                        <div class="relative bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 hover:border-slate-500/30 transition-all duration-300">
                            <div class="text-center">
                                <div class="w-12 h-12 bg-gradient-to-br from-slate-500/20 to-gray-500/20 rounded-xl flex items-center justify-center border border-slate-500/30 mx-auto mb-4">
                                    <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                                    </svg>
                                </div>
                                <h3 class="font-outfit text-lg font-bold text-white mb-2">Storage</h3>
                                <p class="text-slate-400 text-sm mb-4">Cloud accounts</p>
                                <a href="{{ route('storage-accounts') }}" class="inline-flex items-center justify-center w-full bg-gradient-to-r from-slate-500 to-gray-500 hover:from-slate-400 hover:to-gray-400 text-white font-semibold py-2 px-4 rounded-xl transition-all duration-300 text-sm">
                                    Manage
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Video Library -->
                    <div class="group relative">
                        <div class="absolute inset-0 bg-gradient-to-r from-purple-500 to-indigo-500 rounded-2xl blur-lg opacity-20 group-hover:opacity-25 transition-opacity duration-300"></div>
                        <div class="relative bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 hover:border-purple-500/30 transition-all duration-300">
                            <div class="text-center">
                                <div class="w-12 h-12 bg-gradient-to-br from-purple-500/20 to-indigo-500/20 rounded-xl flex items-center justify-center border border-purple-500/30 mx-auto mb-4">
                                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <h3 class="font-outfit text-lg font-bold text-white mb-2">Video Library</h3>
                                <p class="text-slate-400 text-sm mb-4">Recorded sessions</p>
                                <a href="{{ route('video-library') }}" class="inline-flex items-center justify-center w-full bg-gradient-to-r from-purple-500 to-indigo-500 hover:from-purple-400 hover:to-indigo-400 text-white font-semibold py-2 px-4 rounded-xl transition-all duration-300 text-sm">
                                    Browse
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>