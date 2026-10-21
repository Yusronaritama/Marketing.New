@extends('layouts.mahasiswa.pembayaran-layout')
@section('title', 'Status Pembayaran')

@section('content')

<div class="max-w-3xl mx-auto my-10 px-4">

    {{-- Kita kirim data transaksi dari Laravel ke Javascript --}}
    <div x-data="paymentPage({{ Js::from($transaction) }})">

        {{-- ==================================== --}}
        {{-- 1. TAMPILAN MENUNGGU (PENDING)       --}}
        {{-- ==================================== --}}
        <div x-show="status === 'pending'" 
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             {{-- Penting: Gunakan x-cloak atau style display none agar tidak kedip --}}
             style="display: none;" 
        >
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden relative transform transition-all">
                
                {{-- Header Biru --}}
                <div class="bg-blue-600 p-6 sm:p-8 text-white text-center relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-full opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
                    
                    <p class="text-blue-100 text-sm font-medium uppercase tracking-wider mb-1">Total Tagihan</p>
                    <h1 class="text-4xl sm:text-5xl font-extrabold mb-2 tracking-tight">
                        Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                    </h1>

                    {{-- INFORMASI JURUSAN & METODE (BARU) --}}
                    <div class="mt-4 flex justify-center gap-2 flex-wrap relative z-10">
                        <span class="px-3 py-1 bg-blue-700/50 rounded-full text-xs font-semibold border border-blue-400/30 text-blue-50 backdrop-blur-sm">
                            <i class="fas fa-graduation-cap mr-1"></i> {{ $transaction->nama_jurusan ?? 'Jurusan Tidak Diketahui' }}
                        </span>
                        <span class="px-3 py-1 bg-blue-700/50 rounded-full text-xs font-semibold border border-blue-400/30 text-blue-50 backdrop-blur-sm capitalize">
                            <i class="fas fa-credit-card mr-1"></i> {{ $transaction->tenor_pembayaran ?? 'Reguler' }}
                        </span>
                    </div>

                    {{-- DROPDOWN RINCIAN --}}
                    <div class="mt-4 mb-4 relative z-10">
                        <button @click="showDetails = !showDetails" 
                                class="text-xs font-semibold bg-blue-700 hover:bg-blue-800 text-blue-100 py-1.5 px-4 rounded-full transition-colors inline-flex items-center space-x-1 focus:outline-none ring-1 ring-blue-400/30">
                            <span>Lihat Rincian Tagihan</span>
                            <i class="fas fa-chevron-down transition-transform duration-200" :class="{'rotate-180': showDetails}"></i>
                        </button>

                        <div x-show="showDetails" x-collapse class="mt-3 max-w-sm mx-auto">
                            <div class="bg-blue-800/50 backdrop-blur-sm rounded-lg p-3 text-sm text-blue-50 text-left border border-blue-400/30 shadow-inner space-y-2">
                                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-1 sm:gap-4">
                                    <span class="text-sm text-gray-300">Program Studi</span>
                                    <span class="font-bold text-white sm:text-right break-words leading-tight">
                                        {{ $transaction->nama_jurusan }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Metode Bayar</span>
                                    <span class="font-bold text-white capitalize">{{ $transaction->tenor_pembayaran }}</span>
                                </div>
                                <div class="border-t border-blue-400/30 pt-2 font-bold text-white flex justify-between">
                                    <span>Total Bayar</span>
                                    <span>Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="inline-flex items-center bg-blue-700 bg-opacity-50 px-4 py-1.5 rounded-full text-sm border border-blue-400 border-opacity-30 backdrop-blur-sm">
                        <i class="far fa-clock mr-2 animate-pulse"></i> Menunggu Pembayaran
                    </div>
                </div>

                {{-- Body Putih --}}
                <div class="p-6 sm:p-8">
                    
                    {{-- Virtual Account Box --}}
                    <div class="bg-gray-50 rounded-2xl border border-gray-200 p-6 text-center mb-8 relative group hover:border-blue-300 transition-all duration-300 hover:shadow-md">
                        <p class="text-gray-500 text-sm font-medium mb-2">Nomor Virtual Account (Mandiri)</p>
                        
                        <div class="w-full max-w-lg mx-auto">
                            {{-- Container seperti Input Field --}}
                            <div class="flex items-center justify-between gap-2 p-3 bg-gray-50 border border-gray-200 rounded-xl">
                                <span class="font-mono font-bold text-gray-800 tracking-wide select-all text-xl sm:text-3xl w-full text-center break-all">
                                    {{ $transaction->virtual_account }}
                                </span>
                        
                                {{-- Tombol Copy --}}
                                {{-- shrink-0 : Agar tombol tidak mengecil/gepeng --}}
                                <button 
                                    @click="navigator.clipboard.writeText('{{ $transaction->virtual_account }}'); alert('Nomor VA Disalin!')" 
                                    class="shrink-0 bg-white hover:bg-blue-50 text-gray-500 hover:text-blue-600 p-2.5 rounded-lg border border-gray-200 hover:border-blue-200 transition-all shadow-sm active:scale-95 flex items-center justify-center h-10 w-10 md:h-12 md:w-12" 
                                    title="Salin Nomor VA">
                                    <i class="far fa-copy text-lg"></i>
                                </button>
                                
                            </div>
                        </div>
                        <p class="text-sm text-gray-400 mt-2">a.n. {{ Str::limit(Auth::user()->name, 30) }}</p>
                        
                        {{-- Batas Waktu --}}
                        <div x-data="{
                            expiry: '{{ $transaction->datetime_expired }}',
                            expired: false,
                            days: '00', hours: '00', minutes: '00', seconds: '00',
                            init() {
                                this.calculate();
                                setInterval(() => { this.calculate(); }, 1000);
                            },
                            calculate() {
                                const endDate = new Date(this.expiry).getTime();
                                const now = new Date().getTime();
                                const distance = endDate - now;
                        
                                if (distance < 0) {
                                    this.expired = true;
                                    this.days = '00'; this.hours = '00'; this.minutes = '00'; this.seconds = '00';
                                    // Opsional: Refresh halaman jika expired saat dilihat
                                    // window.location.reload(); 
                                } else {
                                    this.days = String(Math.floor(distance / (1000 * 60 * 60 * 24))).padStart(2, '0');
                                    this.hours = String(Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2, '0');
                                    this.minutes = String(Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0');
                                    this.seconds = String(Math.floor((distance % (1000 * 60)) / 1000)).padStart(2, '0');
                                }
                            }
                        }" class="mt-6 mb-2">
                        
                            {{-- Tampilan Timer (Jika Belum Expired) --}}
                            <div x-show="!expired" class="inline-flex gap-3 bg-red-50 border border-red-100 px-4 py-2 rounded-xl shadow-sm">
                                <div class="text-center">
                                    <span class="block text-lg font-bold text-red-600 font-mono leading-none" x-text="days"></span>
                                    <span class="text-[9px] text-red-400 uppercase">Hari</span>
                                </div>
                                <span class="text-red-300 font-bold text-lg">:</span>
                                <div class="text-center">
                                    <span class="block text-lg font-bold text-red-600 font-mono leading-none" x-text="hours"></span>
                                    <span class="text-[9px] text-red-400 uppercase">Jam</span>
                                </div>
                                <span class="text-red-300 font-bold text-lg">:</span>
                                <div class="text-center">
                                    <span class="block text-lg font-bold text-red-600 font-mono leading-none" x-text="minutes"></span>
                                    <span class="text-[9px] text-red-400 uppercase">Mnt</span>
                                </div>
                                <span class="text-red-300 font-bold text-lg">:</span>
                                <div class="text-center">
                                    <span class="block text-lg font-bold text-red-600 font-mono leading-none animate-pulse" x-text="seconds"></span>
                                    <span class="text-[9px] text-red-400 uppercase">Dtk</span>
                                </div>
                            </div>
                        
                            {{-- Tampilan Jika Expired (Realtime) --}}
                            <div x-show="expired" style="display: none;" class="text-red-600 font-bold text-sm bg-red-50 px-4 py-2 rounded-lg border border-red-200">
                                <i class="fas fa-exclamation-circle mr-1"></i> Waktu Habis. Silakan refresh halaman.
                            </div>
                        
                            {{-- Info Tanggal Statis --}}
                            <p class="text-[10px] text-gray-400 mt-2">
                                Jatuh Tempo: {{ \Carbon\Carbon::parse($transaction->datetime_expired)->format('d M Y, H:i') }} WIB
                            </p>
                        </div>
                    </div>

                    {{-- Instruksi Pembayaran --}}
                    <div class="mb-8">
                        <h3 class="text-gray-800 font-bold text-lg mb-4 flex items-center">
                            <span class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mr-3 shadow-sm"><i class="fas fa-wallet"></i></span>
                            Cara Pembayaran
                        </h3>
                        <div class="space-y-3">
                            <template x-for="method in methods" :key="method.id">
                                <div class="border border-gray-100 rounded-xl bg-white shadow-sm hover:shadow-md transition-all duration-300">
                                    <button @click="activeMethod = (activeMethod === method.id ? null : method.id)"
                                            class="w-full flex items-center justify-between px-5 py-4 text-left focus:outline-none group">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-8 flex items-center justify-center bg-gray-50 rounded">
                                             <i class="fas fa-university text-gray-400"></i>
                                        </div>
                                        <span class="font-semibold text-gray-700 group-hover:text-blue-600 transition-colors" x-text="method.name"></span>
                                    </div>
                                    <i class="fas fa-chevron-down text-gray-300 transition-transform duration-300"
                                       :class="{'rotate-180 text-blue-500': activeMethod === method.id}"></i>
                                    </button>
                                    
                                    <div x-show="activeMethod === method.id" x-collapse>
                                        <div class="px-5 pb-5 pt-0">
                                            <div class="h-px bg-gray-100 mb-4"></div>
                                            <ol class="list-decimal list-outside ml-5 space-y-2 text-sm text-gray-600">
                                                <template x-for="(step, index) in method.steps" :key="index">
                                                    <li class="mb-2 text-sm text-slate-600 leading-relaxed" x-html="step"></li>
                                                </template>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                    {{-- TOMBOL UBAH METODE (RESET) --}}
                    <div class="mt-8 text-center border-t pt-6 border-dashed border-gray-300">
                        <p class="text-xs text-gray-500 mb-3">Ingin mengubah metode pembayaran (misal: dari 1 Tahun ke Cicilan)?</p>
                        
                        {{-- UPDATE: Tambahkan ID dan Hapus onsubmit bawaan --}}
                        <form id="form-reset-tagihan" 
                              action="{{ route('mahasiswa.pembayaran.reset', $transaction->order_id) }}" 
                              method="POST">
                            @csrf
                            
                            {{-- UPDATE: type="button" dan panggil fungsi Alpine confirmReset() --}}
                            <button type="button" 
                                    @click="confirmReset()"
                                    class="text-red-500 bg-red-50 hover:bg-red-100 border border-red-200 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center gap-2 transition-all">
                                <i class="fas fa-trash-alt"></i> Batalkan & Ubah Metode Pembayaran
                            </button>
                        </form>
                    </div>
                    
                    <a href="{{ route('mahasiswa.dashboard') }}" class="block w-full py-3.5 text-center rounded-xl text-gray-500 hover:text-gray-700 hover:bg-gray-50 font-medium transition-all duration-200">
                        Kembali ke Dashboard
                    </a>
                    
                </div>
            </div>
            <p class="text-center text-gray-400 text-sm mt-6 animate-pulse">Menunggu pembayaran otomatis...</p>
            
            
            
            
            
        </div>

        {{-- ==================================== --}}
        {{-- 2. TAMPILAN SUKSES (PAID)            --}}
        {{-- ==================================== --}}
        <div x-show="status === 'paid'" 
             x-transition:enter="transition ease-out duration-500 delay-300"
             x-transition:enter-start="opacity-0 transform scale-90 translate-y-4"
             x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
             style="display: none;">
             
            <div class="bg-white rounded-3xl shadow-2xl border-t-8 border-green-500 p-8 sm:p-12 text-center relative overflow-hidden">
                <div class="w-24 h-24 bg-green-100 text-green-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner ring-4 ring-green-50 animate-[bounce_1s_ease-in-out_1]">
                    <i class="fas fa-check text-4xl"></i>
                </div>

                <h2 class="text-3xl font-extrabold text-gray-800 mb-2">Pembayaran Berhasil!</h2>
                <p class="text-gray-500 mb-8">Terima kasih, transaksi Anda telah lunas.</p>

                {{-- Struk Mini --}}
                <div class="bg-gray-50 rounded-2xl p-6 max-w-md mx-auto mb-8 border border-gray-200 text-sm">
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-500">Program Studi</span>
                        <span class="font-semibold text-gray-800 text-right w-1/2">{{ $transaction->nama_jurusan }}</span>
                    </div>
                    <div class="flex justify-between mb-3">
                        <span class="text-gray-500">Metode</span>
                        <span class="font-semibold text-gray-800 capitalize">{{ $transaction->tenor_pembayaran }}</span>
                    </div>
                    <div class="flex justify-between mb-3">
                        <span class="text-gray-500">Waktu</span>
                        <span class="font-semibold text-gray-800" x-text="formatTanggal(transaction.updated_at)"></span>
                    </div>
                    <div class="flex justify-between items-center border-t border-dashed border-gray-300 pt-3 mt-3">
                        <span class="text-gray-600 font-bold">Total</span>
                        <span class="text-xl font-bold text-green-600">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- ALMAMATER --}}
                @if(session('success_almamater'))
                    <div class="max-w-md mx-auto bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl mb-8 shadow-sm flex items-center">
                        <i class="fas fa-check-circle mr-2 text-xl"></i> 
                        <span>{{ session('success_almamater') }}</span>
                    </div>
                @elseif(isset($ereg) && empty($ereg->ukuran_almamater))
                    <div x-data="almamaterForm()" class="max-w-md mx-auto bg-blue-50 p-6 rounded-2xl border border-blue-100 mb-8 text-left shadow-sm relative">
                        <h3 class="font-bold text-gray-800 mb-1 flex items-center">
                            <i class="fas fa-tshirt text-blue-500 mr-2"></i> Pilih Ukuran Almamater
                        </h3>
                        <p class="text-xs text-gray-500 mb-4 ml-6">Wajib diisi untuk pengambilan jas.</p>
                        
                        @if(session('error_almamater'))
                            <p class="text-xs text-red-600 mb-2 ml-6">{{ session('error_almamater') }}</p>
                        @endif

                        <form action="{{ route('pembayaran.saveAlmamater') }}" method="POST" class="flex gap-2 mt-3 relative z-20">
                            @csrf
                            <input type="hidden" name="ukuran_almamater" x-model="selectedUkuran">
                            
                            <div class="relative flex-grow">
                                <button type="button" @click="isOpen = !isOpen" 
                                        class="w-full bg-white border border-gray-300 rounded-xl px-4 py-3 text-left text-sm focus:ring-2 focus:ring-blue-500 flex justify-between items-center shadow-sm hover:bg-gray-50">
                                    <span class="block truncate font-medium" x-text="selectedLabel">-- Pilih Ukuran --</span>
                                    <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform" :class="{'rotate-180': isOpen}"></i>
                                </button>
                                <ul x-show="isOpen" @click.outside="isOpen = false" x-transition class="absolute z-30 mt-1 w-full max-h-48 overflow-auto rounded-xl bg-white py-1 text-sm shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none">
                                    <template x-for="option in options" :key="option.value">
                                        <li @click="selectOption(option)" class="cursor-pointer select-none py-2.5 pl-3 pr-9 hover:bg-blue-50 transition-colors" :class="{'bg-blue-50 text-blue-700 font-semibold': selectedUkuran === option.value}">
                                            <span x-text="option.label"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                            <button type="submit" :disabled="!selectedUkuran" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl text-sm font-bold shadow-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                                Simpan
                            </button>
                        </form>
                    </div>
                @endif

                <a href="{{ route('mahasiswa.dashboard') }}" class="inline-block bg-gray-800 text-white px-8 py-4 rounded-xl font-semibold shadow-lg hover:bg-gray-900 hover:shadow-xl transition-all transform hover:-translate-y-1">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>

        {{-- ==================================== --}}
        {{-- 3. [BARU] TAMPILAN KADALUWARSA       --}}
        {{-- ==================================== --}}
        <div x-show="status === 'expired'" 
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0 transform scale-90"
             x-transition:enter-end="opacity-100 transform scale-100"
             style="display: none;">
             
            <div class="bg-white rounded-3xl shadow-2xl border-t-8 border-red-500 p-8 sm:p-12 text-center relative overflow-hidden">
                
                {{-- Icon Tong Sampah / Silang Merah --}}
                <div class="w-24 h-24 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner ring-4 ring-red-50">
                    <i class="fas fa-times text-4xl"></i>
                </div>

                <h2 class="text-3xl font-extrabold text-gray-800 mb-2">Tagihan Kadaluwarsa</h2>
                <p class="text-gray-500 mb-8 max-w-md mx-auto">
                    Maaf, batas waktu pembayaran untuk tagihan ini sudah habis. Silakan buat tagihan baru untuk melanjutkan.
                </p>

                <div class="bg-red-50 rounded-xl p-4 border border-red-100 text-red-800 text-sm mb-8 inline-block text-left min-w-[280px]">
                    <div class="flex justify-between mb-1">
                        <span class="text-gray-500">Jurusan:</span>
                        <span class="font-bold">{{ $transaction->nama_jurusan }}</span>
                    </div>
                    <div class="flex justify-between mb-3">
                        <span class="text-gray-500">Metode:</span>
                        <span class="font-bold capitalize">{{ $transaction->tenor_pembayaran }}</span>
                    </div>
                    <div class="border-t border-red-200 pt-2 mt-2">
                        <p class="font-bold text-center">Virtual Account: {{ $transaction->virtual_account }}</p>
                        <p class="text-red-600 text-xs mt-1 text-center font-bold uppercase">(NON-AKTIF)</p>
                    </div>
                </div>

                {{-- Tombol Buat Baru --}}
                <div class="flex flex-col sm:flex-row justify-center gap-4">
 
                    {{-- UPDATE: Gunakan Form untuk tombol Reset/Buat Baru --}}
                    <form action="{{ route('mahasiswa.pembayaran.reset', $transaction->order_id) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-red-600 text-white px-8 py-3.5 rounded-xl font-bold shadow-lg hover:bg-red-700 hover:shadow-red-500/30 transition-all transform hover:-translate-y-1 flex items-center justify-center gap-2 w-full sm:w-auto">
                            <i class="fas fa-redo-alt"></i> Buat Tagihan Baru
                        </button>
                    </form>
                    
                    <a href="{{ route('mahasiswa.dashboard') }}" class="bg-gray-100 text-gray-600 px-8 py-3.5 rounded-xl font-bold hover:bg-gray-200 transition-all text-center">
                        Kembali
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- SCRIPT ALPINE JS --}}
<script>
    document.addEventListener('alpine:init', () => {
        
        Alpine.data('paymentPage', (transactionData) => ({
            transaction: transactionData,
            status: (transactionData.status || 'pending').toLowerCase(),
            orderId: transactionData.order_id,
            activeMethod: null,
            showDetails: false,
            interval: null,

            methods: [
                {
                    id: 'mandiri_atm',
                    name: 'ATM Mandiri',
                    steps: [
                        'Masukkan kartu ATM dan PIN Mandiri Anda.',
                        'Pilih menu <strong>TRANSAKSI LAINNYA</strong>.',
                        'Pilih menu <strong>TRANSFER</strong>.',
                        'Pilih menu <strong>KE REKENING MANDIRI</strong>.',
                        `Masukkan Nomor Virtual Account sebagai rekening tujuan: <br><span class="text-xl font-bold text-blue-600">${transactionData.virtual_account}</span>`,
                        'Tekan <strong>BENAR</strong>.',
                        `Masukkan <strong>Jumlah Pembayaran</strong> sesuai tagihan: <br><span class="font-bold">Rp ${new Intl.NumberFormat('id-ID').format(transactionData.amount)}</span>`,
                        'Tekan <strong>BENAR</strong>.',
                        'Layar akan menampilkan data konfirmasi. Pastikan Nama Penerima dan Jumlah sesuai.',
                        'Jika benar, tekan <strong>YA</strong>.',
                        'Simpan struk sebagai bukti pembayaran.'
                    ]
                },
                {
                    id: 'mandiri_livin',
                    name: 'Livin\' by Mandiri (Mobile App)',
                    steps: [
                        'Login ke aplikasi Livin\' by Mandiri.',
                        'Pilih menu <strong>BAYAR/VA</strong> pada halaman utama.',
                        `Pada kolom pencarian, Masukkan Nomor Virtual Account: <br><span class="text-xl font-bold text-blue-600">${transactionData.virtual_account}</span>`,
                        'Konfirmasi data tagihan & nominal pembayaran.',
                        'Lanjut Bayar dan masukkan <strong>PIN Livin\'</strong> Anda.',
                        'Pembayaran selesai. Simpan resi transaksi.'
                    ]
                },
                {
                    id: 'bank_lain_mobile',
                    name: 'Mobile Banking Bank Lain (BCA, BNI, BRI, dll)',
                    steps: [
                        'Buka aplikasi Mobile Banking bank Anda (BCA Mobile, BNI Mobile, Brimo, dll).',
                        'Pilih menu <strong>TRANSFER ANTAR BANK</strong> atau <strong>TRANSFER KE BANK LAIN</strong>.',
                        'Pilih Bank Tujuan: <strong>BANK MANDIRI</strong> (Kode Bank: 008).',
                        `Masukkan Nomor Rekening Tujuan dengan Nomor Virtual Account: <br><span class="text-xl font-bold text-blue-600">${transactionData.virtual_account}</span>`,
                        `Masukkan nominal pembayaran sesuai tagihan: <strong>Rp ${new Intl.NumberFormat('id-ID').format(transactionData.amount)}</strong> (Harus Persis).`,
                        'Pilih metode transfer: <strong>Realtime Online</strong> (biaya admin sesuai kebijakan bank).',
                        'Pastikan Nama Penerima muncul (Biasanya Nama Mahasiswa atau Institusi).',
                        'Lanjutkan pembayaran dan masukkan PIN Anda.'
                    ]
                },
                {
                    id: 'atm_bersama',
                    name: 'ATM Bersama / Prima / Link',
                    steps: [
                        'Masukkan kartu ATM dan PIN Bank Anda.',
                        'Pilih menu <strong>TRANSFER</strong> -> <strong>TRANSFER KE BANK LAIN</strong>.',
                        'Masukkan Kode Bank Mandiri: <strong>008</strong>.',
                        `Masukkan Nomor Rekening Tujuan (No. VA): <br><span class="text-xl font-bold text-blue-600">${transactionData.virtual_account}</span>`,
                        `Masukkan nominal pembayaran: <strong>Rp ${new Intl.NumberFormat('id-ID').format(transactionData.amount)}</strong>`,
                        'Layar ATM akan menampilkan rincian transaksi. Pastikan Nama Penerima & Nominal benar.',
                        'Jika sesuai, tekan <strong>YA/BENAR</strong> untuk memproses.',
                        'Simpan struk transaksi.'
                    ]
                }
            ],
            
            confirmReset() {
                Swal.fire({
                    title: 'Batalkan Tagihan?',
                    text: "Virtual Account saat ini akan dinonaktifkan. Anda harus membuat ulang tagihan baru jika ingin melanjutkan.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Batalkan!',
                    cancelButtonText: 'Kembali',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Memproses...',
                            text: 'Sedang membatalkan tagihan lama.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        document.getElementById('form-reset-tagihan').submit();
                    }
                });
            },

            init() {
                console.log('✅ Payment Page Loaded. Status:', this.status);
                if (this.status === 'pending') {
                    this.startPolling();
                }
            },

            startPolling() {
                console.log('⏳ Mulai Polling ke Server...');
                this.interval = setInterval(() => {
                    this.checkStatus();
                }, 3000); 
            },

            checkStatus() {
                const url = `/mahasiswa/pembayaran/status/${this.orderId}`;

                fetch(url)
                    .then(response => {
                        if (response.status === 404) {
                            clearInterval(this.interval);
                            console.warn('⚠️ Route status check tidak ditemukan. Polling dihentikan.');
                            return null;
                        }
                        if (!response.ok) throw new Error(`Server Error: ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        if(!data) return;

                        console.log('📩 Status Check:', data.status);
                        
                        const currentStatus = (data.status || '').toLowerCase();
                        
                        if (currentStatus === 'paid' || currentStatus === 'lunas') {
                            this.status = 'paid';
                            clearInterval(this.interval);
                            // Opsional: Reload halaman otomatis jika sudah lunas
                            // window.location.reload(); 
                        }
                        
                        if (currentStatus === 'expired') {
                            this.status = 'expired';
                            clearInterval(this.interval);
                        }
                    })
                    .catch(error => {
                        console.error('❌ Polling Error:', error);
                    });
            },

            formatTanggal(dateString) {
                if(!dateString) return '-';
                const date = new Date(dateString);
                return date.toLocaleDateString('id-ID', { 
                    day: 'numeric', month: 'long', year: 'numeric',
                    hour: '2-digit', minute: '2-digit'
                });
            }
        }));

        Alpine.data('almamaterForm', () => ({
            isOpen: false,
            selectedUkuran: null,
            selectedLabel: '-- Pilih Ukuran --',
            options: [
                { value: 'S', label: 'S (Tinggi: 65cm, Lebar: 50cm)' },
                { value: 'M', label: 'M (Tinggi: 68cm, Lebar: 53cm)' },
                { value: 'L', label: 'L (Tinggi: 71cm, Lebar: 56cm)' },
                { value: 'XL', label: 'XL (Tinggi: 74cm, Lebar: 59cm)' },
                { value: 'XXL', label: 'XXL (Tinggi: 77cm, Lebar: 62cm)' },
                { value: 'XXXL', label: 'XXXL (Tinggi: 80cm, Lebar: 65cm)' },
                { value: '4XL', label: '4XL' },
                { value: '5XL', label: '5XL' },
                { value: '6XL', label: '6XL' }
            ],
            selectOption(option) {
                this.selectedUkuran = option.value;
                this.selectedLabel = option.label;
                this.isOpen = false;
            }
        }));
    });
</script>

@endsection