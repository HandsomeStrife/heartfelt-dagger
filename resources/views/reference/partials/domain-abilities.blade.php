@php
    use Domain\Character\Enums\DomainEnum;
    
    $domainEnum = DomainEnum::tryFrom($domain_key);
    $domainColor = $domainEnum ? $domainEnum->getColor() : '#24395d';
@endphp

<div class="mb-8">
    <!-- Domain Header -->
    <div class="flex items-center gap-4 mb-6">
        <div class="w-16 h-16 rounded-xl flex items-center justify-center shadow-lg p-3" style="background-color: {{ $domainColor }};">
            <x-dynamic-component component="icons.{{ $domain_key }}" class="fill-white w-10 h-10" />
        </div>
        <div>
            <h1 class="font-outfit text-2xl font-bold text-white">{{ $title }}</h1>
            @if($domain_info)
                <p class="text-slate-400 text-sm mt-1">{{ ucfirst($domain_key) }} Domain</p>
            @endif
        </div>
    </div>

    @if($domain_info && isset($domain_info['description']))
        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4 mb-8">
            <p class="text-slate-300 leading-relaxed">{{ $domain_info['description'] }}</p>
        </div>
    @endif
</div>

@if(count($abilities) > 0)
    @php
        // Group abilities by level
        $abilitiesByLevel = [];
        foreach ($abilities as $ability) {
            $level = $ability['level'] ?? 1;
            if (!isset($abilitiesByLevel[$level])) {
                $abilitiesByLevel[$level] = [];
            }
            $abilitiesByLevel[$level][] = $ability;
        }
        ksort($abilitiesByLevel);
    @endphp

    @foreach($abilitiesByLevel as $level => $levelAbilities)
        <div class="mb-8">
            <h2 class="font-outfit text-xl font-bold text-amber-400 mb-6 border-b border-slate-700 pb-2">
                Level {{ $level }} Abilities
            </h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach($levelAbilities as $ability)
                    <div class="bg-slate-900 border border-slate-700 rounded-xl overflow-hidden shadow-lg">
                        <!-- Use the domain card component structure -->
                        @php
                            $card = [
                                'name' => $ability['name'],
                                'domain' => $domain_key,
                                'level' => $ability['level'] ?? 1,
                                'type' => $ability['type'] ?? 'Ability',
                                'recall_cost' => $ability['recallCost'] ?? 0,
                                'descriptions' => $ability['descriptions'] ?? ['No description available.']
                            ];
                        @endphp
                        
                        <!-- Banner Structure -->
                        <div class="relative min-h-[120px] flex flex-col items-center justify-end bg-slate-900 w-full overflow-hidden rounded-t-xl">
                            <!-- Banner Background -->
                            <div class="absolute -top-1 left-[13.5px] z-40">
                                <img class="h-[120px] w-[75px]" src="/img/empty-banner.webp">
                                <div class="absolute inset-0 flex flex-col items-center justify-center pb-3 gap-1 pt-0.5">
                                    <!-- Level Badge -->
                                    <div class="text-2xl leading-[22px] font-bold border-2 border-dashed border-transparent pt-1 px-1 rounded-md">
                                        <div class="text-white font-black">{{ $card['level'] }}</div>
                                    </div>
                                    <!-- Domain Icon -->
                                    <div class="w-9 h-auto aspect-contain">
                                        <x-dynamic-component component="icons.{{ $card['domain'] }}" class="fill-white size-8" />
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Banner Colored Layers -->
                            <div class="absolute left-[16px] -top-1 h-[120px] w-[71px] z-30" 
                                 style="background: linear-gradient(to top, {{ $domainColor }} 75%, color-mix(in srgb, {{ $domainColor }}, white 30%) 100%); clip-path: polygon(0 0, 11% 1%, 11% 51%, 17% 55%, 18% 0, 82% 0, 83% 56%, 88% 52%, 88% 0, 100% 1%, 100% 58%, 83% 69%, 82% 90%, 72% 90%, 63% 88%, 57% 85%, 49% 82%, 43% 85%, 34% 88%, 25% 90%, 18% 90%, 17% 68%, 0 59%);"></div>
                            
                            <!-- Banner Background Color -->
                            <div class="absolute left-[16px] -top-1 h-[120px] w-[71px] z-20" style="background: {{ $domainColor }}; clip-path: polygon(0 0, 11% 1%, 11% 51%, 17% 55%, 18% 0, 82% 0, 83% 56%, 88% 52%, 88% 0, 100% 1%, 100% 58%, 83% 69%, 82% 90%, 72% 90%, 63% 88%, 57% 85%, 49% 82%, 43% 85%, 34% 88%, 25% 90%, 18% 90%, 17% 68%, 0 59%);"></div>
                            
                            <!-- Recall Cost Badge -->
                            @if(isset($card['recall_cost']) && $card['recall_cost'] > 0)
                                <div class="absolute top-4 right-4 aspect-square rounded-full w-9.5 h-9.5 p-0 border-2 border-yellow-400 bg-gray-900 z-40">
                                    <div class="flex gap-0.5 items-center justify-center absolute inset-0 font-bold border-2 border-gray-500 rounded-full">
                                        <div class="pl-1 text-white">{{ $card['recall_cost'] }}</div>
                                        <x-icons.bolt />
                                    </div>
                                </div>
                            @endif

                            <!-- Card Title -->
                            <div class="w-full pl-[100px] pr-3">
                                <h5 class="text-white font-black font-outfit text-xl leading-tight uppercase">
                                    {{ $card['name'] }}
                                </h5>
                                <div class="text-xs font-bold uppercase tracking-wide mt-1" style="color: {{ $domainColor }}">
                                    {{ $card['type'] ?? 'ability' }}
                                    <span class="ml-2 px-1.5 py-0.5 text-[10px] rounded bg-slate-600/60 text-slate-200">Lvl {{ $card['level'] }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Content -->
                        <div class="flex flex-col relative px-4 py-4 z-40 text-sm text-white flex-1">
                            <div class="flex-1 text-white text-sm leading-relaxed">
                                @foreach($card['descriptions'] as $description)
                                    <p class="mb-3">{{ $description }}</p>
                                @endforeach
                            </div>
                            
                            <!-- Domain Label -->
                            <div class="mt-auto pt-4 text-center">
                                <span class="inline-flex items-center px-2 py-1 text-[10px] font-bold uppercase tracking-widest rounded-md" style="background-color: {{ $domainColor }}; color: white;">
                                    {{ ucfirst($card['domain']) }} Domain
                                </span>
                            </div>
                        </div>
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
