@php
    use Domain\Character\Enums\DomainEnum;
@endphp

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
                <livewire:reference-search-new :is_sidebar="false" />
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
                                $current_page = $page ?? 'domains';
                            @endphp
                            
                            <nav class="space-y-6">
                                @include('reference.partials.navigation-menu', ['current_page' => $current_page])
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
                            <div class="prose prose-invert max-w-none" data-search-body>
                                <p class="text-slate-300 leading-relaxed mb-8">
                                    The DaggerHeart core set includes 9 Domain Decks, each comprising a collection of cards granting features or special abilities expressing a particular theme.
                                </p>

                                <div class="space-y-8">
                                    @foreach($domains as $domainKey => $domain)
                                        @php
                                            $domainEnum = DomainEnum::tryFrom($domainKey);
                                            $domainColor = $domainEnum ? $domainEnum->getColor() : '#24395d';
                                        @endphp
                                        <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-6">
                                            <!-- Domain Header -->
                                            <div class="flex items-center gap-4 mb-4">
                                                <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-lg p-2" style="background-color: {{ $domainColor }};">
                                                    <x-dynamic-component component="icons.{{ $domainKey }}" class="fill-white w-8 h-8" />
                                                </div>
                                                <div>
                                                    <h2 class="font-outfit text-xl font-bold text-amber-400">{{ $domain['name'] }}</h2>
                                                    <p class="text-slate-400 text-sm">{{ ucfirst($domainKey) }} Domain</p>
                                                </div>
                                            </div>

                                            <!-- Domain Description -->
                                            <p class="text-slate-300 leading-relaxed mb-6">{{ $domain['description'] }}</p>

                                            <!-- Abilities Summary & Link -->
                                            @if(isset($domain['abilitiesByLevel']) && is_array($domain['abilitiesByLevel']))
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Domain Abilities</h3>
                                                        <p class="text-slate-400 text-sm">
                                                            {{ count($domain['abilitiesByLevel']) }} levels of abilities available
                                                        </p>
                                                    </div>
                                                    <a href="{{ route('reference.page', $domainKey . '-abilities') }}" 
                                                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium text-white transition-all duration-200 hover:scale-105 shadow-lg" 
                                                       style="background: linear-gradient(135deg, {{ $domainColor }}, color-mix(in srgb, {{ $domainColor }}, black 20%));">
                                                        <x-dynamic-component component="icons.{{ $domainKey }}" class="fill-white w-4 h-4" />
                                                        View Abilities
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                        </svg>
                                                    </a>
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
