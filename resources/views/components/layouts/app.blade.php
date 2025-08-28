<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>HeartfeltDagger</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=MedievalSharp&family=Outfit:wght@100..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

        <link rel="icon" type="image/png" href="/favicon/favicon-96x96.png" sizes="96x96" />
        <link rel="icon" type="image/svg+xml" href="/favicon/favicon.svg" />
        <link rel="shortcut icon" href="/favicon/favicon.ico" />
        <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png" />
        <link rel="manifest" href="/favicon/site.webmanifest" />

        <!-- Styles -->
        @livewireStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-900 text-white min-h-screen">
        <div class="fixed w-full h-full bg-repeat opacity-10 pointer-events-none -z-1" style="background-image: url('{{ asset('img/textures/black-marble.jpg') }}');"></div>
        <div>
            @if(!request()->is('login') && !request()->is('register'))
                <x-navigation />
            @endif
            
            <main>
                {{ $slot }}
            </main>
            
            <!-- Footer -->
            <footer data-testid="main-footer" class="bg-slate-800 border-t border-slate-700">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-8">
                    <div class="text-center">
                        <!-- Discord Link -->
                        <div class="mb-4 md:mb-6">
                            <a href="{{ route('discord') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-white/80 hover:text-white transition-colors font-roboto text-sm md:text-base" x-tooltip="Join our Discord community">
                                <x-icons.discord class="w-5 h-5" />
                                Join our Discord Community
                            </a>
                        </div>
                        
                        <div class="text-xs sm:text-sm text-slate-400 leading-relaxed max-w-4xl mx-auto">
                            <p class="mb-2 md:mb-3">
                                This repository includes materials from the Daggerheart System Reference Document © Critical Role, LLC. under the terms of the Darrington Press Community Gaming (DPCGL) License. More information can be found at 
                                <a href="https://www.daggerheart.com/" target="_blank" rel="noopener noreferrer" class="text-blue-400 hover:text-blue-300 underline break-words">https://www.daggerheart.com/</a>. 
                                There are minor modifications to format and structure.
                            </p>
                            <p class="mb-2 md:mb-3">
                                Daggerheart and all related marks are trademarks of Critical Role, LLC and used with permission. This project is not affiliated with, endorsed, or sponsored by Critical Role or Darrington Press.
                            </p>
                            <p>
                                For full license terms, see: 
                                <a href="https://www.daggerheart.com/" target="_blank" rel="noopener noreferrer" class="text-blue-400 hover:text-blue-300 underline break-words">https://www.daggerheart.com/</a>
                            </p>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
        
        @livewireScripts
    </body>
</html>
