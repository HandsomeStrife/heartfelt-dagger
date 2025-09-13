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
                                @include('reference.partials.navigation-menu', ['current_page' => 'campaign-frames'])
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-8">
                            <!-- Page Header -->
                            <div class="mb-8 flex items-start justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 bg-gradient-to-br from-violet-500 to-purple-500 rounded-xl flex items-center justify-center shadow-lg">
                                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h1 class="font-outfit text-3xl font-bold text-white">Campaign Frames</h1>
                                        <p class="text-slate-400 text-sm mt-1">Structured Campaign Types & Settings</p>
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
                                <!-- Campaign Frame Overview -->
                                <div class="bg-violet-500/10 border border-violet-500/30 rounded-xl p-6 mb-8">
                                    <p class="text-violet-100 leading-relaxed text-lg">
                                        A <strong class="text-violet-300">campaign frame</strong> provides inspiration, tools, and mechanics to support a particular type of story at the table.
                                    </p>
                                </div>

                                <!-- Complexity Rating -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Complexity Rating</h2>
                                    
                                    <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6">
                                        <div class="flex items-start gap-4">
                                            <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="font-outfit text-lg font-bold text-blue-300 mb-3">Understanding Complexity</h3>
                                                <p class="text-slate-300 text-sm leading-relaxed">
                                                    Every campaign frame has a <strong class="text-blue-300">complexity rating</strong> that indicates how much its mechanics deviate from or expand upon the Daggerheart core ruleset.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Campaign Frame Components -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Campaign Frame Components</h2>
                                    
                                    <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl p-6 mb-6">
                                        <p class="text-purple-100 leading-relaxed">
                                            Each campaign frame includes the following sections:
                                        </p>
                                    </div>

                                    <div class="space-y-4">
                                        <!-- Pitch -->
                                        <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-green-300 mb-2">Pitch</h3>
                                                    <p class="text-slate-300 text-sm">A compelling summary to present to players</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Tone & Themes -->
                                        <div class="bg-orange-500/10 border border-orange-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m-9 0h10m-10 0a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V6a2 2 0 00-2-2M9 10h6" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-orange-300 mb-2">Tone, Feel, Themes, and Touchstones</h3>
                                                    <p class="text-slate-300 text-sm">Suggestions and guidance for campaign atmosphere</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Background -->
                                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-blue-300 mb-2">Background</h3>
                                                    <p class="text-slate-300 text-sm">An overview of the campaign's setting and context</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Character Integration -->
                                        <div class="bg-teal-500/10 border border-teal-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-10 h-10 bg-teal-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-teal-300 mb-2">Character Integration</h3>
                                                    <p class="text-slate-300 text-sm">Guidance for fitting communities, ancestries, and classes into the setting</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Principles -->
                                        <div class="bg-indigo-500/10 border border-indigo-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-indigo-300 mb-2">Principles</h3>
                                                    <p class="text-slate-300 text-sm">Core guidelines for players and GMs to focus on during the campaign</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Setting Distinctions -->
                                        <div class="bg-pink-500/10 border border-pink-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-10 h-10 bg-pink-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-pink-300 mb-2">Setting Distinctions</h3>
                                                    <p class="text-slate-300 text-sm">Unique elements that make this campaign frame special</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Inciting Incident -->
                                        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-red-300 mb-2">Inciting Incident</h3>
                                                    <p class="text-slate-300 text-sm">The event or situation that launches the campaign</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Special Mechanics -->
                                        <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-yellow-600 mb-2">Special Mechanics</h3>
                                                    <p class="text-slate-300 text-sm">Unique rules and systems to use during the campaign</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Session Zero Questions -->
                                        <div class="bg-cyan-500/10 border border-cyan-500/30 rounded-xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-10 h-10 bg-cyan-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-outfit text-lg font-bold text-cyan-300 mb-2">Session Zero Questions</h3>
                                                    <p class="text-slate-300 text-sm">Questions to consider during campaign setup and character creation</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Available Campaign Frames -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Available Campaign Frames</h2>
                                    
                                    <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl p-8">
                                        <div class="flex items-start gap-6">
                                            <div class="w-16 h-16 bg-emerald-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="font-outfit text-2xl font-bold text-emerald-300 mb-4">The Witherwild</h3>
                                                <p class="text-slate-300 leading-relaxed mb-6">
                                                    The first official campaign frame for Daggerheart, featuring a unique setting with specialized mechanics and thematic elements.
                                                </p>
                                                
                                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-6">
                                                    <h4 class="font-outfit text-lg font-bold text-emerald-400 mb-3">Complete Campaign Package</h4>
                                                    <p class="text-slate-300 text-sm mb-4">
                                                        The Witherwild includes all the components listed above, providing a complete framework for running a themed campaign with unique mechanics and story elements.
                                                    </p>
                                                    <div class="bg-slate-700/30 border border-slate-600/30 rounded-lg p-3">
                                                        <p class="text-emerald-200 text-xs">
                                                            <strong>Note:</strong> You can find each campaign frame map in the appendix of the core rulebook or at www.daggerheart.com/downloads.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Using Campaign Frames -->
                                <div class="mb-12">
                                    <h2 class="font-outfit text-2xl font-bold text-amber-400 mb-6">Using Campaign Frames</h2>
                                    
                                    <div class="grid md:grid-cols-2 gap-6">
                                        <!-- Implementation -->
                                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-blue-300 mb-4">Implementation</h3>
                                            <ul class="space-y-2 text-slate-300 text-sm">
                                                <li class="flex items-start gap-2">
                                                    <span class="text-blue-400 mt-1">•</span>
                                                    <span>Present the pitch to players during session zero</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-blue-400 mt-1">•</span>
                                                    <span>Integrate character creation with setting elements</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-blue-400 mt-1">•</span>
                                                    <span>Use special mechanics to enhance the theme</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-blue-400 mt-1">•</span>
                                                    <span>Launch with the provided inciting incident</span>
                                                </li>
                                            </ul>
                                        </div>

                                        <!-- Customization -->
                                        <div class="bg-violet-500/10 border border-violet-500/30 rounded-xl p-6">
                                            <h3 class="font-outfit text-lg font-bold text-violet-300 mb-4">Customization</h3>
                                            <ul class="space-y-2 text-slate-300 text-sm">
                                                <li class="flex items-start gap-2">
                                                    <span class="text-violet-400 mt-1">•</span>
                                                    <span>Adapt elements to fit your table's preferences</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-violet-400 mt-1">•</span>
                                                    <span>Mix and match components from different frames</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-violet-400 mt-1">•</span>
                                                    <span>Create your own campaign frames using this structure</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-violet-400 mt-1">•</span>
                                                    <span>Scale complexity based on group experience</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- Campaign Frame Resources -->
                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-8">
                                    <h3 class="font-outfit text-2xl font-bold text-amber-300 mb-6">Campaign Frame Resources</h3>
                                    
                                    <div class="bg-violet-500/10 border border-violet-500/30 rounded-lg p-6 text-center">
                                        <div class="w-12 h-12 bg-violet-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                            </svg>
                                        </div>
                                        <h4 class="font-outfit text-lg font-bold text-violet-300 mb-3">Complete Campaign Frameworks</h4>
                                        <p class="text-slate-300 text-sm mb-4">Structured campaign types with detailed guidance, special mechanics, and thematic elements to create memorable adventures</p>
                                        <div class="bg-slate-800/30 border border-slate-600/30 rounded-lg p-3">
                                            <p class="text-violet-200 text-xs">Access complete campaign frames including The Witherwild, with maps, mechanics, and detailed guidance for running themed campaigns</p>
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
