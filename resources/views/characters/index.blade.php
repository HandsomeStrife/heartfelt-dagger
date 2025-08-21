<x-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 p-6" 
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

        <!-- Loading State -->
        <div x-show="loading" class="text-center">
            <div class="inline-flex items-center text-slate-300">
                <svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Loading characters...
            </div>
        </div>

        <!-- No Characters Message -->
        <div x-show="!loading && characters.length === 0" class="text-center">
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

        <!-- Characters Grid -->
        <div x-show="!loading && characters.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            <template x-for="character in characters" :key="character.character_key">
                <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl overflow-hidden hover:border-slate-600/70 transition-all duration-300 group cursor-pointer"
                     @click="viewCharacter(character.character_key)">
                    
                    <!-- Character Portrait -->
                    <div class="relative h-32 bg-gradient-to-br from-slate-700 to-slate-800">
                        <div class="absolute inset-0 flex items-center justify-center" x-show="!character.profile_image">
                            <svg class="w-12 h-12 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <img x-show="character.profile_image" 
                             :src="character.profile_image" 
                             :alt="character.name + ' portrait'"
                             class="w-full h-full object-cover">
                        
                        <!-- Class Badge -->
                        <div class="absolute top-2 right-2" x-show="character.selected_class">
                            <span class="bg-gradient-to-r from-amber-500 to-orange-500 text-black px-1.5 py-0.5 rounded text-xs font-bold" 
                                  x-text="character.selected_class ? character.selected_class.charAt(0).toUpperCase() + character.selected_class.slice(1) : ''"></span>
                        </div>
                    </div>

                    <!-- Character Info -->
                    <div class="p-3">
                        <h3 class="text-white font-bold font-outfit text-base mb-1 truncate" 
                            x-text="character.name || 'Unnamed Character'"></h3>
                        
                        <div class="text-slate-300 text-xs space-y-0.5">
                            <div x-show="character.selected_class">
                                <span class="text-slate-400">Class:</span> 
                                <span x-text="character.selected_class ? character.selected_class.charAt(0).toUpperCase() + character.selected_class.slice(1) : 'None'"></span>
                            </div>
                            <div x-show="character.selected_ancestry">
                                <span class="text-slate-400">Ancestry:</span> 
                                <span x-text="character.selected_ancestry ? character.selected_ancestry.charAt(0).toUpperCase() + character.selected_ancestry.slice(1) : 'None'"></span>
                            </div>
                        </div>

                        <!-- Character Progress -->
                        <div class="mt-2 pt-2 border-t border-slate-700">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400">Progress</span>
                                <span class="text-slate-300" x-text="getCompletionPercentage(character) + '%'"></span>
                            </div>
                            <div class="w-full bg-slate-700 rounded-full h-1 mt-1">
                                <div class="bg-gradient-to-r from-amber-500 to-orange-500 h-1 rounded-full transition-all duration-300" 
                                     :style="`width: ${getCompletionPercentage(character)}%`"></div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-1.5 mt-3">
                            <button @click.stop="editCharacter(character.character_key)"
                                    class="flex-1 bg-slate-700 hover:bg-slate-600 text-white px-2 py-1.5 rounded text-xs font-medium transition-all duration-200">
                                Edit
                            </button>
                            <button @click.stop="deleteCharacter(character.character_key)"
                                    class="bg-red-600/20 hover:bg-red-600/30 text-red-300 px-2 py-1.5 rounded text-xs font-medium transition-all duration-200">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <script>
        function charactersManager() {
            return {
                characters: [],
                loading: true,

                async loadCharacters() {
                    this.loading = true;
                    
                    // Get character keys from localStorage
                    const character_keys = this.getStoredCharacterKeys();
                    
                    if (character_keys.length === 0) {
                        this.loading = false;
                        return;
                    }

                    // Fetch character data for each key
                    const characters = [];
                    for (const key of character_keys) {
                        try {
                            const response = await fetch(`/api/character/${key}`);
                            if (response.ok) {
                                const character = await response.json();
                                characters.push(character);
                            } else {
                                // Character not found, remove from localStorage
                                this.removeCharacterKey(key);
                            }
                        } catch (error) {
                            console.error(`Failed to load character ${key}:`, error);
                            // Optionally remove the key if it's consistently failing
                        }
                    }

                    this.characters = characters;
                    this.loading = false;
                },

                getStoredCharacterKeys() {
                    try {
                        const keys = localStorage.getItem('daggerheart_characters');
                        return keys ? JSON.parse(keys) : [];
                    } catch (error) {
                        console.error('Error reading character keys from localStorage:', error);
                        return [];
                    }
                },

                removeCharacterKey(character_key) {
                    try {
                        const keys = this.getStoredCharacterKeys();
                        const updatedKeys = keys.filter(key => key !== character_key);
                        localStorage.setItem('daggerheart_characters', JSON.stringify(updatedKeys));
                    } catch (error) {
                        console.error('Error updating localStorage:', error);
                    }
                },

                getCompletionPercentage(character) {
                    let completed = 0;
                    const total = 8; // Total number of steps

                    // Check each step completion
                    if (character.selected_class) completed++;
                    if (character.selected_ancestry && character.selected_community) completed++;
                    if (character.assigned_traits && Object.keys(character.assigned_traits).length >= 6) completed++;
                    if (character.selected_equipment && character.selected_equipment.length > 0) completed++;
                    if (character.background && character.background.length > 0) completed++;
                    if (character.experiences && character.experiences.length >= 2) completed++;
                    if (character.selected_domain_cards && character.selected_domain_cards.length > 0) completed++;
                    if (character.connections && character.connections.length > 0) completed++;

                    return Math.round((completed / total) * 100);
                },

                viewCharacter(character_key) {
                    window.location.href = `/character/${character_key}`;
                },

                editCharacter(character_key) {
                    window.location.href = `/character-builder/${character_key}`;
                },

                async deleteCharacter(character_key) {
                    if (confirm('Are you sure you want to delete this character? This action cannot be undone.')) {
                        try {
                            // Delete from database
                            const response = await fetch(`/api/character/${character_key}`, { 
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });

                            if (response.ok) {
                                // Remove from localStorage
                                this.removeCharacterKey(character_key);
                                
                                // Remove from current list
                                this.characters = this.characters.filter(char => char.character_key !== character_key);
                                
                                console.log('Character deleted successfully');
                            } else {
                                console.error('Failed to delete character from database');
                                alert('Failed to delete character. Please try again.');
                            }
                        } catch (error) {
                            console.error('Error deleting character:', error);
                            alert('An error occurred while deleting the character. Please try again.');
                        }
                    }
                }
            }
        }
    </script>
</x-layout>
