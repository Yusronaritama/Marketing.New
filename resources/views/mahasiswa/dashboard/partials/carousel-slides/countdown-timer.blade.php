<div x-show="currentSlide === 0" 
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 translate-x-10"
     x-transition:enter-end="opacity-100 translate-x-0"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 translate-x-0"
     x-transition:leave-end="opacity-0 -translate-x-10"
     class="absolute inset-0 w-full text-center">
    
    <div class="h-full flex flex-col justify-center py-4 relative z-10">
        
        {{-- Ikon Jam (Glassy Red) --}}
        <div class="flex justify-center mb-4">
            <div class="w-14 h-14 bg-red-500/20 border border-red-400/30 rounded-full flex items-center justify-center shadow-[0_0_15px_rgba(239,68,68,0.4)] relative">
                {{-- Shine effect --}}
                <div class="absolute top-0 right-0 w-full h-full rounded-full bg-gradient-to-tr from-transparent via-white/10 to-transparent"></div>
                <svg class="w-7 h-7 text-red-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <h4 class="text-xl font-bold text-white mb-2 drop-shadow-md tracking-tight">Batas Waktu Pendaftaran</h4>
        <p class="text-sm text-blue-100/80 mb-4 font-light">Hitung mundur penutupan pendaftaran:</p>
        
        {{-- Kotak Waktu (Glass Boxes) --}}
        <div x-show="!countdownData.expired" class="flex justify-center gap-3 sm:gap-4 my-2">
            {{-- Hari --}}
            <div class="flex flex-col items-center bg-white/10 border border-white/20 rounded-lg p-2 min-w-[60px] backdrop-blur-sm">
                <div class="text-2xl sm:text-3xl font-bold text-white drop-shadow-sm" x-text="countdownData.days">00</div>
                <div class="text-[9px] text-blue-200 uppercase font-bold tracking-wider mt-1">Hari</div>
            </div>
            
            {{-- Jam --}}
            <div class="flex flex-col items-center bg-white/10 border border-white/20 rounded-lg p-2 min-w-[60px] backdrop-blur-sm">
                <div class="text-2xl sm:text-3xl font-bold text-white drop-shadow-sm" x-text="countdownData.hours">00</div>
                <div class="text-[9px] text-blue-200 uppercase font-bold tracking-wider mt-1">Jam</div>
            </div>
            
            {{-- Menit --}}
            <div class="flex flex-col items-center bg-white/10 border border-white/20 rounded-lg p-2 min-w-[60px] backdrop-blur-sm">
                <div class="text-2xl sm:text-3xl font-bold text-white drop-shadow-sm" x-text="countdownData.minutes">00</div>
                <div class="text-[9px] text-blue-200 uppercase font-bold tracking-wider mt-1">Mnt</div>
            </div>
            
            {{-- Detik --}}
            <div class="flex flex-col items-center bg-white/10 border border-white/20 rounded-lg p-2 min-w-[60px] backdrop-blur-sm relative overflow-hidden">
                {{-- Efek merah halus untuk detik --}}
                <div class="absolute inset-0 bg-red-500/10"></div>
                <div class="text-2xl sm:text-3xl font-bold text-white drop-shadow-sm relative z-10" x-text="countdownData.seconds">00</div>
                <div class="text-[9px] text-blue-200 uppercase font-bold tracking-wider mt-1 relative z-10">Dtk</div>
            </div>
        </div>

        {{-- Tampilan Jika Expired --}}
        <div x-show="countdownData.expired" class="my-4">
            <div class="inline-block px-6 py-2 bg-red-600/80 border border-red-400 rounded-lg shadow-lg">
                <p class="text-lg font-bold text-white tracking-wide">Pendaftaran Ditutup 🔒</p>
            </div>
        </div>

        <p class="text-xs text-blue-200/60 mt-4 italic">
            Pastikan semua data sudah tersimpan (finalisasi).
        </p>
    </div>
</div>