@php
    use Domain\Character\Enums\DomainEnum;
@endphp

<h1 class="font-outfit text-2xl font-bold text-white border-b border-slate-700 pb-3 mb-6 mt-0">{{ $title }}</h1>

<p class="text-slate-300 leading-relaxed mb-6">
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
                    <x-dynamic-component component="icons.{{ $domain['key'] }}" class="fill-white w-8 h-8" />
                </div>
                <div>
                    <h2 class="font-outfit text-xl font-bold text-amber-400">{{ $domain['name'] }}</h2>
                    <p class="text-slate-400 text-sm">Domain Key: {{ $domain['key'] }}</p>
                </div>
            </div>

            <!-- Domain Description -->
            <p class="text-slate-300 leading-relaxed mb-6">{{ $domain['description'] }}</p>

            <!-- Abilities Summary & Link -->
            @if(isset($domain['abilitiesByLevel']) && is_array($domain['abilitiesByLevel']))
                <div class="flex items-center justify-between bg-slate-900/30 border border-slate-700 rounded-lg p-4">
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
                        View All Abilities
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            @endif
        </div>
    @endforeach
</div>
