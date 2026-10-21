@extends('layouts.admin.app')

@section('title', 'Status QR Code Presensi')

@section('content')
<div class="rounded-lg bg-white p-6 shadow-theme-sm dark:bg-gray-dark">
    
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 pb-5 dark:border-gray-700">
        <div>
            <h2 class="text-title-md font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-qrcode text-brand-500"></i> Status QR Code Presensi PMB
            </h2>
            <p class="text-sm text-gray-500 mt-1">Memantau mahasiswa yang sudah dan belum memiliki QR Code.</p>
        </div>
        <div>
            <a href="{{ url('/admin/generate-qrcode') }}" class="rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-all shadow-sm">
                <i class="fas fa-cogs mr-2"></i> Generate Massal
            </a>
        </div>
    </div>

    {{-- KARTU RINGKASAN --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <a href="{{ url()->current() }}" class="rounded-xl border border-gray-100 bg-gray-50 p-5 shadow-sm transition hover:border-gray-300 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pendaftar Lunas</p>
            <h4 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                {{ number_format($totalLunas) }}
            </h4>
        </a>
        <a href="{{ url()->current() }}?status=generated" class="rounded-xl border border-green-100 bg-green-50/50 p-5 shadow-sm transition hover:border-green-300 dark:border-green-900/20 dark:bg-green-900/10">
            <p class="text-sm font-medium text-green-600 dark:text-green-400">QR Code Siap Cetak (Sudah Ada)</p>
            <h4 class="mt-2 text-2xl font-bold text-green-700 dark:text-green-300">
                {{ number_format($totalSiapGlobal) }}
            </h4>
        </a>
        <a href="{{ url()->current() }}?status=pending" class="rounded-xl border border-yellow-100 bg-yellow-50/50 p-5 shadow-sm transition hover:border-yellow-300 dark:border-yellow-900/20 dark:bg-yellow-900/10">
            <p class="text-sm font-medium text-yellow-600 dark:text-yellow-400">Menunggu Generate (Belum Ada)</p>
            <h4 class="mt-2 text-2xl font-bold text-yellow-700 dark:text-yellow-300">
                {{ number_format(max(0, $totalLunas - $totalSiapGlobal)) }}
            </h4>
        </a>
    </div>

    {{-- FORM FILTER & SEARCH --}}
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
            <label for="status" class="text-sm font-medium text-gray-700 dark:text-gray-300">Filter Status:</label>
            <select name="status" id="status" onchange="this.form.submit()" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="" {{ empty($statusFilter) ? 'selected' : '' }}>Semua Status</option>
                <option value="generated" {{ $statusFilter === 'generated' ? 'selected' : '' }}>Sudah Di-generate</option>
                <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Belum Di-generate</option>
            </select>
        </form>
    </div>

    {{-- TABEL DATA --}}
    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="min-w-full border-collapse bg-white text-sm dark:bg-gray-800">
            <thead class="bg-gray-100 dark:bg-gray-700">
                <tr>
                    <th class="border-b border-gray-200 p-4 text-left font-semibold text-gray-700 dark:border-gray-600 dark:text-gray-300">No</th>
                    <th class="border-b border-gray-200 p-4 text-left font-semibold text-gray-700 dark:border-gray-600 dark:text-gray-300">TRX ID</th>
                    <th class="border-b border-gray-200 p-4 text-left font-semibold text-gray-700 dark:border-gray-600 dark:text-gray-300">Nama Mahasiswa</th>
                    <th class="border-b border-gray-200 p-4 text-center font-semibold text-gray-700 dark:border-gray-600 dark:text-gray-300">Status QR Code</th>
                    <th class="border-b border-gray-200 p-4 text-center font-semibold text-gray-700 dark:border-gray-600 dark:text-gray-300">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($mahasiswa as $index => $row)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="p-4 text-gray-600 dark:text-gray-400">
                            {{ $mahasiswa->firstItem() + $index }}
                        </td>
                        <td class="p-4 font-mono text-sm text-gray-600 dark:text-gray-400">
                            {{ $row->trx_id }}
                        </td>
                        <td class="p-4 font-medium text-gray-900 dark:text-white">
                            {{ $row->nama }}
                        </td>
                        <td class="p-4 text-center">
                            @if($row->has_qr)
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i> Sudah Dibuat
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-1 text-xs font-semibold text-yellow-800">
                                    <i class="fas fa-clock mr-1"></i> Belum Ada
                                </span>
                            @endif
                        </td>
                        <td class="p-4 text-center">
                            @if($row->has_qr)
                                <a href="{{ asset('assets/QrCodePresensi/qrcode-id-' . $row->trx_id . '.svg') }}" target="_blank" 
                                   class="inline-block rounded border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                    Lihat QR
                                </a>
                            @else
                                <span class="text-xs text-gray-400 italic">Menunggu...</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-gray-500">
                            Tidak ditemukan data dengan kriteria filter tersebut.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    <div class="mt-4">
        {{ $mahasiswa->links() }}
    </div>

</div>
@endsection