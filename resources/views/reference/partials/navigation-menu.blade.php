<!-- Introduction -->
<div>
    <h4 class="text-xs uppercase tracking-wider text-slate-400 mb-1 px-2">Introduction</h4>
    <div class="space-y-1">
        @foreach(['what-is-this', 'the-basics'] as $pageKey)
            @if(isset($pages[$pageKey]))
                <a href="{{ route('reference.page', $pageKey) }}" 
                   class="block text-left p-2 rounded text-sm transition-colors {{ ($current_page ?? 'what-is-this') === $pageKey ? 'bg-amber-500/20 text-amber-400' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">
                    {{ $pages[$pageKey]['title'] ?? ucwords(str_replace('-', ' ', $pageKey)) }}
                </a>
            @endif
        @endforeach
    </div>
</div>

<!-- Character Creation -->
<div>
    <h4 class="text-xs uppercase tracking-wider text-slate-400 mb-1 px-2">Character Creation</h4>
    <div class="space-y-1">
        @foreach(['character-creation'] as $pageKey)
            @if(isset($pages[$pageKey]))
                <a href="{{ route('reference.page', $pageKey) }}" 
                   class="block text-left p-2 rounded text-sm transition-colors {{ ($current_page ?? 'what-is-this') === $pageKey ? 'bg-amber-500/20 text-amber-400' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">
                    {{ $pages[$pageKey]['title'] ?? ucwords(str_replace('-', ' ', $pageKey)) }}
                </a>
            @endif
        @endforeach
    </div>
</div>

<!-- Domains -->
<div>
    <h4 class="text-xs uppercase tracking-wider text-slate-400 mb-1 px-2">Domains</h4>
    <div class="space-y-1">
        <!-- Main domains page -->
        <a href="{{ route('reference.page', 'domains') }}" 
           class="block text-left p-2 rounded text-sm transition-colors {{ ($current_page ?? 'what-is-this') === 'domains' ? 'bg-amber-500/20 text-amber-400' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">
            Domains Overview
        </a>
        
        <!-- Individual domain abilities -->
        @foreach(['arcana-abilities', 'blade-abilities', 'bone-abilities', 'codex-abilities', 'grace-abilities', 'midnight-abilities', 'sage-abilities', 'splendor-abilities', 'valor-abilities'] as $pageKey)
            <a href="{{ route('reference.page', $pageKey) }}" 
               class="block text-left p-2 pl-4 rounded text-sm transition-colors {{ ($current_page ?? 'what-is-this') === $pageKey ? 'bg-amber-500/20 text-amber-400' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">
                {{ ucfirst(str_replace('-abilities', '', $pageKey)) }}
            </a>
        @endforeach
    </div>
</div>

<!-- Classes -->
<div>
    <h4 class="text-xs uppercase tracking-wider text-slate-400 mb-1 px-2">Classes</h4>
    <div class="space-y-1">
        <!-- Main classes page -->
        <a href="{{ route('reference.page', 'classes') }}" 
           class="block text-left p-2 rounded text-sm transition-colors {{ ($current_page ?? 'what-is-this') === 'classes' ? 'bg-amber-500/20 text-amber-400' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">
            Classes Overview
        </a>
        
        <!-- Individual class pages -->
        @foreach(['bard', 'druid', 'guardian', 'ranger', 'rogue', 'seraph', 'sorcerer', 'warrior', 'wizard'] as $pageKey)
            <a href="{{ route('reference.page', $pageKey) }}" 
               class="block text-left p-2 pl-4 rounded text-sm transition-colors {{ ($current_page ?? 'what-is-this') === $pageKey ? 'bg-amber-500/20 text-amber-400' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">
                {{ ucfirst($pageKey) }}
            </a>
        @endforeach
    </div>
</div>

<!-- Ancestries & Communities -->
<div>
    <h4 class="text-xs uppercase tracking-wider text-slate-400 mb-1 px-2">Heritage</h4>
    <div class="space-y-1">
        @foreach(['ancestries', 'communities'] as $pageKey)
            <a href="{{ route('reference.page', $pageKey) }}" 
               class="block text-left p-2 rounded text-sm transition-colors {{ ($current_page ?? 'what-is-this') === $pageKey ? 'bg-amber-500/20 text-amber-400' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">
                {{ ucwords($pageKey) }}
            </a>
        @endforeach
    </div>
</div>

<!-- Core Mechanics -->
<div>
    <h4 class="text-xs uppercase tracking-wider text-slate-400 mb-1 px-2">Core Mechanics</h4>
    <div class="space-y-1">
        @foreach(['flow-of-the-game', 'core-gameplay-loop', 'the-spotlight', 'turn-order-and-action-economy', 'making-moves-and-taking-action', 'combat', 'stress', 'attacking', 'maps-range-and-movement', 'conditions', 'downtime', 'death', 'additional-rules', 'leveling-up', 'multiclassing'] as $pageKey)
            @if(isset($pages[$pageKey]))
                <a href="{{ route('reference.page', $pageKey) }}" 
                   class="block text-left p-2 rounded text-sm transition-colors {{ ($current_page ?? 'what-is-this') === $pageKey ? 'bg-amber-500/20 text-amber-400' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">
                    {{ $pages[$pageKey]['title'] ?? ucwords(str_replace('-', ' ', $pageKey)) }}
                </a>
            @endif
        @endforeach
    </div>
</div>

<!-- Equipment -->
<div>
    <h4 class="text-xs uppercase tracking-wider text-slate-400 mb-1 px-2">Equipment</h4>
    <div class="space-y-1">
        @foreach(['equipment', 'weapons', 'armor', 'consumables', 'items', 'primary-weapon-tables', 'secondary-weapon-tables', 'combat-wheelchair', 'armor-tables', 'loot', 'gold'] as $pageKey)
            @if(isset($pages[$pageKey]))
                <a href="{{ route('reference.page', $pageKey) }}" 
                   class="block text-left p-2 rounded text-sm transition-colors {{ ($current_page ?? 'what-is-this') === $pageKey ? 'bg-amber-500/20 text-amber-400' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">
                    {{ $pages[$pageKey]['title'] ?? ucwords(str_replace('-', ' ', $pageKey)) }}
                </a>
            @endif
        @endforeach
    </div>
</div>

<!-- GM Resources -->
<div>
    <h4 class="text-xs uppercase tracking-wider text-slate-400 mb-1 px-2">GM Resources</h4>
    <div class="space-y-1">
        @foreach(['gm-guidance', 'core-gm-mechanics', 'adversaries', 'additional-gm-guidance'] as $pageKey)
            @if(isset($pages[$pageKey]))
                <a href="{{ route('reference.page', $pageKey) }}" 
                   class="block text-left p-2 rounded text-sm transition-colors {{ ($current_page ?? 'what-is-this') === $pageKey ? 'bg-amber-500/20 text-amber-400' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">
                    {{ $pages[$pageKey]['title'] ?? ucwords(str_replace('-', ' ', $pageKey)) }}
                </a>
            @endif
        @endforeach
    </div>
</div>

<!-- Reference -->
<div>
    <h4 class="text-xs uppercase tracking-wider text-slate-400 mb-1 px-2">Reference</h4>
    <div class="space-y-1">
        @foreach(['domain-abilities', 'domain-card-reference'] as $pageKey)
            @if(isset($pages[$pageKey]))
                <a href="{{ route('reference.page', $pageKey) }}" 
                   class="block text-left p-2 rounded text-sm transition-colors {{ ($current_page ?? 'what-is-this') === $pageKey ? 'bg-amber-500/20 text-amber-400' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">
                    {{ $pages[$pageKey]['title'] ?? ucwords(str_replace('-', ' ', $pageKey)) }}
                </a>
            @endif
        @endforeach
    </div>
</div>

