@extends('layouts.admin.app')

@section('title', 'Data S2 & Final Mahasiswa')

@section('content')
<div class="space-y-6">
    {{-- Header Section --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Data S2 & Final</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Calon mahasiswa yang memilih pilihan pertama (S2)</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.data-s2.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 font-medium text-white transition hover:bg-brand-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Data
            </a>
            <a href="{{ route('admin.data-s2.export') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export CSV
            </a>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        {{-- Total Records --}}
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Data</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $totalRecords }}</p>
                </div>
                <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900/30">
                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Completed Records --}}
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Selesai</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $completedRecords }}</p>
                </div>
                <div class="rounded-full bg-green-100 p-3 dark:bg-green-900/30">
                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Pending Records --}}
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $dataS2List->total() - $completedRecords }}</p>
                </div>
                <div class="rounded-full bg-yellow-100 p-3 dark:bg-yellow-900/30">
                    <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter & Search Section --}}
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
        <form method="GET" class="space-y-4" x-data="{ showAdvanced: false }">
            {{-- Search Bar --}}
            <div class="flex gap-2">
                <div class="flex-1">
                    <input type="text"
                           name="search"
                           placeholder="Cari nama, email, atau nomor HP..."
                           value="{{ request('search') }}"
                           class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400">
                </div>
                <button type="submit"
                        class="rounded-lg bg-brand-500 px-4 py-2 font-medium text-white transition hover:bg-brand-600">
                    Cari
                </button>
                <button type="button"
                        @click="showAdvanced = !showAdvanced"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                </button>
            </div>

            {{-- Advanced Filters --}}
            <div x-show="showAdvanced" x-transition class="space-y-4 border-t border-gray-200 pt-4 dark:border-gray-700">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    {{-- Status Filter --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status Data</label>
                        <select name="status_data"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="">-- Semua Status --</option>
                            @foreach($statusList as $key => $label)
                                <option value="{{ $key }}" {{ request('status_data') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Sort By --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Urutkan Berdasarkan</label>
                        <select name="sort_by"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Terbaru</option>
                            <option value="nama_mahasiswa" {{ request('sort_by') === 'nama_mahasiswa' ? 'selected' : '' }}>Nama A-Z</option>
                            <option value="tanggal_lahir" {{ request('sort_by') === 'tanggal_lahir' ? 'selected' : '' }}>Tanggal Lahir</option>
                        </select>
                    </div>

                    {{-- Per Page --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Item Per Halaman</label>
                        <select name="per_page"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="15" {{ request('per_page') === '15' ? 'selected' : '' }}>15</option>
                            <option value="25" {{ request('per_page') === '25' ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') === '50' ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') === '100' ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Alert Messages --}}
    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-900/30 dark:bg-red-900/10">
            <h3 class="font-semibold text-red-800 dark:text-red-400">Terjadi Kesalahan</h3>
            <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-red-700 dark:text-red-300">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-900/30 dark:bg-green-900/10">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-green-800 dark:text-green-400">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    {{-- Table Section --}}
    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        {{-- Table Header --}}
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900">
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">No.</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Nama Mahasiswa</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Email</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">HP</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Program Studi (Pilihan 1)</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Status Pembayaran</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Status E-Reg</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Status Data</th>
                        <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900 dark:text-white">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($dataS2List as $item)
                        <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-300">
                                {{ $dataS2List->firstItem() + $loop->index }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white font-bold">
                                        {{ substr($item->nama_mahasiswa, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $item->nama_mahasiswa }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">ID: {{ $item->id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ $item->user->email ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ $item->handphone ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                <div class="flex items-center gap-2">
                                    <span class="inline-block w-2 h-2 bg-blue-500 rounded-full"></span>
                                    {{ $item->masterEreg->jurusan1 ?? '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if ($item->masterUser)
                                    @php
                                        $vaStatus = $item->masterUser->va_status;
                                        $statusClass = match($vaStatus) {
                                            'paid' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                                            'expired' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                                        };
                                    @endphp
                                    <span class="inline-block rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                        {{ ucfirst($vaStatus) }}
                                    </span>
                                @else
                                    <span class="inline-block rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                        Belum Ada
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if ($item->masterEreg)
                                    @php
                                        $eregStatus = $item->masterEreg->status;
                                        $statusClass = match($eregStatus) {
                                            'completed' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                            'in_progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                                            'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                                        };
                                    @endphp
                                    <span class="inline-block rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                        {{ match($eregStatus) { 'completed' => 'Selesai', 'in_progress' => 'Proses', 'rejected' => 'Ditolak', default => ucfirst($eregStatus) } }}
                                    </span>
                                @else
                                    <span class="inline-block rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                        Belum Ada
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @php
                                    $dataStatus = $item->status_data;
                                    $statusClass = match($dataStatus) {
                                        'completed' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                        'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                                        'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                                    };
                                @endphp
                                <span class="inline-block rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                    {{ match($dataStatus) { 'completed' => 'Selesai', 'pending' => 'Pending', 'rejected' => 'Ditolak', default => ucfirst($dataStatus) } }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('admin.data-s2.show', $item->id) }}"
                                       class="rounded bg-blue-100 p-2 text-blue-600 transition hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50"
                                       title="Lihat Detail">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.data-s2.edit', $item->id) }}"
                                       class="rounded bg-amber-100 p-2 text-amber-600 transition hover:bg-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:hover:bg-amber-900/50"
                                       title="Edit">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <button onclick="deleteConfirm({{ $item->id }})"
                                            class="rounded bg-red-100 p-2 text-red-600 transition hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50"
                                            title="Hapus">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                    <p class="text-lg font-medium">Data tidak ditemukan</p>
                                    <p class="text-sm">Coba ubah filter pencarian Anda</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="border-t border-gray-200 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
            {{ $dataS2List->links('pagination::tailwind') }}
        </div>
    </div>
</div>

@push('scripts')
<script>
function deleteConfirm(id) {
    Swal.fire({
        title: 'Hapus Data?',
        text: 'Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/data-s2/${id}`;
            form.innerHTML = `
                @csrf
                @method('DELETE')
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush
@endsection