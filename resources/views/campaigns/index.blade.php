<x-layout>
    <div class="min-h-screen">
        <div class="px-4 sm:px-6 lg:px-8 pt-12 pb-16">
            <div class="max-w-6xl mx-auto">
                <!-- Header -->
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h1 class="font-outfit text-3xl text-white tracking-wide">
                            Campaigns
                        </h1>
                        <p class="text-slate-300 text-lg">
                            Manage your epic adventures
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button onclick="showModal('joinCampaignModal')" class="inline-flex items-center bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-400 hover:to-cyan-400 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 shadow-lg hover:shadow-blue-500/25">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM12 14c-1.49 0-2.92.6-4 1.67V19h8v-3.33c-1.08-1.07-2.51-1.67-4-1.67z" />
                            </svg>
                            Join Campaign
                        </button>
                        <a href="{{ route('campaigns.create') }}" class="inline-flex items-center bg-gradient-to-r from-violet-500 to-purple-500 hover:from-violet-400 hover:to-purple-400 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 shadow-lg hover:shadow-violet-500/25">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Create Campaign
                        </a>
                    </div>
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
                                The Campaigns feature is currently under active development. While functional, you may encounter bugs, data loss, or breaking changes. 
                                Please use with caution and report any issues you discover.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- My Campaigns -->
                    <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 bg-gradient-to-br from-violet-500/20 to-purple-500/20 rounded-xl flex items-center justify-center border border-violet-500/30 mr-3">
                                <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="font-outfit text-xl font-bold text-white">My Campaigns</h2>
                                <p class="text-slate-400 text-sm">Campaigns you've created</p>
                            </div>
                        </div>

                        @if($created_campaigns->isEmpty())
                            <div class="text-center py-8">
                                <div class="w-16 h-16 bg-slate-800 rounded-xl flex items-center justify-center border border-slate-600 mb-4 mx-auto">
                                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-white font-semibold mb-2">No campaigns yet</h3>
                                <p class="text-slate-400 text-sm mb-4">Create your first campaign to get started.</p>
                                <a href="{{ route('campaigns.create') }}" class="inline-flex items-center px-4 py-2 bg-violet-500 hover:bg-violet-400 text-white text-sm font-semibold rounded-lg transition-colors">
                                    Create Campaign
                                </a>
                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach($created_campaigns as $campaign)
                                    <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-4 hover:border-violet-500/30 transition-all duration-300">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <h3 class="font-outfit font-semibold text-white mb-1">{{ $campaign->name }}</h3>
                                                <p class="text-slate-300 text-sm mb-2 line-clamp-2">{{ $campaign->description }}</p>
                                                <div class="flex items-center gap-4 text-xs">
                                                    <span class="text-slate-400">
                                                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                                        </svg>
                                                        {{ $campaign->member_count ?? 0 }} members
                                                    </span>
                                                    <span class="px-2 py-1 bg-{{ $campaign->status->color() }}-500/20 text-{{ $campaign->status->color() }}-400 rounded-full">
                                                        {{ $campaign->status->label() }}
                                                    </span>
                                                </div>
                                            </div>
                                            <a href="{{ route('campaigns.show', $campaign->campaign_code) }}" class="ml-4 text-violet-400 hover:text-violet-300 transition-colors">
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

                    <!-- Joined Campaigns -->
                    <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 bg-gradient-to-br from-emerald-500/20 to-teal-500/20 rounded-xl flex items-center justify-center border border-emerald-500/30 mr-3">
                                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="font-outfit text-xl font-bold text-white">Joined Campaigns</h2>
                                <p class="text-slate-400 text-sm">Campaigns you're participating in</p>
                            </div>
                        </div>

                        @if($joined_campaigns->isEmpty())
                            <div class="text-center py-8">
                                <div class="w-16 h-16 bg-slate-800 rounded-xl flex items-center justify-center border border-slate-600 mb-4 mx-auto">
                                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-white font-semibold mb-2">No joined campaigns</h3>
                                <p class="text-slate-400 text-sm">You'll see campaigns you've joined here.</p>
                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach($joined_campaigns as $campaign)
                                    <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-4 hover:border-emerald-500/30 transition-all duration-300">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <h3 class="font-outfit font-semibold text-white mb-1">{{ $campaign->name }}</h3>
                                                <p class="text-slate-300 text-sm mb-2 line-clamp-2">{{ $campaign->description }}</p>
                                                <div class="flex items-center gap-4 text-xs">
                                                    <span class="text-slate-400">
                                                        Created by {{ $campaign->creator?->username ?? 'Unknown' }}
                                                    </span>
                                                    <span class="text-slate-400">
                                                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                                        </svg>
                                                        {{ $campaign->member_count ?? 0 }} members
                                                    </span>
                                                </div>
                                            </div>
                                            <a href="{{ route('campaigns.show', $campaign->campaign_code) }}" class="ml-4 text-emerald-400 hover:text-emerald-300 transition-colors">
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
                </div>
            </div>
        </div>
    </div>

    <!-- Join Campaign Modal -->
    <div id="joinCampaignModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl p-8 max-w-md w-full mx-4">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-outfit font-bold text-white">Join Campaign</h3>
                <button onclick="hideModal('joinCampaignModal')" class="text-slate-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <form action="{{ route('campaigns.join') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-slate-300 text-sm font-semibold mb-2">Campaign Invite Code</label>
                    <input 
                        type="text" 
                        name="invite_code" 
                        id="join_invite_code"
                        required 
                        maxlength="8"
                        pattern="[A-Z0-9]{8}"
                        placeholder="e.g., ABC12345"
                        class="w-full bg-slate-800 text-white px-4 py-3 rounded-lg border border-slate-600 font-mono text-lg tracking-wider uppercase focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                        autocomplete="off"
                    >
                    <p class="text-slate-400 text-xs mt-2">Enter the 8-character code provided by the campaign creator</p>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideModal('joinCampaignModal')" class="px-6 py-3 text-slate-400 hover:text-white font-semibold transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-400 hover:to-cyan-400 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 shadow-lg">
                        Join Campaign
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Auto-format invite code input
        document.getElementById('join_invite_code').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        });

        // Include the modal functions from the component
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

        // Setup modal event listeners when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Close modal when clicking outside
            document.getElementById('joinCampaignModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    hideModal('joinCampaignModal');
                }
            });

            // Close modal with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    hideModal('joinCampaignModal');
                }
            });
        });
    </script>
</x-layout>
