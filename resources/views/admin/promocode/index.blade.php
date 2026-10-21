@extends('layouts.admin.app')

@section('title', 'Kode Promo')

@section('content')
<div class="mx-auto max-w-6xl">
    <!-- Form Buat Promo -->
    <div class="rounded-2xl bg-white dark:bg-gray-900 p-6 shadow-sm mb-6">
        <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-gray-100">Buat Kode Promo</h2>
        <p class="mb-6 text-gray-500 dark:text-gray-400 text-sm">
            Gunakan halaman ini untuk membuat kode promo untuk CAMABA.
        </p>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <ul class="text-red-600 dark:text-red-400 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.promocode.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Deskripsi *</label>
                    <input type="text" name="description" placeholder="Contoh: Promo Registrasi 2025" required
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-gray-800 dark:text-gray-100 p-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Diskon (%) *</label>
                    <input type="number" name="discount" required min="1" max="100" value="10"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-gray-800 dark:text-gray-100 p-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

<!-- Tambahkan di head -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<div>
    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Tanggal Kadaluarsa</label>
    <input type="text" 
           name="expired_at" 
           id="expiryDate"
           placeholder="Pilih tanggal..."
           class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-gray-800 dark:text-gray-100 p-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
</div>

<script>
    flatpickr("#expiryDate", {
        dateFormat: "Y-m-d",
        minDate: "today",
        locale: "id", // Opsional: bahasa Indonesia
        disableMobile: true, // Untuk UI yang lebih konsisten
    });
</script>
                <div>
                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Batas Penggunaan</label>
                    <input type="number" name="usage_limit" placeholder="Kosongkan untuk unlimited"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-gray-800 dark:text-gray-100 p-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 dark:text-black font-semibold py-2 px-6 rounded-lg transition duration-200">
                🔑 Buat Kode Promo
            </button>
        </form>
    </div>

    <!-- Daftar Kode Promo -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Daftar Kode Promo</h3>
        
        <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-3">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Daftar Kode Promo</h3>
        
        <!-- Tombol Export Excel -->
        <a href="{{ route('admin.promocode.export') }}" 
           class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Export ke Excel
        </a>
    </div>
        @if (session('success'))
            <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-green-600 dark:text-green-400 font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-red-600 dark:text-red-400 font-medium">
                {{ session('error') }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-300">
                <thead class="border-b border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
    <tr>
        <th class="py-3 px-4">Kode</th>
        <th class="py-3 px-4">Deskripsi</th>
        <th class="py-3 px-4">Diskon</th>
        <th class="py-3 px-4">Status</th>
        <th class="py-3 px-4" style="text-align: center;">Total Pakai</th>
        <th class="py-3 px-4" style="text-align: center;">Sudah Bayar</th>
        <th class="py-3 px-4" style="text-align: center;">Belum Bayar</th>
        <th class="py-3 px-4">Kuota</th>
        <th class="py-3 px-4">Kadaluarsa</th>
        <th class="py-3 px-4">Aksi</th>
    </tr>
</thead>
<tbody>
    @forelse($promos as $promo)
        <tr class="border-b border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50">
    <td class="py-3 px-4 font-mono font-bold">{{ $promo->code }}</td>
    <td class="py-3 px-4">{{ $promo->description ?? '-' }}</td>
    <td class="py-3 px-4">{{ $promo->discount }}%</td>
    <td class="py-3 px-4">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $promo->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' }}">
            {{ $promo->is_active ? 'Aktif' : 'Nonaktif' }}
        </span>
    </td>
    
    {{-- BARU: Menampilkan data real total pakai dari subquery controller --}}
    <td class="py-3 px-4 text-center font-bold text-gray-900 dark:text-gray-100">
        {{ $promo->total_count }} User
    </td>

    {{-- BARU: Menampilkan data real sudah bayar --}}
    <td class="py-3 px-4 text-center">
        <span class="px-2 py-1 text-xs font-bold rounded-md bg-green-50 text-green-700 border border-green-200 dark:bg-green-950/30 dark:text-green-400 dark:border-green-900">
            {{ $promo->paid_count }} User
        </span>
    </td>

    {{-- BARU: Menampilkan data real belum bayar --}}
    <td class="py-3 px-4 text-center">
        <span class="px-2 py-1 text-xs font-bold rounded-md bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/30 dark:text-amber-400 dark:border-amber-900">
            {{ $promo->unpaid_count }} User
        </span>
    </td>

    {{-- Sisa kolom batas kuota, expired, dan tombol aksi silakan biarkan tetap sama --}}
    <td class="py-3 px-4">
        @if($promo->usage_limit)
            Max: {{ $promo->usage_limit }}
        @else
            ∞
        @endif
    </td>
            
            <td class="py-3 px-4">
                @if($promo->expired_at)
                    {{ $promo->expired_at->format('d M Y') }}
                    @if($promo->expired_at->isPast())
                        <span class="text-red-500 text-xs block">(Expired)</span>
                    @endif
                @else
                    -
                @endif
            </td>
            <td class="py-3 px-4">
                <div class="flex items-center space-x-2">
                    <a href="{{ route('admin.promo.usage', ['kode_promo' => $promo->code]) }}" 
                       class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium text-sm flex items-center transition-colors" 
                       title="Lihat riwayat pengguna promo ini">
                        Lihat
                    </a>
                    <span class="text-gray-300 dark:text-gray-600">|</span>
                    <form action="{{ route('admin.promocode.toggleStatus', $promo->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="text-blue-500 hover:text-blue-600 font-medium text-sm transition-colors">
                            {{ $promo->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </form>
                    <span class="text-gray-300 dark:text-gray-600">|</span>
                    <form action="{{ route('admin.promocode.destroy', $promo->id) }}" method="POST"
                          onsubmit="return confirm('Hapus kode promo {{ $promo->code }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-600 font-medium text-sm transition-colors">
                            Hapus
                        </button>
                    </form>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="10" class="text-center py-8 text-gray-500 dark:text-gray-400">
                <div class="flex flex-col items-center">
                    Belum ada kode promo
                </div>
            </td>
        </tr>
    @endforelse
</tbody>
<tfoot class="border-t-2 border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-800/80 font-bold text-gray-900 dark:text-gray-100">
                    <tr>
                        <td colspan="4" class="py-4 px-4 text-left tracking-wide text-xs uppercase" style="color: var(--text-muted);">
                            📊 Total Akumulasi Keseluruhan
                        </td>
                        
                        {{-- GRAND TOTAL PAKAI --}}
                        <td class="py-4 px-4 text-center text-base" style="font-family: monospace;">
                            {{ $grandTotalPakai }} User
                        </td>
                        
                        {{-- GRAND TOTAL SUDAH BAYAR --}}
                        <td class="py-4 px-4 text-center">
                            <span class="px-3 py-1 text-xs font-black rounded-lg bg-green-600 text-black shadow-sm">
                                🎉 {{ $grandTotalPaid }} User
                            </span>
                        </td>
                        
                        {{-- GRAND TOTAL BELUM BAYAR --}}
                        <td class="py-4 px-4 text-center">
                            <span class="px-3 py-1 text-xs font-black rounded-lg bg-amber-500 text-black shadow-sm">
                                ⏳ {{ $grandTotalUnpaid }} User
                            </span>
                        </td>
                        
                        {{-- Kolom sisa dikosongkan agar layout tidak bergeser --}}
                        <td colspan="3" class="py-4 px-4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

 <script src="{{ asset('assets/js/bundle.js') }}"></script>