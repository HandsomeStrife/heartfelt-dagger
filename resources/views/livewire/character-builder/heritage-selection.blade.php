<!-- Heritage Selection Step -->
<div class="space-y-8">
    <!-- Step Header -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-white mb-2 font-outfit">Heritage</h2>
        <p class="text-slate-300 font-roboto">Choose your ancestry and community to establish your character's heritage.</p>
    </div>

    <!-- Step Completion Indicator -->
    <div x-show="selected_ancestry && selected_community" class="my-6 p-4 bg-gradient-to-r from-emerald-500/10 to-green-500/10 border border-emerald-500/20 rounded-xl">
        <div class="flex items-center">
            <div class="bg-emerald-500 rounded-full p-2 mr-3">
                <svg class="w-5 h-5 text-black" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            </div>
            <div>
                <p class="text-emerald-400 font-semibold">Heritage Selection Complete!</p>
                <p class="text-slate-300 text-sm">
                    You are a <strong x-text="selected_ancestry ? ({{ json_encode($game_data['ancestries'] ?? []) }}[selected_ancestry]?.name || '') : ''"></strong>
                    <span x-show="selected_ancestry && selected_community"> from the </span>
                    <strong x-text="selected_community ? ({{ json_encode($game_data['communities'] ?? []) }}[selected_community]?.name || '') : ''"></strong>
                    <span x-show="selected_ancestry && selected_community"> community</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Left Column: Ancestry Selection -->
        <div>
            <div class="mb-6">
                <h3 class="text-xl font-bold text-white font-outfit mb-2">Choose Your Ancestry</h3>
                <p class="text-slate-300 text-sm">Your ancestry determines your physical traits and innate abilities.</p>
            </div>

            <!-- Ancestry Grid - Show When No Ancestry Selected -->
            <div x-show="!hasSelectedAncestry">
                <div class="grid grid-cols-1 gap-4">
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
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        {{ $ancestryData['playtest']['label'] ?? 'Void - Playtest' }}
                                    </div>
                                </div>
                            @endif
                            
                            <div class="flex flex-row gap-4">
                                <div class="w-24 h-24 bg-gradient-to-r from-emerald-700 to-emerald-600 rounded-xl flex items-center justify-center">
                                    <span class="text-2xl">{{ substr($ancestryData['name'], 0, 1) }}</span>
                                </div>
                                
                                <div class="flex-1">
                                    <h4 class="text-xl font-bold text-white font-outfit">{{ $ancestryData['name'] }}</h4>
                                    <p class="text-slate-300 text-sm line-clamp-3 leading-relaxed">
                                        {{ Str::limit($ancestryData['description'], 150) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Selected Ancestry Details - Show When Ancestry Selected -->
            <div x-show="hasSelectedAncestry">
                @foreach($game_data['ancestries'] ?? [] as $ancestryKey => $ancestryData)
                    <div x-show="selected_ancestry === '{{ $ancestryKey }}'">
                        <div class="mb-6">
                            <button 
                                @click="selectAncestry(null)"
                                class="inline-flex items-center px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition-colors duration-200 mb-4"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Change Ancestry
                            </button>
                        </div>
                        
                        <div class="relative group cursor-pointer transition-all duration-300 transform hover:scale-105 bg-gradient-to-br from-slate-800/90 to-slate-900/90 backdrop-blur border border-slate-700/50 hover:border-slate-600/70 hover:shadow-lg hover:shadow-slate-500/10 rounded-2xl p-6">
                            <!-- Playtest Badge for Selected View -->
                            @if(isset($ancestryData['playtest']['isPlaytest']) && $ancestryData['playtest']['isPlaytest'])
                                <div class="absolute -top-2 -right-2 bg-gradient-to-r from-purple-600 to-violet-600 text-white text-xs font-bold px-3 py-1 rounded-full border-2 border-slate-800 shadow-lg">
                                    <div class="flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        {{ $ancestryData['playtest']['label'] ?? 'Void - Playtest' }}
                                    </div>
                                </div>
                            @endif
                            
                            <div>
                                <h2 class="text-2xl font-bold text-white font-outfit mb-4">{{ $ancestryData['name'] }}</h2>
                                
                                @if(isset($ancestryData['playtest']['isPlaytest']) && $ancestryData['playtest']['isPlaytest'])
                                    <div class="mb-4 p-3 bg-purple-900/30 border border-purple-500/30 rounded-lg">
                                        <p class="text-purple-300 text-sm">
                                            <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                            </svg>
                                            This ancestry is part of DaggerHeart's {{ $ancestryData['playtest']['label'] ?? 'playtest' }} content and may be subject to changes based on community feedback.
                                        </p>
                                    </div>
                                @endif
                                
                                <p class="text-slate-300 text-base leading-relaxed">
                                    {{ $ancestryData['description'] }}
                                </p>
                            </div>

                            <!-- Ancestry Features -->
                            @if(isset($ancestryData['features']) && count($ancestryData['features']) > 0)
                                <div>
                                    <h4 class="text-lg font-bold text-emerald-400 font-outfit my-4">Ancestry Features</h4>
                                    <div class="space-y-4">
                                        @foreach($ancestryData['features'] as $feature)
                                            <div class="bg-slate-800/50 rounded-lg p-4">
                                                <h5 class="text-white font-medium text-sm mb-2">{{ $feature['name'] }}</h5>
                                                <div class="text-slate-300 text-sm leading-relaxed">
                                                    @markdown($feature['description'])
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Character Stat Bonuses -->
                            @if(!empty($ancestry_bonuses) && $character->selected_ancestry === $ancestryKey)
                                <div>
                                    <h4 class="text-lg font-bold text-amber-400 font-outfit my-4">
                                        <svg class="inline w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd" />
                                        </svg>
                                        Character Bonuses
                                    </h4>
                                    <div class="bg-gradient-to-r from-amber-500/10 to-orange-500/10 border border-amber-500/20 rounded-lg p-4">
                                        <p class="text-amber-200 text-sm mb-3 font-medium">This ancestry provides the following bonuses to your character:</p>
                                        <div class="grid grid-cols-1 gap-2">
                                            @if(isset($ancestry_bonuses['evasion']))
                                                <div class="flex items-center text-sm">
                                                    <div class="w-2 h-2 bg-amber-400 rounded-full mr-3"></div>
                                                    <span class="text-white">+{{ $ancestry_bonuses['evasion'] }} Evasion</span>
                                                    <span class="text-slate-400 ml-2">(Makes you harder to hit)</span>
                                                </div>
                                            @endif
                                            @if(isset($ancestry_bonuses['hit_points']))
                                                <div class="flex items-center text-sm">
                                                    <div class="w-2 h-2 bg-red-400 rounded-full mr-3"></div>
                                                    <span class="text-white">+{{ $ancestry_bonuses['hit_points'] }} Hit Point slot</span>
                                                    <span class="text-slate-400 ml-2">(More health)</span>
                                                </div>
                                            @endif
                                            @if(isset($ancestry_bonuses['stress']))
                                                <div class="flex items-center text-sm">
                                                    <div class="w-2 h-2 bg-blue-400 rounded-full mr-3"></div>
                                                    <span class="text-white">+{{ $ancestry_bonuses['stress'] }} Stress slot</span>
                                                    <span class="text-slate-400 ml-2">(More resilience)</span>
                                                </div>
                                            @endif
                                            @if(isset($ancestry_bonuses['damage_thresholds']))
                                                <div class="flex items-center text-sm">
                                                    <div class="w-2 h-2 bg-green-400 rounded-full mr-3"></div>
                                                    <span class="text-white">+{{ $ancestry_bonuses['damage_thresholds'] }} to Damage Thresholds</span>
                                                    <span class="text-slate-400 ml-2">(Harder to wound seriously)</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Right Column: Community Selection -->
        <div>
            <div class="mb-6">
                <h3 class="text-xl font-bold text-white font-outfit mb-2">Choose Your Community</h3>
                <p class="text-slate-300 text-sm">Your community represents where you came from and your cultural background.</p>
            </div>

            <!-- Community Grid - Show When No Community Selected -->
            <div x-show="!hasSelectedCommunity">
                <div class="grid grid-cols-1 gap-4">
                    @foreach($game_data['communities'] ?? [] as $communityKey => $communityData)
                        <div 
                            dusk="community-card-{{ $communityKey }}"
                            @click="selectCommunity('{{ $communityKey }}')"
                            class="relative group cursor-pointer transition-all duration-300 transform hover:scale-105 bg-gradient-to-br from-slate-800/90 to-slate-900/90 backdrop-blur border border-slate-700/50 hover:border-slate-600/70 hover:shadow-lg hover:shadow-slate-500/10 rounded-2xl p-6"
                        >
                            <!-- Playtest Badge -->
                            @if(isset($communityData['playtest']['isPlaytest']) && $communityData['playtest']['isPlaytest'])
                                <div class="absolute -top-2 -right-2 bg-gradient-to-r from-purple-600 to-violet-600 text-white text-xs font-bold px-3 py-1 rounded-full border-2 border-slate-800 shadow-lg">
                                    <div class="flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        {{ $communityData['playtest']['label'] ?? 'Void - Playtest' }}
                                    </div>
                                </div>
                            @endif
                            
                            <div class="flex flex-row gap-4">
                                <div class="w-24 h-24 bg-gradient-to-r from-blue-700 to-blue-600 rounded-xl flex items-center justify-center">
                                    <span class="text-2xl">{{ substr($communityData['name'], 0, 1) }}</span>
                                </div>
                                
                                <div class="flex-1">
                                    <h4 class="text-xl font-bold text-white font-outfit">{{ $communityData['name'] }}</h4>
                                    <p class="text-slate-300 text-sm line-clamp-3 leading-relaxed">
                                        {{ Str::limit($communityData['description'], 150) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Selected Community Details - Show When Community Selected -->
            <div x-show="hasSelectedCommunity">
                @foreach($game_data['communities'] ?? [] as $communityKey => $communityData)
                    <div x-show="selected_community === '{{ $communityKey }}'">
                        <div class="mb-6">
                            <button 
                                @click="selectCommunity(null)"
                                class="inline-flex items-center px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition-colors duration-200 mb-4"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Change Community
                            </button>
                        </div>
                        
                        <div class="relative group cursor-pointer transition-all duration-300 transform hover:scale-105 bg-gradient-to-br from-slate-800/90 to-slate-900/90 backdrop-blur border border-slate-700/50 hover:border-slate-600/70 hover:shadow-lg hover:shadow-slate-500/10 rounded-2xl p-6">
                            <!-- Playtest Badge for Selected View -->
                            @if(isset($communityData['playtest']['isPlaytest']) && $communityData['playtest']['isPlaytest'])
                                <div class="absolute -top-2 -right-2 bg-gradient-to-r from-purple-600 to-violet-600 text-white text-xs font-bold px-3 py-1 rounded-full border-2 border-slate-800 shadow-lg">
                                    <div class="flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        {{ $communityData['playtest']['label'] ?? 'Void - Playtest' }}
                                    </div>
                                </div>
                            @endif
                            
                            <div>
                                <h2 class="text-2xl font-bold text-white font-outfit mb-4">{{ $communityData['name'] }}</h2>
                                
                                @if(isset($communityData['playtest']['isPlaytest']) && $communityData['playtest']['isPlaytest'])
                                    <div class="mb-4 p-3 bg-purple-900/30 border border-purple-500/30 rounded-lg">
                                        <p class="text-purple-300 text-sm">
                                            <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                            </svg>
                                            This community is part of DaggerHeart's {{ $communityData['playtest']['label'] ?? 'playtest' }} content and may be subject to changes based on community feedback.
                                        </p>
                                    </div>
                                @endif
                                
                                <div class="text-slate-300 text-base leading-relaxed">
                                    @markdown($communityData['description'])
                                </div>
                            </div>

                            <!-- Community Equipment -->
                            @if(isset($communityData['equipment']) && !empty($communityData['equipment']))
                                <div>
                                    <h4 class="text-lg font-bold text-blue-400 font-outfit mb-4">Starting Equipment</h4>
                                    <div class="bg-slate-800/50 rounded-lg p-4">
                                        <p class="text-slate-300 text-sm leading-relaxed">{{ $communityData['equipment'] }}</p>
                                    </div>
                                </div>
                            @endif

                            <!-- Community Environment -->
                            @if(isset($communityData['environment']) && !empty($communityData['environment']))
                                <div>
                                    <h4 class="text-lg font-bold text-blue-400 font-outfit mb-4">Environment</h4>
                                    <div class="bg-slate-800/50 rounded-lg p-4">
                                        <p class="text-slate-300 text-sm leading-relaxed">{{ ucfirst($communityData['environment']) }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>