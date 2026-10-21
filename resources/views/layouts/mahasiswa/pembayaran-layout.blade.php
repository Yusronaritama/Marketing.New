<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Pembayaran UKT') - UBP Karawang</title>

    {{-- 1. FONTS & ICONS --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- 2. TAILWIND & CONFIG --}}
    @if (app()->environment('local', 'development'))
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                            mono: ['"Inter"', 'sans-serif'],
                        },
                        colors: { 'ice': '#f1f5f9' },
                        boxShadow: { 'glass': '0 8px 32px 0 rgba(31, 38, 135, 0.37)' },
                        animation: { 'fade-down': 'fadeDown 0.5s ease-out' },
                        keyframes: {
                            fadeDown: {
                                '0%': { opacity: '0', transform: 'translateY(-20px)' },
                                '100%': { opacity: '1', transform: 'translateY(0)' },
                            }
                        }
                    }
                }
            }
        </script>
    @endif

    {{-- 3. LIBRARIES --}}
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- 4. CUSTOM STYLES --}}
    <style>
        [x-cloak] { display: none !important; }

        body {
            background: radial-gradient(circle at top left, #1e40af, #1e3a8a, #0f172a);
            background-attachment: fixed;
            min-height: 100vh;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        .glass-nav {
            background: rgba(15, 23, 42, 0.3);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-25px) scale(1.05); }
        }
        .blob { animation: float 10s infinite ease-in-out; }

        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.3); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.5); }
    </style>

    {{-- 5. FUNCTION COPY --}}
    <script>
        window.globalCopy = function(text) {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(() => { showSuccessToast(text); })
                .catch(err => { fallbackCopy(text); });
            } else { fallbackCopy(text); }
        };
        function fallbackCopy(text) {
            let textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed"; textArea.style.left = "-9999px";
            document.body.appendChild(textArea);
            textArea.focus(); textArea.select();
            try { document.execCommand('copy'); showSuccessToast(text); } catch (err) { alert("Gagal menyalin. Copy manual."); }
            document.body.removeChild(textArea);
        }
        function showSuccessToast(text) {
            Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000, timerProgressBar: true, icon: 'success', title: 'Berhasil disalin!', text: text });
        }
    </script>

    @stack('scripts')
</head>
<body class="antialiased selection:bg-blue-500 selection:text-white overflow-x-hidden text-slate-800 font-sans">

    {{-- 1. BACKGROUND BLOBS --}}
    <div class="fixed inset-0 z-[-1] overflow-hidden pointer-events-none">
        <div class="blob absolute -top-20 -left-20 w-[40rem] h-[40rem] bg-blue-600 rounded-full mix-blend-screen filter blur-[100px] opacity-30"></div>
        <div class="blob absolute top-1/3 -right-32 w-[35rem] h-[35rem] bg-indigo-600 rounded-full mix-blend-screen filter blur-[100px] opacity-25" style="animation-delay: 3s"></div>
        <div class="blob absolute -bottom-40 left-20 w-[30rem] h-[30rem] bg-cyan-600 rounded-full mix-blend-screen filter blur-[100px] opacity-20" style="animation-delay: 6s"></div>
    </div>

    {{-- 2. NAVBAR --}}
    <nav class="fixed top-0 w-full z-50 glass-nav transition-all duration-300">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex justify-between items-center h-16 sm:h-20">
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="h-9 w-9 sm:h-10 sm:w-10 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center border border-white/20 shadow-lg group hover:bg-white/20 transition-all">
                        <i class="fas fa-wallet text-white text-base sm:text-lg group-hover:scale-110 transition-transform"></i>
                    </div>
                    <div class="flex flex-col text-white">
                        <span class="font-bold text-base sm:text-lg leading-tight tracking-tight">Payment Portal</span>
                        <span class="text-[9px] sm:text-[10px] uppercase tracking-wider opacity-80 font-medium">Univ. Buana Perjuangan</span>
                    </div>
                </div>
                <a href="{{ route('mahasiswa.dashboard') }}"
                   class="group flex items-center gap-2 rounded-full bg-white/10 px-4 sm:px-6 py-2 sm:py-2.5 text-xs sm:text-sm font-bold text-white transition-all hover:bg-white hover:text-blue-800 hover:shadow-lg hover:shadow-blue-500/20 border border-white/20">
                    <i class="fas fa-columns transition-transform group-hover:rotate-180"></i>
                    <span class="hidden sm:inline">Dashboard</span>
                </a>
            </div>
        </div>
    </nav>

    {{-- 3. MAIN CONTENT --}}
    {{-- PERBAIKAN: justify-center → justify-start agar header tidak terdorong ke bawah di mobile --}}
    <main class="relative z-10 flex min-h-screen flex-col items-center justify-start pt-24 sm:pt-32 pb-16 px-4">

        <div class="w-full max-w-lg sm:max-w-5xl">

            {{-- Header: Logo & Judul --}}
            <div class="text-center mb-8 sm:mb-12">

                {{-- Logo --}}
                <div class="mb-6 sm:mb-10 flex justify-center animate-fade-down">
                    <div class="bg-white/95 backdrop-blur-md px-6 sm:px-10 py-3 sm:py-5 rounded-[1.5rem] sm:rounded-[2rem] shadow-2xl border border-white/60 inline-flex items-center gap-5 sm:gap-8 md:gap-10 transform hover:scale-105 transition-all duration-300">
                        <img src="{{ asset('images/logo_ubp.png') }}"
                             alt="UBP Karawang"
                             class="h-12 sm:h-20 md:h-24 w-auto object-contain drop-shadow-sm">
                        <div class="h-10 sm:h-16 w-[1.5px] bg-slate-300/50 rounded-full"></div>
                        <img src="{{ asset('images/mandiri.png') }}"
                             alt="Bank Mandiri"
                             class="h-9 sm:h-14 md:h-16 w-auto object-contain drop-shadow-sm">
                    </div>
                </div>

                <span class="inline-block py-1 sm:py-1.5 px-4 sm:px-5 rounded-full bg-blue-800/30 border border-blue-400/30 text-blue-100 text-[10px] sm:text-[11px] font-extrabold uppercase tracking-[0.2em] backdrop-blur-sm mb-4 sm:mb-5 shadow-sm">
                    Official Payment Channel
                </span>
                <h1 class="text-3xl sm:text-4xl md:text-6xl font-extrabold text-white tracking-tight drop-shadow-2xl mb-4 sm:mb-6 leading-tight">
                    Pembayaran UKT
                </h1>
                <p class="text-blue-100/90 text-sm sm:text-base md:text-lg max-w-2xl mx-auto font-medium leading-relaxed drop-shadow-md">
                    Selesaikan kewajiban administrasi Anda melalui kanal resmi <b>Bank Mandiri</b> atau jaringan ATM Bersama/Prima menggunakan nomor Virtual Account.
                </p>
            </div>

            {{-- GLASS CARD --}}
            <div class="glass-card rounded-[2rem] sm:rounded-[3rem] p-6 sm:p-8 md:p-12 relative overflow-hidden">
                <div class="absolute -top-40 -left-40 h-80 w-80 bg-white opacity-20 blur-[80px] rounded-full pointer-events-none select-none"></div>
                <div class="absolute top-0 right-0 h-full w-full bg-gradient-to-b from-white/10 to-transparent pointer-events-none select-none"></div>
                <div class="relative z-10">
                    @yield('content')
                </div>
            </div>

            {{-- Footer --}}
            <footer class="mt-8 sm:mt-12 text-center">
                <p class="text-xs sm:text-sm text-blue-200/70 font-semibold">
                    &copy; {{ date('Y') }} Universitas Buana Perjuangan Karawang.
                </p>
                <p class="text-xs text-blue-300/50 mt-1">Sistem Pembayaran Terintegrasi.</p>
            </footer>

        </div>
    </main>

    @yield('modals')
</body>
</html>