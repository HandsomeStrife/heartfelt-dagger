<div class="max-w-4xl mx-auto p-6">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-outfit font-bold text-slate-800 mb-6">TipTap Rich Text Editor Test</h1>
        
        <!-- TipTap Editor -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-slate-700 mb-2">
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
            
            <div class="text-sm text-slate-500">
                Content will sync automatically with Livewire
            </div>
        </div>

        <!-- Debug Output -->
        <div class="mt-8 border-t pt-6">
            <h3 class="text-lg font-medium text-slate-800 mb-3">Raw HTML Output:</h3>
            <div class="bg-slate-100 rounded p-4 text-sm font-mono text-slate-700 overflow-auto max-h-60">
                {{ $content }}
            </div>
        </div>

        <!-- Rendered Output -->
        <div class="mt-6">
            <h3 class="text-lg font-medium text-slate-800 mb-3">Rendered Preview:</h3>
            <div class="border rounded p-4 prose prose-slate max-w-none">
                {!! $content !!}
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
