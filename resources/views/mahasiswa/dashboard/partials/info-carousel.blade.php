@php
    $majorsData = \App\Models\ProgramStudi::query()
                    ->where('status_prodi', 1) 
                    ->orderBy('jenjang', 'asc')
                    ->orderBy('nama_prodi', 'asc')
                    ->get()
                    ->map(function($prodi) {
                        return [
                            'id' => $prodi->kode_prodi,
                            'nama' => $prodi->nama_prodi,
                            'jenjang' => $prodi->jenjang,
                            'sisa' => ($prodi->kuota ?? 0) > 9000 ? '∞' : ($prodi->kuota ?? 0),
                            'persen' => ($prodi->daya_tampung > 0) 
                                        ? round(($prodi->jumlah_pendaftar / $prodi->daya_tampung) * 100, 1) 
                                        : 0
                        ];
                    })
                    ->values(); 
@endphp

<div class="w-full relative z-20 h-full"
     x-data='{
        selected: "", 
        openDropdown: false,
        searchQuery: "",
        majors: @json($majorsData),
        get current() {
            return this.majors.find(m => m.id == this.selected);
        },
        get filteredMajors() {
            if (this.searchQuery === "") return this.majors;
            return this.majors.filter(m => m.nama.toLowerCase().includes(this.searchQuery.toLowerCase()) || m.jenjang.toLowerCase().includes(this.searchQuery.toLowerCase()));
        }
     }'>

    <div class="bg-[#0A1930]/40 backdrop-blur-xl border border-white/10 rounded-2xl shadow-inner overflow-visible relative h-full flex flex-col">
        
        <div class="relative z-10 p-5 flex flex-col h-full"> 
            
            {{-- 1. Custom Searchable Dropdown --}}
            <div class="mb-5">
                <div class="relative" @click.away="openDropdown = false">
                    <!-- Trigger Button -->
                    <button @click="openDropdown = !openDropdown" 
                            class="w-full flex items-center justify-between rounded-xl border border-white/20 bg-white/5 hover:bg-white/10 px-4 py-3.5 text-xs font-bold text-white shadow-sm focus:border-cyan-400 focus:outline-none transition-all cursor-pointer font-mono uppercase">
                        
                        <span x-text="current ? '[' + current.jenjang + '] ' + current.nama : '-- KETIK / PILIH JURUSAN --'" 
                              :class="current ? 'text-white' : 'text-cyan-200/50'"></span>
                        
                        <div class="text-cyan-400 ml-2">
                            <i class="fas fa-chevron-down text-sm transition-transform" :class="openDropdown ? 'rotate-180' : ''"></i>
                        </div>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="openDropdown" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                         class="relative mt-2 w-full bg-[#030b1a]/95 backdrop-blur-2xl border border-cyan-500/30 rounded-xl shadow-2xl overflow-hidden flex flex-col">
                        
                        <!-- Search Input -->
                        <div class="p-3 border-b border-white/10 bg-white/5">
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-cyan-400/60"></i>
                                <input type="text" x-model="searchQuery" placeholder="Ketik nama jurusan..." 
                                       class="w-full bg-black/40 border border-white/10 rounded-lg pl-9 pr-3 py-2.5 text-xs text-white placeholder-slate-400 focus:outline-none focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400 font-mono transition-all">
                            </div>
                        </div>

                        <!-- Options List -->
                        <ul class="max-h-56 overflow-y-auto custom-scrollbar p-2">
                            <template x-for="major in filteredMajors" :key="major.id">
                                <li>
                                    <button @click="selected = major.id; openDropdown = false; searchQuery = ''"
                                            class="w-full text-left px-3 py-2.5 rounded-lg text-[10px] md:text-xs font-mono uppercase transition-colors"
                                            :class="selected == major.id ? 'bg-cyan-500/30 text-cyan-300 font-bold border border-cyan-500/30' : 'text-slate-200 hover:bg-white/10 border border-transparent'">
                                        <span x-text="'[' + major.jenjang + '] ' + major.nama"></span>
                                    </button>
                                </li>
                            </template>
                            <li x-show="filteredMajors.length === 0" class="text-center py-5 text-xs text-slate-400 font-mono">
                                <i class="fas fa-ghost text-2xl mb-2 text-slate-500 block"></i>
                                Jurusan tidak ditemukan
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- 2. Result Display Area --}}
            <div class="flex-grow bg-white/5 rounded-xl border border-white/10 p-4 relative overflow-hidden shadow-sm flex flex-col justify-center min-h-[160px]">
                
                {{-- STATE 1: IDLE --}}
                <div x-show="!selected" class="flex flex-col items-center justify-center h-full text-center absolute inset-0">
                    <div class="w-12 h-12 rounded-full bg-cyan-500/10 flex items-center justify-center mb-3 text-cyan-400/50">
                        <i class="fas fa-university text-2xl animate-pulse"></i>
                    </div>
                    <p class="text-[10px] font-mono text-cyan-200/50 uppercase tracking-widest">Silakan pilih program studi...</p>
                </div>

                {{-- STATE 2: HASIL PENCARIAN --}}
                <div x-show="selected" style="display: none;" class="flex flex-col h-full justify-center"
                     x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100">
                    
                    <template x-if="current">
                        <div>
                            {{-- Nama Prodi --}}
                            <div class="text-center mb-4">
                                <span class="inline-block bg-cyan-500/20 text-cyan-300 text-[9px] font-bold px-2 py-0.5 rounded uppercase tracking-wider border border-cyan-400/30 mb-2" x-text="current.jenjang"></span>
                                <h4 class="text-sm font-black text-white leading-tight uppercase" x-text="current.nama"></h4>
                            </div>

                            {{-- Angka Kuota --}}
                            <div class="bg-cyan-900/30 rounded-xl p-4 border border-cyan-500/20 shadow-inner flex items-center justify-between">
                                <div>
                                    <span class="block text-[9px] text-cyan-200 uppercase font-mono tracking-widest mb-1">Sisa Kuota</span>
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-3xl font-black text-cyan-300" x-text="current.sisa"></span>
                                        <span class="text-[10px] text-cyan-400/70 font-mono uppercase">Kursi</span>
                                    </div>
                                </div>
                                <div class="w-12 h-12 rounded-full bg-cyan-500/20 border border-cyan-400/30 flex items-center justify-center text-cyan-400">
                                    <i class="fas fa-users text-xl"></i>
                                </div>
                            </div>

                            {{-- Warning jika kuota menipis --}}
                            <div x-show="current.sisa !== '∞' && current.sisa <= 10 && current.sisa > 0" class="mt-3 text-center">
                                <span class="text-[10px] font-bold text-amber-400 uppercase tracking-widest flex items-center justify-center gap-1">
                                    <i class="fas fa-exclamation-triangle"></i> Kuota Menipis!
                                </span>
                            </div>
                            <div x-show="current.sisa === 0" class="mt-3 text-center">
                                <span class="text-[10px] font-bold text-red-400 uppercase tracking-widest flex items-center justify-center gap-1">
                                    <i class="fas fa-times-circle"></i> Kuota Penuh
                                </span>
                            </div>

                        </div>
                    </template>
                </div>
                
            </div>
            
            <div class="mt-4 text-center">
                <p class="text-[9px] text-cyan-200/50 font-mono">
                    Real-time Database Query
                </p>
            </div>
        </div>
    </div>
</div>