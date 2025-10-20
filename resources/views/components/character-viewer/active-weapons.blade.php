@props([
    'organizedEquipment' => [],
    'character' => null,
    'traitValues' => [],
    'weaponDamageCount' => 1,
    'primaryWeapon' => null,
    'primaryWeaponFeature' => 'No feature present for the selected weapon.',
])

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
        @if ($primaryWeapon)
            <div pest="primary-weapon-details" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <div class="text-[10px] uppercase tracking-wider text-slate-400">Primary</div>
                    <div pest="weapon-name" class="text-base font-semibold">{{ $primaryWeapon['data']['name'] ?? 'Weapon' }}</div>
                    <div pest="weapon-range" class="text-xs text-slate-400">Trait: {{ ucfirst($primaryWeapon['data']['range'] ?? 'Melee') }}</div>
                </div>
                <div class="flex items-end gap-3">
                    <div pest="weapon-trait-stat" class="px-3 py-2 rounded-xl ring-1 ring-slate-700/60 bg-slate-800/60 cursor-pointer hover:bg-slate-700/60 transition-colors duration-200"
                         onclick="rollWeaponAttack('{{ $primaryWeapon['key'] ?? 'primary' }}', {{ str_replace('+', '', $traitValues[$primaryWeapon['data']['trait'] ?? 'strength'] ?? '0') }})"
                         title="Click to roll attack">
                        <div class="text-[10px] uppercase text-slate-400">{{ ucfirst($primaryWeapon['data']['trait'] ?? 'Strength') }}</div>
                        <div class="font-bold text-sm text-white">
                            {{ $traitValues[$primaryWeapon['data']['trait'] ?? 'strength'] ?? '+0' }}
                        </div>
                        <div class="text-[8px] text-slate-500 mt-1">Click to Attack</div>
                    </div>
                    <div pest="weapon-damage-stat" class="px-3 py-2 rounded-xl ring-1 ring-slate-700/60 bg-slate-800/60 cursor-pointer hover:bg-slate-700/60 transition-colors duration-200"
                         onclick="rollWeaponDamage('{{ $primaryWeapon['key'] ?? 'primary' }}', {{ json_encode($primaryWeapon['data']['damage'] ?? ['dice' => 8, 'bonus' => 0, 'type' => 'phy']) }})"
                         title="Click to roll damage">
                        <div class="text-[10px] uppercase text-slate-400">Damage</div>
                        <div class="font-bold text-sm">
                            <span class="text-white">{{ $weaponDamageCount }}d{{ $primaryWeapon['data']['damage']['dice'] ?? 6 }}@if(($primaryWeapon['data']['damage']['bonus'] ?? 0) > 0)+{{ $primaryWeapon['data']['damage']['bonus'] }}@elseif(($primaryWeapon['data']['damage']['bonus'] ?? 0) < 0){{ $primaryWeapon['data']['damage']['bonus'] }}@endif</span>
                            <span class="text-xs text-slate-400 ml-1">({{ $primaryWeapon['data']['damage']['type'] ?? 'phy' }})</span>
                        </div>
                        <div class="text-[8px] text-slate-500 mt-1">Click to Damage</div>
                    </div>
                </div>
                <div class="md:text-right text-sm text-slate-300">
                    {{ $primaryWeaponFeature }}
                </div>
            </div>
        @else
            <div class="mt-4 rounded-2xl ring-1 ring-dashed ring-slate-700/60 p-4 text-sm text-slate-400 text-center">Set active in the equipment section</div>
        @endif
    @else
        <div class="mt-4 rounded-2xl ring-1 ring-dashed ring-slate-700/60 p-4 text-sm text-slate-400 text-center">Set active in the equipment section</div>
    @endif
</div>

