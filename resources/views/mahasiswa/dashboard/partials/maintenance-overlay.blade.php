<style>
    /* Kunci scroll body saat maintenance aktif */
    body.is-maintenance { overflow: hidden !important; }

    /* Overlay Hitam Full Screen */
    .maintenance-overlay {
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background-color: rgba(15, 23, 42, 0.98); /* Warna gelap pekat */
        z-index: 999999; /* Pastikan paling atas menutupi sidebar/navbar */
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: #ffffff;
        backdrop-filter: blur(8px);
    }
    
    .maintenance-icon { font-size: 6rem; margin-bottom: 1rem; animation: float 3s ease-in-out infinite; }
    
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
        100% { transform: translateY(0px); }
    }
</style>

<div class="maintenance-overlay">
    <div class="maintenance-icon">🛠️</div>
    <h2 class="text-3xl font-bold mb-2">Sistem Sedang Dalam Perbaikan</h2>
    <p class="text-gray-300 mb-8 max-w-lg px-4">
        Mohon maaf, saat ini kami sedang melakukan pemeliharaan sistem. 
        Akses dashboard ditutup sementara. Silakan kembali lagi nanti.
    </p>

    {{-- Tombol Logout (Penting agar user tidak terjebak) --}}
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded transition">
            Keluar Aplikasi
        </button>
    </form>
</div>

<script>
    // Tambahkan class ke body untuk mematikan scroll
    document.body.classList.add('is-maintenance');
</script>