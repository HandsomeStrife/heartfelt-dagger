<x-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950">
        <!-- Header -->
        <div class="bg-slate-900/80 backdrop-blur-sm border-b border-slate-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
                            <span class="text-slate-300 text-sm font-medium">VIEWING</span>
                        </div>
                        <div class="h-6 w-px bg-slate-600"></div>
                        <h1 class="text-xl font-outfit font-bold text-white">{{ $room->name }}</h1>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2 text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span class="text-sm">Read-only access</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Room Info -->
            <div class="bg-slate-900/60 backdrop-blur-xl rounded-2xl border border-slate-700 p-6 mb-8">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-slate-300 leading-relaxed">{{ $room->description }}</p>
                        <div class="flex items-center space-x-6 mt-4 text-sm text-slate-400">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span>{{ $participants->count() }}/{{ $room->guest_count }} Participants</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Started {{ $room->created_at ? \Carbon\Carbon::parse($room->created_at)->diffForHumans() : 'recently' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Video Grid -->
            <div class="grid gap-4 mb-8" style="grid-template-columns: repeat({{ min($room->guest_count, 3) }}, minmax(0, 1fr));">
                @foreach($participants as $index => $participant)
                    <div class="bg-slate-900/60 backdrop-blur-xl rounded-2xl border border-slate-700 aspect-video relative overflow-hidden">
                        <!-- Video placeholder -->
                        <div class="absolute inset-0 bg-gradient-to-br from-slate-800 to-slate-900 flex items-center justify-center">
                            <div class="text-center">
                                <div class="w-16 h-16 bg-slate-700 rounded-full flex items-center justify-center mb-4 mx-auto">
                                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div class="text-white font-semibold">
                                    {{ $participant->character ? $participant->character->name : ($participant->character_name ?? $participant->user->username) }}
                                </div>
                                @if($participant->character)
                                    <div class="text-slate-400 text-sm">
                                        {{ $participant->character->class }}
                                        @if($participant->character->subclass)
                                            ({{ $participant->character->subclass }})
                                        @endif
                                    </div>
                                @elseif($participant->character_class)
                                    <div class="text-slate-400 text-sm">{{ $participant->character_class }}</div>
                                @endif
                            </div>
                        </div>

                        <!-- Participant info overlay -->
                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    @if($participant->user_id === $room->creator_id)
                                        <span class="bg-amber-500 text-amber-100 text-xs px-2 py-1 rounded-full font-semibold">HOST</span>
                                    @endif
                                    <span class="text-white text-sm font-medium">
                                        {{ $participant->user ? $participant->user->username : 'Unknown User' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- Empty slots -->
                @for($i = $participants->count(); $i < $room->guest_count; $i++)
                    <div class="bg-slate-900/30 backdrop-blur-xl rounded-2xl border border-slate-600 border-dashed aspect-video relative overflow-hidden">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="text-center">
                                <div class="w-12 h-12 border-2 border-slate-600 border-dashed rounded-full flex items-center justify-center mb-3 mx-auto">
                                    <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </div>
                                <div class="text-slate-500 text-sm">Available Slot</div>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>

            <!-- Participants List -->
            @if($participants->isNotEmpty())
                <div class="bg-slate-900/60 backdrop-blur-xl rounded-2xl border border-slate-700 p-6">
                    <h3 class="text-lg font-outfit font-bold text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Active Participants
                    </h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($participants as $participant)
                            <div class="bg-slate-800/50 rounded-xl p-4 border border-slate-600">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="flex items-center space-x-2 mb-2">
                                            <h4 class="text-white font-semibold">
                                                {{ $participant->user ? $participant->user->username : 'Unknown User' }}
                                            </h4>
                                            @if($participant->user_id === $room->creator_id)
                                                <span class="bg-amber-500 text-amber-100 text-xs px-2 py-1 rounded-full font-semibold">HOST</span>
                                            @endif
                                        </div>
                                        
                                        @if($participant->character)
                                            <div class="text-slate-300 text-sm">
                                                <div class="font-medium">{{ $participant->character->name }}</div>
                                                <div class="text-slate-400">
                                                    {{ $participant->character->class }}
                                                    @if($participant->character->subclass)
                                                        ({{ $participant->character->subclass }})
                                                    @endif
                                                </div>
                                                <div class="text-slate-500 text-xs">
                                                    {{ $participant->character->ancestry }} {{ $participant->character->community }}
                                                </div>
                                            </div>
                                        @elseif($participant->character_name)
                                            <div class="text-slate-300 text-sm">
                                                <div class="font-medium">{{ $participant->character_name }}</div>
                                                @if($participant->character_class)
                                                    <div class="text-slate-400">{{ $participant->character_class }}</div>
                                                @endif
                                            </div>
                                        @else
                                            <div class="text-slate-400 text-sm">No character selected</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layout>
