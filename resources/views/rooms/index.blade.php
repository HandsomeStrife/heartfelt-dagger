<x-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950">
        <div class="px-4 sm:px-6 lg:px-8 pt-12 pb-16">
            <div class="max-w-6xl mx-auto">
                <!-- Header -->
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h1 class="font-outfit text-3xl text-white tracking-wide">
                            Rooms
                        </h1>
                        <p class="text-slate-300 text-lg">
                            Create and join video chat rooms
                        </p>
                    </div>
                    <a href="{{ route('rooms.create') }}" class="inline-flex items-center bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 shadow-lg hover:shadow-emerald-500/25">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Create Room
                    </a>
                </div>

                <!-- Development Notice -->
                <div class="mb-8 bg-amber-500/10 border border-amber-500/30 rounded-xl p-6 backdrop-blur-sm">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-amber-300 font-semibold text-lg mb-2">🚧 Development Notice</h3>
                            <p class="text-amber-200/90 text-sm leading-relaxed">
                                The Rooms feature is currently under active development. While functional, you may encounter bugs, data loss, or breaking changes. 
                                Please use with caution and report any issues you discover.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- My Rooms -->
                    <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 bg-gradient-to-br from-emerald-500/20 to-teal-500/20 rounded-xl flex items-center justify-center border border-emerald-500/30 mr-3">
                                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="font-outfit text-xl font-bold text-white">My Rooms</h2>
                                <p class="text-slate-400 text-sm">Rooms you've created</p>
                            </div>
                        </div>

                        @if($created_rooms->isEmpty())
                            <div class="text-center py-8">
                                <div class="w-16 h-16 bg-slate-800 rounded-xl flex items-center justify-center border border-slate-600 mb-4 mx-auto">
                                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-white font-semibold mb-2">No rooms yet</h3>
                                <p class="text-slate-400 text-sm mb-4">Create your first room to get started.</p>
                                <a href="{{ route('rooms.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-500 hover:bg-emerald-400 text-white text-sm font-semibold rounded-lg transition-colors">
                                    Create Room
                                </a>
                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach($created_rooms as $room)
                                    <div class="bg-slate-800/50 border border-slate-600/50 rounded-xl p-4 hover:bg-slate-800/70 transition-colors">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <h3 class="font-semibold text-white mb-1">{{ $room->name }}</h3>
                                                <p class="text-slate-400 text-sm mb-2 line-clamp-2">{{ $room->description }}</p>
                                                <div class="flex items-center space-x-4 text-sm text-slate-500">
                                                    <span class="flex items-center">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                        </svg>
                                                        {{ $room->getActiveParticipantCount() }}/{{ $room->getTotalCapacity() }}
                                                    </span>
                                                    <span>Created {{ \Carbon\Carbon::parse($room->created_at)->diffForHumans() }}</span>
                                                </div>
                                            </div>
                                            <a href="{{ route('rooms.show', $room) }}" class="ml-4 text-emerald-400 hover:text-emerald-300 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Joined Rooms -->
                    <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500/20 to-cyan-500/20 rounded-xl flex items-center justify-center border border-blue-500/30 mr-3">
                                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="font-outfit text-xl font-bold text-white">Joined Rooms</h2>
                                <p class="text-slate-400 text-sm">Rooms you're participating in</p>
                            </div>
                        </div>

                        @if($joined_rooms->isEmpty())
                            <div class="text-center py-8">
                                <div class="w-16 h-16 bg-slate-800 rounded-xl flex items-center justify-center border border-slate-600 mb-4 mx-auto">
                                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-white font-semibold mb-2">No joined rooms</h3>
                                <p class="text-slate-400 text-sm">Get invited to a room to start participating!</p>
                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach($joined_rooms as $room)
                                    <div class="bg-slate-800/50 border border-slate-600/50 rounded-xl p-4 hover:bg-slate-800/70 transition-colors">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <h3 class="font-semibold text-white mb-1">{{ $room->name }}</h3>
                                                <p class="text-slate-400 text-sm mb-2 line-clamp-2">{{ $room->description }}</p>
                                                <div class="flex items-center space-x-4 text-sm text-slate-500">
                                                    <span class="flex items-center">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                        </svg>
                                                        {{ $room->getActiveParticipantCount() }}/{{ $room->getTotalCapacity() }}
                                                    </span>
                                                    <span>Creator: {{ $room->creator->username ?? 'Unknown' }}</span>
                                                </div>
                                            </div>
                                            <a href="{{ route('rooms.session', $room) }}" class="ml-4 text-blue-400 hover:text-blue-300 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293H15M9 10V9a2 2 0 012-2h2a2 2 0 012 2v1M9 10v5a2 2 0 002 2h2a2 2 0 002-2v-5" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
