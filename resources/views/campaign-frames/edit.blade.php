<x-layout>
    <div class="min-h-screen">
        <div class="px-4 sm:px-6 lg:px-8 pt-12 pb-16">
            <div class="max-w-4xl mx-auto">
                <!-- Header -->
                <div class="text-center mb-12">
                    <h1 class="font-outfit text-4xl text-white tracking-wide mb-2">
                        Edit Campaign Frame
                    </h1>
                    <p class="text-slate-300 text-lg">
                        Refine your campaign foundation
                    </p>
                </div>

                <!-- Back Button -->
                <div class="mb-8">
                    <a href="{{ route('campaign-frames.show', $frame->id) }}" class="inline-flex items-center text-slate-400 hover:text-white transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Frame
                    </a>
                </div>

                <!-- Edit Form -->
                <livewire:campaign-frame.campaign-frame-manager mode="edit" :frame="$frame" />
            </div>
        </div>
    </div>
</x-layout>
