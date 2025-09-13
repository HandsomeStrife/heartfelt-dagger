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
                                @include('reference.partials.navigation-menu', ['current_page' => 'what-is-this'])
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-8">
                            <!-- Page Header -->
                            <div class="mb-8 flex items-start justify-between">
                                <h1 class="font-outfit text-3xl font-bold text-white">
                                    What Is This?
                                </h1>
                                
                                <div class="flex items-center space-x-2 ml-4 flex-shrink-0">
                                    <span class="px-3 py-1 bg-amber-500/20 text-amber-300 text-sm rounded-full">
                                        Introduction
                                    </span>
                                </div>
                            </div>

                            <!-- Page Content -->
                            <div class="prose prose-invert max-w-none" data-search-body>
                                <p class="text-slate-300 leading-relaxed mb-6">
                                    This is the Daggerheart SRD (System Reference Document). It is a repository of the mechanical elements of the Daggerheart system, edited and organized for clarity, conciseness, and quick reference.
                                </p>

                                <p class="text-slate-300 leading-relaxed mb-6">
                                    You can use this SRD in several ways:
                                </p>

                                <ul class="text-slate-300 leading-relaxed mb-6 space-y-2">
                                    <li class="flex items-start">
                                        <span class="text-amber-400 mr-2 mt-1">•</span>
                                        To quickly look up Daggerheart's rules-as-written during gameplay sessions.
                                    </li>
                                    <li class="flex items-start">
                                        <span class="text-amber-400 mr-2 mt-1">•</span>
                                        To ensure any homebrew content you create or publish conforms with Daggerheart's core ruleset.
                                    </li>
                                    <li class="flex items-start">
                                        <span class="text-amber-400 mr-2 mt-1">•</span>
                                        To provide copy text made available by Darrington Press for your own publications under their <a href="https://www.darringtonpress.com/license" class="text-amber-400 hover:text-amber-300 underline" target="_blank" rel="noopener">Community Gaming License</a>.
                                    </li>
                                    <li class="flex items-start">
                                        <span class="text-amber-400 mr-2 mt-1">•</span>
                                        To better understand the mechanics of Daggerheart, absent the flavor and setting information, so you can bend or break them in the process of making your own content.
                                    </li>
                                </ul>

                                <div class="bg-slate-800/30 border border-slate-600/30 rounded-xl p-6 mb-8">
                                    <p class="text-slate-300 leading-relaxed mb-4">
                                        <strong class="text-amber-300">Important Note:</strong> The Daggerheart SRD is not a replacement for the core rulebook, which contains setting information, additional examples of various gameplay elements, and tons of great advice for playing Daggerheart—not to mention gorgeous artwork and layout.
                                    </p>
                                    <p class="text-slate-300 leading-relaxed">
                                        In short, it is Daggerheart, the system, boiled down to the bones—a lean and clean offering without all the flavor, style, and supporting material that makes the core rulebook such an evocative and enjoyable read.
                                    </p>
                                </div>

                                <p class="text-slate-300 leading-relaxed text-center text-lg font-medium">
                                    We hope this document proves useful to your table. <span class="text-amber-400">Happy adventuring!</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
