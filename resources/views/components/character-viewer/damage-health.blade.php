<div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6 shadow-lg">
    <div class="flex items-center justify-between gap-3 overflow-auto">
        <div class="flex items-center gap-3">
            <h2 class="text-lg font-bold">Damage & Health</h2>
        </div>
        <div class="w-92">
            <x-damage-threshold class="w-full text-zinc-800" :left="$computedStats['major_threshold']" :right="$computedStats['severe_threshold']" />
        </div>
    </div>

    <div class="mt-4">
        <div class="flex items-center gap-2 text-xs text-slate-400">
            <span>HP</span>
            <span class="scale-80" x-text="`${hitPoints.filter(Boolean).length} / ${hitPoints.length} Marked`"></span>
        </div>
        <div class="mt-2 flex flex-wrap gap-1.5">
            <template x-for="(marked, index) in hitPoints" :key="index">
                <label :data-testid="'hp-toggle-' + index">
                    <input type="checkbox" class="sr-only peer" :checked="marked" @change="toggleHitPoint(index)">
                    <span class="block w-11 h-4 rounded-full border border-slate-700 peer-checked:bg-rose-500/85 transition-colors" :class="canEdit ? 'cursor-pointer hover:border-rose-400/50' : ''"></span>
                </label>
            </template>
            <template x-for="(_, index) in Array(Math.max(0, 12 - hitPoints.length)).fill(0)" :key="'hp-future-' + index">
                <span :data-testid="'hp-future-' + index" class="block w-11 h-4 rounded-full border border-dashed border-slate-700/60 bg-transparent"></span>
            </template>
        </div>
    </div>

    <div class="mt-5">
        <div class="flex items-center gap-2 text-xs text-slate-400">
            <span>Stress</span>
            <span class="scale-80" x-text="`${stress.filter(Boolean).length} / ${stress.length} Marked`"></span>
        </div>
        <div class="mt-2 flex flex-wrap gap-1.5">
            <template x-for="(marked, index) in stress" :key="index">
                <label :data-testid="'stress-toggle-' + index">
                    <input type="checkbox" class="sr-only peer" :checked="marked" @change="toggleStress(index)">
                    <span class="block w-11 h-4 rounded-full border border-slate-700 peer-checked:bg-amber-400/80 transition-colors" :class="canEdit ? 'cursor-pointer hover:border-amber-400/50' : ''"></span>
                </label>
            </template>
            <template x-for="(_, index) in Array(Math.max(0, 12 - stress.length)).fill(0)" :key="'stress-future-' + index">
                <span :data-testid="'stress-future-' + index" class="block w-11 h-4 rounded-full border border-dashed border-slate-700/60 bg-transparent"></span>
            </template>
        </div>
    </div>

    <div class="mt-5">
        <div class="text-[11px] uppercase tracking-wide text-slate-400">Armor Slots</div>
        <div class="mt-2 flex gap-2">
            <template x-for="(damaged, index) in armorSlots" :key="index">
                <label :data-testid="'armor-toggle-' + index" class="inline-flex items-center justify-center">
                    <input type="checkbox" class="sr-only peer" :checked="damaged" @change="toggleArmorSlot(index)">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-md ring-1 ring-slate-700 bg-slate-900 peer-checked:bg-emerald-500/20 transition-colors" :class="canEdit ? 'cursor-pointer hover:ring-emerald-400/50' : ''">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 text-slate-400 peer-checked:text-emerald-400" fill="currentColor">
                            <path d="M12 2l7 3v6c0 5-3.5 9-7 11-3.5-2-7-6-7-11V5l7-3z" />
                        </svg>
                    </span>
                </label>
            </template>
            <template x-for="(_, index) in Array(Math.max(0, 12 - armorSlots.length)).fill(0)" :key="'armor-future-' + index">
                <span :data-testid="'armor-future-' + index" class="inline-flex items-center justify-center w-7 h-7 rounded-md ring-1 ring-dashed ring-slate-700/60 bg-transparent"></span>
            </template>
        </div>
    </div>
</div>

