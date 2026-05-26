<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Notification System'))</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-screen overflow-hidden bg-slate-50 text-slate-900 font-sans flex flex-col">
    <header class="shrink-0 bg-white border-b border-slate-200">
        <div class="w-full px-6 sm:px-8 lg:px-12 py-5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 grid place-items-center text-white font-bold shadow-sm">
                    PM
                </div>
                <h1 class="text-base font-semibold text-slate-900 leading-none">Notification System</h1>
            </div>
        </div>
    </header>

    <main class="flex-1 min-h-0 overflow-y-auto lg:overflow-hidden">
        <div class="w-full px-6 sm:px-8 lg:px-12 py-8 lg:h-full lg:flex lg:flex-col">
            @yield('content')
        </div>
    </main>

    <footer class="shrink-0 border-t border-slate-200 bg-white">
        <div class="w-full px-6 sm:px-8 lg:px-12 py-4 text-xs text-slate-500 flex items-center justify-between">
            <span>&copy; {{ date('Y') }} Notification System</span>
            <span>Built by Pedro Matos to LoanPro</span>
        </div>
    </footer>
</body>
</html>
