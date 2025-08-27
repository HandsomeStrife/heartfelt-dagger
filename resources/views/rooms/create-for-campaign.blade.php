<x-layout>
    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-slate-900/80 backdrop-blur-xl rounded-2xl border border-slate-700 p-8">
                <div class="mb-8">
                    <h1 class="text-3xl font-outfit font-bold text-white mb-4">Create Campaign Room</h1>
                    <div class="bg-indigo-500/10 border border-indigo-500/30 rounded-xl p-4">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-indigo-300 font-semibold">Campaign: {{ $campaign->name }}</h3>
                                <p class="text-indigo-200/90 text-sm">This room will be accessible only to campaign members.</p>
                            </div>
                        </div>
                    </div>
                </div>

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

                <form action="{{ route('campaigns.rooms.store', $campaign->campaign_code) }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Room Name -->
                        <div>
                            <label class="block text-slate-300 text-sm font-semibold mb-2">Room Name</label>
                            <input 
                                type="text" 
                                name="name" 
                                id="name"
                                required 
                                maxlength="100"
                                value="{{ old('name') }}"
                                placeholder="e.g., Main Session Room"
                                class="w-full bg-slate-800 text-white px-4 py-3 rounded-lg border border-slate-600 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                            >
                        </div>

                        <!-- Guest Count -->
                        <div>
                            <label class="block text-slate-300 text-sm font-semibold mb-2">Max Participants</label>
                            <select 
                                name="guest_count" 
                                id="guest_count"
                                required
                                class="w-full bg-slate-800 text-white px-4 py-3 rounded-lg border border-slate-600 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                            >
                                <option value="">Select max participants</option>
                                @for ($i = 2; $i <= 6; $i++)
                                    <option value="{{ $i }}" {{ old('guest_count') == $i ? 'selected' : '' }}>
                                        {{ $i }} participant{{ $i > 1 ? 's' : '' }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-slate-300 text-sm font-semibold mb-2">Description</label>
                        <textarea 
                            name="description" 
                            id="description"
                            required 
                            maxlength="500"
                            rows="4"
                            placeholder="Describe the purpose of this room session..."
                            class="w-full bg-slate-800 text-white px-4 py-3 rounded-lg border border-slate-600 focus:ring-indigo-500 focus:border-indigo-500 transition-colors resize-none"
                        >{{ old('description') }}</textarea>
                    </div>

                    <!-- Campaign Room Notice -->
                    <div class="bg-indigo-500/10 border border-indigo-500/30 rounded-xl p-4">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-indigo-300 font-semibold">No Password Required</h3>
                                <p class="text-indigo-200/90 text-sm">Access is automatically restricted to campaign members only.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('campaigns.show', $campaign->campaign_code) }}" 
                           class="px-6 py-3 text-slate-400 hover:text-white font-semibold transition-colors">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-400 hover:to-purple-400 text-white font-semibold py-3 px-8 rounded-xl transition-all duration-300 shadow-lg hover:shadow-indigo-500/25">
                            Create Campaign Room
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>
