<x-layout>
    <div class="min-h-screen container mx-auto p-6" 
         x-data="charactersManager()" 
         x-init="loadCharacters()">
        
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="font-outfit text-4xl text-white tracking-wide mb-2">
                Your Characters
            </h1>
            <p class="font-roboto text-slate-300 text-lg">
                Manage your created Daggerheart characters
            </p>
        </div>

        <!-- Create New Character Button -->
        <div class="text-center mb-8">
            <a href="{{ route('character-builder') }}" 
               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-black font-semibold rounded-xl transition-all duration-300 shadow-lg hover:shadow-amber-500/25">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Create New Character
            </a>
        </div>

        <!-- Character Grid Component -->
        <livewire:character-grid />
    </div>

    <script>
        function charactersManager() {
            return {
                async loadCharacters() {
                    // Get character keys from localStorage
                    const character_keys = this.getStoredCharacterKeys();
                    
                    // Pass character keys to Livewire component
                    Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'))
                        .call('loadCharacters', character_keys);
                },

                getStoredCharacterKeys() {
                    try {
                        const keys = localStorage.getItem('daggerheart_characters');
                        return keys ? JSON.parse(keys) : [];
                    } catch (error) {
                        console.error('Error reading character keys from localStorage:', error);
                        return [];
                    }
                }
            }
        }
    </script>
</x-layout>