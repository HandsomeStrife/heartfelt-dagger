<x-layouts.app>
    <x-sub-navigation>
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center space-x-4">
                <a href="{{ route('reference.index') }}" 
                   class="text-slate-400 hover:text-white transition-colors text-sm">
                    ← Back
                </a>
            </div>
            
            <div class="flex-1 max-w-md mx-4">
                <livewire:reference-search-new :is_sidebar="false" />
            </div>
            
            <div class="w-16"></div> <!-- Spacer for centering -->
        </div>
    </x-sub-navigation>

    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950">
        <div class="container mx-auto px-4 py-8">
            <div class="w-full mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    <!-- Sidebar -->
                    <div class="lg:col-span-3">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-6">
                            <h3 class="font-outfit text-lg font-semibold text-white mb-4">Reference Pages</h3>
                            
                            <nav class="space-y-6">
                                @include('reference.partials.navigation-menu', ['current_page' => 'classes'])
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-8">
                            <!-- Page Header -->
                            <div class="mb-8 flex items-start justify-between">
                                <h1 class="font-outfit text-3xl font-bold text-white">
                                    Classes
                                </h1>
                                
                                <div class="flex items-center space-x-2 ml-4 flex-shrink-0">
                                    <span class="px-3 py-1 bg-emerald-500/20 text-emerald-300 text-sm rounded-full">
                                        Core Materials
                                    </span>
                                </div>
                            </div>

                            <!-- Page Content -->
                            <div class="prose prose-invert max-w-none" data-search-body>
                                <!-- Class Domains Section -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Class Domains</h2>
                                    <p class="text-slate-300 leading-relaxed mb-8">
                                        Each class grants access to two domains:
                                    </p>

                                    <div class="grid md:grid-cols-2 gap-4 mb-8">
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-3">Bard</h3>
                                            <p class="text-slate-300 text-sm">
                                                <a href="{{ route('reference.page', 'codex-abilities') }}" class="text-blue-400 hover:text-blue-300 underline">Codex</a> & 
                                                <a href="{{ route('reference.page', 'grace-abilities') }}" class="text-pink-400 hover:text-pink-300 underline">Grace</a>
                                            </p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-3">Druid</h3>
                                            <p class="text-slate-300 text-sm">
                                                <a href="{{ route('reference.page', 'arcana-abilities') }}" class="text-purple-400 hover:text-purple-300 underline">Arcana</a> & 
                                                <a href="{{ route('reference.page', 'sage-abilities') }}" class="text-green-400 hover:text-green-300 underline">Sage</a>
                                            </p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-3">Guardian</h3>
                                            <p class="text-slate-300 text-sm">
                                                <a href="{{ route('reference.page', 'blade-abilities') }}" class="text-red-400 hover:text-red-300 underline">Blade</a> & 
                                                <a href="{{ route('reference.page', 'valor-abilities') }}" class="text-orange-400 hover:text-orange-300 underline">Valor</a>
                                            </p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-3">Ranger</h3>
                                            <p class="text-slate-300 text-sm">
                                                <a href="{{ route('reference.page', 'bone-abilities') }}" class="text-gray-400 hover:text-gray-300 underline">Bone</a> & 
                                                <a href="{{ route('reference.page', 'sage-abilities') }}" class="text-green-400 hover:text-green-300 underline">Sage</a>
                                            </p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-3">Rogue</h3>
                                            <p class="text-slate-300 text-sm">
                                                <a href="{{ route('reference.page', 'grace-abilities') }}" class="text-pink-400 hover:text-pink-300 underline">Grace</a> & 
                                                <a href="{{ route('reference.page', 'midnight-abilities') }}" class="text-slate-400 hover:text-slate-300 underline">Midnight</a>
                                            </p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-3">Seraph</h3>
                                            <p class="text-slate-300 text-sm">
                                                <a href="{{ route('reference.page', 'splendor-abilities') }}" class="text-yellow-400 hover:text-yellow-300 underline">Splendor</a> & 
                                                <a href="{{ route('reference.page', 'valor-abilities') }}" class="text-orange-400 hover:text-orange-300 underline">Valor</a>
                                            </p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-3">Sorcerer</h3>
                                            <p class="text-slate-300 text-sm">
                                                <a href="{{ route('reference.page', 'arcana-abilities') }}" class="text-purple-400 hover:text-purple-300 underline">Arcana</a> & 
                                                <a href="{{ route('reference.page', 'midnight-abilities') }}" class="text-slate-400 hover:text-slate-300 underline">Midnight</a>
                                            </p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-3">Warrior</h3>
                                            <p class="text-slate-300 text-sm">
                                                <a href="{{ route('reference.page', 'blade-abilities') }}" class="text-red-400 hover:text-red-300 underline">Blade</a> & 
                                                <a href="{{ route('reference.page', 'bone-abilities') }}" class="text-gray-400 hover:text-gray-300 underline">Bone</a>
                                            </p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-3">Wizard</h3>
                                            <p class="text-slate-300 text-sm">
                                                <a href="{{ route('reference.page', 'codex-abilities') }}" class="text-blue-400 hover:text-blue-300 underline">Codex</a> & 
                                                <a href="{{ route('reference.page', 'splendor-abilities') }}" class="text-yellow-400 hover:text-yellow-300 underline">Splendor</a>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl p-6">
                                        <p class="text-emerald-100 leading-relaxed">
                                            PCs acquire <strong>two 1st-level domain cards</strong> at character creation and an additional domain card at or below their level each time they level up.
                                        </p>
                                    </div>
                                </div>

                                <!-- Domain Cards Section -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Domain Cards</h2>
                                    <p class="text-slate-300 leading-relaxed mb-8">
                                        Each domain card provides one or more features your PC can utilize during their adventures. Some domain cards provide moves you can make, such as a unique attack or a spell. Others offer passive effects, new downtime or social encounter abilities, or one-time benefits.
                                    </p>

                                    <h3 class="font-outfit text-xl font-bold text-amber-300 mb-6">Domain Card Anatomy</h3>
                                    <p class="text-slate-300 leading-relaxed mb-6">
                                        Each domain card includes six elements:
                                    </p>

                                    <div class="grid md:grid-cols-2 gap-6 mb-8">
                                        <div class="space-y-4">
                                            <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                                <h4 class="font-outfit text-lg font-bold text-amber-300 mb-2">Level</h4>
                                                <p class="text-slate-300 text-sm">
                                                    The number in the top left of the card indicates the card's level. You cannot acquire a domain card with a level higher than your PC's.
                                                </p>
                                            </div>
                                            <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                                <h4 class="font-outfit text-lg font-bold text-amber-300 mb-2">Domain</h4>
                                                <p class="text-slate-300 text-sm">
                                                    Beneath the card's level there is a symbol indicating its domain. You can only choose cards from your class's two domains.
                                                </p>
                                            </div>
                                            <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                                <h4 class="font-outfit text-lg font-bold text-amber-300 mb-2">Recall Cost</h4>
                                                <p class="text-slate-300 text-sm">
                                                    The number and lightning bolt in the top right shows its Recall Cost. This is the amount of Stress a player must mark to swap this card from their <strong>vault</strong> with a card from their <strong>loadout</strong>.
                                                </p>
                                            </div>
                                        </div>
                                        <div class="space-y-4">
                                            <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                                <h4 class="font-outfit text-lg font-bold text-amber-300 mb-2">Title</h4>
                                                <p class="text-slate-300 text-sm">
                                                    The name of the card.
                                                </p>
                                            </div>
                                            <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                                <h4 class="font-outfit text-lg font-bold text-amber-300 mb-2">Type</h4>
                                                <p class="text-slate-300 text-sm">
                                                    The card's <strong>type</strong> is listed in the center above the title. There are three types: <strong>abilities, spells, and grimoires</strong>.
                                                </p>
                                            </div>
                                            <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                                <h4 class="font-outfit text-lg font-bold text-amber-300 mb-2">Feature</h4>
                                                <p class="text-slate-300 text-sm">
                                                    The text on the bottom half describes its feature(s), including any special rules you need to follow when you use that card.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-4 mb-6">
                                        <p class="text-amber-100 leading-relaxed text-sm">
                                            <strong>Note:</strong> A player can swap domain cards during downtime without paying the domain card's Recall Cost.
                                        </p>
                                    </div>
                                </div>

                                <!-- Loadout & Vault Section -->
                                <div class="mb-12">
                                    <h3 class="font-outfit text-xl font-bold text-amber-300 mb-6">Loadout & Vault</h3>
                                    
                                    <div class="grid md:grid-cols-2 gap-6">
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <h4 class="font-outfit text-lg font-bold text-amber-300 mb-3">Loadout</h4>
                                            <p class="text-slate-300 text-sm leading-relaxed">
                                                Your <strong>loadout</strong> is the set of acquired domain cards whose effects your PC can use during play. You can have up to <strong>5 domain cards</strong> in your loadout at one time.
                                            </p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <h4 class="font-outfit text-lg font-bold text-amber-300 mb-3">Vault</h4>
                                            <p class="text-slate-300 text-sm leading-relaxed">
                                                Once you've acquired six or more domain cards, you must choose five to keep in your loadout; the rest are considered to be in your <strong>vault</strong>. Vault cards are inactive and do not influence play.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl p-4 mt-6">
                                        <p class="text-emerald-100 leading-relaxed text-sm">
                                            <strong>Note:</strong> Your subclass, ancestry, and community cards don't count toward your loadout or vault and are always active and available.
                                        </p>
                                    </div>
                                </div>

                                <!-- Card Types Section -->
                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-8">
                                    <h3 class="font-outfit text-2xl font-bold text-amber-300 mb-6">Domain Card Types</h3>
                                    
                                    <div class="grid md:grid-cols-3 gap-6">
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <h4 class="font-outfit text-lg font-bold text-amber-400 mb-3">Abilities</h4>
                                            <p class="text-slate-300 text-sm leading-relaxed">
                                                Typically non-magical in nature, representing learned skills, techniques, or natural talents.
                                            </p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <h4 class="font-outfit text-lg font-bold text-amber-400 mb-3">Spells</h4>
                                            <p class="text-slate-300 text-sm leading-relaxed">
                                                Magical effects that harness the power of the domains to create supernatural results.
                                            </p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <h4 class="font-outfit text-lg font-bold text-amber-400 mb-3">Grimoires</h4>
                                            <p class="text-slate-300 text-sm leading-relaxed">
                                                Unique to the Codex domain, these grant access to a collection of less potent spells.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
