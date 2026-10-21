{{-- Progress Card Wrapper (White Glassmorphism) --}}
<div class="lg:col-span-2 h-full" 
     x-data="{
        // --- LOGIC REMAINS THE SAME ---
        mounted: false, activeView: {{ $currentStep ?? 1 }}, currentStep: {{ $currentStep ?? 1 }}, 
        isPendaftaranComplete: {{ (isset($ereg) && $ereg->step_completed >= 2) ? 'true' : 'false' }},
        isBiodataComplete: {{ (isset($ereg) && $ereg->step_completed >= 3) ? 'true' : 'false' }},
        openDropdown: null,

        // --- INITIALIZATION ---
        init() {
            setTimeout(() => { this.mounted = true; }, 100);
        },

        // --- 1. LOGIKA STEPPER (LENGKAP) ---
        handleStepClick(step) {
            switch(step) {
                case 1:
                    this.activeView = 1;
                    break;
                // ... (Logika case 2, 3, 4, 5 tetap sama) ...
                case 2:
                    if (this.currentStep >= 2) {
                        this.activeView = 2;
                        if(this.activeView == 2) this.toggleDropdown('data-diri');
                    } else {
                        this.showWarning('Silakan selesaikan tahap Login & Verifikasi terlebih dahulu.');
                    }
                    break;
                    
                case 3:
                    if (this.currentStep >= 3 && this.isPendaftaranComplete) {
                        this.activeView = 3;
                    } else {
                        if (this.currentStep < 3) {
                            this.showWarning('Silakan selesaikan Data Diri terlebih dahulu.');
                        } else {
                            this.showWarning('Data pendaftaran Anda belum lengkap.');
                        }
                    }
                    break;
                    
                case 4:
                    if (this.currentStep >= 4 && this.isBiodataComplete) {
                        this.activeView = 4;
                    } else {
                        if (this.currentStep < 4) {
                            this.showWarning('Silakan selesaikan Ujian terlebih dahulu.');
                        } else {
                            this.showWarning('Anda harus melengkapi biodata terlebih dahulu sebelum dapat melanjutkan.');
                        }
                    }
                    break;
                    
                case 5:
                    if (this.currentStep >= 5) {
                        this.activeView = 5;
                    } else {
                        this.showWarning('Silakan selesaikan Pembayaran terlebih dahulu.');
                    }
                    break;
            }
        },

        // --- 2. LOGIKA DROPDOWN ---
        toggleDropdown(id) {
            if (this.openDropdown === id) {
                this.openDropdown = null;
            } else {
                this.openDropdown = id;
            }
        },

        // --- 3. UTILITY ---
        showWarning(message) {
            alert('⚠️ ' + message);
        }
     }"
     x-init="init()">
    
    {{-- 
        CONTAINER KARTU GLASSMORPHISM PUTIH
        Perubahan Kunci: 
        1. Background putih dengan opacity tinggi
        2. Sedikit blur
        3. Border halus
    --}}
    <div class="bg-transparent h-full flex flex-col relative"
         x-show="mounted"
         x-transition:enter="transition ease-out duration-700"
         x-transition:enter-start="opacity-0 translate-y-10"
         x-transition:enter-end="opacity-100 translate-y-0"
         style="display: none;">
        
        {{-- KONTEN FLEXBOX --}}
        <div>
            @include('mahasiswa.dashboard.progress.kolom-flexbox')
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>