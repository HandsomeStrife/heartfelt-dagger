<x-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950">
        <div class="px-4 sm:px-6 lg:px-8 pt-12 pb-16">
            <div class="max-w-6xl mx-auto">
                <!-- Header -->
                <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-8 mb-8">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h1 class="font-outfit text-3xl text-white tracking-wide mb-2">
                                {{ $room->name }}
                            </h1>
                            <p class="text-slate-300 text-lg mb-4">
                                {{ $room->description }}
                            </p>
                            
                            <!-- Room Details -->
                            <div class="flex items-center space-x-6 text-sm text-slate-400">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Created by {{ $room->creator->username ?? 'Unknown' }}
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Max Guests: {{ $room->guest_count }}
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Created {{ \Carbon\Carbon::parse($room->created_at)->diffForHumans() }}
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center space-x-4">
                            @if($user_is_creator)
                                <a href="{{ route('rooms.session', $room) }}" class="bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 shadow-lg hover:shadow-emerald-500/25">
                                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197 2.132A1 1 0 0110 13.82V10.18a1 1 0 011.555-.832l3.197 2.132a1 1 0 010 1.664z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Start Session
                                </a>
                            @elseif($user_is_participant)
                                <a href="{{ route('rooms.session', $room) }}" class="bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-400 hover:to-cyan-400 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 shadow-lg hover:shadow-blue-500/25">
                                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197 2.132A1 1 0 0110 13.82V10.18a1 1 0 011.555-.832l3.197 2.132a1 1 0 010 1.664z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Join Session
                                </a>
                                <form action="{{ route('rooms.leave', $room) }}" method="POST" onsubmit="return confirm('Are you sure you want to leave this room?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-500/20 hover:bg-red-500/30 text-red-400 border border-red-500/30 font-semibold py-3 px-6 rounded-xl transition-all duration-300">
                                        Leave Room
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('rooms.invite', $room->invite_code) }}" class="bg-gradient-to-r from-purple-500 to-violet-500 hover:from-purple-400 hover:to-violet-400 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 shadow-lg hover:shadow-purple-500/25">
                                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM12 14c-1.49 0-2.92.6-4 1.67V19h8v-3.33c-1.08-1.07-2.51-1.67-4-1.67z" />
                                    </svg>
                                    Join Room
                                </a>
                            @endif
                        </div>
                    </div>

                    @if($user_is_creator)
                        <!-- Invite Code Section -->
                        <div class="mt-6 pt-6 border-t border-slate-700">
                            <div class="flex items-center justify-between p-4 bg-slate-800/50 rounded-xl">
                                <div>
                                    <h3 class="text-white font-semibold mb-1">Invite Link</h3>
                                    <p class="text-slate-400 text-sm">Share this link for others to join your room</p>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <code class="bg-slate-700 text-emerald-400 px-3 py-2 rounded-lg font-mono text-sm">
                                        {{ $room->invite_code }}
                                    </code>
                                    <button onclick="copyInviteLink()" class="bg-emerald-500 hover:bg-emerald-400 text-white px-4 py-2 rounded-lg font-semibold transition-colors">
                                        Copy Link
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Participants -->
                <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-8">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500/20 to-cyan-500/20 rounded-xl flex items-center justify-center border border-blue-500/30 mr-3">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-outfit text-xl font-bold text-white">Participants</h2>
                            <p class="text-slate-400 text-sm">{{ $participants->count() }} of {{ $room->guest_count }} slots filled</p>
                        </div>
                    </div>

                    @if($participants->isEmpty())
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-slate-800 rounded-xl flex items-center justify-center border border-slate-600 mb-4 mx-auto">
                                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <h3 class="text-white font-semibold mb-2">No participants yet</h3>
                            <p class="text-slate-400 text-sm">Share the invite link to get people to join!</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($participants as $participant)
                                <div class="bg-slate-800/50 border border-slate-600/50 rounded-xl p-4">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-gradient-to-br from-violet-500/20 to-purple-500/20 rounded-xl flex items-center justify-center border border-violet-500/30 mr-3">
                                            <svg class="w-6 h-6 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-white">
                                                @if($participant->character)
                                                    {{ $participant->character->name }}
                                                @elseif($participant->character_name)
                                                    {{ $participant->character_name }}
                                                @else
                                                    {{ $participant->user->username }}
                                                @endif
                                            </h3>
                                            <div class="text-sm text-slate-400">
                                                @if($participant->character)
                                                    <span class="text-emerald-400">{{ $participant->character->class }}</span>
                                                    @if($participant->character->subclass)
                                                        ({{ $participant->character->subclass }})
                                                    @endif
                                                    <br>
                                                    <span>{{ $participant->character->ancestry }} {{ $participant->character->community }}</span>
                                                @elseif($participant->character_class)
                                                    <span class="text-emerald-400">{{ $participant->character_class }}</span> (Temporary)
                                                @else
                                                    <span class="text-slate-500">No character</span>
                                                @endif
                                                <div class="text-xs text-slate-500 mt-1">
                                                    Player: {{ $participant->user->username }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
    function copyInviteLink() {
        const inviteUrl = "{{ route('rooms.invite', $room->invite_code) }}";
        navigator.clipboard.writeText(inviteUrl).then(() => {
            // Create a temporary notification
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-emerald-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
            notification.textContent = 'Invite link copied to clipboard!';
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        });
    }
    </script>
</x-layout>
