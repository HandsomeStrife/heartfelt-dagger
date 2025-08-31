<div>
    <form wire:submit.prevent="save">
        <div class="space-y-8">
            <!-- Basic Information -->
            <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                <h2 class="font-outfit text-xl text-white mb-6 flex items-center">
                    <svg class="w-5 h-5 mr-3 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Basic Information
                </h2>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-300 mb-2">Frame Name</label>
                        <input 
                            type="text" 
                            id="name"
                            wire:model="{{ $mode === 'create' ? 'create_form.name' : 'edit_form.name' }}"
                            class="w-full bg-slate-800/50 border border-slate-700/50 rounded-xl px-4 py-3 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50"
                            placeholder="Enter frame name"
                            maxlength="100"
                        >
                        @error($mode === 'create' ? 'create_form.name' : 'edit_form.name') 
                            <span class="text-red-400 text-sm mt-1">{{ $message }}</span> 
                        @enderror
                    </div>

                    <div>
                        <label for="complexity_rating" class="block text-sm font-medium text-slate-300 mb-2">Complexity Rating</label>
                        <select 
                            id="complexity_rating"
                            wire:model="{{ $mode === 'create' ? 'create_form.complexity_rating' : 'edit_form.complexity_rating' }}"
                            class="w-full bg-slate-800/50 border border-slate-700/50 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50"
                        >
                            @foreach($this->getComplexityOptions() as $option)
                                <option value="{{ $option['value'] }}">{{ $option['label'] }} - {{ $option['description'] }}</option>
                            @endforeach
                        </select>
                        @error($mode === 'create' ? 'create_form.complexity_rating' : 'edit_form.complexity_rating') 
                            <span class="text-red-400 text-sm mt-1">{{ $message }}</span> 
                        @enderror
                    </div>
                </div>

                <div class="mt-6">
                    <label for="description" class="block text-sm font-medium text-slate-300 mb-2">Description</label>
                    <textarea 
                        id="description"
                        wire:model="{{ $mode === 'create' ? 'create_form.description' : 'edit_form.description' }}"
                        rows="3"
                        class="w-full bg-slate-800/50 border border-slate-700/50 rounded-xl px-4 py-3 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50"
                        placeholder="Brief description of your campaign frame"
                        maxlength="500"
                    ></textarea>
                    @error($mode === 'create' ? 'create_form.description' : 'edit_form.description') 
                        <span class="text-red-400 text-sm mt-1">{{ $message }}</span> 
                    @enderror
                </div>

                <div class="mt-6 flex items-center">
                    <input 
                        type="checkbox" 
                        id="is_public"
                        wire:model="{{ $mode === 'create' ? 'create_form.is_public' : 'edit_form.is_public' }}"
                        class="w-4 h-4 text-emerald-600 bg-slate-800 border-slate-700 rounded focus:ring-emerald-500 focus:ring-2"
                    >
                    <label for="is_public" class="ml-2 text-sm text-slate-300">
                        Make this frame publicly visible for others to use
                    </label>
                </div>
            </div>

            <!-- The Pitch -->
            <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                <h2 class="font-outfit text-xl text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-3 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2h3a1 1 0 011 1v1a1 1 0 01-1 1h-1v12a2 2 0 01-2 2H6a2 2 0 01-2-2V7H3a1 1 0 01-1-1V5a1 1 0 011-1h3z" />
                    </svg>
                    The Pitch
                </h2>
                <p class="text-slate-400 text-sm mb-4">Create engaging pitch points to present to your players</p>
                
                @if($mode === 'create')
                    @forelse($create_form->pitch as $index => $pitch_item)
                        <div class="flex items-center space-x-2 mb-3">
                            <textarea 
                                wire:model="create_form.pitch.{{ $index }}"
                                rows="2"
                                class="flex-1 bg-slate-800/50 border border-slate-700/50 rounded-lg px-3 py-2 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500/50"
                                placeholder="Enter a compelling pitch point..."
                            ></textarea>
                            <button 
                                type="button"
                                wire:click="create_form.removePitchItem({{ $index }})"
                                class="px-3 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    @empty
                        <p class="text-slate-500 text-sm italic">No pitch points added yet</p>
                    @endforelse
                @else
                    @forelse($edit_form->pitch as $index => $pitch_item)
                        <div class="flex items-center space-x-2 mb-3">
                            <textarea 
                                wire:model="edit_form.pitch.{{ $index }}"
                                rows="2"
                                class="flex-1 bg-slate-800/50 border border-slate-700/50 rounded-lg px-3 py-2 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500/50"
                                placeholder="Enter a compelling pitch point..."
                            ></textarea>
                            <button 
                                type="button"
                                wire:click="edit_form.removePitchItem({{ $index }})"
                                class="px-3 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    @empty
                        <p class="text-slate-500 text-sm italic">No pitch points added yet</p>
                    @endforelse
                @endif
                
                <button 
                    type="button"
                    wire:click="{{ $mode === 'create' ? 'create_form.addPitchItem' : 'edit_form.addPitchItem' }}"
                    class="mt-3 px-4 py-2 bg-purple-600 hover:bg-purple-500 text-white rounded-lg transition-colors flex items-center space-x-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Add Pitch Point</span>
                </button>
            </div>

            <!-- Touchstones -->
            <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                <h2 class="font-outfit text-xl text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-3 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2h3a1 1 0 011 1v1a1 1 0 01-1 1h-1v12a2 2 0 01-2 2H6a2 2 0 01-2-2V7H3a1 1 0 01-1-1V5a1 1 0 011-1h3z" />
                    </svg>
                    Touchstones
                </h2>
                <p class="text-slate-400 text-sm mb-4">Media references that inspire the campaign (movies, games, books, etc.)</p>
                
                @if($mode === 'create')
                    @forelse($create_form->touchstones as $index => $touchstone)
                        <div class="flex items-center space-x-2 mb-3">
                            <input 
                                wire:model="create_form.touchstones.{{ $index }}"
                                type="text"
                                class="flex-1 bg-slate-800/50 border border-slate-700/50 rounded-lg px-3 py-2 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
                                placeholder="e.g., Princess Mononoke, The Legend of Zelda, The Dark Crystal..."
                            >
                            <button 
                                type="button"
                                wire:click="create_form.removeTouchstoneItem({{ $index }})"
                                class="px-3 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    @empty
                        <p class="text-slate-500 text-sm italic">No touchstones added yet</p>
                    @endforelse
                @else
                    @forelse($edit_form->touchstones as $index => $touchstone)
                        <div class="flex items-center space-x-2 mb-3">
                            <input 
                                wire:model="edit_form.touchstones.{{ $index }}"
                                type="text"
                                class="flex-1 bg-slate-800/50 border border-slate-700/50 rounded-lg px-3 py-2 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
                                placeholder="e.g., Princess Mononoke, The Legend of Zelda, The Dark Crystal..."
                            >
                            <button 
                                type="button"
                                wire:click="edit_form.removeTouchstoneItem({{ $index }})"
                                class="px-3 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    @empty
                        <p class="text-slate-500 text-sm italic">No touchstones added yet</p>
                    @endforelse
                @endif
                
                <button 
                    type="button"
                    wire:click="{{ $mode === 'create' ? 'create_form.addTouchstoneItem' : 'edit_form.addTouchstoneItem' }}"
                    class="mt-3 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg transition-colors flex items-center space-x-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Add Touchstone</span>
                </button>
            </div>

            <!-- Tone & Themes -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Tone & Feel -->
                <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                    <h2 class="font-outfit text-xl text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                        Tone & Feel
                    </h2>
                    <p class="text-slate-400 text-sm mb-4">Mood descriptors (Adventurous, Epic, Heroic, etc.)</p>
                    
                    @if($mode === 'create')
                        @forelse($create_form->tone as $index => $tone_item)
                            <div class="flex items-center space-x-2 mb-3">
                                <input 
                                    wire:model="create_form.tone.{{ $index }}"
                                    type="text"
                                    class="flex-1 bg-slate-800/50 border border-slate-700/50 rounded-lg px-3 py-2 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                                    placeholder="e.g., Adventurous, Epic, Heroic..."
                                >
                                <button 
                                    type="button"
                                    wire:click="create_form.removeToneItem({{ $index }})"
                                    class="px-3 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        @empty
                            <p class="text-slate-500 text-sm italic">No tone descriptors added yet</p>
                        @endforelse
                    @else
                        @forelse($edit_form->tone as $index => $tone_item)
                            <div class="flex items-center space-x-2 mb-3">
                                <input 
                                    wire:model="edit_form.tone.{{ $index }}"
                                    type="text"
                                    class="flex-1 bg-slate-800/50 border border-slate-700/50 rounded-lg px-3 py-2 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                                    placeholder="e.g., Adventurous, Epic, Heroic..."
                                >
                                <button 
                                    type="button"
                                    wire:click="edit_form.removeToneItem({{ $index }})"
                                    class="px-3 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        @empty
                            <p class="text-slate-500 text-sm italic">No tone descriptors added yet</p>
                        @endforelse
                    @endif
                    
                    <button 
                        type="button"
                        wire:click="{{ $mode === 'create' ? 'create_form.addToneItem' : 'edit_form.addToneItem' }}"
                        class="mt-3 px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg transition-colors flex items-center space-x-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Add Tone</span>
                    </button>
                </div>

                <!-- Themes -->
                <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                    <h2 class="font-outfit text-xl text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                        Themes
                    </h2>
                    <p class="text-slate-400 text-sm mb-4">Core story themes (Cultural Clash, Grief, etc.)</p>
                    
                    @if($mode === 'create')
                        @forelse($create_form->themes as $index => $theme_item)
                            <div class="flex items-center space-x-2 mb-3">
                                <input 
                                    wire:model="create_form.themes.{{ $index }}"
                                    type="text"
                                    class="flex-1 bg-slate-800/50 border border-slate-700/50 rounded-lg px-3 py-2 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-green-500/50"
                                    placeholder="e.g., Cultural Clash, People vs. Nature..."
                                >
                                <button 
                                    type="button"
                                    wire:click="create_form.removeThemeItem({{ $index }})"
                                    class="px-3 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        @empty
                            <p class="text-slate-500 text-sm italic">No themes added yet</p>
                        @endforelse
                    @else
                        @forelse($edit_form->themes as $index => $theme_item)
                            <div class="flex items-center space-x-2 mb-3">
                                <input 
                                    wire:model="edit_form.themes.{{ $index }}"
                                    type="text"
                                    class="flex-1 bg-slate-800/50 border border-slate-700/50 rounded-lg px-3 py-2 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-green-500/50"
                                    placeholder="e.g., Cultural Clash, People vs. Nature..."
                                >
                                <button 
                                    type="button"
                                    wire:click="edit_form.removeThemeItem({{ $index }})"
                                    class="px-3 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        @empty
                            <p class="text-slate-500 text-sm italic">No themes added yet</p>
                        @endforelse
                    @endif
                    
                    <button 
                        type="button"
                        wire:click="{{ $mode === 'create' ? 'create_form.addThemeItem' : 'edit_form.addThemeItem' }}"
                        class="mt-3 px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg transition-colors flex items-center space-x-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Add Theme</span>
                    </button>
                </div>
            </div>

            <!-- Background Overview -->
            <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                <h2 class="font-outfit text-xl text-white mb-6 flex items-center">
                    <svg class="w-5 h-5 mr-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    Background Overview
                </h2>
                <p class="text-slate-400 text-sm mb-4">Provide an overview of the campaign's background and setting</p>
                
                <textarea 
                    wire:model="{{ $mode === 'create' ? 'create_form.background_overview' : 'edit_form.background_overview' }}"
                    rows="6"
                    class="w-full bg-slate-800/50 border border-slate-700/50 rounded-xl px-4 py-3 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500/50"
                    placeholder="Provide an overview of the campaign's background and setting. This should give players the context they need to understand the world and their place in it..."
                    maxlength="2000"
                ></textarea>
                @error($mode === 'create' ? 'create_form.background_overview' : 'edit_form.background_overview') 
                    <span class="text-red-400 text-sm mt-1">{{ $message }}</span> 
                @enderror
            </div>

            <!-- Principles -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Player Principles -->
                <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                    <h2 class="font-outfit text-xl text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-3 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                        Player Principles
                    </h2>
                    <p class="text-slate-400 text-sm mb-4">Guidelines and principles specifically for players</p>
                    
                    @if($mode === 'create')
                        @forelse($create_form->player_principles as $index => $principle)
                            <div class="flex items-start space-x-2 mb-3">
                                <textarea 
                                    wire:model="create_form.player_principles.{{ $index }}"
                                    rows="2"
                                    class="flex-1 bg-slate-800/50 border border-slate-700/50 rounded-lg px-3 py-2 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/50"
                                    placeholder="e.g., Make the invasion personal..."
                                ></textarea>
                                <button 
                                    type="button"
                                    wire:click="create_form.removePlayerPrincipleItem({{ $index }})"
                                    class="px-3 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        @empty
                            <p class="text-slate-500 text-sm italic">No player principles added yet</p>
                        @endforelse
                    @else
                        @forelse($edit_form->player_principles as $index => $principle)
                            <div class="flex items-start space-x-2 mb-3">
                                <textarea 
                                    wire:model="edit_form.player_principles.{{ $index }}"
                                    rows="2"
                                    class="flex-1 bg-slate-800/50 border border-slate-700/50 rounded-lg px-3 py-2 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/50"
                                    placeholder="e.g., Make the invasion personal..."
                                ></textarea>
                                <button 
                                    type="button"
                                    wire:click="edit_form.removePlayerPrincipleItem({{ $index }})"
                                    class="px-3 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        @empty
                            <p class="text-slate-500 text-sm italic">No player principles added yet</p>
                        @endforelse
                    @endif
                    
                    <button 
                        type="button"
                        wire:click="{{ $mode === 'create' ? 'create_form.addPlayerPrincipleItem' : 'edit_form.addPlayerPrincipleItem' }}"
                        class="mt-3 px-4 py-2 bg-cyan-600 hover:bg-cyan-500 text-white rounded-lg transition-colors flex items-center space-x-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Add Player Principle</span>
                    </button>
                </div>

                <!-- GM Principles -->
                <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                    <h2 class="font-outfit text-xl text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-3 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        GM Principles
                    </h2>
                    <p class="text-slate-400 text-sm mb-4">Guidelines and principles specifically for Game Masters</p>
                    
                    @if($mode === 'create')
                        @forelse($create_form->gm_principles as $index => $principle)
                            <div class="flex items-start space-x-2 mb-3">
                                <textarea 
                                    wire:model="create_form.gm_principles.{{ $index }}"
                                    rows="2"
                                    class="flex-1 bg-slate-800/50 border border-slate-700/50 rounded-lg px-3 py-2 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500/50"
                                    placeholder="e.g., Paint the world in contrast..."
                                ></textarea>
                                <button 
                                    type="button"
                                    wire:click="create_form.removeGmPrincipleItem({{ $index }})"
                                    class="px-3 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        @empty
                            <p class="text-slate-500 text-sm italic">No GM principles added yet</p>
                        @endforelse
                    @else
                        @forelse($edit_form->gm_principles as $index => $principle)
                            <div class="flex items-start space-x-2 mb-3">
                                <textarea 
                                    wire:model="edit_form.gm_principles.{{ $index }}"
                                    rows="2"
                                    class="flex-1 bg-slate-800/50 border border-slate-700/50 rounded-lg px-3 py-2 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500/50"
                                    placeholder="e.g., Paint the world in contrast..."
                                ></textarea>
                                <button 
                                    type="button"
                                    wire:click="edit_form.removeGmPrincipleItem({{ $index }})"
                                    class="px-3 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        @empty
                            <p class="text-slate-500 text-sm italic">No GM principles added yet</p>
                        @endforelse
                    @endif
                    
                    <button 
                        type="button"
                        wire:click="{{ $mode === 'create' ? 'create_form.addGmPrincipleItem' : 'edit_form.addGmPrincipleItem' }}"
                        class="mt-3 px-4 py-2 bg-orange-600 hover:bg-orange-500 text-white rounded-lg transition-colors flex items-center space-x-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Add GM Principle</span>
                    </button>
                </div>
            </div>

            <!-- Setting Guidance & Distinctions -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Setting Guidance -->
                <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                    <h2 class="font-outfit text-xl text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-3 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                        Setting Guidance
                    </h2>
                    <p class="text-slate-400 text-sm mb-4">Guidance for integrating the setting</p>
                    
                    @if($mode === 'create')
                        @forelse($create_form->setting_guidance as $index => $guidance)
                            <div class="flex items-start space-x-2 mb-3">
                                <textarea 
                                    wire:model="create_form.setting_guidance.{{ $index }}"
                                    rows="2"
                                    class="flex-1 bg-slate-800/50 border border-slate-700/50 rounded-lg px-3 py-2 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-yellow-500/50"
                                    placeholder="Setting guidance or recommendation..."
                                ></textarea>
                                <button 
                                    type="button"
                                    wire:click="create_form.removeSettingGuidanceItem({{ $index }})"
                                    class="px-3 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        @empty
                            <p class="text-slate-500 text-sm italic">No setting guidance added yet</p>
                        @endforelse
                    @else
                        @forelse($edit_form->setting_guidance as $index => $guidance)
                            <div class="flex items-start space-x-2 mb-3">
                                <textarea 
                                    wire:model="edit_form.setting_guidance.{{ $index }}"
                                    rows="2"
                                    class="flex-1 bg-slate-800/50 border border-slate-700/50 rounded-lg px-3 py-2 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-yellow-500/50"
                                    placeholder="Setting guidance or recommendation..."
                                ></textarea>
                                <button 
                                    type="button"
                                    wire:click="edit_form.removeSettingGuidanceItem({{ $index }})"
                                    class="px-3 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        @empty
                            <p class="text-slate-500 text-sm italic">No setting guidance added yet</p>
                        @endforelse
                    @endif
                    
                    <button 
                        type="button"
                        wire:click="{{ $mode === 'create' ? 'create_form.addSettingGuidanceItem' : 'edit_form.addSettingGuidanceItem' }}"
                        class="mt-3 px-4 py-2 bg-yellow-600 hover:bg-yellow-500 text-white rounded-lg transition-colors flex items-center space-x-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Add Setting Guidance</span>
                    </button>
                </div>

                <!-- Setting Distinctions -->
                <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                    <h2 class="font-outfit text-xl text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-3 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                        Setting Distinctions
                    </h2>
                    <p class="text-slate-400 text-sm mb-4">Unique setting features and distinctions</p>
                    
                    @if($mode === 'create')
                        @forelse($create_form->setting_distinctions as $index => $distinction)
                            <div class="flex items-start space-x-2 mb-3">
                                <textarea 
                                    wire:model="create_form.setting_distinctions.{{ $index }}"
                                    rows="2"
                                    class="flex-1 bg-slate-800/50 border border-slate-700/50 rounded-lg px-3 py-2 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-pink-500/50"
                                    placeholder="Unique setting distinction..."
                                ></textarea>
                                <button 
                                    type="button"
                                    wire:click="create_form.removeSettingDistinctionItem({{ $index }})"
                                    class="px-3 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        @empty
                            <p class="text-slate-500 text-sm italic">No setting distinctions added yet</p>
                        @endforelse
                    @else
                        @forelse($edit_form->setting_distinctions as $index => $distinction)
                            <div class="flex items-start space-x-2 mb-3">
                                <textarea 
                                    wire:model="edit_form.setting_distinctions.{{ $index }}"
                                    rows="2"
                                    class="flex-1 bg-slate-800/50 border border-slate-700/50 rounded-lg px-3 py-2 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-pink-500/50"
                                    placeholder="Unique setting distinction..."
                                ></textarea>
                                <button 
                                    type="button"
                                    wire:click="edit_form.removeSettingDistinctionItem({{ $index }})"
                                    class="px-3 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        @empty
                            <p class="text-slate-500 text-sm italic">No setting distinctions added yet</p>
                        @endforelse
                    @endif
                    
                    <button 
                        type="button"
                        wire:click="{{ $mode === 'create' ? 'create_form.addSettingDistinctionItem' : 'edit_form.addSettingDistinctionItem' }}"
                        class="mt-3 px-4 py-2 bg-pink-600 hover:bg-pink-500 text-white rounded-lg transition-colors flex items-center space-x-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Add Setting Distinction</span>
                    </button>
                </div>
            </div>

            <!-- Inciting Incident -->
            <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                <h2 class="font-outfit text-xl text-white mb-6 flex items-center">
                    <svg class="w-5 h-5 mr-3 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Inciting Incident
                </h2>
                <p class="text-slate-400 text-sm mb-4">The event that launches the campaign and brings the characters together</p>
                
                <textarea 
                    wire:model="{{ $mode === 'create' ? 'create_form.inciting_incident' : 'edit_form.inciting_incident' }}"
                    rows="4"
                    class="w-full bg-slate-800/50 border border-slate-700/50 rounded-xl px-4 py-3 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:border-red-500/50"
                    placeholder="What event launches the campaign and brings the characters together? This should provide a clear starting point for the adventure..."
                    maxlength="1000"
                ></textarea>
                @error($mode === 'create' ? 'create_form.inciting_incident' : 'edit_form.inciting_incident') 
                    <span class="text-red-400 text-sm mt-1">{{ $message }}</span> 
                @enderror
            </div>

            <!-- Campaign Mechanics -->
            <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                <h2 class="font-outfit text-xl text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-3 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Campaign Mechanics
                </h2>
                <p class="text-slate-400 text-sm mb-4">Special mechanics unique to this campaign</p>
                
                @if($mode === 'create')
                    @forelse($create_form->campaign_mechanics as $index => $mechanic)
                        <div class="flex items-start space-x-2 mb-3">
                            <textarea 
                                wire:model="create_form.campaign_mechanics.{{ $index }}"
                                rows="3"
                                class="flex-1 bg-slate-800/50 border border-slate-700/50 rounded-lg px-3 py-2 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500/50"
                                placeholder="Describe a special campaign mechanic..."
                            ></textarea>
                            <button 
                                type="button"
                                wire:click="create_form.removeCampaignMechanicItem({{ $index }})"
                                class="px-3 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    @empty
                        <p class="text-slate-500 text-sm italic">No campaign mechanics added yet</p>
                    @endforelse
                @else
                    @forelse($edit_form->campaign_mechanics as $index => $mechanic)
                        <div class="flex items-start space-x-2 mb-3">
                            <textarea 
                                wire:model="edit_form.campaign_mechanics.{{ $index }}"
                                rows="3"
                                class="flex-1 bg-slate-800/50 border border-slate-700/50 rounded-lg px-3 py-2 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500/50"
                                placeholder="Describe a special campaign mechanic..."
                            ></textarea>
                            <button 
                                type="button"
                                wire:click="edit_form.removeCampaignMechanicItem({{ $index }})"
                                class="px-3 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    @empty
                        <p class="text-slate-500 text-sm italic">No campaign mechanics added yet</p>
                    @endforelse
                @endif
                
                <button 
                    type="button"
                    wire:click="{{ $mode === 'create' ? 'create_form.addCampaignMechanicItem' : 'edit_form.addCampaignMechanicItem' }}"
                    class="mt-3 px-4 py-2 bg-violet-600 hover:bg-violet-500 text-white rounded-lg transition-colors flex items-center space-x-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Add Campaign Mechanic</span>
                </button>
            </div>

            <!-- Session Zero Questions -->
            <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                <h2 class="font-outfit text-xl text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-3 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Session Zero Questions
                </h2>
                <p class="text-slate-400 text-sm mb-4">Questions to consider during session zero</p>
                
                @if($mode === 'create')
                    @forelse($create_form->session_zero_questions as $index => $question)
                        <div class="flex items-start space-x-2 mb-3">
                            <textarea 
                                wire:model="create_form.session_zero_questions.{{ $index }}"
                                rows="2"
                                class="flex-1 bg-slate-800/50 border border-slate-700/50 rounded-lg px-3 py-2 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500/50"
                                placeholder="Session zero question..."
                            ></textarea>
                            <button 
                                type="button"
                                wire:click="create_form.removeSessionZeroQuestionItem({{ $index }})"
                                class="px-3 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    @empty
                        <p class="text-slate-500 text-sm italic">No session zero questions added yet</p>
                    @endforelse
                @else
                    @forelse($edit_form->session_zero_questions as $index => $question)
                        <div class="flex items-start space-x-2 mb-3">
                            <textarea 
                                wire:model="edit_form.session_zero_questions.{{ $index }}"
                                rows="2"
                                class="flex-1 bg-slate-800/50 border border-slate-700/50 rounded-lg px-3 py-2 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500/50"
                                placeholder="Session zero question..."
                            ></textarea>
                            <button 
                                type="button"
                                wire:click="edit_form.removeSessionZeroQuestionItem({{ $index }})"
                                class="px-3 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    @empty
                        <p class="text-slate-500 text-sm italic">No session zero questions added yet</p>
                    @endforelse
                @endif
                
                <button 
                    type="button"
                    wire:click="{{ $mode === 'create' ? 'create_form.addSessionZeroQuestionItem' : 'edit_form.addSessionZeroQuestionItem' }}"
                    class="mt-3 px-4 py-2 bg-teal-600 hover:bg-teal-500 text-white rounded-lg transition-colors flex items-center space-x-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Add Session Zero Question</span>
                </button>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end space-x-4">
                <a 
                    href="{{ $mode === 'edit' && $frame ? route('campaign-frames.show', $frame->id) : route('campaign-frames.index') }}" 
                    class="inline-flex items-center justify-center bg-slate-700 hover:bg-slate-600 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300"
                >
                    Cancel
                </a>
                <button 
                    type="submit" 
                    class="inline-flex items-center justify-center bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-black font-semibold py-3 px-6 rounded-xl transition-all duration-300 shadow-lg"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-50 cursor-not-allowed"
                >
                    <svg wire:loading class="animate-spin -ml-1 mr-3 h-5 w-5 text-black" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ $mode === 'create' ? 'Create Frame' : 'Update Frame' }}
                </button>
            </div>
        </div>
    </form>
</div>