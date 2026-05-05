<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', 'PDFCoreLab')</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] antialiased min-h-screen flex flex-col">

        {{-- Navigation --}}
        <header class="px-6 py-4 flex items-center justify-between border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
            <a href="{{ url('/') }}" class="text-lg font-bold tracking-tight">
                <span class="text-[#f53003]">PDF</span>CoreLab
            </a>
            <nav class="flex items-center gap-4 text-sm">
                @auth
                    <a href="{{ route('dashboard') }}" class="text-[#706f6c] dark:text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC] transition">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-[#706f6c] dark:text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC] transition">Log Out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-[#706f6c] dark:text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC] transition">Log In</a>
                    <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-[#1b1b18] dark:bg-[#EDEDEC] text-white dark:text-[#1b1b18] font-semibold rounded-md text-sm hover:bg-black dark:hover:bg-white transition">Register</a>
                @endauth
            </nav>
        </header>

        @yield('content')

        <footer class="py-6 text-center text-xs text-[#706f6c] dark:text-[#A1A09A]">
            PDFCoreLab &copy; {{ date('Y') }}. Powered by Laravel &amp; Ghostscript.
        </footer>
    </body>
</html>
