<div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6 shadow-lg">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold">Hope</h2>
        <span class="text-xs text-slate-400" x-text="`${hope.filter(Boolean).length} / 6`"></span>
    </div>
    <div class="mt-4 flex justify-center gap-3">
        <template x-for="(filled, index) in hope" :key="index">
            <label :data-testid="'hope-toggle-' + index">
                <input type="checkbox" class="sr-only peer" :checked="filled" @change="toggleHope(index)">
                <span class="block w-6 h-6 rotate-45 rounded-sm ring-1 ring-indigo-400/50 bg-slate-900 peer-checked:bg-indigo-500/85 transition-colors" :class="canEdit ? 'cursor-pointer hover:ring-indigo-400' : ''"></span>
            </label>
        </template>
    </div>
    <p class="mt-4 text-sm text-center text-slate-300">Spend a Hope to use an experience or help an ally.</p>
    @if ($classData && isset($classData['hopeFeature']))
        <div class="mt-4 pt-4 border-t border-slate-800/60">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold">{{ $classData['hopeFeature']['name'] ?? $classData['name'] . ' Hope' }}</h3>
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-slate-400">Cost</span>
                    <div class="flex gap-1">
                        @for ($i = 0; $i < 3; $i++)
                            <span class="block w-4 h-4 rotate-45 rounded-sm ring-1 ring-indigo-400/50 bg-slate-900"></span>
                        @endfor
                    </div>
                </div>
            </div>
            <p class="mt-2 text-sm leading-relaxed text-slate-300">{{ $classData['hopeFeature']['description'] ?? 'Hope feature description' }}</p>
        </div>
    @endif
</div>

