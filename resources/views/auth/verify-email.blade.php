@extends('layouts.app')

@section('title', 'Verify Email — PDFCoreLab')

@section('content')
    <main class="flex-1 flex items-center justify-center px-6 py-16 lg:py-24">
        <div class="max-w-md w-full">
            <div class="text-center mb-8">
                <h1 class="text-2xl lg:text-3xl font-bold tracking-tight">Verify your email</h1>
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mt-2">
                    We sent a verification link to your email address
                </p>
            </div>

            <div class="rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] p-6 lg:p-8">
                @if (session('status'))
                    <div class="mb-4 rounded-md bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-3 text-sm text-green-700 dark:text-green-400">
                        {{ session('status') }}
                    </div>
                @endif

                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mb-6">
                    Please check your inbox and click the verification link to activate your account. If you didn't receive the email, click the button below to request a new one.
                </p>

                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf

                    <button type="submit"
                            class="w-full inline-flex items-center justify-center px-6 py-3 bg-[#1b1b18] dark:bg-[#EDEDEC] text-white dark:text-[#1b1b18] font-semibold rounded-md text-sm hover:bg-black dark:hover:bg-white transition">
                        Resend Verification Email
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}" class="mt-4">
                    @csrf
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center px-6 py-3 border border-[#19140035] dark:border-[#3E3E3A] font-semibold rounded-md text-sm hover:border-[#1915014a] dark:hover:border-[#62605b] transition">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </main>
@endsection
