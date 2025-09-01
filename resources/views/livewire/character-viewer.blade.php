<div x-data="{
    canEdit: @js($can_edit),
    characterKey: @js($character_key),
    hitPoints: Array(@js($computed_stats['final_hit_points'] ?? 6)).fill(false),
    stress: Array(@js($computed_stats['stress'] ?? 6)).fill(false),
    hope: [true, true, false, false, false, false],
    // Armor: allow marking only up to Armor Score; still show 12 total with placeholders
    armorSlots: Array(Math.max(1, @js($computed_stats['armor_score'] ?? 0))).fill(false),
    goldHandfuls: Array(9).fill(false),
    goldBags: Array(9).fill(false),
    goldChest: false,

    init() {
        if (!@json(auth()->check())) {
            const storedKeys = JSON.parse(localStorage.getItem('daggerheart_characters') || '[]');
            this.canEdit = storedKeys.includes(this.characterKey);
        }

        // Anonymous lock marker for tests (and hard-disable controls when locked)
        if (!this.canEdit) {
            document.body.dataset.anonLocked = '1';
        } else {
            delete document.body.dataset.anonLocked;
        }

        this.loadCharacterState();
    },

    toggleHitPoint(index) {
        if (!this.canEdit) return;
        this.hitPoints[index] = !this.hitPoints[index];
        this.saveCharacterState();
    },

    toggleStress(index) {
        if (!this.canEdit) return;
        this.stress[index] = !this.stress[index];
        this.saveCharacterState();
    },

    toggleHope(index) {
        if (!this.canEdit) return;
        this.hope[index] = !this.hope[index];
        this.saveCharacterState();
    },

    toggleArmorSlot(index) {
        if (!this.canEdit) return;
        this.armorSlots[index] = !this.armorSlots[index];
        this.saveCharacterState();
    },

    toggleGoldHandfuls(index) {
        if (!this.canEdit) return;
        this.goldHandfuls[index] = !this.goldHandfuls[index];
        this.saveCharacterState();
    },

    toggleGoldBags(index) {
        if (!this.canEdit) return;
        this.goldBags[index] = !this.goldBags[index];
        this.saveCharacterState();
    },

    toggleGoldChest() {
        if (!this.canEdit) return;
        this.goldChest = !this.goldChest;
        this.saveCharacterState();
    },

    isValidState(state) {
        if (!state || typeof state !== 'object') return false;
        const keys = ['hitPoints', 'stress', 'hope', 'goldHandfuls', 'goldBags', 'goldChest', 'armorSlots'];
        return keys.some(k => Object.prototype.hasOwnProperty.call(state, k));
    },

    async saveCharacterState() {
        const state = {
            hitPoints: this.hitPoints,
            stress: this.stress,
            hope: this.hope,
            armorSlots: this.armorSlots,
            goldHandfuls: this.goldHandfuls,
            goldBags: this.goldBags,
            goldChest: this.goldChest,
        };

        // Save to localStorage for immediate persistence
        localStorage.setItem(`character_state_` + this.characterKey, JSON.stringify(state));

        // Save to database via Livewire (for authenticated users)
        if (@json(auth()->check())) {
            try {
                await $wire.saveCharacterState(state);
            } finally {
                window.__saveSeq = (window.__saveSeq || 0) + 1;
            }
        } else {
            // For tests, bump sequence even for local-only saves
            window.__saveSeq = (window.__saveSeq || 0) + 1;
        }
    },

    async loadCharacterState() {
        let state = null;

        // Try to load from database first (for authenticated users)
        if (@json(auth()->check())) {
            try {
                state = await $wire.getCharacterState();
            } catch (error) {
                console.warn('Failed to load character state from database:', error);
            }
            // Treat empty/invalid DB payloads as missing so we can fall back to localStorage
            if (!this.isValidState(state)) {
                state = null;
            }
        }

        // Fall back to localStorage if no database state
        if (!state) {
            const saved = localStorage.getItem(`character_state_` + this.characterKey);
            if (saved) {
                state = JSON.parse(saved);
            }
        }

        if (this.isValidState(state)) {
            // Ensure goldBags has the correct length (9)
            if (state.goldBags && state.goldBags.length !== 9) {
                const currentBags = state.goldBags.slice(); // Copy existing state
                state.goldBags = Array(9).fill(false);
                // Preserve existing bag states up to the available length
                for (let i = 0; i < Math.min(currentBags.length, 9); i++) {
                    state.goldBags[i] = currentBags[i];
                }
            }

            // Ensure goldHandfuls has the correct length (9)
            if (state.goldHandfuls && state.goldHandfuls.length !== 9) {
                const currentHandfuls = state.goldHandfuls.slice(); // Copy existing state
                state.goldHandfuls = Array(9).fill(false);
                // Preserve existing handful states up to the available length
                for (let i = 0; i < Math.min(currentHandfuls.length, 9); i++) {
                    state.goldHandfuls[i] = currentHandfuls[i];
                }
            }

            // Ensure armorSlots length matches allowed Armor Score
            const desiredArmorLen = Math.max(1, @js($computed_stats['armor_score'] ?? 0));
            if (!Array.isArray(state.armorSlots)) {
                state.armorSlots = Array(desiredArmorLen).fill(false);
            } else if (state.armorSlots.length !== desiredArmorLen) {
                const currentArmor = state.armorSlots.slice();
                state.armorSlots = Array(desiredArmorLen).fill(false);
                for (let i = 0; i < Math.min(currentArmor.length, desiredArmorLen); i++) {
                    state.armorSlots[i] = currentArmor[i];
                }
            }

            Object.assign(this, state);
        }
        // Mark hydration complete for deterministic tests
        document.body.dataset.hydrated = '1';
    }
}" class="bg-slate-950 text-slate-100/95 antialiased min-h-screen"
    style="font-family: Inter, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial, 'Apple Color Emoji', 'Segoe UI Emoji';">

    <main class="max-w-7xl mx-auto p-6 md:p-8 space-y-6">

        <!-- TOP BANNER -->
        <header
            class="rounded-3xl border border-slate-800 bg-gradient-to-br from-slate-900 via-indigo-900/40 to-fuchsia-900/30 px-6 md:px-8 py-6 shadow-lg relative">
            <x-class-banner :class-name="$character->selected_class" size="sm" class="absolute top-0 right-6" />
            <!-- Level badge (absolute near banner) -->
            <div class="absolute top-4 right-6 translate-y-full sm:translate-y-0 sm:top-3">
                <div class="rounded-3xl ring-1 ring-indigo-400/40 bg-indigo-500/10 px-4 py-3 min-w-[8.5rem]">
                    <div class="text-[10px] uppercase tracking-wider text-indigo-200/90">Level</div>
                    <div class="text-3xl font-extrabold text-indigo-200 leading-none">1</div>
                </div>
            </div>
            <div class="grid grid-cols-12 gap-6 items-start">
                <!-- Row 1: Image, text, level -->
                <!-- Portrait -->
                <div class="col-span-12 sm:col-span-2 flex sm:block justify-center">
                    <div>
                        <div aria-hidden
                            class="aspect-square w-32 sm:w-32 md:w-40 rounded-2xl ring-1 ring-slate-700/60 overflow-hidden bg-slate-800/40">
                            @if ($character->profile_image_path)
                                <img src="{{ Storage::disk('s3')->url($character->profile_image_path) }}"
                                    alt="{{ $character->name }}" class="h-full w-full object-cover">
                            @else
                                <div
                                    class="h-full w-full bg-gradient-to-br from-slate-700 via-indigo-700 to-fuchsia-700">
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Name, pills and bottom-aligned badges -->
                <div class="col-span-12 sm:col-span-10 flex flex-col h-full">
                    <div class="space-y-3">
                        <div class="flex items-center justify-center sm:justify-start gap-2">
                            <h1
                                class="text-2xl md:text-3xl font-extrabold tracking-tight leading-tight text-center sm:text-left">
                                {{ $character->name ?: 'Unnamed Character' }}
                                <span class="text-xs text-slate-400 font-light ml-1">{{ $pronouns }}</span>
                            </h1>
                            @if ($can_edit)
                                <a x-show="canEdit" :href="`/character-builder/${characterKey}`"
                                    aria-label="Edit character"
                                    class="inline-flex items-center p-1.5 rounded-md ring-1 ring-indigo-400/40 hover:bg-indigo-500/20 text-indigo-200">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536M4 20h4l10.607-10.607a2.5 2.5 0 10-3.536-3.536L4 16.464V20z" />
                                    </svg>
                                </a>
                            @endif
                        </div>
                        <div class="mt-2 flex flex-wrap items-center gap-2 justify-center sm:justify-start">
                            <span
                                class="inline-flex items-center gap-1 text-xs md:text-sm font-semibold px-3 py-1 rounded-full ring-1 ring-indigo-400/40 bg-indigo-500/15 text-indigo-200">
                                {{ $community_data['name'] ?? ucfirst($character->selected_community ?? 'Unknown') }}
                                {{ $ancestry_data['name'] ?? ucfirst($character->selected_ancestry ?? 'Unknown') }} •
                                {{ $class_data['name'] ?? ucfirst($character->selected_class ?? 'Unknown') }}
                                @if ($character->selected_subclass && $subclass_data)
                                    <span
                                        class="opacity-70">({{ $subclass_data['name'] ?? ucwords(str_replace('-', ' ', $character->selected_subclass)) }})</span>
                                @endif
                            </span>
                            @if ($class_data && isset($class_data['domains']))
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold ring-1 ring-slate-700/60 bg-slate-800/60">{{ ucfirst($class_data['domains'][0] ?? '') }}
                                    & {{ ucfirst($class_data['domains'][1] ?? '') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="mt-auto pt-3">
                        <div class="flex items-center gap-3 flex-nowrap overflow-x-auto">
                            <div class="flex gap-2">
                                <x-icons.evasion-frame :number="$computed_stats['evasion'] ?? '?'" class="size-20" />
                                <x-icons.armor-frame :number="$computed_stats['armor_score'] ?? '?'" class="size-20" />
                            </div>
                            <span class="text-slate-500/80 select-none">|</span>
                            @if (!empty($character->assigned_traits))
                                <div class="flex items-center gap-1 flex-nowrap">
                                    @foreach ($this->getTraitInfo() as $trait => $label)
                                        <x-icons.stat-frame :number="$this->getFormattedTraitValue($trait) ?? '?'" :label="$label" class="size-20" />
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>



            </div>
        </header>

        <!-- MAIN: Left = Damage & Health, Right = Hope + Gold -->
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left: DAMAGE & HEALTH -->
            <div class="lg:col-span-7">
                <div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6 shadow-lg">
                    <div class="flex items-center justify-between gap-3 overflow-auto">
                        <div class="flex items-center gap-3">
                            <h2 class="text-lg font-bold">Damage & Health</h2>
                        </div>
                        <div class="w-92">
                            <x-damage-threshold class="w-full text-zinc-800" :left="$computed_stats['major_threshold']" :right="$computed_stats['severe_threshold']" />
                        </div>
                    </div>

                    <!-- HP row -->
                    <div class="mt-4">
                        <div class="flex items-center gap-2 text-xs text-slate-400">
                            <span>HP</span>
                            <span class="scale-80" x-text="`${hitPoints.filter(Boolean).length} / ${hitPoints.length} Marked`"></span>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            <template x-for="(marked, index) in hitPoints" :key="index">
                                <label :data-testid="'hp-toggle-' + index">
                                    <input type="checkbox" class="sr-only peer" :checked="marked"
                                        @change="toggleHitPoint(index)">
                                    <span
                                        class="block w-11 h-4 rounded-full border border-slate-700 peer-checked:bg-rose-500/85 transition-colors"
                                        :class="canEdit ? 'cursor-pointer hover:border-rose-400/50' : ''"></span>
                                </label>
                            </template>
                            <!-- Future HP slots up to 12 (non-interactive) -->
                            <template x-for="(_, index) in Array(Math.max(0, 12 - hitPoints.length)).fill(0)" :key="'hp-future-' + index">
                                <span :data-testid="'hp-future-' + index"
                                    class="block w-11 h-4 rounded-full border border-dashed border-slate-700/60 bg-transparent"></span>
                            </template>
                        </div>
                    </div>

                    <!-- STRESS row -->
                    <div class="mt-5">
                        <div class="flex items-center gap-2 text-xs text-slate-400">
                            <span>Stress</span>
                            <span class="scale-80" x-text="`${stress.filter(Boolean).length} / ${stress.length} Marked`"></span>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            <template x-for="(marked, index) in stress" :key="index">
                                <label :data-testid="'stress-toggle-' + index">
                                    <input type="checkbox" class="sr-only peer" :checked="marked"
                                        @change="toggleStress(index)">
                                    <span
                                        class="block w-11 h-4 rounded-full border border-slate-700 peer-checked:bg-amber-400/80 transition-colors"
                                        :class="canEdit ? 'cursor-pointer hover:border-amber-400/50' : ''"></span>
                                </label>
                            </template>
                            <!-- Future Stress slots up to 12 (non-interactive) -->
                            <template x-for="(_, index) in Array(Math.max(0, 12 - stress.length)).fill(0)" :key="'stress-future-' + index">
                                <span :data-testid="'stress-future-' + index"
                                    class="block w-11 h-4 rounded-full border border-dashed border-slate-700/60 bg-transparent"></span>
                            </template>
                        </div>
                    </div>

                    <!-- ARMOR ICON SLOTS under Stress -->
                    <div class="mt-5">
                        <div class="text-[11px] uppercase tracking-wide text-slate-400">Armor Slots</div>
                        <div class="mt-2 flex gap-2">
                            <template x-for="(damaged, index) in armorSlots" :key="index">
                                <label :data-testid="'armor-toggle-' + index" class="inline-flex items-center justify-center">
                                    <input type="checkbox" class="sr-only peer" :checked="damaged"
                                        @change="toggleArmorSlot(index)">
                                    <span
                                        class="inline-flex items-center justify-center w-7 h-7 rounded-md ring-1 ring-slate-700 bg-slate-900 peer-checked:bg-emerald-500/20 transition-colors"
                                        :class="canEdit ? 'cursor-pointer hover:ring-emerald-400/50' : ''">
                                        <svg viewBox="0 0 24 24"
                                            class="w-4 h-4 text-slate-400 peer-checked:text-emerald-400"
                                            fill="currentColor">
                                            <path d="M12 2l7 3v6c0 5-3.5 9-7 11-3.5-2-7-6-7-11V5l7-3z" />
                                        </svg>
                                    </span>
                                </label>
                            </template>
                            <!-- Future Armor slots up to 12 (non-interactive) -->
                            <template x-for="(_, index) in Array(Math.max(0, 12 - armorSlots.length)).fill(0)" :key="'armor-future-' + index">
                                <span :data-testid="'armor-future-' + index"
                                    class="inline-flex items-center justify-center w-7 h-7 rounded-md ring-1 ring-dashed ring-slate-700/60 bg-transparent"></span>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-7 rounded-3xl border border-slate-800 bg-slate-900/60 p-6 shadow-lg mt-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold">Active Weapons</h2>
                        <div class="flex items-center gap-1 text-indigo-200" aria-label="Proficiency">
                            <span class="text-xs mr-2 text-slate-400">Proficiency</span>
                            @for ($i = 0; $i < 6; $i++)
                                <span
                                    class="block w-4 h-4 rounded-full ring-1 ring-indigo-400/60 {{ $i < 1 ? 'bg-indigo-500/70' : 'bg-slate-800' }}"></span>
                            @endfor
                        </div>
                    </div>
    
                    <!-- Primary Weapon -->
                    @if (!empty($organized_equipment['weapons']))
                        @php $primary = collect($organized_equipment['weapons'])->first(fn($w) => ($w['data']['type'] ?? 'Primary') === 'Primary'); @endphp
                        @if ($primary)
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <div class="text-[10px] uppercase tracking-wider text-slate-400">Primary</div>
                                    <div class="text-base font-semibold">{{ $primary['data']['name'] ?? 'Weapon' }}</div>
                                    <div class="text-xs text-slate-400">Trait:
                                        {{ ucfirst($primary['data']['range'] ?? 'Melee') }}</div>
                                </div>
                                <div class="flex items-end gap-3">
                                    <div class="px-3 py-2 rounded-xl ring-1 ring-slate-700/60 bg-slate-800/60">
                                        <div class="text-[10px] uppercase text-slate-400">
                                            {{ ucfirst($primary['data']['trait'] ?? 'Strength') }}</div>
                                        <div class="font-bold">
                                            {{ $character->assigned_traits[$primary['data']['trait'] ?? 'strength'] ?? 0 > 0 ? '+' : '' }}{{ $character->assigned_traits[$primary['data']['trait'] ?? 'strength'] ?? 0 }}
                                        </div>
                                    </div>
                                    <div class="px-3 py-2 rounded-xl ring-1 ring-slate-700/60 bg-slate-800/60">
                                        <div class="text-[10px] uppercase text-slate-400">Damage</div>
                                        <div class="font-bold">
                                            {{ $primary['data']['damage']['dice'] ?? 'd6' }}{{ isset($primary['data']['damage']['modifier']) && $primary['data']['damage']['modifier'] > 0 ? ' + ' . $primary['data']['damage']['modifier'] : '' }}
                                            ({{ $primary['data']['damage']['type'] ?? 'physical' }})</div>
                                    </div>
                                </div>
                                <div class="md:text-right text-sm text-slate-300">
                                    {{ $this->getWeaponFeatureText($primary['data']) }}
                                </div>
                            </div>
                        @else
                            <div
                                class="mt-4 rounded-2xl ring-1 ring-dashed ring-slate-700/60 p-4 text-sm text-slate-400 text-center">
                                Set active in the equipment section</div>
                        @endif
                    @else
                        <div
                            class="mt-4 rounded-2xl ring-1 ring-dashed ring-slate-700/60 p-4 text-sm text-slate-400 text-center">
                            Set active in the equipment section</div>
                    @endif
                </div>
    
                <!-- Active Armor under weapons -->
                <div class="mt-6 rounded-3xl border border-slate-800 bg-slate-900/60 p-6 shadow-lg">
                    <h2 class="text-lg font-bold">Active Armor</h2>
                    @if (!empty($organized_equipment['armor']))
                        @php $armor = $organized_equipment['armor'][0]; @endphp
                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                            <div>
                                <div class="text-[10px] uppercase tracking-wider text-slate-400">Name</div>
                                <div class="font-semibold">
                                    {{ $armor['data']['name'] ?? ucwords(str_replace('-', ' ', $armor['key'])) }}</div>
                            </div>
                            <div>
                                <div class="text-[10px] uppercase tracking-wider text-slate-400">Base Thresholds</div>
                                <div class="font-semibold">
                                    @if (isset($armor['data']['baseThresholds']))
                                        {{ $armor['data']['baseThresholds']['minor'] ?? 1 }} /
                                        {{ $armor['data']['baseThresholds']['major'] ?? 2 }} /
                                        {{ $armor['data']['baseThresholds']['severe'] ?? 3 }}
                                    @else
                                        1 / 2 / 3
                                    @endif
                                </div>
                            </div>
                            <div>
                                <div class="text-[10px] uppercase tracking-wider text-slate-400">Base Score</div>
                                <div class="font-semibold">+{{ $armor['data']['baseScore'] ?? 0 }}</div>
                            </div>
                        </div>
                        @if (isset($armor['data']['features']) && !empty($armor['data']['features']))
                            <p class="mt-3 text-sm text-slate-300">Feature:
                                {{ is_array($armor['data']['features']) ? implode(', ', $armor['data']['features']) : $armor['data']['features'] }}
                            </p>
                        @endif
                    @else
                        <div class="mt-4 text-center text-slate-500 text-sm italic">No armor equipped</div>
                    @endif
                </div>
            </div>

            <!-- Right: HOPE + GOLD -->
            <div class="lg:col-span-5 space-y-6">
                <div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold">Hope</h2>
                        <span class="text-xs text-slate-400" x-text="`${hope.filter(Boolean).length} / 6`"></span>
                    </div>
                    <!-- centered diamonds with small gaps -->
                    <div class="mt-4 flex justify-center gap-3">
                        <template x-for="(filled, index) in hope" :key="index">
                            <label :data-testid="'hope-toggle-' + index">
                                <input type="checkbox" class="sr-only peer" :checked="filled"
                                    @change="toggleHope(index)">
                                <span
                                    class="block w-6 h-6 rotate-45 rounded-sm ring-1 ring-indigo-400/50 bg-slate-900 peer-checked:bg-indigo-500/85 transition-colors"
                                    :class="canEdit ? 'cursor-pointer hover:ring-indigo-400' : ''"></span>
                            </label>
                        </template>
                    </div>
                    <p class="mt-4 text-sm text-center text-slate-300">Spend a Hope to use an experience or help an
                        ally.</p>
                    @if ($class_data && isset($class_data['hopeFeature']))
                        <div class="mt-4 pt-4 border-t border-slate-800/60">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-bold">
                                    {{ $class_data['hopeFeature']['name'] ?? $class_data['name'] . ' Hope' }}</h3>
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="text-slate-400">Cost</span>
                                    <div class="flex gap-1">
                                        @for ($i = 0; $i < 3; $i++)
                                            <span
                                                class="block w-4 h-4 rotate-45 rounded-sm ring-1 ring-indigo-400/50 bg-slate-900"></span>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                            <p class="mt-2 text-sm leading-relaxed text-slate-300">
                                {{ $class_data['hopeFeature']['description'] ?? 'Hope feature description' }}</p>
                        </div>
                    @endif
                </div>

                <div x-show="canEdit" class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold">Gold</h2>
                        <span class="text-xs text-slate-400">Handfuls, Bags, Chest</span>
                    </div>
                    <!-- Single row layout with 9 handfuls, 9 bags in 2 rows, 1 chest -->
                    <div class="mt-4 flex items-center justify-between gap-8">
                        <!-- Handfuls - 9 icons in 2 rows (5 top, 4 bottom) -->
                        <div class="flex flex-col items-center gap-2">
                            <!-- Top row: 5 handfuls -->
                            <div class="flex gap-2">
                                <template x-for="(filled, index) in goldHandfuls.slice(0, 5)" :key="index">
                                    <label :data-testid="'gold-handful-' + index">
                                        <input type="checkbox" class="sr-only peer" :checked="filled"
                                            @change="toggleGoldHandfuls(index)">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                                            xml:space="preserve"
                                            class="w-6 h-6 transition-colors hover:text-yellow-400/70"
                                            :class="[
                                                canEdit ? 'cursor-pointer' : 'cursor-default',
                                                filled ? 'text-yellow-400' : 'text-slate-400'
                                            ]">
                                            <path
                                                d="M508.509 340.945c-24.436-24.436-61.673-29.091-90.764-12.8l-48.873 23.273c-12.8 5.818-27.927 9.309-43.055 10.473 6.982-9.309 11.636-22.109 11.636-34.909s-10.473-23.273-23.273-23.273H193.164c-30.255 0-59.345 12.8-79.127 36.073L73.31 386.327c-5.818-9.309-16.291-13.964-26.764-13.964h-34.91C4.655 372.364 0 377.018 0 384s4.655 11.636 11.636 11.636h34.909c6.982 0 11.636 4.655 11.636 11.636v69.818c0 6.982-4.655 11.636-11.636 11.636H11.636C4.655 488.727 0 493.382 0 500.364S4.655 512 11.636 512h34.909c15.127 0 27.927-9.309 32.582-23.273h64c5.818 0 11.636 0 16.291-1.164l164.073-18.618c26.764-3.491 52.364-12.8 74.473-29.091L507.345 358.4c2.327-2.327 4.655-4.655 4.655-8.145s-1.164-6.982-3.491-9.31zM384 421.236c-18.618 13.964-39.564 22.109-62.836 24.436L157.091 464.29c-4.655 0-9.309 1.164-13.964 1.164H81.455v-53.527l51.2-58.182c15.127-17.455 37.236-27.927 61.673-27.927h119.855c0 19.782-15.127 34.909-34.909 34.909h-69.818c-6.982 0-11.636 4.655-11.636 11.636 0 6.982 4.655 11.636 11.636 11.636H322.329c19.782 0 38.4-4.655 55.855-12.8l50.036-23.273c16.291-9.309 37.236-9.309 53.527 1.164L384 421.236zM244.364 139.636c-38.4 0-69.818 31.418-69.818 69.818s31.418 69.818 69.818 69.818 69.818-31.418 69.818-69.818c0-38.399-31.418-69.818-69.818-69.818zm0 116.364c-25.6 0-46.545-20.945-46.545-46.545s20.945-46.545 46.545-46.545 46.545 20.945 46.545 46.545S269.964 256 244.364 256zM395.636 93.091c-32.582 0-58.182 25.6-58.182 58.182s25.6 58.182 58.182 58.182 58.182-25.6 58.182-58.182-25.6-58.182-58.182-58.182zm0 93.091c-19.782 0-34.909-15.127-34.909-34.909 0-19.782 15.127-34.909 34.909-34.909 19.782 0 34.909 15.127 34.909 34.909 0 19.782-15.127 34.909-34.909 34.909zM279.273 0c-25.6 0-46.545 20.945-46.545 46.545s20.945 46.545 46.545 46.545 46.545-20.945 46.545-46.545S304.873 0 279.273 0zm0 69.818c-12.8 0-23.273-10.473-23.273-23.273s10.473-23.273 23.273-23.273c12.8 0 23.273 10.473 23.273 23.273s-10.473 23.273-23.273 23.273z"
                                                fill="currentColor" :style="filled ? 'opacity: 1' : 'opacity: 0.4'" />
                                        </svg>
                                    </label>
                                </template>
                            </div>
                            <!-- Bottom row: 4 handfuls -->
                            <div class="flex gap-2">
                                <template x-for="(filled, index) in goldHandfuls.slice(5, 9)" :key="index + 5">
                                    <label :data-testid="'gold-handful-' + (index + 5)">
                                        <input type="checkbox" class="sr-only peer" :checked="filled"
                                            @change="toggleGoldHandfuls(index + 5)">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                                            xml:space="preserve"
                                            class="w-6 h-6 transition-colors hover:text-yellow-400/70"
                                            :class="[
                                                canEdit ? 'cursor-pointer' : 'cursor-default',
                                                filled ? 'text-yellow-400' : 'text-slate-400'
                                            ]">
                                            <path
                                                d="M508.509 340.945c-24.436-24.436-61.673-29.091-90.764-12.8l-48.873 23.273c-12.8 5.818-27.927 9.309-43.055 10.473 6.982-9.309 11.636-22.109 11.636-34.909s-10.473-23.273-23.273-23.273H193.164c-30.255 0-59.345 12.8-79.127 36.073L73.31 386.327c-5.818-9.309-16.291-13.964-26.764-13.964h-34.91C4.655 372.364 0 377.018 0 384s4.655 11.636 11.636 11.636h34.909c6.982 0 11.636 4.655 11.636 11.636v69.818c0 6.982-4.655 11.636-11.636 11.636H11.636C4.655 488.727 0 493.382 0 500.364S4.655 512 11.636 512h34.909c15.127 0 27.927-9.309 32.582-23.273h64c5.818 0 11.636 0 16.291-1.164l164.073-18.618c26.764-3.491 52.364-12.8 74.473-29.091L507.345 358.4c2.327-2.327 4.655-4.655 4.655-8.145s-1.164-6.982-3.491-9.31zM384 421.236c-18.618 13.964-39.564 22.109-62.836 24.436L157.091 464.29c-4.655 0-9.309 1.164-13.964 1.164H81.455v-53.527l51.2-58.182c15.127-17.455 37.236-27.927 61.673-27.927h119.855c0 19.782-15.127 34.909-34.909 34.909h-69.818c-6.982 0-11.636 4.655-11.636 11.636 0 6.982 4.655 11.636 11.636 11.636H322.329c19.782 0 38.4-4.655 55.855-12.8l50.036-23.273c16.291-9.309 37.236-9.309 53.527 1.164L384 421.236zM244.364 139.636c-38.4 0-69.818 31.418-69.818 69.818s31.418 69.818 69.818 69.818 69.818-31.418 69.818-69.818c0-38.399-31.418-69.818-69.818-69.818zm0 116.364c-25.6 0-46.545-20.945-46.545-46.545s20.945-46.545 46.545-46.545 46.545 20.945 46.545 46.545S269.964 256 244.364 256zM395.636 93.091c-32.582 0-58.182 25.6-58.182 58.182s25.6 58.182 58.182 58.182 58.182-25.6 58.182-58.182-25.6-58.182-58.182-58.182zm0 93.091c-19.782 0-34.909-15.127-34.909-34.909 0-19.782 15.127-34.909 34.909-34.909 19.782 0 34.909 15.127 34.909 34.909 0 19.782-15.127 34.909-34.909 34.909zM279.273 0c-25.6 0-46.545 20.945-46.545 46.545s20.945 46.545 46.545 46.545 46.545-20.945 46.545-46.545S304.873 0 279.273 0zm0 69.818c-12.8 0-23.273-10.473-23.273-23.273s10.473-23.273 23.273-23.273c12.8 0 23.273 10.473 23.273 23.273s-10.473 23.273-23.273 23.273z"
                                                fill="currentColor" :style="filled ? 'opacity: 1' : 'opacity: 0.4'" />
                                        </svg>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <!-- Bags - 9 bags in 2 rows (5 top, 4 bottom) -->
                        <div class="flex flex-col items-center gap-2">
                            <!-- Top row: 5 bags -->
                            <div class="flex gap-2">
                                <template x-for="(filled, index) in goldBags.slice(0, 5)" :key="index">
                                    <label :data-testid="'gold-bag-' + index">
                                        <input type="checkbox" class="sr-only peer" :checked="filled"
                                            @change="toggleGoldBags(index)">
                                        <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"
                                            class="w-6 h-6 transition-colors hover:text-yellow-400/70"
                                            :class="[
                                                canEdit ? 'cursor-pointer' : 'cursor-default',
                                                filled ? 'text-yellow-400' : 'text-slate-400'
                                            ]">
                                            <path
                                                d="M17.891 9.805h-3.79s-6.17 4.831-6.17 12.108 6.486 7.347 6.486 7.347 1.688.125 3.125 0c0 .062 6.525-.865 6.525-7.353.001-6.486-6.176-12.102-6.176-12.102zm-3.79-.475h3.797V7.906h-3.797V9.33zm3.739-1.898 1.928-4.747s-1.217 1.009-1.928 1.009c-.713 0-1.84-.979-1.84-.979s-1.216.979-1.928.979-1.869-.949-1.869-.949l1.958 4.688h3.679z"
                                                fill="currentColor" :style="filled ? 'opacity: 1' : 'opacity: 0.4'" />
                                        </svg>
                                    </label>
                                </template>
                            </div>
                            <!-- Bottom row: 4 bags -->
                            <div class="flex gap-2">
                                <template x-for="(filled, index) in goldBags.slice(5, 9)" :key="index + 5">
                                    <label :data-testid="'gold-bag-' + (index + 5)">
                                        <input type="checkbox" class="sr-only peer" :checked="filled"
                                            @change="toggleGoldBags(index + 5)">
                                        <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"
                                            class="w-6 h-6 transition-colors hover:text-yellow-400/70"
                                            :class="[
                                                canEdit ? 'cursor-pointer' : 'cursor-default',
                                                filled ? 'text-yellow-400' : 'text-slate-400'
                                            ]">
                                            <path
                                                d="M17.891 9.805h-3.79s-6.17 4.831-6.17 12.108 6.486 7.347 6.486 7.347 1.688.125 3.125 0c0 .062 6.525-.865 6.525-7.353.001-6.486-6.176-12.102-6.176-12.102zm-3.79-.475h3.797V7.906h-3.797V9.33zm3.739-1.898 1.928-4.747s-1.217 1.009-1.928 1.009c-.713 0-1.84-.979-1.84-.979s-1.216.979-1.928.979-1.869-.949-1.869-.949l1.958 4.688h3.679z"
                                                fill="currentColor" :style="filled ? 'opacity: 1' : 'opacity: 0.4'" />
                                        </svg>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <!-- Chest - 1 large chest -->
                        <div class="flex items-center">
                            <label data-testid="gold-chest-toggle">
                                <input type="checkbox" class="sr-only peer" :checked="goldChest"
                                    @change="toggleGoldChest()">
                                <svg version="1.1" id="_x32_" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 512 512" xml:space="preserve"
                                    class="w-12 h-12 transition-colors hover:text-yellow-400/70"
                                    :class="[
                                        canEdit ? 'cursor-pointer' : 'cursor-default',
                                        goldChest ? 'text-yellow-400' : 'text-slate-400'
                                    ]">
                                    <g id="SVGRepo_iconCarrier">
                                        <path class="st0"
                                            d="m250 371.255-3.368 20.676h18.74l-3.368-20.676c3.219-2.012 5.365-5.58 5.365-9.648 0-6.277-5.089-11.37-11.366-11.37-6.28 0-11.373 5.093-11.373 11.37 0 4.069 2.148 7.636 5.37 9.648zM236.109 255.575l27.541 22.783 12.638-4.119-38.807-33.804zM307.585 261.307l12.514 15.017 5.849-8.348-9.184-10.011zM295.9 287.17l-2.507 12.518 31.719-10.847-10.851-8.347zM179.892 288.013l17.527-14.195-17.527-5.842zM218.702 285.5l-4.381 9.393h26.289l-3.129-9.393zM211.665 189.98c2.01-9.408 6.056-19.746 11.973-25.667 5.918-5.914 16.252-9.961 25.653-11.98-9.401-2.005-19.736-6.052-25.657-11.973-5.914-5.914-9.964-16.251-11.973-25.652-2.012 9.401-6.059 19.739-11.98 25.66-5.914 5.914-16.252 9.961-25.652 11.973 9.404 2.012 19.739 6.058 25.656 11.973 5.914 5.92 9.968 16.258 11.98 25.666zM334.291 206.37c1.206-5.652 3.64-11.864 7.192-15.424 3.557-3.552 9.76-5.986 15.413-7.199-5.652-1.206-11.856-3.64-15.413-7.192-3.556-3.553-5.986-9.764-7.196-15.416-1.206 5.652-3.643 11.864-7.195 15.416-3.557 3.56-9.768 5.986-15.42 7.199 5.652 1.206 11.864 3.64 15.42 7.2 3.552 3.551 5.989 9.763 7.199 15.416z"
                                            fill="currentColor" :style="goldChest ? 'opacity: 1' : 'opacity: 0.4'" />
                                        <path class="st0"
                                            d="m412.568 213.874 27.483-132.426-22.383-44.76C406.429 14.203 383.443 0 358.306 0H153.702a66.37 66.37 0 0 0-59.366 36.688l-22.383 44.76 27.483 132.426-56.874 103.853V512h426.875V317.727l-56.869-103.853zm.116 51.16c-4.242.908-8.906 2.732-11.572 5.405-2.674 2.666-4.497 7.33-5.406 11.573-.908-4.243-2.739-8.906-5.405-11.573-2.673-2.674-7.338-4.497-11.573-5.405 4.235-.908 8.9-2.738 11.573-5.405 2.666-2.674 4.49-7.338 5.405-11.573.901 4.235 2.732 8.9 5.406 11.573 2.665 2.667 7.329 4.497 11.572 5.405zm-290.89 37.32 15.024-12.83 9.386-14.436h22.532l3.756-12.823 22.532-16.04 30.041 3.204 3.756-16.034 39.431-8.028 28.162 24.062 18.78-6.415 24.407 22.456 20.658 9.619 29.314 27.265h-25.602l-13.2-13.716-2.369 13.716H121.794zm179.71 30.898v74.132H210.5v-74.132h91.004zm-186.986-86.911c5.18-1.104 10.876-3.342 14.138-6.596 3.258-3.262 5.489-8.95 6.596-14.138 1.108 5.18 3.339 10.876 6.597 14.138 3.262 3.254 8.958 5.485 14.141 6.596-5.183 1.105-10.879 3.342-14.137 6.596-3.258 3.262-5.489 8.958-6.601 14.138-1.108-5.18-3.338-10.876-6.596-14.138-3.259-3.254-8.954-5.491-14.138-6.596zm3.331-197.897a40.094 40.094 0 0 1 35.852-22.159h204.604a40.09 40.09 0 0 1 35.845 22.159l12.714 25.413h-98.292V37.372H203.431v36.485h-98.292l12.71-25.413zm175.704 3.951v21.461h-75.099V52.395h75.099zm117.322 40.241-24.156 116.421H125.285L101.122 92.636h309.753zM68.851 330.477H195.48v40.691H68.851v-40.691zm374.295 155.231H68.851v-34.421h374.296v34.421zm0-49.446H68.851v-50.077H195.48V422.4h121.045v-36.215h126.622v50.077zm0-65.094H316.524v-40.691h126.622v40.691z"
                                            fill="currentColor" :style="goldChest ? 'opacity: 1' : 'opacity: 0.4'" />
                                    </g>
                                </svg>
                            </label>
                        </div>
                    </div>
                </div>

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
            </div>
        </section>

        <!-- FEATURES -->
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-12 grid grid-cols-1 gap-6">
                <!-- Domain Effects as Cards -->
                @if (!empty($domain_card_details))
                    <div class="flex flex-wrap justify-start gap-6">
                        @foreach ($domain_card_details as $card)
                            <div
                                class="bg-slate-900/60 border border-slate-800 rounded-xl shadow-lg overflow-hidden w-[360px] flex flex-col">
                                <!-- Banner -->
                                <div
                                    class="relative min-h-[120px] flex flex-col items-center justify-end bg-slate-900 w-full overflow-hidden">
                                    <div class="absolute -top-1 left-[13.5px] z-40">
                                        <img class="h-[120px] w-[75px]" src="/img/empty-banner.webp">
                                        <div
                                            class="absolute inset-0 flex flex-col items-center justify-center pb-3 gap-1 pt-0.5">
                                            @if (isset($card['ability_data']['level']))
                                                <div
                                                    class="text-2xl leading-[22px] font-bold border-2 border-dashed border-transparent pt-1 px-1 rounded-md">
                                                    <div class="text-white font-black">
                                                        {{ $card['ability_data']['level'] }}</div>
                                                </div>
                                            @endif
                                            <div class="w-9 h-auto aspect-contain">
                                                <x-dynamic-component
                                                    component="icons.{{ $card['ability_data']['domain'] ?? ($card['domain'] ?? 'codex') }}"
                                                    class="fill-white size-8" />
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Cost (diamond indicator) -->
                                    @if (isset($card['ability_data']['recallCost']) && $card['ability_data']['recallCost'] > 0)
                                        <div class="absolute top-3 right-3 flex items-center gap-2 text-xs">
                                            <span class="text-slate-300">Cost</span>
                                            <div class="flex gap-1">
                                                @for ($i = 0; $i < $card['ability_data']['recallCost']; $i++)
                                                    <span
                                                        class="block w-5 h-5 rotate-45 rounded-sm ring-1 ring-indigo-400/50 bg-slate-900"></span>
                                                @endfor
                                            </div>
                                        </div>
                                    @endif
                                    <!-- Title/Type -->
                                    <div class="w-full pl-[100px] pr-4 pb-3">
                                        <h3 class="text-white font-black font-outfit text-lg leading-tight uppercase">
                                            {{ $card['ability_data']['name'] ?? ucwords(str_replace('-', ' ', $card['ability_key'])) }}
                                        </h3>
                                        <div class="text-[10px] font-bold uppercase tracking-wide mt-1 text-slate-300">
                                            {{ $card['ability_data']['type'] ?? 'ability' }}
                                        </div>
                                    </div>
                                </div>
                                <!-- Body -->
                                <div class="px-4 py-4 text-sm text-white flex-1">
                                    @if (isset($card['ability_data']['descriptions']) && is_array($card['ability_data']['descriptions']))
                                        <div class="text-slate-300 space-y-2 leading-relaxed">
                                            @foreach ($card['ability_data']['descriptions'] as $description)
                                                <p>{{ $description }}</p>
                                            @endforeach
                                        </div>
                                    @elseif(isset($card['ability_data']['description']))
                                        <p class="text-slate-300 leading-relaxed">
                                            {{ $card['ability_data']['description'] }}</p>
                                    @endif
                                </div>
                                <!-- Footer -->
                                <div class="mt-auto px-4 pb-4">
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-[10px] font-bold uppercase tracking-widest rounded-md bg-slate-700 text-white">
                                        {{ ucfirst($card['ability_data']['domain'] ?? ($card['domain'] ?? 'codex')) }}
                                        Domain
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <!-- JOURNAL -->
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6 shadow-lg">
                <h2 class="text-lg font-bold">Equipment</h2>
                <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="rounded-2xl ring-1 ring-slate-700/60 p-4">
                        <div class="text-xs text-slate-400">Inventory</div>
                        <ul class="mt-2 space-y-1.5 text-sm">
                            @if (!empty($organized_equipment['items']))
                                @foreach ($organized_equipment['items'] as $item)
                                    <li>{{ $item['data']['name'] ?? ucwords(str_replace('-', ' ', $item['key'])) }}
                                    </li>
                                @endforeach
                            @endif
                            @if (!empty($organized_equipment['consumables']))
                                @foreach ($organized_equipment['consumables'] as $consumable)
                                    <li>{{ $consumable['data']['name'] ?? ucwords(str_replace('-', ' ', $consumable['key'])) }}
                                    </li>
                                @endforeach
                            @endif
                            @if (empty($organized_equipment['items']) && empty($organized_equipment['consumables']))
                                <li class="text-slate-500 italic">No items in inventory</li>
                            @endif
                        </ul>
                    </div>
                    <div class="rounded-2xl ring-1 ring-slate-700/60 p-4">
                        <div class="text-xs text-slate-400">Stash</div>
                        <ul class="mt-2 space-y-1.5 text-sm">
                            <li class="text-slate-500 italic">Empty</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div
                class="w-full rounded-3xl border border-slate-800 bg-slate-900/60 p-6 shadow-lg">
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
                        <p class="text-sm text-slate-300">Session notes, quests, bonds, and discoveries go here.
                            (Editable later.)</p>
                    @endif
                </div>
            </div>
        </section>
    </main>
</div>
