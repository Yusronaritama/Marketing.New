<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Pendaftaran Mahasiswa Baru') - UBP Karawang</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @if (app()->environment('local', 'development'))
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        },
                        colors: {
                            'ice': '#f1f5f9', 
                        },
                        boxShadow: {
                            'glass': '0 8px 32px 0 rgba(31, 38, 135, 0.37)',
                        }
                    }
                }
            }
        </script>
    @endif
    
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }

        /* 1. GLASS PANEL UTAMA (Form Container) - LEBIH TRANSPARAN */
        .glass-card {
            background: rgba(248, 250, 252, 0.70); 
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.25);
        }

        /* 2. GLASS NAVBAR */
        .glass-nav {
            background: rgba(255, 255, 255, 0.05); /* Sangat transparan */
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }

        /* 3. INPUT STYLES (Solid White on Glass) */
        .form-input {
            background-color: rgba(255, 255, 255, 0.8); 
            border: 1px solid rgba(203, 213, 225, 0.6);
            transition: all 0.3s;
        }
        .form-input:focus {
            background-color: #ffffff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.25);
            outline: none;
        }

        /* 4. RADIO CARDS */
        .radio-card-input { display: none; }
        .radio-card-label {
            position: relative;
            cursor: pointer;
            border: 1px solid rgba(255,255,255,0.4);
            background: rgba(255,255,255,0.6); /* Lebih transparan */
            transition: all 0.2s;
        }
        .radio-card-label:hover {
            background: rgba(255,255,255,0.9);
            border-color: #3b82f6;
        }
        .radio-card-input:checked + .radio-card-label {
            background: #ffffff;
            border-color: #3b82f6;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
        }
        /* Checkmark */
        .radio-card-input:checked + .radio-card-label::after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 0; right: 0;
            background: #3b82f6; color: white;
            padding: 4px 8px; font-size: 10px;
            border-bottom-left-radius: 8px;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.4); }

        /* Animation */
        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-25px) scale(1.05); }
        }
        .blob { animation: float 10s infinite ease-in-out; }
    </style>
    @yield('styles')
</head>
<body class="antialiased text-slate-800 bg-[#0A1930] min-h-screen relative overflow-x-hidden selection:bg-blue-400 selection:text-white">

    <!-- UBP Blue Glassmorphism Background (dari app.blade.php) -->
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

    {{-- 2. NAVBAR (Styled like app.blade.php tapi tanpa logic) --}}
    <div class="sticky top-4 z-50 w-full px-4 md:px-6 lg:px-8 max-w-6xl mx-auto transition-all duration-300"
         x-data="{ scrolled: false }" @scroll.window="scrolled = (window.pageYOffset > 20)">
        
        <nav class="w-full bg-[#0A1930]/70 backdrop-blur-2xl border border-white/10 rounded-[2rem] shadow-[0_10px_40px_rgba(0,0,0,0.3)] transition-all duration-500 relative"
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
                    {{-- Brand --}}
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center border border-white/20 shadow-lg group hover:bg-white/20 transition-all">
                            <i class="fas fa-graduation-cap text-white text-lg group-hover:scale-110 transition-transform"></i>
                        </div>
                        <div class="flex flex-col text-white">
                            <span class="font-bold text-lg leading-tight tracking-tight">PMB Online</span>
                            <span class="text-[10px] uppercase tracking-wider opacity-70">Univ. Buana Perjuangan</span>
                        </div>
                    </div>

                    {{-- Menu --}}
                    <a href="{{ route('mahasiswa.dashboard') }}" 
                       class="group flex items-center gap-2 rounded-full bg-white/10 px-5 py-2.5 text-sm font-semibold text-white transition-all hover:bg-white hover:text-blue-700 hover:shadow-[0_0_20px_rgba(255,255,255,0.3)] border border-white/20">
                        <i class="fas fa-columns transition-transform group-hover:rotate-180"></i>
                        <span class="hidden sm:inline">Dashboard</span>
                    </a>
                </div>
            </div>
        </nav>
    </div>

    {{-- 3. MAIN CONTENT --}}
    <main class="relative z-10 flex min-h-screen flex-col items-center justify-center pt-12 pb-12 px-4">
        
        <div class="w-full max-w-5xl">
            
            {{-- Header Text --}}
            <div class="text-center mb-10">
                <span class="inline-block py-1 px-4 rounded-full bg-white/10 border border-white/20 text-blue-100 text-[11px] font-bold uppercase tracking-widest backdrop-blur-sm mb-4 shadow-sm">
                    Tahun Akademik {{ date('Y') }}/{{ date('Y')+1 }}
                </span>
                <h1 class="text-3xl md:text-5xl font-extrabold text-white tracking-tight drop-shadow-xl mb-4">
                    Formulir Pendaftaran
                </h1>
                <p class="text-blue-100/90 text-sm md:text-base max-w-2xl mx-auto font-light leading-relaxed drop-shadow-sm">
                    Mohon isi data diri Anda dengan teliti. Data yang Anda masukkan akan diverifikasi oleh sistem kami.
                </p>
            </div>

            {{-- GLASS CARD CONTAINER (TRANSPARAN) --}}
            <div class="glass-card rounded-[2.5rem] p-6 md:p-10 lg:p-12 relative overflow-hidden text-slate-800">
                
                {{-- Kilauan --}}
                <div class="absolute -top-32 -left-32 h-64 w-64 bg-white opacity-10 blur-[60px] rounded-full pointer-events-none"></div>
                <div class="absolute top-0 right-0 h-full w-full bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>

                {{-- CONTENT FORM --}}
                <div class="relative z-10">
                    @yield('content')
                </div>

            </div>

            {{-- Footer --}}
            <footer class="mt-10 text-center">
                <p class="text-xs text-blue-200/60 font-medium">
                    &copy; {{ date('Y') }} UBP Karawang. All rights reserved.
                </p>
                <div class="mt-3 flex justify-center gap-4 text-sm">
                    <a href="mailto:pmb@ubpkarawang.ac.id" class="flex items-center gap-2 text-blue-200 hover:text-white transition-colors hover:scale-105 transform">
                        <i class="far fa-envelope"></i> Bantuan Teknis
                    </a>
                </div>
            </footer>

        </div>
    </main>

    @yield('modals')
    @yield('scripts')
</body>
</html>