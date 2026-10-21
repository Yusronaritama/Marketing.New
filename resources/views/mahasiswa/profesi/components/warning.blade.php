{{-- resources/views/mahasiswa/profesi/components/warning.blade.php --}}

<div x-data="systemGuard" x-cloak>
    <template x-if="warningMode">
        <div>
            {{-- 1. MODAL FULL SCREEN (Muncul Pertama Kali) --}}
            <div x-show="showFullModal"
                 class="fixed inset-0 z-[9999] bg-slate-50 transition-transform duration-700 ease-in-out"
                 x-transition:leave="transform -translate-y-full opacity-0">
                
                <div class="absolute inset-0 overflow-hidden pointer-events-none">
                    <div class="absolute inset-0 opacity-[0.05]" 
                         style="background-image: radial-gradient(#1e3a8a 1px, transparent 1px); background-size: 30px 30px;">
                    </div>
                    <div class="absolute top-0 right-0 -mr-20 -mt-20 w-[300px] md:w-[500px] h-[300px] md:h-[500px] bg-blue-900/20 rounded-full blur-3xl mix-blend-multiply animate-pulse"></div>
                    <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-[250px] md:w-[400px] h-[250px] md:h-[400px] bg-yellow-500/20 rounded-full blur-3xl mix-blend-multiply"></div>
                </div>

                <div class="absolute inset-0 overflow-y-auto">
                    <div class="min-h-full flex items-center justify-center py-10 px-4 md:px-12">
                        <div class="relative z-10 w-full max-w-7xl grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12 items-center">
                            
                            <div class="order-1 md:order-2 flex justify-center">
                                <img src="{{ asset('/images/Maintenance.jpg') }}" 
                                     alt="Maintenance Illustration" 
                                     class="w-64 md:w-full max-w-lg drop-shadow-2xl rounded-2xl border-4 border-white/50 animate-[float_6s_ease-in-out_infinite]">
                            </div>

                            <div class="order-2 md:order-1 text-center md:text-left">
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 text-blue-900 font-bold text-[10px] md:text-xs uppercase tracking-widest mb-4 md:mb-6 border border-blue-200">
                                    <span class="w-2 h-2 rounded-full bg-red-600 animate-pulse"></span>
                                    PMB System Notice
                                </div>

                                <h1 class="text-3xl md:text-6xl font-black text-slate-900 mb-4 md:mb-6 leading-tight">
                                    Jadwal <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-800 via-red-600 to-yellow-500">Perbaikan</span> <br> Sistem.
                                </h1>

                                <p class="text-slate-600 text-sm md:text-xl mb-6 md:mb-8 leading-relaxed max-w-lg mx-auto md:mx-0">
                                    Kami akan melakukan pemeliharaan server rutin. Mohon simpan seluruh pekerjaan Anda sebelum waktu di bawah ini.
                                </p>

                                <div class="bg-white border-l-4 border-blue-900 shadow-xl rounded-r-xl p-5 md:p-6 mb-8 text-left max-w-md mx-auto md:mx-0 relative overflow-hidden group">
                                    <div class="absolute inset-0 bg-yellow-50/50 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left duration-500"></div>
                                    <div class="relative z-10">
                                        <p class="text-[10px] md:text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Estimasi Waktu Dimulai</p>
                                        <div class="flex items-center gap-3 text-slate-800">
                                            <i class="far fa-clock text-xl md:text-2xl text-red-600"></i>
                                            <span class="font-mono text-lg md:text-2xl font-bold text-blue-900" x-text="formattedSchedule"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col md:flex-row gap-4 justify-center md:justify-start pb-4 md:pb-0">
                                    <button type="button" 
                                            @click="closeModal()" 
                                            class="w-full md:w-auto px-8 py-4 rounded-xl bg-blue-900 text-white font-bold text-lg shadow-lg hover:bg-blue-800 hover:shadow-blue-900/30 transform hover:-translate-y-1 transition-all duration-300 border-b-4 border-blue-950">
                                        Saya Mengerti, Lanjutkan
                                    </button>
                                </div>
                                
                                <p class="mt-4 md:mt-6 text-slate-400 text-[10px] md:text-xs">
                                    *Notifikasi ini akan diminimalkan setelah diklik.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. TOP BANNER (Muncul Setelah Modal Ditutup) --}}
            <div x-show="showTopBanner"
                 class="fixed top-24 left-0 right-0 z-[60] flex justify-center px-4 pointer-events-none transition-all duration-700 ease-out"
                 x-transition:enter="opacity-0 translate-y-[-100%]"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="opacity-0 translate-y-[-100%]">
                
                 <div class="max-w-4xl w-full bg-blue-900/95 backdrop-blur-sm text-white px-6 py-4 rounded-xl shadow-2xl border-2 border-yellow-400 flex flex-col md:flex-row items-center justify-center gap-4 text-center md:text-left pointer-events-auto">
                    <div class="bg-white/10 p-3 rounded-full animate-pulse flex-shrink-0 border border-white/20">
                        <i class="fas fa-tools text-2xl md:text-3xl text-yellow-400"></i>
                    </div>
                    <div class="flex-grow">
                        <h3 class="font-bold text-lg uppercase mb-1 tracking-wide text-yellow-400">
                            ⚠️ PMB System Maintenance
                        </h3>
                        <p class="font-medium text-sm leading-snug text-blue-100 mb-2 md:mb-1">
                            Mohon selesaikan aktivitas sebelum waktu berikut:
                        </p>
                        <div class="inline-block bg-white text-blue-900 font-mono font-bold px-4 py-1.5 rounded-lg text-sm mt-1 shadow-inner border border-blue-200">
                            <i class="far fa-clock mr-1 text-red-600"></i> 
                            <span x-text="formattedSchedule"></span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </template>
</div>

<style>
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
    }
</style>