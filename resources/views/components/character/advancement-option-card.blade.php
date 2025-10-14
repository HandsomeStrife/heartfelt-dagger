@props([
    'option',
    'index',
    'selected' => false,
    'disabled' => false,
    'selectCount' => 0,
])

{{-- Extract values from option array --}}
@php
    // Only extract values, no complex logic in @php blocks
    $available = $option['available'] ?? true;
    $description = $option['description'] ?? '';
    $maxSelections = $option['max_selections'] ?? 1;
    $slotsRequired = $option['slots_required'] ?? 1;
    $notes = $option['notes'] ?? null;
    $canSelect = $available && !$disabled;
@endphp

<div 
    @class([
        'relative p-6 rounded-lg border-2 transition-all duration-200 cursor-pointer group',
        'border-amber-400 bg-amber-500/10 ring-2 ring-amber-400/50' => $selected,
        'border-slate-700 bg-slate-900/50 opacity-60 cursor-not-allowed' => !$canSelect && !$selected,
        'border-slate-700 bg-slate-900/80 hover:border-amber-400/50 hover:bg-slate-800/90 hover:scale-[1.02]' => $canSelect && !$selected,
    ])
    role="button"
    tabindex="{{ $canSelect ? '0' : '-1' }}"
    aria-pressed="{{ $selected ? 'true' : 'false' }}"
    aria-disabled="{{ !$canSelect ? 'true' : 'false' }}"
    aria-label="Advancement option: {{ strip_tags($description) }}{{ $selected ? ' (selected)' : '' }}{{ !$canSelect ? ' (unavailable)' : '' }}"
>
    <!-- Selection Indicator -->
    @if($selected)
        <div class="absolute top-4 right-4">
            <div class="w-8 h-8 rounded-full bg-amber-400 flex items-center justify-center">
                <svg class="w-5 h-5 text-slate-900" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
            </div>
        </div>
    @endif

    <!-- Selection Count Badge (if selected multiple times) -->
    @if($selected && $selectCount > 1)
        <div class="absolute top-4 left-4">
            <div class="px-3 py-1 rounded-full bg-amber-500 text-slate-900 text-sm font-bold">
                ×{{ $selectCount }}
            </div>
        </div>
    @endif

    <!-- Option Description -->
    <div class="space-y-3 {{ $selected || $selectCount > 1 ? 'pr-10' : '' }}">
        <p class="text-white font-medium text-lg leading-relaxed">
            {{ $description }}
        </p>

        <!-- Metadata Row -->
        <div class="flex flex-wrap items-center gap-3 text-sm">
            <!-- Max Selections Badge -->
            @if($maxSelections > 1)
                <div class="flex items-center space-x-1 px-3 py-1 rounded-full bg-blue-500/20 border border-blue-500/50 text-blue-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <span class="font-medium">Up to {{ $maxSelections }}×</span>
                </div>
            @endif

            <!-- Slots Required Badge (if more than 1) -->
            @if($slotsRequired > 1)
                <div class="flex items-center space-x-1 px-3 py-1 rounded-full bg-purple-500/20 border border-purple-500/50 text-purple-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-medium">{{ $slotsRequired }} Slots</span>
                </div>
            @endif

            <!-- Availability Badge -->
            @if(!$available)
                <div class="flex items-center space-x-1 px-3 py-1 rounded-full bg-red-500/20 border border-red-500/50 text-red-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <span class="font-medium">Unavailable</span>
                </div>
            @endif

            <!-- Available Badge -->
            @if($available && !$selected)
                <div class="flex items-center space-x-1 px-3 py-1 rounded-full bg-green-500/20 border border-green-500/50 text-green-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span class="font-medium">Available</span>
                </div>
            @endif
        </div>

        <!-- Special Notes -->
        @if($notes)
            <div class="mt-3 p-3 rounded-md bg-blue-500/10 border border-blue-500/30">
                <div class="flex items-start space-x-2">
                    <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-blue-300 text-sm leading-relaxed">
                        {{ $notes }}
                    </p>
                </div>
            </div>
        @endif
    </div>

    <!-- Hover Effect Indicator -->
    @if($canSelect && !$selected)
        <div class="absolute inset-0 rounded-lg ring-2 ring-amber-400 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none"></div>
    @endif

    <!-- Keyboard Navigation Hint -->
    <div class="sr-only">
        @if($selected)
            Selected advancement option: {{ $description }}. Press Enter or Space to deselect.
        @elseif($canSelect)
            Available advancement option: {{ $description }}. Press Enter or Space to select.
        @else
            Unavailable advancement option: {{ $description }}.
            @if($notes) {{ $notes }} @endif
        @endif
    </div>
</div>

