<x-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950">
        <div class="px-4 sm:px-6 lg:px-8 pt-12 pb-16">
            <div class="max-w-4xl mx-auto">
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="font-outfit text-3xl text-white tracking-wide mb-2">
                        Join Room
                    </h1>
                    <p class="text-slate-300 text-lg">
                        You've been invited to join a video chat room
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Room Info -->
                    <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-8">
                        <div class="flex items-center mb-6">
                            <div class="w-12 h-12 bg-gradient-to-br from-emerald-500/20 to-teal-500/20 rounded-xl flex items-center justify-center border border-emerald-500/30 mr-4">
                                <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="font-outfit text-2xl font-bold text-white">{{ $room->name }}</h2>
                                <p class="text-slate-400">Room Details</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <h3 class="text-white font-semibold mb-2">Description</h3>
                                <p class="text-slate-300">{{ $room->description }}</p>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <h4 class="text-slate-400 text-sm font-semibold mb-1">Creator</h4>
                                    <p class="text-white">{{ $room->creator->username ?? 'Unknown' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-slate-400 text-sm font-semibold mb-1">Capacity</h4>
                                    <p class="text-white">{{ $room->getActiveParticipantCount() }}/{{ $room->getTotalCapacity() }} participants</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Join Form -->
                    <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-8">
                        <h2 class="font-outfit text-2xl font-bold text-white mb-6">Join Room</h2>
                        
                                        <form action="{{ route('rooms.join', $room) }}" method="POST" class="space-y-6">
                    @csrf

                    @if ($errors->any())
                        <div class="mb-6 bg-red-500/10 border border-red-500/30 rounded-xl p-4">
                            <div class="flex items-start space-x-3">
                                <svg class="w-5 h-5 text-red-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                                <div>
                                    <h3 class="text-red-300 font-semibold">Please fix the following errors:</h3>
                                    <ul class="text-red-200/90 text-sm mt-1 space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>• {{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                            @if($room->password)
                                <!-- Room Password -->
                                <div>
                                    <label for="password" class="block text-sm font-semibold text-slate-200 mb-2">
                                        Room Password <span class="text-red-400">*</span>
                                    </label>
                                    <input 
                                        type="password" 
                                        id="password" 
                                        name="password" 
                                        class="w-full px-4 py-3 bg-slate-800/50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all" 
                                        placeholder="Enter room password"
                                        required
                                    >
                                    @error('password')
                                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            @else
                                <!-- No Password Required -->
                                <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl p-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-emerald-300 font-semibold">No Password Required</h3>
                                            <p class="text-emerald-200/90 text-sm">
                                                @if($room->campaign_id)
                                                    This is a campaign room - access is restricted to campaign members.
                                                @else
                                                    This room is open to all participants.
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($characters->isNotEmpty())
                                <!-- Character Selection -->
                                <div>
                                    <label class="block text-sm font-semibold text-slate-200 mb-3">
                                        Character Selection
                                    </label>
                                    
                                    <div class="space-y-3">
                                        <!-- Existing Characters -->
                                        <div>
                                            <h4 class="text-slate-300 font-medium mb-2">Use Existing Character</h4>
                                            <select 
                                                id="character_id" 
                                                name="character_id" 
                                                class="w-full px-4 py-3 bg-slate-800/50 border border-slate-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                                                onchange="handleCharacterSelection()"
                                            >
                                                <option value="">Select an existing character</option>
                                                <option value="temporary">Create temporary character</option>
                                                @foreach($characters as $character)
                                                    <option value="{{ $character->id }}" {{ old('character_id') == $character->id ? 'selected' : '' }}>
                                                        {{ $character->name }} ({{ $character->class }})@if($character->subclass) - {{ $character->subclass }}@endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <!-- No existing characters - direct temporary character creation -->
                                <div>
                                    <label class="block text-sm font-semibold text-slate-200 mb-3">
                                        Create Temporary Character
                                    </label>
                                    
                                    <!-- Hidden radio button for temporary character (always selected when no characters exist) -->
                                    <input type="radio" name="character_id" value="" checked style="display: none;">
                            @endif

                            <div id="temporary-character-fields" class="space-y-3 {{ $characters->isNotEmpty() ? 'opacity-50' : '' }}">
                                <div>
                                    <input 
                                        type="text" 
                                        id="character_name" 
                                        name="character_name" 
                                        value="{{ old('character_name') }}"
                                        class="w-full px-4 py-3 bg-slate-800/50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all" 
                                        placeholder="Character name (required for temporary character)"
                                        {{ $characters->isNotEmpty() ? 'disabled' : 'required' }}
                                    >
                                </div>
                                <div>
                                    <select 
                                        id="character_class" 
                                        name="character_class" 
                                        class="w-full px-4 py-3 bg-slate-800/50 border border-slate-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                                        {{ $characters->isNotEmpty() ? 'disabled' : 'required' }}
                                    >
                                        <option value="">Select class</option>
                                        <option value="Bard" {{ old('character_class') == 'Bard' ? 'selected' : '' }}>Bard</option>
                                        <option value="Druid" {{ old('character_class') == 'Druid' ? 'selected' : '' }}>Druid</option>
                                        <option value="Guardian" {{ old('character_class') == 'Guardian' ? 'selected' : '' }}>Guardian</option>
                                        <option value="Ranger" {{ old('character_class') == 'Ranger' ? 'selected' : '' }}>Ranger</option>
                                        <option value="Rogue" {{ old('character_class') == 'Rogue' ? 'selected' : '' }}>Rogue</option>
                                        <option value="Seraph" {{ old('character_class') == 'Seraph' ? 'selected' : '' }}>Seraph</option>
                                        <option value="Sorcerer" {{ old('character_class') == 'Sorcerer' ? 'selected' : '' }}>Sorcerer</option>
                                        <option value="Warrior" {{ old('character_class') == 'Warrior' ? 'selected' : '' }}>Warrior</option>
                                        <option value="Wizard" {{ old('character_class') == 'Wizard' ? 'selected' : '' }}>Wizard</option>
                                    </select>
                                </div>
                            </div>
                            @if($characters->isNotEmpty())
                                    </div>
                                </div>
                            @endif
                            </div>

                                @error('character_id')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                                @error('character_name')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                                @error('character_class')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-700">
                                <a href="{{ route('rooms.index') }}" class="px-6 py-3 text-slate-300 hover:text-white transition-colors">
                                    Cancel
                                </a>
                                <button type="submit" class="bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-white font-semibold py-3 px-8 rounded-xl transition-all duration-300 shadow-lg hover:shadow-emerald-500/25">
                                    Join Room
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function handleCharacterSelection() {
        const characterSelect = document.getElementById('character_id');
        const temporaryFields = document.getElementById('temporary-character-fields');
        const nameField = document.getElementById('character_name');
        const classField = document.getElementById('character_class');
        
        if (!characterSelect || !temporaryFields || !nameField || !classField) {
            return; // Elements don't exist (user has no characters)
        }

        if (characterSelect.value === 'temporary') {
            // Enable temporary character fields
            temporaryFields.classList.remove('opacity-50');
            nameField.required = true;
            classField.required = true;
            nameField.disabled = false;
            classField.disabled = false;
            
            // Set the value to empty string for form submission
            characterSelect.value = '';
        } else if (characterSelect.value === '') {
            // Default state - disable temporary fields
            temporaryFields.classList.add('opacity-50');
            nameField.value = '';
            classField.value = '';
            nameField.required = false;
            classField.required = false;
            nameField.disabled = true;
            classField.disabled = true;
        } else {
            // Existing character selected - disable temporary character fields
            temporaryFields.classList.add('opacity-50');
            nameField.value = '';
            classField.value = '';
            nameField.required = false;
            classField.required = false;
            nameField.disabled = true;
            classField.disabled = true;
        }
    }

    // Initialize form state on page load
    document.addEventListener('DOMContentLoaded', function() {
        const characterSelect = document.getElementById('character_id');
        const temporaryFields = document.getElementById('temporary-character-fields');
        const nameField = document.getElementById('character_name');
        const classField = document.getElementById('character_class');
        
        // If character dropdown exists (user has characters), initialize to disabled state
        if (characterSelect && temporaryFields && nameField && classField) {
            // Set initial state - temporary fields disabled when user has character dropdown
            temporaryFields.classList.add('opacity-50');
            nameField.disabled = true;
            classField.disabled = true;
            nameField.required = false;
            classField.required = false;
        }
        // If no character dropdown exists (anonymous user or user with no characters), 
        // fields should already be enabled via server-side rendering
    });
    </script>
</x-layout>
