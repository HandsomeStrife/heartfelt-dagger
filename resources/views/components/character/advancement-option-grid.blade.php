@props([
    'level',
    'options' => [],
    'selections' => [],
    'requiredCount' => 2,
])

@php
    $selectCount = $options['select_count'] ?? $requiredCount;
    $advancementOptions = $options['options'] ?? [];
@endphp

<div 
    x-data="{
        selectedOptions: @js($selections),
        level: @js($level),
        requiredCount: @js($selectCount),
        
        // Check if an option is selected
        isSelected(optionIndex) {
            return this.selectedOptions.filter(s => s.index === optionIndex).length > 0;
        },
        
        // Get selection count for an option
        getSelectionCount(optionIndex) {
            return this.selectedOptions.filter(s => s.index === optionIndex).length;
        },
        
        // Check if we can select more options
        canSelectMore() {
            return this.selectedOptions.length < this.requiredCount;
        },
        
        // Toggle option selection
        toggleOption(optionIndex, optionData) {
            const currentCount = this.getSelectionCount(optionIndex);
            const maxSelections = optionData.max_selections ?? 1;
            
            if (currentCount > 0) {
                // Remove one selection
                const selectionIndex = this.selectedOptions.findIndex(s => s.index === optionIndex);
                if (selectionIndex !== -1) {
                    this.selectedOptions.splice(selectionIndex, 1);
                }
            } else if (this.canSelectMore() && currentCount < maxSelections) {
                // Add selection if we can AND haven't reached max for this option
                this.selectedOptions.push({
                    index: optionIndex,
                    description: optionData.description,
                    type: this.getAdvancementType(optionData), // Use server-provided type
                });
            }
            
            // Emit selection change event
            this.$dispatch('advancement-selection-changed', {
                level: this.level,
                selections: this.selectedOptions
            });
        },
        
        // Check if an option can be selected (hasn't reached max)
        canSelectOption(optionIndex, optionData) {
            const currentCount = this.getSelectionCount(optionIndex);
            const maxSelections = optionData.max_selections ?? 1;
            return currentCount < maxSelections && this.canSelectMore();
        },
        
        // Get advancement type from option (server provides explicit type field)
        getAdvancementType(option) {
            // Server now provides explicit 'type' field, no need to parse
            return option.type || 'generic';
        },
        
        // Check if selections are complete
        isComplete() {
            return this.selectedOptions.length === this.requiredCount;
        },
        
        // Get validation message
        getValidationMessage() {
            const remaining = this.requiredCount - this.selectedOptions.length;
            if (remaining > 0) {
                return `Select ${remaining} more advancement${remaining > 1 ? 's' : ''}`;
            }
            return 'All advancements selected';
        }
    }"
    @advancement-reset.window="selectedOptions = []"
    class="space-y-6"
>
    <!-- Selection Progress Header -->
    <div class="flex items-center justify-between p-4 rounded-lg bg-slate-800/50 border border-slate-700">
        <div>
            <h4 class="text-lg font-outfit font-bold text-white">
                Select <span x-text="requiredCount"></span> Advancement<span x-text="requiredCount > 1 ? 's' : ''"></span>
            </h4>
            <p class="text-sm text-slate-400 mt-1">
                For Level <span x-text="level"></span>
            </p>
        </div>
        
        <div class="text-right">
            <!-- Progress Indicator -->
            <div class="flex items-center space-x-2">
                <div class="text-2xl font-bold" :class="isComplete() ? 'text-green-400' : 'text-amber-400'">
                    <span x-text="selectedOptions.length"></span>/<span x-text="requiredCount"></span>
                </div>
                <div x-show="isComplete()" class="text-green-400">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs mt-1" :class="isComplete() ? 'text-green-400' : 'text-slate-400'" x-text="getValidationMessage()"></p>
        </div>
    </div>

    <!-- Option Cards Grid -->
    @if(count($advancementOptions) > 0)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach($advancementOptions as $option)
                <x-character.advancement-option-card
                    :option="$option"
                    :index="$option['index']"
                    :selected="false"
                    x-bind:selected="isSelected({{ $option['index'] }})"
                    x-bind:select-count="getSelectionCount({{ $option['index'] }})"
                    x-bind:disabled="!canSelectOption({{ $option['index'] }}, @js($option)) && !isSelected({{ $option['index'] }})"
                    x-on:click="if ({{ $option['available'] ? 'true' : 'false' }}) { toggleOption({{ $option['index'] }}, @js($option)) }"
                    x-on:keydown.enter.prevent="if ({{ $option['available'] ? 'true' : 'false' }}) { toggleOption({{ $option['index'] }}, @js($option)) }"
                    x-on:keydown.space.prevent="if ({{ $option['available'] ? 'true' : 'false' }}) { toggleOption({{ $option['index'] }}, @js($option)) }"
                />
            @endforeach
        </div>
    @else
        <!-- No Options Available -->
        <div class="text-center py-12 px-4">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-800 mb-4">
                <svg class="w-8 h-8 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
            </div>
            <h4 class="text-lg font-outfit font-bold text-white mb-2">No Advancement Options Available</h4>
            <p class="text-slate-400">
                Unable to load advancement options for this level. Please contact support if this persists.
            </p>
        </div>
    @endif

    <!-- Selected Options Summary (Mobile-Friendly) -->
    <div x-show="selectedOptions.length > 0" x-cloak class="lg:hidden">
        <div class="p-4 rounded-lg bg-amber-500/10 border border-amber-500/30">
            <h5 class="text-sm font-bold text-amber-400 mb-2">Selected Advancements:</h5>
            <ul class="space-y-1">
                <template x-for="(selection, index) in selectedOptions" :key="index">
                    <li class="text-sm text-white flex items-start space-x-2">
                        <span class="text-amber-400">•</span>
                        <span x-text="selection.description"></span>
                    </li>
                </template>
            </ul>
        </div>
    </div>

    <!-- Help Text -->
    <div class="p-4 rounded-lg bg-blue-500/10 border border-blue-500/30">
        <div class="flex items-start space-x-3">
            <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-sm text-blue-300 space-y-1">
                <p><strong>Per DaggerHeart SRD:</strong> You must select exactly <strong x-text="requiredCount"></strong> advancement option<span x-text="requiredCount > 1 ? 's' : ''"></span> from your current tier or lower.</p>
                <p class="text-blue-200/80">Some advancements can be selected multiple times (marked with "Up to N×"). Some advancements require additional choices after selection.</p>
            </div>
        </div>
    </div>
</div>

