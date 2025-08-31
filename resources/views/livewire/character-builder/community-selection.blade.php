<!-- Community Selection Step -->
<div x-cloak>
    <!-- Step Header -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-white mb-2 font-outfit">Choose Your Community</h2>
        <p class="text-slate-300 font-roboto">Your community represents where and how you were raised, shaping your background and skills.</p>
    </div>

    <!-- Step Completion Indicator -->
    <div x-show="selected_community" class="my-6 p-4 bg-gradient-to-r from-emerald-500/10 to-green-500/10 border border-emerald-500/20 rounded-xl">
        <div class="flex items-center">
            <div class="bg-emerald-500 rounded-full p-2 mr-3">
                <svg class="w-5 h-5 text-black" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            </div>
            <div>
                <p class="text-emerald-400 font-semibold">Community Selection Complete!</p>
                <p class="text-slate-300 text-sm">
                    You are from the <strong x-text="selected_community ? ({{ json_encode($game_data['communities'] ?? []) }}[selected_community]?.name || '') : ''"></strong> community
                </p>
            </div>
        </div>
    </div>

    <!-- Community Selection Grid -->
    <div x-show="!hasSelectedCommunity">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            @foreach($game_data['communities'] ?? [] as $communityKey => $communityData)
                <div 
                    dusk="community-card-{{ $communityKey }}"
                    @click="selectCommunity('{{ $communityKey }}')"
                    class="relative group cursor-pointer transition-all duration-300 transform hover:scale-105 bg-gradient-to-br from-slate-800/90 to-slate-900/90 backdrop-blur border border-slate-700/50 hover:border-slate-600/70 hover:shadow-lg hover:shadow-slate-500/10 rounded-2xl p-6"
                >
                    <div class="flex flex-col h-full">
                        <!-- Header -->
                        <div class="mb-4">
                            <h3 class="text-xl font-bold text-white font-outfit mb-2">{{ $communityData['name'] }}</h3>
                            <p class="text-slate-300 text-sm line-clamp-3 leading-relaxed mb-3">
                                {{ $communityData['description'] }}
                            </p>
                        </div>

                        <!-- Features Preview -->
                        @if(!empty($communityData['features']))
                            <div class="space-y-2 flex-grow">
                                <h4 class="text-emerald-400 font-semibold text-sm font-outfit">Features</h4>
                                @foreach(array_slice($communityData['features'], 0, 2) as $feature)
                                    <div class="bg-slate-800/50 rounded-lg p-3">
                                        <div class="text-white font-medium text-sm mb-1">{{ $feature['name'] }}</div>
                                        <div class="text-slate-300 text-xs line-clamp-2">{{ $feature['description'] }}</div>
                                    </div>
                                @endforeach
                                @if(count($communityData['features']) > 2)
                                    <p class="text-slate-400 text-xs">+{{ count($communityData['features']) - 2 }} more features</p>
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

    <!-- Selected Community Details -->
    <div x-show="hasSelectedCommunity">
        @foreach($game_data['communities'] ?? [] as $communityKey => $communityData)
            <div x-show="selected_community === '{{ $communityKey }}'">
                <div class="bg-gradient-to-br from-slate-800/90 to-slate-900/90 backdrop-blur border border-slate-700/50 rounded-2xl p-6">
                    <!-- Header with Change Button -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6">
                        <div>
                            <h3 class="text-2xl font-bold text-white font-outfit mb-2">{{ $communityData['name'] }}</h3>
                            <p class="text-slate-300">{{ $communityData['description'] }}</p>
                        </div>
                        <button 
                            dusk="change-community-button"
                            x-on:click="selectCommunity(null)"
                            class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg border border-slate-600 hover:border-slate-500 transition-all duration-200 text-sm font-medium w-full sm:w-auto mt-4 sm:mt-0"
                        >
                            Change Community
                        </button>
                    </div>

                    <!-- Features -->
                    @if(!empty($communityData['features']))
                        <div class="mb-6">
                            <h4 class="text-lg font-semibold text-white font-outfit mb-3">Community Features</h4>
                            <div class="space-y-3">
                                @foreach($communityData['features'] as $feature)
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
