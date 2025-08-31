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
            <div class="flex items-center gap-8">
                @auth
                    <!-- Mobile menu button (Available to all users) -->
                    <div class="md:hidden" x-data="{ mobileMenuOpen: false }">
                        <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-white p-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                        
                        <!-- Mobile Navigation Menu -->
                        <div x-show="mobileMenuOpen" 
                             x-cloak
                             @click.away="mobileMenuOpen = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-4 top-16 w-64 bg-slate-800 border border-slate-600 rounded-lg shadow-xl z-[9999]">
                            <div class="py-2">
                                <!-- Character Links -->
                                <a href="{{ route('character-builder') }}" class="block px-4 py-3 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto border-b border-slate-600">
                                    Create Character
                                </a>
                                <a href="{{ route('characters') }}" class="block px-4 py-3 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto border-b border-slate-600">
                                    My Characters
                                </a>
                                <a href="{{ route('discord') }}" target="_blank" rel="noopener noreferrer" class="flex items-center px-4 py-3 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto border-b border-slate-600">
                                    <x-icons.discord class="w-4 h-4 mr-3" />
                                    Discord
                                </a>
                                
                                <!-- Auth-only links -->
                                <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto border-b border-slate-600">
                                    <svg class="w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                                    </svg>
                                    Dashboard
                                </a>
                                <a href="{{ route('campaigns.index') }}" class="flex items-center px-4 py-3 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Campaigns
                                </a>
                                <a href="{{ route('rooms.index') }}" class="flex items-center px-4 py-3 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto border-b border-slate-600">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    Rooms
                                </a>
                                <form method="POST" action="{{ route('logout') }}" class="block">
                                    @csrf
                                    <button type="submit" class="flex items-center w-full px-4 py-3 text-white/80 hover:bg-slate-700 hover:text-red-300 transition-colors font-roboto">
                                        <svg class="w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M10 2L8 4v16l2 2h8l2-2V4l-2-2h-8zM9 3h8v18H9V3zm5 10l3-3-3-3v2H8v2h7v2z"/>
                                        </svg>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- User Menu (Desktop) -->
                    <div class="relative" x-data="{ open: false }">
                        <!-- Profile Button -->
                        <button 
                            x-on:click="open = !open" 
                            class="flex items-center space-x-2 bg-slate-700 hover:bg-slate-600 rounded-lg px-3 py-2 transition-colors"
                        >
                            <!-- Profile Avatar -->
                            <div class="w-8 h-8 bg-gradient-to-br from-amber-400 to-orange-500 rounded-full flex items-center justify-center">
                                <span class="text-black font-bold text-sm">{{ substr(auth()->user()->username, 0, 1) }}</span>
                            </div>
                            <span class="text-white/80 font-medium font-roboto hidden lg:block">{{ auth()->user()->username }}</span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div 
                            x-show="open" 
                            x-cloak
                            @click.away="open = false"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-48 bg-slate-800 border border-slate-600 rounded-lg shadow-xl z-[9999]"
                        >
                            <div class="py-1">
                                <a href="{{ route('character-builder') }}" class="flex items-center px-4 py-2 text-sm text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">
                                    <svg class="w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                    Create Character
                                </a>
                                <a href="{{ route('characters') }}" class="flex items-center px-4 py-2 text-sm text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">
                                    <x-icons.characters class="w-4 h-4 mr-3" />
                                    My Characters
                                </a>
                                <a href="{{ route('discord') }}" target="_blank" rel="noopener noreferrer" class="flex items-center px-4 py-2 text-sm text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">
                                    <x-icons.discord class="w-4 h-4 mr-3" />
                                    Discord
                                </a>
                                <hr class="my-1 border-slate-600">
                                <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-2 text-sm text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">
                                    <svg class="w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                                    </svg>
                                    Dashboard
                                </a>
                                <a href="{{ route('campaigns.index') }}" class="flex items-center px-4 py-2 text-sm text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Campaigns
                                </a>
                                <a href="{{ route('rooms.index') }}" class="flex items-center px-4 py-2 text-sm text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    Rooms
                                </a>
                                <hr class="my-1 border-slate-600">
                                <form method="POST" action="{{ route('logout') }}" class="block">
                                    @csrf
                                    <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-white/80 hover:bg-slate-700 hover:text-red-300 transition-colors font-roboto">
                                        <svg class="w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M10 2L8 4v16l2 2h8l2-2V4l-2-2h-8zM9 3h8v18H9V3zm5 10l3-3-3-3v2H8v2h7v2z"/>
                                        </svg>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Guest Navigation (Desktop) -->
                    <div class="hidden md:flex items-center gap-6">
                        <a href="{{ route('character-builder') }}" class="text-white/80 hover:text-white font-medium transition-colors font-roboto">
                            Create
                        </a>
                        <a href="{{ route('characters') }}" class="text-white/80 hover:text-white font-medium transition-colors font-roboto">
                            Characters
                        </a>
                        <a href="{{ route('login') }}" class="text-white/80 hover:text-white font-medium transition-colors font-roboto">
                            Login
                        </a>
                        <a href="{{ route('register') }}" class="text-white/80 hover:text-white font-medium transition-colors font-roboto">
                            Register
                        </a>
                        <span class="text-white/80">|</span>
                        <a href="{{ route('discord') }}" target="_blank" rel="noopener noreferrer" class="text-white/80 hover:text-white font-medium transition-colors font-roboto flex items-center gap-2" x-tooltip="Join our Discord community">
                            <x-icons.discord class="w-5 h-5" />
                        </a>
                    </div>

                    <!-- Mobile menu button (Guest) -->
                    <div class="md:hidden" x-data="{ mobileMenuOpen: false }">
                        <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-white p-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                        
                        <!-- Mobile Navigation Menu (Guest) -->
                        <div x-show="mobileMenuOpen" 
                             x-cloak
                             @click.away="mobileMenuOpen = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-4 top-16 w-64 bg-slate-800 border border-slate-600 rounded-lg shadow-xl z-[9999]">
                            <div class="py-2">
                                <a href="{{ route('character-builder') }}" class="block px-4 py-3 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto border-b border-slate-600">
                                    Create
                                </a>
                                <a href="{{ route('characters') }}" class="block px-4 py-3 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto border-b border-slate-600">
                                    Characters
                                </a>
                                <a href="{{ route('discord') }}" target="_blank" rel="noopener noreferrer" class="flex items-center px-4 py-3 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto border-b border-slate-600">
                                    <x-icons.discord class="w-4 h-4 mr-3" />
                                    Discord
                                </a>
                                <a href="{{ route('login') }}" class="block px-4 py-3 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto border-b border-slate-600">
                                    Login
                                </a>
                                <a href="{{ route('register') }}" class="block px-4 py-3 text-white/80 hover:bg-slate-700 hover:text-white transition-colors font-roboto">
                                    Register
                                </a>
                            </div>
                        </div>
                    </div>
                @endauth
            </div>
            </div> <!-- Close Right Side container -->
        </div>
    </div>
</nav>
