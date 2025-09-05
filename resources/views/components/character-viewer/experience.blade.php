@if ($character->experiences->isNotEmpty())
    <div pest="experience-section" class="mt-6 rounded-3xl border border-slate-800 bg-slate-900/60 p-5 shadow-lg">
        <h2 class="text-lg font-bold">Experience</h2>
        <ul pest="experience-list" class="mt-2 divide-y divide-slate-800/80">
            @foreach ($character->experiences as $index => $experience)
                <li pest="experience-item-{{ $index }}" class="flex items-center justify-between py-2">
                    <span pest="experience-name">
                        {{ $experience->name }}
                    </span>
                    <div class="flex items-center gap-2">
                        @if($character->ancestry === 'clank' && $experience->is_clank_bonus)
                            <span pest="clank-bonus-badge" class="px-2 py-1 text-[10px] rounded-md bg-purple-500/20 ring-1 ring-purple-400/30 text-purple-200">Clank Bonus</span>
                        @endif
                        <span pest="experience-modifier" class="px-2 py-1 text-xs rounded-md bg-slate-800/80 ring-1 ring-slate-700/60">
                            +{{ $experience->modifier }}
                        </span>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@endif

