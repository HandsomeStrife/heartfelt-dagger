<x-layout>
    <div class="min-h-screen">
        <div class="px-4 sm:px-6 lg:px-8 pt-12 pb-16">
            <div class="max-w-6xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <a href="{{ route('campaigns.index') }}" class="text-slate-400 hover:text-slate-300 mr-4">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </a>
                                <h1 class="font-outfit text-3xl text-white tracking-wide">
                                    {{ $campaign->name }}
                                </h1>
                                <span class="ml-4 px-3 py-1 bg-{{ $campaign->status->color() }}-500/20 text-{{ $campaign->status->color() }}-400 rounded-full text-sm">
                                    {{ $campaign->status->label() }}
                                </span>
                            </div>
                            <p class="text-slate-300 text-lg">
                                {{ $campaign->description }}
                            </p>
                            <div class="flex items-center gap-4 mt-4 text-sm text-slate-400">
                                <span>Created by {{ $campaign->creator?->username ?? 'Unknown' }}</span>
                                <span>•</span>
                                <span>{{ $campaign->member_count ?? 0 }} members</span>
                                <span>•</span>
                                <span>Created {{ \Carbon\Carbon::parse($campaign->created_at)->diffForHumans() }}</span>
                            </div>
                        </div>
                        
                        @if($user_is_creator)
                            <div class="flex items-center gap-3">
                                <!-- Share Invite Button -->
                                <button 
                                    onclick="showModal('campaignInviteModal')"
                                    class="inline-flex items-center bg-emerald-500 hover:bg-emerald-400 text-white font-semibold py-2 px-4 rounded-xl transition-colors"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z" />
                                    </svg>
                                    Share Invite
                                </button>
                            </div>
                        @elseif($user_is_member)
                            <form action="{{ route('campaigns.leave', $campaign->campaign_code) }}" method="POST" onsubmit="return confirm('Are you sure you want to leave this campaign?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center bg-red-500 hover:bg-red-400 text-white font-semibold py-2 px-4 rounded-xl transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Leave Campaign
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Campaign Frame Visibility Manager (GM Only) -->
                @if($user_is_creator)
                    <livewire:campaign-frame.campaign-frame-visibility-manager :campaign="$campaign_model" />
                @endif

                <!-- Campaign Frame Content Display -->
                <livewire:campaign-frame.campaign-frame-display :campaign="$campaign_model" />

                <!-- Campaign Pages Section -->
                <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 mb-8">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-gradient-to-br from-amber-500/20 to-orange-500/20 rounded-xl flex items-center justify-center border border-amber-500/30 mr-3">
                                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="font-outfit text-xl font-bold text-white">Campaign Pages</h2>
                                <p class="text-slate-400 text-sm">Manage your campaign lore, NPCs, and world-building content</p>
                            </div>
                        </div>
                        @if($user_is_creator || $user_is_member)
                            <a href="{{ route('campaigns.pages', $campaign->campaign_code) }}" 
                               class="inline-flex items-center bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-white font-semibold py-2 px-4 rounded-xl transition-all duration-300 shadow-lg hover:shadow-amber-500/25 text-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Manage Pages
                            </a>
                        @endif
                    </div>

                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-slate-800 rounded-xl flex items-center justify-center border border-slate-600 mb-4 mx-auto">
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-white font-semibold mb-2">Organize Your Campaign</h3>
                        <p class="text-slate-400 text-sm mb-4">Create hierarchical pages to organize lore, NPCs, locations, and plot information. Use rich text editing and category tags for easy organization.</p>
                        @if($user_is_creator || $user_is_member)
                            <a href="{{ route('campaigns.pages', $campaign->campaign_code) }}" 
                               class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-400 text-white text-sm font-semibold rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Start Building
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Campaign Rooms -->
                <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 mb-8">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500/20 to-purple-500/20 rounded-xl flex items-center justify-center border border-indigo-500/30 mr-3">
                                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="font-outfit text-xl font-bold text-white">Campaign Rooms</h2>
                                <p class="text-slate-400 text-sm">Video rooms for this campaign</p>
                            </div>
                        </div>
                        @if($user_is_creator || $user_is_member)
                            <a href="{{ route('campaigns.rooms.create', $campaign->campaign_code) }}" 
                               class="inline-flex items-center bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-400 hover:to-purple-400 text-white font-semibold py-2 px-4 rounded-xl transition-all duration-300 shadow-lg hover:shadow-indigo-500/25 text-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Create Room
                            </a>
                        @endif
                    </div>

                    @if($campaign_rooms->isEmpty())
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-slate-800 rounded-xl flex items-center justify-center border border-slate-600 mb-4 mx-auto">
                                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="text-white font-semibold mb-2">No rooms yet</h3>
                            <p class="text-slate-400 text-sm mb-4">Create your first campaign room to start video sessions.</p>
                            @if($user_is_creator || $user_is_member)
                                <a href="{{ route('campaigns.rooms.create', $campaign->campaign_code) }}" 
                                   class="inline-flex items-center px-4 py-2 bg-indigo-500 hover:bg-indigo-400 text-white text-sm font-semibold rounded-lg transition-colors">
                                    Create Room
                                </a>
                            @endif
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($campaign_rooms as $room)
                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-4 hover:bg-slate-800/70 transition-colors">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex-1">
                                            <h3 class="font-outfit font-semibold text-white">{{ $room->name }}</h3>
                                            <p class="text-slate-400 text-sm mb-2 line-clamp-2">{{ $room->description }}</p>
                                            <div class="flex items-center space-x-4 text-sm text-slate-500">
                                                <span class="flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    </svg>
                                                    {{ $room->active_participant_count ?? 0 }}/{{ ($room->guest_count ?? 0) + 1 }}
                                                </span>
                                                <span>By {{ $room->creator?->username ?? 'Unknown' }}</span>
                                            </div>
                                            <p class="text-slate-500 text-xs mt-1">Created {{ \Carbon\Carbon::parse($room->created_at)->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                No Password
                                            </span>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-500/20 text-blue-400 border border-blue-500/30">
                                                Campaign
                                            </span>
                                        </div>
                                        <a href="{{ route('rooms.show', $room->id) }}" 
                                           class="text-indigo-400 hover:text-indigo-300 transition-colors">
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

                <!-- Campaign Members -->
                <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-gradient-to-br from-emerald-500/20 to-teal-500/20 rounded-xl flex items-center justify-center border border-emerald-500/30 mr-3">
                                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="font-outfit text-xl font-bold text-white">Campaign Members</h2>
                                <p class="text-slate-400 text-sm">Players in this campaign</p>
                            </div>
                        </div>
                    </div>

                    @if($members->isEmpty())
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-slate-800 rounded-xl flex items-center justify-center border border-slate-600 mb-4 mx-auto">
                                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <h3 class="text-white font-semibold mb-2">No members yet</h3>
                            <p class="text-slate-400 text-sm">Share the invite link to get players to join!</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($members as $member)
                                <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-4">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex-1">
                                            <h3 class="font-outfit font-semibold text-white">{{ $member->user?->username ?? 'Unknown User' }}</h3>
                                            <p class="text-slate-400 text-sm">Joined {{ \Carbon\Carbon::parse($member->joined_at)->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                    
                                    @if($member->character)
                                        <div class="bg-slate-700/50 rounded-lg p-3 border border-slate-600/30">
                                            <div class="flex items-center">
                                                @if($member->character && method_exists($member->character, 'getBanner'))
                                                    <img src="{{ $member->character->getBanner() }}" alt="{{ $member->character->class }}" class="w-8 h-8 rounded mr-3">
                                                @endif
                                                <div class="flex-1">
                                                    <h4 class="font-outfit font-semibold text-amber-300 text-sm">{{ $member->character->name }}</h4>
                                                    <div class="text-xs text-slate-400 space-y-1">
                                                        <div>{{ $member->character->class }} / {{ $member->character->subclass }}</div>
                                                        <div>{{ $member->character->ancestry }} • {{ $member->character->community }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="bg-slate-700/50 rounded-lg p-3 border border-slate-600/30">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-slate-600 rounded mr-3 flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <h4 class="font-outfit font-semibold text-slate-300 text-sm">Empty Character</h4>
                                                    <p class="text-xs text-slate-400">No character selected</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($user_is_creator)
        <x-invite-modal 
            modal-id="campaignInviteModal"
            title="Share Campaign Invite"
            :invite-code="$campaign->invite_code"
            :invite-url="route('campaigns.join', $campaign->invite_code)"
            code-label="Campaign Code"
            link-label="Campaign Link"
        />
    @endif
</x-layout>
