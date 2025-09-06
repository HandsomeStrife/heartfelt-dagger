<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        <title>{{ config('app.name', 'Laravel') }}</title>
        
        <!-- Favicon -->
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-touch-icon.png') }}">
        <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon/favicon-96x96.png') }}">
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon/favicon.svg') }}">
        <link rel="shortcut icon" href="{{ asset('favicon/favicon.ico') }}">
        <link rel="manifest" href="{{ asset('favicon/site.webmanifest') }}">
        
        <!-- Preconnect to Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=MedievalSharp&display=swap" rel="stylesheet">
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- AssemblyAI SDK for speech-to-text -->
        <script src="https://www.unpkg.com/assemblyai@4.7.1/dist/assemblyai.streaming.umd.min.js"></script>
    </head>
    <body class="bg-slate-900 text-white min-h-screen overflow-hidden">
        <div class="fixed w-full h-full bg-repeat opacity-10 pointer-events-none z-0" style="background-image: url('{{ asset('img/textures/black-marble.jpg') }}');"></div>
        <div class="relative h-screen w-screen">
            <main class="relative z-10 h-full w-full">
                {{ $slot }}
            </main>
        </div>
        @livewireScriptConfig
    </body>
</html>
