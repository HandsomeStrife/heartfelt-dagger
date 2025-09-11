<h1 class="font-outfit text-2xl font-bold text-white border-b border-slate-700 pb-3 mb-6 mt-0">{{ $title }}</h1>

<p class="text-slate-300 leading-relaxed mb-6">
    Classes define your character's profession and starting capabilities. Each class has unique features, starting statistics, and access to two specific domains.
</p>

<div class="space-y-8">
    @foreach($classes as $classKey => $class)
        <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-6">
            <!-- Class Header -->
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-500 rounded-xl flex items-center justify-center shadow-lg">
                    <span class="text-white font-bold text-lg">{{ strtoupper(substr($class['name'], 0, 1)) }}</span>
                </div>
                <div>
                    <h2 class="font-outfit text-xl font-bold text-amber-400">{{ $class['name'] }}</h2>
                    <div class="flex gap-2 mt-1">
                        @if(isset($class['domains']) && is_array($class['domains']))
                            @foreach($class['domains'] as $domain)
                                <span class="px-2 py-1 bg-slate-700 text-amber-300 text-xs rounded-lg capitalize">{{ $domain }}</span>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <!-- Class Description -->
            <p class="text-slate-300 leading-relaxed mb-6">{{ $class['description'] ?? 'No description available.' }}</p>

            <!-- Starting Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-slate-900/50 border border-slate-700 rounded-lg p-4">
                    <h4 class="font-outfit text-sm font-bold text-amber-200 mb-1">Starting Evasion</h4>
                    <p class="text-white text-lg font-bold">{{ $class['startingEvasion'] ?? 'N/A' }}</p>
                </div>
                <div class="bg-slate-900/50 border border-slate-700 rounded-lg p-4">
                    <h4 class="font-outfit text-sm font-bold text-amber-200 mb-1">Starting Hit Points</h4>
                    <p class="text-white text-lg font-bold">{{ $class['startingHitPoints'] ?? 'N/A' }}</p>
                </div>
                <div class="bg-slate-900/50 border border-slate-700 rounded-lg p-4">
                    <h4 class="font-outfit text-sm font-bold text-amber-200 mb-1">Class Items</h4>
                    <p class="text-slate-300 text-sm">{{ $class['classItems'] ?? 'None specified' }}</p>
                </div>
            </div>

            <!-- Hope Feature -->
            @if(isset($class['hopeFeature']))
                <div class="bg-amber-500/10 border border-amber-500/30 rounded-lg p-4 mb-4">
                    <h4 class="font-outfit text-base font-bold text-amber-300 mb-2">Hope Feature: {{ $class['hopeFeature']['name'] ?? 'Unnamed' }}</h4>
                    <p class="text-amber-100 text-sm mb-2">{{ $class['hopeFeature']['description'] ?? 'No description available.' }}</p>
                    <p class="text-amber-400 text-xs">Hope Cost: {{ $class['hopeFeature']['hopeCost'] ?? 'N/A' }}</p>
                </div>
            @endif

            <!-- Class Features -->
            @if(isset($class['classFeatures']) && is_array($class['classFeatures']) && count($class['classFeatures']) > 0)
                <div class="mb-4">
                    <h4 class="font-outfit text-base font-bold text-amber-300 mb-3">Class Features</h4>
                    <div class="space-y-3">
                        @foreach($class['classFeatures'] as $feature)
                            <div class="bg-slate-900/30 border border-slate-700 rounded-lg p-3">
                                <h5 class="font-outfit text-sm font-bold text-white mb-1">{{ $feature['name'] ?? 'Unnamed Feature' }}</h5>
                                <p class="text-slate-300 text-sm">{{ $feature['description'] ?? 'No description available.' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Subclasses -->
            @if(isset($class['subclasses']) && is_array($class['subclasses']) && count($class['subclasses']) > 0)
                <div>
                    <h4 class="font-outfit text-base font-bold text-amber-300 mb-2">Available Subclasses</h4>
                    <div class="flex flex-wrap gap-2">
                        @foreach($class['subclasses'] as $subclass)
                            <span class="px-3 py-1 bg-slate-700 text-slate-300 text-sm rounded-lg capitalize">{{ $subclass }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endforeach
</div>

