@props(['title' => 'Quick Reference', 'items' => []])

<div class="bg-slate-800/30 rounded-lg p-3 border border-slate-700/50">
    <div class="flex items-center justify-between mb-2">
        <h4 class="text-white font-outfit font-semibold text-xs">{{ $title }}</h4>
        <a href="{{ route('reference.index') }}" 
           class="text-amber-400 hover:text-amber-300 text-xs flex items-center"
           target="_blank">
            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
            Full Reference
        </a>
    </div>
    
    <div class="space-y-1 max-h-32 overflow-y-auto">
        @forelse($items as $item)
            <a href="{{ $item['url'] }}" 
               target="_blank"
               class="block text-slate-300 hover:text-white text-xs p-1.5 rounded hover:bg-slate-700/50 transition-colors">
                {{ $item['title'] }}
            </a>
        @empty
            <div class="text-slate-400 text-xs text-center py-2">
                No quick references available
            </div>
        @endforelse
    </div>
</div>
