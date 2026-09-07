<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ $title ?? 'Drestro Staff' }}</title>
        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="#10b981">
        
        <!-- Offline Fonts -->
        <link rel="stylesheet" href="{{ asset('fonts/inter/inter.css') }}">
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="bg-slate-50 font-[Inter] antialiased text-slate-800 dark:bg-slate-900 dark:text-slate-200">
        <div class="h-screen flex flex-col overflow-hidden">
            <!-- Premium Glassy Top Bar -->
            <header class="h-16 bg-white/80 backdrop-blur-md border-b border-slate-200/50 flex items-center justify-between px-6 shadow-[0_4px_24px_-8px_rgba(0,0,0,0.05)] flex-shrink-0 z-50 sticky top-0 dark:bg-slate-900/80">
                @php $restaurant = current_restaurant(); @endphp
                <div class="flex items-center gap-4">
                    @if($restaurant && $restaurant->logo)
                        <img src="{{ Storage::url($restaurant->logo) }}" class="h-8 w-auto drop-shadow-sm" alt="Logo">
                    @endif
                    <div class="flex items-center gap-3">
                        <span class="text-xl font-black bg-gradient-to-r from-emerald-600 to-teal-500 bg-clip-text text-transparent tracking-tight">{{ $restaurant->name ?? 'Drestro' }}</span>
                        <div class="h-5 w-px bg-slate-300 dark:bg-slate-700"></div>
                        <span class="text-sm font-bold text-slate-500 dark:text-slate-400">{{ $title ?? 'Staff Panel' }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 bg-emerald-50 rounded-full border border-emerald-100 shadow-inner dark:bg-emerald-900/20 dark:border-emerald-800/50">
                    <div class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </div>
                    <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider dark:text-emerald-400">Live</span>
                </div>
            </header>

            <!-- Full Screen Content -->
            <main class="flex-1 overflow-hidden p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>
        
        @livewireScripts
    </body>
</html>
