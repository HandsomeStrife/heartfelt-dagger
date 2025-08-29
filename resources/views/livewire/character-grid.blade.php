<div x-data="{ 
        loadCharactersFromStorage() {
            try {
                const keys = localStorage.getItem('daggerheart_characters');
                const characterKeys = keys ? JSON.parse(keys) : [];
                $wire.loadCharacters(characterKeys);
            } catch (error) {
                console.error('Error reading character keys from localStorage:', error);
                $wire.loadCharacters([]);
            }
        }
     }" 
     @load-characters-from-storage.window="loadCharactersFromStorage()"
     x-init="loadCharactersFromStorage()">
    <!-- Loading State -->
    <div wire:loading class="text-center">
        <div class="inline-flex items-center text-slate-300">
            <svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Loading characters...
        </div>
    </div>

    <!-- No Characters Message -->
    @if(!$loading && $characters->isEmpty())
        <div class="text-center">
            <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl p-12">
                <div class="mb-4">
                    <svg class="w-16 h-16 text-slate-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white font-outfit mb-2">No Characters Yet</h3>
                <p class="text-slate-300 mb-4">
                    You haven't created any characters yet. Start your adventure by creating your first character!
                </p>
                <a href="{{ route('character-builder') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-black font-semibold rounded-lg transition-all duration-300">
                    Create Your First Character
                </a>
            </div>
        </div>
    @endif

    <!-- Characters Grid -->
    @if(!$loading && $characters->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4" 
             x-data="{ 
                 deleteCharacter: function(character_key) {
                     if (confirm('Are you sure you want to delete this character? This action cannot be undone.')) {
                         // Get CSRF token from meta tag
                         const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
                         
                         fetch('/api/character/' + character_key, { 
                             method: 'DELETE',
                             headers: {
                                 'Content-Type': 'application/json',
                                 'X-Requested-With': 'XMLHttpRequest',
                                 'X-CSRF-TOKEN': token
                             }
                         }).then(response => {
                             if (response.ok) {
                                 // Remove from localStorage
                                 let keys = JSON.parse(localStorage.getItem('daggerheart_characters') || '[]');
                                 keys = keys.filter(key => key !== character_key);
                                 localStorage.setItem('daggerheart_characters', JSON.stringify(keys));
                                 
                                 // Reload the character grid
                                 $wire.loadCharacters(keys);
                             } else {
                                 response.json().then(data => {
                                     console.error('Delete failed:', data);
                                     alert(data.error || 'Failed to delete character. Please try again.');
                                 }).catch(() => {
                                     alert('Failed to delete character. Please try again.');
                                 });
                             }
                         }).catch(error => {
                             console.error('Error deleting character:', error);
                             alert('An error occurred while deleting the character. Please try again.');
                         });
                     }
                 }
             }">
            @foreach($characters as $character)
                <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl overflow-hidden hover:border-slate-600/70 transition-all duration-300 group">
                    
                    <!-- Character Portrait -->
                    <div class="relative h-32 bg-gradient-to-br from-slate-700 to-slate-800">
                        @if(empty($character->profile_image_path))
                            <div class="absolute inset-0 flex items-center justify-center">
                                <svg class="w-12 h-12 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        @else
                            <img src="{{ $character->getProfileImage() }}" 
                                 alt="{{ $character->name ?? 'Unnamed Character' }} portrait"
                                 class="w-full h-full object-cover">
                        @endif
                    </div>

                    <!-- Character Class Banner -->
                    @if(!empty($character->selected_class))
                        <x-class-banner :class-name="$character->selected_class" size="sm" class="absolute top-0 left-0" />
                    @endif

                    <!-- Character Info -->
                    <div class="p-3">
                        <h3 class="text-white font-bold font-outfit text-base mb-2 truncate">
                            {{ $character->name ?: 'Unnamed Character' }}
                        </h3>
                        
                        <!-- Standardized Character Details -->
                        <div class="text-slate-300 text-xs mb-3 space-y-1">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Class:</span> 
                                <span>{{ !empty($character->selected_class) ? ucfirst($character->selected_class) : 'None' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Subclass:</span> 
                                <span>{{ !empty($character->selected_subclass) ? ucfirst($character->selected_subclass) : 'None' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Ancestry:</span> 
                                <span>{{ !empty($character->selected_ancestry) ? ucfirst($character->selected_ancestry) : 'None' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Community:</span> 
                                <span>{{ !empty($character->selected_community) ? ucfirst($character->selected_community) : 'None' }}</span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-1 mt-3">
                            <button wire:click="viewCharacter('{{ $character->character_key }}')"
                                    class="flex-1 bg-slate-600 hover:bg-slate-500 text-white px-2 py-1.5 rounded text-xs font-medium transition-all duration-200">
                                View
                            </button>
                            <button wire:click="editCharacter('{{ $character->character_key }}')"
                                    class="flex-1 bg-slate-700 hover:bg-slate-600 text-white px-2 py-1.5 rounded text-xs font-medium transition-all duration-200">
                                Edit
                            </button>
                            <button @click="deleteCharacter('{{ $character->character_key }}')"
                                    class="flex-1 bg-red-600/20 hover:bg-red-600/30 text-red-300 px-2 py-1.5 rounded text-xs font-medium transition-all duration-200">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>