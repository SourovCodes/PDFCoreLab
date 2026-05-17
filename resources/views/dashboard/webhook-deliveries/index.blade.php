@extends('layouts.app')

@section('title', 'Webhook Deliveries — PDFCoreLab')

@section('content')
    <main class="flex-1 px-6 py-12 lg:py-16">
        <div class="max-w-5xl mx-auto">
            <h1 class="text-2xl lg:text-3xl font-bold tracking-tight mb-8">Webhook Deliveries</h1>

            {{-- Filters --}}
            <form method="GET" class="mb-6 flex flex-wrap items-center gap-3">
                <select name="status" class="rounded-md border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] px-3 py-2 text-sm">
                    <option value="">All statuses</option>
                    @foreach (['pending', 'delivered', 'failed'] as $value)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? null) === $value)>{{ ucfirst($value) }}</option>
                    @endforeach
                </select>
                <select name="event" class="rounded-md border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] px-3 py-2 text-sm">
                    <option value="">All events</option>
                    @foreach (['compression.completed', 'compression.failed'] as $value)
                        <option value="{{ $value }}" @selected(($filters['event'] ?? null) === $value)>{{ $value }}</option>
                    @endforeach
                </select>
                <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-[#1b1b18] dark:bg-[#EDEDEC] text-white dark:text-[#1b1b18] font-medium rounded-md text-sm hover:bg-black dark:hover:bg-white transition">
                    Apply
                </button>
                @if(($filters['status'] ?? null) || ($filters['event'] ?? null))
                    <a href="{{ route('dashboard.webhook-deliveries.index') }}" class="text-sm text-[#706f6c] dark:text-[#A1A09A] underline underline-offset-4">Clear</a>
                @endif
            </form>

            <div class="rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] p-6 lg:p-8">
                @if($deliveries->isEmpty())
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        No webhook deliveries yet. Configure a webhook URL on an
                        <a href="{{ route('dashboard.api-keys.index') }}" class="text-[#f53003] underline underline-offset-4">API key</a>
                        and queue a PDF compression to receive callbacks here.
                    </p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                                    <th class="text-left py-2 pr-4 font-medium">Event</th>
                                    <th class="text-left py-2 pr-4 font-medium">API Key</th>
                                    <th class="text-left py-2 pr-4 font-medium">Compression</th>
                                    <th class="text-left py-2 pr-4 font-medium">Status</th>
                                    <th class="text-left py-2 pr-4 font-medium">Response</th>
                                    <th class="text-left py-2 pr-4 font-medium">Attempts</th>
                                    <th class="text-left py-2 font-medium">When</th>
                                </tr>
                            </thead>
                            <tbody class="text-[#706f6c] dark:text-[#A1A09A]">
                                @foreach($deliveries as $delivery)
                                    <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A] hover:bg-[#fafafa] dark:hover:bg-[#1a1a1a]">
                                        <td class="py-3 pr-4 text-[#1b1b18] dark:text-[#EDEDEC] font-mono text-xs">
                                            <a href="{{ route('dashboard.webhook-deliveries.show', $delivery) }}" class="hover:underline">
                                                {{ $delivery->event }}
                                            </a>
                                        </td>
                                        <td class="py-3 pr-4">{{ $delivery->apiKey?->name ?? '—' }}</td>
                                        <td class="py-3 pr-4 font-mono text-xs">{{ $delivery->pdfCompression?->public_id ? Str::limit($delivery->pdfCompression->public_id, 12) : '—' }}</td>
                                        <td class="py-3 pr-4">
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
                                        </td>
                                        <td class="py-3 pr-4">{{ $delivery->response_status ?? '—' }}</td>
                                        <td class="py-3 pr-4">{{ $delivery->attempt }}</td>
                                        <td class="py-3">{{ $delivery->created_at->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $deliveries->links() }}
                    </div>
                @endif
            </div>
        </div>
    </main>
@endsection
