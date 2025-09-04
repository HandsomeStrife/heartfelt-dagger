@if (!empty($ancestryData))
    <div x-data="{ open: false }" pest="ancestry-features-section" class="bg-slate-900/60 border border-slate-800 rounded-xl shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-green-500/20 to-emerald-500/20 border-b border-green-500/30 p-4">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <h2 class="text-xl font-bold text-white font-outfit mb-1">{{ $ancestryData['name'] ?? 'Ancestry' }} Features</h2>
                    @if (isset($ancestryData['playtest']) && $ancestryData['playtest']['isPlaytest'])
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-purple-900/50 text-purple-300 border border-purple-600/50">
                                {{ $ancestryData['playtest']['label'] ?? 'Playtest Content' }}
                            </span>
                        </div>
                    @endif
                </div>
                <button x-on:click="open = true" class="text-green-400 hover:text-green-300 text-sm font-medium transition-colors flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Info
                </button>

                <x-slideover x-modelable="show" x-model="open" max-width="xl">
                    <x-slot name="header">
                        <div>
                            <h2 class="text-xl font-outfit font-bold text-white">{{ $ancestryData['name'] ?? 'Ancestry' }}</h2>
                            @if (isset($ancestryData['playtest']) && $ancestryData['playtest']['isPlaytest'])
                                <div class="mt-2">
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-purple-900/50 text-purple-300 border border-purple-600/50">
                                        {{ $ancestryData['playtest']['label'] ?? 'Playtest Content' }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </x-slot>

                    <div class="p-6 space-y-6">
                        <!-- Description -->
                        <div class="bg-slate-800/30 border border-slate-700/50 rounded-lg p-4">
                            <h3 class="text-green-400 font-semibold text-base font-outfit mb-3">Description</h3>
                            <p class="text-slate-300 text-sm leading-relaxed">{{ $ancestryData['description'] ?? '' }}</p>
                        </div>

                        <!-- Features -->
                        @if (!empty($ancestryData['features']))
                            <div>
                                <h3 class="text-green-400 font-semibold text-base font-outfit mb-3">Features</h3>
                                <div class="space-y-4">
                                    @foreach ($ancestryData['features'] as $feature)
                                        <div class="bg-gradient-to-r from-green-500/10 to-emerald-500/10 border border-green-500/30 rounded-lg p-4">
                                            <h4 class="text-white font-semibold text-base font-outfit mb-2">{{ $feature['name'] ?? '' }}</h4>
                                            <div class="text-slate-300 text-sm leading-relaxed">{{ $feature['description'] ?? '' }}</div>
                                            @if (!empty($feature['effects']))
                                                <div class="mt-2 text-xs text-green-400">
                                                    @foreach ($feature['effects'] as $effect)
                                                        <div>{{ $effect['description'] ?? '' }}</div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </x-slideover>
            </div>
        </div>

        <div class="p-6">
            <!-- Ancestry Features -->
            @if (!empty($ancestryData['features']))
                <div class="space-y-4">
                    @foreach ($ancestryData['features'] as $feature)
                        <div pest="ancestry-feature-{{ $loop->index }}" class="bg-gradient-to-r from-green-500/10 to-emerald-500/10 border border-green-500/30 rounded-lg p-4">
                            <h4 class="text-white font-semibold text-base font-outfit mb-2">{{ $feature['name'] ?? '' }}</h4>
                            <div class="text-slate-300 text-sm leading-relaxed">
                                {{ $feature['description'] ?? '' }}
                            </div>
                            @if (!empty($feature['effects']))
                                <div class="mt-2 text-xs text-green-400">
                                    @foreach ($feature['effects'] as $effect)
                                        <div>{{ $effect['description'] ?? '' }}</div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endif
