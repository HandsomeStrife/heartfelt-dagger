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
                                @include('reference.partials.navigation-menu', ['current_page' => 'the-basics'])
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-8">
                            <!-- Page Header -->
                            <div class="mb-8 flex items-start justify-between">
                                <h1 class="font-outfit text-3xl font-bold text-white">
                                    The Basics
                                </h1>
                                
                                <div class="flex items-center space-x-2 ml-4 flex-shrink-0">
                                    <span class="px-3 py-1 bg-amber-500/20 text-amber-300 text-sm rounded-full">
                                        Introduction
                                    </span>
                                </div>
                            </div>

                            <!-- Page Content -->
                            <div class="prose prose-invert max-w-none" data-search-body>
                                <div class="mb-10">
                                    <h2 class="font-outfit text-xl font-bold text-amber-400 mt-0 mb-6">What is Daggerheart?</h2>
                                    <p class="text-slate-300 leading-relaxed mb-6">
                                        Daggerheart is a tabletop roleplaying game for one Game Master ("GM") and 2-5 players. Each game session lasts about 2-4 hours, and Daggerheart can be played as a one-shot or a multi-session campaign of any length.
                                    </p>
                                    <p class="text-slate-300 leading-relaxed mb-6">
                                        During a session of Daggerheart the GM describes situations, narrates events, and controls any adversaries or obstacles that the Player Characters ("PCs") encounter. The players, in turn, roleplay their PCs' reactions to the scenario presented by the GM. If the outcome of a player's action depends on fate or fortune, the GM calls for an action roll.
                                    </p>
                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6 mb-8">
                                        <p class="text-slate-300 leading-relaxed">
                                            <strong class="text-amber-300">Duality Dice System:</strong> When a player makes an action roll, they utilize Duality Dice—two differently colored 12-sided dice ("d12s") representing Hope and Fear. The Duality Dice are rolled, relevant modifiers are added to the results, and the total is compared to a Difficulty set by the GM. If the total meets or beats the Difficulty, the player succeeds. If it's lower, they fail. In addition, the situation changes based on which Duality Die rolls higher, either giving the player helpful Hope tokens or generating terrifying Fear tokens for the GM.
                                        </p>
                                    </div>
                                </div>

                                <div class="mb-10">
                                    <h2 class="font-outfit text-xl font-bold text-amber-400 mb-6">The Golden Rule</h2>
                                    <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-6 mb-6">
                                        <p class="text-amber-100 leading-relaxed font-medium">
                                            The most important rule of Daggerheart is to <strong>make the game your own</strong>. The rules included in this SRD are designed to help you enjoy the experience at the table, but everyone has a different approach to interpreting rules and telling stories.
                                        </p>
                                    </div>
                                    <p class="text-slate-300 leading-relaxed">
                                        The rules should never get in the way of the story you want to tell, the characters you want to play, or the adventures you want to have. As long as your group agrees, everything can be adjusted to fit your play style. If there's a rule you'd rather ignore or modify, feel free to implement any change with your table's consent.
                                    </p>
                                </div>

                                <div class="mb-8">
                                    <h2 class="font-outfit text-xl font-bold text-amber-400 mb-6">Rulings Over Rules</h2>
                                    <p class="text-slate-300 leading-relaxed mb-6">
                                        While playing Daggerheart, the GM and players should always prioritize <strong class="text-amber-300">rulings over rules</strong>. This SRD offers answers for many questions your table may have about the game, but it won't answer all of them. When you're in doubt about how a rule applies, the GM should make a ruling that aligns with the narrative.
                                    </p>
                                    
                                    <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-6 mb-6">
                                        <h3 class="font-outfit text-lg font-bold text-amber-300 mb-3">Example: The Grappler Weapon</h3>
                                        <p class="text-slate-300 leading-relaxed mb-3">
                                            Daggerheart has a weapon called a grappler that lets you pull a target close to you. If you try to use it to pull an entire castle, the weapon text doesn't forbid you from doing that—but it doesn't make sense within the narrative.
                                        </p>
                                        <p class="text-slate-300 leading-relaxed">
                                            Instead, the GM might rule that you pull a few bricks out, or pull yourself toward the wall instead.
                                        </p>
                                    </div>

                                    <p class="text-slate-300 leading-relaxed mb-6">
                                        Similarly, if your character does something that would logically result in immediate death—such as diving into an active volcano without protection—you might not get to make one of Daggerheart's death moves, which normally give you control of your character's fate in their final moments. This kind of consequence should be made clear before the action is completed, and it should always follow the logic of the world.
                                    </p>

                                    <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6">
                                        <p class="text-slate-300 leading-relaxed">
                                            <strong class="text-amber-300">Remember:</strong> As a narrative-focused game, Daggerheart is not a place where technical, out-of-context interpretations of the rules are encouraged. Everything should flow back to the fiction, and the GM has the authority and responsibility to make rulings about how rules are applied to underscore that fiction.
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
