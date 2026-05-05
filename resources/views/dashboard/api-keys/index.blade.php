@extends('layouts.app')

@section('title', 'API Keys — PDFCoreLab')

@section('content')
    <main class="flex-1 px-6 py-12 lg:py-16">
        <div class="max-w-4xl mx-auto">
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-2xl lg:text-3xl font-bold tracking-tight">API Keys</h1>
            </div>

            {{-- New key flash --}}
            @if(session('newKey'))
                <div class="mb-6 rounded-lg border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 p-5">
                    <p class="text-sm font-medium text-green-800 dark:text-green-300 mb-2">Your new API key (copy it now — it won't be shown again):</p>
                    <div class="flex items-center gap-2">
                        <code class="flex-1 bg-white dark:bg-[#0a0a0a] border border-green-200 dark:border-green-800 rounded px-3 py-2 text-sm font-mono break-all select-all">{{ session('newKey') }}</code>
                    </div>
                </div>
            @endif

            {{-- Success flash --}}
            @if(session('success') && !session('newKey'))
                <div class="mb-6 rounded-md bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-3 text-sm text-green-700 dark:text-green-400">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Create new key --}}
            <div class="rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] p-6 lg:p-8 mb-8">
                <h2 class="font-semibold text-lg mb-4">Create New Key</h2>
                <form method="POST" action="{{ route('dashboard.api-keys.store') }}" class="flex flex-col sm:flex-row gap-3">
                    @csrf
                    <div class="flex-1">
                        <input type="text" name="name" placeholder="Key name (e.g. Production, Testing)" required
                               class="w-full rounded-md border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#f53003]/50 focus:border-[#f53003]">
                        @error('name')
                            <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit"
                            class="inline-flex items-center justify-center px-6 py-2 bg-[#1b1b18] dark:bg-[#EDEDEC] text-white dark:text-[#1b1b18] font-semibold rounded-md text-sm hover:bg-black dark:hover:bg-white transition">
                        Create Key
                    </button>
                </form>
            </div>

            {{-- Keys list --}}
            <div class="rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] p-6 lg:p-8">
                <h2 class="font-semibold text-lg mb-4">Your Keys</h2>

                @if($apiKeys->isEmpty())
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">No API keys yet. Create one above to get started.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                                    <th class="text-left py-2 pr-4 font-medium">Name</th>
                                    <th class="text-left py-2 pr-4 font-medium">Created</th>
                                    <th class="text-left py-2 pr-4 font-medium">Last Used</th>
                                    <th class="text-left py-2 pr-4 font-medium">Status</th>
                                    <th class="text-right py-2 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-[#706f6c] dark:text-[#A1A09A]">
                                @foreach($apiKeys as $key)
                                    <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                                        <td class="py-3 pr-4 text-[#1b1b18] dark:text-[#EDEDEC] font-medium">{{ $key->name }}</td>
                                        <td class="py-3 pr-4">{{ $key->created_at->format('M d, Y') }}</td>
                                        <td class="py-3 pr-4">{{ $key->last_used_at ? $key->last_used_at->diffForHumans() : 'Never' }}</td>
                                        <td class="py-3 pr-4">
                                            @if($key->is_active)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">Active</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="py-3 text-right">
                                            <form method="POST" action="{{ route('dashboard.api-keys.toggle', $key) }}">
                                                @csrf
                                                @method('PATCH')
                                                @if($key->is_active)
                                                    <button type="submit" class="text-xs text-yellow-600 dark:text-yellow-400 hover:text-yellow-800 dark:hover:text-yellow-300 underline underline-offset-4">
                                                        Deactivate
                                                    </button>
                                                @else
                                                    <button type="submit" class="text-xs text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 underline underline-offset-4">
                                                        Activate
                                                    </button>
                                                @endif
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $apiKeys->links() }}
                    </div>
                @endif
            </div>
        </div>
    </main>
@endsection
