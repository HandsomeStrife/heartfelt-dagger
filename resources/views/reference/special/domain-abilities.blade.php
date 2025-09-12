@php
    use Domain\Character\Enums\DomainEnum;
    
    $domainEnum = DomainEnum::tryFrom($domain_key);
    $domainColor = $domainEnum ? $domainEnum->getColor() : '#24395d';
@endphp

<x-layouts.app>
    <x-sub-navigation>
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center space-x-4">
                <a href="{{ route('reference.page', 'domains') }}" 
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
            <div class="max-w-6xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    <!-- Sidebar -->
                    <div class="lg:col-span-3">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-6">
                            <h3 class="font-outfit text-lg font-semibold text-white mb-4">Reference Pages</h3>
                            
                            @php
                                $current_page = $page ?? $domain_key . '-abilities';
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
                                <!-- Domain Header -->
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 rounded-xl flex items-center justify-center shadow-lg p-3" style="background-color: {{ $domainColor }};">
                                        <x-dynamic-component component="icons.{{ $domain_key }}" class="fill-white w-10 h-10" />
                                    </div>
                                    <div>
                                        <h1 class="font-outfit text-3xl font-bold text-white">{{ $title }}</h1>
                                        <p class="text-slate-400 text-sm mt-1">{{ ucfirst($domain_key) }} Domain</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-2 ml-4 flex-shrink-0">
                                    <span class="px-3 py-1 bg-amber-500/20 text-amber-300 text-sm rounded-full">
                                        Domain Abilities
                                    </span>
                                </div>
                            </div>

                            @if($domain_info && isset($domain_info['description']))
                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4 mb-8">
                                    <p class="text-slate-300 leading-relaxed">{{ $domain_info['description'] }}</p>
                                </div>
                            @endif

                            <!-- Abilities by Level -->
                            @if(count($abilities_by_level) > 0)
                                @foreach($abilities_by_level as $level => $levelAbilities)
                                    <div class="mb-12">
                                        <h2 class="font-outfit text-2xl font-bold text-white mb-6 flex items-center gap-3">
                                            <span class="px-3 py-1 rounded-lg text-sm font-bold" style="background-color: {{ $domainColor }}; color: white;">
                                                Level {{ $level }}
                                            </span>
                                            <span class="text-slate-400 text-base font-normal">
                                                ({{ count($levelAbilities) }} {{ count($levelAbilities) === 1 ? 'ability' : 'abilities' }})
                                            </span>
                                        </h2>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            @foreach($levelAbilities as $abilityKey => $card)
                                                <div class="bg-slate-900 border border-slate-600/50 rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-shadow duration-200">
                                                    <!-- Use the existing domain card component -->
                                                    <x-character-level-up.domain-card :card="$card" />
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-8">
                                    <div class="w-16 h-16 bg-slate-800 rounded-xl flex items-center justify-center mx-auto mb-4">
                                        <x-dynamic-component component="icons.{{ $domain_key }}" class="fill-slate-400 w-8 h-8" />
                                    </div>
                                    <h3 class="font-outfit text-lg font-bold text-slate-400 mb-2">No Abilities Found</h3>
                                    <p class="text-slate-500">No abilities were found for the {{ ucfirst($domain_key) }} domain.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
