@extends('layouts.admin.app')

@section('title', 'Riwayat Penggunaan Promo')

@section('content')
<div class="rounded-lg bg-white p-6 shadow-theme-sm dark:bg-gray-dark">
    
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 pb-5 dark:border-gray-700">
        <div>
            <h2 class="text-title-md font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-ticket-alt text-brand-500"></i> Riwayat Penggunaan Kode Promo
            </h2>
            <p class="text-sm text-gray-500 mt-1">Memantau daftar mahasiswa yang mendaftar menggunakan potongan harga dari kode promo.</p>
        </div>
    </div>

    {{-- KOTAK FILTER --}}
    <div class="mb-6 rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
        <form action="{{ route('admin.promo.usage') }}" method="GET" class="flex flex-col sm:flex-row gap-4 items-end">
            <div class="w-full sm:w-1/3">
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Kode Promo</label>
                <select name="kode_promo" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Semua Kode Promo</option>
                    @foreach($promoCodes as $promo)
                        <option value="{{ $promo->code }}" {{ request('kode_promo') == $promo->code ? 'selected' : '' }}>
                            {{ $promo->code }} - {{ $promo->description }} (Sisa: {{ $promo->usage_limit - $promo->used_count }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-all">
                    <i class="fas fa-filter mr-2"></i> Filter Data
                </button>
                @if(request('kode_promo'))
                    <a href="{{ route('admin.promo.usage') }}" class="ml-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>
<h3 class="mb-3 text-sm font-bold uppercase tracking-wider text-gray-500">Ringkasan Keseluruhan</h3>
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pengguna Promo</p>
            <h4 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                {{ number_format($totalPengguna) }} <span class="text-sm font-normal text-gray-500">Orang</span>
            </h4>
        </div>
        
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Tagihan Asli</p>
            <h4 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                Rp {{ number_format($totalTagihanAsli, 0, ',', '.') }}
            </h4>
        </div>

        <div class="rounded-xl border border-red-100 bg-red-50/50 p-5 shadow-sm dark:border-red-900/20 dark:bg-red-900/10">
            <p class="text-sm font-medium text-red-600 dark:text-red-400">Total Potongan Diskon</p>
            <h4 class="mt-2 text-2xl font-bold text-red-700 dark:text-red-300">
                - Rp {{ number_format($totalDiskon, 0, ',', '.') }}
            </h4>
        </div>

        <div class="rounded-xl border border-green-100 bg-green-50 p-5 shadow-sm dark:border-green-900/20 dark:bg-green-900/10">
            <p class="text-sm font-medium text-green-600 dark:text-green-400">Total Tagihan Akhir</p>
            <h4 class="mt-2 text-2xl font-bold text-green-700 dark:text-green-300">
                Rp {{ number_format($totalTagihanAkhir, 0, ',', '.') }}
            </h4>
        </div>
    </div>

    {{-- KARTU RINGKASAN KHUSUS S2 --}}
    <h3 class="mb-3 text-sm font-bold uppercase tracking-wider text-gray-500">Khusus Pendaftar S2 (Magister)</h3>
    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-indigo-100 bg-indigo-50/50 p-5 shadow-sm dark:border-indigo-900/20 dark:bg-indigo-900/10">
            <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Total Pengguna (S2)</p>
            <h4 class="mt-2 text-2xl font-bold text-indigo-700 dark:text-indigo-300">
                {{ number_format($totalPenggunaS2) }} <span class="text-sm font-normal opacity-70">Orang</span>
            </h4>
        </div>

        <div class="rounded-xl border border-indigo-100 bg-indigo-50 p-5 shadow-sm dark:border-indigo-900/20 dark:bg-indigo-900/10">
            <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Pendapatan Akhir (S2)</p>
            <h4 class="mt-2 text-2xl font-bold text-indigo-700 dark:text-indigo-300">
                Rp {{ number_format($totalPendapatanS2, 0, ',', '.') }}
            </h4>
        </div>
    </div>

    {{-- TABEL DATA --}}
    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="min-w-full border-collapse bg-white text-sm dark:bg-gray-800">
            <thead class="bg-gray-100 dark:bg-gray-700">
                <tr>
                    <th class="border-b border-gray-200 p-4 text-left font-semibold text-gray-700 dark:border-gray-600 dark:text-gray-300">No</th>
                    <th class="border-b border-gray-200 p-4 text-left font-semibold text-gray-700 dark:border-gray-600 dark:text-gray-300">Nama Mahasiswa</th>
                    <th class="border-b border-gray-200 p-4 text-left font-semibold text-gray-700 dark:border-gray-600 dark:text-gray-300">Program Studi</th>
                    <th class="border-b border-gray-200 p-4 text-center font-semibold text-gray-700 dark:border-gray-600 dark:text-gray-300">Kode Promo</th>
                    <th class="border-b border-gray-200 p-4 text-right font-semibold text-gray-700 dark:border-gray-600 dark:text-gray-300">Diskon</th>
                    <th class="border-b border-gray-200 p-4 text-right font-semibold text-gray-700 dark:border-gray-600 dark:text-gray-300">Tagihan Akhir</th>
                    <th class="border-b border-gray-200 p-4 text-center font-semibold text-gray-700 dark:border-gray-600 dark:text-gray-300">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($penggunaPromo as $index => $user)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="p-4 text-gray-600 dark:text-gray-400">
                            {{ $penggunaPromo->firstItem() + $index }}
                        </td>
                        <td class="p-4">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $user->name }}</div>
                            <div class="text-xs text-gray-500">{{ $user->email }}</div>
                        </td>
                        <td class="p-4">
                            <div class="text-gray-800 dark:text-gray-300 font-medium">
                                {{ $user->nama_prodi ?? 'Belum Pilih/Kosong' }}
                            </div>
                            <div class="text-[11px] font-bold text-brand-500 mt-1">
                                JENJANG: {{ $user->jenjang ?? 'TIDAK TERDETEKSI' }}
                            </div>
                        </td>
                        <td class="p-4 text-center">
                            <span class="inline-block rounded bg-indigo-100 px-2.5 py-1 text-xs font-bold text-indigo-800 border border-indigo-200">
                                {{ $user->promo_code_used }}
                            </span>
                            <div class="text-[10px] text-gray-500 mt-1">{{ $user->asal_sekolah ?? 'Promo Umum' }}</div>
                        </td>
                        <td class="p-4 text-right">
                            <span class="text-red-600 font-medium">
                                - Rp {{ number_format($user->discount_amount, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="p-4 text-right font-bold text-gray-900 dark:text-white">
                            Rp {{ number_format($user->final_amount, 0, ',', '.') }}
                        </td>
                        <td class="p-4 text-center">
                            @if(in_array(strtolower($user->va_status), ['paid', 'settlement', 'success', 'lunas']))
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                    <span class="mr-1 h-1.5 w-1.5 rounded-full bg-green-600"></span> LUNAS
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                                    <span class="mr-1 h-1.5 w-1.5 rounded-full bg-yellow-600"></span> PENDING
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-gray-500 dark:text-gray-400">
                            Belum ada data mahasiswa yang menggunakan promo ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    <div class="mt-4">
        {{ $penggunaPromo->links() }}
    </div>

</div>
@endsection