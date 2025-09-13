<x-layouts.app>
    <x-sub-navigation>
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center space-x-4">
                <a href="{{ route('reference.page', 'classes') }}" 
                   class="text-slate-400 hover:text-white transition-colors text-sm">
                    ← Back
                </a>
            </div>
            
            <div class="flex-1 max-w-md mx-4">
                <livewire:reference-search :is_sidebar="false" />
            </div>
            
            <div class="w-16"></div> <!-- Spacer for centering -->
        </div>
    </x-sub-navigation>

    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950">
        <div class="container mx-auto px-4 py-8">
            <div class="w-full mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    <!-- Sidebar -->
                    <div class="lg:col-span-3">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-6">
                            <h3 class="font-outfit text-lg font-semibold text-white mb-4">Reference Pages</h3>
                            
                            @php
                                $current_page = $page ?? $class_key;
                            @endphp
                            
                            <nav class="space-y-6">
                                @include('reference.partials.navigation-menu', ['pages' => $pages, 'current_page' => $current_page])
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-8">
                            <!-- Page Header -->
                            <div class="mb-12 flex items-start justify-between">
                                <!-- Class Header Section -->
                                <div class="flex items-start gap-6">
                                    <!-- Banner -->
                                    <div class="class-banner-md-width flex-shrink-0">
                                        <x-class-banner className="{{ $class_key }}" class="absolute top-0 left-0" size="md" />
                                    </div>
                                    <!-- Text Content -->
                                    <div class="flex-1 min-w-0 mt-16">
                                        <h1 class="font-outfit text-3xl font-bold text-white mb-3">{{ $title }}</h1>
                                        
                                        <div class="flex flex-wrap items-center gap-2">
                                            @foreach($class_info['domains'] ?? [] as $domain)
                                                <span class="inline-flex items-center px-3 py-1 bg-slate-700/50 text-slate-300 text-sm font-medium rounded-lg border border-slate-600/50">{{ ucfirst($domain) }}</span>
                                            @endforeach
                                            
                                            @if(isset($class_info['playtest']['isPlaytest']) && $class_info['playtest']['isPlaytest'])
                                                <span class="inline-flex items-center px-3 py-1 bg-purple-600/20 text-purple-300 text-sm font-bold rounded-lg border border-purple-500/30">
                                                    {{ $class_info['playtest']['label'] ?? 'PLAYTEST' }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-2 ml-4 flex-shrink-0">
                                    <span class="px-3 py-1 bg-amber-500/20 text-amber-300 text-sm rounded-full">
                                        Character Creation
                                    </span>
                                </div>
                            </div>

                            <!-- Class Description -->
                            <div class="prose prose-invert max-w-none mb-8">
                                <p class="text-slate-300 text-lg leading-relaxed">{{ $class_info['description'] }}</p>
                            </div>

            <!-- Starting Stats -->
            <div class="mb-8">
                <h2 class="font-outfit text-xl font-bold text-white mb-4">Starting Statistics</h2>
                <div class="bg-slate-800/50 rounded-lg p-4 border border-slate-700/50">
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-8">
                            <div>
                                <span class="text-slate-400 font-medium">Evasion:</span>
                                <span class="text-white font-bold text-lg ml-2">{{ $class_info['startingEvasion'] ?? 10 }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 font-medium">Hit Points:</span>
                                <span class="text-white font-bold text-lg ml-2">{{ $class_info['startingHitPoints'] ?? 5 }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 font-medium">Stress:</span>
                                <span class="text-white font-bold text-lg ml-2">6</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                            <!-- Hope Feature -->
                            @if(isset($class_info['hopeFeature']))
                                <div class="mb-8">
                                    <h2 class="font-outfit text-xl font-bold text-white mb-4">Hope Feature</h2>
                                    <div class="bg-gradient-to-r from-amber-500/10 to-orange-500/10 border border-amber-500/30 rounded-xl p-6">
                                        <div class="flex items-center justify-between gap-4 mb-4">
                                            <h3 class="text-amber-400 font-bold text-xl">{{ $class_info['hopeFeature']['name'] }}</h3>
                                            <span class="bg-amber-500/20 text-amber-300 text-sm font-medium px-4 py-2 rounded-full">{{ $class_info['hopeFeature']['hopeCost'] }} Hope</span>
                                        </div>
                                        <p class="text-slate-300 leading-relaxed">{{ $class_info['hopeFeature']['description'] }}</p>
                                    </div>
                                </div>
                            @endif

            <!-- Class Features -->
            @if(!empty($class_info['classFeatures']))
                <div class="mb-8">
                    <h2 class="font-outfit text-xl font-bold text-white mb-4">Class Features</h2>
                    <div class="space-y-6">
                        @foreach($class_info['classFeatures'] as $feature)
                            <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-6">
                                <h3 class="text-white font-semibold text-lg mb-3">{{ $feature['name'] }}</h3>
                                <p class="text-slate-300 leading-relaxed">{{ $feature['description'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Subclasses -->
            @if(!empty($class_subclasses))
                <div class="mb-8">
                    <h2 class="font-outfit text-xl font-bold text-white mb-4">Subclasses</h2>
                    <div class="space-y-6">
                        @foreach($class_subclasses as $subclassKey => $subclass)
                            <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-6">
                                <h3 class="text-amber-400 font-bold text-lg mb-3">{{ $subclass['name'] ?? ucfirst($subclassKey) }}</h3>
                                @if(isset($subclass['description']))
                                    <p class="text-slate-300 leading-relaxed mb-4">{{ $subclass['description'] }}</p>
                                @endif
                                
                                @if(isset($subclass['features']) && is_array($subclass['features']))
                                    <div>
                                        <h4 class="text-white font-medium text-sm mb-2">Subclass Features:</h4>
                                        <ul class="space-y-2">
                                            @foreach($subclass['features'] as $feature)
                                                <li class="text-slate-400 text-sm">
                                                    <span class="text-amber-300 font-medium">{{ $feature['name'] ?? 'Feature' }}:</span>
                                                    {{ $feature['description'] ?? '' }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

                            <!-- Starting Items -->
                            @if(isset($class_info['classItems']))
                                <div class="mb-8">
                                    <h2 class="font-outfit text-xl font-bold text-white mb-4">Starting Items</h2>
                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                        <p class="text-slate-300 leading-relaxed">{{ $class_info['classItems'] }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
