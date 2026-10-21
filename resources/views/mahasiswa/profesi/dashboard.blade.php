@extends('layouts.mahasiswa.profesi')

@section('title', 'Dashboard ' . $namaProfesi)

@include('mahasiswa.profesi.components.opening')
@include('mahasiswa.profesi.components.maintenance')
@include('mahasiswa.profesi.components.warning')

@section('content')
<div class="min-h-screen py-10 px-4 sm:px-6 flex justify-center">
    {{-- Pembungkus utama dengan lebar maksimal --}}
    <div class="w-full max-w-3xl space-y-6 pb-24"> 

        {{-- ==========================================
             1. WARNING BANNER (Paling Atas)
             ========================================== --}}
        <!--<div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-xl shadow-sm flex items-start gap-4">-->
        <!--    <div class="text-amber-500 mt-1">-->
        <!--        <i class="fas fa-exclamation-circle text-xl animate-pulse"></i>-->
        <!--    </div>-->
        <!--    <div>-->
        <!--        <h3 class="text-sm font-bold text-amber-800 uppercase tracking-wider">Pendaftaran Ditunda</h3>-->
        <!--        <p class="text-sm text-amber-700 mt-1">-->
        <!--            Jalur pendaftaran untuk <strong>{{ $namaProfesi }}</strong> sedang dalam tahap sinkronisasi jadwal. Pantau dashboard secara berkala.-->
        <!--        </p>-->
        <!--        <p class="text-sm text-amber-700 mt-1">-->
        <!--            Upload berkas akan dibuka pada tanggal 22.-->
        <!--        </p>-->
        <!--    </div>-->
        <!--</div>-->

        {{-- ==========================================
             2. KARTU IDENTITAS & PROGRAM
             ========================================== --}}
        <div class="bg-white/95 backdrop-blur-md p-6 rounded-2xl shadow-xl border border-white/20 flex flex-col sm:flex-row items-center sm:items-start gap-4">
            <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center shrink-0">
                <i class="fas fa-user-graduate text-2xl"></i>
            </div>
            <div class="flex-1 text-center sm:text-left">
                <h2 class="text-xl font-bold text-gray-900">{{ $user->name }}</h2>
                <p class="text-sm text-gray-500 mb-3">{{ $user->email }} &bull; {{ $user->phone ?? 'No. HP Belum Diisi' }}</p>
                <span class="inline-block bg-indigo-50 text-indigo-700 text-xs font-bold px-3 py-1 rounded-full border border-indigo-100">
                    <i class="fas fa-stethoscope mr-1"></i> Pilihan: {{ $namaProfesi }}
                </span>
            </div>
        </div>

        {{-- ==========================================
             3. SURAT PERNYATAAN
             ========================================== --}}
        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
            <div class="flex items-center justify-center sm:justify-start gap-2 mb-3">
                <i class="fas fa-folder-open text-slate-500 text-sm"></i>
                <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wide">Surat Pernyataan</h4>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <a href="{{ route('profesi.surat.pkms') }}" target="_blank"
                   class="group flex items-center gap-3 p-3 bg-white border border-slate-200 hover:border-blue-300 hover:bg-blue-50/50 rounded-xl transition-all shadow-sm hover:shadow-md">
                    <div class="w-8 h-8 bg-blue-50 text-blue-500 rounded-lg flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-print text-sm"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-700 group-hover:text-blue-700 transition-colors line-clamp-1">Surat Pernyataan Menyelesaikan Studi</h4>
                        <p class="text-[9px] text-slate-400 mt-0.5">Klik untuk buka & cetak</p>
                    </div>
                </a>

                <a href="{{ route('profesi.surat.pkmu') }}" target="_blank"
                   class="group flex items-center gap-3 p-3 bg-white border border-slate-200 hover:border-blue-300 hover:bg-blue-50/50 rounded-xl transition-all shadow-sm hover:shadow-md">
                    <div class="w-8 h-8 bg-blue-50 text-blue-500 rounded-lg flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-print text-sm"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-700 group-hover:text-blue-700 transition-colors line-clamp-1">Surat Pernyataan Kesanggupan Membayar UKT</h4>
                        <p class="text-[9px] text-slate-400 mt-0.5">Klik untuk buka & cetak</p>
                    </div>
                </a>

                <a href="{{ route('profesi.surat.pkpa') }}" target="_blank"
                   class="group flex items-center gap-3 p-3 bg-white border border-slate-200 hover:border-blue-300 hover:bg-blue-50/50 rounded-xl transition-all shadow-sm hover:shadow-md">
                    <div class="w-8 h-8 bg-blue-50 text-blue-500 rounded-lg flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-print text-sm"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-700 group-hover:text-blue-700 transition-colors line-clamp-1">Surat Pernyataan Praktek Kerja Apoteker</h4>
                        <p class="text-[9px] text-slate-400 mt-0.5">Klik untuk buka & cetak</p>
                    </div>
                </a>
            </div>
        </div>

        {{-- ==========================================
             4. WADAH DINAMIS STEPPER (ALPINE.JS)
             ========================================== --}}
        <div x-data="{
                 isBerkasLengkap: {{ $isBerkasLengkap ? 'true' : 'false' }},
                 statusBerkas: '{{ $statusBerkas ?? 'belum_upload' }}',
                 catatanBerkas: '{{ $catatanBerkas ?? '' }}',
                 isPendaftaranBuka: {{ $isPendaftaranBuka ? 'true' : 'false' }},
                 userId: {{ auth()->id() }},
                 isCbtSelesai: {{ $isCbtSelesai ? 'true' : 'false' }},
                 isWawancaraSelesai: {{ $isWawancaraSelesai ? 'true' : 'false' }},
                 statusWawancara: '{{ strtolower($statusWawancara ?? '') }}',
                 activeTab: {{ $currentStep }},
                 
                 init() {
                     setInterval(() => {
                         fetch('{{ route('profesi.berkas.checkStatus') }}')
                             .then(res => res.json())
                             .then(data => {
                                 if (this.statusBerkas !== 'lulus' && data.statusBerkas === 'lulus') {
                                     this.activeTab = 2;
                                 }
                                 this.isBerkasLengkap = data.isBerkasLengkap;
                                 this.statusBerkas = data.statusBerkas;
                                 this.catatanBerkas = data.catatanBerkas;
                             })
                             .catch(err => {});
                     }, 5000);
                 }
             }"
             class="space-y-6">
            
            {{-- HEADER STEPPER BULAT-BULAT --}}
            <div class="bg-white/95 backdrop-blur-md p-6 sm:p-8 rounded-2xl shadow-xl border border-white/20">
                <div class="text-center sm:text-left mb-10">
                    <h3 class="text-lg font-bold text-gray-900 tracking-tight">Alur Pendaftaran</h3>
                    <p class="text-sm text-gray-500 mt-1">Selesaikan setiap tahapan untuk menjadi mahasiswa {{ $namaProfesi }}.</p>
                </div>

                <div class="relative flex flex-col sm:flex-row justify-between gap-8 sm:gap-0 mt-4">
                    
                    {{-- Dynamic Progress Line (Desktop) --}}
                    <div class="hidden sm:block absolute top-5 left-[10%] right-[10%] h-[3px] bg-gray-100 rounded-full z-0 overflow-hidden">
                        <div class="absolute top-0 left-0 h-full bg-blue-500 rounded-full transition-all duration-1000 ease-out"
                             :style="{{ $isBayarLunas ? 'true' : 'false' }} ? 'width: 100%' : ( '{{ $statusKelulusan }}' === 'LULUS' ? 'width: 75%' : ( isCbtSelesai ? 'width: 50%' : ( statusBerkas === 'lulus' ? 'width: 25%' : 'width: 0%' ) ) )"></div>
                    </div>

                    {{-- Dynamic Progress Line (Mobile) --}}
                    <div class="sm:hidden absolute top-[5%] bottom-[10%] left-5 w-[3px] bg-gray-100 rounded-full z-0 overflow-hidden">
                        <div class="absolute top-0 left-0 w-full bg-blue-500 rounded-full transition-all duration-1000 ease-out"
                             :style="{{ $isBayarLunas ? 'true' : 'false' }} ? 'height: 100%' : ( '{{ $statusKelulusan }}' === 'LULUS' ? 'height: 75%' : ( isCbtSelesai ? 'height: 50%' : ( statusBerkas === 'lulus' ? 'height: 25%' : 'height: 0%' ) ) )"></div>
                    </div>

                    {{-- STEP 1: Upload Berkas --}}
                    <div @click="activeTab = 1" class="relative z-10 flex flex-row sm:flex-col items-center gap-4 sm:gap-3 flex-1 cursor-pointer group">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full flex items-center justify-center font-bold text-sm sm:text-base transition-all duration-300 ring-[6px] ring-white"
                             :class="statusBerkas === 'lulus'
                                     ? (activeTab === 1 ? 'bg-emerald-500 text-white shadow-[0_0_20px_rgba(16,185,129,0.5)] scale-110' : 'bg-emerald-100 text-emerald-600 hover:bg-emerald-200')
                                     : (activeTab === 1 ? 'bg-blue-600 text-white shadow-[0_0_20px_rgba(37,99,235,0.5)] scale-110' : 'bg-blue-50 text-blue-600')">
                            <i x-show="statusBerkas === 'lulus'" class="fas fa-check"></i>
                            <span x-show="statusBerkas !== 'lulus'">1</span>
                        </div>
                        <div class="text-left sm:text-center mt-1 sm:mt-2 transition-all">
                            <p class="text-sm font-bold" :class="activeTab === 1 ? 'text-gray-900' : 'text-gray-500'">Upload Berkas</p>
                            <p class="text-[11px] font-medium" :class="statusBerkas === 'lulus' ? 'text-emerald-500' : 'text-blue-500'">
                                <span x-text="statusBerkas === 'lulus' ? 'Selesai' : 'Sedang Aktif'"></span>
                            </p>
                        </div>
                    </div>

                    {{-- STEP 2: Test CBT --}}
                    <div @click="if(statusBerkas === 'lulus') activeTab = 2" 
                         class="relative z-10 flex flex-row sm:flex-col items-center gap-4 sm:gap-3 flex-1 transition-all"
                         :class="statusBerkas === 'lulus' ? 'cursor-pointer group' : 'cursor-not-allowed'">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full flex items-center justify-center font-bold text-sm sm:text-base transition-all duration-300 ring-[6px] ring-white"
                             :class="statusBerkas === 'lulus'
                                     ? (activeTab === 2 ? 'bg-blue-600 text-white shadow-[0_0_20px_rgba(37,99,235,0.5)] scale-110' : (isCbtSelesai ? 'bg-emerald-100 text-emerald-600 hover:bg-emerald-200' : 'bg-white border-2 border-blue-200 text-blue-500 hover:bg-blue-50'))
                                     : 'bg-gray-100 text-gray-400'">
                            <i x-show="statusBerkas !== 'lulus'" class="fas fa-lock text-xs"></i>
                            <i x-show="statusBerkas === 'lulus' && isCbtSelesai" class="fas fa-check" style="display: none;"></i>
                            <span x-show="statusBerkas === 'lulus' && !isCbtSelesai">2</span>
                        </div>
                        <div class="text-left sm:text-center mt-1 sm:mt-2 transition-all">
                            <p class="text-sm font-bold" :class="activeTab === 2 ? 'text-gray-900' : 'text-gray-400'">Test CBT</p>
                            <p class="text-[11px] font-medium" :class="activeTab === 2 ? 'text-blue-500' : (isCbtSelesai ? 'text-emerald-500' : 'text-gray-400')">
                                <span x-text="isCbtSelesai ? 'Selesai' : (statusBerkas === 'lulus' ? 'Klik untuk lihat' : 'Terkunci')"></span>
                            </p>
                        </div>
                    </div>

                    {{-- STEP 3: Wawancara --}}
                    <div @click="if(isCbtSelesai) activeTab = 3" 
                         class="relative z-10 flex flex-row sm:flex-col items-center gap-4 sm:gap-3 flex-1 transition-all"
                         :class="isCbtSelesai ? 'cursor-pointer group' : 'opacity-60 grayscale cursor-not-allowed'">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full flex items-center justify-center font-bold text-sm sm:text-base transition-all duration-300 ring-[6px] ring-white"
                             :class="isCbtSelesai
                                     ? (isWawancaraSelesai 
                                         ? (activeTab === 3 ? 'bg-emerald-500 text-white shadow-[0_0_20px_rgba(16,185,129,0.5)] scale-110' : 'bg-emerald-100 text-emerald-600 hover:bg-emerald-200')
                                         : (activeTab === 3 ? 'bg-blue-600 text-white shadow-[0_0_20px_rgba(37,99,235,0.5)] scale-110' : 'bg-white border-2 border-blue-200 text-blue-500 hover:bg-blue-50'))
                                     : 'bg-gray-100 text-gray-400'">
                            <i x-show="!isCbtSelesai" class="fas fa-lock text-xs"></i>
                            <i x-show="isCbtSelesai && isWawancaraSelesai" class="fas fa-check" style="display: none;"></i>
                            <span x-show="isCbtSelesai && !isWawancaraSelesai">3</span>
                        </div>
                        <div class="text-left sm:text-center mt-1 sm:mt-2 transition-all">
                            <p class="text-sm font-bold" :class="activeTab === 3 ? 'text-gray-900' : (isWawancaraSelesai ? 'text-gray-700' : 'text-gray-500')">Wawancara</p>
                            <p class="text-[11px] font-medium" :class="activeTab === 3 ? 'text-blue-500' : (isWawancaraSelesai ? 'text-emerald-500' : 'text-gray-400')">
                                <span x-text="isCbtSelesai ? (isWawancaraSelesai ? 'Selesai' : (activeTab === 3 ? 'Sedang Aktif' : 'Klik untuk lihat')) : 'Terkunci'"></span>
                            </p>
                        </div>
                    </div>

                    {{-- STEP 4: Pembayaran UKT --}}
                    <div @click="if('{{ $statusKelulusan }}' === 'LULUS') activeTab = 4" 
                         class="relative z-10 flex flex-row sm:flex-col items-center gap-4 sm:gap-3 flex-1 transition-all"
                         :class="'{{ $statusKelulusan }}' === 'LULUS' ? 'cursor-pointer group' : 'opacity-60 grayscale cursor-not-allowed'">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full flex items-center justify-center font-bold text-sm sm:text-base transition-all duration-300 ring-[6px] ring-white"
                             :class="'{{ $statusKelulusan }}' === 'LULUS'
                                     ? (activeTab === 4 ? 'bg-blue-600 text-white shadow-[0_0_20px_rgba(37,99,235,0.5)] scale-110' : ( {{ $isBayarLunas ? 'true' : 'false' }} ? 'bg-emerald-100 text-emerald-600 hover:bg-emerald-200' : 'bg-white border-2 border-blue-200 text-blue-500 hover:bg-blue-50'))
                                     : 'bg-gray-100 text-gray-400'">
                            <i x-show="'{{ $statusKelulusan }}' !== 'LULUS'" class="fas fa-lock text-xs"></i>
                            <i x-show="'{{ $statusKelulusan }}' === 'LULUS' && {{ $isBayarLunas ? 'true' : 'false' }}" class="fas fa-check" style="display: none;"></i>
                            <span x-show="'{{ $statusKelulusan }}' === 'LULUS' && !{{ $isBayarLunas ? 'true' : 'false' }}">4</span>
                        </div>
                        <div class="text-left sm:text-center mt-1 sm:mt-2 transition-all">
                            <p class="text-sm font-bold" :class="activeTab === 4 ? 'text-gray-900' : ('{{ $statusKelulusan }}' === 'LULUS' ? 'text-gray-700' : 'text-gray-500')">Bayar UKT</p>
                            <p class="text-[11px] font-medium" :class="activeTab === 4 ? 'text-blue-500' : ('{{ $statusKelulusan }}' === 'LULUS' ? ({{ $isBayarLunas ? 'true' : 'false' }} ? 'text-emerald-500' : 'text-blue-500') : 'text-gray-400')">
                                <span x-text="'{{ $statusKelulusan }}' === 'LULUS' ? ({{ $isBayarLunas ? 'true' : 'false' }} ? 'Lunas' : 'Klik untuk lihat') : 'Terkunci'"></span>
                            </p>
                        </div>
                    </div>

                    {{-- STEP 5: Selesai --}}
                    <div @click="if({{ $isBayarLunas ? 'true' : 'false' }}) activeTab = 5" 
                         class="relative z-10 flex flex-row sm:flex-col items-center gap-4 sm:gap-3 flex-1 transition-all"
                         :class="{{ $isBayarLunas ? 'true' : 'false' }} ? 'cursor-pointer group' : 'opacity-60 grayscale cursor-not-allowed'">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full flex items-center justify-center font-bold text-sm sm:text-base transition-all duration-300 ring-[6px] ring-white"
                             :class="{{ $isBayarLunas ? 'true' : 'false' }}
                                     ? (activeTab === 5 ? 'bg-emerald-500 text-white shadow-[0_0_20px_rgba(16,185,129,0.5)] scale-110' : 'bg-emerald-100 text-emerald-600 hover:bg-emerald-200')
                                     : 'bg-gray-100 text-gray-400'">
                            <i x-show="!{{ $isBayarLunas ? 'true' : 'false' }}" class="fas fa-lock text-xs"></i>
                            <i x-show="{{ $isBayarLunas ? 'true' : 'false' }}" class="fas fa-flag-checkered text-xs"></i>
                        </div>
                        <div class="text-left sm:text-center mt-1 sm:mt-2 transition-all">
                            <p class="text-sm font-bold" :class="activeTab === 5 ? 'text-gray-900' : ({{ $isBayarLunas ? 'true' : 'false' }} ? 'text-gray-700' : 'text-gray-500')">Selesai</p>
                            <p class="text-[11px] font-medium" :class="activeTab === 5 ? 'text-emerald-500' : ({{ $isBayarLunas ? 'true' : 'false' }} ? 'text-emerald-500' : 'text-gray-400')">
                                <span x-text="{{ $isBayarLunas ? 'true' : 'false' }} ? 'Tuntas' : 'Terkunci'"></span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ---------------------------------------------------------
                 AREA STEP 1 UPLOAD BERKAS
                 --------------------------------------------------------- --}}
            <div x-show="activeTab === 1"
                 style="display: none;" 
                 x-transition:enter="transition ease-out duration-500 delay-200" 
                 x-transition:enter-start="opacity-0 translate-y-8 scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100" 
                 x-transition:leave-end="opacity-0 -translate-y-8 scale-95"
                 class="space-y-6">
                
                {{-- KARTU AKSI UPLOAD --}}
                <div class="bg-white/95 backdrop-blur-md p-6 sm:p-8 rounded-2xl shadow-xl border border-white/20 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-gray-900">Tahap 1: Upload Berkas Persyaratan</h3>
                        <p class="text-sm text-gray-500 mt-1">Silahkan unggah semua dokumen persyaratan untuk diverifikasi oleh Pihak Kampus.</p>

                        <div class="flex flex-wrap gap-2 mt-4">
                            <span x-show="['proses', 'belum'].includes(statusBerkas)" style="display: none;" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                <i class="fas fa-clock"></i> Menunggu Verifikasi
                            </span>
                            <span x-show="statusBerkas === 'tolak'" style="display: none;" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-bold bg-red-50 text-red-700 border border-red-200">
                                <i class="fas fa-ban"></i> Berkas Ditolak
                            </span>
                            <span x-show="!['lulus', 'proses', 'belum', 'tolak'].includes(statusBerkas)" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200">
                                <i class="fas fa-minus-circle"></i> Verifikasi: Belum Tersedia
                            </span>
                        </div>
                        
                        <div x-show="catatanBerkas && statusBerkas === 'tolak'" style="display: none;" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg text-xs text-red-700">
                            <strong>Catatan Admin:</strong> <span x-text="catatanBerkas"></span>
                        </div>
                    </div>

                    <div class="w-full sm:w-auto">
                        <template x-if="!isPendaftaranBuka && userId != 4261">
                            <button disabled class="w-full sm:w-auto px-6 py-3 bg-gray-200 text-gray-500 font-bold rounded-xl cursor-not-allowed flex items-center justify-center gap-2">
                                <i class="fas fa-lock"></i> Jadwal Belum Buka
                            </button>
                        </template>
                        <template x-if="isPendaftaranBuka || userId == 4261">
                            <div class="w-full sm:w-auto flex flex-col sm:flex-row gap-2">
                                
                                {{-- 1. MENUNGGU --}}
                                <button x-show="isBerkasLengkap && ['belum', 'proses'].includes(statusBerkas)" style="display: none;" disabled class="w-full sm:w-auto px-6 py-3 bg-red-500 text-white font-bold rounded-xl flex items-center justify-center gap-2 opacity-90">
                                    <i class="fas fa-hourglass-half animate-pulse"></i> Menunggu
                                </button>
                                
                                {{-- 2. DITOLAK --}}
                                <button x-show="statusBerkas === 'tolak'" style="display: none;" disabled class="w-full sm:w-auto px-6 py-3 bg-gray-800 text-gray-200 font-bold rounded-xl flex items-center justify-center gap-2 opacity-90">
                                    <i class="fas fa-times-circle"></i> Pendaftaran Ditolak
                                </button>
                                
                                {{-- 3. UPLOAD BARU --}}
                                <a x-show="!isBerkasLengkap && statusBerkas !== 'tolak'" style="display: none;" href="{{ route('profesi.berkas.create') }}" class="w-full sm:w-auto px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl flex items-center justify-center gap-2 transition hover:-translate-y-0.5">
                                    <i class="fas fa-cloud-upload-alt"></i> Upload Sekarang
                                </a>

                                {{-- 4. LULUS / DISETUJUI --}}
                                <button x-show="statusBerkas === 'lulus'" style="display: none;" disabled class="w-full sm:w-auto px-6 py-3 bg-emerald-500 text-white font-bold rounded-xl flex items-center justify-center gap-2 shadow-md cursor-default">
                                    <i class="fas fa-check-circle"></i> Berkas Disetujui
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- WADAH CHECKLIST BERKAS --}}
                <div x-data="{
                        userId: '{{ auth()->id() ?? 0 }}',
                        checks: {},
                        init() {
                            let saved = localStorage.getItem('berkasChecklist_' + this.userId);
                            if (saved) {
                                this.checks = JSON.parse(saved);
                            }
                        },
                        toggle(key) {
                            this.checks[key] = !this.checks[key];
                            localStorage.setItem('berkasChecklist_' + this.userId, JSON.stringify(this.checks));
                        }
                     }"
                     class="bg-white/95 backdrop-blur-md p-6 sm:p-8 rounded-2xl shadow-xl border border-white/20 mt-6">
                    
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 bg-emerald-100/80 text-emerald-600 rounded-full flex items-center justify-center shadow-sm">
                            <i class="fas fa-file-pdf text-sm"></i>
                        </div>
                        <h3 class="text-md font-bold text-gray-800">Persiapan Dokumen</h3>
                    </div>
                    <p class="text-sm text-gray-600 mb-5">Sambil menunggu jadwal buka, siapkan dokumen berikut dalam format digital (PDF/JPG). <span class="font-bold text-emerald-600">Klik untuk menandai!</span></p>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div @click="toggle('akreditasi')" class="flex items-center gap-3 p-3 cursor-pointer transition-all duration-300 backdrop-blur-sm rounded-xl border shadow-sm group select-none" :class="checks['akreditasi'] ? 'bg-emerald-50 border-emerald-200' : 'bg-white/50 hover:bg-white/80 border-gray-200'">
                            <i class="text-lg transition-transform group-hover:scale-110" :class="checks['akreditasi'] ? 'fas fa-check-circle text-emerald-500' : 'far fa-circle text-gray-300 group-hover:text-gray-400'"></i>
                            <span class="text-sm font-medium transition-colors" :class="checks['akreditasi'] ? 'text-emerald-700' : 'text-gray-700'">Sertifikat Akreditasi (Min. Baik Sekali)</span>
                        </div>
                        <div @click="toggle('ijazah')" class="flex items-center gap-3 p-3 cursor-pointer transition-all duration-300 backdrop-blur-sm rounded-xl border shadow-sm group select-none" :class="checks['ijazah'] ? 'bg-emerald-50 border-emerald-200' : 'bg-white/50 hover:bg-white/80 border-gray-200'">
                            <i class="text-lg transition-transform group-hover:scale-110" :class="checks['ijazah'] ? 'fas fa-check-circle text-emerald-500' : 'far fa-circle text-gray-300 group-hover:text-gray-400'"></i>
                            <span class="text-sm font-medium transition-colors" :class="checks['ijazah'] ? 'text-emerald-700' : 'text-gray-700'">Scan Asli Ijazah S1 Farmasi</span>
                        </div>
                        <div @click="toggle('transkrip')" class="flex items-center gap-3 p-3 cursor-pointer transition-all duration-300 backdrop-blur-sm rounded-xl border shadow-sm group select-none" :class="checks['transkrip'] ? 'bg-emerald-50 border-emerald-200' : 'bg-white/50 hover:bg-white/80 border-gray-200'">
                            <i class="text-lg transition-transform group-hover:scale-110" :class="checks['transkrip'] ? 'fas fa-check-circle text-emerald-500' : 'far fa-circle text-gray-300 group-hover:text-gray-400'"></i>
                            <span class="text-sm font-medium transition-colors" :class="checks['transkrip'] ? 'text-emerald-700' : 'text-gray-700'">Scan Asli Transkrip Nilai S1</span>
                        </div>
                        <div @click="toggle('pasphoto')" class="flex items-center gap-3 p-3 cursor-pointer transition-all duration-300 backdrop-blur-sm rounded-xl border shadow-sm group select-none" :class="checks['pasphoto'] ? 'bg-emerald-50 border-emerald-200' : 'bg-white/50 hover:bg-white/80 border-gray-200'">
                            <i class="text-lg transition-transform group-hover:scale-110" :class="checks['pasphoto'] ? 'fas fa-check-circle text-emerald-500' : 'far fa-circle text-gray-300 group-hover:text-gray-400'"></i>
                            <span class="text-sm font-medium transition-colors" :class="checks['pasphoto'] ? 'text-emerald-700' : 'text-gray-700'">Pas Photo 4x6 Latar Merah</span>
                        </div>
                        <div @click="toggle('ktp')" class="flex items-center gap-3 p-3 cursor-pointer transition-all duration-300 backdrop-blur-sm rounded-xl border shadow-sm group select-none" :class="checks['ktp'] ? 'bg-emerald-50 border-emerald-200' : 'bg-white/50 hover:bg-white/80 border-gray-200'">
                            <i class="text-lg transition-transform group-hover:scale-110" :class="checks['ktp'] ? 'fas fa-check-circle text-emerald-500' : 'far fa-circle text-gray-300 group-hover:text-gray-400'"></i>
                            <span class="text-sm font-medium transition-colors" :class="checks['ktp'] ? 'text-emerald-700' : 'text-gray-700'">Scan Asli KTP</span>
                        </div>
                        <div @click="toggle('kk')" class="flex items-center gap-3 p-3 cursor-pointer transition-all duration-300 backdrop-blur-sm rounded-xl border shadow-sm group select-none" :class="checks['kk'] ? 'bg-emerald-50 border-emerald-200' : 'bg-white/50 hover:bg-white/80 border-gray-200'">
                            <i class="text-lg transition-transform group-hover:scale-110" :class="checks['kk'] ? 'fas fa-check-circle text-emerald-500' : 'far fa-circle text-gray-300 group-hover:text-gray-400'"></i>
                            <span class="text-sm font-medium transition-colors" :class="checks['kk'] ? 'text-emerald-700' : 'text-gray-700'">Scan Asli Kartu Keluarga (KK)</span>
                        </div>
                        <div @click="toggle('asuransi')" class="flex items-center gap-3 p-3 cursor-pointer transition-all duration-300 backdrop-blur-sm rounded-xl border shadow-sm group select-none" :class="checks['asuransi'] ? 'bg-emerald-50 border-emerald-200' : 'bg-white/50 hover:bg-white/80 border-gray-200'">
                            <i class="text-lg transition-transform group-hover:scale-110" :class="checks['asuransi'] ? 'fas fa-check-circle text-emerald-500' : 'far fa-circle text-gray-300 group-hover:text-gray-400'"></i>
                            <span class="text-sm font-medium transition-colors" :class="checks['asuransi'] ? 'text-emerald-700' : 'text-gray-700'">Asuransi Kesehatan (Min. BPJS)</span>
                        </div>
                        <div @click="toggle('suket')" class="flex items-center gap-3 p-3 cursor-pointer transition-all duration-300 backdrop-blur-sm rounded-xl border shadow-sm group select-none" :class="checks['suket'] ? 'bg-emerald-50 border-emerald-200' : 'bg-white/50 hover:bg-white/80 border-gray-200'">
                            <i class="text-lg transition-transform group-hover:scale-110" :class="checks['suket'] ? 'fas fa-check-circle text-emerald-500' : 'far fa-circle text-gray-300 group-hover:text-gray-400'"></i>
                            <span class="text-sm font-medium transition-colors" :class="checks['suket'] ? 'text-emerald-700' : 'text-gray-700'">Suket Sehat & Bebas Buta Warna</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ---------------------------------------------------------
                 AREA STEP 2 CBT
                 --------------------------------------------------------- --}}
            <div x-show="activeTab === 2"
                 style="display: none;" 
                 x-transition:enter="transition ease-out duration-500 delay-200" 
                 x-transition:enter-start="opacity-0 translate-y-8 scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100" 
                 x-transition:leave-end="opacity-0 -translate-y-8 scale-95"
                 class="space-y-6">
                
                @if(!$biodata || $biodata->status !== 'completed')
                    {{-- BOX TAHAP 1: BIODATA PROFESI --}}
                    <div class="bg-white/95 backdrop-blur-md p-6 sm:p-8 rounded-2xl shadow-xl border border-white/20 mb-6 transition-all">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                    Tahap 1: Lengkapi Biodata Profesi 
                                    <span class="bg-amber-100 text-amber-700 text-[10px] uppercase tracking-wider font-extrabold px-2 py-0.5 rounded-full border border-amber-200 shadow-sm">Wajib</span>
                                </h3>
                                <p class="text-sm text-gray-600 mt-1">Sebelum mendapatkan jadwal ujian CBT, Anda diwajibkan untuk melengkapi data diri dan riwayat pendidikan S1 terlebih dahulu.</p>
                            </div>
                            <div class="hidden sm:flex w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl items-center justify-center border border-indigo-100 shadow-sm">
                                <i class="fas fa-id-card text-xl"></i>
                            </div>
                        </div>
                
                        <div class="mt-6 flex flex-col sm:flex-row items-center gap-3">
                            <a href="{{ route('profesi.biodata.create') }}" class="w-full sm:w-auto px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl flex items-center justify-center gap-2 shadow-md transition-all">
                                <i class="fas fa-edit"></i> Lengkapi Biodata Sekarang
                            </a>
                            <p class="text-xs text-gray-500 text-center sm:text-left">
                                <i class="fas fa-lock text-amber-500 mr-1"></i> Jadwal CBT Terkunci sampai Biodata selesai.
                            </p>
                        </div>
                    </div>
                @else
                    {{-- BOX TAHAP 2: JADWAL CBT --}}
                    <div class="bg-white/95 backdrop-blur-md p-6 sm:p-8 rounded-2xl shadow-xl border border-white/20">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                    Tahap 2: Jadwal Ujian CBT 
                                    @if($isCbtSelesai)
                                        <span class="bg-emerald-100 text-emerald-700 text-[10px] uppercase tracking-wider font-extrabold px-2 py-0.5 rounded-full border border-emerald-200 shadow-sm">Selesai</span>
                                    @else
                                        <span class="bg-red-100 text-red-700 text-[10px] uppercase tracking-wider font-extrabold px-2 py-0.5 rounded-full border border-red-200 shadow-sm">Offline</span>
                                    @endif
                                </h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    @if($isCbtSelesai)
                                        Anda telah menyelesaikan ujian CBT. Silahkan lanjut ke tahap Wawancara pada Alur Pendaftaran di atas.
                                    @else
                                        Ujian akan dilaksanakan secara langsung di area kampus. Harap hadir 30 menit sebelum jadwal yang tertera.
                                    @endif
                                </p>
                            </div>
                            <div class="hidden sm:flex w-12 h-12 bg-blue-50 text-blue-600 rounded-xl items-center justify-center border border-blue-100 shadow-sm">
                                <i class="fas fa-laptop-house text-xl"></i>
                            </div>
                        </div>
                
                        <div class="mt-6 bg-slate-50 border border-slate-200 rounded-xl p-4 sm:p-5 relative overflow-hidden">
                            <div class="absolute -right-6 -top-6 text-slate-100 opacity-50 transform rotate-12"><i class="fas fa-map-marked-alt text-9xl"></i></div>
                            <div class="relative z-10 grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-1">Tanggal & Waktu</p>
                                        <div class="flex items-center gap-2 text-gray-800 font-medium">
                                            <i class="far fa-calendar-alt text-blue-600 w-4"></i> 
                                            <span class="font-bold text-blue-700">06 Juli 2026</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-gray-800 font-medium mt-1">
                                            <i class="far fa-clock text-blue-600 w-4"></i> 
                                            <span class="font-bold text-gray-800">08:00 WIB</span>
                                            <span class="italic text-red-500 font-normal text-[11px ml-1">*Harap hadir 30 menit sebelumnya</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-1">Lokasi Ujian</p>
                                        <div class="flex items-start gap-2 text-gray-800 font-medium">
                                            <i class="fas fa-map-marker-alt text-red-500 w-4 mt-1"></i> 
                                            <span>
                                                <span class="font-bold text-amber-600">Gedung A, Lab Ujian CBT</span><br>
                                                <span class="text-xs text-gray-500 font-normal">Universitas Buana Perjuangan Karawang</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                
                        @if($moodle)
                            <div class="mt-6 bg-blue-50/50 border border-blue-200 rounded-2xl p-5 flex flex-col sm:flex-row items-center gap-4 transition-all hover:shadow-md">
                                <div class="w-12 h-12 bg-blue-100/80 text-blue-600 rounded-xl flex items-center justify-center shrink-0 shadow-sm group-hover:scale-105 transition-transform">
                                    <i class="fas fa-user-check text-xl"></i>
                                </div>
                                <div class="text-center sm:text-left flex-1">
                                    <h4 class="text-sm font-bold text-gray-800 flex items-center justify-center sm:justify-start gap-2">
                                        Akun E-Learning CBT 
                                        <span class="bg-emerald-100 text-emerald-700 text-[9px] uppercase tracking-wider font-extrabold px-2 py-0.5 rounded-md shadow-sm animate-pulse">Aktif</span>
                                    </h4>
                                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                                        Gunakan informasi akun berikut untuk login ke sistem ujian CBT online:
                                    </p>
                                    
                                    <div class="mt-3 flex flex-wrap gap-x-6 gap-y-2 justify-center sm:justify-start">
                                        <div class="text-xs">
                                            <span class="text-gray-400">Username Ujian:</span> 
                                            <strong class="text-gray-700 select-all font-mono tracking-wide bg-white px-2 py-0.5 border border-slate-200 rounded-md shadow-sm ml-1">{{ $moodle->username }}</strong>
                                        </div>
                                        <div class="text-xs">
                                            <span class="text-gray-400">Password Ujian:</span> 
                                            <strong class="text-gray-700 select-all font-mono tracking-wide bg-white px-2 py-0.5 border border-slate-200 rounded-md shadow-sm ml-1">{{ $moodle->password_raw ?? 'Ubp2026*' }}</strong>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="w-full sm:w-auto pt-2 sm:pt-0 shrink-0">
                                    <a href="https://elearning2.ubpkarawang.ac.id/mod/quiz/view.php?id=2" target="_blank" 
                                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-sm hover:shadow-md transition-all">
                                        <i class="fas fa-external-link-alt text-[10px]"></i> Buka CBT
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="mt-6 bg-gray-50/80 border-2 border-dashed border-gray-200 rounded-xl p-5 flex flex-col sm:flex-row items-center gap-4 transition-all">
                                <div class="w-12 h-12 bg-gray-200 text-gray-400 rounded-full flex items-center justify-center shrink-0 shadow-inner">
                                    <i class="fas fa-user-lock text-xl"></i>
                                </div>
                                <div class="text-center sm:text-left flex-1">
                                    <h4 class="text-sm font-bold text-gray-700 flex items-center justify-center sm:justify-start gap-2">
                                        Akun E-Learning CBT 
                                        <span class="bg-gray-200 text-gray-600 text-[9px] uppercase tracking-wider font-bold px-2 py-0.5 rounded-md">Terkunci</span>
                                    </h4>
                                    <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                                        Informasi login (Username & Password) untuk mengakses ujian CBT akan diterbitkan oleh Admin. Silakan pantau halaman ini secara berkala mendekati tanggal ujian.
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- ---------------------------------------------------------
                 AREA STEP 3 WAWANCARA
                 --------------------------------------------------------- --}}
            <div x-show="activeTab === 3"
                 style="display: none;" 
                 x-transition:enter="transition ease-out duration-500 delay-200" 
                 x-transition:enter-start="opacity-0 translate-y-8 scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100" 
                 x-transition:leave-end="opacity-0 -translate-y-8 scale-95"
                 class="space-y-6">
                 
                 <div class="bg-white/95 backdrop-blur-md p-6 sm:p-8 rounded-2xl shadow-xl border border-white/20">
                     <div class="flex items-start justify-between gap-4">
                         <div class="flex-1">
                             <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                 Tahap 3: Jalur Wawancara 
                                 @if($isWawancaraSelesai)
                                     @if(in_array(strtolower($statusWawancara), ['lulus', 'lolos']))
                                         <span class="bg-emerald-100 text-emerald-700 text-[10px] uppercase tracking-wider font-extrabold px-2 py-0.5 rounded-full border border-emerald-200 shadow-sm">Lulus Seleksi</span>
                                     @else
                                         <span class="bg-red-100 text-red-700 text-[10px] uppercase tracking-wider font-extrabold px-2 py-0.5 rounded-full border border-red-200 shadow-sm">Tidak Lulus</span>
                                     @endif
                                 @else
                                     <span class="bg-blue-100 text-blue-700 text-[10px] uppercase tracking-wider font-extrabold px-2 py-0.5 rounded-full border border-blue-200 shadow-sm">Offline</span>
                                 @endif
                             </h3>
                             <p class="text-sm text-gray-600 mt-1">
                                 @if($isWawancaraSelesai)
                                     @if(in_array(strtolower($statusWawancara), ['lulus', 'lolos']))
                                         Selamat! Anda dinyatakan **LULUS** pada tahap seleksi wawancara. Silakan melanjutkan ke tahap Pembayaran UKT.
                                     @else
                                         Mohon maaf, Anda dinyatakan **TIDAK LULUS** pada tahap seleksi wawancara program profesi ini.
                                     @endif
                                 @else
                                     Selamat! Anda telah melewati tahap Ujian CBT. Silakan persiapkan diri Anda untuk hadir pada jadwal wawancara berikut.
                                 @endif
                             </p>
                         </div>
                         <div class="hidden sm:flex w-12 h-12 bg-blue-50 text-blue-600 rounded-xl items-center justify-center border border-blue-100 shadow-sm">
                             <i class="fas fa-comments text-xl"></i>
                         </div>
                     </div>

                     @if(!$isWawancaraSelesai)
                         <div class="mt-6 bg-slate-50 border border-slate-200 rounded-xl p-4 sm:p-5 relative overflow-hidden">
                             <div class="absolute -right-6 -top-6 text-slate-100 opacity-60 transform -rotate-12"><i class="fas fa-user-tie text-9xl"></i></div>
                             <div class="relative z-10 grid grid-cols-1 md:grid-cols-2 gap-5">
                                 <div class="space-y-4">
                                     <div>
                                         <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-1">Tanggal Pelaksanaan</p>
                                         <div class="flex items-center gap-2 text-gray-800 font-medium">
                                             <i class="far fa-calendar-alt text-blue-600 w-4"></i> 
                                             <span class="font-bold text-blue-700">18 Juni 2026</span>
                                         </div>
                                         <div class="flex items-center gap-2 text-gray-800 font-medium mt-1">
                                             <i class="far fa-clock text-blue-600 w-4"></i> 
                                             <span class="font-bold text-gray-800">08:00 WIB</span>
                                             <span class="italic text-red-500 font-normal text-[11px ml-1">*Harap hadir 30 menit sebelumnya</span>
                                         </div>
                                     </div>
                                 </div>
                                 <div class="space-y-4">
                                     <div>
                                         <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-1">Lokasi Wawancara</p>
                                         <div class="flex items-start gap-2 text-gray-800 font-medium">
                                             <i class="fas fa-map-marker-alt text-red-500 w-4 mt-1"></i> 
                                             <span>
                                                 <span class="font-bold text-amber-600">Gedung D</span><br>
                                                 <span class="text-xs text-gray-500 font-normal">Universitas Buana Perjuangan Karawang</span>
                                             </span>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                         </div>
                     @else
                         <div class="mt-6 p-5 rounded-2xl border flex items-center gap-4 {{ in_array(strtolower($statusWawancara), ['lulus', 'lolos']) ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800' }}">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 {{ in_array(strtolower($statusWawancara), ['lulus', 'lolos']) ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600' }}">
                                <i class="fas {{ in_array(strtolower($statusWawancara), ['lulus', 'lolos']) ? 'fa-award text-xl' : 'fa-heart-broken text-xl' }}"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold">Informasi Kelulusan Tahap 3</h4>
                                <p class="text-xs mt-1 opacity-90 leading-relaxed">
                                    @if(in_array(strtolower($statusWawancara), ['lulus', 'lolos']))
                                        Keputusan panitia seleksi bersifat mutlak. Tagihan biaya perkuliahan (UKT Profesi) Anda sudah diterbitkan pada menu <strong>Bayar UKT</strong> di atas. Silakan lakukan penyelesaian administratif.
                                    @else
                                        Terima kasih telah berpartisipasi dalam rangkaian seleksi PMB Universitas Buana Perjuangan Karawang. Tetap semangat dan jangan patah arang!
                                    @endif
                                </p>
                            </div>
                        </div>
                     @endif
                 </div>
            </div>

            {{-- ---------------------------------------------------------
                 AREA STEP 4 PEMBAYARAN UKT
                 --------------------------------------------------------- --}}
            <div x-show="activeTab === 4"
                 style="display: none;" 
                 x-transition:enter="transition ease-out duration-500 delay-200" 
                 x-transition:enter-start="opacity-0 translate-y-8 scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100" 
                 x-transition:leave-end="opacity-0 -translate-y-8 scale-95"
                 class="space-y-6">
                 
                 <div class="bg-white/95 backdrop-blur-md p-6 sm:p-8 rounded-2xl shadow-xl border border-white/20">
                     <div class="flex items-start justify-between gap-4">
                         <div class="flex-1">
                             <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                 Tahap 4: Pembayaran UKT Profesi 
                                 @if($isBayarLunas)
                                     <span class="bg-emerald-100 text-emerald-700 text-[10px] uppercase tracking-wider font-extrabold px-2 py-0.5 rounded-full border border-emerald-200 shadow-sm">Lunas</span>
                                 @else
                                     <span class="bg-amber-100 text-amber-700 text-[10px] uppercase tracking-wider font-extrabold px-2 py-0.5 rounded-full border border-amber-200 shadow-sm">Belum Lunas</span>
                                 @endif
                             </h3>
                             <p class="text-sm text-gray-600 mt-1">Selesaikan kewajiban administrasi UKT (Uang Kuliah Tunggal) untuk mendapatkan Nomor Induk Mahasiswa (NIM).</p>
                         </div>
                         <div class="hidden sm:flex w-12 h-12 bg-blue-50 text-blue-600 rounded-xl items-center justify-center border border-blue-100 shadow-sm">
                             <i class="fas fa-file-invoice-dollar text-xl"></i>
                         </div>
                     </div>

                     @if(!$isBayarLunas)
                         <div class="mt-6 flex flex-col sm:flex-row items-center gap-3">
                            <a href="{{ route('profesi.reviewpayment') }}" class="w-full sm:w-auto px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl flex items-center justify-center gap-2 shadow-md transition-all hover:-translate-y-0.5 shadow-[0_4px_14px_0_rgba(37,99,235,0.39)]">
                                <i class="fas fa-wallet"></i> Lihat & Bayar Tagihan UKT
                            </a>
                            <p class="text-xs text-gray-500 text-center sm:text-left mt-2 sm:mt-0">
                                <i class="fas fa-info-circle text-blue-500 mr-1"></i> Transaksi menggunakan Virtual Account Mandiri.
                            </p>
                        </div>
                     @else
                         <div class="mt-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center gap-4 text-emerald-800">
                             <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center shrink-0">
                                 <i class="fas fa-check-double text-lg"></i>
                             </div>
                             <div>
                                 <p class="text-sm font-bold">Pembayaran Terverifikasi</p>
                                 <p class="text-xs mt-0.5 opacity-90">Terima kasih, tagihan UKT Anda telah lunas. Silakan menuju tahap akhir (Selesai).</p>
                             </div>
                         </div>
                     @endif
                 </div>
            </div>

            {{-- ==========================================
                 STEP 5: FINALISASI & DATA PDDIKTI
                 ========================================== --}}
            <div class="bg-white rounded-2xl shadow-sm border {{ $currentStep == 5 ? 'border-blue-400 ring-1 ring-blue-400' : 'border-gray-200 opacity-75' }} p-6 transition-all relative overflow-hidden">
                
                {{-- Kalo Step belum kebuka (Masih ke-lock) --}}
                @if($currentStep < 5)
                    <div class="absolute inset-0 bg-gray-50/80 backdrop-blur-[2px] flex items-center justify-center z-10">
                        <div class="bg-white px-4 py-2 rounded-full shadow-sm border border-gray-200 text-gray-500 font-medium text-sm flex items-center">
                            <i class="fas fa-lock mr-2"></i> Selesaikan pembayaran UKT terlebih dahulu
                        </div>
                    </div>
                @endif
            
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 shrink-0 rounded-full flex items-center justify-center font-bold text-lg 
                        {{ $isDataFinalAdaDiDB ? 'bg-emerald-100 text-emerald-600' : ($currentStep == 5 ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-400') }}">
                        @if($isDataFinalAdaDiDB)
                            <i class="fas fa-check"></i>
                        @else
                            5
                        @endif
                    </div>
                    
                    <div class="flex-1 pt-1">
                        <h3 class="text-lg font-bold text-gray-900">Finalisasi & Data PDDIKTI</h3>
                        
                        {{-- LOGIKA TAMPILAN BERDASARKAN STATUS PDDIKTI --}}
                        @if($currentStep >= 5)
                            
                            @if(!$isDataFinalAdaDiDB)
                                {{-- STATE A: BELUM ISI DATA PDDIKTI --}}
                                <p class="text-gray-600 text-sm mt-1 mb-4">Pembayaran UKT berhasil. Langkah terakhir, silakan lengkapi biodata final Anda untuk keperluan pelaporan PDDIKTI Kemdikbud.</p>
                                
                                <a href="{{ route('profesi.data-final.create') }}" class="inline-flex items-center justify-center px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-md transition-colors">
                                    <i class="fas fa-edit mr-2"></i> Lengkapi Data PDDIKTI Sekarang
                                </a>
                            @else
                                {{-- STATE B: SUDAH ISI DATA PDDIKTI (SELESAI) --}}
                                <p class="text-emerald-600 text-sm font-medium mt-1 mb-3"><i class="fas fa-check-circle mr-1"></i> Data PDDIKTI telah berhasil difinalisasi.</p>
                                
                                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 mt-2">
                                    <h4 class="text-sm font-bold text-gray-800 mb-2">Pendaftaran Selesai! 🎉</h4>
                                    <p class="text-xs text-gray-600 mb-3">Selamat, Anda telah resmi terdaftar sebagai mahasiswa {{ $namaProfesi }}. Berikut adalah informasi akun Anda:</p>
                                    
                                    <div class="space-y-2">
                                        <div class="flex justify-between items-center bg-white p-2 rounded-lg border border-gray-100">
                                            <span class="text-xs text-gray-500 font-medium">NIM</span>
                                            <span class="text-sm font-bold text-gray-900">{{ $dataAkun->nim ?? 'Sedang digenerate...' }}</span>
                                        </div>
                                        <div class="flex justify-between items-center bg-white p-2 rounded-lg border border-gray-100">
                                            <span class="text-xs text-gray-500 font-medium">Email Kampus</span>
                                            <span class="text-sm font-bold text-gray-900">{{ $dataAkun->email_kampus ?? 'Sedang digenerate...' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                        @else
                            <p class="text-gray-500 text-sm mt-1">Lengkapi form pelaporan PDDIKTI dan dapatkan NIM Anda.</p>
                        @endif
                    </div>
                </div>
            </div>

        </div> {{-- AKHIR DARI WADAH DINAMIS STEPPER (ALPINE.JS) --}}

    </div>
</div>
@endsection