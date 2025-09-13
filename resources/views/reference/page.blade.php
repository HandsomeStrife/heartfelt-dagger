<x-layouts.app>
    <!-- Sub-navigation with search -->
    <x-sub-navigation>
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('reference.index') }}" class="text-slate-400 hover:text-white transition-colors text-sm">
                    ← Back
                </a>
            </div>
            <div class="w-96">
                <livewire:reference-search :is-sidebar="false" />
            </div>
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
                            
                            <!-- Include the navigation menu -->
                            @php
                                $current_page = $page ?? 'what-is-this';
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
                                    {{ $pageData['title'] }}
                                </h1>
                                
                                @if(isset($pageData['category']))
                                    <div class="flex items-center space-x-2 ml-4 flex-shrink-0">
                                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 text-sm rounded-full">
                                            @php
                                                // Map page categories to proper navigation section names
                                                $categoryDisplayName = match($pageData['category']) {
                                                    'gm-guidance' => 'GM Resources',
                                                    'introduction' => 'Introduction',
                                                    'character-creation' => 'Character Creation',
                                                    'core-mechanics' => 'Core Mechanics',
                                                    'equipment' => 'Equipment',
                                                    'reference' => 'Reference',
                                                    'domain-abilities' => 'Domain Abilities',
                                                    default => ucwords(str_replace('-', ' ', $pageData['category']))
                                                };
                                            @endphp
                                            {{ $categoryDisplayName }}
                                        </span>
                                        
                                        @if(isset($pageData['subcategory']))
                                            <span class="px-3 py-1 bg-blue-500/20 text-blue-300 text-sm rounded-full">
                                                {{ ucwords(str_replace('-', ' ', $pageData['subcategory'])) }}
                                            </span>
                                        @endif
                                        
                                        @if(isset($pageData['tier']))
                                            <span class="px-3 py-1 bg-purple-500/20 text-purple-300 text-sm rounded-full">
                                                {{ $pageData['tier'] }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <!-- Page Content -->
                            <div class="prose prose-invert max-w-none">
                                @foreach($pageData['processed_sections'] as $section)
                                    @if($section['title'])
                                        <div id="{{ $section['anchor'] }}" class="scroll-mt-8">
                                    @endif
                                    
                                    {!! $section['content'] !!}
                                    
                                    @if($section['title'])
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            <!-- Page Footer -->
                            <div class="mt-12 pt-8 border-t border-slate-700">
                                <div class="flex items-center justify-between text-sm text-slate-400">
                                    <div>
                                        Category: <span class="text-amber-400">{{ ucwords(str_replace('-', ' ', $pageData['category'])) }}</span>
                                    </div>
                                    <div>
                                        Word count: {{ number_format(array_sum(array_column($pageData['processed_sections'], 'word_count'))) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Smooth scrolling for anchor links
        document.addEventListener('DOMContentLoaded', function() {
            // Handle initial hash in URL
            if (window.location.hash) {
                setTimeout(() => {
                    const element = document.querySelector(window.location.hash);
                    if (element) {
                        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }, 100);
            }

            // Handle anchor link clicks
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    const targetElement = document.querySelector(targetId);
                    
                    if (targetElement) {
                        targetElement.scrollIntoView({ 
                            behavior: 'smooth', 
                            block: 'start' 
                        });
                        
                        // Update URL without triggering navigation
                        history.pushState(null, null, targetId);
                    }
                });
            });
        });
    </script>
    @endpush
</x-layouts.app>
