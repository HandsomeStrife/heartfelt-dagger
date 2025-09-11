<x-layouts.app>
    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950">
        <!-- Compact Navigation -->
        <x-sub-navigation>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a 
                        href="{{ route('dashboard') }}"
                        class="p-1.5 text-slate-400 hover:text-white hover:bg-slate-700/50 rounded-md transition-colors"
                        title="Back to dashboard"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <div>
                        <h1 class="font-outfit text-lg font-bold text-white tracking-wide">
                            {{ $title ?? 'DaggerHeart Reference' }}
                        </h1>
                        <p class="text-slate-400 text-xs">
                            Official System Reference Document
                        </p>
                    </div>
                </div>

                <!-- Page Navigation -->
                <div class="flex gap-2">
                    @php
                        $pageKeys = array_keys($pages);
                        $currentIndex = array_search($current_page ?? 'what-is-this', $pageKeys);
                        $previousPage = $currentIndex > 0 ? $pageKeys[$currentIndex - 1] : null;
                        $nextPage = $currentIndex !== false && $currentIndex < count($pageKeys) - 1 ? $pageKeys[$currentIndex + 1] : null;
                    @endphp
                    
                    @if($previousPage)
                        <a href="{{ route('reference.page', $previousPage) }}" 
                           class="inline-flex items-center px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white text-sm font-medium rounded-md transition-colors">
                            <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Previous
                        </a>
                    @endif
                    
                    @if($nextPage)
                        <a href="{{ route('reference.page', $nextPage) }}" 
                           class="inline-flex items-center px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white text-sm font-medium rounded-md transition-colors">
                            Next
                            <svg class="w-3 h-3 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </x-sub-navigation>

        <div class="px-4 sm:px-6 lg:px-8 pt-8 pb-12">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col lg:flex-row gap-6">
                    <!-- Sidebar Navigation -->
                    <div class="lg:w-80 flex-shrink-0">
                        <!-- Mobile Dropdown (visible on small screens) -->
                        <div class="lg:hidden mb-6">
                            <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-4">
                                <div x-data="{ open: false }" class="relative">
                                    <button @click="open = !open" 
                                            class="w-full flex items-center justify-between p-3 bg-slate-800/50 border border-slate-600 rounded-lg text-white hover:bg-slate-700/50 transition-colors">
                                        <span class="font-outfit text-sm font-semibold">Navigation Menu</span>
                                        <svg class="w-5 h-5 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                    
                                    <div x-show="open" 
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 transform scale-95"
                                         x-transition:enter-end="opacity-100 transform scale-100"
                                         x-transition:leave="transition ease-in duration-150"
                                         x-transition:leave-start="opacity-100 transform scale-100"
                                         x-transition:leave-end="opacity-0 transform scale-95"
                                         class="absolute top-full left-0 right-0 mt-2 bg-slate-900/95 backdrop-blur-xl border border-slate-700/50 rounded-xl p-4 z-50 max-h-96 overflow-y-auto space-y-3">
                                        @include('reference.partials.navigation-menu', ['current_page' => $current_page ?? 'what-is-this', 'pages' => $pages])
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Desktop Sidebar (hidden on small screens) -->
                        <div class="hidden lg:block sticky top-8 h-screen flex flex-col">
                            <!-- SRD Attribution -->
                            <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-3 mb-6">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 text-amber-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div>
                                        <h3 class="text-amber-400 font-outfit font-medium text-xs">DaggerHeart SRD 1.0</h3>
                                        <p class="text-amber-300/80 text-xs">
                                            © 2025 Critical Role LLC
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Page Navigation -->
                            <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-4 flex flex-col h-full">
                                <h3 class="font-outfit text-sm font-semibold text-white mb-3">All Pages</h3>
                                <div class="space-y-3 flex-1 overflow-y-auto">
                                    @include('reference.partials.navigation-menu', ['current_page' => $current_page ?? 'what-is-this', 'pages' => $pages])
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="flex-1 min-w-0">
                        @if(isset($content_type))
                            <!-- Page Content -->
                            <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl">
                                <!-- Styled Content -->
                                <div class="p-6">
                                    @if($content_type === 'blade')
                                        @include($content_view)
                                    @elseif($content_type === 'json')
                                        @include('reference.partials.json-content', [
                                            'data' => $json_data,
                                            'source' => $data_source,
                                            'title' => $title
                                        ])
                                    @elseif($content_type === 'domain-abilities')
                                        @include('reference.partials.domain-abilities', [
                                            'abilities' => $abilities,
                                            'domain_info' => $domain_info,
                                            'domain_key' => $domain_key,
                                            'title' => $title
                                        ])
                                    @endif
                                </div>
                            </div>
                        @else
                            <!-- Welcome Message for Index -->
                            <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                                <div class="text-center">
                                    <div class="w-16 h-16 bg-gradient-to-br from-amber-500 to-orange-500 rounded-xl flex items-center justify-center shadow-lg mb-4 mx-auto">
                                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </div>
                                    <h2 class="font-outfit text-2xl font-bold text-white mb-4">DaggerHeart System Reference</h2>
                                    <p class="text-slate-300 mb-6 max-w-2xl mx-auto">
                                        Complete reference documentation for the DaggerHeart tabletop RPG. 
                                        Select any page from the sidebar to begin exploring the rules and mechanics.
                                    </p>
                                    <a href="{{ route('reference.page', 'what-is-this') }}" 
                                       class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-medium rounded-lg transition-all duration-200 hover:scale-105 shadow-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                        Start Reading
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>