<x-layout.default>
    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950">
        <div class="container mx-auto px-4 py-8">
            
            <!-- Campaign Header -->
            <div class="mb-8">
                <div class="flex items-center text-slate-400 text-sm mb-4">
                    <a href="{{ route('campaigns.index') }}" class="hover:text-white transition-colors">Campaigns</a>
                    <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <a href="{{ route('campaigns.show', $campaign) }}" class="hover:text-white transition-colors">{{ $campaign->name }}</a>
                    <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="text-white">Handouts</span>
                </div>
                
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-outfit font-bold text-white mb-2">{{ $campaign->name }}</h1>
                        <p class="text-slate-400">Manage handouts and documents for your campaign</p>
                    </div>
                    
                    <!-- Campaign Navigation -->
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('campaigns.show', $campaign) }}" 
                           class="text-slate-400 hover:text-white transition-colors flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0V7a2 2 0 012-2h4a2 2 0 012 2v0" />
                            </svg>
                            Overview
                        </a>
                        
                        <a href="{{ route('campaigns.pages', $campaign) }}" 
                           class="text-slate-400 hover:text-white transition-colors flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Pages
                        </a>
                        
                        <a href="{{ route('campaigns.handouts', $campaign) }}" 
                           class="text-amber-400 flex items-center font-medium">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            Handouts
                        </a>
                    </div>
                </div>
            </div>

            <!-- Handouts Manager Component -->
            <div class="bg-slate-900/80 backdrop-blur-xl rounded-xl border border-slate-700/50 shadow-xl p-6">
                <livewire:campaign-handout.campaign-handout-manager :campaign="$campaign" />
            </div>
        </div>
    </div>
</x-layout.default>
