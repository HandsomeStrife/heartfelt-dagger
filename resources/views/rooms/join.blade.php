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
                                    <p class="text-white">{{ $room->getActiveParticipantCount() }}/{{ $room->guest_count }} participants</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Join Form -->
                    <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-8">
                        <h2 class="font-outfit text-2xl font-bold text-white mb-6">Join Room</h2>
                        
                        <form action="{{ route('rooms.join', $room) }}" method="POST" class="space-y-6">
                            @csrf

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

                            <!-- Character Selection -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-200 mb-3">
                                    Character Selection
                                </label>
                                
                                <div class="space-y-3">
                                    <!-- Existing Characters -->
                                    @if($characters->isNotEmpty())
                                        <div>
                                            <h4 class="text-slate-300 font-medium mb-2">Use Existing Character</h4>
                                            @foreach($characters as $character)
                                                <label class="flex items-center p-3 bg-slate-800/30 border border-slate-600/50 rounded-lg hover:bg-slate-800/50 transition-colors cursor-pointer">
                                                    <input 
                                                        type="radio" 
                                                        name="character_id" 
                                                        value="{{ $character->id }}" 
                                                        class="w-4 h-4 text-emerald-500 bg-slate-700 border-slate-600 focus:ring-emerald-500 focus:ring-2"
                                                        onchange="clearTemporaryFields()"
                                                    >
                                                    <div class="ml-3 flex-1">
                                                        <div class="flex items-center justify-between">
                                                            <h5 class="text-white font-semibold">{{ $character->name }}</h5>
                                                            <span class="text-emerald-400 text-sm">{{ $character->class }}</span>
                                                        </div>
                                                        <p class="text-slate-400 text-sm">
                                                            {{ $character->ancestry }} {{ $character->community }}
                                                            @if($character->subclass)
                                                                • {{ $character->subclass }}
                                                            @endif
                                                        </p>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>

                                        <div class="text-center">
                                            <span class="text-slate-500 text-sm">or</span>
                                        </div>
                                    @endif

                                    <!-- Temporary Character -->
                                    <div>
                                        <h4 class="text-slate-300 font-medium mb-2">Create Temporary Character</h4>
                                        <label class="flex items-center p-3 bg-slate-800/30 border border-slate-600/50 rounded-lg hover:bg-slate-800/50 transition-colors cursor-pointer mb-3">
                                            <input 
                                                type="radio" 
                                                name="character_id" 
                                                value="" 
                                                class="w-4 h-4 text-emerald-500 bg-slate-700 border-slate-600 focus:ring-emerald-500 focus:ring-2"
                                                onchange="clearExistingCharacters()"
                                                {{ $characters->isEmpty() ? 'checked' : '' }}
                                            >
                                            <span class="ml-3 text-white">Use temporary character</span>
                                        </label>

                                        <div id="temporary-character-fields" class="space-y-3 {{ $characters->isNotEmpty() ? 'opacity-50' : '' }}">
                                            <div>
                                                <input 
                                                    type="text" 
                                                    id="character_name" 
                                                    name="character_name" 
                                                    value="{{ old('character_name') }}"
                                                    class="w-full px-4 py-3 bg-slate-800/50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all" 
                                                    placeholder="Character name"
                                                    {{ $characters->isEmpty() ? 'required' : '' }}
                                                >
                                            </div>
                                            <div>
                                                <select 
                                                    id="character_class" 
                                                    name="character_class" 
                                                    class="w-full px-4 py-3 bg-slate-800/50 border border-slate-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                                                    {{ $characters->isEmpty() ? 'required' : '' }}
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
                                    </div>
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
                            <div class="flex items-center justify-between pt-6 border-t border-slate-700">
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
    function clearTemporaryFields() {
        const temporaryFields = document.getElementById('temporary-character-fields');
        const nameField = document.getElementById('character_name');
        const classField = document.getElementById('character_class');
        
        temporaryFields.classList.add('opacity-50');
        nameField.value = '';
        classField.value = '';
        nameField.required = false;
        classField.required = false;
    }

    function clearExistingCharacters() {
        const existingRadios = document.querySelectorAll('input[name="character_id"][value!=""]');
        const temporaryFields = document.getElementById('temporary-character-fields');
        const nameField = document.getElementById('character_name');
        const classField = document.getElementById('character_class');
        
        existingRadios.forEach(radio => radio.checked = false);
        temporaryFields.classList.remove('opacity-50');
        nameField.required = true;
        classField.required = true;
    }
    </script>
</x-layout>
