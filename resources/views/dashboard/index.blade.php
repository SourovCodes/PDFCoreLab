@extends('layouts.app')

@section('title', 'Dashboard — PDFCoreLab')

@section('content')
    <main class="flex-1 px-6 py-12 lg:py-16">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-2xl lg:text-3xl font-bold tracking-tight mb-8">Dashboard</h1>

            {{-- Stats cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-10">
                <div class="rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] p-5">
                    <div class="text-sm text-[#706f6c] dark:text-[#A1A09A] mb-1">API Keys</div>
                    <div class="text-2xl font-bold">{{ $activeKeys }} <span class="text-sm font-normal text-[#706f6c] dark:text-[#A1A09A]">/ {{ $totalKeys }} total</span></div>
                </div>
                <div class="rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] p-5">
                    <div class="text-sm text-[#706f6c] dark:text-[#A1A09A] mb-1">Total Compressions</div>
                    <div class="text-2xl font-bold">{{ $totalCompressions }}</div>
                </div>
                <div class="rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] p-5">
                    <div class="text-sm text-[#706f6c] dark:text-[#A1A09A] mb-1">Quick Links</div>
                    <div class="flex flex-wrap gap-3 mt-1">
                        <a href="{{ route('dashboard.api-keys.index') }}" class="text-sm text-[#f53003] underline underline-offset-4 hover:text-[#f53003]/80">API Keys</a>
                        <a href="{{ route('dashboard.compressions.index') }}" class="text-sm text-[#f53003] underline underline-offset-4 hover:text-[#f53003]/80">Compressions</a>
                        <a href="{{ route('dashboard.webhook-deliveries.index') }}" class="text-sm text-[#f53003] underline underline-offset-4 hover:text-[#f53003]/80">Webhooks</a>
                    </div>
                </div>
            </div>

            {{-- Recent compressions --}}
            <div class="rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] p-6 lg:p-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-lg">Recent Compressions</h2>
                    <a href="{{ route('dashboard.compressions.index') }}" class="text-sm text-[#f53003] underline underline-offset-4 hover:text-[#f53003]/80">View all</a>
                </div>

                @if($recentCompressions->isEmpty())
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">No compressions yet. Use your API key to compress your first PDF.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                                    <th class="text-left py-2 pr-4 font-medium">File</th>
                                    <th class="text-left py-2 pr-4 font-medium">Preset</th>
                                    <th class="text-left py-2 pr-4 font-medium">Status</th>
                                    <th class="text-left py-2 font-medium">Date</th>
                                </tr>
                            </thead>
                            <tbody class="text-[#706f6c] dark:text-[#A1A09A]">
                                @foreach($recentCompressions as $compression)
                                    <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                                        <td class="py-2 pr-4 text-[#1b1b18] dark:text-[#EDEDEC]">{{ Str::limit($compression->original_filename, 30) }}</td>
                                        <td class="py-2 pr-4">{{ $compression->ghostscript_preset->value }}</td>
                                        <td class="py-2 pr-4">
                                            @switch($compression->status)
                                                @case(\App\Enums\PdfCompressionStatus::Queued)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">Queued</span>
                                                    @break
                                                @case(\App\Enums\PdfCompressionStatus::Processing)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400">Processing</span>
                                                    @break
                                                @case(\App\Enums\PdfCompressionStatus::Completed)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">Completed</span>
                                                    @break
                                                @case(\App\Enums\PdfCompressionStatus::Failed)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">Failed</span>
                                                    @break
                                            @endswitch
                                        </td>
                                        <td class="py-2">{{ $compression->created_at->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </main>
@endsection
