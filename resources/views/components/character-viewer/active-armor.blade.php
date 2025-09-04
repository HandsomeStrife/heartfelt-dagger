<div pest="active-armor-section" class="mt-6 rounded-3xl border border-slate-800 bg-slate-900/60 p-6 shadow-lg">
    <h2 class="text-lg font-bold">Active Armor</h2>
    @if (!empty($organizedEquipment['armor']))
        @php $armor = $organizedEquipment['armor'][0]; @endphp
        <div pest="armor-details" class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
            <div>
                <div class="text-[10px] uppercase tracking-wider text-slate-400">Name</div>
                <div pest="armor-name" class="font-semibold">{{ $armor['data']['name'] ?? ucwords(str_replace('-', ' ', $armor['key'])) }}</div>
            </div>
            <div>
                <div class="text-[10px] uppercase tracking-wider text-slate-400">Base Thresholds</div>
                <div pest="armor-thresholds" class="font-semibold">
                    @if (isset($armor['data']['baseThresholds']))
                        {{ $armor['data']['baseThresholds']['minor'] ?? 1 }} /
                        {{ $armor['data']['baseThresholds']['major'] ?? 2 }} /
                        {{ $armor['data']['baseThresholds']['severe'] ?? 3 }}
                    @else
                        1 / 2 / 3
                    @endif
                </div>
            </div>
            <div>
                <div class="text-[10px] uppercase tracking-wider text-slate-400">Base Score</div>
                <div pest="armor-score" class="font-semibold">+{{ $armor['data']['baseScore'] ?? 0 }}</div>
            </div>
        </div>
        @if (isset($armor['data']['features']) && !empty($armor['data']['features']))
            <p class="mt-3 text-sm text-slate-300">Feature:
                {{ is_array($armor['data']['features']) ? implode(', ', $armor['data']['features']) : $armor['data']['features'] }}
            </p>
        @endif
    @else
        <div class="mt-4 text-center text-slate-500 text-sm italic">No armor equipped</div>
    @endif
</div>

