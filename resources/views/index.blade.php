<x-layout>
    <div class="h-screen flex items-center justify-center">
        <div class="container mx-auto px-4 py-8 sm:py-16">
            <div class="text-center">
                <div class="flex items-center justify-center mb-6 gap-2 sm:gap-4">
                    <img src="{{ asset('img/logo.png') }}" alt="Heartfelt Dagger Logo" class="w-auto h-12 sm:h-16">
                    <h1 class="text-4xl sm:text-6xl font-bold text-white font-outfit">
                        Heartfelt Dagger
                    </h1>
                </div>
                <div class="mb-4">
                    <span class="inline-block px-3 py-1 bg-amber-500/20 text-amber-400 text-sm font-medium rounded-full border border-amber-500/30">
                        BETA - Feature in Development
                    </span>
                </div>
                <p class="text-lg sm:text-xl text-slate-300 mb-8 max-w-2xl mx-auto px-4">
                    Create your TTRPG character with our interactive character builder. 
                    Choose your class, heritage, and forge your legend in the world of Daggerheart.
                    <br><br>
                    <span class="text-base sm:text-lg text-slate-400">
                        Please note: This character builder is currently in beta and not fully complete. Some features may be missing or under development.
                    </span>
                </p>
                
                <div class="flex flex-col sm:flex-row justify-center gap-4 px-4">
                    <a 
                        href="{{ route('characters') }}" 
                        class="inline-flex gap-2 items-center justify-center px-6 sm:px-8 py-4 bg-slate-700 hover:bg-slate-600 text-white font-semibold rounded-xl transition-all duration-300 transform hover:scale-105 border border-slate-600 hover:border-slate-500"
                    >
                        <x-icons.characters class="size-5"/>
                        My Characters
                    </a>
                    <a 
                        href="{{ route('character-builder') }}" 
                        class="inline-flex items-center justify-center px-6 sm:px-8 py-4 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-black font-semibold rounded-xl transition-all duration-300 transform hover:scale-105"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Create Character
                    </a>
                </div>

                <!-- Bug Report Link -->
                <div class="mt-8 pt-6">
                    <p class="text-sm text-slate-400 mb-3">
                        Found a bug or have feedback?
                    </p>
                    <a 
                        href="{{ route('discord') }}" 
                        target="_blank" 
                        rel="noopener noreferrer" 
                        class="inline-flex items-center gap-2 text-slate-300 hover:text-white transition-colors text-sm font-medium"
                        x-tooltip="Join our Discord to report bugs and give feedback"
                    >
                        <x-icons.discord class="w-4 h-4" />
                        Join our Discord to report issues
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-layout>