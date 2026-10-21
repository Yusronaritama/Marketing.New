@extends('layouts.mahasiswa.pembayaran-layout')
@section('title', 'Buat Pembayaran')

@section('content')
{{-- ======================================================================= --}}
{{-- 1. PHP SETUP VARIABLES                                                  --}}
{{-- ======================================================================= --}}
@php
    $user = Auth::user(); 
    $dataHarga = $dataHarga ?? [];
    
    // Hapus variabel harga semester, sisakan tahun dan cicilan
    $hargaTahun    = $dataHarga['tahun'] ?? 0;
    $hargaCicilan  = $dataHarga['cicilan'] ?? 0;
    $hargaSemester = $dataHarga['semester'] ?? 0;
@endphp

<div class="w-full max-w-5xl mx-auto my-8 px-4" 
     x-data="paymentForm(
          {{ $hargaTahun }}, 
          {{ $hargaCicilan }}
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
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                
                    {{-- [OPSI 1] 1 TAHUN --}}
					<label class="cursor-pointer relative group" @click="setTenor('tahun')">
					    <input type="radio" name="tenor_pembayaran" value="tahun" class="peer sr-only" x-model="tenorPembayaran">
					    
					    <div class="h-full p-6 rounded-2xl border-2 transition-all duration-200 text-center flex flex-col items-center justify-center gap-3
					                peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:shadow-md
					                border-gray-200 hover:border-indigo-300 hover:bg-gray-50 bg-white relative overflow-hidden">
					        
					        <div class="absolute top-0 right-0 bg-green-500 text-white text-[10px] font-bold px-3 py-1 rounded-bl-xl shadow-sm z-10 animate-pulse">
					            HEMAT RP 1 JUTA!
					        </div>
					
					        <div class="w-14 h-14 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-2xl mb-1 group-hover:scale-110 transition-transform">
					            <i class="fas fa-calendar-check"></i>
					        </div>
					        
					        <div class="w-full">
					            <span class="block font-bold text-gray-800 text-xl">1 Tahun</span>
					            <span class="text-sm text-gray-500 block mt-1 font-medium mb-3">Langsung 2 Semester</span>
					            
					            <div class="bg-white/60 border border-indigo-100 rounded-xl p-3 shadow-[inset_0_2px_4px_rgba(0,0,0,0.02)] group-hover:bg-white transition-colors">
					                <span class="block text-[11px] font-bold text-red-500 uppercase tracking-widest mb-0.5">Harga Normal</span>
					                <span class="block text-sm text-gray-400 line-through decoration-red-500 decoration-2 font-mono">
					                    Rp {{ number_format($hargaSemester * 2, 0, ',', '.') }}
					                </span>
					                <div class="h-px w-full bg-indigo-50 my-2"></div>
					                <span class="block text-[11px] font-bold text-indigo-500 uppercase tracking-widest mb-0.5">Harga Diskon</span>
					                <span class="block text-xl font-black text-indigo-700 font-mono leading-none">
					                    Rp {{ number_format($hargaTahun, 0, ',', '.') }}
					                </span>
					            </div>
					        </div>
					
					        <div class="absolute top-4 left-4 opacity-0 peer-checked:opacity-100 text-indigo-600 transition-opacity">
					            <i class="fas fa-check-circle text-2xl"></i>
					        </div>
					    </div>
					</label>
					
					{{-- [OPSI 2] CICILAN --}}
					<label class="cursor-pointer relative group" @click="setTenor('cicilan')">
					    <input type="radio" name="tenor_pembayaran" value="cicilan" class="peer sr-only" x-model="tenorPembayaran">
					    
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
                            
                            {{-- BADGE HEMAT --}}
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

                </div>

                {{-- RINCIAN HARGA --}}
				<div class="bg-gray-50 rounded-xl p-6 border border-gray-200 transition-all duration-300 relative overflow-hidden">
				    
				    {{-- EFEK GLOWING KHUSUS DISKON --}}
				    <div x-show="tenorPembayaran === 'tahun'" x-cloak class="absolute -top-10 -right-10 w-32 h-32 bg-green-300 rounded-full blur-3xl opacity-30 animate-pulse pointer-events-none"></div>
				
				    {{-- BARIS HARGA NORMAL (DICORET) --}}
				    <div x-show="tenorPembayaran === 'tahun'" x-cloak class="flex justify-between items-center mb-1 text-sm text-gray-500">
				        <span>Harga Normal (2 Semester)</span>
				        <span class="font-mono font-medium line-through decoration-red-500 decoration-2">
				            Rp {{ number_format($hargaSemester * 2, 0, ',', '.') }}
				        </span>
				    </div>
				
				    {{-- BARIS POTONGAN DISKON --}}
				    <div x-show="tenorPembayaran === 'tahun'" x-cloak class="flex justify-between items-center mb-3 text-sm text-green-600">
				        <span class="font-bold flex items-center gap-1"><i class="fas fa-tags"></i> Potongan Harga</span>
				        <span class="font-mono font-bold">- Rp 1.000.000</span>
				    </div>
				
				    {{-- BIAYA YANG HARUS DIBAYARKAN --}}
				    <div class="flex justify-between items-center mb-3 text-sm text-gray-600">
				        <span>Biaya UKT (<span x-text="tenorLabel" class="font-semibold text-gray-800 capitalize"></span>)</span>
				        <span class="font-mono font-bold text-gray-800">Rp <span x-text="formatRupiah(tagihanUkt)"></span></span>
				    </div>
				
				    <div class="my-4 border-t border-dashed border-gray-300"></div>
				
				    <div class="flex justify-between items-center">
				        <span class="font-bold text-gray-800 text-lg">Total Bayar</span>
				        <div class="text-right flex flex-col items-end">
				            <span class="block text-3xl font-extrabold text-blue-600 leading-none">
				                Rp <span x-text="formatRupiah(tagihanUkt)"></span>
				            </span>
				            
				            {{-- TEXT SUBLIMINAL MARKETING DI BAWAH TOTAL --}}
				            <div x-show="tenorPembayaran === 'tahun'" x-cloak class="mt-2">
				                <span class="bg-green-100 border border-green-200 text-green-700 text-[10px] font-black px-2 py-1 rounded shadow-sm uppercase tracking-wider">
				                    🎉 Kamu Hemat Rp 1 Juta!
				                </span>
				            </div>
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

{{-- ======================================================================= --}}
{{-- ALPINE JS LOGIC                                                         --}}
{{-- ======================================================================= --}}
<script>
    document.addEventListener('alpine:init', () => {
        // Hapus parameter hargaSemester dari fungsi
        Alpine.data('paymentForm', (hargaTahun, hargaCicilan) => ({
            prices: {
                tahun:    parseInt(hargaTahun) || 0,
                cicilan:  parseInt(hargaCicilan) || 0
            },
            tenorPembayaran: 'cicilan',
            tagihanUkt: 0,

            get tenorLabel() {
                // Hapus logika return untuk semester
                if(this.tenorPembayaran === 'tahun') return '1 Tahun';
                return 'Cicilan Awal';
            },

            formatRupiah(angka) {
                if (angka == null || isNaN(parseInt(angka))) return '0';
                return parseInt(angka).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            },

            updateTagihanUkt(pilihan) {
                this.tenorPembayaran = pilihan;
                // Hapus logika if untuk semester
                if (pilihan === 'tahun') this.tagihanUkt = this.prices.tahun;
                else this.tagihanUkt = this.prices.cicilan;
            },

            init() {
                // Default: Pilih Cicilan saat load
                this.tenorPembayaran = 'cicilan';
                this.tagihanUkt = this.prices.cicilan;
                
                // Pastikan radio button visual sync
                setTimeout(() => {
                    const el = document.getElementById('tenor_cicilan');
                    if(el) el.checked = true;
                }, 100);
            }
        }));
    });
</script>
@endsection