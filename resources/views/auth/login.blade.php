{{-- resources/views/auth/login.blade.php --}}
<style>
    /* === 1. ANIMASI SHAKE ERROR (Bawaan Lama) === */
    @keyframes shake {
        10%, 90% { transform: translate3d(-1px, 0, 0); }
        20%, 80% { transform: translate3d(2px, 0, 0); }
        30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
        40%, 60% { transform: translate3d(4px, 0, 0); }
    }
    .shake-error { animation: shake 0.82s cubic-bezier(.36, .07, .19, .97) both; }

    /* === 2. STYLE BOT BARU (PENGGANTI MONYET) === */
    .bot-wrapper {
        perspective: 1000px;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 160px; /* Tinggi disesuaikan agar mirip area monyet */
        margin-bottom: 1rem;
    }

    .glass-sphere {
    position: relative;
    width: 140px;
    height: 140px;
    
    /* GRADASI UBP (Biru Cyan Gelap ke Terang) */
    background: radial-gradient(circle at 35% 35%, 
        rgba(255, 255, 255, 0.2) 0%,    /* Highlight Putih */
        rgba(0, 191, 255, 0.5) 30%,     /* Biru Cyan Terang (Khas UBP) */
        rgba(0, 80, 150, 0.8) 60%,      /* Biru Medium */
        rgba(2, 28, 60, 0.95) 100%      /* Biru Gelap (Deep Depth) */
    );

    /* Efek Kaca Premium */
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-radius: 50%;
    
    /* Shadow & Glow */
    box-shadow: 
        0 30px 60px rgba(0, 0, 0, 0.5),              
        inset -10px -10px 40px rgba(0, 10, 40, 0.8), /* Bayangan dalam gelap */
        inset 10px 10px 40px rgba(255, 255, 255, 0.3), 
        0 0 30px rgba(0, 191, 255, 0.3);             /* Glow Luar Biru Cyan */

    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-top: 1px solid rgba(255, 255, 255, 0.5);
    border-left: 1px solid rgba(255, 255, 255, 0.5);
    overflow: hidden;
}

/* GANTI BAGIAN INI (Inner Glow dengan sentuhan Kuning UBP) */
.inner-glow {
    position: absolute;
    width: 100%;
    height: 100%;
    /* Sentuhan Kuning Emas (Lambang Bintang/Kemuliaan UBP) di dalam kaca */
    background: radial-gradient(circle at 50% 120%, 
        rgba(255, 215, 0, 0.25),  /* Kuning Emas Transparan */
        transparent 60%
    );
    pointer-events: none;
    mix-blend-mode: overlay; 
}

    .eyes-container {
        display: flex;
        gap: 24px;
        transition: transform 0.1s ease-out;
    }

    .eye {
    width: 12px;
    height: 28px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(255, 255, 255, 0.8);
    transition: all 0.1s ease; /* Dipercepat supaya kedipnya natural */
    position: relative;
    
    /* TAMBAHKAN INI UNTUK KEDIP */
    animation: blink 4s infinite ease-in-out;
    transform-origin: center;
}

/* LOGIKA KEDIP (Mata menutup sebentar setiap 4 detik) */
@keyframes blink {
    0%, 45%, 55%, 100% { 
        transform: scaleY(1); 
    }
    50% { 
        transform: scaleY(0.1); /* Menutup mata */
    }
}

    /* EKSPRESI > < (Saat ketik password) */
    .glass-sphere.clench .eye {
        height: 4px;
        background: transparent;
        box-shadow: none;
    }
    .glass-sphere.clench .eye::before, 
    .glass-sphere.clench .eye::after {
        content: '';
        position: absolute;
        width: 16px;
        height: 3px;
        background: white;
        top: 50%;
    }
    .glass-sphere.clench .eye.left::before { transform: rotate(45deg); left: -2px; }
    .glass-sphere.clench .eye.left::after { transform: rotate(-45deg); left: -2px; }
    .glass-sphere.clench .eye.right::before { transform: rotate(-45deg); right: -2px; }
    .glass-sphere.clench .eye.right::after { transform: rotate(45deg); right: -2px; }

    /* EKSPRESI - _ - (Saat Error) */
    .glass-sphere.invalid {
        background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.3), rgba(153, 27, 27, 0.9)); /* Merah */
    }
    .glass-sphere.invalid .eye {
        height: 4px;
        width: 20px;
        border-radius: 2px;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    .animate-float { animation: float 4s ease-in-out infinite; }
</style>

{{-- === Tambahkan AOS CSS === --}}
<link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet" />

<x-guest-layout>
    {{-- Status Autentikasi --}}
    <x-auth-session-status class="mb-4" :status="session('status')" />

    {{-- === AWAL BOT INTERAKTIF (PENGGANTI MONYET) === --}}
    <div class="bot-wrapper" 
         data-aos="zoom-in" 
         data-aos-duration="1000">
        {{-- Logika Error: Jika ada error, tambah class shake-error dan invalid --}}
        <div id="bot-container" class="glass-sphere animate-float @if($errors->any()) shake-error invalid @endif">
            <div class="inner-glow"></div>
            <div class="eyes-container" id="eyes">
                <div class="eye left"></div>
                <div class="eye right"></div>
            </div>
        </div>
    </div>
    {{-- === AKHIR BOT === --}}

    <h2 class="mb-3 text-3xl font-bold text-center"
        data-aos="fade-up" data-aos-delay="200">SELAMAT DATANG!</h2>
    <p class="mb-6 font-light text-gray-500 text-center"
        data-aos="fade-up" data-aos-delay="400">
        Masuk ke akun Anda untuk melanjutkan.
    </p>

    <form action="{{ route('login') }}" method="POST"
        data-aos="fade-up" data-aos-delay="600">
        @csrf

        @if ($errors->any())
            <div class="mb-4 p-3 rounded-lg border border-red-500/30 bg-red-500/10 text-red-600 text-sm flex items-center gap-2" 
                 data-aos="fade-down" data-aos-duration="500">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <div class="mb-4">
            <label for="username" class="block mb-2 text-sm font-medium text-gray-700">
                Username
            </label>
            <input type="text" id="username" name="username" required
                class="w-full px-4 py-2 border @error('username') border-red-500 focus:ring-red-500 @else border-gray-300 focus:ring-blue-500 @enderror rounded-lg focus:outline-none focus:ring-2 transition duration-100"
                placeholder="Masukkan Username Anda (contoh: PMB56052392)"
                value="{{ old('username') }}">
        </div>

        <div class="mb-4">
            <label for="password" class="block mb-2 text-sm font-medium text-gray-700">Kata Sandi</label>
            <div class="relative">
                <input type="password" id="password" name="password" required
                    class="w-full px-4 pr-10 py-2 border @error('username') border-red-500 focus:ring-red-500 @else border-gray-300 focus:ring-blue-500 @enderror rounded-lg focus:outline-none focus:ring-2 transition duration-200"
                    placeholder="Masukan Kata Sandi">

                <button type="button" id="togglePassword"
                    class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500">
                    <svg id="eye-open" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 
                              7.36 4.5 12 4.5c4.638 0 8.573 3.007 
                              9.963 7.178.07.207.07.432 0 .639C20.577 
                              16.49 16.64 19.5 12 19.5c-4.638 
                              0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <svg id="eye-closed" class="h-5 w-5 hidden" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.98 8.223A10.477 10.477 0 001.934 
                              12C3.226 16.338 7.244 19.5 12 19.5c.993 
                              0 1.953-.138 2.863-.395M6.228 
                              6.228A10.45 10.45 0 0112 4.5c4.756 
                              0 8.773 3.162 10.065 7.498a10.523 
                              10.523 0 01-4.293 5.774M6.228 
                              6.228L3 3m3.228 3.228l3.65 
                              3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 
                              0a3 3 0 10-4.243-4.243m4.243 
                              4.243l-4.243-4.243" />
                    </svg>
                </button>
            </div>
            
            {{-- NOTE UNTUK FORMAT USERNAME (TIDAK DIUBAH) --}}
            <p class="mt-1 text-xs text-gray-500">
                <strong>Note:</strong> password terdiri dari <strong>PMB + 8 digit terakhir nomor HP</strong>.<br>
                Contoh: 081234567890 → Password: <strong>PMB34567890</strong>
            </p>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-3" data-aos="fade-up" data-aos-delay="800">
            {{-- Ingat Saya --}}
            <div class="flex items-center">
                <input id="remember_me" name="remember" type="checkbox"
                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <label for="remember_me" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                    Ingat Saya
                </label>
            </div>

            {{-- Link Aksi --}}
            <div class="flex items-center space-x-4 text-sm">
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                       class="text-blue-600 hover:text-blue-700 font-medium transition-colors duration-200">
                        Password
                    </a>
                @endif

                <a href="{{ route('forgot.account') }}"
                   class="text-blue-600 hover:text-blue-700 font-medium transition-colors duration-200">
                    Lupa Akun?
                </a>
            </div>
        </div>

        <button type="submit"
            class="w-full bg-blue-600 text-white py-2 rounded-lg font-semibold hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 transition-all duration-300 ease-in-out"
            data-aos="zoom-in" data-aos-delay="1000">
            Login
        </button>

        <div class="mt-6 text-center" data-aos="fade-up" data-aos-delay="1200">
            Belum punya akun?
            <a href="{{ route('register-test') }}" class="text-sm text-gray-500 hover:text-blue-600 hover:underline">
                Daftar
            </a>
        </div>
    </form>
</x-guest-layout>

{{-- === Tambahkan AOS JS & LOGIKA ANIMASI BARU === --}}
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        AOS.init({
            once: true,
            offset: 50,
            duration: 800
        });

        // === LOGIKA ANIMASI BOT ===
        const botContainer = document.getElementById('bot-container');
        const eyesContainer = document.getElementById('eyes');
        const passwordInput = document.getElementById('password');

        // 1. MATA MENGIKUTI KURSOR
        document.addEventListener('mousemove', (e) => {
            // Cek jika elemen ada (untuk menghindari error)
            if (!botContainer || !eyesContainer) return;

            // Jangan gerak jika sedang mode password (tutup mata) atau error (invalid)
            if (botContainer.classList.contains('clench') || botContainer.classList.contains('invalid')) return;

            const rect = botContainer.getBoundingClientRect();
            const centerX = rect.left + rect.width / 2;
            const centerY = rect.top + rect.height / 2;
            
            const mouseX = e.clientX;
            const mouseY = e.clientY;

            // Hitung sudut pandang (pembagi 25 agar gerakannya halus)
            const angleX = (mouseX - centerX) / 25;
            const angleY = (mouseY - centerY) / 25;

            // Batasi gerakan bola mata (max 15px)
            const moveX = Math.max(Math.min(angleX, 15), -15);
            const moveY = Math.max(Math.min(angleY, 15), -15);

            eyesContainer.style.transform = `translate(${moveX}px, ${moveY}px)`;
        });

        // 2. EKSPRESI > < SAAT FOKUS PASSWORD
        if (passwordInput) {
            passwordInput.addEventListener('focus', () => {
                if (botContainer) {
                    botContainer.classList.add('clench');
                    eyesContainer.style.transform = `translate(0, 0)`; // Reset posisi mata ke tengah
                }
            });
            passwordInput.addEventListener('blur', () => {
                if (botContainer) botContainer.classList.remove('clench');
            });
        }

        // 3. AUTO RESET ERROR (Jika ada error login, kembali normal setelah 4 detik)
        if (botContainer && botContainer.classList.contains('invalid')) {
            setTimeout(() => {
                botContainer.classList.remove('invalid');
                botContainer.classList.remove('shake-error');
            }, 4000);
        }

        // 4. TOGGLE PASSWORD VIEW (Bawaan Kode Lama)
        const togglePasswordButton = document.getElementById('togglePassword');
        const eyeOpenIcon = document.getElementById('eye-open');
        const eyeClosedIcon = document.getElementById('eye-closed');
        if (togglePasswordButton && passwordInput) {
            togglePasswordButton.addEventListener('click', () => {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                eyeOpenIcon.classList.toggle('hidden');
                eyeClosedIcon.classList.toggle('hidden');
            });
        }

        // === DRIVER.JS (Bawaan Kode Lama, Tidak Diubah) ===
        const startDriverGuide = () => {
            if (typeof Driver === 'undefined') return; // Cek jika driver.js terload
            if (localStorage.getItem('login_guide_seen')) return; // Cek jika sudah pernah akses
            
            // Simpan status di localStorage agar panduan tidak muncul lagi
            localStorage.setItem('login_guide_seen', 'true');
            
            const isMobile = window.innerWidth < 768;
            const driver = new Driver({
                animate: true,
                opacity: 0.75,
                padding: 8,
                allowClose: false,
                stageBackground: '#ffffff',
            });
            driver.defineSteps([{
                    element: '#username',
                    popover: {
                        title: 'Masukkan Username',
                        description: 'Gunakan username Anda (contoh: 20260001).',
                        position: isMobile ? 'bottom' : 'right'
                    }
                },
                {
                    element: '#password',
                    popover: {
                        title: 'Masukkan Password',
                        description: 'Ketik kata sandi Anda di sini.',
                        position: isMobile ? 'bottom' : 'right'
                    }
                },
                {
                    element: 'button[type="submit"]',
                    popover: {
                        title: 'Klik Login',
                        description: 'Setelah semua benar, klik untuk masuk.',
                        position: 'top'
                    }
                },
            ]);
            driver.start();
        };

        // Total waktu delay sebelum driver muncul
        const aosTotal = 1200 + 800;
        // Cek apakah ada error shake, jika ada delay lebih lama
        const shakeDelay = (botContainer && botContainer.classList.contains('shake-error')) ? 1200 : 600;
        const totalDelay = aosTotal + shakeDelay;

        setTimeout(startDriverGuide, totalDelay);
    });
</script>