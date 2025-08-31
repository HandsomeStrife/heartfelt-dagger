<x-layout>
    <div class="min-h-screen p-4">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="font-federant text-4xl text-white tracking-wide mb-2">Campaigns</h1>
                <p class="font-roboto text-white/70 text-lg">Epic adventures await brave heroes</p>
            </div>

            <!-- Coming Soon Card -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-amber-500/30 rounded-lg p-8 shadow-2xl text-center">
                <div class="mb-6">
                    <svg class="w-24 h-24 mx-auto text-amber-400/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>

                <h2 class="text-2xl font-bold text-white mb-4">Coming Soon</h2>
                <p class="font-roboto text-white/70 text-lg mb-6">
                    The campaign system is being crafted with care. Soon you'll be able to create and join epic adventures with fellow heroes!
                </p>

                <div class="flex justify-center space-x-4">
                    <a href="{{ route('dashboard') }}" class="bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-400 hover:to-yellow-400 text-black font-bold py-3 px-6 rounded-lg transition-all duration-300 shadow-lg hover:shadow-amber-500/50 transform hover:scale-105 font-roboto">
                        <svg class="w-5 h-5 inline-block mr-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                        </svg>
                        Go to Dashboard
                    </a>
                </div>
            </div>

            <!-- Decorative Elements -->
            <div class="mt-8 text-center">
                <div class="flex justify-center space-x-2 text-purple-400/50">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2L13.5 8.5L20 10L13.5 11.5L12 18L10.5 11.5L4 10L10.5 8.5L12 2Z"/>
                    </svg>
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2L13.5 8.5L20 10L13.5 11.5L12 18L10.5 11.5L4 10L10.5 8.5L12 2Z"/>
                    </svg>
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2L13.5 8.5L20 10L13.5 11.5L12 18L10.5 11.5L4 10L10.5 8.5L12 2Z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</x-layout>
