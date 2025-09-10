@props(['tierOptions'])

<!-- Confirmation Step -->
<div x-show="currentStep === 'confirmation'" x-cloak>
    <div class="space-y-6">
        <div>
            <h3 class="font-outfit font-bold text-slate-100 text-xl mb-3">Confirm Level Up</h3>
            <p class="text-slate-400">Review your selections before applying the changes.</p>
        </div>
        
        <div class="space-y-4">
            <h4 class="font-semibold text-slate-200 text-lg">Selected Advancements:</h4>
            <div>
                <template x-if="[firstAdvancement, secondAdvancement].filter(x => x !== null).length > 0">
                    <div class="space-y-3">
                        <template x-for="(optionIndex, index) in [firstAdvancement, secondAdvancement].filter(x => x !== null && x !== undefined)" :key="'advancement-slot-' + index">
                            <div class="bg-slate-800 border border-slate-600 rounded-lg p-4">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <span class="text-amber-400 font-medium" x-text="(index + 1) + '.'"></span>
                                        <span class="text-slate-100 ml-2" x-text="@js($tierOptions['options'] ?? [])[optionIndex]?.description || 'Unknown advancement'"></span>
                                        
                                        {{-- Show selected choices --}}
                                        <template x-if="advancementChoices[optionIndex] && advancementChoices[optionIndex].traits">
                                            <div class="mt-2 ml-6">
                                                <span class="text-slate-400 text-sm">Selected traits: </span>
                                                <span class="text-emerald-400 text-sm" x-text="advancementChoices[optionIndex].traits.join(', ')"></span>
                                            </div>
                                        </template>
                                        
                                        {{-- Show selected domain card --}}
                                        <template x-if="advancementChoices[optionIndex] && advancementChoices[optionIndex].domain_card">
                                            <div class="mt-2 ml-6">
                                                <span class="text-slate-400 text-sm">Selected domain card: </span>
                                                <span class="text-emerald-400 text-sm" x-text="advancementChoices[optionIndex].domain_card.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase())"></span>
                                            </div>
                                        </template>
                                        
                                        {{-- Show selected experience bonuses --}}
                                        <template x-if="advancementChoices[optionIndex] && advancementChoices[optionIndex].experience_bonuses">
                                            <div class="mt-2 ml-6">
                                                <span class="text-slate-400 text-sm">Experience bonuses: </span>
                                                <span class="text-emerald-400 text-sm" x-text="advancementChoices[optionIndex].experience_bonuses.join(', ')"></span>
                                            </div>
                                        </template>
                                    </div>
                                    <button type="button" 
                                            @click="$wire.removeAdvancement(optionIndex)"
                                            class="text-red-400 hover:text-red-300 text-sm">
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
                <template x-if="[firstAdvancement, secondAdvancement].filter(x => x !== null).length === 0">
                    <div class="bg-slate-800 border border-slate-600 rounded-lg p-6 text-center">
                        <p class="text-slate-400 italic">No advancements selected.</p>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
