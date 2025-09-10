<div>
    @if($show_modal)
    <div class="fixed inset-0 z-50 overflow-y-auto" 
         x-data="{ show: @entangle('show_modal') }"
         x-show="show"
         x-transition.opacity>
        
        <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm" 
             @click="$wire.closeModal()"></div>
        
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden"
                 @click.stop>
                
                <!-- Header -->
                <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-4 flex items-center justify-between">
                    <h2 class="font-outfit font-bold text-white text-xl">Level Up Character</h2>
                    <button type="button" @click="$wire.closeModal()" class="text-white/80 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <div class="p-6">
                    @if($current_step === 'tier_achievements')
                        <div class="space-y-6">
                            <h3 class="font-outfit font-bold text-slate-100 text-lg">Automatic Tier Benefits</h3>
                            @if($character && $character->level === 2)
                            <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-lg p-4">
                                <h4 class="font-semibold text-emerald-400 mb-2">Level 2 Benefits</h4>
                                <ul class="text-slate-300 text-sm space-y-1">
                                    <li>• Gain a new Experience at +2 modifier</li>
                                    <li>• Permanently increase Proficiency by +1</li>
                                </ul>
                            </div>
                            @endif
                        </div>
                    @endif

                    @if($current_step === 'advancement_selection')
                        <div class="space-y-6">
                            <h3 class="font-outfit font-bold text-slate-100 text-lg">Choose Your Advancements</h3>
                            <p class="text-slate-400 text-sm">Select exactly 2 advancements.</p>
                            
                            @if(isset($tier_options['options']))
                            <div class="grid gap-4">
                                @foreach($tier_options['options'] as $index => $option)
                                <div class="border rounded-lg p-4 cursor-pointer transition-all
                                    {{ in_array($index, $selected_advancements) ? 'border-amber-500 bg-amber-500/10' : 'border-slate-600 bg-slate-800' }}"
                                     wire:click="selectAdvancement({{ $index }})">
                                    
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <p class="text-slate-100 font-medium">{{ $option['description'] }}</p>
                                        </div>
                                        <div class="flex-shrink-0 ml-4">
                                            @if(in_array($index, $selected_advancements))
                                            <div class="w-6 h-6 bg-amber-500 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    @endif

                    @if($current_step === 'confirmation')
                        <div class="space-y-6">
                            <h3 class="font-outfit font-bold text-slate-100 text-lg">Confirm Level Up</h3>
                            <div class="space-y-4">
                                <h4 class="font-semibold text-slate-200">Selected Advancements:</h4>
                                @foreach($selected_advancements as $index => $option_index)
                                <div class="bg-slate-800 border border-slate-600 rounded-lg p-3">
                                    <span class="text-slate-100">{{ $tier_options['options'][$option_index]['description'] }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Footer -->
                <div class="bg-slate-800 px-6 py-4 flex items-center justify-between border-t border-slate-700">
                    <div>
                        @if($current_step !== 'tier_achievements')
                        <button type="button" wire:click="previousStep"
                                class="px-4 py-2 bg-slate-600 text-slate-200 hover:bg-slate-500 rounded-lg transition-colors">
                            Previous
                        </button>
                        @endif
                    </div>
                    
                    <div class="flex space-x-3">
                        <button type="button" @click="$wire.closeModal()"
                                class="px-4 py-2 bg-slate-700 text-slate-300 hover:bg-slate-600 rounded-lg transition-colors">
                            Cancel
                        </button>
                        
                        @if($current_step === 'confirmation')
                        <button type="button" wire:click="confirmLevelUp"
                                class="px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 text-white hover:from-amber-600 hover:to-orange-600 rounded-lg transition-colors font-semibold">
                            Confirm Level Up
                        </button>
                        @else
                        <button type="button" wire:click="nextStep"
                                class="px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 text-white hover:from-amber-600 hover:to-orange-600 rounded-lg transition-colors font-semibold">
                            {{ $current_step === 'tier_achievements' ? 'Continue' : 'Next' }}
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>