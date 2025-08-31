<x-layout>
    <div class="min-h-screen">
        <div class="px-4 sm:px-6 lg:px-8 pt-12 pb-16">
            <div class="max-w-2xl mx-auto">
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="font-outfit text-3xl text-white tracking-wide mb-2">
                        Join Campaign
                    </h1>
                    <p class="text-slate-300 text-lg">
                        Select a character to join the adventure
                    </p>
                </div>

                <!-- Campaign Info -->
                <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 mb-8">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-violet-500/20 to-purple-500/20 rounded-xl flex items-center justify-center border border-violet-500/30 mr-4">
                            <svg class="w-6 h-6 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-outfit text-xl font-bold text-white">{{ $campaign->name }}</h2>
                            <p class="text-slate-400 text-sm">Created by {{ $campaign->creator?->username ?? 'Unknown' }}</p>
                        </div>
                    </div>
                    <p class="text-slate-300">{{ $campaign->description }}</p>
                </div>

                <!-- Character Selection Form -->
                <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-8">
                    <form action="{{ route('campaigns.join', $campaign->campaign_code) }}" method="POST" class="space-y-6">
                        @csrf

                        <div>
                            <label class="block text-sm font-outfit font-medium text-slate-300 mb-4">
                                Choose a Character
                            </label>

                            <div class="space-y-3">
                                <!-- Empty Character Option -->
                                <label class="flex items-center p-4 bg-slate-800/50 border border-slate-600/50 rounded-xl cursor-pointer hover:border-violet-500/30 transition-all duration-300">
                                    <input 
                                        type="radio" 
                                        name="character_id" 
                                        value="" 
                                        class="sr-only peer"
                                        {{ old('character_id', '') === '' ? 'checked' : '' }}
                                    >
                                    <div class="w-5 h-5 border-2 border-slate-400 rounded-full mr-4 peer-checked:border-violet-500 peer-checked:bg-violet-500 flex items-center justify-center">
                                        <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100"></div>
                                    </div>
                                    <div class="flex items-center flex-1">
                                        <div class="w-10 h-10 bg-slate-600 rounded-lg mr-4 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="font-outfit font-semibold text-white">Empty Character</h3>
                                            <p class="text-slate-400 text-sm">Join without a specific character (you can add one later)</p>
                                        </div>
                                    </div>
                                </label>

                                <!-- User Characters -->
                                @if($characters->isNotEmpty())
                                    @foreach($characters as $character)
                                        <label class="flex items-center p-4 bg-slate-800/50 border border-slate-600/50 rounded-xl cursor-pointer hover:border-violet-500/30 transition-all duration-300">
                                            <input 
                                                type="radio" 
                                                name="character_id" 
                                                value="{{ $character->id }}" 
                                                class="sr-only peer"
                                                {{ old('character_id') == $character->id ? 'checked' : '' }}
                                            >
                                            <div class="w-5 h-5 border-2 border-slate-400 rounded-full mr-4 peer-checked:border-violet-500 peer-checked:bg-violet-500 flex items-center justify-center">
                                                <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100"></div>
                                            </div>
                                            <div class="flex items-center flex-1">
                                                @if(method_exists($character, 'getBanner'))
                                                    <img src="{{ $character->getBanner() }}" alt="{{ $character->class }}" class="w-10 h-10 rounded-lg mr-4">
                                                @else
                                                    <div class="w-10 h-10 bg-amber-500/20 rounded-lg mr-4 flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                        </svg>
                                                    </div>
                                                @endif
                                                <div>
                                                    <h3 class="font-outfit font-semibold text-white">{{ $character->name }}</h3>
                                                    <p class="text-slate-400 text-sm">
                                                        {{ $character->class }} / {{ $character->subclass ?? 'Unknown' }} • 
                                                        {{ $character->ancestry ?? 'Unknown' }} • {{ $character->community ?? 'Unknown' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                @endif
                            </div>

                            @error('character_id')
                                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        @if($characters->isEmpty())
                            <!-- No Characters Info -->
                            <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-4">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-blue-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div>
                                        <h3 class="text-blue-400 font-outfit font-semibold text-sm">No Characters Found</h3>
                                        <p class="text-blue-300/80 text-sm mt-1">
                                            You don't have any characters yet. You can join with an empty character slot and 
                                            <a href="{{ route('character-builder') }}" class="underline hover:text-blue-300">create a character</a> later.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Actions -->
                        <div class="flex items-center gap-4">
                            <button 
                                type="submit"
                                class="flex-1 bg-gradient-to-r from-violet-500 to-purple-500 hover:from-violet-400 hover:to-purple-400 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 shadow-lg hover:shadow-violet-500/25"
                            >
                                Join Campaign
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
