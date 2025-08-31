@props(['placeholder' => 'Start writing...', 'height' => '300px'])

<div
    x-data="setupEditor(
        $wire.entangle('{{ $attributes->wire('model')->value() }}')
    )"
    x-init="() => init($refs.editorContent)"
    wire:ignore
    {{ $attributes->whereDoesntStartWith('wire:model') }}
    class="w-full"
>
    <!-- Toolbar -->
    <div class="border border-slate-600 rounded-t-lg bg-slate-800 p-2 flex flex-wrap gap-1">
        <!-- Check if editor is loaded before showing toolbar -->
        <template x-if="isLoaded()">
            <div class="contents">
                <!-- Text Formatting -->
                <button 
                    type="button"
                    @click="toggleBold()"
                    :class="{ 'bg-slate-600': isActive('bold', {}, updatedAt) }"
                    class="px-2 py-1 rounded text-slate-300 hover:bg-slate-600 hover:text-white transition-colors"
                    title="Bold"
                >
                    <x-icons.editor.bold class="w-4 h-4" />
                </button>
                
                <button 
                    type="button"
                    @click="toggleItalic()"
                    :class="{ 'bg-slate-600': isActive('italic', {}, updatedAt) }"
                    class="px-2 py-1 rounded text-slate-300 hover:bg-slate-600 hover:text-white transition-colors"
                    title="Italic"
                >
                    <x-icons.editor.italic class="w-4 h-4" />
                </button>
                
                <button 
                    type="button"
                    @click="toggleUnderline()"
                    :class="{ 'bg-slate-600': isActive('underline', {}, updatedAt) }"
                    class="px-2 py-1 rounded text-slate-300 hover:bg-slate-600 hover:text-white transition-colors"
                    title="Underline"
                >
                    <x-icons.editor.underline class="w-4 h-4" />
                </button>

        <div class="w-px h-6 bg-slate-600 mx-1"></div>

                <!-- Headings -->
                <button 
                    type="button"
                    @click="toggleHeading(1)"
                    :class="{ 'bg-slate-600': isActive('heading', { level: 1 }, updatedAt) }"
                    class="px-2 py-1 rounded text-slate-300 hover:bg-slate-600 hover:text-white transition-colors font-bold text-sm"
                    title="Heading 1"
                >
                    <x-icons.editor.h1 class="w-4 h-4" />
                </button>
                
                <button 
                    type="button"
                    @click="toggleHeading(2)"
                    :class="{ 'bg-slate-600': isActive('heading', { level: 2 }, updatedAt) }"
                    class="px-2 py-1 rounded text-slate-300 hover:bg-slate-600 hover:text-white transition-colors font-bold text-sm"
                    title="Heading 2"
                >
                    <x-icons.editor.h2 class="w-4 h-4" />
                </button>
                
                <button 
                    type="button"
                    @click="toggleHeading(3)"
                    :class="{ 'bg-slate-600': isActive('heading', { level: 3 }, updatedAt) }"
                    class="px-2 py-1 rounded text-slate-300 hover:bg-slate-600 hover:text-white transition-colors font-bold text-sm"
                    title="Heading 3"
                >
                    <x-icons.editor.h3 class="w-4 h-4" />
                </button>

        <div class="w-px h-6 bg-slate-600 mx-1"></div>

                <!-- Lists -->
                <button 
                    type="button"
                    @click="toggleBulletList()"
                    :class="{ 'bg-slate-600': isActive('bulletList', {}, updatedAt) }"
                    class="px-2 py-1 rounded text-slate-300 hover:bg-slate-600 hover:text-white transition-colors"
                    title="Bullet List"
                >
                    <x-icons.editor.list-bullet class="w-4 h-4" />
                </button>
                
                <button 
                    type="button"
                    @click="toggleOrderedList()"
                    :class="{ 'bg-slate-600': isActive('orderedList', {}, updatedAt) }"
                    class="px-2 py-1 rounded text-slate-300 hover:bg-slate-600 hover:text-white transition-colors"
                    title="Ordered List"
                >
                    <x-icons.editor.numbered-list class="w-4 h-4" />
                </button>

        <div class="w-px h-6 bg-slate-600 mx-1"></div>

                <!-- Quote & Code -->
                <button 
                    type="button"
                    @click="toggleBlockquote()"
                    :class="{ 'bg-slate-600': isActive('blockquote', {}, updatedAt) }"
                    class="px-2 py-1 rounded text-slate-300 hover:bg-slate-600 hover:text-white transition-colors"
                    title="Quote"
                >
                    <x-icons.editor.quote class="w-4 h-4" />
                </button>
                
                <button 
                    type="button"
                    @click="toggleCode()"
                    :class="{ 'bg-slate-600': isActive('code', {}, updatedAt) }"
                    class="px-2 py-1 rounded text-slate-300 hover:bg-slate-600 hover:text-white transition-colors"
                    title="Inline Code"
                >
                    <x-icons.editor.code class="w-4 h-4" />
                </button>

        <div class="w-px h-6 bg-slate-600 mx-1"></div>

                <!-- Alignment -->
                <button 
                    type="button"
                    @click="setTextAlign('left')"
                    :class="{ 'bg-slate-600': isActive({ textAlign: 'left' }, {}, updatedAt) }"
                    class="px-2 py-1 rounded text-slate-300 hover:bg-slate-600 hover:text-white transition-colors"
                    title="Align Left"
                >
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4h14v2H3V4zm0 4h10v2H3V8zm0 4h14v2H3v-2zm0 4h10v2H3v-2z"/>
                    </svg>
                </button>
                
                <button 
                    type="button"
                    @click="setTextAlign('center')"
                    :class="{ 'bg-slate-600': isActive({ textAlign: 'center' }, {}, updatedAt) }"
                    class="px-2 py-1 rounded text-slate-300 hover:bg-slate-600 hover:text-white transition-colors"
                    title="Align Center"
                >
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4h14v2H3V4zm2 4h10v2H5V8zm-2 4h14v2H3v-2zm2 4h10v2H5v-2z"/>
                    </svg>
                </button>
                
                <button 
                    type="button"
                    @click="setTextAlign('right')"
                    :class="{ 'bg-slate-600': isActive({ textAlign: 'right' }, {}, updatedAt) }"
                    class="px-2 py-1 rounded text-slate-300 hover:bg-slate-600 hover:text-white transition-colors"
                    title="Align Right"
                >
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4h14v2H3V4zm4 4h10v2H7V8zm-4 4h14v2H3v-2zm4 4h10v2H7v-2z"/>
                    </svg>
                </button>
            </div>
        </template>
    </div>
    
    <!-- Editor Content -->
    <div 
        x-ref="editorContent" 
        class="border border-t-0 border-slate-600 rounded-b-lg p-4 focus-within:ring-2 focus-within:ring-amber-500 focus-within:border-amber-500 transition-colors bg-slate-900"
        style="min-height: {{ $height }}; max-height: 600px; overflow-y: auto;"
    ></div>
</div>
