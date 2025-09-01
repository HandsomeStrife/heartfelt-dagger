<div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6 shadow-lg">
    <h2 class="text-lg font-bold">Equipment</h2>
    <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="rounded-2xl ring-1 ring-slate-700/60 p-4">
            <div class="text-xs text-slate-400">Inventory</div>
            <ul class="mt-2 space-y-1.5 text-sm">
                @if (!empty($organizedEquipment['items']))
                    @foreach ($organizedEquipment['items'] as $item)
                        <li>{{ $item['data']['name'] ?? ucwords(str_replace('-', ' ', $item['key'])) }}</li>
                    @endforeach
                @endif
                @if (!empty($organizedEquipment['consumables']))
                    @foreach ($organizedEquipment['consumables'] as $consumable)
                        <li>{{ $consumable['data']['name'] ?? ucwords(str_replace('-', ' ', $consumable['key'])) }}</li>
                    @endforeach
                @endif
                @if (empty($organizedEquipment['items']) && empty($organizedEquipment['consumables']))
                    <li class="text-slate-500 italic">No items in inventory</li>
                @endif
            </ul>
        </div>
        <div class="rounded-2xl ring-1 ring-slate-700/60 p-4">
            <div class="text-xs text-slate-400">Stash</div>
            <ul class="mt-2 space-y-1.5 text-sm">
                <li class="text-slate-500 italic">Empty</li>
            </ul>
        </div>
    </div>
</div>

