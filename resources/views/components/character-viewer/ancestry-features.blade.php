@if (!empty($ancestryData))
    <div pest="ancestry-features-section" class="bg-slate-900/60 border border-slate-800 rounded-xl shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-green-500/20 to-emerald-500/20 border-b border-green-500/30 p-4">
            <h2 class="text-xl font-bold text-white font-outfit mb-1">{{ $ancestryData['name'] ?? 'Ancestry' }} Features</h2>
            @if (isset($ancestryData['playtest']) && $ancestryData['playtest']['isPlaytest'])
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-purple-900/50 text-purple-300 border border-purple-600/50">
                        {{ $ancestryData['playtest']['label'] ?? 'Playtest Content' }}
                    </span>
                </div>
            @endif
            <p class="text-slate-300 text-sm leading-relaxed">{{ $ancestryData['description'] ?? '' }}</p>
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
