{{-- Slide 3: Butuh Bantuan? (Redesain Glassmorphism) --}}
<div x-show="currentSlide === 2"
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 translate-x-10"
     x-transition:enter-end="opacity-100 translate-x-0"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 translate-x-0"
     x-transition:leave-end="opacity-0 -translate-x-10"
     class="absolute inset-0 w-full" style="display: none;">
    
    <div class="text-center h-full flex flex-col justify-center py-4 relative">
        
        {{-- Ikon Utama Bantuan --}}
        <div class="w-16 h-16 mx-auto bg-green-500/20 border border-green-400/30 rounded-full flex items-center justify-center mb-4 relative overflow-hidden shadow-lg">
            {{-- Shine effect kecil di ikon --}}
            <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-green-200/40 to-transparent"></div>
            <svg class="w-8 h-8 text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
        </div>

        <h4 class="text-lg font-semibold text-white mb-2 drop-shadow-md">Butuh Bantuan?</h4>
        
        <div class="space-y-3 text-sm text-blue-100 mt-4">
            {{-- Hotline --}}
            <div class="flex items-center justify-center space-x-2">
                <svg class="w-4 h-4 text-blue-300" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                </svg>
                <span>WA: 0812-3456-7890</span> {{-- Ubah ke WA, lebih relevan --}}
            </div>
            {{-- Email --}}
            <div class="flex items-center justify-center space-x-2">
                <svg class="w-4 h-4 text-blue-300" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                </svg>
                <span>Email: pmb@ubpkarawang.ac.id</span>
            </div>
            {{-- Jam Operasional --}}
            <div class="flex items-center justify-center space-x-2">
                <svg class="w-4 h-4 text-blue-300" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                </svg>
                <span>Jam Operasional: 08:00 - 16:00 WIB</span>
            </div>
        </div>

        <div class="mt-4 mx-auto max-w-[80%] bg-white/10 border border-white/20 rounded-lg p-3 backdrop-blur-sm">
            <p class="text-xs text-blue-100">
                Tim support kami siap membantu Anda selama jam operasional.
            </p>
        </div>
    </div>
</div>