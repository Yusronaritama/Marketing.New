@extends('layouts.admin.app')

@section('title', 'Manajemen Ubah Prodi')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-black">Manajemen Pindah Program Studi</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Gunakan fitur ini untuk memindahkan prodi mahasiswa dan otomatis menghitung selisih tagihan UKT.</p>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg relative">
            <strong class="font-bold">Berhasil!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg relative">
            <strong class="font-bold">Gagal!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="mb-6 flex items-center justify-between">
        <form action="{{ route('admin.ubah-prodi.index') }}" method="GET" class="w-full max-w-md flex space-x-2">
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}" 
                placeholder="Cari nama, ID reg, atau email..." 
                class="w-full rounded-md border border-gray-300 px-4 py-2 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-700 dark:text-black"
            >
            <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-black hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Cari
            </button>
            @if(request('search'))
                <a href="{{ route('admin.ubah-prodi.index') }}" class="rounded-md bg-gray-500 px-4 py-2 text-black hover:bg-gray-600 focus:outline-none">
                    Reset
                </a>
            @endif
        </form>

        <a href="{{ route('admin.ubah-prodi.export', request()->all()) }}" 
           class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-black hover:bg-emerald-700 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            Export Excel
        </a>
    </div>

    <div class="overflow-x-auto border rounded-lg border-gray-200 dark:border-gray-700">
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 text-gray-700 dark:bg-gray-700 dark:text-gray-200 uppercase font-semibold">
                <tr>
                    <th class="px-4 py-3 text-center w-12">No</th>
                    <th class="px-4 py-3">Nama Mahasiswa</th>
                    <th class="px-4 py-3">Email / Kontak</th>
                    <th class="px-4 py-3">Prodi Saat Ini</th>
                    <th class="px-4 py-3 text-center w-48">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($users as $index => $ereg)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition duration-150">
                        <td class="px-4 py-3 text-center">{{ $users->firstItem() + $index }}</td>
                        <td class="px-4 py-3">
                            <span class="font-bold text-gray-800 dark:text-black">{{ $ereg->nama ?? ($ereg->user->name ?? '-') }}</span><br>
                            <span class="text-xs text-gray-500">ID Reg: {{ $ereg->user_id }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span>{{ $ereg->gmail ?? ($ereg->user->email ?? '-') }}</span><br>
                            <span class="text-xs text-gray-500">{{ $ereg->phone ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $prodiSaatIni = $prodis->firstWhere('kode_prodi', $ereg->jurusan_lulusan);
                            @endphp
                            <span class="inline-flex rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                {{ $prodiSaatIni ? $prodiSaatIni->nama_prodi : ($ereg->jurusan_lulusan ?? 'Belum Ditentukan') }}
                            </span>
                            <br>
                            <span class="mt-1 inline-block text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                                Terbayar: Rp {{ number_format($ereg->nominal_dibayar, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center" x-data="{ modalOpen: false }">
                            
                            @php
                                $selisih = $ereg->tagihan_selisih;
                            @endphp

                            @if($selisih && $selisih->status === 'pending')
                                <div class="flex flex-col items-center justify-center space-y-2 border border-yellow-200 bg-yellow-50 p-2 rounded-lg dark:bg-yellow-900/20 dark:border-yellow-800">
                                    <span class="inline-flex rounded bg-yellow-100 px-2 py-0.5 text-[11px] font-semibold text-yellow-800 dark:bg-yellow-900/60 dark:text-yellow-300">
                                        Menunggu Pembayaran
                                    </span>
                                    
                                    <span class="text-xs font-mono font-bold text-blue-700 bg-blue-100 px-2 py-0.5 rounded border border-blue-200 dark:bg-blue-900/50 dark:text-blue-300 dark:border-blue-800">
                                        VA: {{ $selisih->virtual_account }}
                                    </span>
                                    
                                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200">
                                        Rp {{ number_format($selisih->amount, 0, ',', '.') }}
                                    </span>

                                    <span class="text-[10px] text-gray-500 dark:text-gray-400">
                                        Ke: {{ $selisih->nama_jurusan }}
                                    </span>

                                    {{-- FORM TOMBOL BARU: HAPUS / BATALKAN VA --}}
                                    <form action="{{ route('admin.ubah-prodi.batal-va', $selisih->id) }}" method="POST" class="w-full mt-1" onsubmit="return confirm('Apakah Anda yakin ingin menghapus / membatalkan tagihan VA selisih prodi ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full rounded bg-red-600 px-2 py-1 text-xs font-medium text-black hover:bg-red-700 focus:outline-none">
                                            Hapus VA
                                        </button>
                                    </form>
                                </div>
                            @elseif($selisih && in_array($selisih->status, ['paid', 'settlement', 'success']))
                                <div class="flex flex-col items-center justify-center space-y-2 border border-emerald-200 bg-emerald-50 p-2 rounded-lg dark:bg-emerald-900/20 dark:border-emerald-800 mb-2">
                                    <span class="inline-flex rounded bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300">
                                        Selisih Lunas
                                    </span>
                                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200">
                                        Rp {{ number_format($selisih->amount, 0, ',', '.') }}
                                    </span>
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400">
                                        Ke: {{ $selisih->nama_jurusan }}
                                    </span>
                                </div>
                                <button @click="modalOpen = true" class="rounded bg-yellow-500 px-3 py-1.5 text-sm font-medium text-black hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-400">
                                    Pindah Prodi
                                </button>
                            @else
                                <button @click="modalOpen = true" class="rounded bg-yellow-500 px-3 py-1.5 text-sm font-medium text-black hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-400">
                                    Pindah Prodi
                                </button>
                            @endif

                            <div 
                                x-show="modalOpen" 
                                    style="display: none;"
                                    class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-black/50 p-4"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100"
                                    x-transition:leave="transition ease-in duration-200"
                                    x-transition:leave-start="opacity-100"
                                    x-transition:leave-end="opacity-0"
                                >
                                    <div 
                                        @click.away="modalOpen = false"
                                        class="relative w-full max-w-md rounded-lg bg-white shadow-xl dark:bg-gray-800 text-left"
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                        x-transition:leave="transition ease-in duration-200"
                                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                    >
                                        <div class="flex items-center justify-between border-b p-4 dark:border-gray-700">
                                            <h3 class="text-lg font-bold text-gray-900 dark:text-black">
                                                Pindah Prodi: {{ $ereg->nama ?? $ereg->user->name }}
                                            </h3>
                                            <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-900 dark:hover:text-black">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </div>

                                        <form action="{{ route('admin.ubah-prodi.proses', $ereg->user_id) }}" method="POST">
                                            @csrf
                                            <div class="p-4 space-y-4">
                                                <div class="rounded bg-blue-50 p-3 text-sm text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                                    <p class="mb-2">Total UKT yang telah dibayar: <strong class="text-emerald-600 dark:text-emerald-400">Rp {{ number_format($ereg->nominal_dibayar, 0, ',', '.') }}</strong></p>
                                                    Tentukan Program Studi baru dan masukkan <strong>Nominal Selisih</strong> secara manual jika ada biaya tambahan. Jika nominal dikosongkan/nol, prodi akan langsung berubah.
                                                </div>

                                                <div>
                                                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Program Studi Baru <span class="text-red-500">*</span></label>
                                                    <select name="kode_prodi_baru" required class="w-full rounded-md border border-gray-300 p-2.5 text-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-700 dark:text-black">
                                                        <option value="">-- Pilih Prodi Baru --</option>
                                                        @foreach($prodis as $prodi)
                                                            <option value="{{ $prodi->kode_prodi }}" {{ $ereg->jurusan_lulusan == $prodi->kode_prodi ? 'disabled' : '' }}>
                                                                {{ $prodi->nama_prodi }} (Kode: {{ $prodi->kode_prodi }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div>
                                                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                        Nominal Tagihan Selisih (Rp) <span class="text-gray-400 font-normal">(Opsional)</span>
                                                    </label>
                                                    <input 
                                                        type="number" 
                                                        name="nominal_selisih" 
                                                        min="0" 
                                                        placeholder="Contoh: 500000" 
                                                        class="w-full rounded-md border border-gray-300 p-2.5 text-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-700 dark:text-black"
                                                    >
                                                    <p class="text-xs text-gray-500 mt-1 dark:text-gray-400">Kosongkan atau isi 0 jika tidak ada tagihan tambahan.</p>
                                                </div>
                                            </div>

                                            <div class="flex justify-end space-x-2 border-t p-4 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 rounded-b-lg">
                                                <button type="button" @click="modalOpen = false" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                                    Batal
                                                </button>
                                                <button type="submit" onclick="return confirm('Yakin ingin memindahkan prodi mahasiswa ini?')" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-black hover:bg-blue-700 focus:outline-none">
                                                    Process Perubahan
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                            Data pendaftar tidak ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>

</div>
@endsection