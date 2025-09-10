<x-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950">
        <!-- Compact Navigation -->
        <x-sub-navigation>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a 
                        href="{{ route('rooms.index') }}"
                        class="p-1.5 text-slate-400 hover:text-white hover:bg-slate-700/50 rounded-md transition-colors"
                        title="Back to rooms"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <div>
                        <h1 class="font-outfit text-lg font-bold text-white tracking-wide">
                            Create New Room
                        </h1>
                        <p class="text-slate-400 text-xs">
                            Set up a video chat room for your gaming session
                        </p>
                    </div>
                </div>
            </div>
        </x-sub-navigation>

        <div class="px-4 sm:px-6 lg:px-8 pt-8 pb-12">
            <div class="max-w-3xl mx-auto space-y-6">

                <!-- Form -->
                <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                    <form action="{{ route('rooms.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- Room Name -->
                        <div>
                            <label for="name" class="block text-sm font-semibold text-slate-200 mb-2">
                                Room Name <span class="text-red-400">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                value="{{ old('name') }}"
                                class="w-full px-4 py-3 bg-slate-800/50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all" 
                                placeholder="Enter room name (max 100 characters)"
                                maxlength="100"
                                required
                            >
                            @error('name')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-semibold text-slate-200 mb-2">
                                Description <span class="text-red-400">*</span>
                            </label>
                            <textarea 
                                id="description" 
                                name="description" 
                                rows="4"
                                class="w-full px-4 py-3 bg-slate-800/50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all" 
                                placeholder="Describe your room and what kind of session you're planning (max 500 characters)"
                                maxlength="500"
                                required
                            >{{ old('description') }}</textarea>
                            @error('description')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Room Password -->
                        <div>
                            <label for="password" class="block text-sm font-semibold text-slate-200 mb-2">
                                Room Password <span class="text-slate-500">(Optional)</span>
                            </label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="w-full px-4 py-3 bg-slate-800/50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all" 
                                placeholder="Enter room password (leave blank for no password)"
                            >
                            <p class="text-slate-400 text-sm mt-1">If set, participants will need this password to join your room</p>
                            @error('password')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Guest Count -->
                        <div>
                            <label for="guest_count" class="block text-sm font-semibold text-slate-200 mb-2">
                                Maximum Participants <span class="text-red-400">*</span>
                            </label>
                            <select 
                                id="guest_count" 
                                name="guest_count" 
                                class="w-full px-4 py-3 bg-slate-800/50 border border-slate-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                                required
                            >
                                <option value="">Select maximum participants</option>
                                <option value="2" {{ old('guest_count') == '2' ? 'selected' : '' }}>2 Participants (Side-by-side)</option>
                                <option value="3" {{ old('guest_count') == '3' ? 'selected' : '' }}>3 Participants (Triangle layout)</option>
                                <option value="4" {{ old('guest_count') == '4' ? 'selected' : '' }}>4 Participants (2x2 grid)</option>
                                <option value="5" {{ old('guest_count') == '5' ? 'selected' : '' }}>5 Participants (2x3 grid)</option>
                                <option value="6" {{ old('guest_count') == '6' ? 'selected' : '' }}>6 Participants (2x3 grid expanded)</option>
                            </select>
                            <p class="text-slate-400 text-sm mt-1">This determines the video layout for your room</p>
                            @error('guest_count')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-between pt-6 border-t border-slate-700">
                            <a href="{{ route('rooms.index') }}" class="px-6 py-3 text-slate-300 hover:text-white transition-colors">
                                Cancel
                            </a>
                            <button type="submit" class="bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-white font-semibold py-3 px-8 rounded-xl transition-all duration-300 shadow-lg hover:shadow-emerald-500/25">
                                Create Room
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>
