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
                                @include('reference.partials.navigation-menu', ['current_page' => 'ancestries'])
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-8">
                            <!-- Page Header -->
                            <div class="mb-8 flex items-start justify-between">
                                <h1 class="font-outfit text-3xl font-bold text-white">
                                    Ancestries
                                </h1>
                                
                                <div class="flex items-center space-x-2 ml-4 flex-shrink-0">
                                    <span class="px-3 py-1 bg-emerald-500/20 text-emerald-300 text-sm rounded-full">
                                        Core Materials
                                    </span>
                                </div>
                            </div>

                            <!-- Page Content -->
                            <div class="prose prose-invert max-w-none" data-search-body>
                                <p class="text-slate-300 leading-relaxed mb-8">
                                    <strong class="text-amber-300">Ancestries</strong> represent your character's lineage, which affects their physical appearance and access to certain special abilities. The following section describes each ancestry in Daggerheart and the characteristics generally shared by members of that ancestry. However, each player decides how their character aligns with the "standard" or "average" expression of their ancestry.
                                </p>

                                <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl p-6 mb-8">
                                    <p class="text-emerald-100 leading-relaxed">
                                        In Daggerheart, the term <strong>"people"</strong> is used to refer to all ancestries, as individuals from all lineages possess unique characteristics and cultures, as well as personhood.
                                    </p>
                                </div>

                                <p class="text-slate-300 leading-relaxed mb-8">
                                    Some ancestries are described using the term <strong class="text-amber-300">"humanoid."</strong> This does not imply any genetic relation to "humans," which is a distinct ancestry within Daggerheart. Instead, it refers to the set of physical characteristics humans will recognize from their own anatomy, such as bipedal movement, upright posture, facial layout, and more. These traits vary by ancestry and individual, though "humanoid" should still provide a useful frame of reference.
                                </p>

                                <!-- Available Ancestries -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">The 18 Ancestries</h2>
                                    <p class="text-slate-300 leading-relaxed mb-6">
                                        The core ruleset includes the following ancestries:
                                    </p>

                                    <div class="grid md:grid-cols-3 gap-4 mb-8">
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4 text-center">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Clank</h3>
                                            <p class="text-slate-400 text-sm">Mechanical beings</p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4 text-center">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Drakona</h3>
                                            <p class="text-slate-400 text-sm">Dragon-blooded</p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4 text-center">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Dwarf</h3>
                                            <p class="text-slate-400 text-sm">Hardy mountain folk</p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4 text-center">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Elf</h3>
                                            <p class="text-slate-400 text-sm">Graceful and keen</p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4 text-center">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Faerie</h3>
                                            <p class="text-slate-400 text-sm">Small magical beings</p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4 text-center">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Faun</h3>
                                            <p class="text-slate-400 text-sm">Nature-touched</p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4 text-center">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Firbolg</h3>
                                            <p class="text-slate-400 text-sm">Giant-kin</p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4 text-center">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Fungril</h3>
                                            <p class="text-slate-400 text-sm">Mushroom people</p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4 text-center">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Galapa</h3>
                                            <p class="text-slate-400 text-sm">Turtle-like beings</p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4 text-center">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Giant</h3>
                                            <p class="text-slate-400 text-sm">Large humanoids</p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4 text-center">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Goblin</h3>
                                            <p class="text-slate-400 text-sm">Small and cunning</p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4 text-center">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Halfling</h3>
                                            <p class="text-slate-400 text-sm">Small and cheerful</p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4 text-center">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Human</h3>
                                            <p class="text-slate-400 text-sm">Versatile and adaptable</p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4 text-center">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Infernis</h3>
                                            <p class="text-slate-400 text-sm">Fire-touched</p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4 text-center">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Katari</h3>
                                            <p class="text-slate-400 text-sm">Feline humanoids</p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4 text-center">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Orc</h3>
                                            <p class="text-slate-400 text-sm">Strong warriors</p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4 text-center">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Ribbet</h3>
                                            <p class="text-slate-400 text-sm">Amphibious folk</p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4 text-center">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Simiah</h3>
                                            <p class="text-slate-400 text-sm">Ape-like beings</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Ancestry Features -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Ancestry Features</h2>
                                    <p class="text-slate-300 leading-relaxed mb-6">
                                        Each ancestry grants <strong class="text-amber-300">two ancestry features</strong>. While some features specify the anatomy of any player character of that ancestry's anatomy, players determine their characters' physical form. Work with the GM to re-flavor any implied traits that don't align with your character concept.
                                    </p>
                                    <p class="text-slate-300 leading-relaxed mb-8">
                                        If you'd like to make a character who combines more than one ancestry, see "Mixed Ancestry" below.
                                    </p>
                                </div>

                                <!-- Mixed Ancestry Section -->
                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-8">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-300 mb-6">Mixed Ancestry</h2>
                                    <p class="text-slate-300 leading-relaxed mb-6">
                                        Families within the world of Daggerheart are as unique as the peoples and cultures that inhabit it. Anyone's appearance and abilities can be shaped by blood, magic, proximity, or a variety of other factors.
                                    </p>
                                    <p class="text-slate-300 leading-relaxed mb-8">
                                        If you decide that your character is a descendant of multiple ancestries and you want to mechanically represent that in the game, use the steps below:
                                    </p>

                                    <div class="space-y-6">
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-xl font-bold text-amber-400 mb-4 flex items-center">
                                                <span class="bg-amber-500 text-black rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold mr-3">1</span>
                                                Determine Ancestry Combination
                                            </h3>
                                            <p class="text-slate-300 leading-relaxed mb-4">
                                                When you choose an ancestry at character creation, write down how your character identifies themself in the Heritage section of your character sheet.
                                            </p>
                                            <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-lg p-4">
                                                <p class="text-emerald-100 text-sm">
                                                    <strong>Example:</strong> If your character is descended from both goblins and orcs, you could use a hybridized term, such as "goblin-orc." To describe your ancestry, list only the ancestry you more closely identify with (e.g., just "goblin" or just "orc"), or invent a new term, such as "toothling."
                                                </p>
                                            </div>
                                        </div>

                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-xl font-bold text-amber-400 mb-4 flex items-center">
                                                <span class="bg-amber-500 text-black rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold mr-3">2</span>
                                                Choose Ancestry Features
                                            </h3>
                                            <p class="text-slate-300 leading-relaxed mb-4">
                                                Work with your GM to choose two features from the ancestries in your character's lineage. You must choose the <strong>first feature from one ancestry</strong> and the <strong>second from another</strong>. Write both down on a notecard you can keep with your other cards or next to your character sheet.
                                            </p>
                                            <div class="bg-amber-500/10 border border-amber-500/30 rounded-lg p-4">
                                                <p class="text-amber-100 text-sm">
                                                    <strong>Example:</strong> If you are making a goblin-orc, you might take the "Surefooted" and "Tusks" features or the "Sturdy" and "Danger Sense" features. You can't take both the "Surefooted" and "Sturdy" features because both are the first features listed on their respective ancestry cards.
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
    </div>
</x-layouts.app>
