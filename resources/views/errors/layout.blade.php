@extends('layouts.app')

@section('content')
    <main class="flex-1 flex items-center justify-center px-6 py-16 lg:py-24">
        <div class="max-w-lg w-full text-center">

            <h1 class="text-7xl lg:text-9xl font-bold tracking-tight mb-4">
                <span class="text-[#f53003]">@yield('code')</span>
            </h1>

            <h2 class="text-xl lg:text-2xl font-semibold mb-3">
                @yield('message')
            </h2>

            <p class="text-[#706f6c] dark:text-[#A1A09A] mb-10">
                @yield('description')
            </p>

            <a href="{{ url('/') }}"
               class="inline-flex items-center gap-2 px-6 py-3 bg-[#1b1b18] dark:bg-[#EDEDEC] text-white dark:text-[#1b1b18] font-semibold rounded-md text-sm hover:bg-black dark:hover:bg-white transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Go Home
            </a>
        </div>
    </main>
@endsection
