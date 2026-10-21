{{-- =============================================================== --}}
    {{-- CUSTOM ERROR MODAL POPUP (TAILWIND STYLE)                       --}}
    {{-- =============================================================== --}}
    <template x-if="showErrorModal">
        <div class="fixed inset-0 z-[9999] flex items-center justify-center px-4" x-cloak>
            
            {{-- 1. Backdrop Gelap (Blur) --}}
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" 
                 x-show="showErrorModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"></div>
            
            {{-- 2. Konten Modal --}}
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden transform transition-all"
                 x-show="showErrorModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                {{-- Header Modal --}}
                <div class="bg-red-50 px-6 py-4 border-b border-red-100 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-600 text-lg animate-pulse"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-red-800">Data Belum Valid</h3>
                        <p class="text-xs text-red-600 font-medium">Mohon perbaiki kesalahan berikut:</p>
                    </div>
                </div>

                {{-- Body Modal (List Error) --}}
                <div class="p-6 max-h-[60vh] overflow-y-auto custom-scrollbar">
                    @if($errors->any())
                        <ul class="space-y-3">
                            @foreach ($errors->all() as $error)
                                <li class="flex items-start gap-3 p-3 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700 hover:bg-red-50 hover:border-red-100 transition-colors">
                                    <i class="fas fa-times-circle text-red-500 mt-0.5"></i>
                                    <span>{{ $error }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                {{-- Footer Modal --}}
                <div class="bg-gray-50 px-6 py-4 flex justify-end border-t border-gray-100">
                    <button @click="
                        showErrorModal = false; 
                        setTimeout(() => { 
                            const firstError = document.querySelector('.input-error'); 
                            if(firstError) { 
                                firstError.scrollIntoView({behavior: 'smooth', block: 'center'}); 
                                firstError.focus(); 
                            } 
                        }, 300)"
                        class="w-full sm:w-auto px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl shadow-lg shadow-red-500/30 transition-all transform hover:scale-105 active:scale-95 text-sm">
                        <i class="fas fa-wrench mr-2"></i> Siap, Saya Perbaiki
                    </button>
                </div>
            </div>
        </div>
    </template>