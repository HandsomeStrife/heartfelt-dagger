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
                                @include('reference.partials.navigation-menu', ['current_page' => 'character-creation'])
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-8">
                            <!-- Page Header -->
                            <div class="mb-8 flex items-start justify-between">
                                <h1 class="font-outfit text-3xl font-bold text-white">
                                    Character Creation
                                </h1>
                                
                                <div class="flex items-center space-x-2 ml-4 flex-shrink-0">
                                    <span class="px-3 py-1 bg-emerald-500/20 text-emerald-300 text-sm rounded-full">
                                        Character Creation
                                    </span>
                                </div>
                            </div>

                            <!-- Page Content -->
                            <div class="prose prose-invert max-w-none" data-search-body>
                                <p class="text-slate-300 leading-relaxed mb-8">
                                    Unless their table chooses to use pre-generated characters, each player creates their own PC by making a series of guided choices. Some of these decisions are purely narrative, meaning they only appear in or affect the game through roleplaying, but others are mechanical choices that affect the things their PC is able to do and which actions they're more (or less) likely to succeed at when making moves and taking action.
                                </p>

                                <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-4 mb-10">
                                    <p class="text-amber-100 leading-relaxed">
                                        <strong>Note:</strong> You can fill in your character's name, pronouns, and Character Description details at any point of the character creation process.
                                    </p>
                                </div>

                                <!-- Step 1 -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6 flex items-center">
                                        <span class="bg-amber-500 text-black rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold mr-3">1</span>
                                        Choose a Class and Subclass
                                    </h2>
                                    
                                    <p class="text-slate-300 leading-relaxed mb-6">
                                        <strong class="text-amber-300"><a href="{{ route('reference.page', 'classes') }}" class="text-amber-300 hover:text-amber-200 underline">Classes</a></strong> are role-based archetypes that determine which class features and <strong class="text-amber-300">domain cards</strong> a PC gains access to throughout the campaign. There are nine classes in this SRD: Bard, Druid, Guardian, Ranger, Rogue, Seraph, Sorcerer, Warrior, Wizard.
                                    </p>

                                    <ul class="text-slate-300 leading-relaxed mb-6 space-y-2">
                                        <li class="flex items-start">
                                            <span class="text-amber-400 mr-2 mt-1">•</span>
                                            Select a class and take its corresponding <strong>character sheet</strong> and <strong>character guide</strong> printouts. These sheets are for recording your PC's details; you'll update and reference them throughout the campaign.
                                        </li>
                                        <li class="flex items-start">
                                            <span class="text-amber-400 mr-2 mt-1">•</span>
                                            Every class begins with one or more unique <strong>class feature(s)</strong>, described in the front of each class's character sheet. If your class feature prompts you to make a selection, do so now.
                                        </li>
                                    </ul>

                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                        <p class="text-slate-300 leading-relaxed">
                                            <strong class="text-amber-300">Subclasses</strong> further refine a class archetype and reinforce its expression by granting access to unique <strong>subclass features</strong>. Each class comprises two subclasses. Select one of your class's subclasses and take its <strong>Foundation</strong> card.
                                        </p>
                                    </div>
                                </div>

                                <!-- Step 2 -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6 flex items-center">
                                        <span class="bg-amber-500 text-black rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold mr-3">2</span>
                                        Choose Your Heritage
                                    </h2>
                                    
                                    <p class="text-slate-300 leading-relaxed mb-6">
                                        Your character's <strong class="text-amber-300">heritage</strong> combines two elements: <strong><a href="{{ route('reference.page', 'ancestries') }}" class="text-amber-300 hover:text-amber-200 underline">ancestry</a></strong> and <strong><a href="{{ route('reference.page', 'communities') }}" class="text-amber-300 hover:text-amber-200 underline">community</a></strong>.
                                    </p>

                                    <div class="grid md:grid-cols-2 gap-6">
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-3">Ancestry</h3>
                                            <p class="text-slate-300 leading-relaxed mb-3">
                                                A character's <strong>ancestry</strong> reflects their lineage, impacting their physicality and granting them two unique <strong>ancestry features</strong>.
                                            </p>
                                            <p class="text-slate-300 leading-relaxed text-sm">
                                                Available ancestries: Clank, Drakona, Dwarf, Elf, Faerie, Faun, Firbolg, Fungirl, Galapa, Giant, Goblin, Halfling, Human, Infernis, Katari, Orc, Ribbet, Simian.
                                            </p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-3">Community</h3>
                                            <p class="text-slate-300 leading-relaxed mb-3">
                                                Your character's <strong>community</strong> represents their culture or environment of origin and grants them a <strong>community feature</strong>.
                                            </p>
                                            <p class="text-slate-300 leading-relaxed text-sm">
                                                Available communities: Highborne, Loreborne, Orderborne, Ridgeborne, Seaborne, Skyborne, Underborne, Wanderborne, Wildborne.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-4 mt-4">
                                        <p class="text-amber-100 leading-relaxed text-sm">
                                            <strong>Mixed Ancestry:</strong> To create a Mixed Ancestry, take the top (first-listed) ancestry feature from one ancestry and the bottom (second-listed) ancestry feature from another.
                                        </p>
                                    </div>
                                </div>

                                <!-- Step 3 -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6 flex items-center">
                                        <span class="bg-amber-500 text-black rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold mr-3">3</span>
                                        Assign Character Traits
                                    </h2>
                                    
                                    <p class="text-slate-300 leading-relaxed mb-6">
                                        Your character has six traits that represent their physical, mental, and social aptitude:
                                    </p>

                                    <div class="grid md:grid-cols-2 gap-4 mb-6">
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Agility</h3>
                                            <p class="text-slate-300 text-sm mb-2"><em>Use it to Sprint, Leap, Maneuver, etc.</em></p>
                                            <p class="text-slate-300 text-sm">A high Agility means you're fast on your feet, nimble on difficult terrain, and quick to react to danger.</p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Strength</h3>
                                            <p class="text-slate-300 text-sm mb-2"><em>Use it to Lift, Smash, Grapple, etc.</em></p>
                                            <p class="text-slate-300 text-sm">A high Strength means you're better at feats that test your physical prowess and stamina.</p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Finesse</h3>
                                            <p class="text-slate-300 text-sm mb-2"><em>Use it to Control, Hide, Tinker, etc.</em></p>
                                            <p class="text-slate-300 text-sm">A high Finesse means you're skilled at tasks that require accuracy, stealth, or the utmost control.</p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Instinct</h3>
                                            <p class="text-slate-300 text-sm mb-2"><em>Use it to Perceive, Sense, Navigate, etc.</em></p>
                                            <p class="text-slate-300 text-sm">A high Instinct means you have a keen sense of your surroundings and a natural intuition.</p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Presence</h3>
                                            <p class="text-slate-300 text-sm mb-2"><em>Use it to Charm, Perform, Deceive, etc.</em></p>
                                            <p class="text-slate-300 text-sm">A high Presence means you have a strong force of personality and a facility for social situations.</p>
                                        </div>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Knowledge</h3>
                                            <p class="text-slate-300 text-sm mb-2"><em>Use it to Recall, Analyze, Comprehend, etc.</em></p>
                                            <p class="text-slate-300 text-sm">A high Knowledge means you know information others don't and understand how to apply your mind through deduction.</p>
                                        </div>
                                    </div>

                                    <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl p-6">
                                        <p class="text-emerald-100 leading-relaxed">
                                            <strong>Trait Assignment:</strong> When you "roll with a trait," that trait's modifier is added to the roll's total. Assign the modifiers <strong>+2, +1, +1, +0, +0, -1</strong> to your character's traits in any order you wish.
                                        </p>
                                    </div>
                                </div>

                                <!-- Step 4 -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6 flex items-center">
                                        <span class="bg-amber-500 text-black rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold mr-3">4</span>
                                        Record Additional Character Information
                                    </h2>
                                    
                                    <div class="grid md:grid-cols-2 gap-6">
                                        <div class="space-y-4">
                                            <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                                <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Level</h3>
                                                <p class="text-slate-300 text-sm">Characters start a new campaign at <strong>Level 1</strong>.</p>
                                            </div>
                                            <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                                <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Evasion</h3>
                                                <p class="text-slate-300 text-sm">Represents your character's ability to avoid damage. Determined by your class.</p>
                                            </div>
                                            <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                                <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Hit Points (HP)</h3>
                                                <p class="text-slate-300 text-sm">An abstract measure of your physical health. Determined by your class.</p>
                                            </div>
                                        </div>
                                        <div class="space-y-4">
                                            <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                                <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Stress</h3>
                                                <p class="text-slate-300 text-sm">Reflects your ability to withstand mental and emotional strain. Every PC starts with <strong>6 Stress slots</strong>.</p>
                                            </div>
                                            <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-4">
                                                <h3 class="font-outfit text-lg font-bold text-amber-300 mb-2">Hope</h3>
                                                <p class="text-slate-300 text-sm">A metacurrency that fuels special moves and certain abilities. All PCs start with <strong>2 Hope</strong>.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 5 -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6 flex items-center">
                                        <span class="bg-amber-500 text-black rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold mr-3">5</span>
                                        Choose Your Starting Equipment
                                    </h2>
                                    
                                    <div class="space-y-6">
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-4">Weapons</h3>
                                            <p class="text-slate-300 leading-relaxed mb-4">
                                                Choose your <strong><a href="{{ route('reference.page', 'weapons') }}" class="text-amber-300 hover:text-amber-200 underline">weapon(s)</a></strong>:
                                            </p>
                                            <ul class="text-slate-300 leading-relaxed space-y-2">
                                                <li class="flex items-start">
                                                    <span class="text-amber-400 mr-2 mt-1">•</span>
                                                    Select from the Tier 1 Weapon Tables. Either a <strong>two-handed primary weapon</strong> or a <strong>one-handed primary weapon and a one-handed secondary weapon</strong>.
                                                </li>
                                                <li class="flex items-start">
                                                    <span class="text-amber-400 mr-2 mt-1">•</span>
                                                    At Level 1, your <strong>Proficiency</strong> is 1. Calculate your <strong>damage roll</strong> by combining your Proficiency with your weapon's damage dice.
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-4">Armor</h3>
                                            <p class="text-slate-300 leading-relaxed mb-4">
                                                Choose and equip one set of <strong><a href="{{ route('reference.page', 'armor') }}" class="text-amber-300 hover:text-amber-200 underline">armor</a></strong> from the Tier 1 Armor Table.
                                            </p>
                                            <ul class="text-slate-300 leading-relaxed space-y-2">
                                                <li class="flex items-start">
                                                    <span class="text-amber-400 mr-2 mt-1">•</span>
                                                    Add your character's level to your equipped armor's <strong>Base Score</strong> (at creation, your level is 1).
                                                </li>
                                                <li class="flex items-start">
                                                    <span class="text-amber-400 mr-2 mt-1">•</span>
                                                    Your <strong>Armor Score</strong> equals your equipped armor's Base Score plus any permanent bonuses.
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-amber-300 mb-4">Starting Items</h3>
                                            <p class="text-slate-300 leading-relaxed mb-4">Add the following items to your Inventory:</p>
                                            <ul class="text-slate-300 leading-relaxed space-y-2">
                                                <li class="flex items-start">
                                                    <span class="text-amber-400 mr-2 mt-1">•</span>
                                                    A torch, 50 feet of rope, basic supplies, and a handful of gold
                                                </li>
                                                <li class="flex items-start">
                                                    <span class="text-amber-400 mr-2 mt-1">•</span>
                                                    EITHER a Minor Health Potion (clear 1d4 Hit Points) OR a Minor Stamina Potion (clear 1d4 Stress)
                                                </li>
                                                <li class="flex items-start">
                                                    <span class="text-amber-400 mr-2 mt-1">•</span>
                                                    One of the class-specific items listed on your character guide
                                                </li>
                                                <li class="flex items-start">
                                                    <span class="text-amber-400 mr-2 mt-1">•</span>
                                                    If applicable, whichever class-specific item you selected to carry your spells
                                                </li>
                                                <li class="flex items-start">
                                                    <span class="text-amber-400 mr-2 mt-1">•</span>
                                                    Any other GM-approved items you'd like to have at the start of the game
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- Steps 6-9 -->
                                <div class="grid md:grid-cols-2 gap-8">
                                    <!-- Step 6 -->
                                    <div class="mb-8">
                                        <h2 class="font-outfit text-xl font-bold text-amber-400 mb-4 flex items-center">
                                            <span class="bg-amber-500 text-black rounded-full w-7 h-7 flex items-center justify-center text-sm font-bold mr-3">6</span>
                                            Create Your Background
                                        </h2>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <p class="text-slate-300 leading-relaxed mb-4">
                                                Develop your character's <strong class="text-amber-300">background</strong> by answering the <strong>background questions</strong> in your character guide, modifying or replacing them if they don't fit the character you want to play.
                                            </p>
                                            <div class="bg-amber-500/10 border border-amber-500/30 rounded-lg p-3">
                                                <p class="text-amber-100 text-sm">
                                                    <strong>Note:</strong> Your background has no explicit mechanical effect, but it greatly affects the character you'll play and the prep the GM will do.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Step 7 -->
                                    <div class="mb-8">
                                        <h2 class="font-outfit text-xl font-bold text-amber-400 mb-4 flex items-center">
                                            <span class="bg-amber-500 text-black rounded-full w-7 h-7 flex items-center justify-center text-sm font-bold mr-3">7</span>
                                            Create Your Experiences
                                        </h2>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <p class="text-slate-300 leading-relaxed mb-4">
                                                An <strong class="text-amber-300">Experience</strong> is a word or phrase used to encapsulate a specific set of skills, personality traits, or aptitudes your character has acquired over the course of their life.
                                            </p>
                                            <ul class="text-slate-300 text-sm space-y-1">
                                                <li class="flex items-start">
                                                    <span class="text-amber-400 mr-2 mt-1">•</span>
                                                    Your PC gets <strong>two Experiences</strong> at character creation, each with a <strong>+2 modifier</strong>.
                                                </li>
                                                <li class="flex items-start">
                                                    <span class="text-amber-400 mr-2 mt-1">•</span>
                                                    Can't be too broadly applicable or grant specific mechanical benefits.
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Step 8 -->
                                    <div class="mb-8">
                                        <h2 class="font-outfit text-xl font-bold text-amber-400 mb-4 flex items-center">
                                            <span class="bg-amber-500 text-black rounded-full w-7 h-7 flex items-center justify-center text-sm font-bold mr-3">8</span>
                                            Choose Domain Cards
                                        </h2>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <p class="text-slate-300 leading-relaxed mb-4">
                                                Your class has access to two of the nine <strong><a href="{{ route('reference.page', 'domains') }}" class="text-amber-300 hover:text-amber-200 underline">Domains</a></strong> included in the core set.
                                            </p>
                                            <p class="text-slate-300 text-sm">
                                                Choose <strong>two cards</strong> from your class's domains. You can take one card from each domain or two from a single domain, whichever you prefer.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Step 9 -->
                                    <div class="mb-8">
                                        <h2 class="font-outfit text-xl font-bold text-amber-400 mb-4 flex items-center">
                                            <span class="bg-amber-500 text-black rounded-full w-7 h-7 flex items-center justify-center text-sm font-bold mr-3">9</span>
                                            Create Your Connections
                                        </h2>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                            <p class="text-slate-300 leading-relaxed mb-4">
                                                <strong class="text-amber-300">Connections</strong> are the relationships between the PCs. To create connections:
                                            </p>
                                            <ul class="text-slate-300 text-sm space-y-2">
                                                <li class="flex items-start">
                                                    <span class="text-amber-400 mr-2 mt-1">•</span>
                                                    Have each player describe their characters to one another
                                                </li>
                                                <li class="flex items-start">
                                                    <span class="text-amber-400 mr-2 mt-1">•</span>
                                                    Discuss potential connections using your character guide questions
                                                </li>
                                                <li class="flex items-start">
                                                    <span class="text-amber-400 mr-2 mt-1">•</span>
                                                    Suggest at least one connection between your character and each other PC
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- Example Experiences Section -->
                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-8 mt-12">
                                    <h3 class="font-outfit text-2xl font-bold text-amber-300 mb-6">Example Experiences</h3>
                                    
                                    <div class="grid md:grid-cols-2 gap-6">
                                        <div>
                                            <h4 class="font-outfit text-lg font-bold text-amber-400 mb-3">Backgrounds</h4>
                                            <p class="text-slate-300 text-sm leading-relaxed">
                                                Assassin, Blacksmith, Bodyguard, Bounty Hunter, Chef to the Royal Family, Circus Performer, Con Artist, Fallen Monarch, Field Medic, High Priestess, Merchant, Noble, Pirate, Politician, Runaway, Scholar, Sellsword, Soldier, Storyteller, Thief, World Traveler
                                            </p>
                                        </div>
                                        <div>
                                            <h4 class="font-outfit text-lg font-bold text-amber-400 mb-3">Characteristics</h4>
                                            <p class="text-slate-300 text-sm leading-relaxed">
                                                Affable, Battle-Hardened, Bookworm, Charming, Cowardly, Friend to All, Helpful, Intimidating Presence, Leader, Lone Wolf, Loyal, Observant, Prankster, Silver Tongue, Sticky Fingers, Stubborn to a Fault, Survivor, Young and Naive
                                            </p>
                                        </div>
                                        <div>
                                            <h4 class="font-outfit text-lg font-bold text-amber-400 mb-3">Specialties</h4>
                                            <p class="text-slate-300 text-sm leading-relaxed">
                                                Acrobat, Gambler, Healer, Inventor, Magical Historian, Mapmaker, Master of Disguise, Navigator, Sharpshooter, Survivalist, Swashbuckler, Tactician
                                            </p>
                                        </div>
                                        <div>
                                            <h4 class="font-outfit text-lg font-bold text-amber-400 mb-3">Skills</h4>
                                            <p class="text-slate-300 text-sm leading-relaxed">
                                                Animal Whisperer, Barter, Deadly Aim, Fast Learner, Incredible Strength, Liar, Light Feet, Negotiator, Photographic Memory, Quick Hands, Repair, Scavenger, Tracker
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-6">
                                        <h4 class="font-outfit text-lg font-bold text-amber-400 mb-3">Phrases</h4>
                                        <p class="text-slate-300 text-sm leading-relaxed">
                                            Catch Me If You Can, Fake It Till You Make It, First Time's the Charm, Hold the Line, I Won't Let You Down, I'll Catch You, I've Got Your Back, Knowledge Is Power, Nature's Friend, Never Again, No One Left Behind, Pick on Someone Your Own Size, The Show Must Go On, This Is Not a Negotiation, Wolf in Sheep's Clothing
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
</x-layouts.app>
