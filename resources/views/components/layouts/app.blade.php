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
        <!-- Static texture background -->
        <div class="fixed w-full h-full bg-repeat opacity-5 pointer-events-none -z-1" style="background-image: url('{{ asset('img/textures/black-marble.jpg') }}');"></div>
        
        <!-- Animated beam background - like PestPHP -->
        <div class="pointer-events-none fixed inset-0 min-h-screen h-full -z-20 [mask-image:radial-gradient(ellipse_at_100%_100%,black_20%,transparent_80%)] opacity-90">
            <div class="pointer-events-none absolute inset-0 overflow-hidden">
                <div class="beam-background absolute -inset-[20px] z-20 blur-[80px] filter"></div>
            </div>
        </div>
        
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
                        <!-- Community Links -->
                        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 sm:gap-8 mb-6">
                            <a href="{{ route('discord') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-white/80 hover:text-white transition-colors font-roboto text-sm md:text-base" x-tooltip="Join our Discord community">
                                <x-icons.discord class="w-5 h-5" />
                                Join our Discord Community
                            </a>
                            <a href="https://github.com/HandsomeStrife/heartfelt-dagger" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-white/80 hover:text-white transition-colors font-roboto text-sm md:text-base" x-tooltip="Contribute to our open source project">
                                <x-icons.github class="w-5 h-5" />
                                Open Source Project
                            </a>
                        </div>
                        
                        <!-- Open Source Notice -->
                        <div class="mb-6">
                            <p class="text-sm text-slate-300 mb-2">
                                This is an <strong>open source project</strong> - contributions welcome!
                            </p>
                            <p class="text-xs text-slate-400">
                                Help us improve by contributing code, reporting bugs, or suggesting features.
                            </p>
                        </div>
                        
                        <!-- Legal Information -->
                        <div class="text-xs sm:text-sm text-slate-400 leading-relaxed max-w-4xl mx-auto border-t border-slate-700 pt-6">
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
