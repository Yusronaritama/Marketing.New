@extends('layouts.mahasiswa.profesi')

@section('title', 'Detail Tagihan UKT')

@section('content')
<div class="min-h-screen py-10 px-4 sm:px-6 flex justify-center">
    <div class="w-full max-w-3xl space-y-6 pb-24" 
         x-data="{
            status: '{{ $transaction->status }}',
            isChecking: false,
            
            init() {
                if(this.status !== 'paid') {
                    setInterval(() => {
                        this.checkPaymentStatus();
                    }, 5000);
                }
            },
            
            checkPaymentStatus() {
                if(this.status === 'paid') return;
                
                fetch('{{ route('profesi.reviewpayment.checkStatus', $transaction->order_id) }}')
                    .then(res => res.json())
                    .then(data => {
                        if(data.status === 'paid') {
                            this.status = 'paid';
                            setTimeout(() => {
                                window.location.href = '{{ route('profesi.dashboard') }}';
                            }, 3000);
                        }
                    });
            },
            
            copyVA() {
                navigator.clipboard.writeText('{{ $transaction->virtual_account }}');
                alert('Nomor VA berhasil disalin!');
            }
         }">

        {{-- Top Bar --}}
        <div class="mb-6 flex items-center justify-between">
            <a href="{{ route('profesi.dashboard') }}" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Dashboard
            </a>
            
            <span x-show="status === 'pending'" 
                  class="bg-amber-100 text-amber-800 text-xs font-bold px-3 py-1 rounded-full border border-amber-200 shadow-sm animate-pulse">
                Menunggu Pembayaran
            </span>
            <span x-show="status === 'paid'" 
                  style="display: none;" 
                  class="bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1 rounded-full border border-emerald-200 shadow-sm">
                Lunas & Terverifikasi
            </span>
        </div>

        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">

            {{-- Header Bank --}}
            <div class="bg-[#003D79] px-6 py-8 sm:px-10 text-white flex flex-col sm:flex-row items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold">Virtual Account Mandiri</h2>
                    <p class="mt-1 text-blue-200 text-sm">Pembayaran UKT Program Profesi Apoteker</p>
                </div>
                <div class="bg-white p-2 rounded-xl shrink-0">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/a/ad/Bank_Mandiri_logo_2016.svg" alt="Mandiri" class="h-8">
                </div>
            </div>

            <div class="p-6 sm:p-10">

                {{-- Alert Lunas --}}
                <div x-show="status === 'paid'" 
                     style="display: none;" 
                     class="mb-8 bg-emerald-50 border border-emerald-200 rounded-2xl p-6 text-center">
                    <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-3 shadow-inner">
                        <i class="fas fa-check text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-emerald-900">Pembayaran Berhasil!</h3>
                    <p class="text-sm text-emerald-700 mt-1">Terima kasih, tagihan UKT Anda telah lunas. Anda akan diarahkan ke Dashboard dalam beberapa detik...</p>
                </div>

                {{-- Area Nomor VA --}}
                <div x-show="status !== 'paid'" class="text-center mb-8">
                    <p class="text-sm text-gray-500 font-medium uppercase tracking-wider mb-2">Nomor Virtual Account</p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                        <span class="text-3xl sm:text-4xl font-black text-gray-900 tracking-widest font-mono bg-gray-50 py-3 px-6 rounded-2xl border border-gray-200 shadow-inner">
                            {{ $transaction->virtual_account }}
                        </span>
                        <button @click="copyVA()" 
                                class="bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 p-3.5 rounded-xl border border-blue-200 transition-colors shadow-sm" 
                                title="Salin Nomor VA">
                            <i class="far fa-copy text-lg"></i>
                        </button>
                    </div>
                </div>

                <hr class="border-dashed border-gray-200 mb-8">

                {{-- Rincian Tagihan --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 bg-slate-50 p-6 rounded-2xl border border-slate-100">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Order ID</p>
                        <p class="font-mono font-semibold text-gray-900 text-sm">{{ $transaction->order_id }}</p>
                    </div>

                    <div class="sm:text-right overflow-hidden">
                        <p class="text-xs text-gray-500 mb-1">Nama Registrasi</p>
                        <p class="font-semibold text-gray-900 text-sm truncate" title="{{ $transaction->customer_name }}">
                            {{ $transaction->customer_name ?? 'TIDAK TERSEDIA' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500 mb-1">Metode Pembayaran</p>
                        <p class="font-semibold text-gray-900 text-sm capitalize">
                            {{ $transaction->tenor_pembayaran === 'lunas' ? 'Lunas (Full Payment)' : 'Per Semester' }}
                        </p>
                    </div>

                    <div class="sm:text-right">
                        <p class="text-xs text-gray-500 mb-1">Batas Waktu Pembayaran</p>
                        <p class="font-semibold text-red-600 text-sm">
                            <i class="far fa-clock mr-1"></i>
                            {{ \Carbon\Carbon::parse($transaction->datetime_expired ?? now()->addMonth())->translatedFormat('d F Y, H:i') }} WIB
                        </p>
                    </div>

                    <div class="sm:col-span-2 border-t border-dashed border-slate-200 pt-4 flex justify-between items-center">
                        <p class="font-bold text-gray-700">Total Tagihan</p>
                        <p class="font-black text-blue-700 text-xl">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</p>
                    </div>
                </div>

                {{-- Tombol Cek Manual & Cancel --}}
                <div x-show="status !== 'paid'" class="mt-8 space-y-4">
                    
                    {{-- Cek Manual --}}
                    <button @click="checkPaymentStatus(); isChecking = true; setTimeout(() => isChecking = false, 1000)" 
                            :disabled="isChecking"
                            class="w-full bg-gray-900 hover:bg-black text-white font-bold py-4 rounded-xl shadow-lg transition-all flex justify-center items-center gap-2 disabled:opacity-70 disabled:cursor-wait">
                        <i class="fas fa-sync-alt" :class="isChecking ? 'fa-spin' : ''"></i>
                        <span x-text="isChecking ? 'Mengecek...' : 'Cek Status Pembayaran Manual'"></span>
                    </button>
                    <p class="text-xs text-center text-gray-400">
                        <i class="fas fa-info-circle mr-1"></i> Halaman ini me-refresh status otomatis setiap 5 detik.
                    </p>

                    {{-- Cancel VA --}}
                    @if($transaction->status === 'pending')
                    <div class="border-t border-slate-100 pt-5" x-data="{ confirm: false }">

                        <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-xl p-4 mb-4">
                            <i class="fas fa-rotate-left text-amber-500 mt-0.5 flex-shrink-0"></i>
                            <div>
                                <p class="text-sm font-bold text-amber-800">Ingin ganti metode pembayaran?</p>
                                <p class="text-xs text-amber-700 mt-0.5 leading-relaxed">
                                    Batalkan tagihan ini, lalu buat tagihan baru dengan metode yang berbeda.
                                    Nomor VA akan <strong>langsung dihapus</strong> setelah dibatalkan.
                                </p>
                            </div>
                        </div>

                        <button x-show="!confirm"
                                @click="confirm = true"
                                class="w-full flex items-center justify-center gap-2 py-3 rounded-xl border-2 border-red-200 text-red-600 font-bold text-sm hover:bg-red-50 transition-all active:scale-95">
                            <i class="fas fa-times-circle"></i>
                            Batalkan Tagihan & Ganti Metode
                        </button>

                        <div x-show="confirm" x-transition class="space-y-3">
                            <p class="text-center text-sm font-semibold text-slate-600 pt-2">
                                Yakin ingin menghapus tagihan ini?
                            </p>
                            <div class="grid grid-cols-2 gap-3">
                                <button @click="confirm = false"
                                        class="py-3 rounded-xl border border-slate-200 text-slate-600 font-semibold text-sm hover:bg-slate-50 transition-all">
                                    Tidak, Kembali
                                </button>
                                <form action="{{ route('profesi.reviewpayment.cancel', $transaction->order_id) }}"
                                      method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="w-full py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-sm transition-all active:scale-95">
                                        Ya, Hapus VA
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                    @endif

                </div>

            </div>
        </div>
    </div>
</div>
@endsection