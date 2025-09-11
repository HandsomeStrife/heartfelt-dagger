<h1 class="font-outfit text-2xl font-bold text-white border-b border-slate-700 pb-3 mb-6 mt-0">{{ $title }}</h1>

<p class="text-slate-300 leading-relaxed mb-6">
    Ancestries represent your character's heritage and provide unique physical and cultural traits that influence gameplay.
</p>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    @foreach($ancestries as $ancestryKey => $ancestry)
        <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-6">
            <!-- Ancestry Header -->
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-orange-500 rounded-lg flex items-center justify-center shadow-lg">
                    <span class="text-white font-bold">{{ strtoupper(substr($ancestry['name'], 0, 1)) }}</span>
                </div>
                <h2 class="font-outfit text-lg font-bold text-amber-400">{{ $ancestry['name'] }}</h2>
            </div>

            <!-- Ancestry Description -->
            <p class="text-slate-300 leading-relaxed mb-4 text-sm">{{ $ancestry['description'] ?? 'No description available.' }}</p>

            <!-- Ancestry Features -->
            @if(isset($ancestry['ancestryFeatures']) && is_array($ancestry['ancestryFeatures']) && count($ancestry['ancestryFeatures']) > 0)
                <div>
                    <h4 class="font-outfit text-sm font-bold text-amber-300 mb-2">Ancestry Features</h4>
                    <div class="space-y-2">
                        @foreach($ancestry['ancestryFeatures'] as $feature)
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

