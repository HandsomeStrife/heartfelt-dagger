<div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950">
    <!-- Compact Navigation -->
    <x-sub-navigation>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a 
                    href="{{ route('dashboard') }}"
                    class="p-1.5 text-slate-400 hover:text-white hover:bg-slate-700/50 rounded-md transition-colors"
                    title="Back to dashboard"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="font-outfit text-lg font-bold text-white tracking-wide">
                        TipTap Rich Text Editor Test
                    </h1>
                    <p class="text-slate-400 text-xs">
                        Testing rich text editor functionality
                    </p>
                </div>
            </div>

            <div class="text-xs text-slate-400">
                Development Tool
            </div>
        </div>
    </x-sub-navigation>

    <div class="px-4 sm:px-6 lg:px-8 pt-8 pb-12">
        <div class="max-w-4xl mx-auto">
            <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
        
        <!-- TipTap Editor -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-slate-300 mb-2">
                Campaign Page Content
            </label>
            <x-tiptap-editor 
                wire:model="content" 
                placeholder="Start writing your campaign content..."
                height="400px"
                class="w-full"
            />
        </div>

        <!-- Actions -->
        <div class="flex justify-between items-center">
            <button 
                wire:click="save"
                class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg font-medium transition-colors"
            >
                Save Content
            </button>
            
            <div class="text-sm text-slate-400">
                Content will sync automatically with Livewire
            </div>
        </div>

                <!-- Debug Output -->
                <div class="mt-8 border-t border-slate-700 pt-6">
                    <h3 class="text-lg font-medium text-slate-300 mb-3">Raw HTML Output:</h3>
                    <div class="bg-slate-800 rounded p-4 text-sm font-mono text-slate-300 overflow-auto max-h-60">
                        {{ $content }}
                    </div>
                </div>

                <!-- Rendered Output -->
                <div class="mt-6">
                    <h3 class="text-lg font-medium text-slate-300 mb-3">Rendered Preview:</h3>
                    <div class="bg-slate-800 border border-slate-600 rounded p-4 prose prose-invert prose-slate max-w-none">
                        {!! $content !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>
$wire.on('content-saved', (event) => {
    // Show a success message
    alert('Content saved successfully!\n\nContent length: ' + event.content.length + ' characters');
});
</script>
@endscript
