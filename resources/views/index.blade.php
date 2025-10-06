<x-layout>
    <!-- Hero Section -->
    <section class="py-20 sm:py-32 relative overflow-hidden">        
        <div class="container mx-auto px-4 relative z-10">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Left Content -->
                <div class="max-w-2xl">
                    <!-- Logo and Title -->
                    <div class="mb-8">
                        <div class="flex items-center mb-6 gap-3 sm:gap-4">
                            <img src="{{ asset('img/logo.png') }}" alt="Heartfelt Dagger Logo" class="w-auto h-12 sm:h-16 drop-shadow-2xl">
                            <h1 class="text-4xl sm:text-6xl font-bold text-white font-outfit tracking-tight">
                                Heartfelt Dagger
                            </h1>
                        </div>
                        
                        <!-- Tagline -->
                        <p class="text-lg sm:text-xl text-slate-200 font-light mb-4 font-outfit">
                            Complete TTRPG Platform for Daggerheart
                        </p>
                    </div>
                    
                    <!-- Description -->
                    <div class="mb-10 space-y-6">
                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed">
                            Create characters, manage campaigns, host live sessions, and explore the world of Daggerheart. 
                            Everything you need for epic tabletop adventures in one comprehensive platform.
                        </p>
                        
                        <!-- Beta Notice -->
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800/80 border border-slate-600/50 rounded-xl text-slate-300 backdrop-blur-sm text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="font-medium">Beta Platform - New features being added regularly!</span>
                        </div>
                    </div>
                    
                    <!-- Primary Actions -->
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a 
                            href="{{ route('character-builder') }}" 
                            class="group inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-daggerheart-gold to-amber-400 hover:from-amber-400 hover:to-daggerheart-gold text-black font-bold rounded-xl transition-all duration-300 transform hover:scale-105 hover:shadow-xl hover:shadow-amber-500/25 font-outfit"
                        >
                            <svg class="w-5 h-5 mr-2 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Start Creating
                        </a>
                        <a 
                            href="{{ route('characters') }}" 
                            class="group inline-flex gap-2 items-center justify-center px-6 py-3 bg-slate-800/80 hover:bg-slate-700/80 text-white font-semibold rounded-xl transition-all duration-300 transform hover:scale-105 border border-slate-600/50 hover:border-slate-500 backdrop-blur-sm font-outfit"
                        >
                            <x-icons.characters class="size-4 group-hover:scale-110 transition-transform"/>
                            My Characters
                        </a>
                    </div>
                </div>
                
                <!-- Right Content - Hero Image -->
                <div class="hidden lg:block text-center">
                    <img src="{{ asset('img/hero.png') }}" alt="Heartfelt Dagger Hero Image" class="w-full h-auto">
                    <p class="text-slate-200 opacity-70 text-xs">Artwork courtesy of <a href="https://liquidruby.ca/products/daggerheart-d12-hope-and-fear-dice-2pc-sparkle-holographic-glossy-7-colors-critical-roll?variant=57295188721686" target="_blank" rel="noopener noreferrer" class="underline">Liquid Ruby</a></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Visual Separator -->
    <div class="h-px bg-gradient-to-r from-transparent via-purple-500/50 to-transparent"></div>
    
    <!-- Features Section -->
    <section class="py-20 relative bg-gradient-to-b from-slate-900/50 to-slate-800/30">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4 font-outfit">
                    Complete TTRPG Platform
                </h2>
                <p class="text-lg text-slate-300 max-w-3xl mx-auto">
                    Everything you need for epic Daggerheart adventures - from character creation to live gaming sessions
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-7xl mx-auto">
                <!-- Feature 1: Character Creation -->
                <div class="group text-center p-6 rounded-3xl bg-gradient-to-br from-purple-500/10 to-pink-500/10 backdrop-blur-sm border border-purple-400/30 hover:border-purple-400/60 transition-all duration-300 hover:transform hover:-translate-y-2 hover:shadow-2xl hover:shadow-purple-500/20">
                    <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center group-hover:scale-110 transition-transform shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2 font-outfit">Characters</h3>
                    <p class="text-slate-300 text-sm leading-relaxed">
                        Interactive character builder with SRD-accurate rules and guided creation
                    </p>
                </div>
                
                <!-- Feature 2: Campaign Management -->
                <div class="group text-center p-6 rounded-3xl bg-gradient-to-br from-blue-500/10 to-cyan-500/10 backdrop-blur-sm border border-blue-400/30 hover:border-blue-400/60 transition-all duration-300 hover:transform hover:-translate-y-2 hover:shadow-2xl hover:shadow-blue-500/20">
                    <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-blue-400 to-cyan-400 flex items-center justify-center group-hover:scale-110 transition-transform shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2 font-outfit">Campaigns</h3>
                    <p class="text-slate-300 text-sm leading-relaxed">
                        Create and manage epic campaigns with collaborative storytelling tools
                    </p>
                </div>
                
                <!-- Feature 3: Live Sessions -->
                <div class="group text-center p-6 rounded-3xl bg-gradient-to-br from-emerald-500/10 to-teal-500/10 backdrop-blur-sm border border-emerald-400/30 hover:border-emerald-400/60 transition-all duration-300 hover:transform hover:-translate-y-2 hover:shadow-2xl hover:shadow-emerald-500/20">
                    <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-400 flex items-center justify-center group-hover:scale-110 transition-transform shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2 font-outfit">Live Rooms</h3>
                    <p class="text-slate-300 text-sm leading-relaxed">
                        Host live gaming sessions with webcam support and real-time collaboration
                    </p>
                </div>
                
                <!-- Feature 4: Campaign Frames -->
                <div class="group text-center p-6 rounded-3xl bg-gradient-to-br from-amber-500/10 to-orange-500/10 backdrop-blur-sm border border-amber-400/30 hover:border-amber-400/60 transition-all duration-300 hover:transform hover:-translate-y-2 hover:shadow-2xl hover:shadow-amber-500/20">
                    <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-400 flex items-center justify-center group-hover:scale-110 transition-transform shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2 font-outfit">Frameworks</h3>
                    <p class="text-slate-300 text-sm leading-relaxed">
                        Pre-built campaign templates and frameworks to jumpstart your adventures
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Visual Separator -->
    <div class="h-px bg-gradient-to-r from-transparent via-cyan-500/50 to-transparent"></div>
    
    <!-- Community Section -->
    <section class="py-20 relative bg-gradient-to-b from-slate-800/30 to-slate-900/80">
        <!-- Background Gradient Orbs -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-gradient-to-r from-purple-500/10 to-pink-500/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-gradient-to-r from-blue-500/10 to-cyan-500/10 rounded-full blur-3xl"></div>
        </div>
        
        <div class="container mx-auto px-4 relative z-10">
            <div class="mx-auto text-center">
                <h2 class="text-3xl sm:text-4xl font-bold text-white mb-6 font-outfit">
                    Join the Adventure
                </h2>
                <p class="text-lg text-slate-300 mb-12 max-w-3xl mx-auto">
                    Connect with fellow Game Masters and players, contribute to the platform's development, and help shape the future of digital TTRPG experiences
                </p>
                
                <div class="grid sm:grid-cols-3 gap-6 mb-12">
                    <a 
                        href="https://github.com/HandsomeStrife/heartfelt-dagger" 
                        target="_blank" 
                        rel="noopener noreferrer" 
                        class="group p-6 bg-gradient-to-br from-slate-800/40 to-slate-700/40 hover:from-slate-700/60 hover:to-slate-600/60 rounded-2xl transition-all duration-300 border border-slate-600/50 hover:border-purple-400/50 backdrop-blur-sm"
                        x-tooltip="Contribute code, report bugs, or suggest features"
                    >
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 mb-4 rounded-xl bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center group-hover:scale-110 transition-transform shadow-lg">
                                <x-icons.github class="w-6 h-6 text-white" />
                            </div>
                            <h3 class="font-outfit font-bold text-white mb-2">Open Source</h3>
                            <p class="text-slate-300 text-sm">Contribute to development</p>
                        </div>
                    </a>
                    
                    <a 
                        href="{{ route('discord') }}" 
                        target="_blank" 
                        rel="noopener noreferrer" 
                        class="group p-6 bg-gradient-to-br from-slate-800/40 to-slate-700/40 hover:from-slate-700/60 hover:to-slate-600/60 rounded-2xl transition-all duration-300 border border-slate-600/50 hover:border-indigo-400/50 backdrop-blur-sm"
                        x-tooltip="Join our Discord community for support and discussion"
                    >
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 mb-4 rounded-xl bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center group-hover:scale-110 transition-transform shadow-lg">
                                <x-icons.discord class="w-6 h-6 text-white" />
                            </div>
                            <h3 class="font-outfit font-bold text-white mb-2">Community</h3>
                            <p class="text-slate-300 text-sm">Connect with players</p>
                        </div>
                    </a>
                    
                    <a 
                        href="https://www.daggerheart.com/buy/" 
                        target="_blank" 
                        rel="noopener noreferrer" 
                        class="group p-6 bg-gradient-to-br from-slate-800/40 to-slate-700/40 hover:from-slate-700/60 hover:to-slate-600/60 rounded-2xl transition-all duration-300 border border-slate-600/50 hover:border-amber-400/50 backdrop-blur-sm"
                        x-tooltip="Support the creators - Buy the official game"
                    >
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 mb-4 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center group-hover:scale-110 transition-transform shadow-lg">
                                <x-icons.daggerheart class="w-6 h-6 text-white" />
                            </div>
                            <h3 class="font-outfit font-bold text-white mb-2">Official Game</h3>
                            <p class="text-slate-300 text-sm">Support the creators</p>
                        </div>
                    </a>
                </div>
                
                <div class="max-w-3xl mx-auto">
                    <div class="p-6 bg-gradient-to-r from-emerald-500/10 to-cyan-500/10 rounded-2xl border border-emerald-400/20 backdrop-blur-sm">
                        <p class="text-emerald-300 font-medium mb-2">
                            🚀 Open Source & Community Driven
                        </p>
                        <p class="text-slate-300 text-sm">
                            This platform is built by the community, for the community. Help us improve by contributing code, reporting bugs, suggesting features, or just sharing your epic adventures with fellow players!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layout>