{{-- FILE: resources/views/mahasiswa/pembayaran/partials/mobile.blade.php --}}

@php
    $user = Auth::user();
    $dataHarga    = $dataHarga ?? [];
    $hargaTahun   = $dataHarga['tahun'] ?? 0;
    $hargaCicilan = $dataHarga['cicilan'] ?? 0;
    $hargaLunas   = $dataHarga['lunas'] ?? 0; 
@endphp


<div class="w-full"
     x-data="paymentForm({{ $hargaTahun }}, {{ $hargaCicilan }}, {{ $hargaLunas }})"
     x-init="init()">

    {{-- ALERT ERROR --}}
    @if(session('error'))
        <div class="mb-5 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl flex items-start gap-3" role="alert">
            <i class="fas fa-exclamation-circle text-red-500 mt-0.5 flex-shrink-0"></i>
            <div>
                <p class="font-bold text-red-700 text-sm">Gagal Memproses</p>
                <p class="text-xs text-red-600 mt-0.5">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('pembayaran.createVA') }}">
        @csrf
        <input type="hidden" name="tenor_pembayaran" :value="tenorPembayaran">

        {{-- 1. INFO MAHASISWA --}}
        <div class="flex items-center gap-3 mb-6 pb-5 border-b border-slate-200/70">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white flex items-center justify-center font-extrabold text-lg shadow flex-shrink-0">
                {{ substr($user?->name ?? 'U', 0, 1) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Mahasiswa</p>
                <p class="text-sm font-extrabold text-slate-800 truncate">{{ $user?->name ?? 'Nama Tidak Ditemukan' }}</p>
                <p class="text-[11px] text-slate-400 font-mono truncate">{{ $user?->username ?? $user?->email ?? '-' }}</p>
            </div>
            <span class="flex-shrink-0 text-[10px] font-bold text-blue-700 bg-blue-100 border border-blue-200 px-2.5 py-1 rounded-xl flex items-center gap-1.5">
                <i class="fas fa-graduation-cap text-[9px]"></i>
                {{ $jurusanDiterima ?? 'Umum' }}
            </span>
        </div>

        {{-- 2. LABEL --}}
        <p class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest mb-3">
            Pilih Opsi Pembayaran
        </p>

        {{-- 3. CARD TOGGLE PILIHAN --}}
        <div class="grid {{ $hargaLunas > 0 ? 'grid-cols-1' : 'grid-cols-2' }} gap-3 mb-5">

            @if($hargaLunas > 0)
                {{-- [OPSI BEASISWA] LUNAS --}}
                <button type="button"
                        @click="updateTagihanUkt('lunas')"
                        :class="tenorPembayaran === 'lunas'
                            ? 'border-indigo-500 bg-indigo-600 text-white shadow-lg shadow-indigo-500/30 scale-[1.02]'
                            : 'border-slate-200 bg-white text-slate-700 hover:border-indigo-300'"
                        class="relative p-4 rounded-2xl border-2 text-left transition-all duration-200 flex flex-col gap-1 focus:outline-none overflow-hidden">

                    <span :class="tenorPembayaran === 'lunas' ? 'bg-white/20 text-white' : 'bg-yellow-100 text-yellow-700'"
                          class="absolute top-2 right-2 text-[8px] font-black px-1.5 py-0.5 rounded-full uppercase transition-colors whitespace-nowrap animate-pulse">
                        JALUR VIP
                    </span>

                    <i class="fas fa-check-circle absolute bottom-2.5 right-2.5 text-sm transition-all duration-200"
                       :class="tenorPembayaran === 'lunas' ? 'text-white/70 opacity-100' : 'opacity-0'"></i>

                    <div class="w-9 h-9 rounded-xl flex items-center justify-center mb-1 transition-colors"
                         :class="tenorPembayaran === 'lunas' ? 'bg-white/20' : 'bg-indigo-100'">
                        <i class="fas fa-medal text-base"
                           :class="tenorPembayaran === 'lunas' ? 'text-white' : 'text-indigo-600'"></i>
                    </div>

                    <span class="font-extrabold text-sm leading-tight">Lunas (Beasiswa)</span>
                    <span class="font-black text-sm leading-snug" x-text="'Rp ' + formatRupiah(prices.lunas)"></span>
                    <span class="text-[10px] font-medium leading-tight mt-0.5"
                          :class="tenorPembayaran === 'lunas' ? 'text-indigo-200' : 'text-slate-400'">
                        Pembayaran 1 Tahun
                    </span>
                </button>
            @else
                {{-- [OPSI 1] 1 TAHUN (NORMAL) --}}
                <button type="button"
                        @click="updateTagihanUkt('tahun')"
                        :class="tenorPembayaran === 'tahun'
                            ? 'border-indigo-500 bg-indigo-600 text-white shadow-lg shadow-indigo-500/30 scale-[1.02]'
                            : 'border-slate-200 bg-white text-slate-700 hover:border-indigo-300'"
                        class="relative p-4 rounded-2xl border-2 text-left transition-all duration-200 flex flex-col gap-1 focus:outline-none overflow-hidden">

                    <span :class="tenorPembayaran === 'tahun' ? 'bg-white/20 text-white' : 'bg-green-100 text-green-700'"
                          class="absolute top-2 right-2 text-[8px] font-black px-1.5 py-0.5 rounded-full uppercase transition-colors whitespace-nowrap">
                        POPULER
                    </span>

                    <i class="fas fa-check-circle absolute bottom-2.5 right-2.5 text-sm transition-all duration-200"
                       :class="tenorPembayaran === 'tahun' ? 'text-white/70 opacity-100' : 'opacity-0'"></i>

                    <div class="w-9 h-9 rounded-xl flex items-center justify-center mb-1 transition-colors"
                         :class="tenorPembayaran === 'tahun' ? 'bg-white/20' : 'bg-indigo-100'">
                        <i class="fas fa-calendar-check text-base"
                           :class="tenorPembayaran === 'tahun' ? 'text-white' : 'text-indigo-600'"></i>
                    </div>

                    <span class="font-extrabold text-sm leading-tight">1 Tahun</span>
                    <span class="font-black text-sm leading-snug" x-text="'Rp ' + formatRupiah(prices.tahun)"></span>
                    <span class="text-[10px] font-medium leading-tight mt-0.5"
                          :class="tenorPembayaran === 'tahun' ? 'text-indigo-200' : 'text-slate-400'">
                        2 Semester Sekaligus
                    </span>
                </button>

                {{-- OPSI: CICILAN (NORMAL) --}}
                <button type="button"
                        @click="updateTagihanUkt('cicilan')"
                        :class="tenorPembayaran === 'cicilan'
                            ? 'border-green-500 bg-green-600 text-white shadow-lg shadow-green-500/30 scale-[1.02]'
                            : 'border-slate-200 bg-white text-slate-700 hover:border-green-300'"
                        class="relative p-4 rounded-2xl border-2 text-left transition-all duration-200 flex flex-col gap-1 focus:outline-none overflow-hidden">

                    <span :class="tenorPembayaran === 'cicilan' ? 'bg-white/20 text-white' : 'bg-orange-100 text-orange-600'"
                          class="absolute top-2 right-2 text-[8px] font-black px-1.5 py-0.5 rounded-full uppercase transition-colors whitespace-nowrap">
                        HEMAT
                    </span>

                    <i class="fas fa-check-circle absolute bottom-2.5 right-2.5 text-sm transition-all duration-200"
                       :class="tenorPembayaran === 'cicilan' ? 'text-white/70 opacity-100' : 'opacity-0'"></i>

                    <div class="w-9 h-9 rounded-xl flex items-center justify-center mb-1 transition-colors"
                         :class="tenorPembayaran === 'cicilan' ? 'bg-white/20' : 'bg-green-100'">
                        <i class="fas fa-hand-holding-usd text-base"
                           :class="tenorPembayaran === 'cicilan' ? 'text-white' : 'text-green-600'"></i>
                    </div>

                    <span class="font-extrabold text-sm leading-tight">Cicilan</span>
                    <span class="font-black text-sm leading-snug" x-text="'Rp ' + formatRupiah(prices.cicilan)"></span>
                    <span class="text-[10px] font-medium leading-tight mt-0.5"
                          :class="tenorPembayaran === 'cicilan' ? 'text-green-200' : 'text-slate-400'">
                        Angsuran Per Semester
                    </span>
                </button>
            @endif

        </div>

        {{-- 4. RINCIAN HARGA --}}
        <div class="bg-slate-50 border border-slate-200/70 rounded-2xl px-5 py-4 mb-5">
            <div class="flex justify-between items-center text-xs text-slate-500 mb-3">
                <span>Biaya UKT
                    (<span x-text="tenorLabel" class="font-bold text-slate-700 capitalize"></span>)
                </span>
                <span class="font-mono font-semibold text-slate-700">
                    Rp <span x-text="formatRupiah(tagihanUkt)"></span>
                </span>
            </div>
            <div class="border-t border-dashed border-slate-300 mb-3"></div>
            <div class="flex justify-between items-center">
                <span class="text-xs font-extrabold text-slate-500 uppercase tracking-widest">Total Bayar</span>
                <span class="text-2xl font-black text-blue-700">
                    Rp <span x-text="formatRupiah(tagihanUkt)"></span>
                </span>
            </div>
        </div>

        {{-- 5. TOMBOL SUBMIT --}}
        <button type="submit"
                class="w-full bg-gradient-to-r from-blue-600 to-indigo-700 text-white font-bold py-4 rounded-xl
                       shadow-lg hover:from-blue-700 hover:to-indigo-800 hover:shadow-blue-500/40
                       active:scale-[0.98] transition-all flex items-center justify-center gap-2 text-sm">
            <span>Lanjutkan Pembayaran</span>
            <i class="fas fa-arrow-right"></i>
        </button>

    </form>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('paymentForm', (hargaTahun, hargaCicilan, hargaLunas) => ({
            prices: {
                tahun:   parseInt(hargaTahun)   || 0,
                cicilan: parseInt(hargaCicilan) || 0,
                lunas:   parseInt(hargaLunas)   || 0 // Tambahan beasiswa
            },
            tenorPembayaran: 'cicilan',
            tagihanUkt: 0,

            get tenorLabel() {
                if (this.tenorPembayaran === 'tahun') return '1 Tahun';
                if (this.tenorPembayaran === 'lunas') return 'Lunas (Beasiswa)';
                return 'Cicilan Awal';
            },

            formatRupiah(angka) {
                if (angka == null || isNaN(parseInt(angka))) return '0';
                return parseInt(angka).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            },

            updateTagihanUkt(pilihan) {
                this.tenorPembayaran = pilihan;
                if (pilihan === 'tahun') this.tagihanUkt = this.prices.tahun;
                else if (pilihan === 'lunas') this.tagihanUkt = this.prices.lunas;
                else this.tagihanUkt = this.prices.cicilan;
            },

            init() {
                // Cek apakah dia anak beasiswa
                if (this.prices.lunas > 0) {
                    this.updateTagihanUkt('lunas');
                } else {
                    // Anak Normal, default ke cicilan
                    this.updateTagihanUkt('cicilan');
                }
            },
        }));
    });
</script>