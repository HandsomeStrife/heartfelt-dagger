@props([
    'errors' => [],
    'title' => 'Validation Errors',
    'dismissible' => false,
    'severity' => 'error', // error, warning, info
])

@php
    $severityStyles = [
        'error' => [
            'bg' => 'bg-red-500/10',
            'border' => 'border-red-500/30',
            'text' => 'text-red-400',
            'icon' => 'text-red-500',
        ],
        'warning' => [
            'bg' => 'bg-amber-500/10',
            'border' => 'border-amber-500/30',
            'text' => 'text-amber-400',
            'icon' => 'text-amber-500',
        ],
        'info' => [
            'bg' => 'bg-blue-500/10',
            'border' => 'border-blue-500/30',
            'text' => 'text-blue-400',
            'icon' => 'text-blue-500',
        ],
    ];
    
    $style = $severityStyles[$severity] ?? $severityStyles['error'];
@endphp

<div 
    x-data="{ show: true }"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    class="rounded-lg border-2 {{ $style['bg'] }} {{ $style['border'] }} p-4"
    role="alert"
    aria-live="assertive"
>
    <div class="flex items-start space-x-3">
        <!-- Icon -->
        <div class="flex-shrink-0 {{ $style['icon'] }}">
            @if($severity === 'error')
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            @elseif($severity === 'warning')
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            @else
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            @endif
        </div>

        <!-- Content -->
        <div class="flex-1 min-w-0">
            <h4 class="text-sm font-bold {{ $style['text'] }} mb-2">
                {{ $title }}
            </h4>
            
            @if(is_array($errors) && count($errors) > 0)
                <ul class="space-y-1.5">
                    @foreach($errors as $error)
                        @if(is_array($error))
                            <!-- Grouped Errors -->
                            @if(isset($error['level']))
                                <li class="text-sm {{ $style['text'] }}">
                                    <span class="font-semibold">Level {{ $error['level'] }}:</span>
                                    @if(is_array($error['errors']))
                                        <ul class="ml-4 mt-1 space-y-1">
                                            @foreach($error['errors'] as $levelError)
                                                <li class="flex items-start space-x-2">
                                                    <span class="text-{{ $severity === 'error' ? 'red' : ($severity === 'warning' ? 'amber' : 'blue') }}-400 mt-0.5">•</span>
                                                    <span>{{ $levelError }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span>{{ $error['errors'] }}</span>
                                    @endif
                                </li>
                            @elseif(isset($error['message']))
                                <li class="flex items-start space-x-2 text-sm {{ $style['text'] }}">
                                    <span class="text-{{ $severity === 'error' ? 'red' : ($severity === 'warning' ? 'amber' : 'blue') }}-400 mt-0.5">•</span>
                                    <span>{{ $error['message'] }}</span>
                                </li>
                            @endif
                        @else
                            <!-- Simple Error String -->
                            <li class="flex items-start space-x-2 text-sm {{ $style['text'] }}">
                                <span class="text-{{ $severity === 'error' ? 'red' : ($severity === 'warning' ? 'amber' : 'blue') }}-400 mt-0.5">•</span>
                                <span>{{ $error }}</span>
                            </li>
                        @endif
                    @endforeach
                </ul>
            @elseif(is_string($errors))
                <p class="text-sm {{ $style['text'] }}">
                    {{ $errors }}
                </p>
            @else
                <p class="text-sm {{ $style['text'] }}">
                    No errors to display.
                </p>
            @endif
        </div>

        <!-- Dismiss Button -->
        @if($dismissible)
            <button 
                type="button"
                @click="show = false"
                class="{{ $style['text'] }} hover:opacity-75 transition-opacity flex-shrink-0"
                aria-label="Dismiss"
            >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        @endif
    </div>
</div>


