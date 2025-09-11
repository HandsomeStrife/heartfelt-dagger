<h1 class="font-outfit text-2xl font-bold text-white border-b border-slate-700 pb-3 mb-6 mt-0">{{ $title }}</h1>

<p class="text-slate-300 leading-relaxed mb-6">
    Weapons are the tools of combat in DaggerHeart. Each weapon has unique properties that determine how it can be used in battle.
</p>

<div class="space-y-6">
    @foreach($weapons as $weaponKey => $weapon)
        <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-6">
            <!-- Weapon Header -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-orange-500 rounded-lg flex items-center justify-center shadow-lg">
                        <span class="text-white font-bold">{{ strtoupper(substr($weapon['name'], 0, 1)) }}</span>
                    </div>
                    <div>
                        <h2 class="font-outfit text-lg font-bold text-amber-400">{{ $weapon['name'] }}</h2>
                        @if(isset($weapon['trait']))
                            <p class="text-slate-400 text-sm">{{ ucfirst($weapon['trait']) }} weapon</p>
                        @endif
                    </div>
                </div>
                @if(isset($weapon['tier']))
                    <span class="px-3 py-1 bg-slate-700 text-amber-300 text-sm rounded-lg">Tier {{ $weapon['tier'] }}</span>
                @endif
            </div>

            <!-- Weapon Properties -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                @if(isset($weapon['range']))
                    <div class="bg-slate-900/50 border border-slate-700 rounded p-3">
                        <h4 class="font-outfit text-xs font-bold text-amber-200 mb-1">Range</h4>
                        <p class="text-white text-sm">{{ ucfirst($weapon['range']) }}</p>
                    </div>
                @endif

                @if(isset($weapon['damage']))
                    <div class="bg-slate-900/50 border border-slate-700 rounded p-3">
                        <h4 class="font-outfit text-xs font-bold text-amber-200 mb-1">Damage</h4>
                        <p class="text-white text-sm">{{ $weapon['damage'] }}</p>
                    </div>
                @endif

                @if(isset($weapon['burden']))
                    <div class="bg-slate-900/50 border border-slate-700 rounded p-3">
                        <h4 class="font-outfit text-xs font-bold text-amber-200 mb-1">Burden</h4>
                        <p class="text-white text-sm">{{ $weapon['burden'] }}</p>
                    </div>
                @endif

                @if(isset($weapon['goldCost']))
                    <div class="bg-slate-900/50 border border-slate-700 rounded p-3">
                        <h4 class="font-outfit text-xs font-bold text-amber-200 mb-1">Cost</h4>
                        <p class="text-white text-sm">{{ $weapon['goldCost'] }} gold</p>
                    </div>
                @endif
            </div>

            <!-- Description -->
            @if(isset($weapon['description']))
                <p class="text-slate-300 leading-relaxed mb-4 text-sm">{{ $weapon['description'] }}</p>
            @endif

            <!-- Features -->
            @if(isset($weapon['features']) && is_array($weapon['features']) && count($weapon['features']) > 0)
                <div>
                    <h4 class="font-outfit text-sm font-bold text-amber-300 mb-2">Features</h4>
                    <div class="space-y-2">
                        @foreach($weapon['features'] as $feature)
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

