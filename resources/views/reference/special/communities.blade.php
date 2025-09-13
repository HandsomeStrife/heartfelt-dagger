<x-layouts.app>
    <x-sub-navigation>
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center space-x-4">
                <a href="{{ route('reference.page', 'what-is-this') }}" 
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
                                $current_page = $page ?? 'communities';
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
                            <div class="mb-8 flex items-start justify-between">
                                <h1 class="font-outfit text-3xl font-bold text-white">
                                    {{ $title }}
                                </h1>
                                
                                <div class="flex items-center space-x-2 ml-4 flex-shrink-0">
                                    <span class="px-3 py-1 bg-amber-500/20 text-amber-300 text-sm rounded-full">
                                        Character Creation
                                    </span>
                                </div>
                            </div>

                            <!-- Page Content -->
                            <div class="prose prose-invert max-w-none">
                                <p class="text-slate-300 leading-relaxed mb-8">
                                    Communities represent the social and cultural background of your character, shaping their skills and experiences.
                                </p>

                                <!-- Communities List -->
                                <div class="space-y-8">
                                    @foreach($communities ?? [] as $communityKey => $communityData)
                                        <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-2xl font-bold text-amber-400 mb-4">{{ $communityData['name'] }}</h3>
                                            <p class="text-slate-300 leading-relaxed mb-6">{{ $communityData['description'] }}</p>
                                            
                                            @if(isset($communityData['features']) && is_array($communityData['features']))
                                                <div>
                                                    <h4 class="font-outfit text-lg font-semibold text-white mb-4">Community Features</h4>
                                                    <div class="space-y-4">
                                                        @foreach($communityData['features'] as $feature)
                                                            <div class="bg-slate-700/30 rounded-lg p-4 border border-slate-600/20">
                                                                <h5 class="font-semibold text-amber-300 text-base mb-2">{{ $feature['name'] ?? 'Feature' }}</h5>
                                                                <p class="text-slate-300 text-sm leading-relaxed">{{ $feature['description'] ?? 'No description available.' }}</p>
                                                                
                                                                @if(isset($feature['options']) && is_array($feature['options']))
                                                                    <div class="mt-3">
                                                                        <h6 class="text-slate-400 text-xs font-medium mb-2 uppercase tracking-wide">Options:</h6>
                                                                        <ul class="space-y-1">
                                                                            @foreach($feature['options'] as $option)
                                                                                <li class="text-slate-400 text-sm flex items-start gap-2">
                                                                                    <span class="w-1 h-1 bg-amber-400 rounded-full mt-2 flex-shrink-0"></span>
                                                                                    <span>{{ is_string($option) ? $option : ($option['name'] ?? 'Option') }}</span>
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
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
