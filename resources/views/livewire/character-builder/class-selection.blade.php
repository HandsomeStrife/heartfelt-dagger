<!-- Class Selection Step -->
<div x-cloak>
    <!-- Step Header -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-white mb-2 font-outfit">Choose a Class</h2>
        <p class="text-slate-300 font-roboto">Select your character's class and subclass to define their abilities and role.</p>
    </div>

    <!-- Step Completion Indicator -->
    <div x-show="selected_class && selected_subclass" class="my-6 p-4 bg-gradient-to-r from-emerald-500/10 to-green-500/10 border border-emerald-500/20 rounded-xl">
        <div class="flex items-center">
            <div class="bg-emerald-500 rounded-full p-2 mr-3">
                <svg class="w-5 h-5 text-black" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            </div>
            <div>
                <p class="text-emerald-400 font-semibold">Class Selection Complete!</p>
                <p class="text-slate-300 text-sm">You have chosen <span x-text="selected_class ? ({{ json_encode($game_data['classes'] ?? []) }}[selected_class]?.name || '') : ''"></span><span x-show="selected_subclass"> - </span><span x-text="selected_subclass ? ({{ json_encode($game_data['subclasses'] ?? []) }}[selected_subclass]?.name || '') : ''"></span></p>
            </div>
        </div>
    </div>

    <!-- Class Selection Grid - Show When No Class Selected -->
    <div x-show="!hasSelectedClass">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            @foreach($game_data['classes'] ?? [] as $classKey => $classData)
                <div 
                    dusk="class-card-{{ $classKey }}"
                    x-on:click="selectClass('{{ $classKey }}')"
                    class="relative group cursor-pointer transition-all duration-300 transform hover:scale-105 bg-gradient-to-br from-slate-800/90 to-slate-900/90 backdrop-blur border border-slate-700/50 hover:border-slate-600/70 hover:shadow-lg hover:shadow-slate-500/10 rounded-2xl p-4 sm:p-6"
                >
                    <div class="flex flex-row gap-4">
                        <div class="w-48">
                            @if(file_exists(public_path("img/banners/{$classKey}.webp")))
                                <img 
                                    src="{{ asset("img/banners/{$classKey}.webp") }}" 
                                    alt="{{ $classData['name'] }}" 
                                    class="w-full h-auto object-cover opacity-80 group-hover:opacity-100 transition-opacity duration-300"
                                >
                            @endif
                        </div>

                        <div>
                            <h3 class="text-xl font-bold text-white font-outfit">{{ $classData['name'] }}</h3>

                            <!-- Class Info Preview -->
                            <div class="space-y-3">
                                <!-- Description -->
                                <p class="text-slate-300 text-sm line-clamp-4 leading-relaxed">
                                    {{ Str::limit($classData['description'], 200) }}
                                </p>

                                <!-- Domains -->
                                <div class="flex flex-wrap gap-1">
                                    @foreach($classData['domains'] ?? [] as $domain)
                                        <span class="inline-flex items-center px-2 py-1 bg-slate-700/50 text-slate-300 text-xs font-medium rounded-md">
                                            {{ ucfirst($domain) }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    
    <!-- Two-Column Layout - Show When Class Selected -->
    <div x-show="hasSelectedClass">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
            <!-- Left Column: Pre-loaded Class Details for All Classes -->
            <div class="space-y-6">
                @foreach($game_data['classes'] ?? [] as $classKey => $classData)
                    <div x-show="selected_class === '{{ $classKey }}'" class="bg-gradient-to-br from-slate-800/90 to-slate-900/90 backdrop-blur border border-amber-500/50 rounded-2xl p-4 sm:p-6">
                        <!-- Change Class Button -->
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 sm:mb-6 gap-3">
                            <h3 class="text-xl font-bold text-white font-outfit">Selected Class</h3>
                            <button 
                                dusk="change-class-button"
                                x-on:click="selectClass(null)"
                                class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg border border-slate-600 hover:border-slate-500 transition-all duration-200 text-sm font-medium w-full sm:w-auto"
                            >
                                Change Class
                            </button>
                        </div>
                        
                        <div class="flex flex-row gap-4">
                            <div class="w-96">
                                @if(file_exists(public_path("img/banners/{$classKey}.webp")))
                                    <img 
                                        src="{{ asset("img/banners/{$classKey}.webp") }}" 
                                        alt="{{ $classData['name'] }}" 
                                        class="w-full h-auto object-cover opacity-90"
                                    >
                                @endif                    
                            </div>

                            <div>
                                <h2 class="text-2xl font-bold text-white font-outfit">{{ $classData['name'] }}</h2>
                                <!-- Full Description -->
                                <p class="text-slate-300 text-base leading-relaxed mb-6">
                                    {{ $classData['description'] }}
                                </p>
                            </div>
                        </div>


                        <!-- Domains -->
                        <div class="mb-6">
                            <h4 class="text-lg font-semibold text-white font-outfit mb-3">Domains</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach($classData['domains'] ?? [] as $domain)
                                    <span class="inline-flex items-center px-3 py-2 bg-slate-700/50 text-slate-300 text-sm font-medium rounded-lg border border-slate-600/50">
                                        {{ ucfirst($domain) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <!-- Starting Stats -->
                        <div class="mb-6">
                            <h4 class="text-lg font-semibold text-white font-outfit mb-3">Starting Stats</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="flex justify-between items-center bg-slate-800/50 rounded-lg px-4 py-3 border border-slate-700/50">
                                    <span class="text-slate-400 font-medium">Evasion</span>
                                    <span class="text-white font-bold text-lg">{{ $classData['startingEvasion'] ?? 10 }}</span>
                                </div>
                                <div class="flex justify-between items-center bg-slate-800/50 rounded-lg px-4 py-3 border border-slate-700/50">
                                    <span class="text-slate-400 font-medium">Hit Points</span>
                                    <span class="text-white font-bold text-lg">{{ $classData['startingHitPoints'] ?? 5 }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Class Items -->
                        @if(isset($classData['classItems']))
                            <div class="mb-6">
                                <h4 class="text-lg font-semibold text-white font-outfit mb-3">Starting Items</h4>
                                <p class="text-slate-300 text-sm bg-slate-800/50 rounded-lg p-3 border border-slate-700/50">
                                    {{ $classData['classItems'] }}
                                </p>
                            </div>
                        @endif

                        <!-- Hope Feature -->
                        @if(isset($classData['hopeFeature']))
                            <div class="mb-6">
                                <h4 class="text-lg font-semibold text-white font-outfit mb-3">Hope Feature</h4>
                                <div class="bg-gradient-to-r from-amber-500/10 to-orange-500/10 border border-amber-500/30 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-amber-400 font-bold text-base">{{ $classData['hopeFeature']['name'] }}</span>
                                        <span class="text-amber-300 text-sm font-medium">{{ $classData['hopeFeature']['hopeCost'] }} Hope</span>
                                    </div>
                                    <p class="text-slate-300 text-sm leading-relaxed">
                                        {{ $classData['hopeFeature']['description'] }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        <!-- Class Features -->
                        @if(!empty($classData['classFeatures']))
                            <div class="mb-6">
                                <h4 class="text-lg font-semibold text-white font-outfit mb-3">Class Features</h4>
                                <div class="space-y-3">
                                    @foreach($classData['classFeatures'] as $feature)
                                        <div class="bg-slate-800/50 border border-slate-700/50 rounded-lg p-4">
                                            <h5 class="text-white font-semibold text-sm mb-2">{{ $feature['name'] }}</h5>
                                            <p class="text-slate-300 text-sm leading-relaxed">{{ $feature['description'] }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            
            <!-- Right Column: Pre-loaded Subclass Selection for All Classes -->
            <div class="space-y-6">
                @foreach($game_data['classes'] ?? [] as $classKey => $classData)
                    <div x-show="selected_class === '{{ $classKey }}'">
                        <h3 class="text-xl font-bold text-white font-outfit mb-6">Choose Your Subclass</h3>
                        
                        <!-- Subclass Grid -->
                        @if(!empty($filtered_data['available_subclasses']))
                            <div class="space-y-4">
                                @foreach($filtered_data['available_subclasses'] as $subclassKey => $subclassData)
                                        <div 
                                            dusk="subclass-card-{{ $subclassKey }}"
                                            x-on:click="selectSubclass('{{ $subclassKey }}')"
                                            :class="{
                                                'relative group cursor-pointer transition-all duration-300 bg-gradient-to-br from-slate-800/90 to-slate-900/90 backdrop-blur border rounded-2xl p-6': true,
                                                'border-emerald-500/50 ring-2 ring-emerald-500/30 shadow-lg shadow-emerald-500/20': selected_subclass === '{{ $subclassKey }}',
                                                'border-slate-700/50 hover:border-slate-600/70 hover:shadow-lg hover:shadow-slate-500/10': selected_subclass !== '{{ $subclassKey }}'
                                            }"
                                        >
                                            <!-- Subclass Header -->
                                            <div class="flex items-start justify-between mb-4">
                                                <div>
                                                    <h4 class="text-xl font-bold text-white font-outfit mb-1">{{ $subclassData['name'] }}</h4>
                                                    <p class="text-slate-400 text-sm">{{ $subclassData['description'] }}</p>
                                                </div>
                                                
                                                <template x-if="selected_subclass === '{{ $subclassKey }}'">
                                                    <div class="bg-emerald-500 rounded-full p-1.5 flex-shrink-0">
                                                        <svg class="w-4 h-4 text-black" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        </svg>
                                                    </div>
                                                </template>
                                            </div>

                                            <!-- Spellcast Trait -->
                                            @if(isset($subclassData['spellcastTrait']))
                                                <div class="mb-4">
                                                    <div class="inline-flex items-center px-3 py-1 bg-purple-500/20 border border-purple-500/30 rounded-lg">
                                                        <span class="text-purple-300 text-sm font-medium">Spellcast: {{ ucfirst($subclassData['spellcastTrait']) }}</span>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="space-y-3">
                                                <!-- Foundation Features -->
                                                @if(isset($subclassData['foundationFeatures']))
                                                    <div class="space-y-3">
                                                        <h5 class="text-emerald-400 font-semibold text-sm font-outfit">Foundation Features</h5>
                                                        @foreach($subclassData['foundationFeatures'] as $feature)
                                                            <div class="bg-slate-800/50 rounded-lg p-3">
                                                                <div class="text-white font-medium text-sm mb-1">{{ $feature['name'] }}</div>
                                                                <div class="text-slate-300 text-xs leading-relaxed prose">
                                                                    @markdown($feature['description'])
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                <!-- Specialization Preview -->
                                                @if(isset($subclassData['specializationFeatures']) && count($subclassData['specializationFeatures']) > 0)
                                                    <div class="space-y-3">
                                                        <h5 class="text-emerald-400 font-semibold text-sm font-outfit">Specialization Features</h5>
                                                        @foreach($subclassData['specializationFeatures'] as $feature)
                                                            <div class="bg-slate-800/50 rounded-lg p-3">
                                                                <div class="text-white font-medium text-sm mb-1">{{ $feature['name'] }}</div>
                                                                <div class="text-slate-300 text-xs leading-relaxed prose">
                                                                    @markdown($feature['description'])
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>