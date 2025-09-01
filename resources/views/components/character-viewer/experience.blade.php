@if (!empty($character->experiences))
    <div class="mt-6 rounded-3xl border border-slate-800 bg-slate-900/60 p-5 shadow-lg">
        <h2 class="text-lg font-bold">Experience</h2>
        <ul class="mt-2 divide-y divide-slate-800/80">
            @foreach ($character->experiences as $experience)
                <li class="flex items-center justify-between py-2">
                    <span>{{ $experience['name'] }}</span>
                    <div class="flex items-center gap-2">
                        @if($character->hasExperienceBonusSelection() && $character->getClankBonusExperience() === ($experience['name'] ?? null))
                            <span class="px-2 py-1 text-[10px] rounded-md bg-purple-500/20 ring-1 ring-purple-400/30 text-purple-200">Clank Bonus</span>
                        @endif
                        <span class="px-2 py-1 text-xs rounded-md bg-slate-800/80 ring-1 ring-slate-700/60">+{{ $character->getExperienceModifier($experience['name'] ?? '') }}</span>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@endif

