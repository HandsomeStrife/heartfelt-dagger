<!-- Equipment Selection Step -->
<div class="space-y-8" x-data="equipmentSelector()" x-init="init()">
    <!-- Step Header -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-white mb-2 font-outfit">Select Equipment</h2>
        <p class="text-slate-300 font-roboto">Choose your starting weapons, armor, and inventory according to Daggerheart
            rules.</p>
    </div>

    @if ($character->selected_class)
        <!-- Progress Indicator -->
        <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl p-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-0">
                <div class="flex items-center gap-2 sm:gap-4 text-sm flex-wrap">
                    <div class="flex items-center gap-2">
                        @if ($equipment_progress['selectedPrimary'])
                            <div class="bg-emerald-500 rounded-full p-1">
                                <svg class="w-3 h-3 text-black" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="text-emerald-400">Primary</span>
                        @else
                            <div class="bg-slate-600 rounded-full p-1">
                                <svg class="w-3 h-3 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="text-slate-400">Primary</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($equipment_progress['selectedSecondary'])
                            <div class="bg-emerald-500 rounded-full p-1">
                                <svg class="w-3 h-3 text-black" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="text-emerald-400">Secondary</span>
                        @else
                            <div class="bg-slate-600 rounded-full p-1">
                                <svg class="w-3 h-3 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="text-slate-400">Secondary</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($equipment_progress['selectedArmor'])
                            <div class="bg-emerald-500 rounded-full p-1">
                                <svg class="w-3 h-3 text-black" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="text-emerald-400">Armor</span>
                        @else
                            <div class="bg-slate-600 rounded-full p-1">
                                <svg class="w-3 h-3 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="text-slate-400">Armor</span>
                        @endif
                    </div>
                    @if ($equipment_progress['hasStartingInventory'])
                        <div class="flex items-center gap-2">
                            @if ($equipment_progress['hasSelectedChooseOne'] && $equipment_progress['hasSelectedChooseExtra'])
                                <div class="bg-emerald-500 rounded-full p-1">
                                    <svg class="w-3 h-3 text-black" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <span class="text-emerald-400">Inventory</span>
                            @elseif($equipment_progress['hasSelectedChooseOne'] || $equipment_progress['hasSelectedChooseExtra'])
                                <div class="bg-amber-500 rounded-full p-1">
                                    <svg class="w-3 h-3 text-black" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <span class="text-amber-400">Inventory (Partial)</span>
                            @else
                                <div class="bg-slate-600 rounded-full p-1">
                                    <svg class="w-3 h-3 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <span class="text-slate-400">Inventory</span>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="flex items-center gap-4">
                    @if ($equipment_progress['equipmentComplete'])
                        <div class="flex items-center gap-2">
                            <div class="bg-emerald-500 rounded-full p-1">
                                <svg class="w-3 h-3 text-black" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="text-emerald-400 font-medium text-sm">Complete!</span>
                        </div>
                    @endif

                    <!-- Saving indicator -->
                    <div x-show="saving" class="flex items-center gap-2 text-amber-400">
                        <svg class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span class="text-xs">Saving...</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (!$character->selected_class)
        <!-- No Class Selected Message -->
        <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl p-8 text-center">
            <div class="mb-4">
                <svg class="w-16 h-16 text-blue-400 mx-auto" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-white font-outfit mb-2">Class Selection Required</h3>
            <p class="text-slate-300 mb-4">
                You need to select your character's class before choosing equipment.
                Different classes have different starting equipment and weapon proficiencies.
            </p>
            <p class="text-slate-400 text-sm">
                Return to <span class="text-amber-400 font-medium">Class Selection</span> to choose your class first.
            </p>
        </div>
    @else
        <!-- Equipment Selection -->
        <div class="space-y-4 sm:space-y-6">
            <!-- Weapons Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                <!-- Primary Weapons -->
                <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl p-4">
                    <h3 class="text-lg font-bold text-white font-outfit mb-4">Primary Weapon</h3>

                    <div class="space-y-2 max-h-80 overflow-y-auto">
                        <!-- Class Suggested Primary Weapon -->
                        @if (isset($filtered_data['suggested_primary_weapon']))
                            <div @click="selectEquipment('{{ $filtered_data['suggested_primary_weapon']['weaponKey'] }}', 'weapon', {{ Js::from($filtered_data['suggested_primary_weapon']['weaponData']) }})"
                                :class="{
                                    'flex items-center justify-between p-3 rounded-lg border cursor-pointer transition-all duration-200': true,
                                    'bg-emerald-400/20 border-emerald-400': isEquipmentSelected(
                                        '{{ $filtered_data['suggested_primary_weapon']['weaponKey'] }}', 'weapon'),
                                    'bg-emerald-500/10 border-emerald-500/50 hover:border-emerald-400': !
                                        isEquipmentSelected(
                                            '{{ $filtered_data['suggested_primary_weapon']['weaponKey'] }}', 'weapon')
                                }">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-white font-medium">
                                            {{ $filtered_data['suggested_primary_weapon']['weaponData']['name'] }}</h4>
                                        <span
                                            class="bg-emerald-500 text-black px-1.5 py-0.5 rounded text-xs font-bold">Recommended</span>
                                    </div>
                                    <div class="text-xs text-slate-300 mt-1">
                                        {{ $filtered_data['suggested_primary_weapon']['suggestion']['trait'] }} •
                                        {{ $filtered_data['suggested_primary_weapon']['suggestion']['range'] }} •
                                        {{ $filtered_data['suggested_primary_weapon']['suggestion']['damage'] }}
                                    </div>
                                </div>
                                <div x-show="isEquipmentSelected('{{ $filtered_data['suggested_primary_weapon']['weaponKey'] }}', 'weapon')"
                                    class="bg-emerald-400 rounded-full p-1 ml-2">
                                    <svg class="w-3 h-3 text-black" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        @endif

                        <!-- Other Primary Weapons -->
                        @foreach ($game_data['weapons'] ?? [] as $weaponKey => $weaponData)
                            @if (
                                ($weaponData['tier'] ?? 1) === 1 &&
                                    ($weaponData['type'] ?? 'Primary') === 'Primary' &&
                                    !$this->isWeaponSuggested($weaponKey, 'primary'))
                                <div @click="selectEquipment('{{ $weaponKey }}', 'weapon', @js($weaponData))"
                                    :class="{
                                        'flex items-center justify-between p-3 rounded-lg border cursor-pointer transition-all duration-200': true,
                                        'bg-amber-400/20 border-amber-400': isEquipmentSelected('{{ $weaponKey }}',
                                            'weapon'),
                                        'bg-slate-700/50 border-slate-600 hover:border-slate-500': !isEquipmentSelected(
                                            '{{ $weaponKey }}', 'weapon')
                                    }">
                                    <div class="flex-1">
                                        <h4 class="text-white font-medium">{{ $weaponData['name'] }}</h4>
                                        <div class="text-xs text-slate-400 mt-1">
                                            {{ $weaponData['trait'] ?? 'N/A' }} • {{ $weaponData['range'] ?? 'N/A' }}
                                            •
                                            d{{ $weaponData['damage']['dice'] ?? 6 }}+{{ $weaponData['damage']['bonus'] ?? 0 }}
                                        </div>
                                    </div>
                                    <div x-show="isEquipmentSelected('{{ $weaponKey }}', 'weapon')"
                                        class="bg-amber-400 rounded-full p-1 ml-2">
                                        <svg class="w-3 h-3 text-black" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Secondary Weapons -->
                <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl p-4">
                    <h3 class="text-lg font-bold text-white font-outfit mb-4">Secondary Weapon <span
                            class="text-slate-400 text-sm font-normal">(Optional)</span></h3>

                    <div class="space-y-2 max-h-80 overflow-y-auto">
                        <!-- Class Suggested Secondary Weapon -->
                        @if (isset($filtered_data['suggested_secondary_weapon']))
                            <div @click="selectEquipment('{{ $filtered_data['suggested_secondary_weapon']['weaponKey'] }}', 'weapon', @js($filtered_data['suggested_secondary_weapon']['weaponData']))"
                                :class="{
                                    'flex items-center justify-between p-3 rounded-lg border cursor-pointer transition-all duration-200': true,
                                    'bg-emerald-400/20 border-emerald-400': isEquipmentSelected(
                                        '{{ $filtered_data['suggested_secondary_weapon']['weaponKey'] }}', 'weapon'),
                                    'bg-emerald-500/10 border-emerald-500/50 hover:border-emerald-400': !
                                        isEquipmentSelected(
                                            '{{ $filtered_data['suggested_secondary_weapon']['weaponKey'] }}', 'weapon')
                                }">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-white font-medium">
                                            {{ $filtered_data['suggested_secondary_weapon']['weaponData']['name'] }}</h4>
                                        <span
                                            class="bg-emerald-500 text-black px-1.5 py-0.5 rounded text-xs font-bold">Recommended</span>
                                    </div>
                                    <div class="text-xs text-slate-300 mt-1">
                                        {{ $filtered_data['suggested_secondary_weapon']['suggestion']['trait'] }} •
                                        {{ $filtered_data['suggested_secondary_weapon']['suggestion']['range'] }} •
                                        {{ $filtered_data['suggested_secondary_weapon']['suggestion']['damage'] }}
                                    </div>
                                </div>
                                <div x-show="isEquipmentSelected('{{ $filtered_data['suggested_secondary_weapon']['weaponKey'] }}', 'weapon')"
                                    class="bg-emerald-400 rounded-full p-1 ml-2">
                                    <svg class="w-3 h-3 text-black" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        @endif

                        <!-- Other Secondary Weapons -->
                        @foreach ($game_data['weapons'] ?? [] as $weaponKey => $weaponData)
                            @if (
                                ($weaponData['tier'] ?? 1) === 1 &&
                                    ($weaponData['type'] ?? '') === 'Secondary' &&
                                    !$this->isWeaponSuggested($weaponKey, 'secondary'))
                                <div @click="selectEquipment('{{ $weaponKey }}', 'weapon', @js($weaponData))"
                                    :class="{
                                        'flex items-center justify-between p-3 rounded-lg border cursor-pointer transition-all duration-200': true,
                                        'bg-amber-400/20 border-amber-400': isEquipmentSelected('{{ $weaponKey }}',
                                            'weapon'),
                                        'bg-slate-700/50 border-slate-600 hover:border-slate-500': !isEquipmentSelected(
                                            '{{ $weaponKey }}', 'weapon')
                                    }">
                                    <div class="flex-1">
                                        <h4 class="text-white font-medium">{{ $weaponData['name'] }}</h4>
                                        <div class="text-xs text-slate-400 mt-1">
                                            {{ $weaponData['trait'] ?? 'N/A' }} • {{ $weaponData['range'] ?? 'N/A' }}
                                            •
                                            @if (isset($weaponData['damage']))
                                                d{{ $weaponData['damage']['dice'] ?? 6 }}+{{ $weaponData['damage']['bonus'] ?? 0 }}
                                            @else
                                                Shield
                                            @endif
                                        </div>
                                    </div>
                                    <div x-show="isEquipmentSelected('{{ $weaponKey }}', 'weapon')"
                                        class="bg-amber-400 rounded-full p-1 ml-2">
                                        <svg class="w-3 h-3 text-black" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Armor and Inventory Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                <!-- Armor -->
                <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl p-4">
                    <h3 class="text-lg font-bold text-white font-outfit mb-4">Armor</h3>

                    <div class="space-y-2 max-h-80 overflow-y-auto">
                        <!-- Class Suggested Armor -->
                        @if (isset($filtered_data['suggested_armor']))
                            <div @click="selectEquipment('{{ $filtered_data['suggested_armor']['armorKey'] }}', 'armor', @js($filtered_data['suggested_armor']['armorData']))"
                                :class="{
                                    'flex items-center justify-between p-3 rounded-lg border cursor-pointer transition-all duration-200': true,
                                    'bg-emerald-400/20 border-emerald-400': isEquipmentSelected(
                                        '{{ $filtered_data['suggested_armor']['armorKey'] }}', 'armor'),
                                    'bg-emerald-500/10 border-emerald-500/50 hover:border-emerald-400': !
                                        isEquipmentSelected('{{ $filtered_data['suggested_armor']['armorKey'] }}',
                                            'armor')
                                }">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-white font-medium">
                                            {{ $filtered_data['suggested_armor']['armorData']['name'] }}</h4>
                                        <span
                                            class="bg-emerald-500 text-black px-1.5 py-0.5 rounded text-xs font-bold">Recommended</span>
                                    </div>
                                    <div class="text-xs text-slate-300 mt-1">
                                        Score {{ $filtered_data['suggested_armor']['suggestion']['score'] }} •
                                        {{ $filtered_data['suggested_armor']['suggestion']['thresholds'] }}
                                        @if (isset($filtered_data['suggested_armor']['suggestion']['feature']))
                                            •
                                            {{ Str::limit($filtered_data['suggested_armor']['suggestion']['feature'], 30) }}
                                        @endif
                                    </div>
                                </div>
                                <div x-show="isEquipmentSelected('{{ $filtered_data['suggested_armor']['armorKey'] }}', 'armor')"
                                    class="bg-emerald-400 rounded-full p-1 ml-2">
                                    <svg class="w-3 h-3 text-black" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        @endif

                        <!-- Other Armor Options -->
                        @foreach ($game_data['armor'] ?? [] as $armorKey => $armorData)
                            @if (($armorData['tier'] ?? 1) === 1 && !$this->isArmorSuggested($armorKey))
                                <div @click="selectEquipment('{{ $armorKey }}', 'armor', @js($armorData))"
                                    :class="{
                                        'flex items-center justify-between p-3 rounded-lg border cursor-pointer transition-all duration-200': true,
                                        'bg-blue-400/20 border-blue-400': isEquipmentSelected('{{ $armorKey }}',
                                            'armor'),
                                        'bg-slate-700/50 border-slate-600 hover:border-slate-500': !isEquipmentSelected(
                                            '{{ $armorKey }}', 'armor')
                                    }">
                                    <div class="flex-1">
                                        <h4 class="text-white font-medium">{{ $armorData['name'] }}</h4>
                                        <div class="text-xs text-slate-400 mt-1">
                                            Score {{ $armorData['baseScore'] ?? 'N/A' }} •
                                            {{ $armorData['baseThresholds']['lower'] ?? 'N/A' }}/{{ $armorData['baseThresholds']['higher'] ?? 'N/A' }}
                                        </div>
                                    </div>
                                    <div x-show="isEquipmentSelected('{{ $armorKey }}', 'armor')"
                                        class="bg-blue-400 rounded-full p-1 ml-2">
                                        <svg class="w-3 h-3 text-black" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Starting Inventory -->
                @if ($character->selected_class && isset($filtered_data['selected_class_data']['startingInventory']))
                    <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl p-4">
                        <h3 class="text-lg font-bold text-white font-outfit mb-4">Starting Inventory</h3>

                        <div class="space-y-4 max-h-80 overflow-y-auto">
                            <!-- Always Items -->
                            @if (isset($filtered_data['selected_class_data']['startingInventory']['always']) &&
                                    is_array($filtered_data['selected_class_data']['startingInventory']['always']))
                                <div>
                                    <h4 class="text-white font-medium mb-2 text-sm">✓ Starting Equipment</h4>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($filtered_data['selected_class_data']['startingInventory']['always'] as $item)
                                            <div
                                                class="bg-emerald-500/10 border border-emerald-500/30 rounded px-2 py-1">
                                                <span class="text-emerald-300 text-xs">{{ $item }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Choose One -->
                            @if (isset($filtered_data['processed_choose_one_items']) && count($filtered_data['processed_choose_one_items']) > 0)
                                <div>
                                    <h4 class="text-white font-medium mb-2 text-sm">⚡ Choose One</h4>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($filtered_data['processed_choose_one_items'] as $itemData)
                                            <div @click="selectInventoryItem({{ Js::from($itemData['item_name']) }}, {{ Js::from($itemData['item_key']) }}, {{ Js::from($itemData['item_type']) }}, {{ Js::from($itemData['item_data']) }}, 'chooseOne')"
                                                :class="{
                                                    'flex gap-2 items-center justify-between p-2 rounded-lg border cursor-pointer transition-all duration-200': true,
                                                    'bg-amber-400/20 border-amber-400': isInventoryItemSelected(
                                                        {{ Js::from($itemData['item_name']) }}),
                                                    'bg-slate-700/50 border-slate-600 hover:border-amber-400': !
                                                        isInventoryItemSelected({{ Js::from($itemData['item_name']) }})
                                                }">
                                                <span class="text-white text-sm">{{ $itemData['item_name'] }}</span>
                                                <div x-show="isInventoryItemSelected({{ Js::from($itemData['item_name']) }})"
                                                    class="bg-amber-400 rounded-full p-1">
                                                    <svg class="w-2 h-2 text-black" fill="currentColor"
                                                        viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Choose Extra -->
                            @if (isset($filtered_data['processed_choose_extra_items']) && count($filtered_data['processed_choose_extra_items']) > 0)
                                <div>
                                    <h4 class="text-white font-medium mb-2 text-sm">📦 Choose Extra Items</h4>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($filtered_data['processed_choose_extra_items'] as $itemData)
                                            <div @click="selectInventoryItem({{ Js::from($itemData['item_name']) }}, {{ Js::from($itemData['item_key']) }}, {{ Js::from($itemData['item_type']) }}, {{ Js::from($itemData['item_data']) }}, 'chooseExtra')"
                                                :class="{
                                                    'flex gap-2 items-center justify-between p-2 rounded-lg border cursor-pointer transition-all duration-200': true,
                                                    'bg-purple-400/20 border-purple-400': isInventoryItemSelected(
                                                        {{ Js::from($itemData['item_name']) }}),
                                                    'bg-slate-700/50 border-slate-600 hover:border-purple-400': !
                                                        isInventoryItemSelected({{ Js::from($itemData['item_name']) }})
                                                }">
                                                <span class="text-white text-sm">{{ $itemData['item_name'] }}</span>
                                                <div x-show="isInventoryItemSelected({{ Js::from($itemData['item_name']) }})"
                                                    class="bg-purple-400 rounded-full p-1">
                                                    <svg class="w-2 h-2 text-black" fill="currentColor"
                                                        viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Special Items -->
                            @if (isset($filtered_data['selected_class_data']['startingInventory']['special']) &&
                                    is_array($filtered_data['selected_class_data']['startingInventory']['special']))
                                <div>
                                    <h4 class="text-white font-medium mb-2 text-sm">⭐ Class Items</h4>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($filtered_data['selected_class_data']['startingInventory']['special'] as $item)
                                            <div class="bg-amber-500/10 border border-amber-500/30 rounded px-2 py-1">
                                                <span class="text-amber-300 text-xs">{{ $item }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        @if ($character->selected_class)
            <div class="flex justify-center">
                <button
                    @click="$wire.applySuggestedEquipment().then(() => { selected_equipment = $wire.character.selected_equipment || []; saving = false; })"
                    class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-xl transition-all duration-200 flex items-center gap-2"
                    dusk="apply-all-suggestions">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    Apply All {{ $filtered_data['selected_class_data']['name'] ?? 'Class' }} Suggestions
                </button>
            </div>
        @endif
</div>
@endif
</div>

<script>
    function equipmentSelector() {
        return {
            selected_equipment: @json($character->selected_equipment),
            saving: false,

            init() {
                // Listen for character updates from server
                this.$wire.on('character-updated', () => {
                    this.selected_equipment = this.$wire.character.selected_equipment || [];
                    this.saving = false; // Hide saving indicator when sync complete
                });
            },

            // Helper methods for instant UI updates
            selectEquipment(key, type, data) {
                // Remove existing equipment of the same type (single selection)
                if (type === 'weapon') {
                    const weaponType = data.type || 'Primary';
                    this.selected_equipment = this.selected_equipment.filter(eq =>
                        !(eq.type === 'weapon' && (eq.data.type || 'Primary') === weaponType)
                    );
                } else {
                    this.selected_equipment = this.selected_equipment.filter(eq => eq.type !== type);
                }

                // Add new equipment
                this.selected_equipment.push({
                    key: key,
                    type: type,
                    data: data
                });

                // Sync to server after a short delay to batch updates
                this.debouncedSync();
            },

            selectInventoryItem(itemName, itemKey, type, data, category) {
                // Check if already selected (toggle behavior)
                const existingIndex = this.selected_equipment.findIndex(eq => eq.key === itemKey);

                if (existingIndex !== -1) {
                    // Remove if already selected
                    this.selected_equipment.splice(existingIndex, 1);
                } else {
                    // Single selection for inventory categories - remove existing items from same category
                    if (category === 'chooseOne') {
                        // Remove any existing chooseOne items
                        const chooseOneItems = @json(isset($filtered_data['processed_choose_one_items'])
                                ? array_column($filtered_data['processed_choose_one_items'], 'item_name')
                                : []
                        );

                        this.selected_equipment = this.selected_equipment.filter(eq => {
                            if (eq.type === 'consumable' || eq.type === 'item') {
                                // Check if this equipment is a chooseOne item
                                for (let chooseOneItem of chooseOneItems) {
                                    const chooseOneKey = chooseOneItem.toLowerCase();
                                    if (eq.key === chooseOneKey) {
                                        return false; // Remove this item
                                    }
                                }
                            }
                            return true; // Keep this item
                        });
                    } else if (category === 'chooseExtra') {
                        // Remove any existing chooseExtra items
                        const chooseExtraItems = @json(isset($filtered_data['processed_choose_extra_items'])
                                ? array_column($filtered_data['processed_choose_extra_items'], 'item_name')
                                : []
                        );

                        this.selected_equipment = this.selected_equipment.filter(eq => {
                            if (eq.type === 'consumable' || eq.type === 'item') {
                                // Check if this equipment is a chooseExtra item
                                for (let chooseExtraItem of chooseExtraItems) {
                                    const chooseExtraKey = chooseExtraItem.toLowerCase();
                                    if (eq.key === chooseExtraKey) {
                                        return false; // Remove this item
                                    }
                                }
                            }
                            return true; // Keep this item
                        });
                    }

                    // Add new item
                    this.selected_equipment.push({
                        key: itemKey,
                        type: type,
                        data: data
                    });
                }

                this.debouncedSync();
            },

            isEquipmentSelected(key, type) {
                return this.selected_equipment.some(eq => eq.key === key && eq.type === type);
            },

            isInventoryItemSelected(itemName) {
                const itemKey = itemName.toLowerCase();
                return this.selected_equipment.some(eq => eq.key === itemKey);
            },

            // Debounced sync to server
            syncTimeout: null,
            debouncedSync() {
                this.saving = true; // Show saving indicator
                clearTimeout(this.syncTimeout);
                this.syncTimeout = setTimeout(() => {
                    this.$wire.syncEquipment(this.selected_equipment);
                }, 500); // 500ms delay to batch rapid selections
            }
        }
    }
</script>
