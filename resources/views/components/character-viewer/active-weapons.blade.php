<div pest="active-weapons-section" class="lg:col-span-7 rounded-3xl border border-slate-800 bg-slate-900/60 p-6 shadow-lg mt-6">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold">Active Weapons</h2>
        <div class="flex items-center gap-1 text-indigo-200" aria-label="Proficiency">
            <span class="text-xs mr-2 text-slate-400">Proficiency</span>
            @for ($i = 0; $i < 6; $i++)
                <span class="block w-4 h-4 rounded-full ring-1 ring-indigo-400/60 {{ $i < 1 ? 'bg-indigo-500/70' : 'bg-slate-800' }}"></span>
            @endfor
        </div>
    </div>

    @if (!empty($organizedEquipment['weapons']))
        @php $primary = collect($organizedEquipment['weapons'])->first(fn($w) => ($w['data']['type'] ?? 'Primary') === 'Primary'); @endphp
        @if ($primary)
            <div pest="primary-weapon-details" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <div class="text-[10px] uppercase tracking-wider text-slate-400">Primary</div>
                    <div pest="weapon-name" class="text-base font-semibold">{{ $primary['data']['name'] ?? 'Weapon' }}</div>
                    <div pest="weapon-range" class="text-xs text-slate-400">Trait: {{ ucfirst($primary['data']['range'] ?? 'Melee') }}</div>
                </div>
                <div class="flex items-end gap-3">
                    <div pest="weapon-trait-stat" class="px-3 py-2 rounded-xl ring-1 ring-slate-700/60 bg-slate-800/60 cursor-pointer hover:bg-slate-700/60 transition-colors duration-200"
                         onclick="rollWeaponAttack('{{ $primary['key'] ?? 'primary' }}')"
                         title="Click to roll attack">
                        <div class="text-[10px] uppercase text-slate-400">{{ ucfirst($primary['data']['trait'] ?? 'Strength') }}</div>
                        <div class="font-bold">
                            {{ $traitValues[$primary['data']['trait'] ?? 'strength'] ?? '+0' }}
                        </div>
                        <div class="text-[8px] text-slate-500 mt-1">Click to Attack</div>
                    </div>
                    <div pest="weapon-damage-stat" class="px-3 py-2 rounded-xl ring-1 ring-slate-700/60 bg-slate-800/60 cursor-pointer hover:bg-slate-700/60 transition-colors duration-200"
                         onclick="rollWeaponDamage('{{ $primary['key'] ?? 'primary' }}')"
                         title="Click to roll damage">
                        <div class="text-[10px] uppercase text-slate-400">Damage</div>
                        <div class="font-bold">
                            {{ $primary['data']['damage']['dice'] ?? 'd6' }}{{ isset($primary['data']['damage']['modifier']) && $primary['data']['damage']['modifier'] > 0 ? ' + ' . $primary['data']['damage']['modifier'] : '' }} ({{ $primary['data']['damage']['type'] ?? 'physical' }})
                        </div>
                        <div class="text-[8px] text-slate-500 mt-1">Click to Damage</div>
                    </div>
                </div>
                <div class="md:text-right text-sm text-slate-300">
                    @php $feature = $primary['data']['feature'] ?? null; @endphp
                    @if (is_string($feature) && $feature !== '')
                        {{ $feature }}
                    @elseif (is_array($feature))
                        @php
                            $parts = [];
                            if (function_exists('array_is_list') && array_is_list($feature)) {
                                foreach ($feature as $entry) {
                                    if (is_string($entry)) { $parts[] = $entry; }
                                    elseif (is_array($entry)) { $parts[] = $entry['description'] ?? ($entry['name'] ?? ''); }
                                }
                            } else {
                                $parts[] = $feature['description'] ?? ($feature['name'] ?? '');
                            }
                            $parts = array_filter($parts, fn ($p) => $p !== '');
                        @endphp
                        {{ empty($parts) ? 'No feature present for the selected weapon.' : implode('; ', $parts) }}
                    @else
                        No feature present for the selected weapon.
                    @endif
                </div>
            </div>
        @else
            <div class="mt-4 rounded-2xl ring-1 ring-dashed ring-slate-700/60 p-4 text-sm text-slate-400 text-center">Set active in the equipment section</div>
        @endif
    @else
        <div class="mt-4 rounded-2xl ring-1 ring-dashed ring-slate-700/60 p-4 text-sm text-slate-400 text-center">Set active in the equipment section</div>
    @endif
</div>

