<div class="w-full rounded-3xl border border-slate-800 bg-slate-900/60 p-6 shadow-lg">
    <h2 class="text-lg font-bold">Journal</h2>
    <div class="mt-3 rounded-2xl ring-1 ring-slate-700/60 p-4 bg-slate-950/40 min-h-[8rem]">
        @if ($character->personal_history || $character->motivations || $character->personality_traits)
            <div class="text-sm text-slate-300 space-y-2">
                @if ($character->personality_traits)
                    <div><strong>Personality:</strong> {{ $character->personality_traits }}</div>
                @endif
                @if ($character->personal_history)
                    <div><strong>History:</strong> {{ $character->personal_history }}</div>
                @endif
                @if ($character->motivations)
                    <div><strong>Motivations:</strong> {{ $character->motivations }}</div>
                @endif
            </div>
        @else
            <p class="text-sm text-slate-300">Session notes, quests, bonds, and discoveries go here. (Editable later.)</p>
        @endif
    </div>
</div>

