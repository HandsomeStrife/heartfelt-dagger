@if (!empty($communityData))
    <div x-data="{ open: false }" pest="community-features-section" class="bg-slate-900/60 border border-slate-800 rounded-xl shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-500/20 to-cyan-500/20 border-b border-blue-500/30 p-4">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <h2 class="text-xl font-bold text-white font-outfit mb-1">{{ $communityData['name'] ?? 'Community' }} Features</h2>
                    @if (isset($communityData['playtest']) && $communityData['playtest']['isPlaytest'])
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-purple-900/50 text-purple-300 border border-purple-600/50">
                                {{ $communityData['playtest']['label'] ?? 'Playtest Content' }}
                            </span>
                        </div>
                    @endif
                </div>
                <button x-on:click="open = true" class="text-blue-400 hover:text-blue-300 text-sm font-medium transition-colors flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Info
                </button>

                <x-slideover x-modelable="show" x-model="open" max-width="xl">
                    <x-slot name="header">
                        <div>
                            <h2 class="text-xl font-outfit font-bold text-white">{{ $communityData['name'] ?? 'Community' }}</h2>
                            @if (isset($communityData['playtest']) && $communityData['playtest']['isPlaytest'])
                                <div class="mt-2">
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-purple-900/50 text-purple-300 border border-purple-600/50">
                                        {{ $communityData['playtest']['label'] ?? 'Playtest Content' }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </x-slot>

                    <div class="p-6 space-y-6">
                        <!-- Description -->
                        <div class="bg-slate-800/30 border border-slate-700/50 rounded-lg p-4">
                            <h3 class="text-blue-400 font-semibold text-base font-outfit mb-3">Description</h3>
                            <p class="text-slate-300 text-sm leading-relaxed">{{ $communityData['description'] ?? '' }}</p>
                        </div>

                        <!-- Individual Description -->
                        @if (!empty($communityData['individualDescription']))
                            <div class="bg-slate-800/30 border border-slate-700/50 rounded-lg p-4">
                                <h3 class="text-blue-400 font-semibold text-base font-outfit mb-3">Typical Traits</h3>
                                <p class="text-slate-300 text-sm leading-relaxed">{{ $communityData['individualDescription'] }}</p>
                            </div>
                        @endif

                        <!-- Community Feature -->
                        @if (!empty($communityData['communityFeature']))
                            <div>
                                <h3 class="text-blue-400 font-semibold text-base font-outfit mb-3">Community Feature</h3>
                                <div class="bg-gradient-to-r from-blue-500/10 to-cyan-500/10 border border-blue-500/30 rounded-lg p-4">
                                    <h4 class="text-white font-semibold text-base font-outfit mb-2">{{ $communityData['communityFeature']['name'] ?? 'Community Feature' }}</h4>
                                    <div class="text-slate-300 text-sm leading-relaxed">{{ $communityData['communityFeature']['description'] ?? '' }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </x-slideover>
            </div>
        </div>

        <div class="p-6">
            <!-- Community Feature -->
            @if (!empty($communityData['communityFeature']))
                <div pest="community-feature" class="bg-gradient-to-r from-blue-500/10 to-cyan-500/10 border border-blue-500/30 rounded-lg p-4">
                    <h4 class="text-white font-semibold text-base font-outfit mb-2">{{ $communityData['communityFeature']['name'] ?? 'Community Feature' }}</h4>
                    <div class="text-slate-300 text-sm leading-relaxed">
                        {{ $communityData['communityFeature']['description'] ?? '' }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endif
