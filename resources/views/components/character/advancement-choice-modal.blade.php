@props([
    'title' => 'Make a Selection',
    'description' => '',
    'options' => [],
    'selected' => null,
    'show' => false,
])

<div 
    x-data="{
        isOpen: @js($show),
        selected: @js($selected),
        options: @js($options),
        
        selectOption(value) {
            this.selected = value;
        },
        
        isSelected(value) {
            return this.selected === value;
        },
        
        confirmSelection() {
            if (this.selected) {
                this.$dispatch('choice-confirmed', {
                    selected: this.selected
                });
                this.isOpen = false;
            }
        },
        
        cancel() {
            this.selected = @js($selected);
            this.isOpen = false;
            this.$dispatch('choice-cancelled');
        }
    }"
    @open-choice-modal.window="isOpen = true; options = $event.detail.options || []; selected = $event.detail.selected || null;"
    @close-choice-modal.window="isOpen = false"
    x-show="isOpen"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    style="display: none;"
>
    <!-- Backdrop -->
    <div 
        class="absolute inset-0 bg-black/70 backdrop-blur-sm"
        @click="cancel()"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    ></div>

    <!-- Modal Content -->
    <div 
        class="relative max-w-2xl w-full max-h-[80vh] overflow-y-auto bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 rounded-xl border-2 border-amber-500/30 shadow-2xl"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.stop
    >
        <!-- Header -->
        <div class="sticky top-0 z-10 p-6 border-b border-slate-700 bg-slate-900/95 backdrop-blur-sm">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-2xl font-outfit font-bold text-amber-400">
                        {{ $title }}
                    </h3>
                    @if($description)
                        <p class="text-slate-400 mt-2">
                            {{ $description }}
                        </p>
                    @endif
                </div>
                <button 
                    type="button"
                    @click="cancel()"
                    class="text-slate-400 hover:text-white transition-colors"
                    aria-label="Close modal"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Options List -->
        <div class="p-6 space-y-3">
            <template x-for="(option, index) in options" :key="index">
                <div
                    @click="selectOption(option.value)"
                    x-bind:class="{
                        'border-amber-400 bg-amber-500/10 ring-2 ring-amber-400/50': isSelected(option.value),
                        'border-slate-700 hover:border-amber-400/50 hover:bg-slate-800/90 cursor-pointer': !isSelected(option.value)
                    }"
                    class="relative p-4 rounded-lg border-2 transition-all duration-200"
                    role="button"
                    tabindex="0"
                    x-bind:aria-pressed="isSelected(option.value)"
                >
                    <!-- Selection Indicator -->
                    <div 
                        x-show="isSelected(option.value)"
                        class="absolute top-4 right-4"
                    >
                        <div class="w-6 h-6 rounded-full bg-amber-400 flex items-center justify-center">
                            <svg class="w-4 h-4 text-slate-900" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Option Content -->
                    <div class="pr-10">
                        <h4 class="text-lg font-outfit font-bold mb-1"
                            x-bind:class="isSelected(option.value) ? 'text-amber-400' : 'text-white'"
                            x-text="option.label"
                        ></h4>
                        <p 
                            x-show="option.description" 
                            class="text-sm text-slate-400"
                            x-text="option.description"
                        ></p>
                    </div>
                </div>
            </template>

            <!-- No Options Message -->
            <div x-show="options.length === 0" class="text-center py-8">
                <p class="text-slate-400">No options available</p>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="sticky bottom-0 p-6 border-t border-slate-700 bg-slate-900/95 backdrop-blur-sm">
            <div class="flex justify-between items-center">
                <button 
                    type="button"
                    @click="cancel()"
                    class="px-6 py-3 bg-slate-700 text-white font-semibold rounded-lg hover:bg-slate-600 transition-all duration-200"
                >
                    Cancel
                </button>
                
                <button 
                    type="button"
                    @click="confirmSelection()"
                    x-bind:disabled="!selected"
                    x-bind:class="!selected ? 'opacity-50 cursor-not-allowed' : 'hover:from-amber-600 hover:to-orange-600'"
                    class="px-8 py-3 bg-gradient-to-r from-amber-500 to-orange-500 text-white font-bold rounded-lg transition-all duration-200"
                >
                    Confirm Selection
                </button>
            </div>
        </div>
    </div>
</div>


