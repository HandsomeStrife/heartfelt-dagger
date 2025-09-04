@if (!empty($communityData))
    <div pest="community-features-section" class="bg-slate-900/60 border border-slate-800 rounded-xl shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-500/20 to-cyan-500/20 border-b border-blue-500/30 p-4">
            <h2 class="text-xl font-bold text-white font-outfit mb-1">{{ $communityData['name'] ?? 'Community' }} Features</h2>
            @if (isset($communityData['playtest']) && $communityData['playtest']['isPlaytest'])
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-purple-900/50 text-purple-300 border border-purple-600/50">
                        {{ $communityData['playtest']['label'] ?? 'Playtest Content' }}
                    </span>
                </div>
            @endif
            <p class="text-slate-300 text-sm leading-relaxed">{{ $communityData['description'] ?? '' }}</p>
        </div>

        <div class="p-6 space-y-4">
            <!-- Individual Description -->
            @if (!empty($communityData['individualDescription']))
                <div class="bg-slate-800/30 border border-slate-700/50 rounded-lg p-4">
                    <h4 class="text-blue-400 font-semibold text-sm font-outfit mb-2">Typical Traits</h4>
                    <p class="text-slate-300 text-sm leading-relaxed">{{ $communityData['individualDescription'] }}</p>
                </div>
            @endif

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
