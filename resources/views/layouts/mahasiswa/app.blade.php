<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#213448" />
    <title>@yield('title', 'PMB Online') - Universitas Buana Perjuangan</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- 1. FRAMEWORKS & LIBRARIES --}}
    
    {{-- AlpineJS (Core + Plugins) --}}
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.8.0/vanilla-tilt.min.js"></script>
    
    {{-- Fonts & Icons --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- 2. GLOBAL STYLES --}}
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }
        
        /* Mobile Modal Styles */
        .mobile-modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; align-items: center; z-index: 9999; padding: 1rem; }
        .mobile-modal-container { background: white; border-radius: 1rem; width: 100%; max-width: 320px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); overflow: hidden; transform: scale(0.95); transition: transform 0.3s ease; }
        .mobile-modal-header { background: linear-gradient(to right, #e53e3e, #dd6b20); color: white; padding: 1.25rem; text-align: center; }
        .mobile-modal-body { padding: 1.5rem; text-align: center; }
        .mobile-modal-footer { padding: 0 1.5rem 1.5rem; display: flex; flex-direction: column; gap: 0.5rem; }
        .mobile-modal-btn { width: 100%; padding: 0.75rem 1rem; border-radius: 0.75rem; font-weight: 600; font-size: 0.875rem; transition: all 0.2s; border: none; cursor: pointer; }
        .mobile-modal-btn-primary { background: linear-gradient(to right, #4a00e0, #8e2de2); color: white; }
        .mobile-modal-btn-secondary { background: #f1f5f9; color: #64748b; }
    </style>
    @stack('styles')
</head>

{{-- 3. LOGIKA PHP (LOAD SETTING MAINTENANCE) --}}
@php
    $phpActive = false; 
    $phpSchedule = null; 
    $phpIsAdmin = false;
    
    // 1. Cek Data User & Role Admin DULU (Biar variabel $phpIsAdmin tersedia)
    $user = Auth::user();
    if ($user) {
        // User 215 dimasukkan sebagai Admin di sini
        $phpIsAdmin = ($user->role === 'admin' || $user->is_admin == 1 || $user->id == 215 );
        
        $userName = $user->name;
        $userEmail = $user->email;
        $userAvatar = $user->profile_photo_path;
        $initials = strtoupper(substr($userName, 0, 1));
        $avatarUrl = $userAvatar ? (strpos($userAvatar, 'http') === 0 ? $userAvatar : asset('storage/' . $userAvatar)) : null;
    } else {
        $userName = 'Guest'; $userEmail = '-'; $avatarUrl = null; $initials = 'G';
    }

    // 2. Cek File JSON Maintenance
    try {
        if (\Illuminate\Support\Facades\Storage::disk('local')->exists('app_maintenance_mode.json')) {
            $d = json_decode(\Illuminate\Support\Facades\Storage::disk('local')->get('app_maintenance_mode.json'), true);
            $phpActive = $d['active'] ?? false;
            $phpSchedule = $d['scheduled_at'] ?? null;
        }
    } catch (\Exception $e) {}

    // 3. Cek Status Gelombang
    $phpGelombangClosed = false;
    $phpGelombangClosingAt = null;
    $phpGelombangNext = null;

    try {
        $gelombangData = \Illuminate\Support\Facades\Cache::get('gelombang_setting');
        if ($gelombangData) {
            $phpGelombangClosed = $gelombangData['is_closed'] ?? false; 
            $phpGelombangClosingAt = $gelombangData['closing_at'] ?? null;
            $phpGelombangNext = $gelombangData['next_opening_at'] ?? null;
        }
    } catch (\Exception $e) {}

    // ============================================================
    // LOGIKA SAKTI: BYPASS UNTUK ADMIN & USER 215
    // ============================================================
    if ($phpIsAdmin) {
        // 1. Bypass Maintenance
        $phpActive = false; 

        // 2. Bypass Gelombang Tutup (Ini yang kamu minta)
        // Paksa jadi FALSE (buka) walaupun sistem bilang tutup
        $phpGelombangClosed = false; 
    }
@endphp

{{-- 4. LOGIKA JAVASCRIPT SYSTEM GUARD --}}
<script>
    document.addEventListener('alpine:init', () => {
        
        // --- 1. SYSTEM GUARD (MAINTENANCE) ---
        Alpine.data('systemGuard', () => ({
        isAdmin: @json($phpIsAdmin ?? false),
        isActive: @json($phpActive ?? false),
        scheduledAt: @json($phpSchedule ?? null),
        
        // [BARU] 1. Tambahkan variabel estimasi selesai
        // Kita ambil dari PHP jika ada, atau default null
        estimatedEnd: @json($d['estimated_end'] ?? null), 
    
        dismissed: false, 
        showPaymentAlert: false,
    
        get overlayMode() {
            if (this.isAdmin) return false;
            if (this.isActive) return true;
            if (this.scheduledAt) return new Date() >= new Date(this.scheduledAt);
            return false;
        },
        get warningMode() {
            if (this.isAdmin) return false;
            if (this.overlayMode) return false;
            if (this.scheduledAt) return new Date() < new Date(this.scheduledAt);
            return false;
        },
        get showFullModal() { return this.warningMode && !this.dismissed; },
        get showTopBanner() { return this.warningMode && this.dismissed; },
        
        get formattedSchedule() {
            if (!this.scheduledAt) return '';
            let d = new Date(this.scheduledAt);
            return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) + 
                   ' - ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB';
        },
    
        // [BARU] 2. Helper untuk format tanggal Estimasi Selesai
        get formattedEstimatedEnd() {
            if (!this.estimatedEnd) return 'Tidak Ditentukan';
            let d = new Date(this.estimatedEnd);
            return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) + 
                   ' - ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB';
        },
    
        closeModal() { this.dismissed = true; },
        
        startWatch() {
            // Panggil sekali saat awal load agar data sinkron
            this.fetchStatus();
    
            setInterval(() => {
                this.fetchStatus();
            }, 5000);
        },
    
        // [MODIFIKASI] Pisahkan logic fetch agar lebih rapi
        async fetchStatus() {
            try {
                let res = await fetch('{{ url("/admin/perbaikan/status") }}', { headers: { 'Accept': 'application/json' } });
                let data = await res.json();
                
                // Cek jika jadwal berubah, reset dismissal warning
                if (this.scheduledAt !== data.scheduled_at && data.scheduled_at) {
                    this.dismissed = false;
                    sessionStorage.removeItem('maintenance_ack');
                }
    
                this.isActive = data.is_maintenance;
                this.scheduledAt = data.scheduled_at;
                
                // [BARU] 3. Update data estimasi dari server
                this.estimatedEnd = data.estimated_end; 
    
            } catch (e) {}
        },
    
        init() { console.log('✅ System Guard Loaded'); this.startWatch(); }
    }));

        // --- 2. GELOMBANG GUARD (FIXED) ---
        Alpine.data('gelombangGuard', () => ({
            // 1. DATA STATE
            isAdmin: @json($phpIsAdmin ?? false), 
            isClosed: @json($phpGelombangClosed ?? false), 
            closingAt: @json($phpGelombangClosingAt ?? null),
            nextOpeningAt: @json($phpGelombangNext ?? null),
            dismissed: sessionStorage.getItem('gelombang_ack') === 'true',
        
            // 2. LOGIC GETTERS (Untuk UI)
            get overlayMode() {
                if (this.isAdmin) return false; // Bypass Admin
                if (String(this.isClosed) === "true" || this.isClosed === true) return true;
                if (this.closingAt) {
                    return new Date() >= new Date(this.closingAt);
                }
                return false;
            },
        
            get countdownMode() {
                if (this.isAdmin) return false;
                if (this.overlayMode) return false;
                if (this.closingAt) {
                    return new Date() < new Date(this.closingAt); 
                }
                return false;
            },
        
            get showFullModal() { return this.countdownMode && !this.dismissed; },
            get showTopBanner() { return this.countdownMode && this.dismissed; },
        
            get formattedSchedule() {
                if (!this.closingAt) return '';
                let d = new Date(this.closingAt);
                return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) + 
                       ' - ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB';
            },
        
            get formattedNextOpening() {
                if (!this.nextOpeningAt) return 'Segera Diumumkan';
                try {
                    let d = new Date(this.nextOpeningAt);
                    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) + 
                           ' ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB';
                } catch(e) { return '-'; }
            },
        
            // 3. ACTIONS
            closeModal() {
                this.dismissed = true;
                sessionStorage.setItem('gelombang_ack', 'true');
            },
        
            // INI FUNGSI FETCH YANG ANDA MAKSUD
            async fetchGelombangStatus() {
                try {
                    let res = await fetch('{{ url("/admin/gelombang/status") }}', { 
                        headers: { 'Accept': 'application/json' } 
                    });
                    
                    if(res.ok) {
                        let data = await res.json();
                        
                        // Logic Bypass Admin (Jika server kirim True, kita paksa False di browser Admin)
                        if (this.isAdmin) {
                            data.is_closed = false;
                        }
        
                        // Reset modal jika status berubah dari Tutup ke Buka atau sebaliknya
                        if (String(this.isClosed) !== String(data.is_closed)) {
                            this.dismissed = false; 
                            sessionStorage.removeItem('gelombang_ack');
                        }
        
                        // Update data di Alpine
                        this.isClosed = data.is_closed;
                        this.closingAt = data.closing_at;
                        this.nextOpeningAt = data.next_opening_at;
                    }
                } catch (e) {
                    console.warn("Gagal fetch status gelombang.");
                }
            },
        
            startWatch() {
                // Jalankan polling setiap 3 detik
                setInterval(() => {
                    this.fetchGelombangStatus();
                }, 3000); 
            },
        
            init() { 
                console.log('✅ Gelombang Guard Initialized');
                // Langsung cek status saat halaman dimuat
                this.fetchGelombangStatus();
                this.startWatch(); 
            }
        }));

    });
</script>



{{-- 5. BODY START (INIT ALPINE DATA) --}}
<body x-data="systemGuard" class="antialiased text-slate-100 bg-[#0A1930] min-h-screen flex flex-col relative overflow-x-hidden">

    <!-- UBP Blue Glassmorphism Background -->
    <div class="fixed inset-0 -z-10 w-full h-full pointer-events-none overflow-hidden bg-[#0A1930]">
        <!-- Abstract Glowing Orbs -->
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-blue-600/30 mix-blend-screen blur-[120px] animate-pulse"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] rounded-full bg-cyan-500/20 mix-blend-screen blur-[120px] animate-pulse" style="animation-delay: 2s;"></div>
        <div class="absolute top-[20%] right-[10%] w-[30%] h-[30%] rounded-full bg-indigo-500/30 mix-blend-screen blur-[100px] animate-pulse" style="animation-delay: 4s;"></div>
        <!-- Subtle Grid -->
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff0a_1px,transparent_1px),linear-gradient(to_bottom,#ffffff0a_1px,transparent_1px)] bg-[size:32px_32px]"></div>
        <!-- Overlay -->
        <div class="absolute inset-0 bg-gradient-to-br from-transparent via-[#0A1930]/50 to-[#030914] opacity-80"></div>
    </div>

    {{-- === KOMPONEN UI MAINTENANCE (REAKTIF) === --}}
    
    {{-- A. WARNING BANNER --}}
    <div x-data="systemGuard()">

        <template x-if="warningMode">
            <div>
                <div x-show="showFullModal"
                     class="fixed inset-0 z-[9999] bg-slate-50 transition-transform duration-700 ease-in-out"
                     x-transition:leave="transform -translate-y-full opacity-0">
                    
                    <div class="absolute inset-0 overflow-hidden pointer-events-none">
                        <div class="absolute inset-0 opacity-[0.05]" 
                             style="background-image: radial-gradient(#1e3a8a 1px, transparent 1px); background-size: 30px 30px;">
                        </div>
                        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-[300px] md:w-[500px] h-[300px] md:h-[500px] bg-blue-900/20 rounded-full blur-3xl mix-blend-multiply animate-pulse"></div>
                        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-[250px] md:w-[400px] h-[250px] md:h-[400px] bg-yellow-500/20 rounded-full blur-3xl mix-blend-multiply"></div>
                    </div>
        
                    <div class="absolute inset-0 overflow-y-auto">
                        <div class="min-h-full flex items-center justify-center py-10 px-4 md:px-12">
                            
                            <div class="relative z-10 w-full max-w-7xl grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12 items-center">
                                
                                <div class="order-1 md:order-2 flex justify-center">
                                    <img src="{{ asset('/images/Maintenance.jpg') }}" 
                                         alt="Maintenance Illustration" 
                                         class="w-64 md:w-full max-w-lg drop-shadow-2xl rounded-2xl border-4 border-white/50 animate-[float_6s_ease-in-out_infinite]">
                                </div>
        
                                <div class="order-2 md:order-1 text-center md:text-left">
                                    
                                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 text-blue-900 font-bold text-[10px] md:text-xs uppercase tracking-widest mb-4 md:mb-6 border border-blue-200">
                                        <span class="w-2 h-2 rounded-full bg-red-600 animate-pulse"></span>
                                        PMB System Notice
                                    </div>
        
                                    <h1 class="text-3xl md:text-6xl font-black text-slate-900 mb-4 md:mb-6 leading-tight">
                                        Jadwal <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-800 via-red-600 to-yellow-500">Perbaikan</span> <br> Sistem.
                                    </h1>
        
                                    <p class="text-slate-600 text-sm md:text-xl mb-6 md:mb-8 leading-relaxed max-w-lg mx-auto md:mx-0">
                                        Kami akan melakukan pemeliharaan server rutin. Mohon simpan seluruh pekerjaan Anda sebelum waktu di bawah ini.
                                    </p>
        
                                    <div class="bg-white border-l-4 border-blue-900 shadow-xl rounded-r-xl p-5 md:p-6 mb-8 text-left max-w-md mx-auto md:mx-0 relative overflow-hidden group">
                                        <div class="absolute inset-0 bg-yellow-50/50 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left duration-500"></div>
                                        <div class="relative z-10">
                                            <p class="text-[10px] md:text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Estimasi Waktu Dimulai</p>
                                            <div class="flex items-center gap-3 text-slate-800">
                                                <i class="far fa-clock text-xl md:text-2xl text-red-600"></i>
                                                <span class="font-mono text-lg md:text-2xl font-bold text-blue-900" x-text="formattedSchedule"></span>
                                            </div>
                                        </div>
                                    </div>
        
                                    <div class="flex flex-col md:flex-row gap-4 justify-center md:justify-start pb-4 md:pb-0">
                                        <button type="button" 
                                                @click="closeModal()" 
                                                class="w-full md:w-auto px-8 py-4 rounded-xl bg-blue-900 text-white font-bold text-lg shadow-lg hover:bg-blue-800 hover:shadow-blue-900/30 transform hover:-translate-y-1 transition-all duration-300 border-b-4 border-blue-950">
                                            Saya Mengerti, Lanjutkan
                                        </button>
                                    </div>
                                    
                                    <p class="mt-4 md:mt-6 text-slate-400 text-[10px] md:text-xs">
                                        *Notifikasi ini akan diminimalkan setelah diklik.
                                    </p>
                                </div>
        
                            </div>
                        </div>
                    </div>
                </div>
        
                <div x-show="showTopBanner"
                     class="fixed top-24 left-0 right-0 z-[60] flex justify-center px-4 pointer-events-none transition-all duration-700 ease-out"
                     x-transition:enter="opacity-0 translate-y-[-100%]"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="opacity-0 translate-y-[-100%]">
                    
                     <div class="max-w-4xl w-full bg-blue-900/95 backdrop-blur-sm text-white px-6 py-4 rounded-xl shadow-2xl border-2 border-yellow-400 flex flex-col md:flex-row items-center justify-center gap-4 text-center md:text-left pointer-events-auto">
                        <div class="bg-white/10 p-3 rounded-full animate-pulse flex-shrink-0 border border-white/20">
                            <i class="fas fa-tools text-2xl md:text-3xl text-yellow-400"></i>
                        </div>
                        <div class="flex-grow">
                            <h3 class="font-bold text-lg uppercase mb-1 tracking-wide text-yellow-400">
                                ⚠️ PMB System Maintenance
                            </h3>
                            <p class="font-medium text-sm leading-snug text-blue-100 mb-2 md:mb-1">
                                Mohon selesaikan aktivitas sebelum waktu berikut:
                            </p>
                            <div class="inline-block bg-white text-blue-900 font-mono font-bold px-4 py-1.5 rounded-lg text-sm mt-1 shadow-inner border border-blue-200">
                                <i class="far fa-clock mr-1 text-red-600"></i> 
                                <span x-text="formattedSchedule"></span>
                            </div>
                        </div>
                    </div>
                </div>
        
            </div>
        </template>
    </div>
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
        }
    </style>
    
    {{-- Modal Gelombang --}}
    {{-- === B. GELOMBANG GUARD === --}}
    <div x-data="gelombangGuard()">
        
        {{-- 1. COUNTDOWN WARNING (KUNING/BIRU) --}}
        <template x-if="warningMode">
            <div>
                <div x-show="showFullModal"
                     class="fixed inset-0 z-[9998] bg-slate-50 transition-transform duration-700 ease-in-out"
                     x-transition:leave="transform -translate-y-full opacity-0">
                    
                    <div class="absolute inset-0 overflow-hidden pointer-events-none">
                        <div class="absolute inset-0 opacity-[0.05]" style="background-image: radial-gradient(#d97706 1px, transparent 1px); background-size: 20px 20px;"></div>
                        <div class="absolute top-0 right-0 -mr-10 -mt-10 w-[200px] md:w-[300px] h-[200px] md:h-[300px] bg-amber-500/10 rounded-full blur-2xl animate-pulse"></div>
                    </div>
        
                    <div class="absolute inset-0 overflow-y-auto flex items-center justify-center">
                        <div class="w-full max-w-4xl px-4 py-6">
                            <div class="relative z-10 flex flex-col md:flex-row items-center gap-6 md:gap-8 bg-white/70 backdrop-blur-md p-6 md:p-8 rounded-3xl border border-amber-100 shadow-2xl">
                                
                                <div class="order-1 md:order-2 flex-shrink-0">
                                    <img src="{{ asset('/images/Warning.jpg') }}" onerror="this.src='https://raw.githubusercontent.com/Undraw/undraw-illustrations/master/svg/undraw_warning_cyit.svg'" alt="Warning" class="w-48 md:w-64 drop-shadow-xl rounded-xl animate-[float_6s_ease-in-out_infinite]">
                                </div>
                                
                                <div class="order-2 md:order-1 text-center md:text-left flex-grow">
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-50 text-amber-800 font-bold text-[10px] uppercase tracking-wider mb-3 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span>
                                        Status Pendaftaran
                                    </div>
                                    
                                    <h1 class="text-2xl md:text-4xl font-black text-slate-900 mb-3 leading-tight">
                                        Data Anda <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-500 to-red-600">Belum Lengkap</span>.
                                    </h1>
                                    
                                    <div class="bg-amber-50/50 border-l-4 border-amber-500 shadow-sm rounded-r-lg p-3 md:p-4 mb-5 text-left inline-block">
                                        <div class="flex items-start gap-3 text-slate-700">
                                            <i class="fas fa-exclamation-circle text-xl text-amber-600 mt-0.5"></i>
                                            <p class="text-sm font-medium leading-relaxed">
                                                Harap segera melengkapi unggahan berkas dan menyimpan pendaftaran Anda agar data dapat kami proses ke tahap validasi.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <button type="button" @click="closeModal()" class="w-full md:w-auto block px-6 py-2.5 rounded-lg bg-amber-600 text-white font-bold text-sm shadow-md hover:bg-amber-700 transition-colors">
                                        Lanjutkan Pengisian Data
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        
                <div x-show="showTopBanner"
                     class="fixed top-20 left-0 right-0 z-[60] flex justify-center px-4 pointer-events-none transition-all duration-700 ease-out"
                     x-transition:enter="opacity-0 translate-y-[-100%]"
                     x-transition:enter-end="opacity-100 translate-y-0">
                     
                     <div class="max-w-2xl w-full bg-amber-50/95 backdrop-blur-sm text-amber-900 px-4 py-2.5 rounded-lg shadow-xl border-l-4 border-amber-500 flex items-center justify-between gap-3 pointer-events-auto">
                        <div class="flex items-center gap-3">
                            <div class="bg-amber-200/50 p-2 rounded-full animate-pulse">
                                <i class="fas fa-exclamation-triangle text-lg text-amber-600"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-sm md:text-base uppercase tracking-wide leading-none mb-0.5 text-amber-800">⚠️ Pendaftaran Belum Selesai</h3>
                                <p class="text-[10px] md:text-xs font-medium text-amber-700">Segera lengkapi berkas persyaratan Anda.</p>
                            </div>
                        </div>
                        
                        <button type="button" @click="closeModal()" class="bg-amber-500 text-white font-bold px-3 py-1.5 rounded text-xs shadow-sm hover:bg-amber-600 transition-colors flex-shrink-0">
                            Lengkapi Sekarang
                        </button>
                    </div>
                </div>
            </div>
        </template>

        {{-- 2. OVERLAY HITAM (BLOCKER) - INI YANG TADI ERROR/DUPLIKAT --}}
        <template x-if="overlayMode">
            <div class="fixed inset-0 z-[99999] bg-black text-white flex flex-col items-center justify-center text-center p-6">
                <div class="mb-6 relative">
                    <div class="absolute inset-0 bg-red-500 blur-xl opacity-20 rounded-full animate-pulse"></div>
                    <i class="fas fa-calendar-times text-6xl md:text-7xl text-red-500 relative z-10"></i>
                </div>
                <h2 class="text-3xl md:text-5xl font-black mb-4 uppercase tracking-tight">Pendaftaran <span class="text-red-500">Ditutup</span></h2>
                <p class="text-gray-400 mb-8 max-w-xl text-lg leading-relaxed">
                    Mohon maaf, gelombang pendaftaran saat ini telah berakhir. <br>
                    Data Anda tersimpan aman, silakan menunggu pembukaan gelombang berikutnya.
                </p>
                
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 max-w-md w-full mb-8 relative overflow-hidden" x-show="nextOpeningAt">
                    <div class="absolute top-0 left-0 w-1 h-full bg-blue-500"></div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Gelombang Selanjutnya</p>
                    <div class="flex items-center gap-3">
                        <i class="fas fa-calendar-alt text-blue-500 text-2xl"></i>
                        <span class="text-xl md:text-2xl font-mono font-bold text-white" x-text="formattedNextOpening"></span>
                    </div>
                </div>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="group flex items-center gap-3 px-8 py-3 rounded-full bg-white/10 hover:bg-white/20 text-white font-semibold transition-all border border-white/10">
                        <span>Keluar Aplikasi</span> <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                    </button>
                </form>
                <div x-init="document.body.style.overflow = 'hidden'" x-effect="overlayMode ? document.body.style.overflow = 'hidden' : document.body.style.overflow = 'auto'"></div>
            </div>
        </template>

    </div>

    {{-- B. OVERLAY HITAM --}}
    <template x-if="overlayMode">
        <div class="fixed inset-0 z-[99999] bg-slate-900 text-white flex flex-col items-center justify-center text-center p-4">
            
            {{-- Animasi Maintenance --}}
            <div class="text-6xl mb-6 animate-bounce">
                <i class="fas fa-tools text-blue-500"></i>
            </div>
            
            <h2 class="text-3xl md:text-4xl font-black mb-4 tracking-tight">
                Sistem Sedang <span class="text-blue-500">Perbaikan</span>
            </h2>
            
            <p class="text-slate-400 mb-8 max-w-lg text-sm md:text-base leading-relaxed">
                Mohon maaf, saat ini kami sedang melakukan pemeliharaan sistem untuk meningkatkan layanan PMB. <br>
                Akses dashboard ditutup sementara.
            </p>
    
            {{-- Estimasi Waktu (Hanya muncul jika ada datanya) --}}
            <div class="mb-8 px-6 py-3 bg-slate-800 rounded-lg border border-slate-700 inline-block" x-show="estimatedEnd">
                <p class="text-xs text-slate-500 uppercase font-bold mb-1 flex items-center justify-center gap-1">
                    <i class="far fa-clock"></i> Estimasi Selesai
                </p>
                {{-- UBAH DI SINI: Gunakan formattedEstimatedEnd --}}
                <div class="font-mono text-xl text-yellow-400 font-bold" x-text="formattedEstimatedEnd"></div>
            </div>
    
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-full transition shadow-lg shadow-blue-900/50 flex items-center gap-2">
                    <i class="fas fa-sign-out-alt"></i> Keluar Aplikasi
                </button>
            </form>
            
            {{-- Matikan Scroll Halaman saat Overlay Muncul --}}
            <div x-init="document.body.style.overflow = 'hidden'" x-effect="overlayMode ? document.body.style.overflow = 'hidden' : document.body.style.overflow = 'auto'"></div>
        </div>
    </template>
    
    

    {{-- === KONTEN HALAMAN UTAMA === --}}

    {{-- Navbar (Dynamic Island Concept) --}}
<div class="sticky top-4 z-50 w-full px-4 md:px-6 lg:px-8 max-w-6xl mx-auto transition-all duration-300 pointer-events-none"
     x-data="{ scrolled: false }" @scroll.window="scrolled = (window.pageYOffset > 20)">
    
    <nav class="w-full bg-[#0A1930]/70 backdrop-blur-2xl border border-white/10 rounded-[2rem] shadow-[0_10px_40px_rgba(0,0,0,0.3)] pointer-events-auto transition-all duration-500 relative"
         :class="{ 'shadow-[0_20px_50px_rgba(0,0,0,0.7)] border-white/20 bg-[#0A1930]/95 translate-y-1 scale-[0.99]': scrolled }">
         
        {{-- Background Effects Wrapper --}}
        <div class="absolute inset-0 rounded-[2rem] overflow-hidden pointer-events-none">
            {{-- Ambient Glow --}}
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600/10 via-transparent to-cyan-500/10 opacity-50"></div>
            
            {{-- UBPK color strip: Biru | Merah | Kuning --}}
            <div class="absolute top-0 left-0 w-full flex h-[3px] opacity-90">
                <div class="flex-1 bg-blue-500 shadow-[0_0_10px_rgba(59,130,246,0.8)]"></div>
                <div class="flex-1 bg-red-500 shadow-[0_0_10px_rgba(239,68,68,0.8)]"></div>
                <div class="flex-1 bg-yellow-400 shadow-[0_0_10px_rgba(250,204,21,0.8)]"></div>
            </div>
        </div>

        <div class="px-5 sm:px-6 relative z-10">
            <div class="flex justify-between h-16 items-center">

            {{-- LOGO --}}
            <a href="{{ route('mahasiswa.dashboard') }}" class="flex items-center gap-3 group flex-shrink-0">
                <div class="p-1.5 border border-white/10 rounded-full bg-white/5 shadow-inner group-hover:border-blue-400/50 group-hover:bg-blue-900/30 transition-all duration-300">
                    <img src="{{ asset('images/logo_ubp.png') }}" alt="Logo" class="h-7 w-7 object-contain">
                </div>
                <div class="hidden xl:block leading-tight">
                    <h1 class="font-black text-white text-[15px] tracking-tight group-hover:text-blue-300 transition-colors">PMB Online</h1>
                    <p class="text-[8px] text-blue-200/70 font-bold uppercase tracking-[0.2em] border-l-2 border-blue-500 pl-2">Univ. Buana Perjuangan</p>
                </div>
            </a>

            {{-- MENU TENGAH (Pill style) --}}
            <div class="hidden md:flex items-center absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 bg-black/40 border border-white/5 rounded-full p-1 gap-1 shadow-inner backdrop-blur-md">
                <a href="{{ route('mahasiswa.biodata.create') }}"
                   class="flex items-center gap-2 px-5 py-2 rounded-full text-[10px] font-bold uppercase tracking-widest transition-all duration-300
                   {{ request()->routeIs('mahasiswa.biodata.*')
                       ? 'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-[0_0_15px_rgba(37,99,235,0.4)] border border-blue-400/50'
                       : 'text-slate-400 hover:text-white hover:bg-white/10' }}">
                    <i class="fas fa-user-edit"></i>
                    Biodata
                </a>
            
                <a href="{{ route('mahasiswa.dashboard') }}"
                   class="flex items-center gap-2 px-5 py-2 rounded-full text-[10px] font-bold uppercase tracking-widest transition-all duration-300
                   {{ request()->routeIs('mahasiswa.dashboard')
                       ? 'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-[0_0_15px_rgba(37,99,235,0.4)] border border-blue-400/50'
                       : 'text-slate-400 hover:text-white hover:bg-white/10' }}">
                    <i class="fas fa-home"></i>
                    Dashboard
                </a>
            
                <a href="{{ route('mahasiswa.ringkasan') }}"
                   class="flex items-center gap-2 px-5 py-2 rounded-full text-[10px] font-bold uppercase tracking-widest transition-all duration-300
                   {{ request()->routeIs('mahasiswa.ringkasan')
                       ? 'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-[0_0_15px_rgba(37,99,235,0.4)] border border-blue-400/50'
                       : 'text-slate-400 hover:text-white hover:bg-white/10' }}">
                    <i class="fas fa-file-contract"></i>
                    Ringkasan
                </a>
            </div>

            {{-- PROFIL KANAN --}}
            <div class="hidden md:flex items-center relative" x-data="{ dropdownOpen: false }" @click.away="dropdownOpen = false">
                <button @click="dropdownOpen = !dropdownOpen"
                        class="flex items-center gap-3 px-3 py-1.5 rounded-full border border-transparent hover:border-white/10 hover:bg-white/5 transition-all group cursor-pointer">
                    <div class="text-right">
                        <p class="text-[9px] text-blue-300/70 font-bold uppercase tracking-widest leading-none mb-1">Calon Mahasiswa</p>
                        <p class="text-[12px] font-bold text-white group-hover:text-blue-300 transition-colors leading-none">{{ $userName }}</p>
                    </div>
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-900 to-slate-800 border border-white/20 flex items-center justify-center overflow-hidden flex-shrink-0 shadow-inner">
                        @if($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="Avatar" class="h-full w-full object-cover">
                        @else
                            <span class="font-bold text-blue-300 text-xs">{{ $initials }}</span>
                        @endif
                    </div>
                    <i class="fas fa-chevron-down text-[9px] text-slate-500 group-hover:text-white transition-transform duration-300"
                       :class="dropdownOpen ? 'rotate-180' : ''"></i>
                </button>

                {{-- Dropdown — posisi relative terhadap parent div --}}
                <div x-show="dropdownOpen"
                     x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 top-full mt-2 w-64 bg-slate-900 border border-slate-700 rounded-2xl shadow-[0_10px_40px_rgba(0,0,0,0.8)] overflow-hidden z-50">

                    {{-- UBPK strip --}}
                    <div class="flex h-1">
                        <div class="flex-1 bg-blue-700"></div>
                        <div class="flex-1 bg-red-600"></div>
                        <div class="flex-1 bg-yellow-400"></div>
                    </div>

                    {{-- User info --}}
                    <div class="flex items-center gap-3 px-4 py-4 bg-slate-900 border-b border-slate-800">
                        <div class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center flex-shrink-0 overflow-hidden">
                            @if($avatarUrl)
                                <img src="{{ $avatarUrl }}" class="w-full h-full object-cover">
                            @else
                                <span class="font-bold text-blue-400 text-sm">{{ $initials }}</span>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-white truncate">{{ $userName }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ $userEmail }}</p>
                        </div>
                    </div>

                    {{-- Logout --}}
                    <div class="p-2">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="w-full flex items-center gap-2.5 px-3 py-2.5 text-xs font-bold text-slate-400 hover:bg-red-950/50 hover:text-red-400 rounded-xl transition-all uppercase tracking-wide">
                                <i class="fas fa-sign-out-alt w-4 text-center"></i>
                                Keluar Aplikasi
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            </div>
        </div>
    </nav>
</div>

{{-- Main Content --}}
<main class="flex-grow relative z-0 py-8 px-4 sm:px-6 lg:px-8 pb-28">
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition
             class="max-w-7xl mx-auto mb-6 flex items-center justify-between gap-3 bg-emerald-50 border border-emerald-200 rounded-2xl px-4 py-3 shadow-sm">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 bg-emerald-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-check text-emerald-600 text-xs"></i>
                </div>
                <p class="text-sm font-semibold text-emerald-800">{{ session('success') }}</p>
            </div>
            <button @click="show = false" class="text-emerald-400 hover:text-emerald-600 text-lg leading-none">&times;</button>
        </div>
    @endif
    @yield('content')
</main>

{{-- Footer --}}
<footer class="relative z-10 mt-auto border-t border-slate-800 bg-slate-950/80 backdrop-blur-md">
    <div class="flex h-0.5">
        <div class="flex-1 bg-blue-700"></div>
        <div class="flex-1 bg-red-600"></div>
        <div class="flex-1 bg-yellow-400"></div>
    </div>
    <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-2">
        <p class="text-xs text-slate-500">&copy; {{ date('Y') }} <span class="font-semibold text-slate-400">Universitas Buana Perjuangan Karawang</span>. All rights reserved.</p>
    </div>
</footer>

{{-- Mobile Bottom Nav (SPEED DIAL FAB) --}}
<div class="md:hidden fixed inset-0 z-[80] pointer-events-none" 
     x-data="{ 
        navOpen: false, 
        openMenu: false,
        isDragging: false,
        isClicking: false,
        x: 0,
        y: 0,
        start: { x: 0, y: 0 },
        
        init() {
            this.x = window.innerWidth / 2 - 32; // 32 is half width of button (64px)
            this.y = window.innerHeight - 96; // 96px from bottom edge
        },
        
        dragStart(e) {
            this.isDragging = true;
            this.isClicking = true;
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            this.start.x = clientX - this.x;
            this.start.y = clientY - this.y;
        },

        dragging(e) {
            if (!this.isDragging) return;
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            
            let newX = clientX - this.start.x;
            let newY = clientY - this.start.y;
            
            // Constrain to screen bounds (with padding)
            newX = Math.max(16, Math.min(newX, window.innerWidth - 80));
            newY = Math.max(16, Math.min(newY, window.innerHeight - 80));

            // If it moved more than 5 pixels, consider it a drag, not a click
            if (Math.abs(newX - this.x) > 5 || Math.abs(newY - this.y) > 5) {
                this.isClicking = false;
            }
            
            this.x = newX;
            this.y = newY;
        },

        dragEnd() {
            this.isDragging = false;
        },

        toggle() {
            if (this.isClicking) {
                this.navOpen = !this.navOpen;
            }
        },
        
        // Menentukan arah mekarnya menu berdasarkan jarak terdekat dengan tepi layar
        get expandDirection() {
            const top = this.y;
            const bottom = window.innerHeight - this.y;
            const left = this.x;
            const right = window.innerWidth - this.x;
            
            const min = Math.min(top, bottom, left, right);
            if (min === top) return 'down';
            if (min === left) return 'right';
            if (min === right) return 'left';
            return 'up';
        },
        
        get isLeftHalf() { return this.x < window.innerWidth / 2; },
        get isTopHalf() { return this.y < window.innerHeight / 2; },
        
        // Smart Wrap: Menghitung posisi XY masing-masing item agar bisa beranak (turun baris)
        getItemStyle(index) {
            if (!this.navOpen) return 'transform: translate(0px, 0px) scale(0.5); opacity: 0; pointer-events: none;';
            
            const dir = this.expandDirection; 
            const isHorizontal = (dir === 'left' || dir === 'right');
            
            // Jarak dinamis:
            // Sumbu X selalu butuh jarak besar (96px) entah itu untuk berjejer (horizontal) 
            // atau untuk turun baris (vertical wrap), karena teks selalu memanjang ke kiri/kanan.
            const gapX = 96; 
            // Sumbu Y butuh jarak besar (96px) HANYA jika ia menjadi baris baru (wrap) 
            // agar tombol tidak menabrak teks tooltip di baris sebelumnya. Jika berjejer lurus vertikal, 64px cukup.
            
            const primaryGap = isHorizontal ? gapX : 64;
            const secondaryGap = isHorizontal ? 96 : gapX;
            
            // Hitung kapasitas maksimal item per baris sebelum mentok layar
            let maxItems = 4;
            if (dir === 'left') maxItems = Math.max(1, Math.floor(this.x / primaryGap));
            else if (dir === 'right') maxItems = Math.max(1, Math.floor((window.innerWidth - this.x - 70) / primaryGap));
            else if (dir === 'up') maxItems = Math.max(1, Math.floor(this.y / primaryGap));
            else if (dir === 'down') maxItems = Math.max(1, Math.floor((window.innerHeight - this.y - 70) / primaryGap));
            
            const line = Math.floor(index / maxItems); 
            const pos = index % maxItems; 
            
            const primaryOffset = (pos + 1) * primaryGap;
            const secondaryOffset = line * secondaryGap;
            
            let dx = 0, dy = 0;
            // Atur sumbu X dan Y berdasarkan arah utama dan posisi kuadran
            if (dir === 'left') {
                dx = -primaryOffset;
                dy = this.isTopHalf ? secondaryOffset : -secondaryOffset;
            } else if (dir === 'right') {
                dx = primaryOffset;
                dy = this.isTopHalf ? secondaryOffset : -secondaryOffset;
            } else if (dir === 'up') {
                dy = -primaryOffset;
                dx = this.isLeftHalf ? secondaryOffset : -secondaryOffset;
            } else if (dir === 'down') {
                dy = primaryOffset;
                dx = this.isLeftHalf ? secondaryOffset : -secondaryOffset;
            }
            
            return `transform: translate(${dx}px, ${dy}px) scale(1); opacity: 1; pointer-events: auto; transition-delay: ${index * 0.04}s;`;
        },

        get tooltipClass() {
            const dir = this.expandDirection;
            if (dir === 'up' || dir === 'down') {
                return this.isLeftHalf ? 'left-16 top-1/2 -translate-y-1/2' : 'right-16 top-1/2 -translate-y-1/2';
            } else {
                return this.isTopHalf ? 'top-16 left-1/2 -translate-x-1/2' : 'bottom-16 left-1/2 -translate-x-1/2';
            }
        }
     }"
     x-on:mousemove.window="dragging($event)"
     x-on:mouseup.window="dragEnd()"
     x-on:touchmove.window="dragging($event)"
     x-on:touchend.window="dragEnd()"
     @resize.window="init()">
    
    {{-- Overlay Latar Belakang (Muncul saat FAB aktif) --}}
    <div x-show="navOpen" x-cloak 
         class="absolute inset-0 bg-[#0A1930]/80 backdrop-blur-md pointer-events-auto"
         @click="navOpen = false"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>

    {{-- Centered Popup Akun --}}
    <div x-show="openMenu" x-cloak class="absolute inset-0 z-[100] flex items-center justify-center p-4 pointer-events-auto">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-md"
             x-show="openMenu"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @click="openMenu = false; navOpen = false"></div>

        <div class="relative w-[90vw] max-w-[360px] bg-[#0A1930]/90 backdrop-blur-3xl border border-white/20 rounded-[2rem] shadow-[0_20px_50px_rgba(0,0,0,0.5)] overflow-hidden"
             x-show="openMenu"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-10"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-10">
            
            <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-500/20 rounded-full blur-[50px] pointer-events-none"></div>

            <div class="p-8 flex flex-col items-center text-center relative z-10 border-b border-white/10">
                <div class="w-20 h-20 bg-slate-800 border-2 border-cyan-400/50 rounded-full flex items-center justify-center overflow-hidden mb-4 shadow-[0_0_15px_rgba(34,211,238,0.3)]">
                    @if($avatarUrl)
                        <img src="{{ $avatarUrl }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-3xl font-black text-cyan-400">{{ $initials }}</span>
                    @endif
                </div>
                <h3 class="font-black text-white text-lg tracking-wide uppercase">{{ $userName }}</h3>
                <p class="text-xs text-slate-400 mt-1 mb-2">{{ $userEmail }}</p>
                <span class="inline-block text-[10px] font-bold text-cyan-300 bg-cyan-500/10 border border-cyan-400/30 rounded-full px-3 py-1 uppercase tracking-widest">
                    Calon Mahasiswa
                </span>
            </div>

            <div class="p-6 relative z-10 bg-black/20">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-3 bg-red-500/10 hover:bg-red-500/20 text-red-400 font-bold py-4 rounded-2xl transition-all border border-red-500/30 hover:shadow-[0_0_15px_rgba(239,68,68,0.2)]">
                        <i class="fas fa-sign-out-alt"></i> KELUAR APLIKASI
                    </button>
                </form>
                <button @click="openMenu = false" class="w-full mt-3 text-xs text-slate-400 hover:text-white font-bold py-3 uppercase tracking-widest transition-colors">
                    Batal
                </button>
            </div>
        </div>
    </div>

    {{-- Container FAB --}}
    <div class="fixed flex items-center justify-center pointer-events-none"
         :style="`top: ${y}px; left: ${x}px; width: 64px; height: 64px; touch-action: none;`">
        
        {{-- Daftar Menu (Smart Wrap Array) --}}
        <div class="absolute inset-0 pointer-events-none z-[-1]">
            
            {{-- AKUN (Index 0) --}}
            <div class="absolute inset-0 flex items-center justify-center transition-all duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
                 :style="getItemStyle(0)">
                <div class="relative group flex items-center justify-center">
                    <button @click="openMenu = true" 
                            class="w-12 h-12 rounded-full bg-slate-800 border border-slate-600 shadow-lg flex items-center justify-center text-white overflow-hidden pointer-events-auto active:scale-95 transition-transform">
                        @if($avatarUrl)
                            <img src="{{ $avatarUrl }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-xs font-bold">{{ $initials }}</span>
                        @endif
                    </button>
                    <span class="absolute px-3 py-1.5 rounded-xl text-[10px] font-bold text-slate-300 uppercase tracking-widest whitespace-nowrap shadow-xl bg-slate-800 border border-slate-700 pointer-events-none transition-all"
                          :class="tooltipClass">Akun</span>
                </div>
            </div>

            {{-- RINGKASAN (Index 1) --}}
            <div class="absolute inset-0 flex items-center justify-center transition-all duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
                 :style="getItemStyle(1)">
                <div class="relative group flex items-center justify-center">
                    <a href="{{ route('mahasiswa.ringkasan') }}" class="w-12 h-12 flex items-center justify-center rounded-full shadow-lg border pointer-events-auto active:scale-95 transition-transform
                        {{ request()->routeIs('mahasiswa.ringkasan') ? 'bg-cyan-500 border-cyan-400 text-white' : 'bg-slate-800 border-slate-600 text-slate-300 hover:bg-slate-700' }}">
                        <i class="fas fa-file-contract text-sm"></i>
                    </a>
                    <span class="absolute px-3 py-1.5 rounded-xl text-[10px] font-bold text-slate-300 uppercase tracking-widest whitespace-nowrap shadow-xl bg-slate-800 border border-slate-700 pointer-events-none transition-all"
                          :class="tooltipClass">Ringkasan</span>
                </div>
            </div>

            {{-- BIODATA (Index 2) --}}
            <div class="absolute inset-0 flex items-center justify-center transition-all duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
                 :style="getItemStyle(2)">
                <div class="relative group flex items-center justify-center">
                    <a href="{{ route('mahasiswa.biodata.create') }}" class="w-12 h-12 flex items-center justify-center rounded-full shadow-lg border pointer-events-auto active:scale-95 transition-transform
                        {{ request()->routeIs('mahasiswa.biodata.*') ? 'bg-cyan-500 border-cyan-400 text-white' : 'bg-slate-800 border-slate-600 text-slate-300 hover:bg-slate-700' }}">
                        <i class="fas fa-user-edit text-sm"></i>
                    </a>
                    <span class="absolute px-3 py-1.5 rounded-xl text-[10px] font-bold text-slate-300 uppercase tracking-widest whitespace-nowrap shadow-xl bg-slate-800 border border-slate-700 pointer-events-none transition-all"
                          :class="tooltipClass">Biodata</span>
                </div>
            </div>

            {{-- HOME (Index 3) --}}
            <div class="absolute inset-0 flex items-center justify-center transition-all duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
                 :style="getItemStyle(3)">
                <div class="relative group flex items-center justify-center">
                    <a href="{{ route('mahasiswa.dashboard') }}" class="w-12 h-12 flex items-center justify-center rounded-full shadow-lg border pointer-events-auto active:scale-95 transition-transform
                        {{ request()->routeIs('mahasiswa.dashboard') ? 'bg-cyan-500 border-cyan-400 text-white' : 'bg-slate-800 border-slate-600 text-slate-300 hover:bg-slate-700' }}">
                        <i class="fas fa-home text-sm"></i>
                    </a>
                    <span class="absolute px-3 py-1.5 rounded-xl text-[10px] font-bold text-slate-300 uppercase tracking-widest whitespace-nowrap shadow-xl bg-slate-800 border border-slate-700 pointer-events-none transition-all"
                          :class="tooltipClass">Home</span>
                </div>
            </div>
            
        </div>

        {{-- Tombol Utama (Toggle) --}}
        <button x-on:mousedown="dragStart($event)"
                x-on:touchstart.passive="dragStart($event)"
                x-on:click="toggle()" 
                class="absolute inset-0 w-16 h-16 rounded-full flex items-center justify-center shadow-[0_10px_30px_rgba(6,182,212,0.4)] transition-all duration-300 active:scale-90 cursor-move pointer-events-auto"
                :class="navOpen ? 'bg-red-500 rotate-[135deg] shadow-[0_10px_30px_rgba(239,68,68,0.5)]' : 'bg-cyan-500 hover:bg-cyan-400'">
            <i class="fas fa-plus text-2xl text-white pointer-events-none"></i>
        </button>

    </div>
</div>

{{-- Mobile Payment Alert Modal --}}
<template x-if="showPaymentAlert">
    <div class="mobile-modal-overlay" x-show="showPaymentAlert" x-cloak @click.away="showPaymentAlert = false">
        <div class="mobile-modal-container" x-show="showPaymentAlert"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="mobile-modal-header">
                <div class="mx-auto w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mb-2">
                    <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                </div>
                <h3 class="text-lg font-bold">Akses Dikunci</h3>
            </div>
            <div class="mobile-modal-body">
                <p class="text-gray-600 text-sm">Anda harus menyelesaikan ujian terlebih dahulu.</p>
            </div>
            <div class="mobile-modal-footer">
                <button @click="showPaymentAlert = false" class="mobile-modal-btn mobile-modal-btn-primary">Tutup</button>
                <a href="https://elearning2.ubpkarawang.ac.id/login/index.php" class="mobile-modal-btn mobile-modal-btn-secondary text-center">Ke Halaman Ujian</a>
            </div>
        </div>
    </div>
</template>

@stack('scripts')
@stack('opening_screen')
@include('mahasiswa.dashboard.partials.tour-script')