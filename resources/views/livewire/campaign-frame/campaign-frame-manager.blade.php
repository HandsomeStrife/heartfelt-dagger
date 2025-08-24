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

            <!-- Background Overview -->
            <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                <h2 class="font-outfit text-xl text-white mb-6 flex items-center">
                    <svg class="w-5 h-5 mr-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    Background Overview
                </h2>
                
                <textarea 
                    wire:model="{{ $mode === 'create' ? 'create_form.background_overview' : 'edit_form.background_overview' }}"
                    rows="4"
                    class="w-full bg-slate-800/50 border border-slate-700/50 rounded-xl px-4 py-3 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500/50"
                    placeholder="Provide an overview of the campaign's background and setting..."
                    maxlength="2000"
                ></textarea>
                @error($mode === 'create' ? 'create_form.background_overview' : 'edit_form.background_overview') 
                    <span class="text-red-400 text-sm mt-1">{{ $message }}</span> 
                @enderror
            </div>

            <!-- Inciting Incident -->
            <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                <h2 class="font-outfit text-xl text-white mb-6 flex items-center">
                    <svg class="w-5 h-5 mr-3 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Inciting Incident
                </h2>
                
                <textarea 
                    wire:model="{{ $mode === 'create' ? 'create_form.inciting_incident' : 'edit_form.inciting_incident' }}"
                    rows="3"
                    class="w-full bg-slate-800/50 border border-slate-700/50 rounded-xl px-4 py-3 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:border-red-500/50"
                    placeholder="What event launches the campaign and brings the characters together?"
                    maxlength="1000"
                ></textarea>
                @error($mode === 'create' ? 'create_form.inciting_incident' : 'edit_form.inciting_incident') 
                    <span class="text-red-400 text-sm mt-1">{{ $message }}</span> 
                @enderror
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