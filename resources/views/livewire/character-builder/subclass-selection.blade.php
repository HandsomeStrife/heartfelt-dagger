<!-- Subclass Selection Step -->
<div x-cloak>
    <!-- Step Header -->
    <div class="mb-8" x-show="!selected_subclass">
        <h2 class="text-2xl font-bold text-white mb-2 font-outfit">Choose Your Subclass</h2>
        <p class="text-slate-300 font-roboto">Select a subclass to further specialize your character's abilities and playstyle.</p>
    </div>

    <!-- Step Completion Indicator -->
    <div x-show="selected_subclass" class="mb-6 p-4 bg-gradient-to-r from-emerald-500/10 to-green-500/10 border border-emerald-500/20 rounded-xl">
        <div class="flex items-center">
            <div class="bg-emerald-500 rounded-full p-2 mr-3">
                <svg class="w-5 h-5 text-black" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            </div>
            <div>
                <p class="text-emerald-400 font-semibold">Subclass Selection Complete!</p>
                <p class="text-slate-300 text-sm">You have chosen <span x-text="selected_subclass ? ({{ json_encode($game_data['subclasses'] ?? []) }}[selected_subclass]?.name || '') : ''"></span></p>
            </div>
        </div>
    </div>

    <!-- Require class selection first -->
    <div x-show="!selected_class" class="text-center py-12">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-800/50 rounded-full mb-4">
            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.962-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
        </div>
        <h3 class="text-xl font-semibold text-white mb-2">Select a Class First</h3>
        <p class="text-slate-400 mb-6">You need to choose your character's class before selecting a subclass.</p>
        <button 
            @click="goToStep(1)" 
            class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-400 text-black font-semibold rounded-lg transition-colors"
        >
            Go to Class Selection
        </button>
    </div>

    <!-- Subclass Selection -->
    <div x-show="selected_class" class="space-y-6">
        @foreach($game_data['classes'] ?? [] as $classKey => $classData)
            <div x-show="selected_class === '{{ $classKey }}'">
                <!-- Subclass Grid -->
                @if(!empty($filtered_data['available_subclasses']))
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                        @foreach($filtered_data['available_subclasses'] as $subclassKey => $subclassData)
                            <div 
                                dusk="subclass-card-{{ $subclassKey }}"
                                x-on:click="selectSubclass('{{ $subclassKey }}')"
                                :class="{
                                    'relative group cursor-pointer transition-all duration-300 bg-gradient-to-br from-slate-800/90 to-slate-900/90 backdrop-blur border rounded-2xl overflow-hidden': true,
                                    'border-emerald-500/50 ring-2 ring-emerald-500/30 shadow-lg shadow-emerald-500/20': selected_subclass === '{{ $subclassKey }}',
                                    'border-slate-700/50 hover:border-slate-600/70 hover:shadow-lg hover:shadow-slate-500/10 transform hover:scale-[1.02]': selected_subclass !== '{{ $subclassKey }}'
                                }"
                            >
                                <!-- Selection Indicator -->
                                <template x-if="selected_subclass === '{{ $subclassKey }}'">
                                    <div class="absolute top-4 right-4 bg-emerald-500 rounded-full p-2 z-10">
                                        <svg class="w-4 h-4 text-black" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </template>

                                <div class="p-6">
                                    <!-- Subclass Header -->
                                    <div class="mb-6">
                                        <h4 class="text-2xl font-bold text-white font-outfit mb-3">{{ $subclassData['name'] }}</h4>
                                        <p class="text-slate-300 text-base leading-relaxed mb-4">{{ $subclassData['description'] }}</p>
                                        
                                        <!-- Spellcast Trait -->
                                        @if(isset($subclassData['spellcastTrait']))
                                            <div class="inline-flex items-center px-3 py-1 bg-purple-500/20 border border-purple-500/30 rounded-lg">
                                                <span class="text-purple-300 text-sm font-medium">Spellcast: {{ ucfirst($subclassData['spellcastTrait']) }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Features Section -->
                                    <div class="space-y-6">
                                        <!-- Foundation Features -->
                                        @if(isset($subclassData['foundationFeatures']))
                                            <div>
                                                <h5 class="text-emerald-400 font-semibold text-base font-outfit mb-3">Foundation Features</h5>
                                                <div class="space-y-3">
                                                    @foreach($subclassData['foundationFeatures'] as $feature)
                                                        <div class="bg-slate-800/30 border border-slate-700/50 rounded-lg p-4">
                                                            <div class="text-white font-semibold text-sm mb-2">{{ $feature['name'] }}</div>
                                                            <div class="text-slate-300 text-sm leading-relaxed prose prose-slate prose-sm max-w-none">
                                                                @markdown($feature['description'])
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Specialization Features -->
                                        @if(isset($subclassData['specializationFeatures']) && count($subclassData['specializationFeatures']) > 0)
                                            <div>
                                                <h5 class="text-amber-400 font-semibold text-base font-outfit mb-3">Specialization Features</h5>
                                                <div class="space-y-3">
                                                    @foreach($subclassData['specializationFeatures'] as $feature)
                                                        <div class="bg-gradient-to-r from-amber-500/10 to-orange-500/10 border border-amber-500/30 rounded-lg p-4">
                                                            <div class="text-white font-semibold text-sm mb-2">{{ $feature['name'] }}</div>
                                                            <div class="text-slate-300 text-sm leading-relaxed prose prose-slate prose-sm max-w-none">
                                                                @markdown($feature['description'])
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-800/50 rounded-full mb-4">
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-2">No Subclasses Available</h3>
                        <p class="text-slate-400">No subclasses found for the selected class.</p>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
