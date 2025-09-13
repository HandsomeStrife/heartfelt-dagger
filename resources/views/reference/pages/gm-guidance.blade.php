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
                                @include('reference.partials.navigation-menu', ['current_page' => 'gm-guidance'])
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-8">
                            <!-- Page Header -->
                            <div class="mb-8 flex items-start justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-indigo-500 rounded-xl flex items-center justify-center shadow-lg">
                                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h1 class="font-outfit text-3xl font-bold text-white">GM Guidance</h1>
                                        <p class="text-slate-400 text-sm mt-1">Running Adventures & Managing the Game</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-2 ml-4 flex-shrink-0">
                                    <span class="px-3 py-1 bg-purple-500/20 text-purple-300 text-sm rounded-full">
                                        Running an Adventure
                                    </span>
                                </div>
                            </div>

                            <!-- Page Content -->
                            <div class="prose prose-invert max-w-none" data-search-body>
                                <!-- Introduction -->
                                <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl p-6 mb-8">
                                    <p class="text-purple-100 leading-relaxed text-lg">
                                        The GM is responsible for <strong class="text-purple-300">guiding the narrative</strong> and roleplaying the world the PCs inhabit. This section provides you with advice for running Daggerheart: using the core mechanics; creating memorable encounters; planning exciting sessions; selecting, creating, and using GM moves; crafting a full campaign; running dynamic NPCs; and more.
                                    </p>
                                </div>

                                <!-- Foundation -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Foundation</h2>
                                    
                                    <div class="bg-indigo-500/10 border border-indigo-500/30 rounded-xl p-6">
                                        <p class="text-indigo-100 leading-relaxed mb-4">
                                            These three sections provide a foundation to help you get the most out of this game. The <strong class="text-indigo-300">"GM Principles"</strong> are your guiding star—when in doubt, return to these principles.
                                        </p>
                                    </div>
                                </div>

                                <!-- GM Principles -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">GM Principles</h2>
                                    
                                    <div class="space-y-6">
                                        <!-- Begin and End with Fiction -->
                                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-blue-300 mb-3">Begin and End with the Fiction</h3>
                                                    <p class="text-slate-300 leading-relaxed italic">
                                                        Use the fiction to drive mechanics, then connect the mechanics back to the fiction.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Collaborate -->
                                        <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-green-300 mb-3">Collaborate at All Times, Especially During Conflict</h3>
                                                    <p class="text-slate-300 leading-relaxed italic">
                                                        The PCs are the protagonists of the campaign; antagonism between player and GM should exist only in the fiction.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Fill the World -->
                                        <div class="bg-orange-500/10 border border-orange-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-orange-300 mb-3">Fill the World with Life, Wonder, and Danger</h3>
                                                    <p class="text-slate-300 leading-relaxed italic">
                                                        Showcase rich cultures, take the PCs to wondrous places, and introduce them to dangerous creatures.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Ask Questions -->
                                        <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-purple-300 mb-3">Ask Questions and Incorporate the Answers</h3>
                                                    <p class="text-slate-300 leading-relaxed italic">
                                                        Ensuring that the players' ideas are included results in a narrative that supports the whole group's creativity.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Give Every Roll Impact -->
                                        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-red-300 mb-3">Give Every Roll Impact</h3>
                                                    <p class="text-slate-300 leading-relaxed italic">
                                                        Only ask the players to roll during meaningful moments.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Play to Find Out -->
                                        <div class="bg-cyan-500/10 border border-cyan-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-cyan-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-cyan-300 mb-3">Play to Find Out What Happens</h3>
                                                    <p class="text-slate-300 leading-relaxed italic">
                                                        Be surprised by what the characters do, the choices they make, and the people they become.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Hold On Gently -->
                                        <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-xl font-bold text-yellow-600 mb-3">Hold On Gently</h3>
                                                    <p class="text-slate-300 leading-relaxed italic">
                                                        Don't worry if you need to abandon or alter something that came before.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- GM Practices -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">GM Practices</h2>
                                    
                                    <div class="grid md:grid-cols-2 gap-6">
                                        <!-- Cultivate Curiosity -->
                                        <div class="bg-indigo-500/10 border border-indigo-500/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-indigo-300 mb-3">Cultivate a Curious Table</h3>
                                            <p class="text-slate-300 text-sm leading-relaxed italic">
                                                Follow what catches the players' interest to foster an environment of creative inquiry.
                                            </p>
                                        </div>

                                        <!-- Gain Trust -->
                                        <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-emerald-300 mb-3">Gain Your Players' Trust</h3>
                                            <p class="text-slate-300 text-sm leading-relaxed italic">
                                                Act in good faith, follow through on your promises, admit your mistakes.
                                            </p>
                                        </div>

                                        <!-- Keep Moving -->
                                        <div class="bg-rose-500/10 border border-rose-500/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-rose-300 mb-3">Keep the Story Moving Forward</h3>
                                            <p class="text-slate-300 text-sm leading-relaxed italic">
                                                Maintain momentum and drive the narrative toward meaningful moments and decisions.
                                            </p>
                                        </div>

                                        <!-- More Practices -->
                                        <div class="bg-teal-500/10 border border-teal-500/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-teal-300 mb-3">Additional Practices</h3>
                                            <p class="text-slate-300 text-sm leading-relaxed">
                                                See the complete GM Guidance for detailed practices on session management, player engagement, and narrative flow.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick Reference -->
                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-8">
                                    <h3 class="font-outfit text-2xl font-bold text-amber-300 mb-6">GM Quick Reference</h3>
                                    
                                    <div class="grid md:grid-cols-2 gap-6">
                                        <div>
                                            <h4 class="font-outfit text-lg font-bold text-amber-400 mb-4">Core Principles</h4>
                                            <ul class="space-y-2 text-slate-300 text-sm">
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>Begin and end with fiction</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>Collaborate during conflict</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>Fill world with life, wonder, danger</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>Ask questions, incorporate answers</span>
                                                </li>
                                            </ul>
                                        </div>
                                        
                                        <div>
                                            <h4 class="font-outfit text-lg font-bold text-amber-400 mb-4">Key Practices</h4>
                                            <ul class="space-y-2 text-slate-300 text-sm">
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>Give every roll impact</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>Play to find out what happens</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>Hold on gently</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-400 mt-1">•</span>
                                                    <span>Cultivate curiosity at the table</span>
                                                </li>
                                            </ul>
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
