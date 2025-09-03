<!-- Class Selection Step -->
<div x-cloak>
    <!-- Step Header -->
    <div class="mb-8" x-show="!selected_class">
        <h2 class="text-2xl font-bold text-white mb-2 font-outfit">Choose a Class</h2>
        <p class="text-slate-300 font-roboto">Select your character's class to define their core abilities and role.</p>
    </div>

    <!-- Step Completion Indicator -->
    <div x-show="selected_class" class="mb-6 p-4 bg-gradient-to-r from-emerald-500/10 to-green-500/10 border border-emerald-500/20 rounded-xl">
        <div class="flex items-center">
            <div class="bg-emerald-500 rounded-full p-2 mr-3">
                <svg class="w-5 h-5 text-black" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            </div>
            <div>
                <p class="text-emerald-400 font-semibold">Class Selection Complete!</p>
                <p class="text-slate-300 text-sm">You have chosen <span x-text="selectedClassData?.name || ''"></span></p>
            </div>
        </div>
    </div>

    <!-- Class Selection Grid - Show When No Class Selected -->
    <div x-show="!hasSelectedClass">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4 sm:gap-6">
            @foreach($game_data['classes'] ?? [] as $classKey => $classData)
                <div 
                    pest="class-card-{{ $classKey }}"
                    @click="selectClass('{{ $classKey }}')"
                    class="relative group cursor-pointer transition-all duration-300 transform hover:scale-105 bg-gradient-to-br from-slate-800/90 to-slate-900/90 backdrop-blur border border-slate-700/50 hover:border-slate-600/70 hover:shadow-lg hover:shadow-slate-500/10 rounded-2xl p-4 sm:p-6"
                >
                    <div class="flex flex-row gap-4">
                        <div class="class-banner-sm-width">   
                            <x-class-banner className="{{ $classKey }}" class="absolute top-0 left-0" />
                        </div>

                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-xl font-bold text-white font-outfit">{{ $classData['name'] }}</h3>
                                @if(isset($classData['playtest']['isPlaytest']) && $classData['playtest']['isPlaytest'])
                                    <span class="inline-flex items-center px-2 py-1 bg-purple-600/20 text-purple-300 text-xs font-bold rounded-md border border-purple-500/30">
                                        {{ $classData['playtest']['label'] ?? 'PLAYTEST' }}
                                    </span>
                                @endif
                            </div>

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
    
    <!-- Selected Class Details - Full Width (using Blade for banners, AlpineJS for show/hide) -->
    <div x-show="hasSelectedClass" class="w-full">
        @foreach($game_data['classes'] ?? [] as $classKey => $classData)
            <div x-show="selected_class === '{{ $classKey }}'" class="bg-gradient-to-br from-slate-800/90 to-slate-900/90 backdrop-blur border border-amber-500/50 rounded-2xl p-6">
                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">
                    <!-- Left Column: Core Info -->
                    <div class="col-span-1 xl:col-span-4">
                        <!-- Class Header Section -->
                        <div class="flex items-start gap-6 mb-8">
                            <div class="class-banner-md-width flex-shrink-0">
                                <x-class-banner className="{{ $classKey }}" class="absolute top-0 left-0" size="md" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-4 mb-2">
                                    <h3 class="text-3xl font-bold text-white font-outfit">{{ $classData['name'] }}</h3>
                                    @if(isset($classData['playtest']['isPlaytest']) && $classData['playtest']['isPlaytest'])
                                        <span class="inline-flex items-center px-3 py-1 bg-purple-600/20 text-purple-300 text-sm font-bold rounded-lg border border-purple-500/30">
                                            {{ $classData['playtest']['label'] ?? 'PLAYTEST' }}
                                        </span>
                                    @endif
                                </div>
                                <div class="flex flex-wrap gap-2 mb-4">
                                    @foreach($classData['domains'] ?? [] as $domain)
                                        <span class="inline-flex items-center px-3 py-1 bg-slate-700/50 text-slate-300 text-sm font-medium rounded-lg border border-slate-600/50">{{ ucfirst($domain) }}</span>
                                    @endforeach
                                </div>
                                <!-- Description -->
                                <p class="text-slate-300 text-base leading-relaxed">{{ $classData['description'] }}</p>

                                @if(isset($classData['classItems']))
                                    <p class="text-slate-300 text-xs leading-relaxed mt-6">
                                        Starting Items: {{ $classData['classItems'] }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Stats & Items -->
                    <div class="col-span-1 xl:col-span-1 space-y-4">
                        <!-- Change Class Button -->
                        <div class="flex justify-end mb-4">
                            <button 
                                pest="change-class-button"
                                @click="selectClass(null)"
                                class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg border border-slate-600 hover:border-slate-500 transition-all duration-200 text-sm font-medium"
                            >
                                Change Class
                            </button>
                        </div>
                        
                        <!-- Starting Stats -->
                        <div class="mb-6">
                            <div class="space-y-2 mt-12">
                                <div class="flex justify-between items-center bg-slate-800/50 rounded-lg px-3 py-2 border border-slate-700/50">
                                    <span class="text-slate-400 font-medium text-sm">Evasion</span>
                                    <span class="text-white font-bold">{{ $classData['startingEvasion'] ?? 10 }}</span>
                                </div>
                                <div class="flex justify-between items-center bg-slate-800/50 rounded-lg px-3 py-2 border border-slate-700/50">
                                    <span class="text-slate-400 font-medium text-sm">Hit Points</span>
                                    <span class="text-white font-bold">{{ $classData['startingHitPoints'] ?? 5 }}</span>
                                </div>
                                <div class="flex justify-between items-center bg-slate-800/50 rounded-lg px-3 py-2 border border-slate-700/50">
                                    <span class="text-slate-400 font-medium text-sm">Stress</span>
                                    <span class="text-white font-bold">6</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Features Section - Two Column -->
                <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Hope Feature -->
                    @if(isset($classData['hopeFeature']))
                        <div>
                            <h4 class="text-lg font-semibold text-white font-outfit mb-3">Hope Feature</h4>
                            <div class="bg-gradient-to-r from-amber-500/10 to-orange-500/10 border border-amber-500/30 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-amber-400 font-bold text-lg">{{ $classData['hopeFeature']['name'] }}</span>
                                    <span class="bg-amber-500/20 text-amber-300 text-sm font-medium px-3 py-1 rounded-full">{{ $classData['hopeFeature']['hopeCost'] }} Hope</span>
                                </div>
                                <p class="text-slate-300 text-sm leading-relaxed">{{ $classData['hopeFeature']['description'] }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Class Features -->
                    @if(!empty($classData['classFeatures']))
                        <div>
                            <h4 class="text-lg font-semibold text-white font-outfit mb-3">Class Features</h4>
                            <div class="space-y-3">
                                @foreach($classData['classFeatures'] as $feature)
                                    <div class="bg-slate-800/50 border border-slate-700/50 rounded-lg p-4">
                                        <h5 class="text-white font-semibold text-base mb-2">{{ $feature['name'] }}</h5>
                                        <p class="text-slate-300 text-sm leading-relaxed">{{ $feature['description'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>