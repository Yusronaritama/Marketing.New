{{-- [BARU] STATIC WARNING BANNER --}}
    <div class="bg-red-900/30 border border-red-500/30 backdrop-blur-xl p-5 md:p-6 rounded-2xl mb-8 shadow-[0_8px_32px_rgba(220,38,38,0.15)] relative overflow-hidden group">
        {{-- Animated background accent --}}
        <div class="absolute top-0 right-0 w-32 h-32 bg-red-500/20 rounded-full blur-[40px] -mr-10 -mt-10 pointer-events-none group-hover:bg-red-500/30 transition-all duration-700"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-rose-500/20 rounded-full blur-[30px] -ml-10 -mb-10 pointer-events-none"></div>
        
        {{-- Giant Icon background --}}
        <div class="absolute -right-4 -bottom-4 text-red-500/10 text-8xl group-hover:scale-110 group-hover:-rotate-12 transition-all duration-500 transform rotate-12 pointer-events-none">
            <i class="fas fa-exclamation-triangle"></i>
        </div>

        <div class="relative z-10 flex flex-col md:flex-row gap-4 md:gap-5 items-start md:items-center">
            <div class="shrink-0 bg-red-500/20 border border-red-500/30 p-3 rounded-2xl shadow-inner text-red-400 group-hover:text-red-300 transition-colors">
                <i class="fas fa-exclamation-circle text-3xl animate-pulse"></i>
            </div>
            
            <div class="flex-1">
                <h3 class="text-white font-black text-lg tracking-wide uppercase mb-1">
                    Peringatan Penting!
                </h3>
                <p class="text-red-100/90 text-sm leading-relaxed">
                    Data yang Anda isikan di bawah ini bersifat <b class="text-white bg-red-500/20 px-1 py-0.5 rounded">FINAL</b> dan <b class="text-white bg-red-500/20 px-1 py-0.5 rounded">PERMANEN</b>. Data akan langsung disinkronisasi ke server PDDIKTI. <br class="hidden md:block">
                    <span class="font-bold text-red-300 drop-shadow-md">Kesalahan penulisan (Typo) tidak dapat diubah kembali di masa depan.</span> 
                    Mohon isi dengan sungguh-sungguh sesuai dokumen resmi (KTP/Ijazah/KK).
                </p>
            </div>
        </div>
    </div>