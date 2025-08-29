@props([
    'show' => false,
    'maxWidth' => '2xl',
    'title' => '',
    'subtitle' => '',
    'onClose' => null
])



<div 
    x-data="{ 
        show: @js($show),
        init() {
            this.$watch('show', (value) => {
                if (value) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = '';
                }
            });
        },
        close() {
            this.show = false;
            @if($onClose)
                setTimeout(() => { {{ $onClose }} }, 300);
            @endif
        }
    }"
    x-show="show"
    x-on:keydown.escape.window="close()"
    x-on:slideover-open.window="show = true"
    x-on:slideover-close.window="close()"
    x-trap="show"
    class="fixed inset-0 z-50 overflow-hidden"
    x-cloak
>
    <!-- Background overlay -->
    <div 
        x-show="show"
        x-transition:enter="transition-opacity ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute inset-0 bg-black/60 backdrop-blur-sm"
        @click="close()"
    ></div>
    
    <!-- Slideover panel -->
    <div 
        x-show="show"
        x-transition:enter="transform transition ease-in-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in-out duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 {{ 
            $maxWidth === 'sm' ? 'max-w-sm' : (
            $maxWidth === 'md' ? 'max-w-md' : (
            $maxWidth === 'lg' ? 'max-w-lg' : (
            $maxWidth === 'xl' ? 'max-w-xl' : (
            $maxWidth === '3xl' ? 'max-w-3xl' : (
            $maxWidth === '4xl' ? 'max-w-4xl' : (
            $maxWidth === '5xl' ? 'max-w-5xl' : 'max-w-2xl')))))) 
        }} w-full bg-slate-900 border-l border-slate-700 shadow-2xl"
    >
        <div class="h-full flex flex-col">
            @if($title || $subtitle || isset($header))
                <!-- Header -->
                <div class="flex items-center justify-between p-6 border-b border-slate-700 bg-slate-900">
                    @if(isset($header))
                        {{ $header }}
                    @else
                        <div>
                            @if($title)
                                <h2 class="text-xl font-outfit font-bold text-white">
                                    {{ $title }}
                                </h2>
                            @endif
                            @if($subtitle)
                                <p class="text-slate-300 mt-1 text-sm">
                                    {{ $subtitle }}
                                </p>
                            @endif
                        </div>
                    @endif
                    
                    <button 
                        @click="close()"
                        class="p-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition-colors"
                        type="button"
                        data-close
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endif

            <!-- Content -->
            @if(isset($content))
                <div class="flex-1 overflow-y-auto">
                    {{ $content }}
                </div>
            @else
                <div class="flex-1 overflow-y-auto">
                    {{ $slot }}
                </div>
            @endif

            <!-- Footer -->
            @if(isset($footer))
                <div class="border-t border-slate-700 bg-slate-900">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
