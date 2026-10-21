{{-- ==========================================
     WADAH TRANSISI HALAMAN (PAGE TRANSITION)
     ========================================== --}}
<div x-data="pageTransition()" 
     x-init="initTransition()"
     
     {{-- display: none di sini nahan halaman biar gak kedip (flash) sebelum Alpine siap --}}
     style="display: none;"
     x-show="showPage"
     
     {{-- Animasi Masuk --}}
     x-transition:enter="transition-all duration-500 ease-out"
     x-transition:enter-start="opacity-0 translate-y-8"
     x-transition:enter-end="opacity-100 translate-y-0"
     
     {{-- Animasi Keluar --}}
     x-transition:leave="transition-all duration-300 ease-in"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-8"
     
     class="w-full min-h-screen">
    
    {{-- Di sinilah konten utama (yield) akan disuntikkan secara otomatis --}}
    {{ $slot }}

</div>

{{-- Script Logic Transisi --}}
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('pageTransition', () => ({
        showPage: false, // Default false, tertahan oleh style="display: none;"

        initTransition() {
            // 1. ANIMASI MASUK: Begitu Alpine siap, langsung munculin perlahan
            setTimeout(() => {
                this.showPage = true;
            }, 50);

            // 2. ANIMASI KELUAR: Cegat semua klik pada tag <a> (Link)
            document.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', (e) => {
                    let href = link.getAttribute('href');

                    if (href && !href.startsWith('#') && link.target !== '_blank' && !href.startsWith('javascript')) {
                        e.preventDefault(); 
                        
                        // Sembunyikan halaman (memicu x-transition:leave)
                        this.showPage = false; 
                        
                        // Tunggu transisi keluar selesai (350ms), baru pindah URL
                        setTimeout(() => {
                            window.location.href = href;
                        }, 350);
                    }
                });
            });
        }
    }));
});
</script>