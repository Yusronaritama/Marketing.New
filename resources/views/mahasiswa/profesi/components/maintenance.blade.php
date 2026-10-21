{{-- resources/views/mahasiswa/profesi/components/maintenance.blade.php --}}

{{-- =======================================================
     1. OVERLAY MAINTENANCE SYSTEM (systemGuard)
     Muncul jika Admin mengaktifkan Maintenance
     ======================================================= --}}
<div x-data="systemGuard" x-cloak>
	<!--<div class="fixed top-20 left-4 z-[999999] bg-black text-lime-400 p-4 rounded-xl border border-lime-400 font-mono text-xs opacity-90 pointer-events-none">-->
	<!--           <h4 class="font-bold text-white mb-2 border-b border-lime-400 pb-1">🕵️ DEBUG ALPINE</h4>-->
	<!--           <p>Is Admin? : <span x-text="isAdmin"></span></p>-->
	<!--           <p>Is Active? : <span x-text="isActive"></span></p>-->
	<!--           <p>Scheduled : <span x-text="scheduledAt || 'KOSONG'"></span></p>-->
	<!--           <p>Overlay Mode: <span x-text="overlayMode"></span></p>-->
        <!--</div>-->
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
</div>

