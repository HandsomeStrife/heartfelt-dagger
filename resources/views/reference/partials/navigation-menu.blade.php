<!-- Introduction -->
<div>
    <h4 class="text-xs uppercase tracking-wider text-slate-400 mb-1 px-2">Introduction</h4>
    <div class="space-y-1">
        @foreach(['what-is-this' => 'What Is This?', 'the-basics' => 'The Basics'] as $pageKey => $title)
            <a href="{{ route('reference.page', $pageKey) }}" 
               class="cursor-pointer block text-left p-2 rounded text-sm transition-colors {{ ($current_page ?? 'what-is-this') === $pageKey ? 'bg-amber-500/20 text-amber-400' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">
                {{ $title }}
            </a>
        @endforeach
    </div>
</div>

<!-- Character Creation -->
<div>
    <h4 class="text-xs uppercase tracking-wider text-slate-400 mb-1 px-2">Character Creation</h4>
    <div class="space-y-1">
        <a href="{{ route('reference.page', 'character-creation') }}" 
           class="cursor-pointer block text-left p-2 rounded text-sm transition-colors {{ ($current_page ?? 'what-is-this') === 'character-creation' ? 'bg-amber-500/20 text-amber-400' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">
            Character Creation
        </a>
    </div>
</div>

<!-- Core Materials -->
<div>
    <h4 class="text-xs uppercase tracking-wider text-slate-400 mb-1 px-2">Core Materials</h4>
    <div class="space-y-1">
        <!-- Domains -->
        <a href="{{ route('reference.page', 'domains') }}" 
           class="cursor-pointer block text-left p-2 rounded text-sm transition-colors {{ ($current_page ?? 'what-is-this') === 'domains' ? 'bg-amber-500/20 text-amber-400' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">
            Domains
        </a>
        
        <!-- Domain abilities (indented) -->
        @foreach(['arcana-abilities' => 'Arcana', 'blade-abilities' => 'Blade', 'bone-abilities' => 'Bone', 'codex-abilities' => 'Codex', 'grace-abilities' => 'Grace', 'midnight-abilities' => 'Midnight', 'sage-abilities' => 'Sage', 'splendor-abilities' => 'Splendor', 'valor-abilities' => 'Valor'] as $pageKey => $title)
            <a href="{{ route('reference.page', $pageKey) }}" 
               class="cursor-pointer block text-left py-1 px-2 pl-4 rounded text-sm transition-colors {{ ($current_page ?? 'what-is-this') === $pageKey ? 'bg-amber-500/20 text-amber-400' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">
                {{ $title }}
            </a>
        @endforeach
        
        <!-- Other core materials -->
        @foreach(['classes' => 'Classes', 'ancestries' => 'Ancestries', 'communities' => 'Communities'] as $pageKey => $title)
            <a href="{{ route('reference.page', $pageKey) }}" 
               class="cursor-pointer block text-left p-2 rounded text-sm transition-colors {{ ($current_page ?? 'what-is-this') === $pageKey ? 'bg-amber-500/20 text-amber-400' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">
                {{ $title }}
            </a>
        @endforeach
    </div>
</div>

<!-- Core Mechanics -->
<div>
    <h4 class="text-xs uppercase tracking-wider text-slate-400 mb-1 px-2">Core Mechanics</h4>
    <div class="space-y-1">
        @foreach([
            'flow-of-the-game' => 'Flow of the Game',
            'core-gameplay-loop' => 'Core Gameplay Loop', 
            'the-spotlight' => 'The Spotlight',
            'turn-order-and-action-economy' => 'Turn Order & Action Economy',
            'making-moves-and-taking-action' => 'Making Moves & Taking Action',
            'combat' => 'Combat',
            'stress' => 'Stress',
            'attacking' => 'Attacking',
            'maps-range-and-movement' => 'Maps, Range, and Movement',
            'conditions' => 'Conditions',
            'downtime' => 'Downtime',
            'death' => 'Death',
            'additional-rules' => 'Additional Rules',
            'leveling-up' => 'Leveling Up',
            'multiclassing' => 'Multiclassing'
        ] as $pageKey => $title)
            <a href="{{ route('reference.page', $pageKey) }}" 
               class="cursor-pointer block text-left p-2 rounded text-sm transition-colors {{ ($current_page ?? 'what-is-this') === $pageKey ? 'bg-amber-500/20 text-amber-400' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">
                {{ $title }}
            </a>
        @endforeach
    </div>
</div>

<!-- Equipment -->
<div>
    <h4 class="text-xs uppercase tracking-wider text-slate-400 mb-1 px-2">Equipment</h4>
    <div class="space-y-1">
        @foreach([
            'equipment' => 'Equipment',
            'weapons' => 'Weapons',
            'combat-wheelchair' => 'Combat Wheelchair',
            'armor' => 'Armor',
            'loot' => 'Loot',
            'consumables' => 'Consumables',
            'gold' => 'Gold'
        ] as $pageKey => $title)
            <a href="{{ route('reference.page', $pageKey) }}" 
               class="cursor-pointer block text-left p-2 rounded text-sm transition-colors {{ ($current_page ?? 'what-is-this') === $pageKey ? 'bg-amber-500/20 text-amber-400' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">
                {{ $title }}
            </a>
        @endforeach
    </div>
</div>

<!-- Running an Adventure -->
<div>
    <h4 class="text-xs uppercase tracking-wider text-slate-400 mb-1 px-2">Running an Adventure</h4>
    <div class="space-y-1">
        @foreach([
            'gm-guidance' => 'GM Guidance',
            'core-gm-mechanics' => 'Core GM Mechanics',
            'adversaries' => 'Adversaries',
            'environments' => 'Environments',
            'additional-gm-guidance' => 'Additional GM Guidance',
            'campaign-frames' => 'Campaign Frames'
        ] as $pageKey => $title)
            <a href="{{ route('reference.page', $pageKey) }}" 
               class="cursor-pointer block text-left p-2 rounded text-sm transition-colors {{ ($current_page ?? 'what-is-this') === $pageKey ? 'bg-amber-500/20 text-amber-400' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">
                {{ $title }}
            </a>
        @endforeach
    </div>
</div>