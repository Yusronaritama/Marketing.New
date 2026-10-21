{{-- resources/views/mahasiswa/profesi/components/opening.blade.php --}}
<div x-data="{ 
        showOpening: false, 
        progress: 0,
        
        init() {
            // [UPGRADE] Kita tempelin Session ID Laravel ke kuncinya
            // Tiap kali user login ulang, ID ini bakal ganti secara otomatis!
            let sessionKey = 'hasSeenOpening_{{ session()->getId() }}';
            
            let hasSeenOpening = sessionStorage.getItem(sessionKey);
            
            if (hasSeenOpening) {
                // Kalau udah liat di sesi login ini, skip!
                this.showOpening = false;
                setTimeout(() => { $dispatch('opening-selesai'); }, 100);
                
            } else {
                // Kalau ini baru pertama kali login, munculin openingnya!
                this.showOpening = true;
                let interval = setInterval(() => { this.progress += 2; if(this.progress >= 100) clearInterval(interval); }, 40);
                
                setTimeout(() => { 
                    this.showOpening = false; 
                    // Catat ke memori pakai kunci unik session tadi
                    sessionStorage.setItem(sessionKey, 'true');
                    $dispatch('opening-selesai'); 
                }, 2200);
            }
        }
     }" 
     x-show="showOpening" 
     style="display: none;" 
     x-transition:leave="transition ease-out duration-700" 
     x-transition:leave-start="opacity-100 translate-y-0" 
     x-transition:leave-end="opacity-0 -translate-y-8" 
     class="fixed inset-0 z-[100] flex flex-col items-center justify-center bg-slate-900">
     
    {{-- Subtle Grid Pattern --}}
    <div class="absolute inset-0 opacity-5" 
         style="background-image: radial-gradient(circle, #fff 1px, transparent 1px); background-size: 30px 30px;"></div>

    {{-- Glow Effect --}}
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-blue-500/20 rounded-full blur-3xl"></div>

    {{-- Logo --}}
    <div class="relative z-10 group">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-cyan-400 rounded-2xl blur-xl opacity-50 group-hover:opacity-75 transition-opacity animate-pulse"></div>
        <div class="relative bg-white rounded-2xl p-5 shadow-2xl">
            <img src="{{ asset('images/logo_ubp.png') }}" 
                 alt="Logo UBP" 
                 class="w-20 h-20 object-contain" 
                 onerror="this.onerror=null; this.src='[ubpkarawang.ac.id](https://ubpkarawang.ac.id/wp-content/uploads/2021/08/Logo-UBP-Karawang.png)';">
        </div>
    </div>

    {{-- Text --}}
    <div class="relative z-10 mt-8 text-center">
        <h2 class="text-2xl font-bold text-white tracking-[0.3em]">
            PMB <span class="bg-gradient-to-r from-blue-400 to-cyan-300 bg-clip-text text-transparent">PROFESI</span>
        </h2>
        <p class="text-xs font-medium text-slate-400 mt-2 tracking-widest uppercase">Universitas Buana Perjuangan</p>
    </div>

    {{-- Progress Bar --}}
    <div class="absolute bottom-20 w-48 h-1 bg-slate-700 rounded-full overflow-hidden">
        <div class="h-full bg-gradient-to-r from-blue-500 to-cyan-400 rounded-full transition-all duration-100"
             :style="'width: ' + progress + '%'"></div>
    </div>
</div>
