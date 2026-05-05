@extends('layouts.app')

@section('title', 'Log In — PDFCoreLab')

@section('content')
    <main class="flex-1 flex items-center justify-center px-6 py-16 lg:py-24">
        <div class="max-w-md w-full">
            <div class="text-center mb-8">
                <h1 class="text-2xl lg:text-3xl font-bold tracking-tight">Welcome back</h1>
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mt-2">
                    Log in to manage your API keys and compressions
                </p>
            </div>

            <div class="rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] p-6 lg:p-8">
                @if (session('status'))
                    <div class="mb-4 rounded-md bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-3 text-sm text-green-700 dark:text-green-400">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    {{-- Email --}}
                    <div class="mb-5">
                        <label for="email" class="block text-sm font-medium mb-1.5">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full rounded-md border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#f53003]/50 focus:border-[#f53003]">
                        @error('email')
                            <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mb-5">
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="text-sm font-medium">Password</label>
                            <a href="{{ route('password.request') }}" class="text-xs text-[#f53003] underline underline-offset-4 hover:text-[#f53003]/80">
                                Forgot password?
                            </a>
                        </div>
                        <input id="password" type="password" name="password" required
                               class="w-full rounded-md border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#f53003]/50 focus:border-[#f53003]">
                        @error('password')
                            <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Remember --}}
                    <div class="mb-6">
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input type="checkbox" name="remember" class="rounded border-[#e3e3e0] dark:border-[#3E3E3A] text-[#f53003] focus:ring-[#f53003]">
                            Remember me
                        </label>
                    </div>

                    <button type="submit"
                            class="w-full inline-flex items-center justify-center px-6 py-3 bg-[#1b1b18] dark:bg-[#EDEDEC] text-white dark:text-[#1b1b18] font-semibold rounded-md text-sm hover:bg-black dark:hover:bg-white transition">
                        Log In
                    </button>
                </form>

                <p class="text-sm text-center text-[#706f6c] dark:text-[#A1A09A] mt-6">
                    Don't have an account?
                    <a href="{{ route('register') }}" class="text-[#f53003] underline underline-offset-4 hover:text-[#f53003]/80">Register for free</a>
                </p>
            </div>
        </div>
    </main>
@endsection
