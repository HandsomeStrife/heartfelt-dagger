<x-layout>
    <div class="min-h-screen">
        <!-- Header Section with Breadcrumb -->
        <div class="mb-4">
            <x-sub-navigation>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <a href="{{ route('campaigns.index') }}" class="text-slate-400 hover:text-slate-300 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                        <div class="flex items-center gap-4">
                            <h1 class="font-outfit text-lg font-semibold text-white">
                                {{ $campaign->name }}
                            </h1>
                            <span class="px-2 py-1 bg-{{ $campaign->status->color() }}-500/20 text-{{ $campaign->status->color() }}-400 rounded-lg text-xs font-medium">
                                {{ $campaign->status->label() }}
                            </span>
                        </div>
                        
                        <!-- Campaign Info in Banner -->
                        <div class="hidden md:flex items-center gap-6 text-sm text-slate-400">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span>{{ $campaign->creator?->username ?? 'Unknown' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span>{{ $campaign->member_count ?? 0 }} members</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>{{ \Carbon\Carbon::parse($campaign->created_at)->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                    
                    @if($user_is_creator)
                        <button 
                            onclick="showModal('campaignInviteModal')"
                            class="inline-flex items-center bg-emerald-500 hover:bg-emerald-400 text-white font-semibold py-2 px-4 rounded-xl transition-colors text-sm"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z" />
                            </svg>
                            Share Invite
                        </button>
                    @elseif($user_is_member)
                        <form action="{{ route('campaigns.leave', $campaign->campaign_code) }}" method="POST" onsubmit="return confirm('Are you sure you want to leave this campaign?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center bg-red-600 hover:bg-red-500 text-white font-semibold py-2 px-4 rounded-xl transition-colors text-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Leave Campaign
                            </button>
                        </form>
                    @endif
                </div>
            </x-sub-navigation>
        </div>

        <div class="container mx-auto px-3 sm:px-6 pb-8">
            <!-- Campaign Description -->
            @if($campaign->description)
                <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 mb-6">
                    <p class="text-slate-300 text-base">{{ $campaign->description }}</p>
                </div>
            @endif

            <!-- Tabbed Interface -->
            <div x-data="{ activeTab: 'members' }" class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl">
                <!-- Tab Navigation -->
                <div class="border-b border-slate-700/50">
                    <nav class="flex">
                        <button @click="activeTab = 'members'" 
                                :class="activeTab === 'members' ? 'border-emerald-500 text-emerald-400' : 'border-transparent text-slate-400 hover:text-slate-300'"
                                class="flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Members
                        </button>
                        <button @click="activeTab = 'pages'" 
                                :class="activeTab === 'pages' ? 'border-amber-500 text-amber-400' : 'border-transparent text-slate-400 hover:text-slate-300'"
                                class="flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Pages
                        </button>
                        <button @click="activeTab = 'handouts'" 
                                :class="activeTab === 'handouts' ? 'border-blue-500 text-blue-400' : 'border-transparent text-slate-400 hover:text-slate-300'"
                                class="flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            Handouts
                        </button>
                        <button @click="activeTab = 'rooms'" 
                                :class="activeTab === 'rooms' ? 'border-purple-500 text-purple-400' : 'border-transparent text-slate-400 hover:text-slate-300'"
                                class="flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            Rooms
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-6 relative min-h-[400px]">
                    <!-- Members Tab (Primary) -->
                    <div x-show="activeTab === 'members'" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform translate-y-2"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 transform translate-y-0"
                         x-transition:leave-end="opacity-0 transform translate-y-2"
                         class="absolute inset-0 p-6">
                        @if($members && $members->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                                @foreach($members as $member)
                                    <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl overflow-hidden hover:border-slate-600/70 transition-all duration-300 group">
                                        
                                        <!-- Character Portrait -->
                                        <div class="relative h-32 bg-gradient-to-br from-slate-700 to-slate-800">
                                            @if($member->character && !empty($member->character->profile_image_path))
                                                <img src="{{ $member->character->getProfileImage() }}" 
                                                     alt="{{ $member->character->name ?? 'Character' }} portrait"
                                                     class="w-full h-full object-cover">
                                            @else
                                                <div class="absolute inset-0 flex items-center justify-center">
                                                    <svg class="w-12 h-12 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Character Class Banner -->
                                        @if($member->character && $member->character->class)
                                            <x-class-banner :class-name="$member->character->class" size="sm" class="absolute top-0 left-0" />
                                        @endif

                                        <!-- Character/User Info -->
                                        <div class="p-3">
                                            <!-- User Name with GM Badge -->
                                            <div class="flex items-center gap-2 mb-2">
                                                <h3 class="text-white font-bold font-outfit text-sm truncate">
                                                    {{ $member->user->username }}
                                                </h3>
                                                @if($member->user_id === $campaign->creator_id)
                                                    <span class="px-2 py-1 bg-amber-500/20 text-amber-400 rounded text-xs font-medium">GM</span>
                                                @endif
                                            </div>

                                            <!-- Character Details -->
                                            <div class="text-slate-300 text-xs mb-3 space-y-1">
                                                <div class="flex justify-between">
                                                    <span class="text-slate-400">Character:</span> 
                                                    <span>{{ $member->character ? $member->character->name : 'None' }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-slate-400">Class:</span> 
                                                    <span>{{ $member->character && $member->character->class ? ucfirst($member->character->class) : 'None' }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-slate-400">Subclass:</span> 
                                                    <span>{{ $member->character && $member->character->subclass ? ucfirst($member->character->subclass) : 'None' }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-slate-400">Ancestry:</span> 
                                                    <span>{{ $member->character && $member->character->ancestry ? ucfirst($member->character->ancestry) : 'None' }}</span>
                                                </div>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="flex gap-1 mt-3">
                                                @if($member->character)
                                                    <a href="{{ route('character.show', $member->character->public_key) }}"
                                                       class="flex-1 bg-slate-600 hover:bg-slate-500 text-white px-2 py-1.5 rounded text-xs font-medium transition-all duration-200 text-center">
                                                        View
                                                    </a>
                                                @endif
                                                @if($member->user_id === auth()->id())
                                                    <button onclick="showCharacterSelectionModal({{ $member->id }})"
                                                            class="flex-1 bg-amber-600/20 hover:bg-amber-600/30 text-amber-300 px-2 py-1.5 rounded text-xs font-medium transition-all duration-200">
                                                        {{ $member->character ? 'Change' : 'Select' }}
                                                    </button>
                                                @endif
                                            </div>

                                            <!-- Join Date -->
                                            <div class="mt-2 text-slate-500 text-xs">
                                                Joined {{ \Carbon\Carbon::parse($member->joined_at)->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="w-16 h-16 bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <h3 class="font-outfit text-lg font-semibold text-white mb-2">No Members Yet</h3>
                                <p class="text-slate-400 text-sm">
                                    Share the campaign invite to get players to join.
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Pages Tab -->
                    <div x-show="activeTab === 'pages'" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform translate-y-2"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 transform translate-y-0"
                         x-transition:leave-end="opacity-0 transform translate-y-2"
                         x-cloak
                         class="absolute inset-0 p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="font-outfit text-lg font-semibold text-white">Campaign Pages</h2>
                                <p class="text-slate-400 text-sm">Manage your campaign lore, NPCs, and world-building content</p>
                            </div>
                            @if($user_is_creator || $user_is_member)
                                <a href="{{ route('campaigns.pages', $campaign->campaign_code) }}" 
                                   class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-400 text-amber-900 font-semibold rounded-xl transition-colors text-sm">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Manage Pages
                                </a>
                            @endif
                        </div>
                        
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h3 class="font-outfit text-lg font-semibold text-white mb-2">Organize Your Campaign</h3>
                            <p class="text-slate-400 text-sm mb-6 max-w-sm mx-auto">
                                Create hierarchical pages to organize lore, NPCs, locations, and plot information. Use rich text editing and category tags for easy organization.
                            </p>
                            @if($user_is_creator || $user_is_member)
                                <a href="{{ route('campaigns.pages', $campaign->campaign_code) }}" 
                                   class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-amber-900 font-semibold rounded-xl transition-all duration-300 shadow-lg hover:shadow-amber-500/25">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Start Building
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Handouts Tab -->
                    <div x-show="activeTab === 'handouts'" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform translate-y-2"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 transform translate-y-0"
                         x-transition:leave-end="opacity-0 transform translate-y-2"
                         x-cloak
                         class="absolute inset-0 p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="font-outfit text-lg font-semibold text-white">Campaign Handouts</h2>
                                <p class="text-slate-400 text-sm">Documents, images, and files for your campaign</p>
                            </div>
                            @if($user_is_creator)
                                <a href="{{ route('campaigns.handouts', $campaign->campaign_code) }}" 
                                   class="inline-flex items-center bg-blue-500 hover:bg-blue-400 text-white font-semibold py-2 px-4 rounded-xl transition-colors text-sm">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    Manage Handouts
                                </a>
                            @endif
                        </div>

                        <div class="bg-slate-800/30 rounded-xl p-6 border border-slate-700/50">
                            <div class="text-center">
                                <svg class="w-16 h-16 mx-auto text-slate-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <h3 class="text-white font-medium mb-2">Campaign Handouts</h3>
                                <p class="text-slate-400 mb-4 max-w-md mx-auto">
                                    Share documents, images, maps, character portraits, and other files with your players. Control who can see what with flexible access permissions.
                                </p>
                                @if($user_is_creator)
                                    <a href="{{ route('campaigns.handouts', $campaign->campaign_code) }}"
                                       class="inline-flex items-center bg-blue-600 hover:bg-blue-500 text-white font-medium py-2 px-4 rounded-xl transition-colors text-sm">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        Get Started
                                    </a>
                                @else
                                    <p class="text-slate-500 text-sm">Only the Game Master can manage handouts</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Rooms Tab -->
                    <div x-show="activeTab === 'rooms'" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform translate-y-2"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 transform translate-y-0"
                         x-transition:leave-end="opacity-0 transform translate-y-2"
                         x-cloak
                         class="absolute inset-0 p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="font-outfit text-lg font-semibold text-white">Campaign Rooms</h2>
                                <p class="text-slate-400 text-sm">Video rooms for this campaign</p>
                            </div>
                            @if($user_is_creator || $user_is_member)
                                <a href="{{ route('campaigns.rooms.create', $campaign->campaign_code) }}" 
                                   class="inline-flex items-center px-4 py-2 bg-purple-500 hover:bg-purple-400 text-white font-semibold rounded-xl transition-colors text-sm">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Create Room
                                </a>
                            @endif
                        </div>
                        
                        @if($campaign_rooms && $campaign_rooms->count() > 0)
                            <div class="space-y-3">
                                @foreach($campaign_rooms as $room)
                                    <div class="bg-slate-800/50 border border-slate-600/50 rounded-xl p-4 hover:bg-slate-800/70 transition-colors">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <h3 class="font-outfit font-semibold text-white text-sm">{{ $room->name }}</h3>
                                                <p class="text-slate-400 text-xs mt-1">{{ $room->description ?: 'No description' }}</p>
                                                <div class="flex items-center gap-4 mt-2 text-xs text-slate-400">
                                                    <div class="flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                        </svg>
                                                        <span>{{ $room->creator?->username ?? 'Unknown' }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        <span>{{ \Carbon\Carbon::parse($room->created_at)->diffForHumans() }}</span>
                                                    </div>
                                                    @if(!$room->password)
                                                        <div class="flex items-center gap-1 text-emerald-400">
                                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                            </svg>
                                                            <span>No Password</span>
                                                        </div>
                                                    @endif
                                                    <span class="px-2 py-1 bg-purple-500/20 text-purple-400 rounded text-xs">Campaign</span>
                                                </div>
                                            </div>
                                            @if($user_is_creator)
                                                <a href="{{ route('rooms.show', $room->invite_code) }}" 
                                                   class="inline-flex items-center px-3 py-2 bg-slate-700 hover:bg-slate-600 text-white text-xs font-medium rounded-lg transition-colors">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                    </svg>
                                                    Manage
                                                </a>
                                            @else
                                                <a href="{{ route('rooms.invite', $room->invite_code) }}" 
                                                   class="inline-flex items-center px-3 py-2 bg-slate-700 hover:bg-slate-600 text-white text-xs font-medium rounded-lg transition-colors">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                    </svg>
                                                    Join
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="w-16 h-16 bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <h3 class="font-outfit text-lg font-semibold text-white mb-2">No Rooms Yet</h3>
                                <p class="text-slate-400 text-sm mb-6 max-w-sm mx-auto">
                                    Create video rooms for your campaign sessions. Campaign members can join without passwords.
                                </p>
                                @if($user_is_creator || $user_is_member)
                                    <a href="{{ route('campaigns.rooms.create', $campaign->campaign_code) }}" 
                                       class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-500 to-indigo-500 hover:from-purple-400 hover:to-indigo-400 text-white font-semibold rounded-xl transition-all duration-300 shadow-lg hover:shadow-purple-500/25">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Create Room
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($user_is_creator)
        <x-invite-modal 
            modal-id="campaignInviteModal"
            title="Share Campaign Invite"
            :invite-code="$campaign->invite_code"
            :invite-url="route('campaigns.invite', $campaign->invite_code)"
            code-label="Campaign Code"
            link-label="Campaign Link"
        />
    @endif

    <!-- Character Selection Modal -->
    @if($user_is_member || $user_is_creator)
        <div id="characterSelectionModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-slate-900/95 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-outfit text-xl font-semibold text-white">Select Character</h3>
                    <button onclick="hideCharacterSelectionModal()" class="text-slate-400 hover:text-slate-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="characterSelectionForm" method="POST" action="{{ route('campaigns.update_character', $campaign->campaign_code) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" id="memberIdInput" name="member_id" value="">
                    
                    <div class="space-y-4">
                        <h4 class="font-outfit text-lg text-white mb-4">Choose a character for this campaign:</h4>
                        
                        @if(auth()->user()->characters->count() > 0)
                            <div class="grid grid-cols-1 gap-3">
                                @foreach(auth()->user()->characters as $character)
                                    <label class="flex items-center p-4 bg-slate-800/50 hover:bg-slate-800/70 border border-slate-600/50 hover:border-slate-500/50 rounded-xl cursor-pointer transition-all">
                                        <input type="radio" name="character_id" value="{{ $character->id }}" class="sr-only">
                                        <div class="flex items-center gap-3 flex-1">
                                            <div class="w-12 h-12 rounded-xl overflow-hidden flex-shrink-0">
                                                @if($character->class)
                                                    <x-class-banner className="{{ $character->class }}" class="w-full h-full object-cover" />
                                                @else
                                                    <div class="w-full h-full bg-slate-700 flex items-center justify-center">
                                                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                        </svg>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex-1">
                                                <h5 class="font-semibold text-white">{{ $character->name }}</h5>
                                                <p class="text-slate-400 text-sm">
                                                    {{ $character->class ?: 'Incomplete' }} 
                                                    @if($character->subclass) / {{ $character->subclass }} @endif
                                                    @if($character->ancestry) • {{ $character->ancestry }} @endif
                                                </p>
                                            </div>
                                            <div class="w-5 h-5 border-2 border-slate-400 rounded-full flex items-center justify-center">
                                                <div class="w-2 h-2 bg-amber-400 rounded-full opacity-0 transition-opacity"></div>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="w-16 h-16 bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <h5 class="font-outfit text-lg text-white mb-2">No Characters Found</h5>
                                <p class="text-slate-400 text-sm mb-4">You need to create a character first.</p>
                                <a href="{{ route('character-builder') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-amber-900 font-semibold rounded-xl transition-all duration-300">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Create Character
                                </a>
                            </div>
                        @endif
                    </div>

                    @if(auth()->user()->characters->count() > 0)
                        <div class="flex items-center justify-between mt-6 pt-4 border-t border-slate-700/50">
                            <button type="button" onclick="hideCharacterSelectionModal()" 
                                    class="px-4 py-2 text-slate-400 hover:text-slate-300 transition-colors">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-amber-900 font-semibold rounded-xl transition-all duration-300">
                                Update Character
                            </button>
                        </div>
                    @endif
                </form>
            </div>
        </div>

        <script>
            function showCharacterSelectionModal(memberId) {
                document.getElementById('memberIdInput').value = memberId;
                document.getElementById('characterSelectionModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function hideCharacterSelectionModal() {
                document.getElementById('characterSelectionModal').classList.add('hidden');
                document.body.style.overflow = 'auto';
                
                // Clear selection
                const radios = document.querySelectorAll('input[name="character_id"]');
                radios.forEach(radio => {
                    radio.checked = false;
                    radio.closest('label').querySelector('.opacity-0').classList.add('opacity-0');
                });
            }

            // Handle radio button visual feedback
            document.addEventListener('DOMContentLoaded', function() {
                const radios = document.querySelectorAll('input[name="character_id"]');
                radios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        // Remove selection from all
                        radios.forEach(r => {
                            r.closest('label').querySelector('.opacity-0').classList.add('opacity-0');
                            r.closest('label').classList.remove('border-amber-500/50', 'bg-amber-500/10');
                        });
                        
                        // Add selection to current
                        if (this.checked) {
                            this.closest('label').querySelector('.opacity-0').classList.remove('opacity-0');
                            this.closest('label').classList.add('border-amber-500/50', 'bg-amber-500/10');
                        }
                    });
                });
            });
        </script>
    @endif
</x-layout>