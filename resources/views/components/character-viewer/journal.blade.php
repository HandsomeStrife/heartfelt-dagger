<div class="w-full rounded-3xl border border-slate-800 bg-slate-900/60 p-6 shadow-lg">
    <h2 class="text-lg font-bold">Journal</h2>
    <div class="mt-3 rounded-2xl ring-1 ring-slate-700/60 p-4 bg-slate-950/40 min-h-[8rem]">
        @if ($character->background->backstory || $character->background->motivations || $character->background->personality)
            <div class="text-sm text-slate-300 space-y-2">
                @if ($character->background->personality)
                    <div><strong>Personality:</strong> {{ $character->background->personality }}</div>
                @endif
                @if ($character->background->backstory)
                    <div><strong>History:</strong> {{ $character->background->backstory }}</div>
                @endif
                @if ($character->background->motivations)
                    <div><strong>Motivations:</strong> {{ $character->background->motivations }}</div>
                @endif
            </div>
        @else
            <p class="text-sm text-slate-300">Session notes, quests, bonds, and discoveries go here. (Editable later.)</p>
        @endif
    </div>
</div>

