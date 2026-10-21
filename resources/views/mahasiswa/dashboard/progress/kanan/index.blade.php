{{-- 
    FILE: resources/views/mahasiswa/dashboard/kanan/index.blade.php 
    DESKRIPSI: Panel Kanan Dashboard (Status & Aksi) - ANIMATED VERSION
--}}

@php
    // --- SETUP VARIABLES (JANGAN UBAH) ---
    $currentStep     = $currentStep ?? 1;
    $ereg            = $ereg ?? null;
    $biodataSelesai  = $biodataSelesai ?? false; 
    $isUjianSelesai  = $isUjianSelesai ?? false;
    $jurusanDiterima = $jurusanDiterima ?? '-'; // 
    $moodleUsername  = $moodleUsername ?? '-';
    $ujianPassword   = $ujianPassword ?? '';

    // ✅ FIX: Deteksi apakah jurusan sudah berubah via selisih
    $lunasSelisih         = $lunasSelisih ?? null;
    $tagihanSelisihAktif  = $tagihanSelisihAktif ?? null;
    $isPindahProdi        = !is_null($lunasSelisih);
    $isPindahProdiPending = !is_null($tagihanSelisihAktif);

    $realPassword        = 'Ubp2026*'; 
    $isDataFinalSelesai  = ($ereg && $ereg->step_completed >= 5);

    $stepNames = [
        1 => 'Akun Pendaftar',
        2 => 'Data Diri & Berkas',
        3 => 'Ujian Seleksi',
        4 => 'Pembayaran UKT',
        5 => 'Data Final (LDDIKTI)',
        6 => 'Portal Mahasiswa'
    ];
    $currentStepName = $stepNames[$currentStep] ?? 'Proses Pendaftaran';
@endphp

{{-- STYLE TAMBAHAN --}}
<style>
    .tech-grid-bg {
        background-image: linear-gradient(to right, rgba(0,0,0,0.05) 1px, transparent 1px), linear-gradient(to bottom, rgba(0,0,0,0.05) 1px, transparent 1px);
        background-size: 20px 20px;
    }
    .tech-circuit-bg {
        background-image: radial-gradient(circle at center, rgba(255,255,255,0.1) 0%, transparent 70%), repeating-linear-gradient(45deg, rgba(255,255,255,0.05) 0px, rgba(255,255,255,0.05) 2px, transparent 2px, transparent 10px);
    }
    .border border-slate-200/50 shadow-[0_0_30px_rgba(0,0,0,0.5)] {
        border: 1px solid #e2e8f0;
        box-shadow: 0 0 0 1px rgba(255,255,255,0.5) inset;
    }
    
    @keyframes slideUpFade {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-slide-up {
        animation: slideUpFade 0.5s ease-out forwards;
    }
</style>

<div id="step-content-anchor" 
     class="md:pr-2 custom-scrollbar flex-grow text-gray-800 pb-24 md:pb-0 font-sans"
     x-data="{ mounted: false }"
     x-init="
        setTimeout(() => mounted = true, 100);

        // --- LOGIC AUTO SCROLL MOBILE ---
        $watch('activeView', (value) => {
            // Cek apakah layar mobile (lebar < 768px)
            if (window.innerWidth < 768) {
                setTimeout(() => {
                    const element = document.getElementById('step-content-anchor');
                    if (element) {
                        // Scroll halus ke elemen ini, tapi kasih jarak sedikit dari atas (margin)
                        const y = element.getBoundingClientRect().top + window.scrollY - 100;
                        window.scrollTo({top: y, behavior: 'smooth'});
                    }
                }, 150); // Delay 150ms agar animasi slide-up selesai dulu
            }
        });
     ">
     
     
    
    {{-- ========================================== --}}
    {{-- 1. HERO CARD (ANIMATED ENTRANCE)           --}}
    {{-- ========================================== --}}
    <div id="tour-hero" 
         class="mb-8 transition-all duration-700 ease-out transform"
         :class="mounted ? 'opacity-100 translate-y-0' : 'opacity-0 -translate-y-4'">
         
        {{-- LANGSUNG KONTEN --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4">
            <div>
                <p class="font-mono text-xs text-gray-500 uppercase tracking-wider drop-shadow-md">>>> Lanjutkan proses pendaftaran.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if(!empty($kodeDaftar) && $kodeDaftar !== '-')
                    <div class="flex items-center gap-1.5 bg-blue-950/50 text-blue-200 px-3 py-1.5 rounded-lg border border-blue-500/30 backdrop-blur-sm shadow-[0_0_15px_rgba(59,130,246,0.3)]" title="Nomor / Kode Pendaftaran">
                        <i class="fas fa-id-card text-xs text-blue-600"></i>
                        <span class="text-xs font-semibold text-blue-700">Kode Daftar:</span>
                        <span class="font-mono text-xs font-black text-gray-800 select-all tracking-wider">{{ $kodeDaftar }}</span>
                    </div>
                @endif
                <div class="flex items-center gap-1.5 bg-slate-900/50 text-gray-600 px-3 py-1.5 rounded-lg border border-slate-200/50 backdrop-blur-sm shadow-[0_0_15px_rgba(0,0,0,0.5)]" title="Username / Akun Pendaftar">
                    <i class="fas fa-user-circle text-xs text-gray-500"></i>
                    <span class="text-xs font-semibold text-gray-500">Username:</span>
                    <span class="font-mono text-xs font-bold text-slate-700 select-all">{{ Auth::user()->email ?? Auth::user()->phone }}</span>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-xl shadow-xl {{ $currentStep >= 6 ? 'bg-gradient-to-r from-emerald-950/80 to-green-950/80 backdrop-blur-xl border border-emerald-500/30 shadow-[0_0_40px_rgba(16,185,129,0.3)]' : 'bg-gradient-to-r from-blue-950/80 to-indigo-950/80 backdrop-blur-xl border border-blue-500/30 shadow-[0_0_40px_rgba(59,130,246,0.3)]' }} text-gray-800 p-6 md:p-8 border-2 border-white/20 transition-all duration-500 hover:shadow-2xl group">
            
            {{-- Background Tech Decoration --}}
            <div class="absolute inset-0 tech-circuit-bg opacity-30 group-hover:opacity-40 transition-opacity duration-700"></div>
            
            {{-- Orbs (Animated) --}}
            <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-white opacity-10 blur-3xl animate-pulse"></div>
            <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-white opacity-10 blur-3xl animate-pulse" style="animation-delay: 1s;"></div>

            <div class="relative z-10 flex justify-between items-center">
                <div class="w-full md:w-2/3">
                    @if($currentStep >= 6)
                        <h2 class="text-3xl font-black mb-2 tracking-tight animate-bounce">SELAMAT! 🎉</h2>
                        <p class="text-green-100 mb-6 font-mono text-sm">>> Seluruh tahapan pendaftaran selesai.</p>
                    @else
                        <p class="text-blue-100 text-[10px] font-mono uppercase tracking-[0.2em] font-bold mb-1 border-l-2 border-blue-300 pl-2">Tahapan Saat Ini</p>
                        <h2 class="text-2xl md:text-3xl font-black mb-2 tracking-tight">{{ $currentStepName }}</h2>
                        <p class="text-blue-100 text-sm mb-6 opacity-90 font-mono flex items-center gap-2">
                            <i class="fas fa-info-circle"></i> Langkah <span class="font-bold bg-white/20 px-2 py-0.5 rounded-sm">{{ $currentStep }}</span> dari 6
                        </p>
                        {{-- Progress Bar Hero --}}
                        <div class="w-full bg-black/30 rounded-sm h-3 backdrop-blur-md border border-white/10 p-0.5">
                            <div class="bg-white h-full rounded-sm transition-all duration-1000 ease-out shadow-[0_0_10px_rgba(255,255,255,0.7)] relative overflow-hidden" 
                                 style="width: 0%"
                                 x-init="setTimeout(() => $el.style.width = '{{ ($currentStep / 6) * 100 }}%', 500)">
                                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent animate-pulse w-1/2 h-full transform -skew-x-12"></div>
                            </div>
                        </div>
                    @endif
                </div>
                {{-- Hero Icon Big --}}
                <div class="hidden md:flex items-center justify-center w-24 h-24 bg-white/10 rounded-xl border-2 border-white/30 backdrop-blur-md shadow-lg transform rotate-3 hover:rotate-0 transition-all duration-500 hover:scale-110">
                    @if($currentStep == 1) <i class="fas fa-user-circle text-4xl"></i>
                    @elseif($currentStep == 2) <i class="fas fa-id-card text-4xl"></i>
                    @elseif($currentStep == 3) <i class="fas fa-pen-nib text-4xl"></i>
                    @elseif($currentStep == 4) <i class="fas fa-credit-card text-4xl"></i>
                    @elseif($currentStep == 5) <i class="fas fa-database text-4xl"></i>
                    @else <i class="fas fa-check-circle text-5xl text-green-200"></i>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ======================================================================= --}}
    {{-- CONTENT PER STEP (ANIMATED SWITCHING)                                   --}}
    {{-- Menggunakan class 'animate-slide-up' setiap kali template di-render     --}}
    {{-- ======================================================================= --}}

    {{-- STEP 1: AKUN PENDAFTAR --}}
    <template x-if="activeView == 1">
        <div class="animate-slide-up">
            <div class="bg-white border border-slate-200/50 shadow-[0_0_30px_rgba(0,0,0,0.5)] rounded-lg p-6 shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow duration-300">
                <div class="absolute inset-0 tech-grid-bg opacity-50 pointer-events-none"></div>
                <div class="relative z-10">
                    <div class="flex items-center gap-4 mb-6 border-b border-slate-200/50 pb-4">
                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-lg border border-blue-200 flex items-center justify-center text-xl shadow-sm group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800 text-lg uppercase tracking-tight">Akun Terverifikasi</h4>
                            <p class="text-[10px] font-mono text-gray-500 uppercase tracking-wider">>> Status akun: AKTIF</p>
                        </div>
                    </div>

                    <div class="bg-slate-50/50 p-4 rounded-lg border border-slate-200 space-y-3 mb-6 font-mono text-sm">
                        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-1">
                            <span class="text-[10px] text-gray-500 uppercase font-bold tracking-wider"><i class="fas fa-caret-right text-blue-600 mr-1"></i> NAMA LENGKAP</span>
                            <span class="font-bold text-gray-800 bg-white border border-gray-200 px-2 py-1 rounded-md">{{ Auth::user()->name }}</span>
                        </div>
                        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-1">
                            <span class="text-[10px] text-gray-500 uppercase font-bold tracking-wider"><i class="fas fa-caret-right text-blue-600 mr-1"></i> EMAIL</span>
                            <span class="font-bold text-gray-800 bg-white border border-gray-200 px-2 py-1 rounded-md">{{ Auth::user()->email }}</span>
                        </div>
                        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-1">
                            <span class="text-[10px] text-gray-500 uppercase font-bold tracking-wider"><i class="fas fa-caret-right text-blue-600 mr-1"></i> NO. HP</span>
                            <span class="font-bold text-gray-800 bg-white border border-gray-200 px-2 py-1 rounded-md">{{ Auth::user()->masterereg->phone ?? '-' }}</span>
                        </div>
                    </div>

                    @if($currentStep > 1)
                        <button @click="activeView = 2; window.scrollTo({ top: 0, behavior: 'smooth' })" class="w-full py-3 rounded-lg bg-blue-600 text-gray-800 font-bold text-sm shadow-md hover:bg-blue-700 transition flex items-center justify-center gap-2 uppercase tracking-wider border-b-4 border-blue-800 hover:border-blue-900 hover:translate-y-[1px] active:border-b-0 active:translate-y-[4px]">
                            Lanjut ke Data Diri <i class="fas fa-arrow-right"></i>
                        </button>
                    @else
                        <button disabled class="w-full py-3 rounded-lg bg-green-50 text-green-700 font-bold text-sm flex items-center justify-center gap-2 uppercase tracking-wider border border-green-200 cursor-not-allowed opacity-80">
                            <i class="fas fa-check-square"></i> Akun Aktif
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </template>

    {{-- STEP 2: DATA DIRI & BERKAS --}}
    <template x-if="activeView == 2">
        <div class="animate-slide-up space-y-5"
         x-init="
            setTimeout(() => {
                if (window.runUploadTour && document.getElementById('tour-upload-btn')) {
                    window.runUploadTour();
                }
            }, 800);
         ">
            <div class="flex items-center justify-between border-b border-slate-200/50 pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 border border-blue-100 shadow-sm">
                        <i class="fas fa-fingerprint"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-mono text-gray-500 uppercase tracking-wider block">Module 02</span>
                        <h3 class="font-bold text-slate-800">Identity Data</h3>
                    </div>
                </div>
                @if($biodataSelesai)
                    <span class="px-2 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-bold uppercase tracking-wider rounded border border-emerald-100">
                        <i class="fas fa-check mr-1"></i> Completed
                    </span>
                @else
                    <span class="px-2 py-1 bg-amber-50 text-amber-600 text-[10px] font-bold uppercase tracking-wider rounded border border-amber-100 animate-pulse">
                        Action Required
                    </span>
                @endif
            </div>
            
            <div class="relative w-full overflow-hidden rounded-lg bg-slate-50 border border-slate-200/50 shadow-[0_0_30px_rgba(0,0,0,0.5)] p-4 shadow-sm group">
                <div class="absolute inset-0 tech-grid-bg opacity-30 pointer-events-none"></div>
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-amber-400"></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-xs font-bold text-slate-700 uppercase tracking-widest flex items-center gap-2">
                            <i class="fas fa-info-circle text-amber-500"></i> Important Protocols
                        </h4>
                    </div>
                    <div class="grid grid-cols-1 gap-2">
                        <div class="flex items-start gap-3 bg-white p-2.5 rounded border border-slate-200/50 shadow-sm hover:shadow-md transition-shadow">
                            <div class="shrink-0 text-blue-600 mt-0.5"><i class="fas fa-check-square"></i></div>
                            <div>
                                <span class="block text-[9px] font-bold text-gray-500 uppercase tracking-wider">VALIDITY</span>
                                <span class="text-xs font-mono font-bold text-slate-700">Data diri wajib sesuai KTP/Ijazah.</span>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 bg-white p-2.5 rounded border border-slate-200/50 shadow-sm hover:shadow-md transition-shadow">
                            <div class="shrink-0 text-blue-600 mt-0.5"><i class="fas fa-file-contract"></i></div>
                            <div>
                                <span class="block text-[9px] font-bold text-gray-500 uppercase tracking-wider">QUALITY</span>
                                <span class="text-xs font-mono font-bold text-slate-700">Scan dokumen harus jelas & terbaca.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @if(!$biodataSelesai)
                <div class="text-center py-4 bg-blue-50/30 border border-blue-100 rounded-lg border-dashed">
                    <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wide mb-1">Data Incomplete</h4>
                    <p class="text-xs text-slate-500 mb-4 max-w-xs mx-auto">Silakan isi formulir biodata terlebih dahulu.</p>
                    <a href="{{ route('mahasiswa.biodata.create') }}" class="w-full py-3 rounded-lg bg-blue-600 text-gray-800 font-bold text-sm shadow-md hover:bg-blue-700 transition flex items-center justify-center gap-2 uppercase tracking-wider border-b-4 border-blue-800 hover:border-blue-900 hover:translate-y-[1px] active:border-b-0 active:translate-y-[4px]">
                        Mulai Pengisian Data <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            @else
                <div class="bg-white border border-slate-200/50 shadow-[0_0_30px_rgba(0,0,0,0.5)] rounded-lg p-5 shadow-sm relative overflow-hidden text-center">
                    <div class="relative z-10">
                        <div class="w-12 h-12 bg-green-100 text-green-600 rounded-lg border-2 border-green-200 flex items-center justify-center mb-3 shadow-sm mx-auto">
                            <i class="fas fa-check-double text-xl"></i>
                        </div>
                        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-tight">Biodata Tersimpan</h3>
                        <p class="font-mono text-[10px] text-gray-500 mt-1 mb-4 uppercase tracking-wider">>> Status: Data dasar aman.</p>
                        
                        @if(isset($sudahUjian) && $sudahUjian) 
                            <div class="bg-slate-50 text-slate-700 p-3 rounded-lg text-xs font-medium text-left flex items-start gap-3 mb-4 border border-slate-200 shadow-sm">
                                <i class="fas fa-lock mt-0.5 text-slate-500 text-base"></i>
                                <div>
                                    <span class="block font-bold uppercase tracking-wider text-slate-800 text-[10px] mb-0.5">Status Terkunci</span>
                                    Anda sudah melakukan ujian, jadi sudah tidak bisa mengubah Program Studi lagi.
                                </div>
                            </div>
                        @else
                            <div class="bg-rose-50 text-rose-700 p-3 rounded-lg text-xs font-medium text-left flex items-start gap-3 mb-4 border border-rose-200 shadow-sm">
                                <i class="fas fa-exclamation-triangle mt-0.5 text-rose-500 text-base"></i>
                                <div>
                                    <span class="block font-bold uppercase tracking-wider text-rose-800 text-[10px] mb-0.5">Peringatan Permanen</span>
                                    Pastikan pilihan Program Studi sudah benar. <span class="font-bold underline decoration-rose-400 underline-offset-2">Program Studi tidak dapat diubah lagi</span> setelah Anda mengikuti ujian.
                                </div>
                            </div>
                        @endif
                        @if($ereg && ($ereg->status == 'completed' || $ereg->status == 'final'))
                            <div class="bg-green-50 text-green-800 p-2 rounded text-xs font-mono mb-3 border border-green-200">
                                <i class="fas fa-check-circle"></i> ALL REQUIREMENTS MET
                            </div>
                            
                            {{-- Tombol Lanjut ke Ujian mungkin juga perlu disembunyikan/diubah jika $sudahUjian bernilai true --}}
                            @if(!isset($sudahUjian) || !$sudahUjian)
                                <button @click="activeView = 3; window.scrollTo({ top: 0, behavior: 'smooth' })" class="w-full py-3 rounded-lg bg-slate-800 hover:bg-slate-900 text-gray-800 font-bold text-sm shadow-md transition uppercase tracking-wider border-b-4 border-black flex items-center justify-center gap-2 hover:translate-y-[1px] active:border-b-0 active:translate-y-[4px]">
                                    Lanjut ke Ujian <i class="fas fa-chevron-right"></i>
                                </button>
                            @else
                                <button class="w-full py-3 rounded-lg bg-slate-200 text-slate-500 font-bold text-sm shadow-sm uppercase tracking-wider border-b-4 border-slate-300 flex items-center justify-center gap-2 cursor-not-allowed" disabled>
                                    Ujian Telah Selesai <i class="fas fa-check"></i>
                                </button>
                            @endif

                        @else
                            <div class="w-full bg-amber-50 text-amber-800 p-3 rounded-lg text-xs font-medium text-left flex items-start gap-2 mb-4 border-l-4 border-amber-400 font-mono">
                                <i class="fas fa-exclamation-circle mt-0.5"></i>
                                <span class="uppercase tracking-wider">Perhatian: Silakan pilih Program Studi & Upload Berkas.</span>
                            </div>
                            <a id="tour-upload-btn" href="{{ route('mahasiswa.registration') }}" class="w-full py-3 rounded-lg bg-blue-600 text-gray-800 font-bold text-sm shadow-md hover:bg-blue-700 transition flex items-center justify-center gap-2 uppercase tracking-wider border-b-4 border-blue-800 hover:translate-y-[1px] active:border-b-0 active:translate-y-[4px]">
                                Lanjut Upload Berkas <i class="fas fa-file-upload"></i>
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </template>

    
    {{-- STEP 3: UJIAN SELEKSI --}}
    <template x-if="activeView == 3">
        <div class="animate-slide-up"
         x-init="
            setTimeout(() => {
                if (window.runExamTour) window.runExamTour();
            }, 500);
         ">
            
            {{-- HEADER STEP --}}
            <div class="flex items-center gap-3 mb-5 px-1 border-b border-slate-200/50 pb-3">
                <div class="h-10 w-10 rounded-lg bg-blue-600 text-gray-800 flex items-center justify-center shadow-md border-2 border-blue-400">
                    <i class="fas fa-laptop-code text-sm"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800 uppercase tracking-tight">Ujian Seleksi</h2>
                    <p class="font-mono text-[10px] text-slate-500 uppercase tracking-wider">>> Portal seleksi mahasiswa baru</p>
                </div>
            </div>
            
            @if($currentStep >= 3)
                @php
                    $rawPhone = Auth::user()->phone ?? Auth::user()->no_hp ?? '';
                    $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
                    if (substr($cleanPhone, 0, 2) === '62') $cleanPhone = '0' . substr($cleanPhone, 2);
                    $realPassword = 'Ubp2026*';
                    $displayUsername = $moodleUsername ?? '-';

                    // Cek Farmasi untuk Banner Info
                    $isFarmasi = false;
                    if (isset($ereg)) {
                        $pilihan1 = strtoupper($ereg->jurusan1 ?? '');
                        $pilihan2 = strtoupper($ereg->jurusan2 ?? '');
                        if (str_contains($pilihan1, 'FARMASI') || str_contains($pilihan2, 'FARMASI')) {
                            $isFarmasi = true;
                        }
                    }
                @endphp
                
                {{-- BANNER INFO KHUSUS FARMASI (Hanya Muncul Jika Farmasi) --}}
                @if($isFarmasi && isset($jadwalUjianFarmasi) && $jadwalUjianFarmasi)
                <div class="mb-5 bg-gradient-to-r from-emerald-50 to-teal-50 border-l-4 border-emerald-500 rounded-r-lg p-4 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-2 opacity-10 pointer-events-none"><i class="fas fa-flask text-6xl text-emerald-800 -mr-2 -mt-2"></i></div>
                    <div class="flex items-start gap-3 relative z-10">
                        <div class="text-emerald-600 mt-1"><i class="fas fa-info-circle text-xl animate-pulse"></i></div>
                        <div>
                            <h4 class="font-bold text-emerald-800 text-sm uppercase tracking-wide mb-1">Jadwal Ujian Saringan Farmasi</h4>
                            <p class="text-xs text-emerald-700 font-medium mb-2">Wajib bagi calon mahasiswa Farmasi yang telah Lulus Seleksi Tahap 1:</p>
                            
                            {{-- DATA DINAMIS --}}
                            <div class="bg-white/60 rounded border border-emerald-200 p-3 text-xs text-slate-700 grid grid-cols-1 sm:grid-cols-2 gap-y-2 gap-x-4">
                                <div class="flex items-center gap-2">
                                    <i class="far fa-calendar-alt text-emerald-500 w-4"></i> 
                                    <span class="font-bold">Tanggal:</span> {{ $jadwalUjianFarmasi->tanggal_indo }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="far fa-clock text-emerald-500 w-4"></i> 
                                    <span class="font-bold">Waktu:</span> {{ $jadwalUjianFarmasi->waktu_format }} WIB
                                </div>
                                <div class="flex items-center gap-2 sm:col-span-2">
                                    <i class="fas fa-map-marker-alt text-emerald-500 w-4"></i> 
                                    <span><span class="font-bold">Lokasi:</span> {{ $jadwalUjianFarmasi->deskripsi }}</span>
                                </div>
                            </div>

                            <div class="mt-3 flex items-center gap-2">
                                <a href="https://wa.me/6289612500504" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 text-gray-800 text-[10px] font-bold uppercase rounded shadow hover:bg-emerald-700 transition"><i class="fab fa-whatsapp text-sm"></i> Konfirmasi Kehadiran</a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
                @if(isset($isUjianSelesai) && $isUjianSelesai)
                    <div class="mb-5 bg-slate-50 text-slate-700 p-4 rounded-xl text-sm font-medium text-left flex items-start gap-3 border border-slate-200 shadow-sm">
                        <i class="fas fa-lock mt-0.5 text-slate-600 text-base"></i>
                        <div>
                            <span class="block font-bold uppercase tracking-wider text-slate-800 text-xs mb-1">Status Terkunci</span>
                            <span class="text-slate-700 leading-relaxed">Anda sudah menyelesaikan ujian. Pilihan Program Studi sudah dikunci permanen dan tidak dapat diubah lagi.</span>
                        </div>
                    </div>
                @else
                    <div class="mb-5 bg-rose-50 text-rose-700 p-4 rounded-xl text-sm font-medium text-left flex items-start gap-4 border-2 border-rose-200 shadow-md transform hover:scale-[1.01] transition-transform duration-300">
                        <div class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center text-rose-600 text-xl border-2 border-rose-200 shadow-inner group animate-pulse">
                            <i class="fas fa-exclamation-triangle group-hover:scale-110 transition-transform"></i>
                        </div>
                        <div class="flex-1">
                            <span class="block font-bold uppercase tracking-wider text-rose-800 text-xs mb-1">Peringatan Permanen</span>
                            <span class="text-rose-700 leading-relaxed">Anda telah berada di tahap ujian. <span class="font-bold underline decoration-rose-400 underline-offset-2">Program Studi tidak dapat diubah lagi</span> setelah Anda memulai ujian.</span>
                        </div>
                    </div>
                @endif
                

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    
                    {{-- KOTAK KIRI: KREDENSIAL LOGIN --}}
                    <div id="tour-credentials-box" class="bg-white rounded-lg border border-slate-200/50 shadow-[0_0_30px_rgba(0,0,0,0.5)] shadow-sm p-4 flex flex-col h-full relative overflow-hidden group hover:shadow-md transition-shadow" 
                         x-data="{ showPass: false, copy(text) { navigator.clipboard.writeText(text); Swal.fire({toast:true, icon:'success', title:'Copied', timer:1000}); } }">
                        <div class="absolute inset-0 tech-grid-bg opacity-30 pointer-events-none"></div>
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-xs font-bold text-slate-700 flex items-center gap-1.5 uppercase tracking-wider"><i class="far fa-id-card text-blue-500 text-sm"></i> Akun Login ujian</h3>
                                <span class="text-[9px] bg-blue-50 text-blue-600 px-2 py-0.5 rounded-sm font-mono border border-blue-100 font-bold">E-LEARNING</span>
                            </div>
                            <div class="mb-3">
                                <label class="block font-mono text-[9px] font-bold text-gray-500 uppercase mb-1">Username</label>
                                <div class="flex justify-between bg-slate-50 border border-slate-200 rounded p-2 hover:bg-slate-100 transition-colors">
                                    <span class="font-mono text-xs font-bold text-slate-700">{{ $displayUsername }}</span>
                                    <button @click="copy('{{ $displayUsername }}')" class="text-gray-500 hover:text-blue-600 transition-colors"><i class="far fa-copy"></i></button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="block font-mono text-[9px] font-bold text-gray-500 uppercase mb-1">Password</label>
                                <div class="flex justify-between bg-slate-50 border border-slate-200 rounded p-2 hover:bg-slate-100 transition-colors">
                                    <span class="font-mono text-xs font-bold text-slate-700">
                                        <span x-show="!showPass">••••••••</span>
                                        <span x-show="showPass">Ubp2026*</span>
                                    </span>
                                    <div class="flex gap-2">
                                        <button @click="showPass = !showPass" class="text-gray-500 hover:text-blue-600 transition-colors"><i class="far" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i></button>
                                        <button @click="copy('{{ $realPassword }}')" class="text-gray-500 hover:text-blue-600 transition-colors"><i class="far fa-copy"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- KOTAK KANAN: STATUS KELULUSAN / JADWAL UJIAN --}}
                    <div class="relative w-full h-full">
                        
                        {{-- KONDISI 1: UJIAN SELESAI (SUDAH ADA HASIL) --}}
                        @if($isUjianSelesai)
                            
                            {{-- KOTAK STATUS --}}
                            <div class="bg-white rounded-lg border border-slate-200/50 shadow-[0_0_30px_rgba(0,0,0,0.5)] shadow-sm p-4 flex flex-col h-full items-center justify-center text-center relative overflow-hidden">
                                
                                {{-- A. SUB-KONDISI: LULUS FINAL (FARMASI/NON-FARMASI) --}}
                                @if($statusKelulusan == 'LULUS')
                                    <div class="w-full" x-data="{ printed: localStorage.getItem('printed_letter_lulus') }">
                                        <template x-if="!printed">
                                            <div class="mb-4 mt-2 p-1 bg-gradient-to-r from-yellow-400 via-orange-500 to-red-500 rounded-xl shadow-lg animate-pulse">
                                                <div class="bg-white rounded-lg p-4 text-center">
                                                    <h4 class="text-lg font-black text-slate-800 mb-1 uppercase">🎉 Selamat! Anda Lulus</h4>
                                                    <p class="text-xs text-slate-500 mb-3">Unduh surat keterangan kelulusan di sini.</p>
                                                    <a href="{{ route('mahasiswa.surat.download') }}" target="_blank" @click="localStorage.setItem('printed_letter_lulus', 'true'); printed = true;" class="inline-flex items-center justify-center gap-2 w-full py-3 bg-emerald-600 text-gray-800 font-black text-xs rounded shadow hover:bg-emerald-700 transition uppercase tracking-widest border-b-4 border-emerald-800 active:border-b-0 active:translate-y-1">
                                                        <i class="fas fa-file-download text-lg"></i> DOWNLOAD SURAT
                                                    </a>
                                                </div>
                                            </div>
                                        </template>
                                        <template x-if="printed">
                                            <div class="flex flex-col items-center w-full">
                                                <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center mb-2 animate-bounce"><i class="fas fa-graduation-cap text-2xl"></i></div>
                                                <h3 class="font-black text-emerald-700 uppercase">LULUS SELEKSI</h3>
                                                <p class="font-mono text-xs text-emerald-600 font-bold mb-3">{{ $jurusanDiterima }}</p>
                                                @if($isPindahProdi)
                                                    <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded-full border border-blue-200 mb-3">
                                                        <i class="fas fa-exchange-alt text-[9px]"></i> Prodi Diperbarui (Pindah)
                                                    </span>
                                                @elseif($isPindahProdiPending)
                                                    <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 text-[10px] font-bold px-2 py-0.5 rounded-full border border-amber-200 mb-3 animate-pulse">
                                                        <i class="fas fa-clock text-[9px]"></i> Menunggu Pelunasan Selisih
                                                    </span>
                                                @endif
                                                <a href="{{ route('mahasiswa.surat.download') }}" target="_blank" class="w-full py-2 mb-2 bg-white border border-emerald-600 text-emerald-600 font-bold text-xs rounded shadow hover:bg-emerald-50 uppercase transition-transform hover:scale-105 flex items-center justify-center gap-2"><i class="fas fa-print"></i> Cetak Ulang Surat</a>
                                                <button @click="activeView = 4; window.scrollTo({ top: 0, behavior: 'smooth' })" class="w-full py-2 bg-emerald-600 text-gray-800 font-bold text-xs rounded shadow hover:bg-emerald-700 uppercase transition-transform hover:scale-105">Lanjut Bayar</button>
                                            </div>
                                        </template>
                                    </div>
                                    
                                    {{-- B. SUB-KONDISI: MENUNGGU VERIFIKASI AKADEMIK --}}
	                                @elseif($stuckAkademik)
	                                    <div class="flex flex-col items-center w-full h-full justify-center p-2">
	                                        <div class="w-14 h-14 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mb-3 shadow-sm animate-pulse border-2 border-indigo-200">
	                                            <i class="fas fa-clipboard-check text-2xl"></i>
	                                        </div>
	                                        
	                                        <h3 class="font-black text-indigo-700 uppercase tracking-tight text-sm mb-1">PROSES KONVERSI SKS</h3>
                                            
	                                        <p class="font-bold text-[10px] text-slate-500 mb-4 uppercase tracking-wide text-center">Jalur: {{ $ereg->jalur_pendaftaran ?? 'Khusus' }}</p>
	                                        
	                                        <div class="w-full bg-indigo-50 border border-indigo-200 rounded-lg p-4 text-center shadow-inner relative overflow-hidden">
	                                            <div class="absolute top-0 right-0 opacity-5 pointer-events-none -mt-4 -mr-2">
	                                                <i class="fas fa-file-signature text-6xl text-indigo-800"></i>
	                                            </div>
	                                            
	                                            <div class="relative z-10">
	                                                <span class="inline-block bg-indigo-200 text-indigo-800 text-[10px] font-black px-3 py-1 rounded-full mb-3 uppercase tracking-widest shadow-sm">
	                                                    <i class="fas fa-info-circle mr-1"></i> Instruksi Akademik
	                                                </span>
	                                                
	                                                {{-- INI BAGIAN AJAIBNYA: NAMPILIN TEKS DARI ADMIN / FALLBACK --}}
	                                                <div class="bg-white p-3 rounded-lg border border-indigo-100 shadow-sm text-sm text-slate-700 leading-relaxed font-medium mb-3 text-left">
	                                                    {{ $teksInfoAkademik }}
	                                                </div>

                                                    {{-- TAMPILAN VIRTUAL ACCOUNT KONVERSI --}}
                                                    @if(isset($vaKonversi) && $vaKonversi)
                                                        <div class="mt-3 bg-white/95 border border-indigo-200 rounded-xl p-3 shadow-sm text-left relative overflow-hidden">
                                                            <div class="flex justify-between items-center mb-2">
                                                                <span class="text-[9px] font-black text-indigo-600 uppercase tracking-wider bg-indigo-50 px-2 py-0.5 rounded">
                                                                    Tagihan Konversi SKS
                                                                </span>
                                                                <span class="text-[9px] font-black uppercase tracking-wider {{ strtolower($vaKonversi['status_pembayaran']) === 'sudah' ? 'text-green-600 bg-green-50' : 'text-amber-600 bg-amber-50' }} px-2 py-0.5 rounded">
                                                                    {{ strtolower($vaKonversi['status_pembayaran']) === 'sudah' ? 'LUNAS' : 'BELUM BAYAR' }}
                                                                </span>
                                                            </div>
                                                            
                                                            <div class="space-y-2">
                                                                {{-- VA Number --}}
                                                                <div>
                                                                    <p class="text-[8px] text-gray-500 font-bold uppercase tracking-wider">No. Virtual Account Mandiri</p>
                                                                    <div class="flex items-center justify-between bg-slate-50 p-1.5 rounded border border-slate-200/50 font-mono text-xs font-bold text-slate-800">
                                                                        <span id="va-konversi-num">{{ $vaKonversi['virtual_account'] }}</span>
                                                                        <button onclick="navigator.clipboard.writeText('{{ $vaKonversi['virtual_account'] }}'); alert('No. VA berhasil disalin!');" class="text-indigo-600 hover:text-indigo-800 text-[10px] font-bold px-1.5 cursor-pointer">
                                                                            <i class="far fa-copy"></i> Salin
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                                
                                                                {{-- Nominal & Expired --}}
                                                                <div class="grid grid-cols-2 gap-2 text-[10px]">
                                                                    <div>
                                                                        <p class="text-[8px] text-gray-500 font-bold uppercase tracking-wider">Nominal</p>
                                                                        <p class="font-bold text-indigo-700">Rp {{ number_format($vaKonversi['trx_amount'], 0, ',', '.') }}</p>
                                                                    </div>
                                                                    <div>
                                                                        <p class="text-[8px] text-gray-500 font-bold uppercase tracking-wider">Batas Waktu</p>
                                                                        <p class="font-bold text-slate-600 text-[9px]">
                                                                            {{ \Carbon\Carbon::parse($vaKonversi['datetime_expired'])->format('d M Y H:i') }}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
	                                                
	                                                <p class="text-[10px] text-indigo-600 font-bold mt-2">
	                                                    <i class="fas fa-clock mr-1 animate-pulse"></i> Menunggu konfirmasi Admin untuk akses Pembayaran.
	                                                </p>
	                                            </div>
	                                        </div>
	                                    </div>

                                {{-- B. SUB-KONDISI: LULUS TAHAP 1 (FARMASI PENDING) --}}
                                @elseif($statusKelulusan == 'LULUS_TAHAP_1')
                                    <div class="flex flex-col items-center w-full h-full justify-center">
                                        
                                        <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center mb-2 shadow-sm animate-pulse">
                                            <i class="fas fa-file-signature text-xl"></i>
                                        </div>
                                        
                                        <h3 class="font-black text-amber-700 uppercase tracking-tight text-sm">LULUS TAHAP 1</h3>
                                        <p class="font-bold text-[10px] text-slate-500 mb-3 uppercase tracking-wide">Wajib Mengikuti Tes Tahap 2</p>
                                        
                                        {{-- JADWAL UJIAN FARMASI --}}
                                        @if(isset($jadwalUjianFarmasi) && $jadwalUjianFarmasi)
                                            <div class="w-full bg-amber-50 border border-amber-200 rounded-lg p-3 text-left shadow-inner relative overflow-hidden">
                                                <div class="absolute top-0 right-0 opacity-10 pointer-events-none">
                                                    <i class="fas fa-notes-medical text-6xl text-amber-800 -mr-2 -mt-2"></i>
                                                </div>

                                                <div class="relative z-10 space-y-2">
                                                    {{-- Tanggal --}}
                                                    <div class="flex items-start gap-2">
                                                        <div class="mt-0.5 w-4 flex justify-center"><i class="far fa-calendar-alt text-amber-600 text-xs"></i></div>
                                                        <div>
                                                            <p class="text-[10px] text-slate-500 font-bold uppercase">Tanggal Pelaksanaan</p>
                                                            <p class="text-xs font-bold text-slate-800">{{ $jadwalUjianFarmasi->tanggal_indo }}</p>
                                                        </div>
                                                    </div>
                                                    {{-- Waktu --}}
                                                    <div class="flex items-start gap-2">
                                                        <div class="mt-0.5 w-4 flex justify-center"><i class="far fa-clock text-amber-600 text-xs"></i></div>
                                                        <div>
                                                            <p class="text-[10px] text-slate-500 font-bold uppercase">Waktu Ujian</p>
                                                            <p class="text-xs font-bold text-slate-800">{{ $jadwalUjianFarmasi->waktu_format }} WIB - Selesai</p>
                                                        </div>
                                                    </div>
                                                    {{-- Lokasi --}}
                                                    <div class="flex items-start gap-2">
                                                        <div class="mt-0.5 w-4 flex justify-center"><i class="fas fa-map-marker-alt text-amber-600 text-xs"></i></div>
                                                        <div>
                                                            <p class="text-[10px] text-slate-500 font-bold uppercase">Lokasi & Keterangan</p>
                                                            <p class="text-xs font-bold text-slate-800 leading-tight">{{ $jadwalUjianFarmasi->deskripsi }}</p>
                                                        </div>
                                                    </div>
                                                    {{-- WA --}}
                                                    <div class="pt-2 mt-1 border-t border-amber-200/60">
                                                        <a href="https://wa.me/6289612500504" target="_blank" class="flex items-center gap-2 group">
                                                            <div class="w-6 h-6 bg-green-500 text-gray-800 rounded-full flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform"><i class="fab fa-whatsapp text-xs"></i></div>
                                                            <div>
                                                                <p class="text-[10px] text-slate-500 font-bold uppercase">Konfirmasi Kehadiran</p>
                                                                <p class="text-xs font-bold text-blue-600 group-hover:underline">Hubungi Admin</p>
                                                            </div>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="w-full bg-slate-50 border-2 border-dashed border-slate-300 rounded-lg p-4 text-center">
                                                <i class="fas fa-hourglass-half text-gray-500 text-2xl mb-2"></i>
                                                <p class="text-xs font-bold text-slate-600">Jadwal Belum Dirilis</p>
                                                <p class="text-[10px] text-gray-500 mt-1">Admin Prodi Farmasi sedang menyusun jadwal. Cek berkala.</p>
                                            </div>
                                        @endif
                                        
                                        <p class="text-[9px] text-gray-500 italic text-center mt-3">*Status kelulusan final diupdate setelah tes tahap 2.</p>
                                    </div>

                                {{-- C. SUB-KONDISI: TIDAK LULUS --}}
                                @elseif($statusKelulusan == 'TIDAK_LULUS')
                                    <div class="w-14 h-14 bg-red-100 text-red-600 rounded-lg flex items-center justify-center mb-2"><i class="fas fa-times-circle text-2xl"></i></div>
                                    <h3 class="font-bold text-red-700 uppercase">TIDAK LULUS</h3>
                                    <p class="text-xs text-red-600">Mohon maaf, Anda belum lulus seleksi.</p>

                                {{-- D. SUB-KONDISI: DEFAULT (MENUNGGU) --}}
                                @else
                                    <div class="w-14 h-14 bg-slate-100 text-slate-500 rounded-lg flex items-center justify-center mb-2"><i class="fas fa-history text-2xl"></i></div>
                                    <h3 class="font-bold text-slate-700 uppercase">Ujian Selesai</h3>
                                    <p class="text-xs text-slate-500">Menunggu hasil kelulusan.</p>
                                @endif
                                
                            </div> {{-- Tutup Kotak Status --}}

                        {{-- KONDISI 2: COUNTDOWN / BELUM UJIAN --}}
                        @elseif($ujianCountdown)
                        
                        @php 
                                $date = \Carbon\Carbon::parse($ujianCountdown->start_date); 
                            @endphp
                            <div class="w-full h-full overflow-hidden rounded-lg bg-white border border-slate-200 shadow-sm relative group p-5 flex flex-col justify-between"
                                 x-data="{ status: '', timer: null, startDate: new Date('{{ $ujianCountdown->start_date->toIso8601String() }}').getTime(), endDate: new Date('{{ $ujianCountdown->end_date->toIso8601String() }}').getTime(), countdown: { d: '00', h: '00', m: '00', s: '00' }, init() { this.update(); this.timer = setInterval(() => this.update(), 1000); }, update() { const now = new Date().getTime(); if (now < this.startDate) { this.status = 'upcoming'; this.calc(this.startDate - now); } else if (now >= this.startDate && now <= this.endDate) { this.status = 'ongoing'; this.calc(this.endDate - now); } else { this.status = 'finished'; clearInterval(this.timer); } }, calc(dist) { this.countdown.d = String(Math.floor(dist / (1000 * 60 * 60 * 24))).padStart(2,'0'); this.countdown.h = String(Math.floor((dist % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2,'0'); this.countdown.m = String(Math.floor((dist % (1000 * 60 * 60)) / (1000 * 60))).padStart(2,'0'); this.countdown.s = String(Math.floor((dist % (1000 * 60)) / 1000)).padStart(2,'0'); } }" x-init="init()">
                                
                                <div class="absolute top-0 right-0 p-2 opacity-[0.05] pointer-events-none"><i class="fas fa-clock text-8xl rotate-12 -mr-6 -mt-6 text-slate-800"></i></div>
                                <div class="absolute top-0 left-0 w-1 h-full bg-blue-500"></div>
                                
                                <div class="relative z-10 flex justify-between items-start mb-4">
                                    <div class="flex flex-col">
                                        <span class="text-[9px] font-mono text-gray-500 uppercase tracking-wider font-bold">Exam Schedule</span>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="relative flex h-2 w-2">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" :class="status === 'ongoing' ? 'bg-red-500' : 'bg-blue-400'"></span>
                                                <span class="relative inline-flex rounded-full h-2 w-2" :class="status === 'ongoing' ? 'bg-red-600' : 'bg-blue-500'"></span>
                                            </span>
                                            <span class="text-[10px] font-bold uppercase tracking-widest" :class="status === 'ongoing' ? 'text-red-600' : 'text-blue-600'" x-text="status === 'upcoming' ? 'LOCKED' : (status === 'ongoing' ? 'LIVE NOW' : 'ENDED')"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="relative z-10 mb-5 pb-4 border-b border-slate-200/50">
                                    <div class="flex items-end gap-2 mb-1">
                                        <div class="text-5xl font-black text-slate-800 tracking-tighter leading-none">{{ $date->format('d') }}</div>
                                        <div class="flex flex-col pb-1">
                                            <span class="text-sm font-bold text-blue-600 uppercase leading-none">{{ $date->format('M') }}</span>
                                            <span class="text-[10px] font-mono text-gray-500 leading-none">{{ $date->format('Y') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 text-slate-500 mt-1">
                                        <i class="far fa-clock text-xs"></i>
                                        <span class="font-mono text-sm font-bold tracking-widest">{{ $date->format('H:i') }} <span class="text-[9px] font-bold bg-slate-100 px-1 rounded text-gray-500">WIB</span></span>
                                    </div>
                                </div>

                                <div x-show="status !== 'finished'" class="relative z-10">
                                    <p class="text-[9px] font-mono text-gray-500 uppercase mb-2 font-bold" x-text="status === 'upcoming' ? 'STARTING IN:' : 'ENDING IN:'"></p>
                                    <div class="grid grid-cols-4 gap-2 mb-4 text-center">
                                        <template x-for="(val, key) in countdown">
                                            <div class="bg-slate-50 rounded p-1.5 border border-slate-200">
                                                <div class="font-mono text-sm font-black text-slate-700 tracking-tight" x-text="val"></div>
                                                <div class="text-[7px] text-gray-500 uppercase font-bold" x-text="key"></div>
                                            </div>
                                        </template>
                                    </div>
                                    <template x-if="status === 'ongoing'">
                                        <a id="tour-exam-btn" href="https://elearning2.ubpkarawang.ac.id/mod/quiz/view.php?id=2" target="_blank" class="flex w-full items-center justify-center gap-2 rounded bg-red-600 py-2.5 text-xs font-bold uppercase tracking-wider text-gray-800 shadow-md hover:bg-red-700 hover:shadow-lg animate-pulse transition-all">
                                            <span>MULAI UJIAN</span> <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </template>
                                </div>
                                <div x-show="status === 'finished'" class="relative z-10 text-center py-2"><p class="text-[10px] text-gray-500 italic bg-slate-50 p-2 rounded">Sesi ujian telah berakhir.</p></div>
                            </div>

                        {{-- KONDISI 3: BELUM ADA JADWAL --}}
                        @else
                            <div class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-lg p-6 text-center h-full flex flex-col justify-center">
                                <i class="fas fa-calendar-times text-2xl text-gray-600 mb-2"></i>
                                <p class="font-mono text-xs font-bold text-slate-500 uppercase">NO SCHEDULE</p>
                                <p class="text-[10px] text-gray-500">Jadwal ujian belum dirilis.</p>
                            </div>
                        @endif
                    </div>
            @else
                {{-- STEP TERKUNCI --}}
                <div class="flex items-center gap-4 p-6 border-2 border-dashed border-slate-300 rounded-lg bg-slate-50/50 opacity-75 font-mono">
                    <div class="w-12 h-12 bg-slate-200 rounded-lg flex items-center justify-center text-gray-500 border border-slate-300"><i class="fas fa-lock text-xl"></i></div>
                    <div>
                        <h4 class="font-bold text-slate-600 text-sm uppercase tracking-wider">Akses Terkunci</h4>
                        <p class="text-[10px] text-slate-500 uppercase tracking-wider">>> Selesaikan tahap sebelumnya.</p>
                    </div>
                </div>
            @endif
        </div>
    </template>

    {{-- STEP 4: PEMBAYARAN UKT --}}
    <template x-if="activeView == 4">
        <div class="animate-slide-up">
            
            {{-- Header Step --}}
            <div class="flex items-center gap-3 mb-5 px-1 border-b border-slate-200/50 pb-3">
                <div class="h-10 w-10 rounded-lg bg-emerald-600 text-gray-800 flex items-center justify-center shadow-md border-2 border-emerald-400">
                    <i class="fas fa-wallet text-sm"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800 uppercase tracking-tight">Pembayaran UKT</h2>
                    <p class="font-mono text-[10px] text-slate-500 uppercase tracking-wider">>> Administrasi Keuangan</p>
                </div>
            </div>
            
            {{-- [BARU] CEGATAN JIKA KUOTA HABIS --}}
            @if(isset($kuotaHabis) && $kuotaHabis && !$isBayarLunas)
                <div class="bg-red-50 border-2 border-red-200 rounded-xl p-8 text-center shadow-sm relative overflow-hidden mb-6">
                    <div class="absolute top-0 left-0 w-full h-1 bg-red-500"></div>
                    <div class="w-20 h-20 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4 text-4xl shadow-inner animate-pulse border border-red-200">
                        <i class="fas fa-user-times"></i>
                    </div>
                    <h3 class="text-xl font-black text-red-800 uppercase tracking-tight mb-2">Kuota Program Studi Penuh!</h3>
                    <p class="text-sm text-red-600 mb-6 px-4 md:px-12 leading-relaxed font-medium">
                        {{ $pesanKuotaHabis ?? 'Mohon Maaf, kuota untuk Program Studi ini sudah terisi penuh. Pembayaran tidak dapat dilanjutkan.' }}
                    </p>
                    
                    <div class="inline-block bg-white px-6 py-4 rounded-xl border border-red-100 shadow-sm text-left">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-lg">
                                <i class="fab fa-whatsapp"></i>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider mb-0.5">Solusi & Pindah Prodi</p>
                                <p class="text-sm font-bold text-slate-700 leading-tight">Hubungi Admin PMB</p>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-slate-200/50">
                            {{-- Ganti nomor ini dengan nomor asli Admin PMB UBP --}}
                            <a href="https://wa.me/6281234567890" target="_blank" class="block text-center w-full bg-green-500 hover:bg-green-600 text-gray-800 text-xs font-bold py-2 rounded uppercase tracking-wider transition">
                                Chat Admin Sekarang
                            </a>
                        </div>
                    </div>
                </div>

            {{-- JIKA KUOTA AMAN -> LANJUT KE LOGIKA NORMAL --}}
            @elseif($currentStep >= 4)
                @php
                    // --- 1. LOGIKA UTAMA ---
                    if (isset($tagihanSelisihAktif) && $tagihanSelisihAktif) {
                        $bill = $tagihanSelisihAktif;
                    } elseif (isset($lunasSelisih) && $lunasSelisih) {
                        $bill = $lunasSelisih;
                    } else {
                        $bill = $activeBill ?? null;
                    }
                    
                    $hasBill = $bill ? true : false;
                    
                    // Cek Status Lunas
                    $isPaid = $hasBill && ($bill->status == 'paid' || $bill->status == 'lunas');
                    
                    // Cek Status Expired
                    $isExpired = false;
                    if ($hasBill && !$isPaid) {
                        if ($bill->status == 'expired') {
                            $isExpired = true;
                        } elseif (!empty($bill->datetime_expired)) {
                            try {
                                if (now()->greaterThan(\Carbon\Carbon::parse($bill->datetime_expired))) {
                                    $isExpired = true;
                                }
                            } catch (\Exception $e) {}
                        }
                    }
    
                    // --- LOGIKA NAMA JURUSAN (Untuk Banner Lulus) ---
                    $namaJurusanLulus = '-';
                    if (isset($jurusanDiterima) && $jurusanDiterima != '-') {
                        // explode berguna buat ngebuang teks "(Menunggu Verifikasi...)" jika ada
                        $namaJurusanLulus = trim(explode('(', $jurusanDiterima)[0]);
                    } elseif ($hasBill && !empty($bill->nama_jurusan)) {
                        $namaJurusanLulus = $bill->nama_jurusan;
                    } 
                    // Fallback (Jaga-jaga)
                    elseif (isset($jurusanDiterima) && $jurusanDiterima != '-') {
                        $namaJurusanLulus = explode('(', $jurusanDiterima)[0];
                    }
    
                    // --- 2. FORMAT DATA TAMPILAN ---
                    $amount = $hasBill ? 'Rp ' . number_format($bill->amount, 0, ',', '.') : '-';
                    $rawMetode = $bill->tenor_pembayaran ?? $bill->description ?? '-';
                    $metode = ucwords(strtolower($rawMetode));
    
                    $textCheck = strtolower($rawMetode);
                    if (str_contains($textCheck, 'cicil')) {
                        $iconMetode = 'fa-chart-pie';
                    } elseif (str_contains($textCheck, 'tahun') || str_contains($textCheck, 'full')) {
                        $iconMetode = 'fa-calendar-check';
                    } else {
                        $iconMetode = 'fa-university';
                    }
                    
                    // --- 3. notif buat RPL sama Ekstensi ---
                    $isJalurKhusus = false;
                    $namaJalur = '-';
                    
                    // Ambil langsung dari DB untuk memastikan data terbaca di partial view ini
                    $cekJalurUser = \App\Models\MasterEreg::where('user_id', Auth::id())->value('jalur_pendaftaran');

                    // Cek exact match sesuai teks di database Anda
                    if (in_array($cekJalurUser, ['Jalur RPL', 'Jalur Ekstensi'])) {
                        $isJalurKhusus = true;
                        $namaJalur = $cekJalurUser;
                    }
                @endphp
                
                {{-- [BARU] TAMPILAN TAGIHAN KONVERSI DI DALAM LANGKAH 4 --}}
                @if(isset($vaKonversi) && $vaKonversi)
                    <div class="mb-5 bg-indigo-50/80 border border-indigo-200 rounded-xl p-4 shadow-sm text-left relative overflow-hidden">
                        <div class="absolute top-0 right-0 opacity-5 pointer-events-none -mt-4 -mr-2">
                            <i class="fas fa-file-invoice-dollar text-7xl text-indigo-800"></i>
                        </div>
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-xs font-black text-indigo-700 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fas fa-file-signature text-sm"></i> Tagihan Konversi SKS (Jalur {{ $ereg->jalur_pendaftaran ?? 'Khusus' }})
                            </span>
                            <span class="text-[10px] font-black uppercase tracking-widest {{ strtolower($vaKonversi['status_pembayaran']) === 'sudah' ? 'text-green-700 bg-green-100 border border-green-200' : 'text-amber-700 bg-amber-100 border border-amber-200' }} px-3 py-1 rounded-full">
                                {{ strtolower($vaKonversi['status_pembayaran']) === 'sudah' ? 'LUNAS' : 'BELUM BAYAR' }}
                            </span>
                        </div>
                        
                        <div class="bg-white p-3 rounded-lg border border-indigo-100 shadow-inner space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                {{-- VA Number --}}
                                <div>
                                    <p class="text-[9px] text-gray-500 font-bold uppercase tracking-wider mb-1">No. Virtual Account Mandiri</p>
                                    <div class="flex items-center justify-between bg-slate-50 p-2 rounded border border-slate-200 font-mono text-sm font-bold text-slate-800">
                                        <span>{{ $vaKonversi['virtual_account'] }}</span>
                                        <button onclick="navigator.clipboard.writeText('{{ $vaKonversi['virtual_account'] }}'); alert('No. VA berhasil disalin!');" class="text-indigo-600 hover:text-indigo-800 text-xs font-bold px-2 cursor-pointer">
                                            <i class="far fa-copy"></i> Salin
                                        </button>
                                    </div>
                                </div>
                                
                                {{-- Nominal & Batas Waktu --}}
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div>
                                        <p class="text-[9px] text-gray-500 font-bold uppercase tracking-wider mb-1">Nominal</p>
                                        <p class="font-bold text-indigo-700 text-sm">Rp {{ number_format($vaKonversi['trx_amount'], 0, ',', '.') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] text-gray-500 font-bold uppercase tracking-wider mb-1">Batas Waktu</p>
                                        <p class="font-bold text-slate-600 text-xs">
                                            {{ \Carbon\Carbon::parse($vaKonversi['datetime_expired'])->format('d M Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
        
                {{-- SKENARIO 1: SUDAH ADA TAGIHAN (AKTIF ATAU LUNAS) --}}
                @if($hasBill && !$isExpired)
                    
                    <div class="bg-white rounded-xl border {{ $isPaid ? 'border-emerald-500 ring-1 ring-emerald-200' : 'border-blue-200' }} shadow-sm overflow-hidden relative">
                        
                        {{-- Badge Status di Pojok --}}
                        <div class="absolute top-0 right-0 p-4 z-10">
                            @if($isPaid)
                                <span class="bg-emerald-600 text-gray-800 text-xs font-black px-3 py-1.5 rounded-lg shadow-emerald-200 shadow-md uppercase tracking-widest flex items-center gap-1">
                                    <i class="fas fa-check-circle"></i> LUNAS / PAID
                                </span>
                            @else
                                <span class="bg-orange-500 text-gray-800 text-xs font-black px-3 py-1.5 rounded-lg shadow-orange-200 shadow-md uppercase tracking-widest flex items-center gap-1 animate-pulse">
                                    <i class="fas fa-spinner fa-spin"></i> MENUNGGU PEMBAYARAN
                                </span>
                            @endif
                        </div>
        
                        <div class="p-6 relative">
                            
                            {{-- [POSISI BARU] BANNER SELAMAT LULUS (HANYA MUNCUL JIKA SUDAH BAYAR) --}}
                            @if($isPaid && $namaJurusanLulus != '-' && $namaJurusanLulus != 'TIDAK LULUS')
                                <div class="mb-6 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-lg shadow-md p-5 text-gray-800 relative overflow-hidden group border-b-4 border-emerald-700">
                                    {{-- Background Decoration --}}
                                    <div class="absolute right-0 top-0 opacity-10 transform translate-x-2 -translate-y-2 pointer-events-none">
                                        <i class="fas fa-graduation-cap text-8xl"></i>
                                    </div>
                                    
                                    <div class="relative z-10 flex items-start gap-4">
                                        <div class="bg-white/20 p-2.5 rounded-full backdrop-blur-sm border border-white/20 shadow-inner mt-1">
                                            <i class="fas fa-trophy text-xl text-yellow-300 animate-bounce"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-base uppercase tracking-wide text-emerald-50 leading-tight">Selamat & Sukses!</h3>
                                            <p class="text-xs font-medium leading-relaxed opacity-90 mt-1">
                                                Pembayaran terkonfirmasi. Anda resmi diterima sebagai mahasiswa Program Studi:
                                            </p>
                                            <div class="mt-2 inline-block bg-black/20 px-3 py-1.5 rounded backdrop-blur-md border-l-2 border-yellow-400">
                                                <span class="font-black text-gray-800 tracking-wider uppercase text-xs">
                                                    {{ $namaJurusanLulus }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
    
                            {{-- Background Icon (Hiasan) --}}
                            <div class="absolute right-[-20px] bottom-[-20px] text-[10rem] opacity-[0.03] pointer-events-none">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
        
                            {{-- Judul & Nominal --}}
                            <div class="mb-6">
                                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Total Tagihan</h3>
                                <div class="text-4xl font-black text-slate-800 tracking-tighter flex items-baseline gap-1">
                                    {{ $amount }}
                                </div>
                                <p class="text-[10px] text-gray-500 font-mono mt-1">ID TAGIHAN: #{{ $bill->order_id ?? $bill->trx_id }}</p>
                            </div>
        
                            {{-- Grid Info Detail --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 bg-slate-50 p-4 rounded-xl border border-slate-200/50">
                                
                                {{-- Metode Pembayaran --}}
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-blue-600 shadow-sm">
                                        <i class="fas {{ $iconMetode }}"></i>
                                    </div>
                                    <div>
                                        <span class="block text-[9px] font-bold text-gray-500 uppercase">Metode Pembayaran</span>
                                        <span class="block text-sm font-bold text-slate-700 capitalize">{{ $metode }}</span>
                                    </div>
                                </div>
        
                                {{-- Virtual Account --}}
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-purple-600 shadow-sm">
                                        <i class="fas fa-university"></i>
                                    </div>
                                    <div>
                                        <span class="block text-[9px] font-bold text-gray-500 uppercase">Virtual Account (VA)</span>
                                        <div class="flex items-center gap-2">
                                            <span class="block text-sm font-black text-slate-800 font-mono tracking-wider">{{ $bill->virtual_account }}</span>
                                            <button onclick="navigator.clipboard.writeText('{{ $bill->virtual_account }}'); Swal.fire({toast:true, position:'top-end', icon:'success', title:'VA Disalin', timer:1500, showConfirmButton:false})" class="text-gray-500 hover:text-blue-600 transition" title="Salin VA">
                                                <i class="far fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
        
                            {{-- ACTION BUTTONS --}}
                            <div class="mt-4">
                                    @if($isPaid)
                                        {{-- INFO TAMBAHAN SUDAH BAYAR --}}
                                        <div class="p-3 bg-emerald-50 border border-emerald-100 rounded-lg flex items-center gap-3 mb-3">
                                            <i class="fas fa-receipt text-emerald-600"></i>
                                            <p class="text-[10px] text-emerald-800 leading-tight">
                                                <b>Langkah Selanjutnya:</b> Silakan cetak bukti bayar ini, lalu lanjutkan ke tahap <b>Data Final</b>.
                                            </p>
                                        </div>
    
                                        <a href="{{ route('mahasiswa.pembayaran.receipt', ['order_id' => $bill->order_id]) }}" class="block w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-gray-800 font-bold rounded-lg shadow-lg text-center uppercase text-xs tracking-wider transition-all transform hover:scale-[1.01]">
                                            <i class="fas fa-print mr-2"></i> Cetak Bukti Pembayaran
                                        </a>
                                    @else
                                        <a href="{{ route('pembayaran.show', ['order_id' => $bill->order_id]) }}" class="block w-full py-3 bg-blue-600 hover:bg-blue-700 text-gray-800 font-bold rounded-lg shadow-lg text-center uppercase text-xs tracking-wider transition-all transform hover:scale-[1.01] animate-pulse">
                                            <i class="fas fa-credit-card mr-2"></i> Bayar Sekarang
                                        </a>
    
                                        {{-- COUNTDOWN TIMER --}}
                                        <div x-data="{
                                            expiry: '{{ $bill->datetime_expired }}',
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
                                                } else {
                                                    this.days = String(Math.floor(distance / (1000 * 60 * 60 * 24))).padStart(2, '0');
                                                    this.hours = String(Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2, '0');
                                                    this.minutes = String(Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0');
                                                    this.seconds = String(Math.floor((distance % (1000 * 60)) / 1000)).padStart(2, '0');
                                                }
                                            }
                                        }" class="mt-4 text-center">
                                            
                                            <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-2">Sisa Waktu Pembayaran</p>
                                            
                                            <div class="flex justify-center gap-2" x-show="!expired">
                                                {{-- TIMER HTML --}}
                                                <div class="flex flex-col items-center"><div class="bg-slate-800 text-gray-800 rounded p-1.5 w-8 text-center font-mono font-bold text-sm leading-none" x-text="days"></div><span class="text-[8px] text-gray-500 mt-1 uppercase">Hari</span></div>
                                                <span class="text-gray-600 text-sm font-bold mt-1">:</span>
                                                <div class="flex flex-col items-center"><div class="bg-slate-800 text-gray-800 rounded p-1.5 w-8 text-center font-mono font-bold text-sm leading-none" x-text="hours"></div><span class="text-[8px] text-gray-500 mt-1 uppercase">Jam</span></div>
                                                <span class="text-gray-600 text-sm font-bold mt-1">:</span>
                                                <div class="flex flex-col items-center"><div class="bg-slate-800 text-gray-800 rounded p-1.5 w-8 text-center font-mono font-bold text-sm leading-none" x-text="minutes"></div><span class="text-[8px] text-gray-500 mt-1 uppercase">Mnt</span></div>
                                                <span class="text-gray-600 text-sm font-bold mt-1">:</span>
                                                <div class="flex flex-col items-center"><div class="bg-red-600 text-gray-800 rounded p-1.5 w-8 text-center font-mono font-bold text-sm leading-none animate-pulse" x-text="seconds"></div><span class="text-[8px] text-red-600 mt-1 uppercase font-bold">Dtk</span></div>
                                            </div>
    
                                            <div x-show="expired" style="display: none;" class="bg-red-50 text-red-600 px-3 py-2 rounded border border-red-100 text-xs font-bold">
                                                <i class="fas fa-exclamation-triangle mr-1"></i> Waktu Habis.
                                            </div>
                                        </div>
                                    @endif
                            </div>
                        </div>
                    </div>
        
                {{-- SKENARIO 2: EXPIRED ATAU BELUM ADA TAGIHAN --}}
                @else
                    <div class="bg-white rounded-xl border border-slate-200 p-8 text-center shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 left-0 w-full h-1 {{ $isExpired ? 'bg-red-500' : 'bg-blue-500' }}"></div>
                        <div class="w-20 h-20 {{ $isExpired ? 'bg-red-50 text-red-600' : 'bg-blue-50 text-blue-600' }} rounded-full flex items-center justify-center mx-auto mb-4 text-3xl shadow-sm">
                            <i class="fas {{ $isExpired ? 'fa-history' : 'fa-file-invoice' }}"></i>
                        </div>
                        @if($isExpired)
                            <h3 class="text-xl font-black text-slate-800 uppercase tracking-tight mb-2">Tagihan Kadaluwarsa</h3>
                            <p class="text-sm text-slate-500 mb-6 px-8 leading-relaxed">Masa berlaku Virtual Account habis. Buat tagihan baru.</p>
                        @else
                            <h3 class="text-xl font-black text-slate-800 uppercase tracking-tight mb-2">Belum Ada Tagihan</h3>
                            <p class="text-sm text-slate-500 mb-6 px-8 leading-relaxed">Silakan buat tagihan UKT untuk melanjutkan.</p>
                        @endif
                        <a href="{{ route('pembayaran.ukt') }}" class="inline-flex items-center justify-center px-8 py-3 {{ $isExpired ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700' }} text-gray-800 font-bold rounded-lg shadow-lg uppercase text-xs tracking-wider transition-transform hover:scale-105">
                            <i class="fas fa-plus-circle mr-2"></i> {{ $isExpired ? 'Buat Ulang Tagihan' : 'Buat Tagihan Baru' }}
                        </a>
                    </div>
                @endif
                
                @if($isJalurKhusus && !$isPaid)
                    <div class="mt-6 bg-amber-50 border border-amber-200 rounded-xl p-4 shadow-sm flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0 shadow-inner mt-0.5">
                            <i class="fas fa-exclamation-triangle text-lg animate-pulse"></i>
                        </div>
                        <div>
                            <h4 class="text-amber-800 font-extrabold text-xs uppercase tracking-wider mb-1.5">
                                Pemberitahuan {{ $namaJalur }}
                            </h4>
                            <p class="text-amber-700 text-xs font-medium leading-relaxed">
                                Sebelum melakukan pembayaran, silakan hubungi atau kunjungi bagian <span class="font-black text-amber-900">Akademik</span> di Universitas Buana Perjuangan, <span class="font-black text-amber-900">Gedung Rektorat Lantai 2</span>.
                            </p>
                        </div>
                    </div>
                @endif
        
            @else
                {{-- STATE: TERKUNCI (NORMAL - BUKAN KARENA KUOTA HABIS) --}}
                <div class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-lg p-8 text-center opacity-75 font-mono">
                    <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center mx-auto mb-4 text-gray-400 border border-gray-300">
                        <i class="fas fa-lock text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-600 uppercase tracking-wider">Pembayaran Terkunci</h3>
                    <p class="text-xs text-gray-500 mt-2 uppercase tracking-wider">>> Selesaikan ujian seleksi terlebih dahulu.</p>
                </div>
            @endif
        </div>
    </template>
    
    {{-- STEP 5: DATA FINAL --}}
    <template x-if="activeView == 5">
        <div class="animate-slide-up">
            
            {{-- HEADER STEP --}}
            <div class="flex items-center gap-3 mb-5 px-1 border-b border-slate-200/50 pb-3">
                <div class="h-10 w-10 rounded-lg bg-purple-600 text-gray-800 flex items-center justify-center shadow-md border-2 border-purple-400">
                    <i class="fas fa-database text-sm"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800 uppercase tracking-tight">Data Final</h2>
                    <p class="font-mono text-[10px] text-slate-500 uppercase tracking-wider">>> Pelaporan PDDIKTI</p>
                </div>
            </div>
        
            {{-- PASTIKAN CURRENT STEP SUDAH MENCAPAI TAHAP INI --}}
            @if($currentStep >= 5)
                
                {{-- Cek apakah Data Benar-benar Ada di DB & Completed? --}}
                @if($isDataFinalAdaDiDB) 
    
                    {{-- TAMPILAN JIKA DATA SUDAH ADA DI DB (COMPLETED) --}}
                    <div class="bg-green-50 border border-slate-200/50 shadow-[0_0_30px_rgba(0,0,0,0.5)] border-green-200 rounded-lg p-8 text-center relative overflow-hidden hover:shadow-md transition-shadow">
                        <div class="absolute inset-0 tech-grid-bg opacity-30 pointer-events-none"></div>
                        <div class="relative z-10">
                            <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 text-green-600 rounded-lg mb-4 border-2 border-green-200 shadow-sm animate-bounce">
                                <i class="fas fa-database text-4xl"></i>
                            </div>
                            <h4 class="font-black text-xl text-green-800 uppercase tracking-tight">Data Final Tersimpan</h4>
                            <p class="font-mono text-xs text-green-600 mt-2 uppercase tracking-wider">>> Status: Data lengkap. Akses Portal dibuka.</p>
                            
                            <button @click="activeView = 6" class="mt-6 px-8 py-3 bg-green-600 text-gray-800 rounded-lg font-bold text-sm shadow-md hover:bg-green-700 transition uppercase tracking-wider border-b-4 border-green-800 hover:translate-y-[1px] active:border-b-0 active:translate-y-[4px]">
                                Buka Informasi PKKMB <i class="fas fa-arrow-right ml-1"></i>
                            </button>
                        </div>
                    </div>
    
                @else
                    {{-- TAMPILAN JIKA DATA BELUM ADA (FORM INPUT) --}}
                    {{-- [MODIFIKASI] HAPUS IF AUTH::ID AGAR MUNCUL UNTUK SEMUA USER --}}
                        
                    {{-- BANNER PERINGATAN --}}
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded-r-lg shadow-sm">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-red-600 text-xl animate-pulse"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-bold text-red-800 uppercase">Peringatan Penting!</h3>
                                <p class="text-xs text-red-700 mt-1">
                                    Data yang akan Anda isi bersifat <b>PERMANEN</b>. Pastikan data sesuai dengan <b>KTP/Ijazah</b>.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- TOMBOL LINK KE FORM --}}
                    <a href="{{ route('mahasiswa.data-final.create') }}" class="group block bg-white border border-slate-200/50 shadow-[0_0_30px_rgba(0,0,0,0.5)] hover:border-purple-400 rounded-lg p-6 shadow-sm hover:shadow-md transition-all duration-300 relative overflow-hidden hover:-translate-y-1">
                        <div class="absolute inset-0 tech-grid-bg opacity-50 pointer-events-none group-hover:opacity-70 transition-opacity"></div>
                        <div class="relative z-10 flex items-center gap-6">
                            <div class="w-16 h-16 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center text-3xl border-2 border-purple-200 shadow-sm group-hover:scale-105 transition-transform duration-300">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            <div class="flex-grow">
                                <h4 class="text-lg font-bold text-gray-800 group-hover:text-purple-600 transition-colors uppercase tracking-tight">Lengkapi Data Final</h4>
                                <p class="font-mono text-xs text-gray-500 mt-1 uppercase tracking-wider">>> Wajib untuk pelaporan PDDIKTI.</p>
                            </div>
                            <div class="w-12 h-12 rounded-lg bg-gray-50 text-gray-400 flex items-center justify-center border border-gray-200 group-hover:bg-purple-600 group-hover:text-gray-800 group-hover:border-purple-600 transition-all duration-300">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </div>
                    </a>
                @endif
                
            @else
                {{-- AKSES TERKUNCI --}}
                <div class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-lg p-8 text-center opacity-75 font-mono">
                    <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center mx-auto mb-4 text-gray-400 border border-gray-300">
                        <i class="fas fa-lock text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-600 uppercase tracking-wider">Akses Terkunci</h3>
                    <p class="text-xs text-gray-500 mt-2 uppercase tracking-wider">>> Selesaikan pembayaran UKT terlebih dahulu.</p>
                </div>
            @endif
    
        </div>
    </template>

  
    {{-- STEP 6: PKKMB & NIM (MODERN & RESPONSIVE) --}}
    <template x-if="activeView == 6">
        <div class="animate-slide-up space-y-4">
            
            {{-- HEADER STEP: FLUID & CLEAN --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white/70 backdrop-blur-md p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-indigo-700 text-white flex items-center justify-center shadow-md shadow-blue-500/20 shrink-0">
                        <i class="fas fa-graduation-cap text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-base sm:text-lg font-black text-slate-800 tracking-tight leading-tight">
                            Akun Perkuliahan & PKKMB
                        </h2>
                        <p class="text-xs text-slate-500 font-medium">
                            Kredensial resmi SIPT, E-Learning, & jadwal orientasi mahasiswa baru
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 self-start sm:self-auto">
                    <span class="text-[10px] sm:text-[11px] font-bold bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded-lg border border-indigo-100">
                        Jalur: {{ $ereg->jalur_pendaftaran ?? 'Reguler' }}
                    </span>
                    @if(!empty($kodeDaftar) && $kodeDaftar !== '-')
                    <span class="text-[10px] sm:text-[11px] font-mono font-bold bg-slate-100 text-slate-700 px-2.5 py-1 rounded-lg border border-slate-200">
                        #{{ $kodeDaftar }}
                    </span>
                    @endif
                </div>
            </div>
    
            @if($currentStep >= 6)
                
                @if(isset($needsAlmamater) && $needsAlmamater)
                    {{-- TAMPILAN: WAJIB PILIH UKURAN DULU (RESPONSIVE & CLEAN) --}}
                    <div class="relative w-full bg-white rounded-2xl overflow-hidden shadow-sm border border-amber-200/80 p-6 sm:p-8 flex flex-col items-center justify-center text-center">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center mb-4 shadow-sm shadow-amber-200/50">
                            <i class="fas fa-tshirt text-2xl sm:text-3xl animate-bounce"></i>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-amber-600 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200 mb-2">
                            Langkah Terakhir
                        </span>
                        <h3 class="text-lg sm:text-xl font-black text-slate-800 tracking-tight mb-2">
                            Pilih Ukuran Jas Almamater
                        </h3>
                        <p class="text-slate-600 max-w-md mx-auto mb-6 leading-relaxed text-xs sm:text-sm">
                            Sebelum Kartu Mahasiswa Sementara dan Informasi Akun PKKMB dapat diterbitkan, Anda <b>DIWAJIBKAN</b> memilih ukuran Jas Almamater terlebih dahulu.
                        </p>
                        <a href="{{ route('mahasiswa.ringkasan') }}" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold rounded-xl shadow-md shadow-blue-500/20 transition-all transform active:scale-95 text-xs sm:text-sm gap-2 uppercase tracking-wider">
                            <span>Pilih Ukuran di Ringkasan</span>
                            <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                @else
                    {{-- TAMPILAN UTAMA: INFORMASI AKUN & PKKMB --}}
                    <div x-data="{ 
                        activeSubTab: 'akademik',
                        showPreview: false,
                        copiedItem: null,
                        copyToClipboard(text, itemKey) {
                            if (!text || text === '-') return;
                            navigator.clipboard.writeText(text).then(() => {
                                this.copiedItem = itemKey;
                                setTimeout(() => { this.copiedItem = null; }, 2000);
                            });
                        },
                        unduhBarcode() {
                            const img = new Image();
                            img.crossOrigin = 'Anonymous';
                            img.src = '{{ $fileUrl }}';
                            img.onload = () => {
                                const canvas = document.createElement('canvas');
                                const ctx = canvas.getContext('2d');
                                
                                const size = 600;
                                const textSpace = 100;
                                canvas.width = size;
                                canvas.height = size + textSpace;
                                
                                ctx.fillStyle = '#ffffff';
                                ctx.fillRect(0, 0, canvas.width, canvas.height);
                                
                                const margin = 40;
                                const qrSize = size - (margin * 2);
                                ctx.drawImage(img, margin, margin, qrSize, qrSize);
                                
                                ctx.fillStyle = '#334155';
                                ctx.font = 'bold 30px Arial, sans-serif';
                                ctx.textAlign = 'center';
                                ctx.textBaseline = 'middle';
                                ctx.fillText('Nomor Barcode : {{ $trxId }}', canvas.width / 2, size + (textSpace / 2) - 10);
                                
                                const link = document.createElement('a');
                                link.download = '{{ $fileName }}';
                                link.href = canvas.toDataURL('image/png');
                                link.click();
                            };
                        }
                    }" class="space-y-4">

                        {{-- 1. SUB-TAB SWITCHER: TOUCH-FRIENDLY & RESPONSIVE --}}
                        <div class="grid grid-cols-2 p-1.5 bg-slate-100/90 rounded-2xl border border-slate-200/80 gap-1.5 shadow-2xs">
                            <button type="button" 
                                @click="activeSubTab = 'akademik'" 
                                :class="activeSubTab === 'akademik' 
                                    ? 'bg-white text-blue-700 shadow-xs font-black border border-slate-200/60' 
                                    : 'text-slate-600 hover:text-slate-900 font-bold'"
                                class="py-2.5 sm:py-3 px-2 sm:px-4 rounded-xl text-xs sm:text-sm flex items-center justify-center gap-2 transition-all active:scale-98">
                                <i class="fas fa-university text-xs sm:text-sm"></i>
                                <span class="hidden sm:inline">Akun Akademik & SIPT</span>
                                <span class="sm:hidden">Akun SIPT</span>
                                @if($siptReady)
                                    <span class="inline-flex w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                                @endif
                            </button>
                            <button type="button" 
                                @click="activeSubTab = 'pkkmb'" 
                                :class="activeSubTab === 'pkkmb' 
                                    ? 'bg-white text-emerald-700 shadow-xs font-black border border-slate-200/60' 
                                    : 'text-slate-600 hover:text-slate-900 font-bold'"
                                class="py-2.5 sm:py-3 px-2 sm:px-4 rounded-xl text-xs sm:text-sm flex items-center justify-center gap-2 transition-all active:scale-98">
                                <i class="fas fa-bullhorn text-xs sm:text-sm"></i>
                                <span class="hidden sm:inline">Informasi & LMS PKKMB</span>
                                <span class="sm:hidden">LMS PKKMB</span>
                                @if($bocReady)
                                    <span class="inline-flex w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                @endif
                            </button>
                        </div>

                        {{-- 2. KONTEN TAB 1: AKUN AKADEMIK & SIPT (CAMPUS KEYCARD DESIGN) --}}
                        <div x-show="activeSubTab === 'akademik'" 
                             x-transition:enter="transition ease-out duration-200" 
                             x-transition:enter-start="opacity-0 translate-y-1" 
                             x-transition:enter-end="opacity-100 translate-y-0" 
                             class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-4 sm:p-6 space-y-4 sm:space-y-5">
                            
                            <!-- Header Status Card -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-3.5 border-b border-slate-100">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg {{ $siptReady ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center text-sm shrink-0">
                                        <i class="fas fa-id-card"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-wide">
                                            Kredensial Resmi SIPT (Sistem Akademik)
                                        </h3>
                                        <p class="text-[11px] text-slate-500">
                                            Akun perkuliahan untuk login ke portal sipt2.ubpkarawang.ac.id
                                        </p>
                                    </div>
                                </div>
                                <div>
                                    @if($siptReady)
                                        <span class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-1 rounded-full shadow-2xs">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Siap Digunakan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-500 border border-slate-200 px-2.5 py-1 rounded-full">
                                            <i class="fas fa-clock text-[9px]"></i> Belum Tersedia
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- 3 Credential Boxes (Fluid Grid) -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                
                                <!-- Box NIM -->
                                <div class="bg-gradient-to-br from-blue-50/70 to-indigo-50/30 p-3.5 rounded-xl border border-blue-100 flex flex-col justify-between transition-all hover:border-blue-200">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-[10px] text-blue-700 font-bold uppercase tracking-wider">
                                            NIM Mahasiswa
                                        </span>
                                        <i class="fas fa-hashtag text-blue-400 text-xs"></i>
                                    </div>
                                    <div class="flex items-center justify-between gap-1 mt-1">
                                        <span class="font-mono text-sm sm:text-base font-black text-slate-800 select-all tracking-tight truncate">
                                            {{ $siptReady ? $dataAkun->nim : '-' }}
                                        </span>
                                        @if($siptReady)
                                        <button type="button" 
                                                @click="copyToClipboard('{{ $dataAkun->nim }}', 'nim')" 
                                                class="px-2 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1 shrink-0"
                                                :class="copiedItem === 'nim' ? 'bg-emerald-600 text-white' : 'bg-white text-blue-600 hover:bg-blue-100 shadow-2xs border border-blue-200'">
                                            <i :class="copiedItem === 'nim' ? 'fas fa-check' : 'fas fa-copy'"></i>
                                            <span x-text="copiedItem === 'nim' ? 'Disalin' : 'Salin'"></span>
                                        </button>
                                        @endif
                                    </div>
                                </div>

                                <!-- Box Email Institusi -->
                                <div class="bg-gradient-to-br from-indigo-50/70 to-purple-50/30 p-3.5 rounded-xl border border-indigo-100 flex flex-col justify-between transition-all hover:border-indigo-200">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-[10px] text-indigo-700 font-bold uppercase tracking-wider">
                                            Email Kampus
                                        </span>
                                        <i class="fas fa-envelope text-indigo-400 text-xs"></i>
                                    </div>
                                    <div class="flex items-center justify-between gap-1 mt-1">
                                        <span class="font-mono text-xs font-bold text-indigo-900 select-all break-all leading-tight">
                                            {{ $siptReady ? $dataAkun->email_mhs : '-' }}
                                        </span>
                                        @if($siptReady)
                                        <button type="button" 
                                                @click="copyToClipboard('{{ $dataAkun->email_mhs }}', 'email')" 
                                                class="px-2 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1 shrink-0"
                                                :class="copiedItem === 'email' ? 'bg-emerald-600 text-white' : 'bg-white text-indigo-600 hover:bg-indigo-100 shadow-2xs border border-indigo-200'">
                                            <i :class="copiedItem === 'email' ? 'fas fa-check' : 'fas fa-copy'"></i>
                                            <span x-text="copiedItem === 'email' ? 'Disalin' : 'Salin'"></span>
                                        </button>
                                        @endif
                                    </div>
                                </div>

                                <!-- Box Password Default -->
                                <div class="bg-gradient-to-br from-slate-50 to-blue-50/30 p-3.5 rounded-xl border border-slate-200 flex flex-col justify-between transition-all hover:border-slate-300">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-[10px] text-slate-600 font-bold uppercase tracking-wider">
                                            Password Default
                                        </span>
                                        <i class="fas fa-key text-slate-400 text-xs"></i>
                                    </div>
                                    <div class="flex items-center justify-between gap-1 mt-1">
                                        <span class="font-mono text-sm sm:text-base font-black text-slate-800 select-all tracking-tight">
                                            {{ $siptReady ? 'ubpk2026' : '-' }}
                                        </span>
                                        @if($siptReady)
                                        <button type="button" 
                                                @click="copyToClipboard('ubpk2026', 'pass')" 
                                                class="px-2 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1 shrink-0"
                                                :class="copiedItem === 'pass' ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-100 shadow-2xs border border-slate-200'">
                                            <i :class="copiedItem === 'pass' ? 'fas fa-check' : 'fas fa-copy'"></i>
                                            <span x-text="copiedItem === 'pass' ? 'Disalin' : 'Salin'"></span>
                                        </button>
                                        @endif
                                    </div>
                                </div>

                            </div>

                            <!-- Action Bar & Link Login -->
                            <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <p class="text-[11px] text-slate-500 flex items-center gap-1.5">
                                    <i class="fas fa-info-circle text-blue-500"></i>
                                    <span>Gunakan kredensial ini untuk pengisian KRS dan perkuliahan di sistem SIPT.</span>
                                </p>
                                @if($siptReady)
                                <a href="https://sipt2.ubpkarawang.ac.id/login" target="_blank" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold rounded-xl shadow-sm shadow-blue-500/20 text-xs uppercase tracking-wider transition-all transform active:scale-95">
                                    <span>Buka Portal SIPT</span>
                                    <i class="fas fa-arrow-right text-[10px]"></i>
                                </a>
                                @else
                                <button type="button" disabled class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-slate-100 text-slate-400 font-bold rounded-xl text-xs uppercase tracking-wider cursor-not-allowed">
                                    <span>Portal SIPT Terkunci</span>
                                    <i class="fas fa-lock text-[10px]"></i>
                                </button>
                                @endif
                            </div>
                        </div>

                        {{-- 3. KONTEN TAB 2: INFORMASI & LMS PKKMB (ORIENTATION HUB) --}}
                        <div x-show="activeSubTab === 'pkkmb'" 
                             x-transition:enter="transition ease-out duration-200" 
                             x-transition:enter-start="opacity-0 translate-y-1" 
                             x-transition:enter-end="opacity-100 translate-y-0" 
                             class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            
                            {{-- CARD A: AKUN E-LEARNING PKKMB --}}
                            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-4 sm:p-5 flex flex-col justify-between transition-all hover:border-emerald-200">
                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-3.5 pb-2.5 border-b border-slate-100">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-lg {{ $bocReady ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center text-sm shrink-0">
                                                <i class="fas fa-laptop-code"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-wide">
                                                    Akun E-Learning PKKMB
                                                </h4>
                                                <p class="text-[10px] text-slate-400">Pembelajaran daring orientasi</p>
                                            </div>
                                        </div>
                                        <div>
                                            @if($bocReady)
                                                <span class="text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-full">
                                                    Siap
                                                </span>
                                            @else
                                                <span class="text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-500 border border-slate-200 px-2 py-0.5 rounded-full">
                                                    Menunggu
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 mb-3">
                                        <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200/80 flex flex-col justify-between">
                                            <span class="text-[9px] text-slate-500 font-bold uppercase tracking-wider block mb-0.5">Username</span>
                                            <div class="flex items-center justify-between gap-1">
                                                <span class="font-mono text-xs font-black text-slate-800 select-all truncate">{{ $bocUser }}</span>
                                                @if($bocReady)
                                                <button type="button" 
                                                        @click="copyToClipboard('{{ $bocUser }}', 'boc_user')" 
                                                        class="px-1.5 py-0.5 text-xs font-bold rounded transition"
                                                        :class="copiedItem === 'boc_user' ? 'bg-emerald-600 text-white' : 'text-emerald-700 hover:bg-emerald-100'">
                                                    <i :class="copiedItem === 'boc_user' ? 'fas fa-check' : 'fas fa-copy'"></i>
                                                </button>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200/80 flex flex-col justify-between">
                                            <span class="text-[9px] text-slate-500 font-bold uppercase tracking-wider block mb-0.5">Password</span>
                                            <div class="flex items-center justify-between gap-1">
                                                <span class="font-mono text-xs font-black text-slate-800 select-all truncate">{{ $bocPass }}</span>
                                                @if($bocReady)
                                                <button type="button" 
                                                        @click="copyToClipboard('{{ $bocPass }}', 'boc_pass')" 
                                                        class="px-1.5 py-0.5 text-xs font-bold rounded transition"
                                                        :class="copiedItem === 'boc_pass' ? 'bg-emerald-600 text-white' : 'text-emerald-700 hover:bg-emerald-100'">
                                                    <i :class="copiedItem === 'boc_pass' ? 'fas fa-check' : 'fas fa-copy'"></i>
                                                </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <p class="text-[11px] text-slate-500 flex items-center gap-1.5 mb-2">
                                        <i class="fas fa-info-circle text-emerald-500"></i>
                                        <span>Gunakan untuk login ke sistem pembelajaran PKKMB Online.</span>
                                    </p>
                                </div>

                                <div class="pt-3 border-t border-slate-100">
                                    @if($bocReady)
                                    <a href="https://elearning.ubpkarawang.ac.id/login/index.php" target="_blank" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs text-xs uppercase tracking-wider transition-all transform active:scale-95">
                                        <span>Masuk E-Learning PKKMB</span>
                                        <i class="fas fa-arrow-right text-[10px]"></i>
                                    </a>
                                    @else
                                    <button type="button" disabled class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-100 text-slate-400 font-bold rounded-xl text-xs uppercase tracking-wider cursor-not-allowed">
                                        <span>Akun Belum Siap</span>
                                        <i class="fas fa-lock text-[10px]"></i>
                                    </button>
                                    @endif
                                </div>
                            </div>

                            {{-- CARD B: JADWAL & BARCODE PRESENSI PKKMB --}}
                            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-4 sm:p-5 flex flex-col justify-between transition-all hover:border-indigo-200">
                                <div class="space-y-3">
                                    {{-- Header Jadwal --}}
                                    <div class="flex items-center gap-3 pb-2.5 border-b border-slate-100">
                                        <div class="flex flex-col items-center justify-center bg-gradient-to-br from-indigo-600 to-blue-700 text-white rounded-xl p-2 min-w-[65px] text-center shadow-xs shrink-0">
                                            <span class="text-[9px] font-black uppercase text-blue-200">Sep 2026</span>
                                            <span class="text-lg font-black leading-none">4-6</span>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-[9px] font-black bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded uppercase">Wajib</span>
                                                <h4 class="text-xs sm:text-sm font-black text-slate-800 uppercase">Jadwal Resmi PKKMB</h4>
                                            </div>
                                            <p class="text-[11px] text-slate-600 mt-0.5">
                                                Kegiatan serentak pada <b class="text-slate-800">4, 5, & 6 September 2026</b>.
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Notice Barcode --}}
                                    <div class="bg-indigo-50/60 p-2.5 rounded-xl border border-indigo-100 flex items-start gap-2 text-[11px] text-indigo-900 leading-relaxed">
                                        <i class="fas fa-qrcode text-indigo-600 text-sm mt-0.5 shrink-0"></i>
                                        <p>
                                            <strong class="text-indigo-800">Tiket Presensi:</strong> Simpan barcode sebagai tiket registrasi masuk harian selama orientasi.
                                        </p>
                                    </div>
                                </div>

                                {{-- Tombol Aksi Barcode & Portal (Fluid Grid on Mobile) --}}
                                <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row items-center gap-2 mt-3">
                                    @if($isFileReady)
                                        <button @click="showPreview = true" type="button" class="w-full sm:w-1/2 inline-flex items-center justify-center px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs text-xs gap-1.5 transition-all transform active:scale-95">
                                            <i class="fas fa-qrcode text-sm"></i>
                                            <span>Tiket Barcode</span>
                                        </button>
                                    @else
                                        <button type="button" onclick="alert('Barcode presensi sedang dalam proses penerbitan oleh panitia.')" class="w-full sm:w-1/2 inline-flex items-center justify-center px-3.5 py-2.5 bg-slate-100 text-slate-400 font-bold rounded-xl text-xs gap-1.5 cursor-not-allowed">
                                            <i class="fas fa-qrcode text-sm"></i>
                                            <span>Tiket Barcode</span>
                                        </button>
                                    @endif

                                    <a href="https://pkkmbonline.ubpkarawang.ac.id/" target="_blank" class="w-full sm:w-1/2 inline-flex items-center justify-center px-3.5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-xs text-xs gap-1.5 transition-all transform active:scale-95">
                                        <i class="fas fa-globe text-sm"></i>
                                        <span>Portal PKKMB</span>
                                        <i class="fas fa-external-link-alt text-[9px] opacity-75"></i>
                                    </a>
                                </div>
                            </div>

                        </div>

                        {{-- MODAL POPUP BARCODE PRESENSI (RESPONSIVE BOTTOM-SHEET / MODAL) --}}
                        @if($isFileReady)
                        <div x-show="showPreview" style="display: none;" class="fixed inset-0 z-[9999] flex items-end sm:items-center justify-center p-0 sm:p-4">
                            <div x-show="showPreview" x-transition.opacity.duration.300ms @click="showPreview = false" class="fixed inset-0 bg-slate-900/70 backdrop-blur-xs cursor-pointer"></div>
                            
                            <div x-show="showPreview" 
                                 x-transition:enter="transition ease-out duration-300" 
                                 x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95" 
                                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                                 class="relative bg-white w-full sm:max-w-xs rounded-t-3xl sm:rounded-2xl shadow-2xl border border-slate-100 p-5 sm:p-6 flex flex-col items-center z-10 max-h-[85vh] overflow-y-auto">
                                
                                {{-- Drag handle on mobile --}}
                                <div class="sm:hidden w-12 h-1 bg-slate-300 rounded-full mb-3"></div>

                                <button @click="showPreview = false" class="absolute top-3.5 right-4 text-slate-400 hover:text-red-600 text-lg p-1">
                                    <i class="fas fa-times"></i>
                                </button>
                                <h3 class="text-base font-black text-slate-800 uppercase tracking-tight mb-1 text-center">
                                    Tiket Presensi PKKMB
                                </h3>
                                <p class="text-[11px] text-slate-500 text-center mb-4">
                                    Tunjukkan barcode ini kepada panitia saat registrasi kehadiran harian.
                                </p>
                                
                                {{-- Tampilan Preview Custom --}}
                                <div class="w-full bg-slate-50 rounded-xl border border-slate-200/80 p-3 mb-4 flex flex-col items-center shadow-2xs">
                                    <img src="{{ $fileUrl }}" alt="Barcode Presensi" class="w-full max-w-[200px] aspect-square object-contain mb-3 rounded-lg">
                                    <div class="w-full border-t border-slate-200/80 pt-2 text-center">
                                        <span class="text-xs font-mono font-bold text-slate-700">No. Barcode: {{ $trxId }}</span>
                                    </div>
                                </div>
                                
                                <button type="button" @click="unduhBarcode()" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-sm text-xs flex items-center justify-center gap-1.5 uppercase transition-all active:scale-95">
                                    <i class="fas fa-download"></i>
                                    <span>Simpan Gambar Barcode</span>
                                </button>
                            </div>
                        </div>
                        @endif

                    </div>
                @endif
    
            @else
                {{-- STATUS: LOCKED (CLEAN & MODERN) --}}
                <div class="w-full bg-white border border-slate-200/80 rounded-2xl p-8 sm:p-12 text-center flex flex-col items-center justify-center min-h-[350px] shadow-xs">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-slate-100 rounded-2xl flex items-center justify-center mb-4 text-slate-400 shadow-inner">
                        <i class="fas fa-lock text-2xl sm:text-3xl"></i>
                    </div>
                    <h3 class="text-lg sm:text-xl font-black text-slate-700 uppercase tracking-tight mb-1.5">
                        Akses Informasi Terkunci
                    </h3>
                    <p class="text-slate-500 max-w-sm mx-auto mb-6 text-xs sm:text-sm leading-relaxed">
                        Menu Akun Akademik & PKKMB belum dapat diakses. Silakan selesaikan seluruh tahapan pendaftaran sebelumnya (Data Final).
                    </p>
                    <div class="inline-flex items-center gap-2.5 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-600">
                        <i class="fas fa-shield-alt text-blue-500"></i>
                        <span>Syarat: Selesaikan Tahap 5 (Data Final)</span>
                    </div>
                </div>
            @endif
        </div>
    </template>

</div>