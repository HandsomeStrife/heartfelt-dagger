<!-- Equipment Selection Step -->
<div class="space-y-6 sm:space-y-8">
    <!-- Step Header -->
    <div class="mb-6 sm:mb-8">
        <h2 class="text-xl sm:text-2xl font-bold text-white mb-2 font-outfit">Select Equipment</h2>
        <p class="text-slate-300 font-roboto text-sm sm:text-base">Choose your starting weapons, armor, and inventory according to Daggerheart
            rules.</p>
    </div>

    <div x-show="selected_class">
        <!-- Progress Indicator -->
        <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl p-3 sm:p-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-0">
                <div class="flex flex-wrap items-center gap-2 sm:gap-4 text-sm">
                    <div class="flex items-center gap-2">
                        <div x-show="selectedPrimary" class="bg-emerald-500 rounded-full p-1">
                            <svg class="w-3 h-3 text-black" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div x-show="!selectedPrimary" class="bg-slate-600 rounded-full p-1">
                            <svg class="w-3 h-3 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <span class="text-emerald-400" x-show="selectedPrimary">Primary</span>
                        <span class="text-slate-400" x-show="!selectedPrimary">Primary</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div x-show="selectedSecondary" class="bg-emerald-500 rounded-full p-1">
                            <svg class="w-3 h-3 text-black" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div x-show="!selectedSecondary" class="bg-slate-600 rounded-full p-1">
                            <svg class="w-3 h-3 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <span class="text-emerald-400" x-show="selectedSecondary">Secondary</span>
                        <span class="text-slate-400" x-show="!selectedSecondary">Secondary</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div x-show="selectedArmor" class="bg-emerald-500 rounded-full p-1">
                            <svg class="w-3 h-3 text-black" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div x-show="!selectedArmor" class="bg-slate-600 rounded-full p-1">
                            <svg class="w-3 h-3 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <span class="text-emerald-400" x-show="selectedArmor">Armor</span>
                        <span class="text-slate-400" x-show="!selectedArmor">Armor</span>
                    </div>
                    <!-- TODO: Inventory progress indicator - need to implement client-side inventory tracking -->
                </div>

                <div class="flex items-center gap-4">
                    <div x-show="equipmentComplete" class="flex items-center gap-2">
                        <div class="bg-emerald-500 rounded-full p-1">
                            <svg class="w-3 h-3 text-black" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <span class="text-emerald-400 font-medium text-sm">Complete!</span>
                    </div>

                    <!-- NOTE: Equipment saving indicator removed - equipment now saves only on explicit save button click -->
                </div>
            </div>
        </div>
    </div>

    <div x-show="!selected_class">
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
    </div>

    <div x-show="selected_class">
        <!-- Equipment Selection -->
        <div class="space-y-4 sm:space-y-6">
            <!-- Weapons Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-6">
                <!-- Primary Weapons -->
                <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl p-3 sm:p-4">
                    <h3 class="text-base sm:text-lg font-bold text-white font-outfit mb-3 sm:mb-4">Primary Weapon</h3>

                    <div class="space-y-2 max-h-64 sm:max-h-80 overflow-y-auto">
                        <!-- Class Suggested Primary Weapon -->
                        <template x-if="suggestedPrimaryWeapon">
                            <div @click="selectEquipment(suggestedPrimaryWeapon.weaponKey, 'weapon', suggestedPrimaryWeapon.weaponData)"
                                 pest="suggested-primary-weapon"
                                :class="{
                                    'flex items-center justify-between p-2 sm:p-3 rounded-lg border cursor-pointer transition-all duration-200': true,
                                    'bg-amber-400/20 border-amber-400': isEquipmentSelected(suggestedPrimaryWeapon.weaponKey, 'weapon'),
                                    'bg-slate-700/50 border-slate-600 hover:border-slate-500': !isEquipmentSelected(suggestedPrimaryWeapon.weaponKey, 'weapon')
                                }">
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
                                        <h4 class="text-white font-medium text-sm sm:text-base" x-text="suggestedPrimaryWeapon.weaponData.name"></h4>
                                        <span class="bg-emerald-500 text-black px-1.5 py-0.5 rounded text-xs font-bold self-start">Recommended</span>
                                    </div>
                                    <div class="text-xs text-slate-300 mt-1">
                                        <span x-text="suggestedPrimaryWeapon.weaponData.trait || 'N/A'"></span> •
                                        <span x-text="suggestedPrimaryWeapon.weaponData.range || 'N/A'"></span> •
                                        <span x-text="'d' + (suggestedPrimaryWeapon.weaponData.damage?.dice || 6) + '+' + (suggestedPrimaryWeapon.weaponData.damage?.bonus || 0)"></span>
                                    </div>
                                </div>
                                <div x-show="isEquipmentSelected(suggestedPrimaryWeapon.weaponKey, 'weapon')"
                                    class="bg-amber-400 rounded-full p-1 ml-2">
                                    <svg class="w-3 h-3 text-black" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        </template>

                        <!-- Other Primary Weapons -->
                        <template x-for="[weaponKey, weaponData] in Object.entries(tier1PrimaryWeapons)" :key="weaponKey">
                            <div @click="selectEquipment(weaponKey, 'weapon', weaponData)"
                                 :pest="`weapon-${weaponKey}`"
                                :class="{
                                    'flex items-center justify-between p-2 sm:p-3 rounded-lg border cursor-pointer transition-all duration-200': true,
                                    'bg-amber-400/20 border-amber-400': isEquipmentSelected(weaponKey, 'weapon'),
                                    'bg-slate-700/50 border-slate-600 hover:border-slate-500': !isEquipmentSelected(weaponKey, 'weapon')
                                }">
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-white font-medium text-sm sm:text-base" x-text="weaponData.name"></h4>
                                    <div class="text-xs text-slate-400 mt-1">
                                        <span x-text="weaponData.trait || 'N/A'"></span> •
                                        <span x-text="weaponData.range || 'N/A'"></span> •
                                        <span x-text="'d' + (weaponData.damage?.dice || 6) + '+' + (weaponData.damage?.bonus || 0)"></span>
                                    </div>
                                </div>
                                <div x-show="isEquipmentSelected(weaponKey, 'weapon')"
                                    class="bg-amber-400 rounded-full p-1 ml-2">
                                    <svg class="w-3 h-3 text-black" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Secondary Weapons -->
                <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl p-3 sm:p-4">
                    <h3 class="text-base sm:text-lg font-bold text-white font-outfit mb-3 sm:mb-4">Secondary Weapon <span
                            class="text-slate-400 text-sm font-normal">(Optional)</span></h3>

                    <div class="space-y-2 max-h-64 sm:max-h-80 overflow-y-auto">
                        <!-- Class Suggested Secondary Weapon -->
                        <template x-if="suggestedSecondaryWeapon">
                            <div @click="selectEquipment(suggestedSecondaryWeapon.weaponKey, 'weapon', suggestedSecondaryWeapon.weaponData)"
                                 pest="suggested-secondary-weapon"
                                :class="{
                                    'flex items-center justify-between p-3 rounded-lg border cursor-pointer transition-all duration-200': true,
                                    'bg-amber-400/20 border-amber-400': isEquipmentSelected(suggestedSecondaryWeapon.weaponKey, 'weapon'),
                                    'bg-slate-700/50 border-slate-600 hover:border-slate-500': !isEquipmentSelected(suggestedSecondaryWeapon.weaponKey, 'weapon')
                                }">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-white font-medium" x-text="suggestedSecondaryWeapon.weaponData.name"></h4>
                                        <span class="bg-emerald-500 text-black px-1.5 py-0.5 rounded text-xs font-bold">Recommended</span>
                                    </div>
                                    <div class="text-xs text-slate-300 mt-1">
                                        <span x-text="suggestedSecondaryWeapon.weaponData.trait || 'N/A'"></span> •
                                        <span x-text="suggestedSecondaryWeapon.weaponData.range || 'N/A'"></span> •
                                        <span x-text="suggestedSecondaryWeapon.weaponData.damage ? ('d' + (suggestedSecondaryWeapon.weaponData.damage.dice || 6) + '+' + (suggestedSecondaryWeapon.weaponData.damage.bonus || 0)) : 'Shield'"></span>
                                    </div>
                                </div>
                                <div x-show="isEquipmentSelected(suggestedSecondaryWeapon.weaponKey, 'weapon')"
                                    class="bg-amber-400 rounded-full p-1 ml-2">
                                    <svg class="w-3 h-3 text-black" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        </template>

                        <!-- Other Secondary Weapons -->
                        <template x-for="[weaponKey, weaponData] in Object.entries(tier1SecondaryWeapons)" :key="weaponKey">
                            <div @click="selectEquipment(weaponKey, 'weapon', weaponData)"
                                 :pest="`weapon-${weaponKey}`"
                                :class="{
                                    'flex items-center justify-between p-3 rounded-lg border cursor-pointer transition-all duration-200': true,
                                    'bg-amber-400/20 border-amber-400': isEquipmentSelected(weaponKey, 'weapon'),
                                    'bg-slate-700/50 border-slate-600 hover:border-slate-500': !isEquipmentSelected(weaponKey, 'weapon')
                                }">
                                <div class="flex-1">
                                    <h4 class="text-white font-medium" x-text="weaponData.name"></h4>
                                    <div class="text-xs text-slate-400 mt-1">
                                        <span x-text="weaponData.trait || 'N/A'"></span> •
                                        <span x-text="weaponData.range || 'N/A'"></span> •
                                        <span x-text="weaponData.damage ? ('d' + (weaponData.damage.dice || 6) + '+' + (weaponData.damage.bonus || 0)) : 'Shield'"></span>
                                    </div>
                                </div>
                                <div x-show="isEquipmentSelected(weaponKey, 'weapon')"
                                    class="bg-amber-400 rounded-full p-1 ml-2">
                                    <svg class="w-3 h-3 text-black" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Armor and Inventory Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-6">
                <!-- Armor -->
                <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl p-3 sm:p-4">
                    <h3 class="text-base sm:text-lg font-bold text-white font-outfit mb-3 sm:mb-4">Armor</h3>

                    <div class="space-y-2 max-h-64 sm:max-h-80 overflow-y-auto">
                        <!-- Class Suggested Armor -->
                        <template x-if="suggestedArmor">
                            <div @click="selectEquipment(suggestedArmor.armorKey, 'armor', suggestedArmor.armorData)"
                                 pest="suggested-armor"
                                :class="{
                                    'flex items-center justify-between p-3 rounded-lg border cursor-pointer transition-all duration-200': true,
                                    'bg-amber-400/20 border-amber-400': isEquipmentSelected(suggestedArmor.armorKey, 'armor'),
                                    'bg-slate-700/50 border-slate-600 hover:border-slate-500': !isEquipmentSelected(suggestedArmor.armorKey, 'armor')
                                }">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-white font-medium" x-text="suggestedArmor.armorData.name"></h4>
                                        <span class="bg-emerald-500 text-black px-1.5 py-0.5 rounded text-xs font-bold">Recommended</span>
                                    </div>
                                    <div class="text-xs text-slate-300 mt-1">
                                        <span>Score <span x-text="suggestedArmor.armorData.baseScore || 'N/A'"></span></span> •
                                        <span x-text="(suggestedArmor.armorData.baseThresholds?.lower || 'N/A') + '/' + (suggestedArmor.armorData.baseThresholds?.higher || 'N/A')"></span>
                                    </div>
                                </div>
                                <div x-show="isEquipmentSelected(suggestedArmor.armorKey, 'armor')"
                                    class="bg-amber-400 rounded-full p-1 ml-2">
                                    <svg class="w-3 h-3 text-black" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        </template>

                        <!-- Other Armor Options -->
                        <template x-for="[armorKey, armorData] in Object.entries(tier1Armor)" :key="armorKey">
                            <div @click="selectEquipment(armorKey, 'armor', armorData)"
                                 :pest="`armor-${armorKey}`"
                                :class="{
                                    'flex items-center justify-between p-3 rounded-lg border cursor-pointer transition-all duration-200': true,
                                    'bg-amber-400/20 border-amber-400': isEquipmentSelected(armorKey, 'armor'),
                                    'bg-slate-700/50 border-slate-600 hover:border-slate-500': !isEquipmentSelected(armorKey, 'armor')
                                }">
                                <div class="flex-1">
                                    <h4 class="text-white font-medium" x-text="armorData.name"></h4>
                                    <div class="text-xs text-slate-400 mt-1">
                                        <span>Score <span x-text="armorData.baseScore || 'N/A'"></span></span> •
                                        <span x-text="(armorData.baseThresholds?.lower || 'N/A') + '/' + (armorData.baseThresholds?.higher || 'N/A')"></span>
                                    </div>
                                </div>
                                <div x-show="isEquipmentSelected(armorKey, 'armor')"
                                    class="bg-amber-400 rounded-full p-1 ml-2">
                                    <svg class="w-3 h-3 text-black" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Starting Inventory -->
                <template x-if="selected_class && classStartingInventory">
                    <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl p-3 sm:p-4">
                        <h3 class="text-base sm:text-lg font-bold text-white font-outfit mb-3 sm:mb-4">Starting Inventory</h3>

                        <div class="space-y-3 sm:space-y-4 max-h-64 sm:max-h-80 overflow-y-auto">
                            <!-- Always Items -->
                            <template x-if="classStartingInventory.always && Array.isArray(classStartingInventory.always)">
                                <div>
                                    <h4 class="text-white font-medium mb-2 text-sm">✓ Starting Equipment</h4>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="item in classStartingInventory.always" :key="item">
                                            <div class="bg-emerald-500/10 border border-emerald-500/30 rounded px-2 py-1">
                                                <span class="text-emerald-300 text-xs" x-text="item"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <!-- Choose One -->
                            <template x-if="classStartingInventory.chooseOne && Array.isArray(classStartingInventory.chooseOne)">
                                <div>
                                    <h4 class="text-white font-medium mb-2 text-sm">⚡ Choose One</h4>
                                    <div class="grid grid-cols-1 gap-2">
                                        <template x-for="item in classStartingInventory.chooseOne" :key="item">
                                            <div 
                                                :pest="`inventory-item-${item.toLowerCase().replace(/\s+/g, '-')}`"
                                                @click="selectInventoryItem(item)"
                                                :class="isInventoryItemSelected(item) ? 
                                                    'bg-amber-400/20 border-amber-400' : 
                                                    'bg-slate-700/50 border-slate-600/50 hover:border-slate-500/70'"
                                                class="cursor-pointer transition-all duration-200 border rounded-lg p-3 group"
                                            >
                                                <div class="flex items-center justify-between">
                                                    <span class="text-white font-medium text-sm" x-text="item"></span>
                                                    <div x-show="isInventoryItemSelected(item)" class="text-amber-400">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <!-- Choose Extra -->
                            <template x-if="classStartingInventory.chooseExtra && Array.isArray(classStartingInventory.chooseExtra)">
                                <div>
                                    <h4 class="text-white font-medium mb-2 text-sm">📦 Choose Extra Items</h4>
                                    <div class="text-slate-400 text-xs mb-2">Select any additional items you want</div>
                                    <div class="grid grid-cols-1 gap-2">
                                        <template x-for="item in classStartingInventory.chooseExtra" :key="item">
                                            <div 
                                                :pest="`inventory-item-${item.toLowerCase().replace(/\s+/g, '-')}`"
                                                @click="selectInventoryItem(item)"
                                                :class="isInventoryItemSelected(item) ? 
                                                    'bg-amber-400/20 border-amber-400' : 
                                                    'bg-slate-700/50 border-slate-600/50 hover:border-slate-500/70'"
                                                class="cursor-pointer transition-all duration-200 border rounded-lg p-3 group"
                                            >
                                                <div class="flex items-center justify-between">
                                                    <span class="text-white font-medium text-sm" x-text="item"></span>
                                                    <div x-show="isInventoryItemSelected(item)" class="text-amber-400">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <!-- Special Items -->
                            <template x-if="classStartingInventory.special && Array.isArray(classStartingInventory.special)">
                                <div>
                                    <h4 class="text-white font-medium mb-2 text-sm">⭐ Class Items</h4>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="item in classStartingInventory.special" :key="item">
                                            <div class="bg-amber-500/10 border border-amber-500/30 rounded px-2 py-1">
                                                <span class="text-amber-300 text-xs" x-text="item"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        <div class="flex justify-center">
            <button
                @click="applySuggestedEquipment(); markAsUnsaved()"
                class="px-4 sm:px-6 py-2.5 sm:py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-xl transition-all duration-200 flex items-center gap-2 text-sm sm:text-base"
                pest="apply-all-suggestions">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
                <span class="hidden sm:inline">Apply All <span x-text="selectedClassData?.name || 'Class'"></span> Suggestions</span>
                <span class="sm:hidden">Apply Suggestions</span>
            </button>
        </div>
    </div>
</div>

