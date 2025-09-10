@props(['index', 'option', 'selected'])

{{-- Show selection UI for advancements that require trait choices --}}
<div x-show="{{ $selected }} === {{ $index }} && '{{ strtolower($option['description']) }}'.includes('trait')"
     x-cloak
     x-data="{
        selectedTraits: [],
        maxTraits: 2,
        init() {
            // Initialize from existing choices
            if (advancementChoices[{{ $index }}] && advancementChoices[{{ $index }}].traits) {
                this.selectedTraits = [...advancementChoices[{{ $index }}].traits];
            }
        },
        toggleTrait(trait) {
            const index = this.selectedTraits.indexOf(trait);
            if (index > -1) {
                // Remove trait
                this.selectedTraits.splice(index, 1);
            } else {
                // Add trait if under limit
                if (this.selectedTraits.length < this.maxTraits) {
                    this.selectedTraits.push(trait);
                }
            }
            // Update Livewire model
            if (!advancementChoices[{{ $index }}]) {
                advancementChoices[{{ $index }}] = {};
            }
            advancementChoices[{{ $index }}].traits = [...this.selectedTraits];
            $wire.set('advancement_choices.{{ $index }}.traits', this.selectedTraits);
        },
        isTraitSelected(trait) {
            return this.selectedTraits.includes(trait);
        },
        canSelectMore() {
            return this.selectedTraits.length < this.maxTraits;
        }
     }"
     class="border-t border-slate-600 p-4 bg-slate-900/50">
    <h5 class="text-slate-200 font-medium mb-3">
        Select 2 Character Traits 
        <span class="text-amber-400 text-sm ml-2" x-text="`(${selectedTraits.length}/${maxTraits} selected)`"></span>
    </h5>
    <div class="grid grid-cols-2 gap-3">
        @foreach(['agility', 'strength', 'finesse', 'instinct', 'presence', 'knowledge'] as $trait)
        <label class="flex items-center space-x-2 cursor-pointer"
               :class="!canSelectMore() && !isTraitSelected('{{ $trait }}') ? 'opacity-50 cursor-not-allowed' : ''">
            <input type="checkbox" 
                   :checked="isTraitSelected('{{ $trait }}')"
                   @change="toggleTrait('{{ $trait }}')"
                   :disabled="!canSelectMore() && !isTraitSelected('{{ $trait }}')"
                   class="rounded border-slate-600 bg-slate-700 text-amber-500 focus:ring-amber-500 disabled:opacity-50 disabled:cursor-not-allowed">
            <span class="text-slate-300 capitalize">{{ $trait }}</span>
        </label>
        @endforeach
    </div>
</div>
