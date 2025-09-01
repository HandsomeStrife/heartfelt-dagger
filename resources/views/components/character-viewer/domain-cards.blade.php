@if (!empty($domain_card_details))
    <div class="flex flex-wrap justify-start gap-6">
        @foreach ($domain_card_details as $card)
            <div class="bg-slate-900/60 border border-slate-800 rounded-xl shadow-lg overflow-hidden w-[360px] flex flex-col">
                <div class="relative min-h-[120px] flex flex-col items-center justify-end bg-slate-900 w-full overflow-hidden">
                    <div class="absolute -top-1 left-[13.5px] z-40">
                        <img class="h-[120px] w-[75px]" src="/img/empty-banner.webp">
                        <div class="absolute inset-0 flex flex-col items-center justify-center pb-3 gap-1 pt-0.5">
                            @if (isset($card['ability_data']['level']))
                                <div class="text-2xl leading-[22px] font-bold border-2 border-dashed border-transparent pt-1 px-1 rounded-md">
                                    <div class="text-white font-black">{{ $card['ability_data']['level'] }}</div>
                                </div>
                            @endif
                            <div class="w-9 h-auto aspect-contain">
                                <x-dynamic-component component="icons.{{ $card['ability_data']['domain'] ?? ($card['domain'] ?? 'codex') }}" class="fill-white size-8" />
                            </div>
                        </div>
                    </div>
                    @if (isset($card['ability_data']['recallCost']) && $card['ability_data']['recallCost'] > 0)
                        <div class="absolute top-3 right-3 flex items-center gap-2 text-xs">
                            <span class="text-slate-300">Cost</span>
                            <div class="flex gap-1">
                                @for ($i = 0; $i < $card['ability_data']['recallCost']; $i++)
                                    <span class="block w-5 h-5 rotate-45 rounded-sm ring-1 ring-indigo-400/50 bg-slate-900"></span>
                                @endfor
                            </div>
                        </div>
                    @endif
                    <div class="w-full pl-[100px] pr-4 pb-3">
                        <h3 class="text-white font-black font-outfit text-lg leading-tight uppercase">
                            {{ $card['ability_data']['name'] ?? ucwords(str_replace('-', ' ', $card['ability_key'])) }}
                        </h3>
                        <div class="text-[10px] font-bold uppercase tracking-wide mt-1 text-slate-300">
                            {{ $card['ability_data']['type'] ?? 'ability' }}
                        </div>
                    </div>
                </div>
                <div class="px-4 py-4 text-sm text-white flex-1">
                    @if (isset($card['ability_data']['descriptions']) && is_array($card['ability_data']['descriptions']))
                        <div class="text-slate-300 space-y-2 leading-relaxed">
                            @foreach ($card['ability_data']['descriptions'] as $description)
                                <p>{{ $description }}</p>
                            @endforeach
                        </div>
                    @elseif(isset($card['ability_data']['description']))
                        <p class="text-slate-300 leading-relaxed">{{ $card['ability_data']['description'] }}</p>
                    @endif
                </div>
                <div class="mt-auto px-4 pb-4">
                    <span class="inline-flex items-center px-2 py-1 text-[10px] font-bold uppercase tracking-widest rounded-md bg-slate-700 text-white">
                        {{ ucfirst($card['ability_data']['domain'] ?? ($card['domain'] ?? 'codex')) }} Domain
                    </span>
                </div>
            </div>
        @endforeach
    </div>
@endif

