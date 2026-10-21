<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PMB Online')</title>

    {{-- Tailwind CSS --}}
    @if (app()->environment('local', 'development'))
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- FontAwesome untuk Icon Bottom Nav --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- AlpineJS --}}
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    {{-- Additional Styles --}}
    <style>
        body { font-family: 'Inter', sans-serif; }
        .body-gradient { background-color: #ffffff; background-image: none; }
        
        /* Animasi Transisi Halaman */
        .fade-out { animation: fadeOut 0.3s ease-out forwards; }
        @keyframes fadeOut { from { opacity: 1; } to { opacity: 0; } }
        
        .animate-float { animation: float 3s ease-in-out infinite; }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-4px); }
        }
    </style>
    @stack('styles')
</head>

{{-- Bungkus body dengan AlpineJS x-data untuk kontrol menu --}}
<body class="body-gradient pb-[env(safe-area-inset-bottom)] bg-gray-50" x-data="{ mobileMenuOpen: false }">
    
    @php
        $phpActive = false; 
        $phpSchedule = null; 
        $phpIsAdmin = false;
        
        // 1. Cek Data User & Role Admin DULU
        $user = Auth::user();
        if ($user) {
            // User 215 dimasukkan sebagai Admin di sini
            $phpIsAdmin = ($user->role === 'admin' || $user->is_admin == 1 || $user->id == 215 );
            
            $userName = $user->name;
            $userEmail = $user->email;
            $userAvatar = $user->profile_photo_path ?? null;
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
            // Bypass Maintenance & Gelombang Tutup
            $phpActive = false; 
            $phpGelombangClosed = false; 
        }
    @endphp
    @include('mahasiswa.profesi.components.opening')
    @include('mahasiswa.profesi.components.maintenance')
    @include('mahasiswa.profesi.components.overlay-WA')
    <!--@include('mahasiswa.profesi.components.modal-WA')-->
    
    {{-- Tambahan padding bottom (pb-20) di mobile agar konten tidak tertutup Bottom Nav --}}
    <div class="min-h-screen flex flex-col relative pb-20 md:pb-0">
        
        {{-- ==========================================
             1. FIXED BACKGROUND (Gambar Diam Full Screen)
             ========================================== --}}
        {{-- Karena pakai 'fixed', dia akan nempel terus di layar walau di-scroll --}}
        <div class="fixed inset-0 z-0">
            <img src="{{ asset('images/Rektorat.jpg') }}" alt="Background Kampus" class="w-full h-full object-cover">
            {{-- Overlay Gradasi Midnight biar elegan --}}
            <div class="absolute inset-0 bg-gradient-to-b from-slate-900/80 via-blue-950/80 to-slate-950/95 backdrop-blur-sm"></div>
        </div>

        {{-- ==========================================
             TOP NAVIGATION (Header - Midnight Glass Edition)
             ========================================== --}}
           <header class="bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 backdrop-blur-lg shadow-[0_4px_30px_rgba(0,0,0,0.3)] sticky top-0 z-40 border-b border-white/5">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex items-center justify-between h-16 relative"> {{-- Tambah relative di sini --}}
                            
                    {{-- 1. Logo Kiri (Sendirian di kotaknya) --}}
                    <div class="flex items-center">
                        <a href="/" class="group flex items-center gap-3 transition-transform duration-300 hover:scale-105">
                            {{-- Box Logo dengan Glassmorphism Halus --}}
                            <div class="bg-white/5 p-1.5 rounded-xl backdrop-blur-md border border-white/10 shadow-inner group-hover:bg-white/10 transition-colors duration-300">
                                <img class="h-7 w-auto drop-shadow-lg" src="{{ asset('images/logo_ubp.png') }}" alt="Logo UBP" onerror="this.onerror=null; this.src='https://ubpkarawang.ac.id/wp-content/uploads/2021/08/Logo-UBP-Karawang.png';">
                            </div>
                            {{-- Judul Khusus Mobile --}}
                            <span class="md:hidden font-extrabold text-gray-100 tracking-wide leading-tight drop-shadow-md">
                                PMB<br><span class="text-[10px] text-blue-400 font-medium tracking-wider uppercase">Profesi</span>
                            </span>
                        </a>
                    </div>
                
                    {{-- 2. Menu Navigasi Desktop (Dipisah & Dikunci Tepat di Tengah) --}}
                    <nav class="hidden md:flex absolute left-1/2 transform -translate-x-1/2 items-center justify-center p-1.5 bg-black/30 backdrop-blur-xl rounded-full border border-white/10 shadow-[0_8px_32px_rgba(0,0,0,0.3)] overflow-visible z-50 transition-all hover:bg-black/40 hover:border-white/20 hover:shadow-[0_8px_40px_rgba(37,99,235,0.2)]">
                        
                {{-- Tombol Dashboard --}}
                <div class="relative flex items-center">
                    <a href="{{ route('profesi.dashboard') }}" 
                       class="group flex items-center justify-center gap-2 px-4 py-2 rounded-full transition-all duration-300 relative z-10
                              @if(request()->routeIs('profesi.dashboard')) 
                                  bg-white/10 text-white shadow-inner
                              @else 
                                  text-gray-400 hover:text-gray-100 hover:bg-white/5
                              @endif">
                        <i class="fas fa-home text-[15px] transition-transform duration-300 @if(request()->routeIs('profesi.dashboard')) text-blue-400 scale-110 drop-shadow-[0_0_8px_rgba(96,165,250,0.5)] @else group-hover:scale-110 group-hover:text-gray-200 @endif"></i>
                        
                        {{-- Teks ini selalu muncul kalau aktif, atau muncul saat dihover kalau tidak aktif --}}
                        <span class="text-xs font-bold tracking-wide transition-all duration-300 overflow-hidden 
                                     @if(request()->routeIs('profesi.dashboard')) max-w-[100px] opacity-100 @else max-w-0 opacity-0 group-hover:max-w-[100px] group-hover:opacity-100 group-hover:ml-1 @endif">
                            Beranda
                        </span>
                    </a>
                    
                    {{-- Indikator Titik Aktif (Glowing Dot) --}}
                    @if(request()->routeIs('profesi.dashboard'))
                        <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 w-1.5 h-1.5 bg-blue-500 rounded-full shadow-[0_0_10px_rgba(59,130,246,1)]"></div>
                    @endif
                </div>
            
                {{-- Pemisah Vertikal --}}
                <div class="w-px h-5 bg-white/10 mx-1"></div>

                {{-- Tombol Resume Data --}}
                <div class="relative flex items-center">
                    <a href="{{ route('profesi.resume') }}" 
                       class="group flex items-center justify-center gap-2 px-4 py-2 rounded-full transition-all duration-300 relative z-10
                              @if(request()->routeIs('profesi.resume')) 
                                  bg-white/10 text-white shadow-inner
                              @else 
                                  text-gray-400 hover:text-gray-100 hover:bg-white/5
                              @endif">
                        <i class="fas fa-file-invoice text-[15px] transition-transform duration-300 @if(request()->routeIs('profesi.resume')) text-blue-400 scale-110 drop-shadow-[0_0_8px_rgba(96,165,250,0.5)] @else group-hover:scale-110 group-hover:text-gray-200 @endif"></i>
                        
                        <span class="text-xs font-bold tracking-wide transition-all duration-300 overflow-hidden whitespace-nowrap
                                     @if(request()->routeIs('profesi.resume')) max-w-[100px] opacity-100 @else max-w-0 opacity-0 group-hover:max-w-[100px] group-hover:opacity-100 group-hover:ml-1 @endif">
                            Resume Data
                        </span>
                    </a>

                    {{-- Indikator Titik Aktif (Glowing Dot) --}}
                    @if(request()->routeIs('profesi.resume'))
                        <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 w-1.5 h-1.5 bg-blue-500 rounded-full shadow-[0_0_10px_rgba(59,130,246,1)]"></div>
                    @endif
                </div>
            
            </nav>
            
            
            {{-- Profil Kanan (Desktop) --}}
            <div x-data="{ openProfile: false }" @click.away="openProfile = false" class="relative hidden md:block">
                <button @click="openProfile = !openProfile" class="flex items-center space-x-3 text-gray-200 font-medium focus:outline-none transition-all duration-300 hover:text-white group">
                    {{-- Icon User --}}
                    <div class="w-9 h-9 rounded-full bg-white/5 backdrop-blur-md border border-white/10 flex items-center justify-center group-hover:bg-white/10 group-hover:border-white/20 transition-all shadow-inner">
                        <i class="fas fa-user text-sm text-blue-300 group-hover:text-blue-200"></i>
                    </div>
                    <span class="tracking-wide text-sm">{{ Auth::user()->name ?? 'Mahasiswa' }}</span>
                    <i class="fas fa-chevron-down text-xs text-gray-500 transition-transform duration-300" :class="{'rotate-180 text-blue-400': openProfile}"></i>
                </button>
                
                {{-- Dropdown Profil Desktop --}}
                <div x-show="openProfile" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 translate-y-2"
                     class="absolute right-0 mt-3 w-56 bg-slate-900 rounded-2xl shadow-[0_10px_40px_rgba(0,0,0,0.5)] border border-white/10 py-2 z-50 overflow-hidden" 
                     style="display: none;">
                    
                    <div class="px-4 py-3 border-b border-white/5 mb-1">
                        <p class="text-xs text-gray-400">Login sebagai</p>
                        <p class="text-sm font-bold text-gray-100 truncate">{{ Auth::user()->email ?? 'user@maba.com' }}</p>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-red-400 hover:bg-red-500/10 hover:text-red-300 font-medium flex items-center gap-3 transition-colors">
                            <i class="fas fa-sign-out-alt"></i> Keluar
                        </button>
                    </form>
                </div>
            </div>

            {{-- Info User Simple (Mobile) --}}
            <div class="md:hidden flex items-center gap-3">
                <div class="text-right">
                    <p class="text-xs font-bold text-gray-100 tracking-wide">{{ Str::words(Auth::user()->name ?? 'User', 2, '') }}</p>
                    <p class="text-[9px] text-blue-400 font-medium uppercase tracking-widest">Maba Profesi</p>
                </div>
                <div class="w-9 h-9 rounded-full bg-white/5 backdrop-blur-md border border-white/10 flex items-center justify-center shadow-inner">
                    <i class="fas fa-user text-sm text-blue-300"></i>
                </div>
            </div>

        </div>
    </div>
</header>

        {{-- ==========================================
             MAIN CONTENT
             ========================================== --}}
        {{-- Bungkus @yield('content') pakai 'relative z-10' biar konten melayang di atas gambar --}}
        <main class="relative z-10 w-full">
        	@component('layouts.mahasiswa.components.page-transition') 
            @yield('content')
            @endcomponent
        </main>
    </div>

    {{-- ==========================================
         BOTTOM NAVIGATION (Khusus Mobile)
         Muncul menempel di bawah layar HP
         ========================================== --}}
    <nav class="md:hidden fixed bottom-0 w-full bg-gradient-to-r from-slate-900/95 via-blue-950/95 to-slate-900/95 backdrop-blur-lg border-t border-white/5 z-50 pb-[env(safe-area-inset-bottom)] shadow-[0_-10px_40px_rgba(0,0,0,0.5)]">
        <div class="flex justify-around items-center h-16 px-2">
            
            {{-- Tombol Dashboard --}}
            <a href="{{ route('profesi.dashboard') }}" 
               class="flex flex-col items-center justify-center w-full h-full space-y-1 group transition-colors duration-300 
                      @if(request()->routeIs('profesi.dashboard')) text-blue-400 @else text-gray-500 hover:text-gray-300 @endif">
                
                {{-- Efek Glowing tipis pas aktif, loncat dikit pas dihover (kalau belum aktif) --}}
                <i class="fas fa-home text-xl transition-transform duration-300 
                          @if(request()->routeIs('profesi.dashboard')) scale-110 drop-shadow-[0_0_10px_rgba(96,165,250,0.6)] @else group-hover:-translate-y-1 @endif"></i>
                <span class="text-[10px] font-bold tracking-wide">Beranda</span>
            </a>
            <a href="{{ route('profesi.resume') }}" 
               class="flex flex-col items-center justify-center w-full h-full space-y-1 group transition-colors duration-300 
                      @if(request()->routeIs('profesi.resume')) text-blue-400 @else text-gray-500 hover:text-gray-300 @endif">
                
                <i class="fas fa-file-invoice text-xl transition-transform duration-300 
                          @if(request()->routeIs('profesi.resume')) scale-110 drop-shadow-[0_0_10px_rgba(96,165,250,0.6)] @else group-hover:-translate-y-1 @endif"></i>
                <span class="text-[10px] font-bold tracking-wide">Resume</span>
            </a>

            {{-- Tombol Menu Lainnya (Buka Offcanvas) --}}
            <button @click="mobileMenuOpen = true" 
                    class="flex flex-col items-center justify-center w-full h-full space-y-1 text-gray-500 hover:text-gray-300 group transition-colors duration-300">
                <i class="fas fa-bars text-xl transition-transform duration-300 group-hover:-translate-y-1"></i>
                <span class="text-[10px] font-bold tracking-wide">Menu</span>
            </button>
            
        </div>
    </nav>

    {{-- ==========================================
         OFFCANVAS MENU MOBILE (Muncul dari bawah)
         ========================================== --}}
    <div x-show="mobileMenuOpen" class="md:hidden relative z-[60]" aria-labelledby="slide-over-title" role="dialog" aria-modal="true" style="display: none;">
        {{-- Background Gelap --}}
        <div x-show="mobileMenuOpen" x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="mobileMenuOpen = false"></div>

        <div class="fixed inset-0 overflow-hidden">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 bottom-0 flex max-w-full items-end">
                    
                    {{-- Panel Menu --}}
                    <div x-show="mobileMenuOpen" 
				         @click.away="mobileMenuOpen = false"
				         class="fixed inset-x-4 bottom-20 z-40 bg-white rounded-2xl shadow-[0_0_30px_rgba(0,0,0,0.15)] border border-gray-100 p-4 pb-5"
				         x-transition:enter="transition ease-out duration-300"
				         x-transition:enter-start="opacity-0 translate-y-10 scale-95"
				         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
				         x-transition:leave="transition ease-in duration-200"
				         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
				         x-transition:leave-end="opacity-0 translate-y-10 scale-95"
				         style="display: none;">
                        
                        <div class="flex h-full flex-col overflow-y-scroll bg-white shadow-xl rounded-t-3xl pb-6">
                            <div class="px-4 py-4 flex items-center justify-between border-b border-gray-100">
                                <h2 class="text-lg font-bold text-gray-900" id="slide-over-title">Menu Utama</h2>
                                <button type="button" @click="mobileMenuOpen = false" class="rounded-full bg-gray-100 p-2 text-gray-500 hover:text-gray-900">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            
                            <div class="relative mt-2 flex-1 px-4 sm:px-6 space-y-2">
                                
                                
                                <form method="POST" action="{{ route('logout') }}" class="mt-4">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-4 bg-red-50 text-red-600 rounded-xl font-bold hover:bg-red-100 transition">
                                        <i class="fas fa-sign-out-alt"></i> Keluar (Logout)
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script>
        // Page transition animation (Pertahankan kodingan lamamu)
        document.addEventListener('DOMContentLoaded', function () {
            const pageLinks = document.querySelectorAll('.page-link');
            pageLinks.forEach(link => {
                link.addEventListener('click', function (e) {
                    const href = this.getAttribute('href');
                    if (href && href !== window.location.href && !href.startsWith('#')) {
                        e.preventDefault();
                        document.body.classList.add('fade-out');
                        setTimeout(() => { window.location.href = href; }, 300);
                    }
                });
            });
        });
        
        document.addEventListener('alpine:init', () => {
        
        // ========================================================
        // 1. SYSTEM GUARD (MAINTENANCE POLLING)
        // ========================================================
        Alpine.data('systemGuard', () => ({
            // Ambil data awal dari PHP saat pertama kali load
            isAdmin: @json($phpIsAdmin ?? false),
            isActive: @json($phpActive ?? false),
            scheduledAt: @json($phpSchedule ?? null),
            estimatedEnd: @json($d['estimated_end'] ?? null), 
            
            dismissed: false, 
            showPaymentAlert: false,

            // --- LOGIKA MODE TAMPILAN ---
            get overlayMode() {
                if (this.isAdmin) return false; // Admin kebal gembok
                if (this.isActive) return true; // Tombol manual nyala
                if (this.scheduledAt) return new Date() >= new Date(this.scheduledAt); // Waktu maintenance tiba
                return false;
            },
            get warningMode() {
                if (this.isAdmin) return false;
                if (this.overlayMode) return false;
                if (this.scheduledAt) return new Date() < new Date(this.scheduledAt); // Waktu belum tiba (Warning)
                return false;
            },
            get showFullModal() { return this.warningMode && !this.dismissed; },
            get showTopBanner() { return this.warningMode && this.dismissed; },
            
            // --- FORMAT WAKTU UNTUK UI ---
            get formattedSchedule() {
                if (!this.scheduledAt) return '';
                let d = new Date(this.scheduledAt);
                return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) + 
                       ' - ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB';
            },
            get formattedEstimatedEnd() {
                if (!this.estimatedEnd) return 'Tidak Ditentukan';
                let d = new Date(this.estimatedEnd);
                return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) + 
                       ' - ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB';
            },

            closeModal() { 
                this.dismissed = true; 
            },
            
            // --- FUNGSI POLLING & FETCH ---
            startWatch() {
                this.fetchStatus(); // Tarik data saat pertama kali buka halaman
                setInterval(() => { this.fetchStatus(); }, 5000); // Polling tiap 5 detik
            },

            async fetchStatus() {
                try {
                    // Tambahan ?_=... adalah CACHE BUSTER agar browser selalu minta data baru ke server
                    let url = '/admin/perbaikan/status?_=' + new Date().getTime();
                    let res = await fetch(url, { 
                        headers: { 
                            'Accept': 'application/json',
                            'Cache-Control': 'no-cache'
                        } 
                    });
                    
                    if(res.ok) {
                        let data = await res.json();
                        
                        // Cek jika jadwal berubah, tampilkan lagi modal warning-nya
                        if (this.scheduledAt !== data.scheduled_at && data.scheduled_at) {
                            this.dismissed = false;
                            sessionStorage.removeItem('maintenance_ack');
                        }

                        // Update variabel Alpine dengan data segar dari server
                        this.isActive = data.is_maintenance;
                        this.scheduledAt = data.scheduled_at;
                        this.estimatedEnd = data.estimated_end; 
                    }
                } catch (e) {
                    console.warn("Gagal fetch status maintenance.");
                }
            },

            init() { 
                console.log('✅ System Guard Loaded'); 
                this.startWatch(); 
            }
        }));
    });
    </script>
    @stack('scripts')
</body>
</html>