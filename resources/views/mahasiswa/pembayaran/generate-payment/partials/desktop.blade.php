{{-- FILE: resources/views/mahasiswa/pembayaran/partials/desktop.blade.php --}}

@php
    $user = Auth::user(); 
    $dataHarga = $dataHarga ?? [];
    
    // Hapus variabel harga semester, sisakan tahun dan cicilan
    $hargaTahun    = $dataHarga['tahun'] ?? 0;
    $hargaCicilan  = $dataHarga['cicilan'] ?? 0;
    $hargaLunas    = $dataHarga['lunas'] ?? 0;
@endphp

<div class="w-full max-w-5xl mx-auto my-8 px-4" 
     x-data="paymentForm(
          {{ $hargaTahun }}, 
          {{ $hargaCicilan }}, 
          {{ $hargaLunas }}
     )"
     x-init="init()">

    {{-- ALERT ERROR --}}
    @if(session('error'))
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm flex items-start gap-3" role="alert">
            <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
            <div>
                <p class="font-bold text-red-700">Gagal Memproses</p>
                <p class="text-sm text-red-600">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    {{-- HEADER PAGE --}}
    <div class="text-center mb-8">
        <h1 class="text-3xl font-extrabold text-white drop-shadow-md" style="text-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            Buat Pembayaran Baru
        </h1>
        <p class="text-blue-100 text-sm mt-2 font-medium">
            Pilih skema pembayaran UKT yang sesuai dengan kebutuhan Anda.
        </p>
    </div>

    {{-- MAIN CARD --}}
    <div class="bg-white rounded-3xl shadow-2xl border border-white/50 overflow-hidden relative">
        
        <form method="POST" action="{{ route('pembayaran.createVA') }}">
            @csrf

            {{-- HEADER CARD: DATA MAHASISWA --}}
            <div class="bg-gray-50 border-b border-gray-100 p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-lg shadow-md">
                            {{ substr($user?->name ?? 'U', 0, 1) }}
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Mahasiswa</p>
                            <h2 class="text-lg font-bold text-gray-800 leading-tight">{{ $user?->name ?? 'Nama Tidak Ditemukan' }}</h2>
                            <p class="text-sm text-gray-500 font-mono">{{ $user?->username ?? $user?->email ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="bg-blue-100 text-blue-700 px-4 py-2 rounded-xl text-sm font-bold border border-blue-200 shadow-sm flex items-center">
                        <i class="fas fa-graduation-cap mr-2"></i>
                        {{ $jurusanDiterima ?? 'Umum' }}
                    </div>
                </div>
            </div>

            {{-- CONTENT CARD: OPSI PEMBAYARAN --}}
            <div class="p-6 sm:p-8">
                <h3 class="text-gray-800 font-bold text-lg mb-4 flex items-center">
                    <span class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mr-3">
                        <i class="fas fa-list-ul"></i>
                    </span>
                    Pilih Opsi Pembayaran
                </h3>

                {{-- GRID OPTION (UBAH JADI 2 KOLOM) --}}
               <div class="grid grid-cols-1 {{ $hargaLunas > 0 ? 'max-w-md mx-auto' : 'sm:grid-cols-2' }} gap-6 mb-8">
                
                    @if($hargaLunas > 0)
                        {{-- [OPSI BEASISWA] LUNAS --}}
                        <label class="cursor-pointer relative group">
                            <input type="radio" name="tenor_pembayaran" value="lunas" class="peer sr-only" @change="updateTagihanUkt('lunas')">
                            
                            <div class="h-full p-6 rounded-2xl border-2 border-indigo-500 bg-indigo-50 shadow-md text-center flex flex-col items-center justify-center gap-3 relative overflow-hidden">
                                
                                <div class="absolute top-0 right-0 bg-yellow-500 text-white text-[10px] font-bold px-3 py-1 rounded-bl-xl shadow-sm z-10 animate-pulse">
                                    JALUR VIP
                                </div>
                        
                                <div class="w-14 h-14 rounded-full bg-indigo-200 text-indigo-700 flex items-center justify-center text-2xl mb-1">
                                    <i class="fas fa-medal"></i>
                                </div>
                                
                                <div>
                                    <span class="block font-bold text-gray-800 text-xl">Lunas (Beasiswa UBP)</span>
                                    <span class="text-sm text-gray-500 block mt-1 font-medium">Pembayaran 1 Tahun</span>
                                </div>
                        
                                <div class="absolute top-4 left-4 text-indigo-600">
                                    <i class="fas fa-check-circle text-2xl"></i>
                                </div>
                            </div>
                        </label>
                    @else
                        {{-- [OPSI 1] 1 TAHUN (NORMAL) --}}
                        <label class="cursor-pointer relative group">
                            <input type="radio" name="tenor_pembayaran" value="tahun" class="peer sr-only" @change="updateTagihanUkt('tahun')">
                            
                            <div class="h-full p-6 rounded-2xl border-2 transition-all duration-200 text-center flex flex-col items-center justify-center gap-3
                                        peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:shadow-md
                                        border-gray-200 hover:border-indigo-300 hover:bg-gray-50 bg-white relative overflow-hidden">
                                
                                <div class="absolute top-0 right-0 bg-green-500 text-white text-[10px] font-bold px-3 py-1 rounded-bl-xl shadow-sm z-10">
                                    HEMAT BANYAK
                                </div>
                        
                                <div class="w-14 h-14 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-2xl mb-1 group-hover:scale-110 transition-transform">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                
                                <div>
                                    <span class="block font-bold text-gray-800 text-xl">1 Tahun</span>
                                    <span class="text-sm text-gray-500 block mt-1 font-medium">Langsung 2 Semester</span>
                                </div>
                        
                                <div class="absolute top-4 left-4 opacity-0 peer-checked:opacity-100 text-indigo-600 transition-opacity">
                                    <i class="fas fa-check-circle text-2xl"></i>
                                </div>
                            </div>
                        </label>

                        {{-- [OPSI 2] CICILAN (NORMAL) --}}
                        <label class="cursor-pointer relative group">
                            <input type="radio" name="tenor_pembayaran" value="cicilan" id="tenor_cicilan" class="peer sr-only" @change="updateTagihanUkt('cicilan')">
                            
                            <div class="h-full p-6 rounded-2xl border-2 transition-all duration-200 text-center flex flex-col items-center justify-center gap-3
                                        peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:shadow-md
                                        border-gray-200 hover:border-green-300 hover:bg-gray-50 bg-white relative overflow-hidden">
                                
                                <div class="absolute top-0 right-0 bg-green-600 text-white text-[10px] px-3 py-1 rounded-bl-xl font-bold">
                                    POPULER
                                </div>
        
                                <div class="w-14 h-14 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-2xl mb-1 group-hover:scale-110 transition-transform">
                                    <i class="fas fa-hand-holding-usd"></i>
                                </div>
                                
                                <div>
                                    <span class="block font-bold text-gray-800 text-xl">Cicilan Awal</span>
                                    <span class="text-sm text-gray-500 block mt-1">Angsuran Lebih Ringan</span>
                                </div>
        
                                <div class="absolute top-4 left-4 opacity-0 peer-checked:opacity-100 text-green-600 transition-opacity">
                                    <i class="fas fa-check-circle text-2xl"></i>
                                </div>
                            </div>
                        </label>
                    @endif
                </div>

                {{-- RINCIAN HARGA --}}
                <div class="bg-gray-50 rounded-xl p-6 border border-gray-200 transition-all duration-300">
                    <div class="flex justify-between items-center mb-3 text-sm text-gray-600">
                        <span>Biaya UKT (<span x-text="tenorLabel" class="font-semibold text-gray-800 capitalize"></span>)</span>
                        <span class="font-mono font-medium">Rp <span x-text="formatRupiah(tagihanUkt)"></span></span>
                    </div>
                    <div class="my-4 border-t border-dashed border-gray-300"></div>

                    <div class="flex justify-between items-center">
                        <span class="font-bold text-gray-800 text-lg">Total Bayar</span>
                        <div class="text-right">
                            <span class="block text-3xl font-extrabold text-blue-600">
                                Rp <span x-text="formatRupiah(tagihanUkt)"></span>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- TOMBOL SUBMIT --}}
                <button type="submit" class="w-full mt-8 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold py-4 rounded-xl shadow-lg hover:from-blue-700 hover:to-blue-800 hover:shadow-xl transition-all transform hover:-translate-y-1 flex items-center justify-center gap-2 text-lg">
                    <span>Lanjutkan Pembayaran</span>
                    <i class="fas fa-arrow-right"></i>
                </button>

            </div>
        </form>
    </div>

</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('paymentForm', (hargaTahun, hargaCicilan, hargaLunas) => ({
            prices: {
                tahun:    parseInt(hargaTahun) || 0,
                cicilan:  parseInt(hargaCicilan) || 0,
                lunas:    parseInt(hargaLunas) || 0 // Tambahan beasiswa
            },
            tenorPembayaran: 'cicilan',
            tagihanUkt: 0,

            get tenorLabel() {
                if(this.tenorPembayaran === 'tahun') return '1 Tahun';
                if(this.tenorPembayaran === 'lunas') return 'Lunas (Beasiswa)';
                return 'Cicilan Awal';
            },

            formatRupiah(angka) {
                if (angka == null || isNaN(parseInt(angka))) return '0';
                return parseInt(angka).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
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
                    this.tenorPembayaran = 'lunas';
                    this.tagihanUkt = this.prices.lunas;
                    setTimeout(() => {
                        const el = document.querySelector('input[value="lunas"]');
                        if(el) el.checked = true;
                    }, 100);
                } else {
                    // Anak Normal
                    this.tenorPembayaran = 'cicilan';
                    this.tagihanUkt = this.prices.cicilan;
                    setTimeout(() => {
                        const el = document.getElementById('tenor_cicilan');
                        if(el) el.checked = true;
                    }, 100);
                }
            }
        }));
    });
</script>