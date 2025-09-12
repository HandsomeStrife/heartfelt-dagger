<div class="space-y-3">
    @if($source === 'classes')
        @foreach(array_slice($data, 0, 3) as $classKey => $classData)
            <div class="bg-slate-700/30 rounded-lg p-3">
                <h5 class="text-white font-medium text-sm mb-2">{{ $classData['name'] ?? $classKey }}</h5>
                <div class="text-slate-300 text-xs space-y-1">
                    @if(isset($classData['startingEvasion']))
                        <div>Evasion: {{ $classData['startingEvasion'] }}</div>
                    @endif
                    @if(isset($classData['startingHitPoints']))
                        <div>Hit Points: {{ $classData['startingHitPoints'] }}</div>
                    @endif
                    @if(isset($classData['domains']))
                        <div>Domains: {{ implode(', ', array_map('ucfirst', $classData['domains'])) }}</div>
                    @endif
                </div>
            </div>
        @endforeach
        @if(count($data) > 3)
            <div class="text-xs text-slate-400 text-center">
                and {{ count($data) - 3 }} more classes...
            </div>
        @endif
        
    @elseif($source === 'weapons')
        @foreach(array_slice($data, 0, 4) as $weaponKey => $weaponData)
            <div class="bg-slate-700/30 rounded-lg p-3">
                <h5 class="text-white font-medium text-sm mb-2">{{ $weaponData['name'] ?? $weaponKey }}</h5>
                <div class="text-slate-300 text-xs space-y-1">
                    @if(isset($weaponData['damage']))
                        <div>Damage: {{ $weaponData['damage']['dice'] ?? '?' }}d{{ $weaponData['damage']['type'] ?? 'phy' }}</div>
                    @endif
                    @if(isset($weaponData['range']))
                        <div>Range: {{ ucfirst($weaponData['range']) }}</div>
                    @endif
                    @if(isset($weaponData['trait']))
                        <div>Trait: {{ ucfirst($weaponData['trait']) }}</div>
                    @endif
                </div>
            </div>
        @endforeach
        @if(count($data) > 4)
            <div class="text-xs text-slate-400 text-center">
                and {{ count($data) - 4 }} more weapons...
            </div>
        @endif
        
    @elseif($source === 'ancestries')
        @foreach(array_slice($data, 0, 3) as $ancestryKey => $ancestryData)
            <div class="bg-slate-700/30 rounded-lg p-3">
                <h5 class="text-white font-medium text-sm mb-2">{{ $ancestryData['name'] ?? $ancestryKey }}</h5>
                <div class="text-slate-300 text-xs">
                    @if(isset($ancestryData['description']))
                        {{ Str::limit($ancestryData['description'], 80) }}
                    @endif
                </div>
            </div>
        @endforeach
        @if(count($data) > 3)
            <div class="text-xs text-slate-400 text-center">
                and {{ count($data) - 3 }} more ancestries...
            </div>
        @endif
        
    @elseif($source === 'domains')
        @foreach(array_slice($data, 0, 3) as $domainKey => $domainData)
            <div class="bg-slate-700/30 rounded-lg p-3">
                <h5 class="text-white font-medium text-sm mb-2">{{ $domainData['name'] ?? ucfirst($domainKey) }}</h5>
                <div class="text-slate-300 text-xs">
                    @if(isset($domainData['description']))
                        {{ Str::limit($domainData['description'], 80) }}
                    @endif
                </div>
            </div>
        @endforeach
        @if(count($data) > 3)
            <div class="text-xs text-slate-400 text-center">
                and {{ count($data) - 3 }} more domains...
            </div>
        @endif
        
    @else
        <!-- Generic display for other JSON data -->
        @foreach(array_slice($data, 0, 3) as $itemKey => $itemData)
            <div class="bg-slate-700/30 rounded-lg p-3">
                <h5 class="text-white font-medium text-sm mb-2">
                    {{ $itemData['name'] ?? $itemData['title'] ?? $itemKey }}
                </h5>
                @if(isset($itemData['description']))
                    <div class="text-slate-300 text-xs">
                        {{ Str::limit($itemData['description'], 80) }}
                    </div>
                @endif
            </div>
        @endforeach
        @if(count($data) > 3)
            <div class="text-xs text-slate-400 text-center">
                and {{ count($data) - 3 }} more items...
            </div>
        @endif
    @endif
    
    <div class="pt-2 border-t border-slate-700/50">
        <a href="{{ route('reference.page', request()->route('page', 'what-is-this')) }}" 
           target="_blank"
           class="inline-flex items-center text-amber-400 hover:text-amber-300 transition-colors text-xs">
            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
            View Full Reference
        </a>
    </div>
</div>
