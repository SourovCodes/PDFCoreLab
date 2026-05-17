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
            @if(session('success') && !session('newKey') && !session('newWebhookSecret'))
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
                    <div class="space-y-4">
                        @foreach($apiKeys as $key)
                            <div class="rounded-md border border-[#e3e3e0] dark:border-[#3E3E3A] p-4">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <p class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">{{ $key->name }}</p>
                                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1">
                                            Created {{ $key->created_at->format('M d, Y') }}
                                            · Last used {{ $key->last_used_at ? $key->last_used_at->diffForHumans() : 'never' }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        @if($key->is_active)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">Active</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">Inactive</span>
                                        @endif
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
                                    </div>
                                </div>

                                <details class="mt-4 group" @if(session('newWebhookSecretKeyId') === $key->id || ($errors->any() && old('_webhook_key_id') == $key->id)) open @endif>
                                    <summary class="cursor-pointer text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] select-none">
                                        Webhook
                                        @if($key->webhook_url)
                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">Configured</span>
                                        @else
                                            <span class="ml-2 text-xs text-[#706f6c] dark:text-[#A1A09A]">Not configured</span>
                                        @endif
                                    </summary>

                                    <div class="mt-4 space-y-3">
                                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                            Async (queued) compressions will <code class="font-mono">POST</code> a signed JSON payload to this URL on success and failure. Requests include
                                            <code class="font-mono">X-PDFCoreLab-Signature: sha256=&lt;hex&gt;</code> computed with HMAC-SHA256 over the raw body using your signing secret.
                                        </p>

                                        @if(session('newWebhookSecretKeyId') === $key->id && session('newWebhookSecret'))
                                            <div class="rounded-md border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 p-3">
                                                <p class="text-xs font-medium text-green-800 dark:text-green-300 mb-2">Signing secret (copy it now — it won't be shown again):</p>
                                                <code class="block bg-white dark:bg-[#0a0a0a] border border-green-200 dark:border-green-800 rounded px-3 py-2 text-xs font-mono break-all select-all">{{ session('newWebhookSecret') }}</code>
                                            </div>
                                        @endif

                                        <form method="POST" action="{{ route('dashboard.api-keys.webhook', $key) }}" class="space-y-3">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="_webhook_key_id" value="{{ $key->id }}">
                                            <div>
                                                <label class="block text-xs font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Webhook URL</label>
                                                <input type="url" name="webhook_url" value="{{ old('webhook_url', $key->webhook_url) }}" placeholder="https://example.com/webhooks/pdfcorelab"
                                                       class="w-full rounded-md border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#f53003]/50 focus:border-[#f53003]">
                                                @error('webhook_url')
                                                    <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            @if($key->webhook_url)
                                                <label class="flex items-center gap-2 text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                                    <input type="checkbox" name="regenerate_secret" value="1" class="rounded border-[#e3e3e0] dark:border-[#3E3E3A]">
                                                    Regenerate signing secret
                                                </label>
                                            @endif
                                            <div class="flex flex-wrap gap-2">
                                                <button type="submit"
                                                        class="inline-flex items-center justify-center px-4 py-1.5 bg-[#1b1b18] dark:bg-[#EDEDEC] text-white dark:text-[#1b1b18] font-medium rounded-md text-xs hover:bg-black dark:hover:bg-white transition">
                                                    Save
                                                </button>
                                                @if($key->webhook_url)
                                                    <button type="submit" name="webhook_url" value=""
                                                            class="inline-flex items-center justify-center px-4 py-1.5 border border-red-300 dark:border-red-800 text-red-700 dark:text-red-400 font-medium rounded-md text-xs hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                                                        Remove webhook
                                                    </button>
                                                @endif
                                            </div>
                                        </form>
                                    </div>
                                </details>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $apiKeys->links() }}
                    </div>
                @endif
            </div>
        </div>
    </main>
@endsection
