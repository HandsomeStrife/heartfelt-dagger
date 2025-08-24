<!-- Ancestry Selection Step -->
<div x-cloak>
    <!-- Step Header -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-white mb-2 font-outfit">Choose Your Ancestry</h2>
        <p class="text-slate-300 font-roboto">Your ancestry determines your physical traits and innate abilities.</p>
    </div>

    <!-- Step Completion Indicator -->
    <div x-show="selected_ancestry" class="my-6 p-4 bg-gradient-to-r from-emerald-500/10 to-green-500/10 border border-emerald-500/20 rounded-xl">
        <div class="flex items-center">
            <div class="bg-emerald-500 rounded-full p-2 mr-3">
                <svg class="w-5 h-5 text-black" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            </div>
            <div>
                <p class="text-emerald-400 font-semibold">Ancestry Selection Complete!</p>
                <p class="text-slate-300 text-sm">
                    You are a <strong x-text="selected_ancestry ? ({{ json_encode($game_data['ancestries'] ?? []) }}[selected_ancestry]?.name || '') : ''"></strong>
                </p>
            </div>
        </div>
    </div>

    <!-- Ancestry Selection Grid -->
    <div x-show="!hasSelectedAncestry">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            @foreach($game_data['ancestries'] ?? [] as $ancestryKey => $ancestryData)
                <div 
                    dusk="ancestry-card-{{ $ancestryKey }}"
                    @click="selectAncestry('{{ $ancestryKey }}')"
                    class="relative group cursor-pointer transition-all duration-300 transform hover:scale-105 bg-gradient-to-br from-slate-800/90 to-slate-900/90 backdrop-blur border border-slate-700/50 hover:border-slate-600/70 hover:shadow-lg hover:shadow-slate-500/10 rounded-2xl p-6"
                >
                    <!-- Playtest Badge -->
                    @if(isset($ancestryData['playtest']['isPlaytest']) && $ancestryData['playtest']['isPlaytest'])
                        <div class="absolute -top-2 -right-2 bg-gradient-to-r from-purple-600 to-violet-600 text-white text-xs font-bold px-3 py-1 rounded-full border-2 border-slate-800 shadow-lg">
                            <div class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Void - Playtest {{ $ancestryData['playtest']['version'] ?? 'v2.0' }}
                            </div>
                        </div>
                    @endif

                    <div class="flex flex-col h-full">
                        <!-- Header -->
                        <div class="mb-4">
                            <h3 class="text-xl font-bold text-white font-outfit mb-2">{{ $ancestryData['name'] }}</h3>
                            <p class="text-slate-300 text-sm line-clamp-3 leading-relaxed mb-3">
                                {{ $ancestryData['description'] }}
                            </p>
                        </div>

                        <!-- Features Preview -->
                        @if(!empty($ancestryData['features']))
                            <div class="space-y-2 flex-grow">
                                <h4 class="text-emerald-400 font-semibold text-sm font-outfit">Features</h4>
                                @foreach(array_slice($ancestryData['features'], 0, 2) as $feature)
                                    <div class="bg-slate-800/50 rounded-lg p-3">
                                        <div class="text-white font-medium text-sm mb-1">{{ $feature['name'] }}</div>
                                        <div class="text-slate-300 text-xs line-clamp-2">{{ $feature['description'] }}</div>
                                    </div>
                                @endforeach
                                @if(count($ancestryData['features']) > 2)
                                    <p class="text-slate-400 text-xs">+{{ count($ancestryData['features']) - 2 }} more features</p>
                                @endif
                            </div>
                        @endif

                        <!-- Hover indicator -->
                        <div class="mt-4 text-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <span class="text-amber-400 text-sm font-medium">Click to select</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Selected Ancestry Details -->
    <div x-show="hasSelectedAncestry">
        @foreach($game_data['ancestries'] ?? [] as $ancestryKey => $ancestryData)
            <div x-show="selected_ancestry === '{{ $ancestryKey }}'">
                <div class="bg-gradient-to-br from-slate-800/90 to-slate-900/90 backdrop-blur border border-slate-700/50 rounded-2xl p-6">
                    <!-- Header with Change Button -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6">
                        <div>
                            <h3 class="text-2xl font-bold text-white font-outfit mb-2">{{ $ancestryData['name'] }}</h3>
                            <p class="text-slate-300">{{ $ancestryData['description'] }}</p>
                        </div>
                        <button 
                            dusk="change-ancestry-button"
                            x-on:click="selectAncestry(null)"
                            class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg border border-slate-600 hover:border-slate-500 transition-all duration-200 text-sm font-medium w-full sm:w-auto mt-4 sm:mt-0"
                        >
                            Change Ancestry
                        </button>
                    </div>

                    <!-- Features -->
                    @if(!empty($ancestryData['features']))
                        <div class="mb-6">
                            <h4 class="text-lg font-semibold text-white font-outfit mb-3">Ancestry Features</h4>
                            <div class="space-y-3">
                                @foreach($ancestryData['features'] as $feature)
                                    <div class="bg-slate-800/50 border border-slate-700/50 rounded-lg p-4">
                                        <h5 class="text-white font-semibold text-sm mb-2">{{ $feature['name'] }}</h5>
                                        <p class="text-slate-300 text-sm leading-relaxed">{{ $feature['description'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
