<div x-show="currentSlide === 1" 
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 translate-x-10"
     x-transition:enter-end="opacity-100 translate-x-0"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 translate-x-0"
     x-transition:leave-end="opacity-0 -translate-x-10"
     class="absolute inset-0 w-full text-center">
    
    <div class="h-full flex flex-col justify-center py-4 relative z-10">
        
        {{-- Ikon Info (Glassy Blue) --}}
        <div class="flex justify-center mb-4">
            <div class="w-14 h-14 bg-blue-500/20 border border-blue-400/30 rounded-full flex items-center justify-center shadow-[0_0_15px_rgba(59,130,246,0.4)] relative overflow-hidden">
                {{-- Shine Animation --}}
                <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full animate-[shimmer_2s_infinite]"></div>
                <svg class="w-7 h-7 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <h4 class="text-xl font-bold text-white mb-4 drop-shadow-md tracking-tight">Penting!</h4>
        
        {{-- List Poin Penting --}}
        <div class="space-y-3 text-sm text-blue-50 text-left px-4 sm:px-8">
            
            <div class="flex items-start gap-3 p-2 rounded-lg hover:bg-white/5 transition-colors">
                <div class="flex-shrink-0 mt-0.5 bg-green-500/20 rounded-full p-1">
                    <svg class="w-3 h-3 text-green-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                </div>
                <span class="leading-snug">Pastikan data diri sesuai dengan dokumen asli (KTP/Kartu Pelajar)</span>
            </div>

            <div class="flex items-start gap-3 p-2 rounded-lg hover:bg-white/5 transition-colors">
                <div class="flex-shrink-0 mt-0.5 bg-green-500/20 rounded-full p-1">
                    <svg class="w-3 h-3 text-green-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                </div>
                <span class="leading-snug">Siapkan dokumen pendukung seperti foto dan transkrip nilai</span>
            </div>

            <div class="flex items-start gap-3 p-2 rounded-lg hover:bg-white/5 transition-colors">
                <div class="flex-shrink-0 mt-0.5 bg-green-500/20 rounded-full p-1">
                    <svg class="w-3 h-3 text-green-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                </div>
                <span class="leading-snug">Ujian akan dilakukan secara online dengan waktu terbatas</span>
            </div>

        </div>

        {{-- Warning Box (Glassy Yellow) --}}
        <div class="mt-4 mx-4 p-3 bg-yellow-500/10 border border-yellow-400/30 rounded-xl backdrop-blur-sm">
            <p class="text-xs text-yellow-100">
                <strong class="text-yellow-300 block mb-1">⚠️ Perhatian:</strong> 
                Data yang sudah diinput tidak dapat diubah setelah Anda melakukan finalisasi (submit).
            </p>
        </div>

    </div>
</div>