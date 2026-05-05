@extends('layouts.app')

@section('title', 'Reset Password — PDFCoreLab')

@section('content')
    <main class="flex-1 flex items-center justify-center px-6 py-16 lg:py-24">
        <div class="max-w-md w-full">
            <div class="text-center mb-8">
                <h1 class="text-2xl lg:text-3xl font-bold tracking-tight">Set new password</h1>
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mt-2">
                    Choose a new password for your account
                </p>
            </div>

            <div class="rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] p-6 lg:p-8">
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">

                    {{-- Email --}}
                    <div class="mb-5">
                        <label for="email" class="block text-sm font-medium mb-1.5">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required
                               class="w-full rounded-md border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#f53003]/50 focus:border-[#f53003]">
                        @error('email')
                            <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mb-5">
                        <label for="password" class="block text-sm font-medium mb-1.5">New Password</label>
                        <input id="password" type="password" name="password" required autofocus
                               class="w-full rounded-md border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#f53003]/50 focus:border-[#f53003]">
                        @error('password')
                            <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div class="mb-6">
                        <label for="password_confirmation" class="block text-sm font-medium mb-1.5">Confirm New Password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required
                               class="w-full rounded-md border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#f53003]/50 focus:border-[#f53003]">
                    </div>

                    <button type="submit"
                            class="w-full inline-flex items-center justify-center px-6 py-3 bg-[#1b1b18] dark:bg-[#EDEDEC] text-white dark:text-[#1b1b18] font-semibold rounded-md text-sm hover:bg-black dark:hover:bg-white transition">
                        Reset Password
                    </button>
                </form>
            </div>
        </div>
    </main>
@endsection
