{{-- INDICATOR SLIDE (RESPONSIVE) --}}
    <div class="mb-8 px-2">
        
        {{-- 1. JUDUL STEP AKTIF (KHUSUS MOBILE) --}}
        {{-- Karena teks di tombol kita sembunyikan di HP, kita butuh judul ini --}}
        <div class="md:hidden text-center mb-4 transition-all duration-300" x-transition>
            <span class="text-[10px] font-bold text-blue-500 uppercase tracking-widest bg-blue-50 px-2 py-1 rounded-full">
                Langkah <span x-text="slide"></span> dari 4
            </span>
            <h2 class="text-xl font-extrabold text-white drop-shadow-md mt-1" 
                x-text="slide === 1 ? 'Identitas Diri' : 
                       (slide === 2 ? 'Domisili & Kontak' : 
                       (slide === 3 ? 'Data Orang Tua' : 'Finalisasi Data'))">
            </h2>
        </div>

        {{-- 2. PROGRESS BAR (DARK PREMIUM UI) --}}
        <div class="bg-white/5 backdrop-blur-xl rounded-2xl md:rounded-full p-2 shadow-2xl border border-white/10">
            {{-- Gunakan Grid di Mobile (4 kolom), Flex di Desktop --}}
            <div class="grid grid-cols-4 gap-2 md:flex md:items-center md:justify-center">
                
                {{-- STEP 1 --}}
                <button type="button" @click="slide = 1" 
                        class="group relative flex flex-col md:flex-row items-center justify-center p-2 md:px-4 md:py-2 rounded-xl md:rounded-full transition-all duration-300"
                        :class="slide === 1 ? 'bg-blue-600 shadow-[0_0_20px_rgba(37,99,235,0.4)] border border-blue-400/50' : 'hover:bg-white/10'">
                    
                    <div class="w-8 h-8 md:w-6 md:h-6 flex items-center justify-center rounded-full text-xs transition-colors shadow-inner"
                         :class="slide === 1 ? 'bg-white/20 text-white' : (slide > 1 ? 'bg-blue-500/30 text-blue-300' : 'bg-slate-800 border border-slate-700 text-slate-400')">
                        <i class="fas fa-user-circle text-sm md:text-xs"></i>
                    </div>
                    
                    {{-- Teks: Hidden di Mobile, Show di Desktop --}}
                    <span class="hidden md:block ml-2 text-xs font-bold uppercase tracking-wide transition-colors"
                          :class="slide === 1 ? 'text-white drop-shadow-md' : 'text-slate-400 group-hover:text-slate-200'">
                        Identitas
                    </span>
                    
                    {{-- Indicator Garis Bawah (Mobile Only) --}}
                    <div class="md:hidden absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-1 h-1 rounded-full bg-blue-400 shadow-[0_0_10px_rgba(96,165,250,1)]" x-show="slide === 1"></div>
                </button>

                {{-- CONNECTOR (Desktop Only) --}}
                <div class="hidden md:block w-8 h-0.5 rounded-full transition-colors" 
                     :class="slide > 1 ? 'bg-blue-500 shadow-[0_0_10px_rgba(59,130,246,0.8)]' : 'bg-white/10'"></div>

                {{-- STEP 2 --}}
                <button type="button" @click="slide = 2" 
                        class="group relative flex flex-col md:flex-row items-center justify-center p-2 md:px-4 md:py-2 rounded-xl md:rounded-full transition-all duration-300"
                        :class="slide === 2 ? 'bg-indigo-600 shadow-[0_0_20px_rgba(79,70,229,0.4)] border border-indigo-400/50' : 'hover:bg-white/10'">
                    
                    <div class="w-8 h-8 md:w-6 md:h-6 flex items-center justify-center rounded-full text-xs transition-colors shadow-inner"
                         :class="slide === 2 ? 'bg-white/20 text-white' : (slide > 2 ? 'bg-indigo-500/30 text-indigo-300' : 'bg-slate-800 border border-slate-700 text-slate-400')">
                        <i class="fas fa-map-marker-alt text-sm md:text-xs"></i>
                    </div>
                    
                    <span class="hidden md:block ml-2 text-xs font-bold uppercase tracking-wide transition-colors"
                          :class="slide === 2 ? 'text-white drop-shadow-md' : 'text-slate-400 group-hover:text-slate-200'">
                        Domisili
                    </span>
                    
                    <div class="md:hidden absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-1 h-1 rounded-full bg-indigo-400 shadow-[0_0_10px_rgba(129,140,248,1)]" x-show="slide === 2"></div>
                </button>

                {{-- CONNECTOR --}}
                <div class="hidden md:block w-8 h-0.5 rounded-full transition-colors" 
                     :class="slide > 2 ? 'bg-indigo-500 shadow-[0_0_10px_rgba(99,102,241,0.8)]' : 'bg-white/10'"></div>

                {{-- STEP 3 --}}
                <button type="button" @click="slide = 3" 
                        class="group relative flex flex-col md:flex-row items-center justify-center p-2 md:px-4 md:py-2 rounded-xl md:rounded-full transition-all duration-300"
                        :class="slide === 3 ? 'bg-purple-600 shadow-[0_0_20px_rgba(147,51,234,0.4)] border border-purple-400/50' : 'hover:bg-white/10'">
                    
                    <div class="w-8 h-8 md:w-6 md:h-6 flex items-center justify-center rounded-full text-xs transition-colors shadow-inner"
                         :class="slide === 3 ? 'bg-white/20 text-white' : (slide > 3 ? 'bg-purple-500/30 text-purple-300' : 'bg-slate-800 border border-slate-700 text-slate-400')">
                        <i class="fas fa-users text-sm md:text-xs"></i>
                    </div>
                    
                    <span class="hidden md:block ml-2 text-xs font-bold uppercase tracking-wide transition-colors"
                          :class="slide === 3 ? 'text-white drop-shadow-md' : 'text-slate-400 group-hover:text-slate-200'">
                        Orang Tua
                    </span>

                    <div class="md:hidden absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-1 h-1 rounded-full bg-purple-400 shadow-[0_0_10px_rgba(192,132,252,1)]" x-show="slide === 3"></div>
                </button>

                {{-- CONNECTOR --}}
                <div class="hidden md:block w-8 h-0.5 rounded-full transition-colors" 
                     :class="slide > 3 ? 'bg-purple-500 shadow-[0_0_10px_rgba(168,85,247,0.8)]' : 'bg-white/10'"></div>

                {{-- STEP 4 --}}
                <button type="button" @click="slide = 4" 
                        class="group relative flex flex-col md:flex-row items-center justify-center p-2 md:px-4 md:py-2 rounded-xl md:rounded-full transition-all duration-300"
                        :class="slide === 4 ? 'bg-emerald-600 shadow-[0_0_20px_rgba(5,150,105,0.4)] border border-emerald-400/50' : 'hover:bg-white/10'">
                    
                    <div class="w-8 h-8 md:w-6 md:h-6 flex items-center justify-center rounded-full text-xs transition-colors shadow-inner"
                         :class="slide === 4 ? 'bg-white/20 text-white' : 'bg-slate-800 border border-slate-700 text-slate-400'">
                        <i class="fas fa-check-circle text-sm md:text-xs"></i>
                    </div>
                    
                    <span class="hidden md:block ml-2 text-xs font-bold uppercase tracking-wide transition-colors"
                          :class="slide === 4 ? 'text-white drop-shadow-md' : 'text-slate-400 group-hover:text-slate-200'">
                        Finalisasi
                    </span>

                    <div class="md:hidden absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-1 h-1 rounded-full bg-emerald-400 shadow-[0_0_10px_rgba(52,211,153,1)]" x-show="slide === 4"></div>
                </button>

            </div>
        </div>
    </div>