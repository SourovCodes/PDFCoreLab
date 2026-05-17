@extends('layouts.app')

@section('title', 'Delivery — PDFCoreLab')

@section('content')
    <main class="flex-1 px-6 py-12 lg:py-16">
        <div class="max-w-4xl mx-auto">
            <a href="{{ route('dashboard.webhook-deliveries.index') }}" class="text-sm text-[#706f6c] dark:text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">← Back to deliveries</a>
            <h1 class="text-2xl lg:text-3xl font-bold tracking-tight mt-2 mb-8 font-mono break-all">{{ $delivery->event }}</h1>

            <div class="rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] p-6 lg:p-8 mb-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-6 text-sm">
                    <div>
                        <dt class="text-[#706f6c] dark:text-[#A1A09A]">Delivery ID</dt>
                        <dd class="font-mono text-xs break-all">{{ $delivery->public_id }}</dd>
                    </div>
                    <div>
                        <dt class="text-[#706f6c] dark:text-[#A1A09A]">Status</dt>
                        <dd>
                            @switch($delivery->status)
                                @case(\App\Models\WebhookDelivery::STATUS_PENDING)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">Pending</span>
                                    @break
                                @case(\App\Models\WebhookDelivery::STATUS_DELIVERED)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">Delivered</span>
                                    @break
                                @case(\App\Models\WebhookDelivery::STATUS_FAILED)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">Failed</span>
                                    @break
                            @endswitch
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[#706f6c] dark:text-[#A1A09A]">API Key</dt>
                        <dd>{{ $delivery->apiKey?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[#706f6c] dark:text-[#A1A09A]">Compression</dt>
                        <dd class="font-mono text-xs break-all">{{ $delivery->pdfCompression?->public_id ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[#706f6c] dark:text-[#A1A09A]">URL</dt>
                        <dd class="font-mono text-xs break-all">{{ $delivery->url }}</dd>
                    </div>
                    <div>
                        <dt class="text-[#706f6c] dark:text-[#A1A09A]">Response Status</dt>
                        <dd>{{ $delivery->response_status ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[#706f6c] dark:text-[#A1A09A]">Attempts</dt>
                        <dd>{{ $delivery->attempt }}</dd>
                    </div>
                    <div>
                        <dt class="text-[#706f6c] dark:text-[#A1A09A]">Created</dt>
                        <dd>{{ $delivery->created_at?->toDayDateTimeString() }}</dd>
                    </div>
                    @if($delivery->delivered_at)
                        <div>
                            <dt class="text-[#706f6c] dark:text-[#A1A09A]">Delivered</dt>
                            <dd>{{ $delivery->delivered_at->toDayDateTimeString() }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            @if($delivery->error)
                <div class="rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 p-4 mb-6">
                    <p class="text-xs font-medium text-red-800 dark:text-red-300 mb-2">Last error</p>
                    <pre class="text-xs whitespace-pre-wrap break-all text-red-900 dark:text-red-200">{{ $delivery->error }}</pre>
                </div>
            @endif

            <div class="rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] p-6 lg:p-8 mb-6">
                <h2 class="font-semibold mb-3">Request body</h2>
                <pre class="text-xs bg-[#0a0a0a] text-[#EDEDEC] rounded p-4 overflow-x-auto">{{ json_encode($delivery->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>

            @if($delivery->response_body)
                <div class="rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] p-6 lg:p-8">
                    <h2 class="font-semibold mb-3">Response body</h2>
                    <pre class="text-xs bg-[#0a0a0a] text-[#EDEDEC] rounded p-4 overflow-x-auto whitespace-pre-wrap break-all">{{ $delivery->response_body }}</pre>
                </div>
            @endif
        </div>
    </main>
@endsection
