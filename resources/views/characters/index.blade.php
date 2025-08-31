<x-layout>
    <x-sub-navigation>
        <div class="flex items-center justify-between">
            <!-- Left: Title and Description -->
            <div class="flex items-center gap-4">
                <div>
                    <h1 class="font-outfit text-lg font-semibold text-white">
                        Your Characters
                    </h1>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Manage your created Daggerheart characters
                    </p>
                </div>
            </div>
            
            <!-- Right: Actions -->
            <div class="flex items-center gap-2">
                <!-- Create New Character Button -->
                <a href="{{ route('character-builder') }}" 
                   class="inline-flex items-center justify-center px-3 py-1.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-black text-sm font-medium rounded-lg transition-all duration-300 shadow-lg hover:shadow-amber-500/25">
                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Create New Character
                </a>
            </div>
        </div>
    </x-sub-navigation>
    <div class="container mx-auto p-6 min-h-screen">
        <!-- Character Grid Component -->
        <livewire:character-grid />
    </div>
</x-layout>