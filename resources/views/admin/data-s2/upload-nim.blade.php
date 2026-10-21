@extends('layouts.admin.app')

@section('title', 'Upload NIM Mahasiswa S2')

@section('content')
<div class="space-y-6" x-data="nimUploader()" x-cloak>

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Upload NIM Mahasiswa S2</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Import NIM dari file Excel ke dalam database</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.data-s2.index') }}"
               class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition">
                ← Kembali
            </a>
            <a href="{{ route('admin.data-s2.nim-template') }}"
               class="rounded-lg border border-green-500 bg-white px-4 py-2 text-sm font-medium text-green-600 hover:bg-green-50 dark:bg-gray-700 dark:text-green-400 dark:hover:bg-gray-600 transition">
                <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download Template
            </a>
        </div>
    </div>

    {{-- Alert: Import Results (Improved) --}}
    @if (session('import_results'))
        @php 
            $res = session('import_results'); 
            $berhasil = count($res['berhasil'] ?? []);
            $dilewati = count($res['dilewati'] ?? []);
            $gagal = count($res['gagal'] ?? []);
        @endphp
        
        <div class="rounded-lg border border-green-200 bg-green-50 p-5 dark:border-green-900/30 dark:bg-green-900/10">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    <h3 class="font-semibold text-green-900 dark:text-green-100">Import Berhasil Selesai</h3>
                    <p class="text-sm text-green-800 dark:text-green-200 mt-1">
                        {{ $berhasil }} berhasil, {{ $dilewati }} dilewati, {{ $gagal }} tidak ditemukan
                    </p>
                </div>
            </div>

            {{-- Summary Cards --}}
            <div class="grid grid-cols-3 gap-3 mt-4">
                <div class="rounded-lg bg-green-100 dark:bg-green-900/20 p-4 text-center">
                    <p class="text-2xl font-bold text-green-700 dark:text-green-400">{{ $berhasil }}</p>
                    <p class="text-xs text-green-600 dark:text-green-300 font-medium mt-1">✅ Berhasil</p>
                </div>
                <div class="rounded-lg bg-yellow-100 dark:bg-yellow-900/20 p-4 text-center">
                    <p class="text-2xl font-bold text-yellow-700 dark:text-yellow-400">{{ $dilewati }}</p>
                    <p class="text-xs text-yellow-600 dark:text-yellow-300 font-medium mt-1">⏭️ Dilewati</p>
                </div>
                <div class="rounded-lg bg-red-100 dark:bg-red-900/20 p-4 text-center">
                    <p class="text-2xl font-bold text-red-700 dark:text-red-400">{{ $gagal }}</p>
                    <p class="text-xs text-red-600 dark:text-red-300 font-medium mt-1">❌ Gagal</p>
                </div>
            </div>

            {{-- Detail Sections --}}
            <div class="mt-4 space-y-2">
                {{-- Detail Berhasil --}}
                @if ($berhasil > 0)
                    <details class="group border border-green-200 dark:border-green-900/30 rounded-lg">
                        <summary class="cursor-pointer px-4 py-3 flex items-center justify-between select-none hover:bg-green-100 dark:hover:bg-green-900/20">
                            <span class="text-sm font-medium text-green-800 dark:text-green-300">
                                📋 {{ $berhasil }} Data Berhasil Diimport
                            </span>
                            <svg class="h-4 w-4 text-green-600 dark:text-green-400 transition group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                            </svg>
                        </summary>
                        <div class="border-t border-green-200 dark:border-green-900/30 px-4 py-3 bg-green-50 dark:bg-green-900/5 max-h-96 overflow-y-auto">
                            <div class="space-y-2">
                                @foreach ($res['berhasil'] as $item)
                                    <div class="flex items-start gap-2 text-sm text-green-700 dark:text-green-300">
                                        <span class="text-green-600 dark:text-green-400 mt-0.5">✓</span>
                                        <div class="flex-1">
                                            <strong>{{ $item['nama'] }}</strong>
                                            <br/>
                                            <code class="text-xs bg-green-100 dark:bg-green-900/30 px-2 py-1 rounded text-green-900 dark:text-green-100">
                                                NIM: {{ $item['nim'] }}
                                            </code>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </details>
                @endif

                {{-- Detail Dilewati --}}
                @if ($dilewati > 0)
                    <details class="group border border-yellow-200 dark:border-yellow-900/30 rounded-lg">
                        <summary class="cursor-pointer px-4 py-3 flex items-center justify-between select-none hover:bg-yellow-100 dark:hover:bg-yellow-900/20">
                            <span class="text-sm font-medium text-yellow-800 dark:text-yellow-300">
                                ⏭️ {{ $dilewati }} Data Dilewati (Sudah Punya NIM)
                            </span>
                            <svg class="h-4 w-4 text-yellow-600 dark:text-yellow-400 transition group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                            </svg>
                        </summary>
                        <div class="border-t border-yellow-200 dark:border-yellow-900/30 px-4 py-3 bg-yellow-50 dark:bg-yellow-900/5 max-h-96 overflow-y-auto">
                            <div class="space-y-2">
                                @foreach ($res['dilewati'] as $item)
                                    <div class="flex items-start gap-2 text-sm text-yellow-700 dark:text-yellow-300">
                                        <span class="text-yellow-600 dark:text-yellow-400 mt-0.5">→</span>
                                        <div class="flex-1">
                                            <strong>{{ $item['nama'] }}</strong>
                                            <br/>
                                            <span class="text-xs">
                                                <code class="bg-yellow-100 dark:bg-yellow-900/30 px-2 py-1 rounded text-yellow-900 dark:text-yellow-100">
                                                    {{ $item['nim_lama'] }}
                                                </code>
                                                <span class="text-yellow-600 dark:text-yellow-400">→</span>
                                                <code class="bg-yellow-100 dark:bg-yellow-900/30 px-2 py-1 rounded text-yellow-900 dark:text-yellow-100">
                                                    {{ $item['nim_baru'] }}
                                                </code>
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </details>
                @endif

                {{-- Detail Gagal --}}
                @if ($gagal > 0)
                    <details class="group border border-red-200 dark:border-red-900/30 rounded-lg">
                        <summary class="cursor-pointer px-4 py-3 flex items-center justify-between select-none hover:bg-red-100 dark:hover:bg-red-900/20">
                            <span class="text-sm font-medium text-red-800 dark:text-red-300">
                                ❌ {{ $gagal }} Data Tidak Ditemukan
                            </span>
                            <svg class="h-4 w-4 text-red-600 dark:text-red-400 transition group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                            </svg>
                        </summary>
                        <div class="border-t border-red-200 dark:border-red-900/30 px-4 py-3 bg-red-50 dark:bg-red-900/5 max-h-96 overflow-y-auto">
                            <div class="space-y-2">
                                @foreach ($res['gagal'] as $item)
                                    <div class="flex items-start gap-2 text-sm text-red-700 dark:text-red-300">
                                        <span class="text-red-600 dark:text-red-400 mt-0.5">✕</span>
                                        <div class="flex-1">
                                            <strong>{{ $item['nama'] }}</strong> 
                                            <code class="text-xs bg-red-100 dark:bg-red-900/30 px-1 rounded text-red-900 dark:text-red-100">
                                                ({{ $item['nim'] }})
                                            </code>
                                            <br/>
                                            <span class="text-xs text-red-600 dark:text-red-400">{{ $item['pesan'] }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </details>
                @endif
            </div>
        </div>
    @endif

    {{-- Error Alert --}}
    @if (session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-900/30 dark:bg-red-900/10 flex items-start gap-3">
            <svg class="h-5 w-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <div class="text-red-800 dark:text-red-400 text-sm">{{ session('error') }}</div>
        </div>
    @endif

    {{-- Format Guide --}}
    <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
        <h2 class="font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Format File Excel
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700">
                        <th class="border border-gray-200 dark:border-gray-600 px-4 py-2 text-left font-semibold text-gray-900 dark:text-white">Kolom</th>
                        <th class="border border-gray-200 dark:border-gray-600 px-4 py-2 text-left font-semibold text-gray-900 dark:text-white">Header</th>
                        <th class="border border-gray-200 dark:border-gray-600 px-4 py-2 text-left font-semibold text-gray-900 dark:text-white">Keterangan</th>
                        <th class="border border-gray-200 dark:border-gray-600 px-4 py-2 text-center font-semibold text-gray-900 dark:text-white">Wajib</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr>
                        <td class="border border-gray-200 dark:border-gray-700 px-4 py-2 font-mono text-gray-700 dark:text-gray-300">A</td>
                        <td class="border border-gray-200 dark:border-gray-700 px-4 py-2 font-mono text-blue-600 dark:text-blue-400">no</td>
                        <td class="border border-gray-200 dark:border-gray-700 px-4 py-2 text-gray-600 dark:text-gray-400">Nomor urut (opsional)</td>
                        <td class="border border-gray-200 dark:border-gray-700 px-4 py-2 text-center text-gray-500">—</td>
                    </tr>
                    <tr class="bg-gray-50 dark:bg-gray-700/30">
                        <td class="border border-gray-200 dark:border-gray-700 px-4 py-2 font-mono text-gray-700 dark:text-gray-300">B</td>
                        <td class="border border-gray-200 dark:border-gray-700 px-4 py-2 font-mono text-blue-600 dark:text-blue-400"><strong>nim</strong></td>
                        <td class="border border-gray-200 dark:border-gray-700 px-4 py-2 text-gray-600 dark:text-gray-400">NIM mahasiswa — pastikan format <strong>Text</strong> di Excel</td>
                        <td class="border border-gray-200 dark:border-gray-700 px-4 py-2 text-center text-red-600 dark:text-red-400">
                            <svg class="h-4 w-4 inline" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></path></svg>
                        </td>
                    </tr>
                    <tr>
                        <td class="border border-gray-200 dark:border-gray-700 px-4 py-2 font-mono text-gray-700 dark:text-gray-300">C</td>
                        <td class="border border-gray-200 dark:border-gray-700 px-4 py-2 font-mono text-blue-600 dark:text-blue-400"><strong>nama_mahasiswa</strong></td>
                        <td class="border border-gray-200 dark:border-gray-700 px-4 py-2 text-gray-600 dark:text-gray-400">Nama untuk pencocokan ke database</td>
                        <td class="border border-gray-200 dark:border-gray-700 px-4 py-2 text-center text-red-600 dark:text-red-400">
                            <svg class="h-4 w-4 inline" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></path></svg>
                        </td>
                    </tr>
                    <tr class="bg-gray-50 dark:bg-gray-700/30">
                        <td class="border border-gray-200 dark:border-gray-700 px-4 py-2 font-mono text-gray-700 dark:text-gray-300">D, E, ...</td>
                        <td class="border border-gray-200 dark:border-gray-700 px-4 py-2 font-mono text-gray-400">email, keterangan, ...</td>
                        <td class="border border-gray-200 dark:border-gray-700 px-4 py-2 text-gray-600 dark:text-gray-400">Diabaikan saat import</td>
                        <td class="border border-gray-200 dark:border-gray-700 px-4 py-2 text-center text-gray-500">—</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Upload Form --}}
    <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Upload File Excel</h2>

        {{-- Dropzone --}}
        <div class="mb-4 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 p-8 text-center transition cursor-pointer"
             :class="{ 'border-blue-500 bg-blue-50 dark:bg-blue-900/10': isDragging }"
             @dragover.prevent="isDragging = true"
             @dragleave.prevent="isDragging = false"
             @drop.prevent="handleDrop($event)"
             @click="$refs.fileInput.click()">
            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-gray-600 dark:text-gray-400 mb-2">Drag & drop file Excel di sini, atau</p>
            <label class="cursor-pointer inline-block rounded-lg bg-blue-500 px-4 py-2 text-sm font-medium text-white hover:bg-blue-600 transition focus-within:outline-none focus-within:ring-2 focus-within:ring-blue-300 dark:focus-within:ring-blue-700">
                Pilih File
                <input type="file" 
                       ref="fileInput"
                       class="sr-only" 
                       accept=".xlsx,.xls,.csv" 
                       aria-label="Pilih file Excel untuk upload NIM"
                       @change="handleFile($event)">
            </label>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Format: .xlsx, .xls, .csv — Maksimal 5 MB</p>
            <p x-show="fileName" class="mt-3 text-sm font-medium text-blue-600 dark:text-blue-400">
                📄 <span x-text="fileName"></span>
                <span x-show="fileSize" class="text-xs text-gray-500 dark:text-gray-400">
                    (<span x-text="formatFileSize(fileSize)"></span>)
                </span>
            </p>
        </div>

        {{-- Opsi Overwrite --}}
        <div class="mb-6 flex items-start gap-3 rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-900/30 dark:bg-yellow-900/10">
            <input type="checkbox" id="overwrite" x-model="overwrite"
                   class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 flex-shrink-0 mt-0.5">
            <label for="overwrite" class="text-sm text-yellow-800 dark:text-yellow-300">
                <strong>Timpa NIM yang sudah ada</strong>
                <br/>
                <span class="text-xs text-yellow-700 dark:text-yellow-400">Centang jika ingin mengganti NIM mahasiswa yang sudah memiliki NIM sebelumnya</span>
            </label>
        </div>

        {{-- Tombol Aksi --}}
        <div class="flex gap-3 flex-col sm:flex-row">
            <button type="button"
                    @click="preview()"
                    :disabled="!selectedFile || isPreviewing"
                    aria-label="Preview data dari file Excel"
                    class="rounded-lg bg-blue-500 px-5 py-2 text-sm font-medium text-white hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition focus:outline-none focus:ring-2 focus:ring-blue-300 dark:focus:ring-blue-700 flex items-center justify-center gap-2">
                <span x-show="!isPreviewing">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Preview Data
                </span>
                <span x-show="isPreviewing" class="flex items-center gap-2">
                    <svg class="h-4 w-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Memuat preview...
                </span>
            </button>
            <button type="button"
                    @click="importNow()"
                    :disabled="!selectedFile || isImporting"
                    aria-label="Import NIM dari file ke database"
                    class="rounded-lg bg-green-500 px-5 py-2 text-sm font-medium text-white hover:bg-green-600 disabled:opacity-50 disabled:cursor-not-allowed transition focus:outline-none focus:ring-2 focus:ring-green-300 dark:focus:ring-green-700 flex items-center justify-center gap-2">
                <span x-show="!isImporting">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Import Sekarang
                </span>
                <span x-show="isImporting" class="flex items-center gap-2">
                    <svg class="h-4 w-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Mengimpor...
                </span>
            </button>
        </div>
    </div>

    {{-- Preview Table --}}
    <div x-show="previewData.length > 0" x-transition class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h2 class="font-semibold text-gray-900 dark:text-white">
                    Preview Data 
                    <span class="text-blue-600 dark:text-blue-400">(<span x-text="previewData.length"></span> baris)</span>
                </h2>
                <div class="flex gap-4 text-sm flex-wrap">
                    <span class="text-green-600 dark:text-green-400">
                        ✅ <strong><span x-text="previewData.filter(r => r.status !== 'tidak_ditemukan').length"></span></strong> siap
                    </span>
                    <span class="text-red-600 dark:text-red-400">
                        ❌ <strong><span x-text="previewData.filter(r => r.status === 'tidak_ditemukan').length"></span></strong> gagal
                    </span>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900">
                        <th class="px-4 py-3 text-left font-semibold text-gray-900 dark:text-white">Baris</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-900 dark:text-white hidden sm:table-cell">NIM</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-900 dark:text-white">Nama</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-900 dark:text-white hidden md:table-cell">NIM Lama</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-900 dark:text-white">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="row in previewData" :key="row.row">
                        <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-700/50"
                            :class="{
                                'bg-red-50 dark:bg-red-900/10': row.status === 'tidak_ditemukan'
                            }">
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs sm:text-sm font-mono" x-text="row.row"></td>
                            <td class="px-4 py-3 font-mono text-gray-900 dark:text-white text-xs hidden sm:table-cell" x-text="row.nim"></td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                <div class="text-xs sm:text-sm font-medium" x-text="row.nama_excel"></div>
                                <div class="text-xs text-gray-500 dark:text-gray-400" x-show="row.nama_db">
                                    → <span x-text="row.nama_db"></span>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-mono text-gray-500 dark:text-gray-400 text-xs hidden md:table-cell" x-text="row.nim_lama ?? '—'"></td>
                            <td class="px-4 py-3">
                                <span class="inline-block rounded-full px-2 py-0.5 text-xs font-semibold whitespace-nowrap"
                                      :class="{
                                          'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': row.status === 'baru',
                                          'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400': row.status === 'update',
                                          'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': row.status === 'tidak_ditemukan'
                                      }"
                                      x-text="row.status === 'baru' ? '✓ Baru' : row.status === 'update' ? '↻ Update' : '✕ Tidak Ada'">
                                </span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
function nimUploader() {
    return {
        selectedFile: null,
        fileName: '',
        fileSize: 0,
        isDragging: false,
        overwrite: false,
        isPreviewing: false,
        isImporting: false,
        previewData: [],

        // Format file size untuk display
        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
        },

        // Validasi file
        validateFile(file) {
            const validExts = ['xlsx', 'xls', 'csv'];
            const ext = file.name.split('.').pop().toLowerCase();
            const maxSize = 5 * 1024 * 1024; // 5MB

            if (!validExts.includes(ext)) {
                return { 
                    valid: false, 
                    message: `Format file tidak valid. Hanya ${validExts.join(', ')} yang diterima.` 
                };
            }

            if (file.size > maxSize) {
                return { 
                    valid: false, 
                    message: `Ukuran file terlalu besar. Maksimal ${this.formatFileSize(maxSize)}.` 
                };
            }

            return { valid: true };
        },

        handleFile(event) {
            const file = event.target.files[0];
            if (file) {
                const validation = this.validateFile(file);
                
                if (!validation.valid) {
                    Swal.fire('Error', validation.message, 'error');
                    this.selectedFile = null;
                    this.fileName = '';
                    this.fileSize = 0;
                    event.target.value = '';
                    return;
                }

                this.selectedFile = file;
                this.fileName = file.name;
                this.fileSize = file.size;
                this.previewData = [];
            }
        },

        handleDrop(event) {
            this.isDragging = false;
            const file = event.dataTransfer.files[0];

            if (!file) return;

            const validation = this.validateFile(file);
            
            if (!validation.valid) {
                Swal.fire('Error', validation.message, 'error');
                return;
            }

            this.selectedFile = file;
            this.fileName = file.name;
            this.fileSize = file.size;
            this.previewData = [];
        },

        async preview() {
            if (!this.selectedFile) {
                Swal.fire('Error', 'Pilih file terlebih dahulu', 'warning');
                return;
            }

            this.isPreviewing = true;
            this.previewData = [];

            const formData = new FormData();
            formData.append('file', this.selectedFile);
            formData.append('_token', '{{ csrf_token() }}');

            try {
                const res = await fetch('{{ route('admin.data-s2.nim-preview') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!res.ok) {
                    throw new Error(`HTTP ${res.status}`);
                }

                const json = await res.json();

                if (json.success) {
                    this.previewData = json.data;
                    
                    // Scroll ke preview
                    setTimeout(() => {
                        document.querySelector('[x-show="previewData.length > 0"]')?.scrollIntoView({ 
                            behavior: 'smooth', 
                            block: 'start' 
                        });
                    }, 100);
                } else {
                    Swal.fire('Error', json.message ?? 'Terjadi kesalahan saat preview', 'error');
                }
            } catch (error) {
                console.error('Preview error:', error);
                let message = 'Terjadi kesalahan saat memproses file';
                
                if (error instanceof TypeError) {
                    message = 'Tidak dapat terhubung ke server';
                }
                
                Swal.fire('Error', message, 'error');
            } finally {
                this.isPreviewing = false;
            }
        },

        importNow() {
            if (!this.selectedFile) {
                Swal.fire('Error', 'Pilih file terlebih dahulu', 'warning');
                return;
            }

            const message = this.overwrite 
                ? '⚠️ Anda akan mengganti NIM yang sudah ada. Lanjutkan?'
                : '✓ NIM yang sudah ada akan <strong>TIDAK</strong> ditimpa. Lanjutkan?';

            Swal.fire({
                title: 'Konfirmasi Import',
                html: message,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Import',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#22c55e',
                cancelButtonColor: '#6b7280',
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submitImport();
                }
            });
        },

        submitImport() {
            this.isImporting = true;

            const formData = new FormData();
            formData.append('file', this.selectedFile);
            formData.append('overwrite', this.overwrite ? '1' : '0');
            formData.append('_token', '{{ csrf_token() }}');

            // Gunakan form submit biasa agar Laravel redirect berfungsi
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('admin.data-s2.nim-import') }}';
            form.enctype = 'multipart/form-data';
            form.style.display = 'none';

            // Token
            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = '{{ csrf_token() }}';
            form.appendChild(tokenInput);

            // Overwrite flag
            const overwriteInput = document.createElement('input');
            overwriteInput.type = 'hidden';
            overwriteInput.name = 'overwrite';
            overwriteInput.value = this.overwrite ? '1' : '0';
            form.appendChild(overwriteInput);

            // File input
            const dt = new DataTransfer();
            dt.items.add(this.selectedFile);
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.name = 'file';
            fileInput.files = dt.files;
            form.appendChild(fileInput);

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }
    };
}
</script>
@endpush
@endsection