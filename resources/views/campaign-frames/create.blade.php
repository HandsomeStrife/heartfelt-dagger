<x-layout>
    <div class="min-h-screen">
        <!-- Compact Navigation -->
        <x-sub-navigation>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a 
                        href="{{ route('campaign-frames.index') }}"
                        class="p-1.5 text-slate-400 hover:text-white hover:bg-slate-700/50 rounded-md transition-colors"
                        title="Back to campaign frames"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <div>
                        <h1 class="font-outfit text-lg font-bold text-white tracking-wide">
                            Create Campaign Frame
                        </h1>
                        <p class="text-slate-400 text-xs">
                            Design an inspiring foundation for epic adventures
                        </p>
                    </div>
                </div>
            </div>
        </x-sub-navigation>

        <div class="px-4 sm:px-6 lg:px-8 pt-8 pb-12">
            <div class="max-w-4xl mx-auto space-y-6">

                <!-- Create Form -->
                <livewire:campaign-frame.campaign-frame-manager mode="create" />
            </div>
        </div>
    </div>
</x-layout>
