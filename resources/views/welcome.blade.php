@extends('layouts.app')

@section('title', 'PDFCoreLab — PDF Compression API')

@section('content')
    {{-- Hero --}}
    <main class="flex-1 flex items-center justify-center px-6 py-16 lg:py-24">
        <div class="max-w-3xl w-full text-center">

                {{-- Logo / Title --}}
                <div class="mb-8">
                    <h1 class="text-4xl lg:text-5xl font-bold tracking-tight mb-3">
                        <span class="text-[#f53003]">PDF</span>CoreLab
                    </h1>
                    <p class="text-lg lg:text-xl text-[#706f6c] dark:text-[#A1A09A]">
                        High-performance PDF compression API powered by Ghostscript
                    </p>
                </div>

                {{-- Feature cards --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-12 text-left">
                    <div class="rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] p-5 bg-white dark:bg-[#161615]">
                        <div class="text-[#f53003] font-semibold mb-1">Upload &amp; Compress</div>
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                            Upload PDFs up to 50 MB and compress them with five quality presets — from screen-optimized to prepress.
                        </p>
                    </div>
                    <div class="rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] p-5 bg-white dark:bg-[#161615]">
                        <div class="text-[#f53003] font-semibold mb-1">Async Processing</div>
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                            Jobs are queued and processed in the background. Poll the status or use webhooks to know when they're done.
                        </p>
                    </div>
                    <div class="rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] p-5 bg-white dark:bg-[#161615]">
                        <div class="text-[#f53003] font-semibold mb-1">Signed Downloads</div>
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                            Download original and compressed files via time-limited signed URLs — no extra auth step required.
                        </p>
                    </div>
                </div>

                {{-- Quick start --}}
                <div class="rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] p-6 lg:p-8 text-left mb-12">
                    <h2 class="font-semibold text-lg mb-4">Quick Start</h2>
                    <div class="bg-[#1b1b18] dark:bg-[#0a0a0a] rounded-md p-4 overflow-x-auto mb-4">
                        <pre class="text-sm text-green-400 font-mono leading-relaxed"><code>curl -X POST {{ url('/api/v1/pdf-compressions') }} \
  -H "X-API-Key: YOUR_API_KEY" \
  -F "pdf=@document.pdf" \
  -F "preset=ebook"</code></pre>
                    </div>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        Returns <code class="bg-gray-100 dark:bg-[#3E3E3A] px-1.5 py-0.5 rounded text-xs">202 Accepted</code> with a compression resource you can poll for status.
                    </p>
                </div>

                {{-- Presets table --}}
                <div class="rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] p-6 lg:p-8 text-left mb-12">
                    <h2 class="font-semibold text-lg mb-4">Compression Presets</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                                    <th class="text-left py-2 pr-4 font-medium">Preset</th>
                                    <th class="text-left py-2 pr-4 font-medium">DPI</th>
                                    <th class="text-left py-2 font-medium">Best For</th>
                                </tr>
                            </thead>
                            <tbody class="text-[#706f6c] dark:text-[#A1A09A]">
                                <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                                    <td class="py-2 pr-4"><code class="text-[#1b1b18] dark:text-[#EDEDEC]">screen</code></td>
                                    <td class="py-2 pr-4">72</td>
                                    <td class="py-2">Smallest file — screen viewing only</td>
                                </tr>
                                <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                                    <td class="py-2 pr-4"><code class="text-[#1b1b18] dark:text-[#EDEDEC]">ebook</code></td>
                                    <td class="py-2 pr-4">150</td>
                                    <td class="py-2">Digital distribution</td>
                                </tr>
                                <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                                    <td class="py-2 pr-4"><code class="text-[#1b1b18] dark:text-[#EDEDEC]">printer</code></td>
                                    <td class="py-2 pr-4">300</td>
                                    <td class="py-2">High quality printing</td>
                                </tr>
                                <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                                    <td class="py-2 pr-4"><code class="text-[#1b1b18] dark:text-[#EDEDEC]">prepress</code></td>
                                    <td class="py-2 pr-4">300+</td>
                                    <td class="py-2">Professional prepress output</td>
                                </tr>
                                <tr>
                                    <td class="py-2 pr-4"><code class="text-[#1b1b18] dark:text-[#EDEDEC]">default</code></td>
                                    <td class="py-2 pr-4">—</td>
                                    <td class="py-2">Ghostscript defaults</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- CTA buttons --}}
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-12">
                    <a href="{{ url('/api/v1/docs') }}"
                       class="inline-flex items-center gap-2 px-6 py-3 bg-[#1b1b18] dark:bg-[#EDEDEC] text-white dark:text-[#1b1b18] font-semibold rounded-md text-sm hover:bg-black dark:hover:bg-white transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                        API Documentation
                    </a>
                    <a href="mailto:sourovcodes@gmail.com?subject=PDFCoreLab API Key Request"
                       class="inline-flex items-center gap-2 px-6 py-3 border border-[#19140035] dark:border-[#3E3E3A] font-semibold rounded-md text-sm hover:border-[#1915014a] dark:hover:border-[#62605b] transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                        Request an API Key
                    </a>
                </div>

                {{-- API key notice --}}
                <div class="rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] bg-gray-100/50 dark:bg-[#161615] p-5 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    <p>
                        <strong class="text-[#1b1b18] dark:text-[#EDEDEC]">Need an API key?</strong>
                        Contact
                        <a href="mailto:sourovcodes@gmail.com" class="text-[#f53003] underline underline-offset-4 hover:text-[#f53003]/80">
                            sourovcodes@gmail.com
                        </a>
                        to get started.
                    </p>
                </div>
        </div>
    </main>
@endsection
