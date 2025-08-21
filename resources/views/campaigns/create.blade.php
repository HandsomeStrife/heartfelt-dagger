<x-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950">
        <div class="px-4 sm:px-6 lg:px-8 pt-12 pb-16">
            <div class="max-w-2xl mx-auto">
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="font-outfit text-3xl text-white tracking-wide mb-2">
                        Create Campaign
                    </h1>
                    <p class="text-slate-300 text-lg">
                        Start your next epic adventure
                    </p>
                </div>

                <!-- Create Form -->
                <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-8">
                    <form action="{{ route('campaigns.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- Campaign Name -->
                        <div>
                            <label for="name" class="block text-sm font-outfit font-medium text-slate-300 mb-2">
                                Campaign Name
                            </label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                value="{{ old('name') }}"
                                maxlength="100"
                                required
                                class="w-full px-4 py-3 bg-slate-800/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:border-violet-500/50 focus:ring-2 focus:ring-violet-500/20 focus:outline-none transition-all duration-300"
                                placeholder="Enter your campaign name..."
                            >
                            @error('name')
                                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Campaign Description -->
                        <div>
                            <label for="description" class="block text-sm font-outfit font-medium text-slate-300 mb-2">
                                Description
                            </label>
                            <textarea 
                                id="description" 
                                name="description" 
                                rows="4"
                                maxlength="1000"
                                required
                                class="w-full px-4 py-3 bg-slate-800/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:border-violet-500/50 focus:ring-2 focus:ring-violet-500/20 focus:outline-none transition-all duration-300 resize-none"
                                placeholder="Describe your campaign setting, tone, and what players can expect..."
                            >{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-xs text-slate-400">Maximum 1000 characters</p>
                        </div>

                        <!-- Info Box -->
                        <div class="bg-violet-500/10 border border-violet-500/30 rounded-xl p-4">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-violet-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <h3 class="text-violet-400 font-outfit font-semibold text-sm">What happens next?</h3>
                                    <p class="text-violet-300/80 text-sm mt-1">
                                        Once created, you'll get a unique invite code that you can share with players. 
                                        They can join your campaign and select which character they want to play with.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-4">
                            <button 
                                type="submit"
                                class="flex-1 bg-gradient-to-r from-violet-500 to-purple-500 hover:from-violet-400 hover:to-purple-400 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 shadow-lg hover:shadow-violet-500/25"
                            >
                                Create Campaign
                            </button>
                            <a 
                                href="{{ route('campaigns.index') }}"
                                class="px-6 py-3 bg-slate-700 hover:bg-slate-600 text-white font-semibold rounded-xl transition-colors border border-slate-600"
                            >
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>
