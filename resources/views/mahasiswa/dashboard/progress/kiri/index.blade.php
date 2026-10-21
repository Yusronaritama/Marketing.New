{{-- FILE: resources/views/mahasiswa/dashboard/progress-layouts.blade.php --}}

@php
    // --- 1. LOGIKA STATUS PER STEP ---
    
    // Step 1: Akun Pendaftar (Selalu Completed karena sudah login)
    $s1_status = 'completed';

    // Step 2: Biodata
    $s2_status = $biodataSelesai ? 'completed' : 'active';

    // Step 3: Ujian Seleksi
    $eregSelesai = $ereg && ($ereg->status == 'completed' || $ereg->status == 'final');
    $s3_status = 'pending';
    if ($s2_status == 'completed') {
        if ($eregSelesai && $isUjianSelesai) {
            $s3_status = 'completed';
        } elseif ($eregSelesai) {
            $s3_status = 'active';
        } else {
            $s3_status = 'pending';
        }
    }

    // Step 4: Pembayaran UKT
    $s4_status = 'pending';
    if ($s3_status == 'completed') {
        if ($isBayarLunas) {
            $s4_status = 'completed';
        } elseif ($statusKelulusan == 'LULUS') {
            $s4_status = 'active';
        }
    }

    // Step 5: Data Final (Pelaporan PDDIKTI)
    $s5_status = 'pending';
    if ($s4_status == 'completed') {
        if (isset($isDataFinalAdaDiDB) && $isDataFinalAdaDiDB) {
            $s5_status = 'completed';
        } else {
            $s5_status = 'active';
        }
    }

    // Step 6: Portal Mahasiswa (Generate NIM & Akun)
    $s6_status = 'pending';
    if ($s5_status == 'completed') {
        // Jika NIM sudah ada di tabel pendaftar_get_nim, maka Step 6 dianggap Selesai (100%)
        if (isset($akunSudahAda) && $akunSudahAda) {
            $s6_status = 'completed'; 
        } else {
            $s6_status = 'active'; // Menunggu antrean generate NIM
        }
    }

    // --- 2. DATA ARRAY STEPS UNTUK LOOPING UI ---
    $steps = [
        1 => ['label' => 'Akun', 'id' => 'ACC-01', 'icon' => 'user-check', 'status' => $s1_status],
        2 => ['label' => 'Biodata', 'id' => 'BIO-02', 'icon' => 'fingerprint', 'status' => $s2_status],
        3 => ['label' => 'Ujian', 'id' => 'CBT-03', 'icon' => 'laptop-code', 'status' => $s3_status],
        4 => ['label' => 'Bayar', 'id' => 'PAY-04', 'icon' => 'credit-card', 'status' => $s4_status],
        5 => ['label' => 'Final', 'id' => 'FIN-05', 'icon' => 'server', 'status' => $s5_status],
        6 => ['label' => 'Informasi PKKMB', 'id' => 'SYS-06', 'icon' => 'bullhorn', 'status' => $s6_status],
    ];

    // --- 3. PERHITUNGAN PERSENTASE ---
    $completedCount = 0;
    foreach($steps as $s) {
        if($s['status'] == 'completed') $completedCount++;
    }
    $percentage = round(($completedCount / 6) * 100);

    // --- 4. LABEL STATUS UTAMA (SYSTEM MESSAGES) ---
    $mainStatus = 'System Standby';
    if($percentage >= 100) {
        $mainStatus = 'Registration Accomplished'; 
    } elseif($percentage >= 80) {
        $mainStatus = 'Finalizing';
    } elseif($percentage >= 50) {
        $mainStatus = 'Processing';
    } elseif($percentage > 0) {
        $mainStatus = 'Initializing';
    }
    
    // --- 5. LOGIKA DISPLAY RANK / STATUS KELULUSAN ---
    $rankTitle = 'STATUS SELEKSI';
    $rankSubtitle = '-'; 
    $rankIcon = 'circle-notch'; 
    $rankTheme = 'slate'; 
    $isContentHidden = false;

    if ($statusKelulusan == 'LULUS') {
        if ($isBayarLunas) {
            // Lulus & Sudah Bayar
            $rankTitle = 'LULUS SELEKSI';
            $rankSubtitle = $jurusanDiterima;
            $rankIcon = 'graduation-cap';
            $rankTheme = 'emerald'; 
        } else {
            // Lulus tapi belum bayar (Data detail disembunyikan/dikunci)
            $rankTitle = 'Selesaikan Administrasi';
            $rankSubtitle = 'Selesaikan Pembayaran';
            $rankIcon = 'lock';
            $isContentHidden = true; 
        }
    } elseif ($statusKelulusan == 'TIDAK_LULUS') {
        // Tidak Lulus
        $rankTitle = 'TIDAK LULUS';
        $rankSubtitle = 'Mohon Maaf';
        $rankIcon = 'times-circle';
        $rankTheme = 'red';
    }
@endphp

<div class="w-full flex flex-col gap-8">
        {{-- MAIN CONTAINER --}}
        <div id="tour-progress-bar" class="w-full max-w-5xl mx-auto bg-white rounded-3xl shadow-lg border border-slate-100 p-6 lg:p-8 relative overflow-hidden group transition-all duration-700 ease-out transform opacity-0 scale-95"
     x-data="{
        mounted: false,
        targetPercent: {{ $percentage }},
        currentPercent: 0,
        circumference: 283, 
        
        init() {
            setTimeout(() => { 
                this.mounted = true; 
                $el.classList.remove('opacity-0', 'scale-95');
                $el.classList.add('opacity-100', 'scale-100');
            }, 100);

            setTimeout(() => {
                let interval = setInterval(() => {
                    if (this.currentPercent < this.targetPercent) {
                        this.currentPercent++;
                    } else {
                        clearInterval(interval);
                    }
                }, 20);
            }, 500);
        }
     }"
     x-init="init()">
    
    {{-- Background Tech Grid Pattern --}}
    <div class="absolute inset-0 opacity-[0.05]"
         style="background-image: radial-gradient(#3b82f6 1px, transparent 1px); background-size: 20px 20px;"></div>

    {{-- ================================================= --}}
    {{-- MOBILE VIEW (LIST INTERAKTIF)                     --}}
    {{-- ================================================= --}}
    <div class="block md:hidden relative z-10">
        
        {{-- HEADER RINGKASAN --}}
        <div class="flex items-center justify-between mb-8 pb-4 border-b border-blue-100/20">
            <div>
                <h3 class="font-bold text-lg text-slate-800">Status Pendaftaran</h3>
                <div class="flex items-center gap-2 mt-1">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                    </span>
                    <p class="text-xs font-mono text-slate-600 uppercase">{{ $mainStatus }}</p>
                </div>
            </div>
            {{-- Circular Progress Kecil di Kanan --}}
            <div class="relative w-12 h-12 flex items-center justify-center">
                <svg class="w-full h-full transform -rotate-90">
                    <circle cx="50%" cy="50%" r="20" fill="none" stroke="#e2e8f0" stroke-width="4" />
                    <circle cx="50%" cy="50%" r="20" fill="none" stroke="#3b82f6" stroke-width="4" stroke-linecap="round" stroke-dasharray="125" :stroke-dashoffset="125 - (currentPercent / 100 * 125)" class="transition-all duration-1000 ease-out" />
                </svg>
                <span class="absolute text-[10px] font-bold text-blue-600"><span x-text="currentPercent"></span>%</span>
            </div>
        </div>

        {{-- TIMELINE CONTAINER --}}
        <div class="relative pl-4 space-y-8">
            
            {{-- GARIS PENGHUBUNG VERTIKAL (Background Line) --}}
            <div class="absolute left-[27px] top-2 bottom-4 w-0.5 bg-slate-200"></div>

            @foreach($steps as $num => $item)
                @php
                    $isDone = $item['status'] == 'completed';
                    $isActive = $item['status'] == 'active';
                @endphp

                <div id="nav-mobile-step-{{ $num }}" 
                     @click="activeView = {{ $num }}; window.scrollTo({ top: 0, behavior: 'smooth' })"
                     class="relative flex items-start gap-4 cursor-pointer group"
                     x-show="mounted"
                     x-transition:enter="transition ease-out duration-500"
                     x-transition:enter-start="opacity-0 translate-x-4"
                     x-transition:enter-end="opacity-100 translate-x-0"
                     style="transition-delay: {{ $num * 100 }}ms;">
                    
                    {{-- BULATAN INDIKATOR (Marker) --}}
                    <div class="relative z-10 flex-shrink-0 w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-300 bg-white
                                {{ $isDone ? 'border-emerald-500 bg-emerald-50' : ($isActive ? 'border-blue-500 bg-blue-50 shadow-[0_0_10px_rgba(59,130,246,0.5)] scale-110' : 'border-slate-300') }}">
                        
                        @if($isDone)
                            <div class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
                        @elseif($isActive)
                            <div class="w-2.5 h-2.5 rounded-full bg-blue-500 animate-pulse"></div>
                        @else
                            <div class="w-1.5 h-1.5 rounded-full bg-slate-300"></div>
                        @endif
                    </div>

                    {{-- KONTEN TEKS --}}
                    {{-- PERBAIKAN: Memisahkan atribut class (statis) dan :class (dinamis) --}}
                    <div class="flex-1 -mt-1 p-3 rounded-lg border transition-all duration-300"
                         :class="activeView == {{ $num }} ? 'bg-white border-blue-500 shadow-md translate-x-1' : 
                                '{{ $isActive ? 'bg-white border-blue-200 shadow-sm' : 'bg-transparent border-transparent' }}'">
                        
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-bold transition-colors"
                                  :class="activeView == {{ $num }} ? 'text-blue-700' : 
                                         '{{ $isDone ? 'text-emerald-700' : ($isActive ? 'text-blue-700' : 'text-slate-500') }}'">
                                {{ $item['label'] }}
                            </span>
                            
                            {{-- Badge Status --}}
                            @if($isDone)
                                <i class="fas fa-check-circle text-emerald-500 text-sm"></i>
                            @elseif($isActive)
                                <span class="text-[9px] font-mono bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded">RUNNING</span>
                            @endif
                        </div>

                        {{-- ID Step --}}
                        <div class="text-[10px] font-mono text-slate-400 flex items-center gap-2">
                            <span>{{ $item['id'] }}</span>
                            @if($isActive) 
                                <span class="w-1 h-1 rounded-full bg-blue-400 animate-ping"></span>
                            @endif
                        </div>
                    </div>

                </div>
            @endforeach
        </div>
    </div>

    {{-- ================================================= --}}
    {{-- DESKTOP VIEW (SPACE THEME: SUN & PLANETS)         --}}
    {{-- ================================================= --}}
    <div class="hidden md:flex items-center justify-center min-h-[460px] relative z-10 w-full mt-4">

        {{-- ORBIT TRACKS --}}
        <div class="absolute w-[360px] h-[360px] rounded-full border border-slate-700/40 transition-all duration-1000 delay-100" :class="mounted ? 'scale-100 opacity-100' : 'scale-50 opacity-0'"></div>
        <div class="absolute w-[280px] h-[280px] rounded-full border border-dashed border-slate-600/30 animate-spin-slow transition-all duration-1000 delay-200" style="animation-duration: 150s;" :class="mounted ? 'scale-100 opacity-100' : 'scale-50 opacity-0'"></div>
        <div class="absolute w-[200px] h-[200px] rounded-full border border-slate-700/50 transition-all duration-1000 delay-300" :class="mounted ? 'scale-100 opacity-100' : 'scale-50 opacity-0'"></div>

        {{-- CENTER PROGRESS (THE SUN) --}}
        <div class="absolute z-20 w-36 h-36 rounded-full flex items-center justify-center bg-gradient-to-tr from-orange-600 via-amber-500 to-yellow-300 shadow-[0_0_60px_rgba(245,158,11,0.5)] border-2 border-yellow-200/50 transition-all duration-700 delay-300 overflow-hidden" :class="mounted ? 'scale-100 opacity-100' : 'scale-0 opacity-0'">
            
            {{-- Sun Surface Animation (Optional flare) --}}
            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/stardust.png')] opacity-30 mix-blend-overlay animate-spin-slow" style="animation-duration: 60s;"></div>
            <div class="absolute inset-0 bg-gradient-to-b from-transparent via-white/20 to-transparent opacity-50 animate-pulse"></div>

            {{-- SVG PROGRESS --}}
            <svg class="absolute inset-0 w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="45" fill="none" stroke="rgba(0,0,0,0.15)" stroke-width="4" />
                <circle 
                    cx="50" cy="50" r="45" 
                    fill="none" 
                    stroke="#ffffff" 
                    stroke-width="5" 
                    stroke-linecap="round" 
                    stroke-dasharray="283" 
                    :stroke-dashoffset="283 - (currentPercent / 100 * 283)" 
                    class="transition-all duration-300 ease-out drop-shadow-[0_0_5px_rgba(255,255,255,0.8)]" 
                />
            </svg>

            <div class="text-center relative z-10 flex flex-col items-center">
                <span class="text-[9px] font-bold text-orange-950/80 uppercase tracking-widest mb-0.5 drop-shadow-sm">Total</span>
                <div class="text-3xl font-black text-white tracking-tighter drop-shadow-md">
                    <span x-text="currentPercent"></span><span class="text-lg text-yellow-100 font-bold">%</span>
                </div>
                <div class="mt-2 flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-black/30 border border-white/20 backdrop-blur-sm">
                    <div class="w-1.5 h-1.5 rounded-full {{ $percentage >= 100 ? 'bg-emerald-400' : 'bg-yellow-300 animate-pulse' }} shadow-[0_0_5px_currentColor]"></div>
                    <span class="text-[9px] font-bold uppercase text-white tracking-wide">{{ $mainStatus }}</span>
                </div>
            </div>
        </div>

        {{-- ICONS / NODES (PLANETS) --}}
        <div class="absolute inset-0 w-full h-full pointer-events-none">
            @foreach($steps as $num => $item)
                @php
                    $radius = 140; 
                    $angleDeg = (($num - 1) * 60) - 90;
                    $angleRad = deg2rad($angleDeg);
                    $isDone = $item['status'] == 'completed';
                    $isActive = $item['status'] == 'active';

                    // Posisi Label
                    $posClass = ''; $alignClass = '';
                    if ($angleDeg == -90) { $posClass = 'bottom-[4rem]'; $alignClass = 'items-center'; }
                    elseif ($angleDeg == 90) { $posClass = 'top-[4rem]'; $alignClass = 'items-center'; }
                    elseif ($angleDeg > -90 && $angleDeg < 90) { $posClass = 'left-[4rem]'; $alignClass = 'items-start pl-2'; }
                    else { $posClass = 'right-[4rem]'; $alignClass = 'items-end pr-2'; }
                @endphp

                <div id="nav-desktop-step-{{ $num }}" @click="activeView = {{ $num }}; window.scrollTo({ top: 0, behavior: 'smooth' })"
                     class="absolute transform -translate-x-1/2 -translate-y-1/2 flex flex-col items-center group pointer-events-auto cursor-pointer"
                     style="left: calc(50% + {{ $radius * cos($angleRad) }}px); top: calc(50% + {{ $radius * sin($angleRad) }}px);">
                    
                    {{-- Sun Ray (Connector Line) --}}
                    <div class="absolute top-1/2 left-1/2 h-[1.5px] bg-gradient-to-r from-amber-500/50 to-transparent origin-left -z-10 transition-all duration-1000 ease-out"
                         x-show="mounted"
                         x-transition:enter="transition duration-1000"
                         x-transition:enter-start="w-0 opacity-0"
                         x-transition:enter-end="w-full opacity-100"
                         style="width: {{ $isActive ? '75px' : '0px' }}; transform: rotate({{ $angleDeg + 180 }}deg); transition-delay: {{ 500 + ($num * 100) }}ms">
                    </div>

                    {{-- PLANET CIRCLE --}}
                    <div x-show="mounted"
                         x-transition:enter="transition ease-out duration-500"
                         x-transition:enter-start="opacity-0 scale-0"
                         x-transition:enter-end="opacity-100 scale-100"
                         style="transition-delay: {{ 600 + ($num * 100) }}ms"
                         class="relative z-30 w-[3.25rem] h-[3.25rem] rounded-full flex items-center justify-center border-2 transition-all duration-300 group-hover:scale-110 group-active:scale-95 overflow-hidden"
                         :class="activeView == {{ $num }} ? 'bg-gradient-to-br from-cyan-400 to-blue-700 border-cyan-300 shadow-[0_0_30px_rgba(34,211,238,0.6)] scale-110' : 
                                 '{{ $isActive ? 'bg-gradient-to-br from-blue-500 to-indigo-800 border-blue-400 shadow-[0_0_20px_rgba(59,130,246,0.5)]' : 
                                   ($isDone ? 'bg-gradient-to-br from-emerald-400 to-teal-800 border-emerald-400 shadow-[0_0_15px_rgba(16,185,129,0.3)]' : 'bg-slate-800 border-slate-600 group-hover:border-slate-500 group-hover:shadow-[0_0_15px_rgba(255,255,255,0.1)]') }}'">
                        
                        {{-- Atmosphere glow for planets --}}
                        <div class="absolute inset-0 bg-white/10 rounded-full" style="box-shadow: inset -5px -5px 15px rgba(0,0,0,0.5);"></div>

                        <i class="fas fa-{{ $item['icon'] }} text-lg relative z-10 transition-colors drop-shadow-md"
                           :class="activeView == {{ $num }} ? 'text-white' : 
                                   '{{ $isDone ? 'text-emerald-50' : ($isActive ? 'text-blue-100' : 'text-slate-400 group-hover:text-slate-200') }}'"></i>

                        @if($isActive)
                            <div class="absolute -inset-1 rounded-full border border-cyan-400 animate-ping opacity-60" x-show="activeView != {{ $num }}"></div>
                        @endif
                    </div>

                    {{-- PLANET LABEL --}}
                    <div x-show="mounted"
                         x-transition:enter="transition ease-out duration-500"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         style="transition-delay: {{ 700 + ($num * 100) }}ms"
                         class="absolute w-36 flex flex-col {{ $alignClass }} justify-center pointer-events-none {{ $posClass }}">
                        
                        <div class="text-[9px] font-mono mb-0.5 tracking-wider transition-colors"
                             :class="activeView == {{ $num }} ? 'text-cyan-300' : 'text-slate-500'">
                             {{ $item['id'] }}
                        </div>

                        <div class="backdrop-blur-md px-3 py-1.5 rounded-xl border transition-all duration-300"
                             :class="activeView == {{ $num }} ? 'bg-cyan-900/60 border-cyan-500/50 shadow-[0_0_15px_rgba(34,211,238,0.2)] -translate-y-1' : 
                                     '{{ $isActive ? 'bg-blue-900/50 border-blue-500/40' : 
                                       ($isDone ? 'bg-emerald-900/40 border-emerald-500/30' : 'bg-slate-900/50 border-slate-700/50 opacity-80') }}'">
                            
                            <h4 class="font-bold text-[10px] uppercase tracking-wider whitespace-nowrap transition-colors"
                                :class="activeView == {{ $num }} ? 'text-white drop-shadow-[0_0_5px_rgba(255,255,255,0.5)]' : 
                                        '{{ $isActive ? 'text-blue-200' : ($isDone ? 'text-emerald-200' : 'text-slate-400') }}'">
                                {{ $item['label'] }}
                            </h4>
                        </div>

                        <div class="mt-1.5 flex items-center gap-1.5" x-show="activeView == {{ $num }}">
                            <div class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse shadow-[0_0_5px_#22d3ee]"></div>
                            <span class="text-[9px] font-bold uppercase text-cyan-400 tracking-widest">Orbiting</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        </div>
    </div>

    {{-- INFO PANELS --}}
    <div class="w-full flex flex-col gap-6 mt-6">

    {{-- CARD STATUS KELULUSAN (THEME: UBP PRIDE) --}}
    <div class="relative overflow-hidden rounded-2xl border transition-all duration-500 hover:shadow-2xl hover:scale-[1.01]
                {{-- LOGIKA WARNA BACKGROUND & BORDER --}}
                {{ $rankTheme == 'emerald' 
                    ? 'bg-gradient-to-br from-blue-600 via-blue-700 to-blue-800 border-blue-500 shadow-blue-200' 
                    : ($rankTheme == 'red' ? 'bg-red-50 border-red-200' : 'bg-slate-50 border-slate-200') }} 
                p-6 shadow-xl">
        
        {{-- Background Pattern --}}
        <div class="absolute inset-0 opacity-10" style="background-image: url('https://www.transparenttextures.com/patterns/cubes.png');"></div>
        
        {{-- Dekorasi Cahaya (Glow Effect) --}}
        @if($rankTheme == 'emerald')
            <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-yellow-400 rounded-full blur-[80px] opacity-20 animate-pulse"></div>
            <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-red-600 rounded-full blur-[80px] opacity-20"></div>
        @endif
        
        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
            
            {{-- Kiri: Icon & Title --}}
            <div class="flex items-center gap-5 text-center md:text-left w-full md:w-auto justify-center md:justify-start">
                {{-- KOTAK IKON: Merah UBP saat Lulus --}}
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center shadow-lg border border-white/10
                            {{ $rankTheme == 'emerald' ? 'bg-white text-blue-600 shadow-blue-900/20' : 'bg-slate-200 text-slate-500' }}">
                    <i class="fas fa-{{ $rankIcon }} text-3xl"></i>
                </div>
                
                <div>
                    {{-- LABEL KECIL: Kuning Emas saat Lulus --}}
                    <p class="text-xs font-mono font-bold uppercase tracking-[0.2em] mb-1
                              {{ $rankTheme == 'emerald' ? 'text-blue-100 drop-shadow-sm' : 'text-slate-500' }}">
                        Status Seleksi
                    </p>
                    
                    {{-- JUDUL UTAMA: Putih Tegas --}}
                    <h2 class="text-3xl font-black italic uppercase tracking-tighter {{ $rankTheme == 'emerald' ? 'text-white' : 'text-slate-700' }}">
                        {{ $rankTitle }}
                    </h2>
                </div>
            </div>

            {{-- Kanan: Detail Content --}}
            <div class="w-full md:w-auto bg-white/20 backdrop-blur-sm rounded-xl p-4 border border-white/30 min-w-[200px]">
                @if($isContentHidden)
                    {{-- Tampilan Terkunci (Formal) --}}
                    <div class="flex items-center justify-center gap-3 text-slate-300">
                        <i class="fas fa-lock text-xl animate-pulse text-yellow-500"></i>
                        <div class="text-left">
                            <p class="text-[10px] uppercase tracking-wider text-slate-400">Informasi Terkunci</p>
                            <p class="text-sm font-bold text-white">Selesaikan Administrasi</p>
                        </div>
                    </div>
                @else
                    {{-- Tampilan Normal (Lulus/Placeholder) --}}
                    <div class="text-center md:text-right">
                        <p class="text-[10px] uppercase tracking-wider mb-1 
                                  {{ $rankTheme == 'emerald' ? 'text-yellow-400' : 'text-slate-400' }}">
                            Program Studi
                        </p>
                        <p class="text-xl font-bold text-white tracking-wide">
                            {{ $rankSubtitle }}
                        </p>
                    </div>
                @endif
            </div>

        </div>
    </div>
    
    {{-- ================================================= --}}
    {{-- INFORMASI PENTING (SELALU TAMPIL)                   --}}
    {{-- ================================================= --}}
    
    {{-- BOX INFO 1: KUOTA & RESET (AMBER) --}}
    <div class="relative overflow-hidden rounded-xl border border-amber-200 bg-amber-50 p-1 group shadow-sm">
        
        {{-- Dekorasi Glow Halus --}}
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-amber-500/10 rounded-full blur-3xl group-hover:bg-amber-500/20 transition-all duration-700"></div>

        <div class="flex items-start gap-4 p-4 relative z-10">
            {{-- Ikon --}}
            <div class="flex-shrink-0 mt-1">
                <div class="w-10 h-10 rounded-lg bg-amber-100 border border-amber-200 flex items-center justify-center shadow-inner shadow-amber-500/10">
                    <i class="fas fa-info-circle text-amber-400 text-xl drop-shadow-[0_0_8px_rgba(251,191,36,0.8)]"></i>
                </div>
            </div>

            {{-- Konten Teks --}}
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <h3 class="text-amber-700 font-bold text-sm tracking-wide">
                        Informasi Kuota & Akun
                    </h3>
                    <span class="px-2 py-0.5 rounded text-[9px] bg-amber-100 text-amber-700 border border-amber-200 font-bold uppercase tracking-wider">
                        Limited Slot
                    </span>
                </div>
                
                <p class="text-slate-600 text-xs leading-relaxed font-medium">
                    Sistem akan melakukan <span class="text-slate-800 font-extrabold border-b border-dashed border-slate-400">reset data otomatis</span> pada pergantian gelombang. 
                    Demi keamanan akun, silakan <span class="text-amber-600 font-bold">selesaikan ujian</span> dan 
                    <span class="text-emerald-600 font-bold">pembayaran UKT</span> untuk mengunci kuota kursi Anda.
                </p>
            </div>
        </div>

        {{-- Bottom Line --}}
        <div class="absolute bottom-0 left-0 w-full h-[2px] bg-slate-800">
            <div class="h-full bg-gradient-to-r from-amber-500 to-transparent w-1/3 opacity-80"></div>
        </div>
    </div>

    {{-- BOX INFO 2: KETENTUAN GANTI PRODI (BLUE) --}}
    <div class="relative overflow-hidden rounded-xl border border-blue-200 bg-blue-50 p-1 group shadow-sm">
        
        {{-- Dekorasi Glow Halus --}}
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-blue-500/10 rounded-full blur-3xl group-hover:bg-blue-500/20 transition-all duration-700"></div>

        <div class="flex items-start gap-4 p-4 relative z-10">
            {{-- Ikon --}}
            <div class="flex-shrink-0 mt-1">
                <div class="w-10 h-10 rounded-lg bg-blue-100 border border-blue-200 flex items-center justify-center shadow-inner shadow-blue-500/10">
                    <i class="fas fa-exchange-alt text-blue-400 text-xl drop-shadow-[0_0_8px_rgba(96,165,250,0.8)]"></i>
                </div>
            </div>

            {{-- Konten Teks --}}
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <h3 class="text-blue-700 font-bold text-sm tracking-wide">
                        Ketentuan Ganti Program Studi
                    </h3>
                    <span class="px-2 py-0.5 rounded text-[9px] bg-blue-100 text-blue-700 border border-blue-200 font-bold uppercase tracking-wider">
                        Penting
                    </span>
                </div>
                
                <p class="text-slate-600 text-xs leading-relaxed font-medium">
                    Perubahan pilihan Program Studi (Prodi) <span class="text-blue-600 font-extrabold border-b border-dashed border-blue-300">maksimal hanya dapat dilakukan 2 (dua) kali</span> selama masa pendaftaran. 
                    Pastikan pilihan prodi Anda sudah tepat dan final sebelum menyelesaikan proses administrasi kelulusan.
                </p>
            </div>
        </div>

        {{-- Bottom Line --}}
        <div class="absolute bottom-0 left-0 w-full h-[2px] bg-slate-800">
            <div class="h-full bg-gradient-to-r from-blue-500 to-transparent w-1/3 opacity-80"></div>
        </div>
    </div>

    </div> {{-- End Info Panels --}}

</div>