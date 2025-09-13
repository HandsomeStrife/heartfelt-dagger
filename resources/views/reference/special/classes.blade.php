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
                                $current_page = $page ?? 'classes';
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
                                    Each class grants access to two domains, defining the character's magical and thematic capabilities. Choose your class to determine your character's role and abilities.
                                </p>

                                <!-- Classes List -->
                                <div class="space-y-6">
                                    @foreach($classes ?? [] as $classKey => $classData)
                                        <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-6 hover:border-slate-500/50 transition-colors">
                                            <div class="flex items-start justify-between gap-6">
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-3 mb-3">
                                                        <h3 class="font-outfit text-2xl font-bold text-amber-400">{{ $classData['name'] }}</h3>
                                                        @if(isset($classData['playtest']['isPlaytest']) && $classData['playtest']['isPlaytest'])
                                                            <span class="inline-flex items-center px-2 py-1 bg-purple-600/20 text-purple-300 text-xs font-bold rounded-md border border-purple-500/30">
                                                                {{ $classData['playtest']['label'] ?? 'PLAYTEST' }}
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <!-- Domains -->
                                                    <div class="flex flex-wrap gap-2 mb-4">
                                                        @foreach($classData['domains'] ?? [] as $domain)
                                                            <span class="inline-flex items-center px-3 py-1 bg-slate-700/50 text-slate-300 text-sm font-medium rounded-lg border border-slate-600/50">{{ ucfirst($domain) }}</span>
                                                        @endforeach
                                                    </div>

                                                    <!-- Brief Description -->
                                                    <p class="text-slate-300 leading-relaxed mb-4">
                                                        {{ Str::limit($classData['description'], 200) }}
                                                    </p>

                                                    <!-- Starting Stats (Inline) -->
                                                    <div class="flex items-center gap-4 text-sm text-slate-400 mb-4">
                                                        <span><strong class="text-white">{{ $classData['startingEvasion'] ?? 10 }}</strong> Evasion</span>
                                                        <span><strong class="text-white">{{ $classData['startingHitPoints'] ?? 5 }}</strong> Hit Points</span>
                                                        <span><strong class="text-white">6</strong> Stress</span>
                                                    </div>
                                                </div>

                                                <!-- Read More Button -->
                                                <div class="flex-shrink-0">
                                                    <a href="{{ route('reference.page', $classKey) }}" 
                                                       class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-medium rounded-lg transition-colors">
                                                        Read More
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                        </svg>
                                                    </a>
                                                </div>
                                            </div>
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
