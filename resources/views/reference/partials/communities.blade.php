<h1 class="font-outfit text-2xl font-bold text-white border-b border-slate-700 pb-3 mb-6 mt-0">{{ $title }}</h1>

<p class="text-slate-300 leading-relaxed mb-6">
    Communities represent where your character grew up and what cultural influences shaped their worldview and skills.
</p>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    @foreach($communities as $communityKey => $community)
        <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-6">
            <!-- Community Header -->
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-orange-500 rounded-lg flex items-center justify-center shadow-lg">
                    <span class="text-white font-bold">{{ strtoupper(substr($community['name'], 0, 1)) }}</span>
                </div>
                <h2 class="font-outfit text-lg font-bold text-amber-400">{{ $community['name'] }}</h2>
            </div>

            <!-- Community Description -->
            <p class="text-slate-300 leading-relaxed mb-4 text-sm">{{ $community['description'] ?? 'No description available.' }}</p>

            <!-- Community Features -->
            @if(isset($community['communityFeatures']) && is_array($community['communityFeatures']) && count($community['communityFeatures']) > 0)
                <div>
                    <h4 class="font-outfit text-sm font-bold text-amber-300 mb-2">Community Features</h4>
                    <div class="space-y-2">
                        @foreach($community['communityFeatures'] as $feature)
                            <div class="bg-slate-900/30 border border-slate-700 rounded p-3">
                                <h5 class="font-outfit text-xs font-bold text-white mb-1">{{ $feature['name'] ?? 'Unnamed Feature' }}</h5>
                                <p class="text-slate-300 text-xs">{{ $feature['description'] ?? 'No description available.' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endforeach
</div>

