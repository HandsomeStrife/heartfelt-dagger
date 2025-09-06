@props(['participant'])

@php
    $character = $participant->character;
    $characterName = $character?->name ?? $participant->character_name ?? ($participant->user?->username ?? 'Unknown');
    $characterClass = $character?->class ?? $participant->character_class ?? 'Unknown';
    $characterAncestry = $character?->ancestry ?? null;
    $characterCommunity = $character?->community ?? null;
    
    // Get class color for banner (you may want to load this from game data)
    $classColors = [
        'warrior' => 'from-red-600 to-red-800',
        'guardian' => 'from-blue-600 to-blue-800', 
        'ranger' => 'from-green-600 to-green-800',
        'rogue' => 'from-purple-600 to-purple-800',
        'wizard' => 'from-indigo-600 to-indigo-800',
        'sorcerer' => 'from-pink-600 to-pink-800',
        'bard' => 'from-yellow-600 to-yellow-800',
        'druid' => 'from-emerald-600 to-emerald-800',
        'seraph' => 'from-amber-600 to-amber-800',
    ];
    
    $classBanner = $classColors[strtolower($characterClass)] ?? 'from-slate-600 to-slate-800';
@endphp

<div class="bg-slate-800/30 rounded-lg overflow-hidden border border-slate-700/50 hover:border-slate-600/50 transition-colors">
    <!-- Class Banner -->
    <div class="bg-gradient-to-r {{ $classBanner }} p-3">
        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-white font-outfit font-semibold">{{ $characterName }}</h4>
                <p class="text-white/80 text-sm capitalize">{{ $characterClass }}</p>
            </div>
            @if($character)
                <div class="text-white/60 text-xs text-right">
                    @if($characterAncestry)
                        <div class="capitalize">{{ $characterAncestry }}</div>
                    @endif
                    @if($characterCommunity)
                        <div class="capitalize">{{ $characterCommunity }}</div>
                    @endif
                </div>
            @endif
        </div>
    </div>
    
    <!-- Character Details -->
    <div class="p-3 space-y-2">
        @if($character)
            <!-- Character Stats -->
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="bg-slate-700/30 rounded px-2 py-1">
                    <span class="text-slate-400">HP:</span>
                    <span class="text-white font-medium">{{ $character->stats->hit_points ?? 'N/A' }}</span>
                </div>
                <div class="bg-slate-700/30 rounded px-2 py-1">
                    <span class="text-slate-400">Evasion:</span>
                    <span class="text-white font-medium">{{ $character->stats->evasion ?? 'N/A' }}</span>
                </div>
                <div class="bg-slate-700/30 rounded px-2 py-1">
                    <span class="text-slate-400">Hope:</span>
                    <span class="text-white font-medium">{{ $character->stats->hope ?? 2 }}</span>
                </div>
                <div class="bg-slate-700/30 rounded px-2 py-1">
                    <span class="text-slate-400">Stress:</span>
                    <span class="text-white font-medium">{{ $character->stats->stress ?? 0 }}</span>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="flex gap-1 pt-2">
                <button onclick="openCharacterSheet('{{ $character->character_key }}')" 
                        class="flex-1 bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 text-xs py-1 px-2 rounded transition-colors">
                    View Sheet
                </button>
                @if($character->stats->stress > 0)
                    <button class="bg-red-500/20 hover:bg-red-500/30 text-red-300 text-xs py-1 px-2 rounded transition-colors">
                        Stressed
                    </button>
                @endif
            </div>
        @else
            <!-- Temporary Character Info -->
            <div class="text-slate-400 text-sm">
                <p>Temporary character</p>
                <p class="text-xs">Player: {{ $participant->user?->username ?? 'Anonymous' }}</p>
            </div>
        @endif
    </div>
</div>

<script>
function openCharacterSheet(characterKey) {
    // Open character sheet in new tab
    const url = `/character-builder/${characterKey}`;
    window.open(url, '_blank');
}
</script>
