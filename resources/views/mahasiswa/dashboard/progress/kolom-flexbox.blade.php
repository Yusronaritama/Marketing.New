<div class="relative w-full max-w-[1400px] mx-auto min-h-[90vh] bg-slate-900 rounded-[2.5rem] p-4 lg:p-8 shadow-2xl flex flex-col justify-center items-center group mb-10">
    
    <!-- Immersive Background for Focus Mode (Abstract/Soft) -->
    <!-- Gradient mesh background to give a premium feel -->
    <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/20 via-purple-500/20 to-blue-500/20 mix-blend-overlay opacity-80 transition-transform duration-1000 group-hover:scale-105 rounded-[2.5rem]"></div>
    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-400/30 rounded-full blur-[100px] pointer-events-none animate-pulse"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-purple-400/30 rounded-full blur-[100px] pointer-events-none animate-pulse" style="animation-delay: 2s;"></div>

    <!-- The Glass Window (App Interface) -->
    <div class="relative z-10 w-full min-h-[85vh] bg-white/75 backdrop-blur-2xl rounded-3xl border border-white/60 shadow-[0_8px_32px_rgba(0,0,0,0.15)] flex flex-col transition-all duration-500">
        
        <!-- MacOS-style Title Bar -->
        <div class="h-12 bg-white/40 border-b border-white/50 flex items-center px-5 gap-2.5 shrink-0 relative rounded-t-3xl">
            <!-- Window Controls -->
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded-full bg-red-400 shadow-inner"></div>
                <div class="w-3 h-3 rounded-full bg-amber-400 shadow-inner"></div>
                <div class="w-3 h-3 rounded-full bg-green-400 shadow-inner"></div>
            </div>
            
            <!-- Centered Title -->
            <div class="absolute left-1/2 transform -translate-x-1/2 flex items-center gap-2 text-slate-500">
                <i class="fas fa-rocket text-blue-500 text-xs"></i>
                <span class="text-[11px] font-bold uppercase tracking-[0.2em]">Sistem PMB</span>
            </div>
            
            <!-- Right Actions -->
            <div class="ml-auto flex items-center gap-3">
                <span class="text-[10px] font-bold text-slate-400 bg-white/50 px-2 py-1 rounded-md border border-white/60">FOCUS MODE</span>
            </div>
        </div>

        <!-- App Layout Content -->
        <!-- Hilangkan overflow di sini agar membesar mengikuti tinggi konten -->
        <div class="flex flex-col xl:flex-row flex-1 bg-slate-50/30 rounded-b-3xl">
            
            <!-- KOLOM KIRI (Sidebar Stepper & Info) -->
            <!-- Diubah menjadi 50% agar seimbang dengan kolom kanan -->
            <div class="w-full xl:w-1/2 p-6 lg:p-8 border-b xl:border-b-0 xl:border-r border-slate-200/50">
                @include('mahasiswa.dashboard.progress.kiri.index')
            </div>

            <!-- KOLOM KANAN (Konten Utama) -->
            <!-- Diubah menjadi 50% agar seimbang dengan kolom kiri -->
            <div class="w-full xl:w-1/2 p-6 lg:p-10 relative">
                <!-- Inner Container for Content -->
                <div class="w-full max-w-4xl mx-auto bg-transparent rounded-2xl p-6 lg:p-8 h-full">
                    @include('mahasiswa.dashboard.progress.kanan.index')
                </div>
            </div>
            
        </div>
    </div>
</div>

<style>
    /* Custom Scrollbar for Focus Mode to keep it clean */
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(148, 163, 184, 0.3); border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(148, 163, 184, 0.5); }
</style>