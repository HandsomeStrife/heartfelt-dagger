<nav data-testid="main-navigation" class="bg-gradient-to-r from-slate-800 to-slate-900 border-b border-amber-500/30 z-20">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo/Brand -->
            <div class="flex items-center">
                <a href="/" class="flex items-center space-x-2">
                    <img src="{{ asset('img/logo.png') }}" alt="Heartfelt Dagger Logo" class="w-auto h-8">
                    <span class="font-outfit text-xl sm:text-2xl text-white tracking-wide hidden sm:block">HeartfeltDagger</span>
                </a>
            </div>

            <!-- Right Side: Navigation + User Menu -->
            <div class="flex items-center gap-8" x-data="{ charactersOpen: false, toolsOpen: false }">
                @auth
                    <!-- Desktop navigation (authenticated) -->
                    <div class="hidden md:flex items-center gap-6">
                        <a data-testid="nav-campaigns" href="{{ route('campaigns.index') }}" class="text-white/80 hover:text-white font-medium transition-colors font-roboto">Campaigns</a>

                        <!-- Characters dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button data-testid="nav-characters" @click="open = !open" class="inline-flex items-center gap-2 text-white/80 hover:text-white font-medium transition-colors font-roboto">
                                Characters
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div x-show="open" x-cloak @click.away="open = false" x-transition class="absolute right-0 mt-2 w-56 bg-slate-800 border border-slate-600 rounded-lg shadow-xl z-[9999]">
                                <a href="{{ route('characters') }}" class="block px-4 py-2 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">Your Characters</a>
                                <a href="{{ route('character-builder') }}" class="block px-4 py-2 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">Character Builder</a>
                            </div>
                        </div>

                        <!-- Tools dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button data-testid="nav-tools" @click="open = !open" class="inline-flex items-center gap-2 text-white/80 hover:text-white font-medium transition-colors font-roboto">
                                Resources
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div x-show="open" x-cloak @click.away="open = false" x-transition class="absolute right-0 mt-2 w-56 bg-slate-800 border border-slate-600 rounded-lg shadow-xl z-[9999]">
                                <a href="{{ route('range-check') }}" class="block px-4 py-2 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">Visual Range Checker</a>
                                <a href="{{ route('actual-plays') }}" class="block px-4 py-2 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">Actual Plays</a>
                            </div>
                        </div>
                    </div>

                    <!-- Profile (authenticated) -->
                    <div class="relative" x-data="{ open: false }">
                        <button data-testid="nav-profile" x-on:click="open = !open" class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-gradient-to-br from-amber-400 to-orange-500 rounded-full flex items-center justify-center">
                                <span class="text-black font-bold text-sm">{{ substr(auth()->user()->username, 0, 1) }}</span>
                            </div>
                            <span class="text-white/80 font-medium font-roboto hidden lg:block">{{ auth()->user()->username }}</span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div x-show="open" x-cloak @click.away="open = false" x-transition class="absolute right-0 mt-2 w-56 bg-slate-800 border border-slate-600 rounded-lg shadow-xl z-[9999]">
                            <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">Dashboard</a>
                            <a href="{{ route('rooms.index') }}" class="block px-4 py-2 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">Rooms</a>
                            <a href="{{ route('discord') }}" target="_blank" rel="noopener noreferrer" class="block px-4 py-2 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">Discord</a>
                            <hr class="my-1 border-slate-600">
                            <form method="POST" action="{{ route('logout') }}" class="block">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-white/80 hover:bg-slate-700 hover:text-red-300 transition-colors font-roboto">Logout</button>
                            </form>
                        </div>
                    </div>

                    <!-- Mobile menu (authenticated) -->
                    <div class="md:hidden" x-data="{ mobileMenuOpen: false }">
                        <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-white p-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                        <div x-show="mobileMenuOpen" x-cloak @click.away="mobileMenuOpen = false" x-transition class="absolute right-4 top-16 w-72 bg-slate-800 border border-slate-600 rounded-lg shadow-xl z-[9999]">
                            <div class="py-2">
                                <a href="{{ route('campaigns.index') }}" class="block px-4 py-3 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">Campaigns</a>
                                <div class="px-4 py-2 text-xs uppercase tracking-wider text-slate-400">Characters</div>
                                <a href="{{ route('characters') }}" class="block px-6 py-2 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">Your Characters</a>
                                <a href="{{ route('character-builder') }}" class="block px-6 py-2 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">Character Builder</a>
                                <div class="px-4 pt-3 pb-2 text-xs uppercase tracking-wider text-slate-400">Resources</div>
                                <a href="{{ route('range-check') }}" class="block px-6 py-2 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">Visual Range Checker</a>
                                <a href="{{ route('actual-plays') }}" class="block px-6 py-2 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">Actual Plays</a>
                                <hr class="my-2 border-slate-600">
                                <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">Dashboard</a>
                                <a href="{{ route('rooms.index') }}" class="block px-4 py-2 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">Rooms</a>
                                <a href="{{ route('discord') }}" target="_blank" rel="noopener noreferrer" class="block px-4 py-2 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">Discord</a>
                                <form method="POST" action="{{ route('logout') }}" class="block">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-white/80 hover:bg-slate-700 hover:text-red-300 transition-colors font-roboto">Logout</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Desktop navigation (guest) -->
                    <div class="hidden md:flex items-center gap-6">
                        <!-- Characters dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button data-testid="nav-characters" @click="open = !open" class="inline-flex items-center gap-2 text-white/80 hover:text-white font-medium transition-colors font-roboto">
                                Characters
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div x-show="open" x-cloak @click.away="open = false" x-transition class="absolute right-0 mt-2 w-56 bg-slate-800 border border-slate-600 rounded-lg shadow-xl z-[9999]">
                                <a href="{{ route('characters') }}" class="block px-4 py-2 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">Your Characters</a>
                                <a href="{{ route('character-builder') }}" class="block px-4 py-2 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">Character Builder</a>
                            </div>
                        </div>

                        <!-- Tools dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button data-testid="nav-tools" @click="open = !open" class="inline-flex items-center gap-2 text-white/80 hover:text-white font-medium transition-colors font-roboto">
                                Tools
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div x-show="open" x-cloak @click.away="open = false" x-transition class="absolute right-0 mt-2 w-56 bg-slate-800 border border-slate-600 rounded-lg shadow-xl z-[9999]">
                                <a href="{{ route('range-check') }}" class="block px-4 py-2 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">Visual Range Checker</a>
                                <a href="{{ route('actual-plays') }}" class="block px-4 py-2 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">Actual Plays</a>
                            </div>
                        </div>

                        <!-- Fake profile icon (guest) -->
                        <div class="relative" x-data="{ open: false }">
                            <button data-testid="nav-profile" @click="open = !open" class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-slate-700 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-slate-300" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.7 0 4.8-2.2 4.8-4.8S14.7 2.4 12 2.4 7.2 4.6 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8V22h19.2v-2.8c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
                                </div>
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div x-show="open" x-cloak @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-slate-800 border border-slate-600 rounded-lg shadow-xl z-[9999]">
                                <a href="{{ route('login') }}" class="block px-4 py-2 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">Login</a>
                                <a href="{{ route('register') }}" class="block px-4 py-2 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">Register</a>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile menu (guest) -->
                    <div class="md:hidden" x-data="{ mobileMenuOpen: false }">
                        <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-white p-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                        <div x-show="mobileMenuOpen" x-cloak @click.away="mobileMenuOpen = false" x-transition class="absolute right-4 top-16 w-72 bg-slate-800 border border-slate-600 rounded-lg shadow-xl z-[9999]">
                            <div class="py-2">
                                <div class="px-4 py-2 text-xs uppercase tracking-wider text-slate-400">Characters</div>
                                <a href="{{ route('characters') }}" class="block px-6 py-2 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">Your Characters</a>
                                <a href="{{ route('character-builder') }}" class="block px-6 py-2 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">Character Builder</a>
                                <div class="px-4 pt-3 pb-2 text-xs uppercase tracking-wider text-slate-400">Tools</div>
                                <a href="{{ route('range-check') }}" class="block px-6 py-2 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">Visual Range Checker</a>
                                <a href="{{ route('actual-plays') }}" class="block px-6 py-2 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">Actual Plays</a>
                                <hr class="my-2 border-slate-600">
                                <a href="{{ route('login') }}" class="block px-4 py-2 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">Login</a>
                                <a href="{{ route('register') }}" class="block px-4 py-2 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">Register</a>
                            </div>
                        </div>
                    </div>
                @endauth
            </div>
            </div> <!-- Close Right Side container -->
        </div>
    </div>
</nav>
