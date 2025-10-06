<x-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950">
        <!-- Compact Navigation -->
        <x-sub-navigation>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a 
                        href="{{ route('rooms.index') }}"
                        class="p-1.5 text-slate-400 hover:text-white hover:bg-slate-700/50 rounded-md transition-colors"
                        title="Back to rooms"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="font-outfit text-lg font-bold text-white tracking-wide">
                                {{ $room->name }}
                            </h1>
                            @if($room->status === \Domain\Room\Enums\RoomStatus::Archived)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-500/20 text-amber-400 border border-amber-500/30">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                    </svg>
                                    Archived
                                </span>
                            @endif
                        </div>
                        <p class="text-slate-400 text-xs">
                            {{ \Str::limit($room->description, 50) }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @if($room->status === \Domain\Room\Enums\RoomStatus::Archived)
                        <span class="text-slate-400 text-sm italic">This room has been archived</span>
                    @else
                        @if($user_is_creator)
                            <a href="{{ route('rooms.session', $room) }}" 
                               class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white text-sm font-medium rounded-md transition-all duration-200">
                                <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197 2.132A1 1 0 0110 13.82V10.18a1 1 0 011.555-.832l3.197 2.132a1 1 0 010 1.664z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Join Room
                            </a>
                            {{-- Only show Share button for non-campaign rooms --}}
                            @if(!$room->campaign_id)
                                <button onclick="showModal('roomInviteModal')" 
                                        class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600 text-white text-sm font-medium rounded-md transition-all duration-200">
                                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z" />
                                    </svg>
                                    Share
                                </button>
                            @endif
                        @elseif($user_is_participant)
                            <a href="{{ route('rooms.session', $room) }}" 
                               class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600 text-white text-sm font-medium rounded-md transition-all duration-200">
                                <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197 2.132A1 1 0 0110 13.82V10.18a1 1 0 011.555-.832l3.197 2.132a1 1 0 010 1.664z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Join Session
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        </x-sub-navigation>

        <div class="px-4 sm:px-6 lg:px-8 pt-8 pb-12">
            <div class="max-w-6xl mx-auto space-y-6">
                <!-- Room Info Card -->
                <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div class="flex items-center text-slate-400">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Created by {{ $room->creator->username ?? 'Unknown' }}
                        </div>
                        <div class="flex items-center text-slate-400">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Max Participants: {{ $room->guest_count }}
                        </div>
                        <div class="flex items-center text-slate-400">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Created {{ \Carbon\Carbon::parse($room->created_at)->diffForHumans() }}
                        </div>
                    </div>
                    
                    @if($room->description)
                        <div class="mt-4 pt-4 border-t border-slate-700">
                            <p class="text-slate-300">{{ $room->description }}</p>
                        </div>
                    @endif

                    @if($user_is_creator)
                        <div class="mt-4 pt-4 border-t border-slate-700">
                            @if($room->status === \Domain\Room\Enums\RoomStatus::Archived)
                                <!-- Archived Room Info -->
                                <div class="bg-amber-500/10 border border-amber-500/30 rounded-lg p-4 mb-4">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-amber-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <div>
                                            <h4 class="text-amber-300 font-medium text-sm mb-1">This room is archived</h4>
                                            <p class="text-slate-300 text-sm">Archived rooms cannot be joined, but all recordings, transcripts, and session data remain accessible. You can still delete the room permanently if needed.</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="flex gap-2">
                                @if($room->status !== \Domain\Room\Enums\RoomStatus::Archived)
                                    <!-- Archive Room Button -->
                                    <form action="{{ route('rooms.archive', $room) }}" method="POST" onsubmit="return confirm('Archive this room? Archived rooms cannot be joined but all recordings and transcripts will be preserved.')" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button data-testid="archive-room-button" type="submit" class="inline-flex items-center px-3 py-1.5 bg-amber-500/20 hover:bg-amber-500/30 text-amber-400 border border-amber-500/30 text-sm font-medium rounded-md transition-all duration-200">
                                            <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                            </svg>
                                            Archive Room
                                        </button>
                                    </form>
                                @endif
                                
                                <!-- Delete Room Button (always available to creator) -->
                                <form action="{{ route('rooms.destroy', $room) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this room? This action cannot be undone.')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button data-testid="delete-room-button" type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-500/20 hover:bg-red-500/30 text-red-400 border border-red-500/30 text-sm font-medium rounded-md transition-all duration-200">
                                        <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Delete Room
                                    </button>
                                </form>
                            </div>
                        </div>
                    @elseif($user_is_participant)
                        <div class="mt-4 pt-4 border-t border-slate-700">
                            <form action="{{ route('rooms.leave', $room) }}" method="POST" onsubmit="return confirm('Are you sure you want to leave this room?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-500/20 hover:bg-red-500/30 text-red-400 border border-red-500/30 text-sm font-medium rounded-md transition-all duration-200">
                                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Leave Room
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                @if(!$user_is_creator && !$user_is_participant)
                    <!-- Join Room Card -->
                    <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 text-center">
                        <h3 class="text-lg font-medium text-slate-300 mb-2">Want to join this room?</h3>
                        <p class="text-slate-400 text-sm mb-4">Connect and participate in this gaming session</p>
                        <a href="{{ route('rooms.invite', $room->invite_code) }}" 
                           class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-500 to-violet-500 hover:from-purple-600 hover:to-violet-600 text-white font-medium rounded-lg transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM12 14c-1.49 0-2.92.6-4 1.67V19h8v-3.33c-1.08-1.07-2.51-1.67-4-1.67z" />
                            </svg>
                            Join Room
                        </a>
                    </div>
                @endif

                @if($user_is_creator)
                    <!-- Recording Settings -->
                    <div class="mb-8">
                        <livewire:room-recording-settings :room="$room" />
                    </div>
                @endif

            </div>
        </div>
    </div>

    @if($user_is_creator && !$room->campaign_id)
        <!-- Room Invite Modal with Viewer URL -->
        <div id="roomInviteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
            <div class="bg-slate-900 border border-slate-700 rounded-2xl p-8 max-w-lg w-full mx-4">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-outfit font-bold text-white">Share Room Access</h3>
                    <button onclick="hideModal('roomInviteModal')" class="text-slate-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="space-y-6">
                    <!-- Participant Link -->
                    <div>
                        <label class="block text-slate-300 text-sm font-semibold mb-2">Participant Invite</label>
                        <div class="flex items-center space-x-3">
                            <input type="text" readonly value="{{ route('rooms.invite', $room->invite_code) }}" 
                                   class="flex-1 bg-slate-800 text-slate-300 px-4 py-3 rounded-lg border border-slate-600 text-sm">
                            <button onclick="copyText('{{ route('rooms.invite', $room->invite_code) }}', this)" class="bg-emerald-500 hover:bg-emerald-400 text-white px-4 py-3 rounded-lg font-semibold transition-colors">
                                Copy
                            </button>
                        </div>
                        <p class="text-slate-400 text-xs mt-2">
                            @if($room->campaign_id)
                                Campaign members can join the session with this link
                            @else
                                Anyone with this link can join the session
                            @endif
                        </p>
                    </div>

                    <!-- Viewer Link -->
                    <div>
                        <label class="block text-slate-300 text-sm font-semibold mb-2">Viewer Link</label>
                        <div class="flex items-center space-x-3">
                            <input type="text" readonly value="{{ route('rooms.viewer', $room->viewer_code) }}" 
                                   class="flex-1 bg-slate-800 text-slate-300 px-4 py-3 rounded-lg border border-slate-600 text-sm">
                            <button onclick="copyText('{{ route('rooms.viewer', $room->viewer_code) }}', this)" class="bg-indigo-500 hover:bg-indigo-400 text-white px-4 py-3 rounded-lg font-semibold transition-colors">
                                Copy
                            </button>
                        </div>
                        <p class="text-slate-400 text-xs mt-2">Read-only access - viewers can watch but not participate</p>
                    </div>
                </div>
            </div>
        </div>

        <script>
            window.showModal = window.showModal || function(modalId) {
                const modal = document.getElementById(modalId);
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            window.hideModal = window.hideModal || function(modalId) {
                const modal = document.getElementById(modalId);
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            window.copyText = window.copyText || function(text, button) {
                navigator.clipboard.writeText(text).then(function() {
                    const originalText = button.textContent;
                    button.textContent = 'Copied!';
                    button.classList.remove('bg-emerald-500', 'hover:bg-emerald-400', 'bg-indigo-500', 'hover:bg-indigo-400');
                    button.classList.add('bg-green-500');
                    
                    setTimeout(() => {
                        button.textContent = originalText;
                        button.classList.remove('bg-green-500');
                        if (originalText === 'Copy' && button.parentElement.parentElement.querySelector('label').textContent.includes('Participant')) {
                            button.classList.add('bg-emerald-500', 'hover:bg-emerald-400');
                        } else {
                            button.classList.add('bg-indigo-500', 'hover:bg-indigo-400');
                        }
                    }, 2000);
                }, function(err) {
                    console.error('Could not copy text: ', err);
                    button.textContent = 'Error';
                    button.classList.add('bg-red-500');
                    setTimeout(() => {
                        button.textContent = 'Copy';
                        button.classList.remove('bg-red-500');
                    }, 2000);
                });
            }

            // Setup modal event listeners
            document.addEventListener('DOMContentLoaded', function() {
                const modal = document.getElementById('roomInviteModal');
                if (modal) {
                    modal.addEventListener('click', function(e) {
                        if (e.target === this) {
                            hideModal('roomInviteModal');
                        }
                    });
                }

                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        hideModal('roomInviteModal');
                    }
                });
            });
        </script>
    @endif
</x-layout>
