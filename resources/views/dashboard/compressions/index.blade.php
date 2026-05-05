@extends('layouts.app')

@section('title', 'Compressions — PDFCoreLab')

@section('content')
    <main class="flex-1 px-6 py-12 lg:py-16">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-2xl lg:text-3xl font-bold tracking-tight mb-8">PDF Compressions</h1>

            <div class="rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] p-6 lg:p-8">
                @if($compressions->isEmpty())
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">No compressions yet. Use your API key to compress your first PDF.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                                    <th class="text-left py-2 pr-4 font-medium">File</th>
                                    <th class="text-left py-2 pr-4 font-medium">Preset</th>
                                    <th class="text-left py-2 pr-4 font-medium">Original</th>
                                    <th class="text-left py-2 pr-4 font-medium">Compressed</th>
                                    <th class="text-left py-2 pr-4 font-medium">Status</th>
                                    <th class="text-left py-2 font-medium">Date</th>
                                </tr>
                            </thead>
                            <tbody class="text-[#706f6c] dark:text-[#A1A09A]">
                                @foreach($compressions as $compression)
                                    <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                                        <td class="py-3 pr-4 text-[#1b1b18] dark:text-[#EDEDEC]">{{ Str::limit($compression->original_filename, 25) }}</td>
                                        <td class="py-3 pr-4">{{ $compression->ghostscript_preset->value }}</td>
                                        <td class="py-3 pr-4">
                                            @if($compression->original_size_bytes)
                                                {{ number_format($compression->original_size_bytes / 1024, 1) }} KB
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="py-3 pr-4">
                                            @if($compression->compressed_size_bytes)
                                                {{ number_format($compression->compressed_size_bytes / 1024, 1) }} KB
                                                @php
                                                    $reduction = round((1 - $compression->compressed_size_bytes / $compression->original_size_bytes) * 100);
                                                @endphp
                                                <span class="text-green-600 dark:text-green-400 text-xs">(−{{ $reduction }}%)</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="py-3 pr-4">
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
                                        <td class="py-3">{{ $compression->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $compressions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </main>
@endsection
