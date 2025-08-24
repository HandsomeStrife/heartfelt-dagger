<div {{ $attributes }}>
    <div class="relative class-banner-{{ $size }}">
        <!-- Banner Background -->
        <div class="absolute -top-1 left-[13.5px] z-40 w-full h-full">
            <img class="class-banner-{{ $size }}-height w-full absolute top-0 left-0" src="{{ asset('img/empty-banner.webp') }}">
            <div class="flex flex-col items-center justify-center gap-2 w-full h-full absolute top-0 left-0 pb-3">
                <x-dynamic-component component="icons.{{ $top_icon }}" class="fill-white {{ $size === 'lg' ? 'size-12' : ($size === 'md' ? 'size-10' : 'size-8') }}" />
                <x-dynamic-component component="icons.{{ $bottom_icon }}" class="fill-white {{ $size === 'lg' ? 'size-12' : ($size === 'md' ? 'size-10' : 'size-8') }}" />
            </div>
        </div>
        
        <!-- Banner Colored Layers -->
        <div class="absolute left-[16px] -top-1 class-banner-{{ $size }}-height class-banner-{{ $size }}-bg-width z-30" 
            style="background: linear-gradient(to top, {{ $top_color }} 75%, color-mix(in srgb, {{ $top_color }}, white 30%) 100%); clip-path: polygon(0 0, 11% 1%, 11% 51%, 17% 55%, 18% 0, 82% 0, 83% 56%, 88% 52%, 88% 0, 100% 1%, 100% 58%, 83% 69%, 82% 90%, 72% 90%, 63% 88%, 57% 85%, 49% 82%, 43% 85%, 34% 88%, 25% 90%, 18% 90%, 17% 68%, 0 59%);"></div>
        
        <!-- Banner Sparkle Overlay -->
        <div class="absolute left-[16px] -top-1 class-banner-{{ $size }}-height class-banner-{{ $size }}-bg-width z-35 pointer-events-none" 
            style="background-image: url('data:image/svg+xml,%3Csvg%20width%3D\'20\'%20height%3D\'20\'%20viewBox%3D\'0%200%2020%2020\'%20fill%3D\'none\'%20xmlns%3D\'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg\'%3E%3Ccircle%20cx%3D\'7.32289569642708\'%20cy%3D\'10.393732452875549\'%20r%3D\'0.1835629390800243\'%20fill%3D\'gold\'%20fill-opacity%3D\'0.4418777326489399\'%20%2F%3E%3Ccircle%20cx%3D\'18.294652949617227\'%20cy%3D\'6.495357930520824\'%20r%3D\'0.15887393824069335\'%20fill%3D\'white\'%20fill-opacity%3D\'0.5632516711972856\'%20%2F%3E%3C%2Fsvg%3E'); background-repeat: repeat; background-size: 40px 40px; mix-blend-mode: screen; opacity: 0.25; clip-path: polygon(0 0, 100% 0, 85% 85%, 15% 85%);"></div>
        
        <!-- Banner Background Color -->
        <div class="absolute left-[16px] -top-1 class-banner-{{ $size }}-height class-banner-{{ $size }}-bg-width z-20" style="background: {{ $bottom_color }}; clip-path: polygon(92% 100%, 92% 0, 8% 0, 8% 100%, 28% 98%, 39% 96%, 47% 91%, 44% 95%, 48% 85%, 50% 81%, 56% 85%, 53% 90%, 57% 92%, 60% 95%, 67% 98%, 78% 100%);"></div>
    </div>
</div>