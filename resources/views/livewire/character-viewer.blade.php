<div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="font-outfit text-4xl text-white tracking-wide mb-2">
                {{ $character->name ?: 'Unnamed Character' }}
            </h1>
            <p class="font-roboto text-slate-300 text-lg">
                {{ ucfirst($character->selected_class ?? 'Unknown Class') }}
                @if($character->selected_subclass)
                    - {{ ucwords(str_replace('-', ' ', $character->selected_subclass)) }}
                @endif
            </p>
        </div>

        <!-- Character Sheet -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Character Portrait & Basic Info -->
            <div class="lg:col-span-1">
                <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                    @if($character->profile_image_path)
                        <div class="mb-6">
                            <img src="{{ $character->getProfileImage() }}" 
                                 alt="Character Portrait" 
                                 class="w-full h-64 object-cover rounded-xl">
                        </div>
                    @endif

                    <div class="space-y-4">
                        <div class="text-center border-b border-slate-700/50 pb-4">
                            <h2 class="text-2xl font-bold text-white font-outfit">{{ $character->name ?: 'Unnamed' }}</h2>
                            <p class="text-slate-300">Level 1 {{ ucfirst($character->selected_class ?? 'Unknown Class') }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-slate-400">Ancestry:</span>
                                <span class="text-white block font-medium">{{ ucfirst($character->selected_ancestry ?? 'Unknown') }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400">Community:</span>
                                <span class="text-white block font-medium">{{ ucfirst($character->selected_community ?? 'Unknown') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Character Details -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Traits -->
                @if(!empty($character->assigned_traits))
                    <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                        <h3 class="text-xl font-bold text-white mb-4 font-outfit">Traits</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach($character->assigned_traits as $trait => $value)
                                <div class="bg-slate-800/50 rounded-lg p-4 text-center">
                                    <div class="text-slate-300 text-sm mb-1">{{ ucfirst($trait) }}</div>
                                    <div class="text-2xl font-bold text-white">{{ $value > 0 ? '+' : '' }}{{ $value }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Equipment -->
                @if(!empty($character->selected_equipment))
                    <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                        <h3 class="text-xl font-bold text-white mb-4 font-outfit">Equipment</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($character->selected_equipment as $equipment)
                                <div class="bg-slate-800/50 rounded-lg p-4">
                                    <div class="text-white font-medium">{{ $equipment['data']['name'] ?? ucwords(str_replace('-', ' ', $equipment['key'])) }}</div>
                                    <div class="text-slate-400 text-sm">{{ ucfirst($equipment['type']) }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Experiences -->
                @if(!empty($character->experiences))
                    <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                        <h3 class="text-xl font-bold text-white mb-4 font-outfit">Experiences</h3>
                        <div class="space-y-3">
                            @foreach($character->experiences as $experience)
                                <div class="bg-slate-800/50 rounded-lg p-4">
                                    <div class="text-white font-medium">{{ $experience['name'] }}</div>
                                    @if(!empty($experience['description']))
                                        <div class="text-slate-300 text-sm mt-1">{{ $experience['description'] }}</div>
                                    @endif
                                    <div class="text-amber-400 text-sm mt-2">+{{ $experience['modifier'] ?? 2 }} to related rolls</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Domain Cards -->
                @if(!empty($character->selected_domain_cards))
                    <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                        <h3 class="text-xl font-bold text-white mb-4 font-outfit">Domain Cards</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($character->selected_domain_cards as $card)
                                <div class="bg-slate-800/50 rounded-lg p-4">
                                    <div class="text-white font-medium">{{ ucwords(str_replace('-', ' ', $card['ability_key'])) }}</div>
                                    <div class="text-slate-400 text-sm">{{ ucfirst($card['domain']) }} Domain</div>
                                    <div class="text-amber-400 text-sm">Level {{ $card['ability_level'] ?? 1 }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-8 text-center">
            <a href="{{ route('character-builder.edit', $character->character_key ?? 'new') }}" 
               class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-black font-semibold rounded-xl transition-all duration-300 shadow-lg hover:shadow-amber-500/25">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Character
            </a>
        </div>
    </div>
</div>
