@props([
    'show' => false,
    'level',
    'selectedTraits' => [],
    'markedTraits' => [],
])

@php
    $traits = [
        'agility' => [
            'name' => 'Agility',
            'description' => 'Quick movement, dexterity, speed',
            'icon' => 'M13 10V3L4 14h7v7l9-11h-7z', // Lightning bolt
        ],
        'strength' => [
            'name' => 'Strength',
            'description' => 'Physical power, carrying capacity, melee prowess',
            'icon' => 'M12 2L2 19.5h20L12 2z M12 8v6', // Triangle with line
        ],
        'finesse' => [
            'name' => 'Finesse',
            'description' => 'Precision, accuracy, careful manipulation',
            'icon' => 'M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5', // Precision layers
        ],
        'instinct' => [
            'name' => 'Instinct',
            'description' => 'Intuition, perception, animal handling',
            'icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z', // Eye
        ],
        'presence' => [
            'name' => 'Presence',
            'description' => 'Charisma, leadership, social influence',
            'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', // Users
        ],
        'knowledge' => [
            'name' => 'Knowledge',
            'description' => 'Intelligence, memory, academic learning',
            'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', // Book
        ],
    ];
@endphp

<div 
    x-data="{
        isOpen: @js($show),
        level: @js($level),
        selectedTraits: @js($selectedTraits),
        markedTraits: @js($markedTraits),
        
        toggleTrait(traitKey) {
            const index = this.selectedTraits.indexOf(traitKey);
            if (index > -1) {
                this.selectedTraits.splice(index, 1);
            } else if (this.selectedTraits.length < 2) {
                this.selectedTraits.push(traitKey);
            }
        },
        
        isSelected(traitKey) {
            return this.selectedTraits.includes(traitKey);
        },
        
        isMarked(traitKey) {
            return this.markedTraits.includes(traitKey);
        },
        
        canSelectTrait(traitKey) {
            return !this.isMarked(traitKey);
        },
        
        isComplete() {
            return this.selectedTraits.length === 2;
        },
        
        confirmSelection() {
            if (this.isComplete()) {
                this.$dispatch('trait-selection-confirmed', {
                    level: this.level,
                    traits: this.selectedTraits
                });
                this.isOpen = false;
            }
        },
        
        cancel() {
            this.selectedTraits = @js($selectedTraits);
            this.isOpen = false;
            this.$dispatch('trait-selection-cancelled');
        }
    }"
    @open-trait-selector.window="isOpen = true"
    @close-trait-selector.window="isOpen = false"
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
        class="relative max-w-4xl w-full max-h-[90vh] overflow-y-auto bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 rounded-xl border-2 border-amber-500/30 shadow-2xl"
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
                        Select 2 Traits to Increase
                    </h3>
                    <p class="text-slate-400 mt-2">
                        Choose two different traits to increase by +1 for Level <span x-text="level"></span>
                    </p>
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
            
            <!-- Progress Indicator -->
            <div class="mt-4 flex items-center space-x-3">
                <div class="flex-1 h-2 bg-slate-700 rounded-full overflow-hidden">
                    <div 
                        class="h-full bg-gradient-to-r from-amber-500 to-orange-500 transition-all duration-300"
                        :style="`width: ${(selectedTraits.length / 2) * 100}%`"
                    ></div>
                </div>
                <div class="text-sm font-bold" :class="isComplete() ? 'text-green-400' : 'text-amber-400'">
                    <span x-text="selectedTraits.length"></span>/2
                </div>
            </div>
        </div>

        <!-- Trait Grid -->
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($traits as $key => $trait)
                    <div
                        x-on:click="if (canSelectTrait('{{ $key }}')) { toggleTrait('{{ $key }}') }"
                        x-on:keydown.enter.prevent="if (canSelectTrait('{{ $key }}')) { toggleTrait('{{ $key }}') }"
                        x-on:keydown.space.prevent="if (canSelectTrait('{{ $key }}')) { toggleTrait('{{ $key }}') }"
                        x-bind:class="{
                            'border-amber-400 bg-amber-500/10 ring-2 ring-amber-400/50': isSelected('{{ $key }}'),
                            'border-red-500 bg-red-500/10 opacity-60 cursor-not-allowed': isMarked('{{ $key }}'),
                            'border-slate-700 hover:border-amber-400/50 hover:bg-slate-800/90 hover:scale-105 cursor-pointer': !isSelected('{{ $key }}') && !isMarked('{{ $key }}')
                        }"
                        class="relative p-5 rounded-lg border-2 transition-all duration-200 group"
                        role="button"
                        tabindex="0"
                        x-bind:aria-pressed="isSelected('{{ $key }}')"
                        x-bind:aria-disabled="isMarked('{{ $key }}')"
                        aria-label="Select {{ $trait['name'] }} trait{{ $trait['marked'] ?? false ? ' (marked, cannot be selected in this tier)' : '' }}"
                    >
                        <!-- Selection Indicator -->
                        <div 
                            x-show="isSelected('{{ $key }}')"
                            class="absolute top-3 right-3"
                        >
                            <div class="w-6 h-6 rounded-full bg-amber-400 flex items-center justify-center">
                                <svg class="w-4 h-4 text-slate-900" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Marked Indicator -->
                        <div 
                            x-show="isMarked('{{ $key }}')"
                            class="absolute top-3 right-3"
                        >
                            <div class="px-2 py-1 rounded-full bg-red-500 text-white text-xs font-bold">
                                Marked
                            </div>
                        </div>

                        <!-- Trait Icon -->
                        <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-slate-800 mb-3"
                             x-bind:class="isSelected('{{ $key }}') ? 'bg-amber-500/20' : 'bg-slate-800'"
                        >
                            <svg class="w-6 h-6" 
                                 x-bind:class="isSelected('{{ $key }}') ? 'text-amber-400' : 'text-slate-400'"
                                 fill="none" 
                                 stroke="currentColor" 
                                 viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $trait['icon'] }}"/>
                            </svg>
                        </div>

                        <!-- Trait Info -->
                        <div>
                            <h4 class="text-lg font-outfit font-bold mb-1"
                                x-bind:class="isSelected('{{ $key }}') ? 'text-amber-400' : 'text-white'"
                            >
                                {{ $trait['name'] }}
                            </h4>
                            <p class="text-sm text-slate-400">
                                {{ $trait['description'] }}
                            </p>
                        </div>

                        <!-- Marked Explanation -->
                        <div x-show="isMarked('{{ $key }}')" class="mt-3 text-xs text-red-400">
                            This trait is marked and cannot be increased again this tier
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Help Text -->
            <div class="mt-6 p-4 rounded-lg bg-blue-500/10 border border-blue-500/30">
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-sm text-blue-300 space-y-1">
                        <p><strong>Trait Advancement Rules:</strong></p>
                        <ul class="list-disc list-inside space-y-1 text-blue-200/80">
                            <li>Select exactly 2 different traits to increase by +1 each</li>
                            <li>You cannot select a trait that's already marked in this tier</li>
                            <li>Once selected, that trait will be marked for this tier</li>
                            <li>Marked traits are cleared at levels 5 and 8 (tier transitions)</li>
                        </ul>
                    </div>
                </div>
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
                    x-bind:disabled="!isComplete()"
                    x-bind:class="!isComplete() ? 'opacity-50 cursor-not-allowed' : 'hover:from-amber-600 hover:to-orange-600'"
                    class="px-8 py-3 bg-gradient-to-r from-amber-500 to-orange-500 text-white font-bold rounded-lg transition-all duration-200"
                >
                    Confirm Trait Selection
                </button>
            </div>
        </div>
    </div>
</div>

