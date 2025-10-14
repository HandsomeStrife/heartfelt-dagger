@props([
    'character',
    'pronouns' => null,
    'classData' => null,
    'subclassData' => null,
    'ancestryData' => null,
    'communityData' => null,
    'computedStats' => [],
    'canEdit' => false,
    'traitInfo' => [],
    'canLevelUp' => false,
    'characterKey' => null,
])
<header
    pest="character-viewer-top-banner"
    class="rounded-3xl border border-slate-800 bg-gradient-to-br from-slate-900 via-indigo-900/40 to-fuchsia-900/30 px-6 md:px-8 py-6 shadow-lg relative">
    @if($character->class)
        <x-class-banner :class-name="$character->class" size="sm" class="absolute top-0 right-6" />
    @endif
    <div class="absolute top-4 right-6 translate-y-full sm:translate-y-0 sm:top-3">
        <div class="rounded-3xl ring-1 ring-indigo-400/40 bg-indigo-500/10 px-4 py-3 min-w-[8.5rem]">
            <div class="flex items-baseline gap-2">
                <div>
                    <div class="text-[10px] uppercase tracking-wider text-indigo-200/90">Level</div>
                    <div class="text-3xl font-extrabold text-indigo-200 leading-none">{{ $character->level ?? 1 }}</div>
                </div>
                <div class="ml-2">
                    <div class="text-[10px] uppercase tracking-wider text-indigo-200/60">Tier</div>
                    <div class="text-2xl font-bold text-indigo-300/80 leading-none">{{ $getTier() }}</div>
                </div>
            </div>
            
            @if($canLevelUp && $canEdit && $characterKey)
            <div class="mt-2 pt-2 border-t border-indigo-400/20">
                <a href="{{ route('character.level-up', ['public_key' => $character->public_key, 'character_key' => $characterKey]) }}"
                   class="text-xs text-amber-400 hover:text-amber-300 underline transition-colors flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                    </svg>
                    Level Up
                </a>
            </div>
            @endif
        </div>
    </div>
    <div class="grid grid-cols-12 gap-6 items-start">
        <div class="col-span-12 sm:col-span-2 flex sm:block justify-center">
            <div>
                <div aria-hidden
                    class="aspect-square w-32 sm:w-32 md:w-40 rounded-2xl ring-1 ring-slate-700/60 overflow-hidden bg-slate-800/40">
                    @if ($character->profile_image_path)
                        <img src="{{ Storage::disk('s3')->url($character->profile_image_path) }}"
                            alt="{{ $character->name }}" class="h-full w-full object-cover">
                    @else
                        <div class="h-full w-full bg-gradient-to-br from-slate-700 via-indigo-700 to-fuchsia-700"></div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-span-12 sm:col-span-10 flex flex-col h-full">
            <div class="space-y-3">
                <div class="flex items-center justify-center sm:justify-start gap-2">
                    <h1 pest="character-name" class="text-2xl md:text-3xl font-extrabold tracking-tight leading-tight text-center sm:text-left">
                        {{ $character->name ?: 'Unnamed Character' }}
                        <span pest="character-pronouns" class="text-xs text-slate-400 font-light ml-1">{{ $pronouns }}</span>
                    </h1>
                    @if ($canEdit)
                        <a x-show="canEdit" :href="`/character-builder/${characterKey}`" aria-label="Edit character"
                            class="inline-flex items-center p-1.5 rounded-md ring-1 ring-indigo-400/40 hover:bg-indigo-500/20 text-indigo-200">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.232 5.232l3.536 3.536M4 20h4l10.607-10.607a2.5 2.5 0 10-3.536-3.536L4 16.464V20z" />
                            </svg>
                        </a>
                    @endif
                </div>
                <div class="mt-2 flex flex-wrap items-center gap-2 justify-center sm:justify-start">
                    <span pest="character-heritage" class="inline-flex items-center gap-1 text-xs md:text-sm font-semibold px-3 py-1 rounded-full ring-1 ring-indigo-400/40 bg-indigo-500/15 text-indigo-200">
                        {{ $communityData['name'] ?? ucfirst($character->community ?? 'Unknown') }}
                        {{ $ancestryData['name'] ?? ucfirst($character->ancestry ?? 'Unknown') }} •
                        {{ $classData['name'] ?? ucfirst($character->class ?? 'Unknown') }}
                        @if ($character->subclass && $subclassData)
                            <span class="opacity-70">({{ $subclassData['name'] ?? ucwords(str_replace('-', ' ', $character->subclass)) }})</span>
                        @endif
                    </span>
                    @if ($classData && isset($classData['domains']))
                        <span pest="class-domains" class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold ring-1 ring-slate-700/60 bg-slate-800/60">
                            {{ ucfirst($classData['domains'][0] ?? '') }} & {{ ucfirst($classData['domains'][1] ?? '') }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="mt-auto pt-3">
                <div pest="character-stats" class="flex items-center gap-3 flex-nowrap overflow-x-auto">
                    <div class="flex gap-2">
                        <x-icons.evasion-frame pest="evasion-stat" :number="$computedStats['evasion'] ?? '?'" class="size-20" />
                        <x-icons.armor-frame pest="armor-stat" :number="$computedStats['armor_score'] ?? '?'" class="size-20" />
                    </div>
                    <span class="text-slate-500/80 select-none">|</span>
                    <div pest="trait-stats" class="flex items-center gap-1 flex-nowrap">
                        @foreach ($traitInfo as $trait => $label)
                            <div class="cursor-pointer hover:scale-105 transition-transform duration-200" 
                                 data-trait="{{ $trait }}" 
                                 data-trait-value="{{ $traitValues[$trait] ?? '+0' }}"
                                 onclick="rollTraitCheck('{{ $trait }}', {{ str_replace('+', '', $traitValues[$trait] ?? '0') }})"
                                 title="Click to roll {{ $label }} check">
                                <x-icons.stat-frame pest="trait-{{ $trait }}" :number="$traitValues[$trait] ?? '+0'" :label="$label" class="size-20" />
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>


