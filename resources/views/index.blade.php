<x-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
        <div class="container mx-auto px-4 py-16">
            <div class="text-center">
                <div class="flex items-center justify-center mb-6 gap-4">
                    <img src="{{ asset('img/logo.png') }}" alt="Heartfelt Dagger Logo" class="w-auto h-16">
                    <h1 class="text-6xl font-bold text-white font-outfit">
                        Heartfelt Dagger
                    </h1>
                </div>
                <div class="mb-4">
                    <span class="inline-block px-3 py-1 bg-amber-500/20 text-amber-400 text-sm font-medium rounded-full border border-amber-500/30">
                        BETA - Feature in Development
                    </span>
                </div>
                <p class="text-xl text-slate-300 mb-8 max-w-2xl mx-auto">
                    Create your TTRPG character with our interactive character builder. 
                    Choose your class, heritage, and forge your legend in the world of Daggerheart.
                    <br><br>
                    <span class="text-lg text-slate-400">
                        Please note: This character builder is currently in beta and not fully complete. Some features may be missing or under development.
                    </span>
                </p>
                
                <div class="flex justify-center gap-4">
                    <a 
                        href="{{ route('characters') }}" 
                        class="inline-flex items-center px-8 py-4 bg-slate-700 hover:bg-slate-600 text-white font-semibold rounded-xl transition-all duration-300 transform hover:scale-105 border border-slate-600 hover:border-slate-500"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        My Characters
                    </a>
                    <a 
                        href="{{ route('character-builder') }}" 
                        class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-black font-semibold rounded-xl transition-all duration-300 transform hover:scale-105"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Create Character
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layout>