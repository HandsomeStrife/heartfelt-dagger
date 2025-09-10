@props(['card'])

@php
    $domainColors = [
        'valor' => '#e2680e', 'splendor' => '#b8a342', 'sage' => '#244e30', 'midnight' => '#1e201f',
        'grace' => '#8d3965', 'codex' => '#24395d', 'bone' => '#a4a9a8', 'blade' => '#af231c', 'arcana' => '#4e345b'
    ];
    $domainColor = $domainColors[$card['domain']] ?? '#24395d';
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
