<x-layout>
    <x-slot name="title">{{ $campaign->name }} - Pages</x-slot>

    <!-- Compact Navigation -->
    <x-sub-navigation>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a 
                    href="{{ route('campaigns.show', $campaign) }}"
                    class="p-1.5 text-slate-400 hover:text-white hover:bg-slate-700/50 rounded-md transition-colors"
                    title="Back to campaign"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <span class="text-sm text-slate-400">{{ $campaign->name }}</span>
            </div>

            <!-- Campaign Navigation -->
            <div class="flex items-center gap-1">
                <a 
                    href="{{ route('campaigns.show', $campaign) }}"
                    class="px-3 py-1.5 text-sm text-slate-400 hover:text-white hover:bg-slate-700/50 rounded-md transition-colors"
                >
                    Overview
                </a>
                <span class="px-3 py-1.5 bg-slate-700 text-white rounded-md text-sm font-medium">
                    Pages
                </span>
                @if($campaign->campaignFrame)
                    <a 
                        href="{{ route('campaign-frames.show', $campaign->campaignFrame) }}"
                        class="px-3 py-1.5 text-sm text-slate-400 hover:text-white hover:bg-slate-700/50 rounded-md transition-colors"
                    >
                        Frame
                    </a>
                @endif
            </div>
        </div>
    </x-sub-navigation>

    <!-- Campaign Page Manager -->
    <div class="min-h-screen">
        <livewire:campaign-page.campaign-page-manager :campaign="$campaign" />
    </div>
</x-layout>
